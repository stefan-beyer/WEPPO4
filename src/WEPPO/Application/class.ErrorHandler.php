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
 * 
 * 
 */
class ErrorHandler {
    
    protected $plus_code_lines = 2;
    protected $html = true; // TODO


    public function start() {
        //echo 'start error handling';
        # PHP-Fehlerbehandlung wird eingestellt.
        # in der konfiguration wird eingestellt, wie Fehler abgefangen werden sollen
        ini_set('display_errors', 'on');
        ini_set('display_startup_errors', 'on');
        ini_set('html_errors', 'on');
        ini_set('log_errors', 'on');
        error_reporting(-1);
        ini_set('error_log', './log/error.log');
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    public function handleException($ex) {
        echo $this->getExceptionHTML($ex, true);
    }
    
    public function handleError($errno, $errstr = '', $errfile = '', $errline = '', $errcontext = null) {
        echo $this->getErrorHtml($errno, $errstr, $errfile, $errline, $errcontext);
    }
    
    
    protected function getErrorHtml($errno, $errstr, $errfile, $errline, $errcontext) {
        
        $errorType = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSING ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR'
        );
        
        $s = '';
        
        $s .= $this->getErrorLineHTML(
                (isset($errorType[$errno]) ? $errorType[$errno] : 'Unknown Error'),
                $errstr,
                $errfile,
                $errline
        );
        
        // array_reverse
        $trace = (debug_backtrace());
        $s .= $this->getTraceHTML($trace);

        $s .= $this->getJS();
        
        return $s;
    }
    
    protected function getErrorLineHTML($h1, $h2, $file, $line) {
        $s = '';
        $s .= '<h1>'.$h1.'</h1>';
        $s .= '<h2>»'. $h2. '«</h2>';
        
        $lines = $this->_get_source_lines($file, $line, $this->plus_code_lines);
        $s .= '<div style="margin-top:10px; cursor:pointer;" class="trace_step"><code style="color:blue;">'. $file. '</code> (<code style="color:brown;">'. $line. '</code>):'.
        '<div class="trace_code" style="font-family:mono;font-size:70%; padding:10px;background-color:#eee;display:none;"><small>Code-Context<br/></small><br/>'. implode('<br/>', $lines). '</div>'.
        '</div>';
        
        return $s;
    }
    
    protected function getTraceHTML(&$trace) {
        $s = '';
        
        $s .= '<h3>Trace</h3><ol>';
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
            $lines = $this->_get_source_lines($tr['file'], $tr['line'], $this->plus_code_lines);
            $s .= '<li style="margin-top:10px; cursor:pointer;" class="trace_step"><code style="color:blue;">'. $tr['file']. '</code> (<code style="color:brown;">'. $tr['line']. '</code>):'.
            '<br/><code style="color:green;">'. (isset($tr['class']) ? $tr['class'] : ''). (isset($tr['type']) ? $tr['type'] : ''). '<strong>'. $tr['function']. '</strong>'. '('. implode(', ', $strargs). ')'. '</code>'.
            '<div class="trace_code" style="font-family:mono;font-size:70%; padding:10px;background-color:#eee;display:none;"><small>Code-Context<br/></small><br/>'. implode('<br/>', $lines). '</div>'.
            '</li>';
        }
        $s .= '</ol>';
        
        return $s;
    }

    


    /**
     * Helper for pretty printing an exception.
     * 
     * @param Exception $e
     * @param bool $js
     */
    protected function getExceptionHTML(\Throwable $e, $js = true) {
        $s = '';
        
        $s .= $this->getErrorLineHTML(
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
        );
        
        


        $trace = $e->getTrace();
        $s .= $this->getTraceHTML($trace);
        
        if ($e->getPrevious()) {
            $s .= $this->getExceptionHTML($e->getPrevious(), false);
        }
        
        if ($js) {
            $s .= $this->getJS();
        }
        
        return $s;
    }
    
    protected function getJS() {
        return '<script>'
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