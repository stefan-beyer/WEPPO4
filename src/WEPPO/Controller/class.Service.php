<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Controller;

/**
 * A Service is the main action dispatcher.
 * A service can have child services attached, where you can outsource some of the work.
 * A service can not be used as a Controller, but as part of a controller.
 * And it is the baseclass of a Controller.
 */
class Service {
    /**
     * Array of sub services. (key=>classname).
     * 
     * This will hold only classnames associated with a key
     * until the service is called: the created object will replace its
     * classname in the array.
     * The advantage of this is, that no service object will be created until its used.
     */
    protected $services = [];
    
    protected $parentController = null;

    protected $calledAction = null;
    
    protected $serviceArrPath = [];
    
    protected $request;


    /**
     * 
     * The constructor of a service should not change any data or take persist actions (send emails).
     * If possible do nothing that is dependend on the request.
     * Those things should be done in an overwritten dispatch() method.
     * 
     * @param WEPPO::Controller::Service $parent
     */
    public function __construct(/*Service || null*/ &$parent, \WEPPO\Routing\Request &$request) {
        $this->parentController = $parent;
        $this->request = $request;
    }

    /**
     * Register a Service for some key aktions.
     * 
     * @param array $serves Array with keys that will be connected to the service.
     * @param string $classname Classname of a subclass of WEPPO::Controller::Service
     */
    protected function registerService(array $serves, string $classname) {
        foreach ($serves as $s) {
            $this->services[$s] = $classname;
        }
    }
    
    /**
     * Returns the array of registered Services
     * The values can be strings or Service objects.
     * 
     * @return array
     */
    public function &getServices(): array {
        return $this->services;
    }


    /**
     * Get a Service Object.
     * Creates it, if not yet created.
     * 
     * @param string $name
     * @return WEPPO::Controller::Service
     * @throws Exception
     */
    protected function &getService(string $name) : Service {
        
        # Is there a Service for that name?
        if (!isset($this->services[$name])) {
            throw new \Exception('Service Not Found');
        }
        
        # A string will be treated as classname: an service object will be created and stored.
        if (is_string($this->services[$name])) {
            $classname = $this->services[$name];
            # maybe not needed to store the object ...
            $this->services[$name] = new $classname($this, $this->getRequest());
        }
        
        $service = $this->services[$name];
        return $service;
    }
    
    /**
     * Dispatches the action.
     * 
     * 1. If there is a child service for the action, call the dispatcher of that service.
     * 2. If there is an action method (function action_[actionname]($pathdata)), call it.
     * 3. call catchAll method (which will throw an exception by default.)
     * 
     * @param string $action
     * @param array $arrPath The Rest of the request path array
     * @return bool true if the action got handeled
     */
    public function dispatch(string $action, array $arrPath) : bool {
        
        $this->calledAction = $action;
        
        try {
            $service = $this->getService($action);
        } catch (\Exception $e) {
            $service = null;
        }
        if ($service) {
            #$action2 = array_shift($arrPath);
            #if ($action2 === null) {
            #    $action2 = '';
            #}
            $service->serviceArrPath = $this->serviceArrPath;
            $service->serviceArrPath[] = $action;
            $ret = $service->dispatch($action, $arrPath);
            if ($ret) {
                return true;
            }
        }
        
        $method = 'action_'.$action;
        if (method_exists($this, $method)) {
            $ret = $this->{$method}($arrPath);
            if ($ret) {
                return true;
            }
        }
        
        return $this->catchAll($action, $arrPath);
        
    }
    
    public function catchAll(string $action, array $arrPath) : bool {
        $e = new \WEPPO\Routing\RequestException(\WEPPO\Routing\RequestException::ACTION_NOT_HANDLED);
        $e->setRequest($this->getRequest());
        $e->setInfo('Action \''.$action.'\'');
        throw $e;
        // return false
    }
    
    public function &getRootController() : Controller {
        if ($this->parentController !== null) {
            return $this->parentController->getRootController();
        }
        return $this;
    }
    
    public function &getPage() : \WEPPO\Routing\PageInterface {
        return $this->getRequest()->getPage();
    }
    
    public function &getRequest() : \WEPPO\Routing\Request {
        return $this->request;
    }
}


