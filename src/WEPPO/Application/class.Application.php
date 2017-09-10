<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Application;

/**
 * In the first place, the application holds the RequestHandler Instance
 * and serves the start methode run().
 * By default, run() will call autoRequest() to handle a normal Request.
 * It will also catch redirect exceptions.
 */
class Application {

    /** @var $requestHandler \WEPPO\Routing\RequestHandler */
    protected $requestHandler;
    
    /**
     * This contruktor must be called in all subclasses.
     * It will register the application in the context.
     * 
     * @param \WEPPO\Routing\RequestHandler $requestHandler
     */
    public function __construct(\WEPPO\Routing\RequestHandler &$requestHandler) {
        $this->requestHandler = $requestHandler;

        $context = Context::getInstance();
        $context->setApplication($this);

        $this->internalInit();
    }

    protected function internalInit() {
        
    }

    public function init() {
        
    }

    /**
     * Starts a normal request handling with the $_SERVER['REQUEST_URI']
     * and $_GET and $_POST vars.
     */
    public function autoRequest() {

        # path will be prepared and converted into an array
        $arrPath = $this->requestHandler->preparePath($_SERVER['REQUEST_URI']);

        # Request erzeugen
        $request = new \WEPPO\Routing\Request($arrPath, $_GET, $_POST);

        # execute request and catch redirections
        try {
            $ret = $this->requestHandler->processRequest($request);
        } catch (\WEPPO\Routing\RedirectException $e) { # other exceptions will be forwarded...
            $url = \WEPPO\Routing\Url::getAbsUrl($e->getUrl());
            header('Location: ' . $url, true, $e->getCode());
            return;
        }

        if ($ret !== \WEPPO\Routing\RequestHandler::OK) {
            throw new \Exception("Die Anfrage kann nicht bearbeitet werden: RequestHandler::processRequest returned FAIL");
        }
    }

    /**
     * This is the main call in a start script.
     * Calls init() and autoRequest()
     */
    public function run() {
        $this->init();
        try {
            $this->autoRequest();
        } catch (\Exception $e) {
            if (Settings::getInstance()->get('printExceptions')) {
                $this->_print_exception($e);
            }
            if (Settings::getInstance()->get('throwExceptions')) {
                throw $e;
            }
        }
    }

    /**
     * Get the RequestHandler instance.
     * 
     * @return \WEPPO\Routing\RequestHandler
     */
    public function &getRequestHandler(): \WEPPO\Routing\RequestHandler {
        return $this->requestHandler;
    }

    /**
     * Helper for pretty printing an exception.
     * 
     * @param \Exception $e
     */
    protected function _print_exception(\Exception $e, $js = true) {
        echo '<h1>'.get_class($e).'</h1>';
        echo '<h2>»', $e->getMessage(), '«</h2>';

        $plus_code_lines = 2;

        $lines = $this->_get_source_lines($e->getFile(), $e->getLine(), $plus_code_lines);
        echo '<div style="margin-top:10px; cursor:pointer;" class="trace_step"><code style="color:blue;">', $e->getFile(), '</code> (<code style="color:brown;">', $e->getLine(), '</code>):',
        //'<code style="color:green;">', $tr['class'], $tr['type'], '<strong>', $tr['function'], '</strong>', '(', implode(', ', $strargs), ')', '</code>',
        '<div class="trace_code" style="font-family:mono;font-size:70%; padding:10px;background-color:#eee;display:none;"><small>Code-Context<br/></small><br/>', implode('<br/>', $lines), '</div>',
        '</div>';

        $trace = $e->getTrace();
        echo '<h3>Trace</h3><ol>';
        foreach ($trace as &$tr) {
            $args = $tr['args'];
            $strargs = [];
            foreach ($args as &$arg) {
                $a = gettype($arg);
                if ($a === 'object') {
                    $a = get_class($arg);
                } else if ($a === 'array') {
                    $a .= '(' . count($arg) . ')';
                }
                $strargs[] = '<em>&lt;' . htmlentities($a) . '&gt;</em>';
            }
            $lines = $this->_get_source_lines($tr['file'], $tr['line'], $plus_code_lines);
            echo '<li style="margin-top:10px; cursor:pointer;" class="trace_step"><code style="color:blue;">', $tr['file'], '</code> (<code style="color:brown;">', $tr['line'], '</code>):',
            '<br/><code style="color:green;">', isset($tr['class']) ? $tr['class'] : '', isset($tr['type']) ? $tr['type'] : '', '<strong>', $tr['function'], '</strong>', '(', implode(', ', $strargs), ')', '</code>',
            '<div class="trace_code" style="font-family:mono;font-size:70%; padding:10px;background-color:#eee;display:none;"><small>Code-Context<br/></small><br/>', implode('<br/>', $lines), '</div>',
            '</li>';
        }
        echo '</ol>';
        
        
        if ($e->getPrevious()) {
            $this->_print_exception($e->getPrevious(), false);
        }
        
        if ($js) {
            echo '<script>'
            . 'var trs = document.getElementsByClassName("trace_step");'
            . 'for (var i=0; i < trs.length; i++) {'
            . ' (function(){'
            . '  var elem = trs.item(i);'
            . '  elem.onclick = function(e) {'
            . '   for(var n=0; n < elem.childNodes.length; n++) {'
            . '    var celem = elem.childNodes.item(n);'
            . '    if (celem.className == "trace_code") {'
            . '     celem.style.display= celem.style.display=="none" ? "block" : "none";'
            . '     break;'
            . '    }'
            . '   }'
            . '  }'
            . ' })();'
            . '}'
            . '</script>';
        }
    }
    
    /**
     * Get some lines of a source file.
     * 
     * @param string $sfile The sourcefile you want
     * @param int $line The line you want
     * @param int $plus Amount of lines to get before and after the line
     * @param bool $html Create HTML Code
     * @return array
     */
    protected function _get_source_lines(string $sfile, int $line, int $plus, bool $html = true): array {
        $lines = file($sfile);
        $from = $line - 1 - $plus;
        $to = $line - 1 + $plus;
        $ll = [];
        $c = count($lines);
        for ($i = $from; $i <= $to; $i++) {
            if ($i < 0 || $i >= $c)
                continue;
            if ($i + 1 == $line)
                $color = 'color:red;';
            else
                $color = '';
            $l = htmlspecialchars($lines[$i]);
            $l = str_replace(' ', '&nbsp;', $l);
            if ($html) {
                $ll[] = '<span style="' . $color . '"><strong>' . ($i + 1) . '</strong> ' . $l . '</span>';
            } else {
                $ll[] = $l;
            }
        }
        return $ll;
    }

}
