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
 * Base Class for Templates of all sorts.
 * 
 * Defines two ways of communiaction between template and controller:
 *  - Params: params will be created by the controller and privides via set().
 *    The template can use them via get(). The thing with params is:
 *    they will be created whether it's used or not.
 *  - Parts: parts work the other way around. They are requested by the template
 *    via getPart(). This call will be forwarded to the controller in charge.
 *    So the content is generated on demand.
 *    
 */
abstract class TemplateBase {
    /* @var $controller \WEPPO\Controller\Controller */
    protected $controller = null;
    
    protected $params = [];
    
    # abgeleitete klasse braucht eigenen, öffentlichen Konstruktor!
    protected function __construct(\WEPPO\Controller\Controller &$controller) {
        $this->controller = $controller;
        $this->resetParams();
    }
    
    
    abstract public function isExisting() : bool;
    abstract public function getOutput(bool $trim = true) : string;
    
    public function &getController() : \WEPPO\Controller\Controller {
        return $this->controller;
    }
            
    /**
     * Setzt das gesamte Parameter-Array
     * 
     * @param array $params Parameter
     */
    public function setParams(array $params) {
            $this->params = $params;
    }
    /**
     * Die Template-Parameter zurücksetzen
     */
    public function resetParams() {
        $this->params = array();
    }

    /**
     * Template-Parameter abfragen
     * 
     * Wenn der Parameter nicht gesetzt ist und kein $default gesetzt ist, so wird '' zurück gegeben.
     * 
     * @param string $n Schlüssel
     * @param mixed $default Wert, der zurück kommt, wenn Parameter nicht gesetzt
     * @return string
     */
    function get($n, $default = null) {
        // TODO wenn nicht vorhanden, parent fragen!
        if (isset($this->params[$n])) {
            return $this->params[$n];
        }
        if ($default !== null) {
            return $default;
        }
        return '';
    }
    
    /**
     * Template-Parameter definiert?
     * 
     * @param string $n Schlüssel
     * @return boolean
     */
    function hasParam($n) {
        return isset($this->params[$n]);
    }
    
    /**
     * Einen Template-Parameter setzen
     * 
     * @param string $n Schlüssel
     * @param mixed $v Wert
     */
    function set($n, $v) {
        $this->params[$n] = &$v;
    }

    /**
     * Daten einem Template-Parameter hinzufügen
     * 
     * Dafür wird ggf. aus dem bisherigen Wert ein Array gemacht und der neue Wert wird 
     * dem Array hinzugefügt.
     * @param string $n Schlüssel des Template-Parameters
     * @param mixed $v Wert, der hinzugefügt wird
     * @param mixed $k Schlüssel für den Wert im (neuen) Array
     */
    function add($n, $v, $k = null) {
        if (isset($this->params[$n])) {
            if (!\is_array($this->params[$n])) {
                $this->params[$n] = array($this->params[$n]);
            }
        } else {
            $this->params[$n] = array();
        }
        if ($k !== null) {
            $this->params[$n][$k] = &$v;
        } else {
            $this->params[$n][] = &$v;
        }
    }
    
    /**
     * Startet das Aufzeichnen eines Parameters für dieses Template
     * 
     * Der Ausgabe-Buffer wird gestartet.
     * 
     * @param string $name Parameter-Name, wird hier nicht verwendet, ist nur zur Übersicht beim Aufruf 
     */
    public function startParam($name) {
        \ob_start();
    }

    /**
     * Beendet das Aufzeichnen eines Parameters und setzt den Parameter
     * 
     * Der Ausgabe-Buffer wird beendet.
     * 
     * @param string $name Parameter-Name
     * @param callable $callback Callback-Funktion, die vor dem setzen auf den Parameter-Wert angewendet wird. Signatur: string cb(string)
     */
    public function endParam($name, $callback = null) {
        $param = \ob_get_contents();
        \ob_end_clean();
        if ($callback !== null) {
            $param = call_user_func($callback, $param);
        }
        //return $param;
        $this->set($name, $param);
    }

    /**
     * Holt die im Array angegebenen Parameter aus einem anderen Template
     */
    public function setParamsFromTemplate(array $paramNames, TemplateBase &$template) {
        foreach ($paramNames as $k) {
            if ($template->hasParam($k)) {
                $this->set($k, $template->get($k));
            }
        }
    }
    
    /**
     * Get a part from the controller on demand.
     * 
     * @see WEPPO::Controller::getPart()
     * 
     * @param string $name
     * @return string
     */
    public function getPart(string $name): string {
        return $this->controller->getPart($name);
    }
    
    
    static protected function parseTemplateDescriptor(string $td): array {
        return explode(':', $td);
    }
    
    static function createTemplate($descriptor, $th): \WEPPO\Presentation\TemplateBase {
        $parsed = self::parseTemplateDescriptor($descriptor);
        if (count($parsed) !== 2) {
            throw new \Exception('Template descriptor \''.$descriptor.'\' is incorrect.');
        }
        list($classname, $nameParam) = $parsed;
        if (!class_exists($classname)) {
            throw new \Exception('Template class '.$classname.' not found.');
        }
        $template = new $classname($nameParam, $th);
        return $template;
    }
    
}
