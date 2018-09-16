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
class Application extends ApplicationBase {

    protected function internalInit() {
        
    }

    public function init() {
        
    }

    protected function doRequest(string $path, &$g, &$p) {
        # path will be prepared and converted into an array
        $arrPath = $this->requestHandler->preparePath($path);

        # Request erzeugen
        $request = new \WEPPO\Routing\Request($arrPath, $g, $p);

        # execute request and catch redirections
        try {
            
            $ret = $this->requestHandler->processRequest($request);
            
        } catch (\WEPPO\Routing\RedirectException $e) { # other exceptions will be forwarded...
            $url = $e->getUrl();
            $mode = $e->getMode();
            switch ($mode) {
            case \WEPPO\Routing\REDIRECT_EXTERN:
                header('Location: ' . \WEPPO\Routing\Url::getAbsUrl($url), true, $e->getCode());
                break;
            case \WEPPO\Routing\REDIRECT_INTERN:
                if ($url !== $path) {
                    $this->doRequest($url, $g, $p);
                }
                break;
            }
            return;
        }

        if ($ret !== \WEPPO\Routing\RequestHandler::OK) {
            throw new \Exception("Die Anfrage kann nicht bearbeitet werden: RequestHandler::processRequest returned FAIL");
        }
    }
    /**
     * Starts a normal request handling with the $_SERVER['REQUEST_URI']
     * and $_GET and $_POST vars.
     */
    public function autoRequest() {

        $this->doRequest($_SERVER['REQUEST_URI'], $_GET, $_POST);
        
    }

    /**
     * This is the main call in a start script.
     * Calls init() and autoRequest()
     */
    public function run() {
        $this->init();
        $this->autoRequest();
    }


}
