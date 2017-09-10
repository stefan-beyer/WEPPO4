<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Presentation;

/**
 * Simple Templating engine that uses {curly} bracket to define placholders for params.
 * 
 * Additionally, some functions can be applied. @TODO more doc.
 * 
 * At the moment, parts cannot be used in CurlyTemplate.
 * 
 */
class CurlyTemplate extends TemplateBase {
    static protected $templateRoot = null;
    
    static protected $functions = [
        'upper'=>'mb_strtoupper',
        'lower'=>'mb_strtolower',
        'mask'=>'htmlentities',
        'substr'=>'mb_substr',
    ];
    
    protected $name;
    protected $code = null;
    protected $filename = null;
    
    public function __construct(string $name, \WEPPO\Controller\Controller &$controller) {
        parent::__construct($controller);
        $this->setName($name);
        
        if (self::$templateRoot === null) {
            if (\WEPPO\Application\Settings::getInstance()->has('curlyTemplateRoot')) {
                self::$templateRoot = \WEPPO\Application\Settings::getInstance()->get('curlyTemplateRoot', APP_ROOT);
            } else {
                self::$templateRoot = \WEPPO\Application\Settings::getInstance()->get('templateRoot', APP_ROOT);
            }
            if (substr(self::$templateRoot, -1) !== '/') {
                self::$templateRoot = self::$templateRoot . '/';
            }
        }
    }
    
    public function getOutput(bool $trim = true): string {
        if ($this->isExisting()) {
            if ($this->code !== null) {
                $code = $this->code;
            } else {
                $code = file_get_contents($this->getFilename());
            }
            
            #do {
            #    echo '#';
            #    try {
            $code = $this->process($code);
            #    } catch (NoMoreMatchesException $e) {
            #        break;
            #    }
            #} while (true);
            if ($trim) {
                $code = trim($code);
            }
            
            return $code;
        }
        throw new TemplateException('Template "' . $this->name . '" existiert nicht. gesuchte Template-Datei: ' . $this->getFilename());
    }
    
    protected function process($code) {
        $erg = preg_match_all('/\\{([a-zA-Z]+)(\\|([a-zA-Z0-9\\|,]+))?\\}/', $code, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if (!$erg) {
            return $code;
            #throw new NoMoreMatchesException();
        }
        #o($erg);
        #o($matches);
        foreach ($matches as $m) {
            //$start = $m[0][1];
            //$end = $m[0][1];
            $complete = $m[0][0];
            $name = $m[1][0];
            $function = isset($m[3]) ? $m[3][0] : null;

            $value = $this->get($name);
            
            if (!empty($function)) {
                $function = explode('|', $function);
                foreach ($function as $fd) {
                    $fc = explode(',', $fd);
                    $fname = array_shift($fc);
                    if (isset(self::$functions[$fname])) {
                        $fname = self::$functions[$fname];
                    }

                    array_unshift($fc, $value);
                    
                    if (!is_callable($fname)) {
                        throw new \Exception('CurlyTemplate Callback '.var_export($fname, true) . ' not callable.');
                    }

                    $value = call_user_func_array($fname, $fc);
                }
            }

            $code = str_replace($complete, $value, $code);
        }
        return $code;
    }

    
    static public function registerFuntion($name, $callback) {
        self::$functions[$name] = $callback;
    }


    public function getFilename() {
        if ($this->filename === null) {
            $this->filename = self::$templateRoot . $this->name;
        }
        return $this->filename;
    }
    
    public function setName($name) {
        $this->name = $name;
        $this->filename = null;
    }
    
    public function setCode(string $code) {
        $this->code = $code;
    }
    
    public function isExisting(): bool {
        return file_exists($this->getFilename());
    }

}

class NoMoreMatchesException extends \Exception {
    
}