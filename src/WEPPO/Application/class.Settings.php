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
 * Manages globally available application settings.
 * 
 * It implements the singelton pattern so that there is only one settings object
 * throughout the application.
 * It initially loads a file called default.php in the WEPPO directory.
 * With loadSettings() you can load application specific settings and thereby
 * overwrite the defaults.
 */
class Settings {
    
    /* @var $instance \WEPPO\Application\Settings */
    protected static $instance = null;
    
    /* @var $settings array */
    protected $settings = [];


    /**
     * Protected Constructor.
     * Loads default settings from default.php in the WEPPO directory.
     */
    protected function __construct() {
        $dsf = WEPPO_ROOT . 'WEPPO/default.php';
        
        if (file_exists($dsf)) {
            $this->settings = (include $dsf);
        } else {
            $this->settings = [];
        }
    }
    
    /**
     * Merges new settings with the persisting ones.
     * 
     * @param array $newSettings
     */
    protected function mergeSettings(array $newSettings) {
        $this->settings = array_replace_recursive($this->settings, $newSettings);
    }
    
    
    /**
     * Get the Settings instance. Creates one if not created.
     * 
     * @return WEPPO::Application::Settings
     */
    static public function &getInstance() : Settings {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Loads a settings file and merges it with the persisting settings.
     * This way settings can be overwritten.
     * 
     * The Settings can be an array or a filename of a php file relative to the
     * APP_ROOT that will be included and should return an array with the settings.
     * 
     * @param mixed $settings array or file name
     * @throws Exception if settings could not be merged. eg. no array given or include file does not return an array.
     */
    public function loadSettings($settings) {
        if (!is_null($settings)) {
            if (is_string($settings)) {
                if (!is_readable($settings)) {
                    throw new \Exception('Settings::loadSettings() Settings not found: '.$settings);
                }
                $settings = (include $settings);
            }
            if (!is_array($settings)) {
                throw new \Exception('Settings::loadSettings() no valid settings.');
            }
            self::$instance->mergeSettings($settings);
        }
    }
    
    /**
     * Get the array of settings
     * @return array
     */
    public function &getSettings() : array {
        return $this->settings;
    }
    
    /**
     * Checks if a Setting is present.
     * 
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool {
        return isset($this->settings[$name]);
    }
    
    /**
     * Get a settings value or the default.
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function &get(string $name, $default = null) {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }
        return $default;
    }
    
    /**
     * Sets a Setting-
     * 
     * @param string $name
     * @param mixed $val
     */
    public function set(string $name, $val) {
        $this->settings[$name] = $val;
    }
    
    
}
