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
 * With its singelton pattern, the context gives "global" access to the settings
 * and application object.
 */
class Context {
    
    /* @var $instance \WEPPO\Application\Context */
    protected static $instance = null;
    
    /* @var $settings \WEPPO\Application\Settings */
    protected $settings = null;
    
    /* @var $application \WEPPO\Application\Application */
    protected $application = null;

    protected $errorHandler = null;


    /**
     * Protected Construktor
     * 
     * Error-Handling will be started. Settings should be initialized.
     */
    protected function __construct() {
        $this->settings = Settings::getInstance();
        $this->errorHandler = new ErrorHandler();
        $this->errorHandler->start();
    }
    
    
    /**
     * Get the context instance. Creates it, if necessary.
     * 
     * @return WEPPO::Application::Context
     */
    static public function &getInstance() : Context {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    public function &getErrorHandler(): ErrorHandler {
        return $this->errorHandler;
    }

        /**
     * Set the application reference.
     * 
     * @param WEPPO::Application::Application $app
     */
    public function setApplication(ApplicationBase &$app) {
        $this->application = $app;
    }
    
    /**
     * Get the application reference. 
     * 
     * @return WEPPO::Application::Application
     */
    public function &getApplication() : ApplicationBase {
        return $this->application;
    }
    
    /**
     * Convenience method to get the current request handler.
     * 
     * @return WEPPO::Routing::RequestHandler
     */
    public function &getRequestHandler() : \WEPPO\Routing\RequestHandler {
        return $this->getApplication()->getRequestHandler();
    }
    
    /**
     * Get the settings reference. 
     * 
     * @return WEPPO::Application::Settings
     */
    public function &getSettings() : Settings {
        return $this->settings;
    }
}




