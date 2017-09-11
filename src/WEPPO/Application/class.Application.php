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
        
        $this->g->a();
        
        #try {
            $this->autoRequest();
        #} catch (\Exception $e) {
            
            #Context::getInstance()->getErrorHandler()->handleException($e);
            
            #if (Settings::getInstance()->get('printExceptions')) {
            #    $this->_print_exception($e);
            #}
            #if (Settings::getInstance()->get('throwExceptions')) {
            #    throw $e;
            #}
        #}
    }

    /**
     * Get the RequestHandler instance.
     * 
     * @return WEPPO::Routing::RequestHandler
     */
    public function &getRequestHandler(): \WEPPO\Routing\RequestHandler {
        return $this->requestHandler;
    }


}
