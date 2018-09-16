<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Application;

class CLIApplication extends ApplicationBase {

    protected function internalInit() {
        
    }

    public function init() {
        
    }

    /**
     * This is the main call in a start script.
     * Calls init() and autoRequest()
     */
    public function run() {
        global $argv;
        
        $this->init();
        
        $script = array_shift($argv);
        $path = array_shift($argv);
        $path = $path;
        
        $g = [];
        $p = [];
        foreach ($argv as $arg) {
            $e = explode("=",$arg);
            if(count($e) === 2) $g[$e[0]] = $e[1];
            else $g[$e[0]] = true;
        }
        # path will be prepared and converted into an array
        $arrPath = $this->requestHandler->preparePath($path, false);

        # Request erzeugen
        $request = new \WEPPO\Routing\Request($arrPath, $g, $p);

        # execute request and catch redirections
        $ret = $this->requestHandler->processRequest($request);
    }


}
