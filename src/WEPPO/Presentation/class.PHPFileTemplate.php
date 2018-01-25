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
 * A Templating engine that uses simple PHP-Scripts.
 * 
 * Thy will be included in the Templates getOutput() method,
 * so $this will be the template instance.
 * 
 * For more abeout Templates @see WEPPO::Presentation::TemplateBase
 */
class PHPFileTemplate extends TemplateBase {
    static protected $templateRoot = null;
    
    protected $name;
    
    protected $filename = null;
    
    public function __construct(string $name, /*\WEPPO\Controller\Controller*/ &$controller) {
        parent::__construct($controller);
        $this->setName($name);
    }
    
    public function getOutput(bool $trim = true): string {
        if ($this->isExisting()) {
            \ob_start();
            include $this->getFilename();
            $ret = \ob_get_contents();
            \ob_end_clean();
            
            if ($trim) {
                $ret = trim($ret);
            }
            return $ret;
        }
        throw new TemplateException('Template "' . $this->name . '" existiert nicht. gesuchte Template-Datei: ' . $this->getFilename());
    }

    public function getFilename() {
        if ($this->filename === null) {
            $this->filename = $this->name;
            if (strpos($this->filename, '.php') !== (strlen($this->filename)-4)) {
                $this->filename .= '.php';
            }
            $this->filename = self::getTemplateRoot() . $this->filename;
        }
        return $this->filename;
    }
    
    static public function getTemplateRoot() {
        if (self::$templateRoot === null) {
            if (\WEPPO\Application\Settings::getInstance()->has('phpTemplateRoot')) {
                self::$templateRoot = \WEPPO\Application\Settings::getInstance()->get('phpTemplateRoot', APP_ROOT);
            } else {
                self::$templateRoot = \WEPPO\Application\Settings::getInstance()->get('templateRoot', APP_ROOT);
            }
            if (substr(self::$templateRoot, -1) !== '/') {
                self::$templateRoot = self::$templateRoot . '/';
            }
        }
        return self::$templateRoot;
    }
    
    public function setName($name) {
        $this->name = $name;
        $this->filename = null;
    }
    
    public function isExisting(): bool {
        return file_exists($this->getFilename());
    }

    /**
     * Schnelle, vereinfachte Rückgabe eines Template-Inhaltes
     * 
     * Gibt den Inhalt zurück, tätigt *keine* Ausgabe.
     * 
     * @param string $name Template-Name
     * @param array $params Parameter-Werte
     * @param string $t Template-Typ
     * @return string
     */
    static function quickContent(string $name, array $params, \WEPPO\Controller\Controller &$controller) {
        $t = new self($name, $controller);
        $t->setParams($params);
        return $t->getOutput();
    }

    /**
     * Schnelle, vereinfachte Ausgabe eines Templates
     * 
     * Gibt den Inhalt sirekt aus.
     * 
     * @param string $name Template-Name
     * @param array $params Parameter-Werte
     * @param string $t Template-Typ
     */
    /*
    static function quickOutput(string $name, array $params, \WEPPO\Controller\Controller &$controller) {
        echo self::quickContent($name, $params, $controller);
    }*/
}








