<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Application;


abstract class ApplicationBase {

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

    abstract protected function internalInit();

    abstract public function init();

    /**
     * This is the main call in a start script.
     * Calls init() and autoRequest()
     */
    abstract public function run();

    /**
     * Get the RequestHandler instance.
     * 
     * @return WEPPO::Routing::RequestHandler
     */
    public function &getRequestHandler(): \WEPPO\Routing\RequestHandler {
        return $this->requestHandler;
    }


}
