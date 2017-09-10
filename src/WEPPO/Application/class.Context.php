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



    /**
     * Protected Construktor
     * 
     * Error-Handling will be started. Settings should be initialized.
     */
    protected function __construct() {
        $this->settings = Settings::getInstance();
        $this->startErrorHandling();
    }
    
    
    /**
     * Get the context instance. Creates it, if necessary.
     * 
     * @return \WEPPO\Application\Context
     */
    static public function &getInstance() : Context {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Starts global error handling as cofigured in the settings.
     * @TODO
     */
    protected function startErrorHandling() {
        //echo 'start error handling';
        # PHP-Fehlerbehandlung wird eingestellt.
        # in der konfiguration wird eingestellt, wie Fehler abgefangen werden sollen
        ini_set('display_errors', 'on');
        ini_set('display_startup_errors', 'on');
        ini_set('html_errors', 'on');
        ini_set('log_errors', 'on');
        error_reporting(-1);
        ini_set('error_log', './log/error.log');
    }
    
    /**
     * Set the application reference.
     * 
     * @param \WEPPO\Application\Application $app
     */
    public function setApplication(Application &$app) {
        $this->application = $app;
    }
    
    /**
     * Get the application reference. 
     * 
     * @return \WEPPO\Application\Application
     */
    public function &getApplication() : Application {
        return $this->application;
    }
    
    /**
     * Convenience method to get the current request handler.
     * 
     * @return \WEPPO\Routing\RequestHandler
     */
    public function &getRequestHandler() : \WEPPO\Routing\RequestHandler {
        return $this->getApplication()->getRequestHandler();
    }
    
    /**
     * Get the settings reference. 
     * 
     * @return \WEPPO\Application\Settings
     */
    public function &getSettings() : Settings {
        return $this->settings;
    }
}