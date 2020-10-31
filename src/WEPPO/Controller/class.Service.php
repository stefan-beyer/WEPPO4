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
  protected $parentService = null;
  protected $calledAction = null;
  protected $serviceArrPath = [];
  protected $request;
  public static $dispatchMode = 'std';

  /**
   * 
   * The constructor of a service should not change any data or take persist actions (send emails).
   * If possible do nothing that is dependend on the request.
   * Those things should be done in an overwritten dispatch() method.
   * 
   * @param WEPPO::Controller::Service $parent
   */
  public function __construct(/* Service || null */ &$parent, \WEPPO\Routing\Request &$request) {
    $this->parentService = $parent;
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
  public function &getService(string $name, bool $create = true): Service {

    # Is there a Service for that name?
    if (!isset($this->services[$name])) {
      throw new \Exception('Service Not Found');
    }

    # A string will be treated as classname: an service object will be created and stored.
    if (is_string($this->services[$name])) {
      if ($create) {
        $classname = $this->services[$name];
        if (!class_exists($classname)) {
          throw new \Exception('service "' . $classname . '" not found');
        }
        # maybe not needed to store the object ...
        $this->services[$name] = new $classname($this, $this->getRequest());
      } else {
        throw new \Exception('Service Not Found');
      }
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
  public function dispatch(string $action, array $arrPath): bool {
    logToConsole(json_encode([static::class, $action]));

    $this->calledAction = $action;

    try {
      $service = $this->getService($action);
    } catch (\Exception $e) {
      $service = null;
    }
    if ($service) {
      logToConsole(json_encode([static::class, $action, 'try service']));
      if (self::$dispatchMode === 'v2') {
        //$action = 'index';
        //$nextAction = array_shift($arrPath);
        //try {
        //    $nextService = $service->getService($action);
        //} catch (\Exception $e) {
        //    $nextService = null;
        // }
      }

      $service->serviceArrPath = $this->serviceArrPath;
      $service->serviceArrPath[] = $action;

      if (self::$dispatchMode === 'std') {
        $service->serviceArrPath = $this->serviceArrPath;
        $service->serviceArrPath[] = $action;
        $ret = $service->dispatch($action, $arrPath);
        //logToConsole(json_encode([static::class, $action, 'returned'=>$ret]));
      } else if (self::$dispatchMode === 'v2') {

        $oldAction = $action;
        $oldPath = array_slice($arrPath, 0);
        $action = array_shift($arrPath);
        if (!$action) {
          $action = 'index';
        }
        $ret = $service->dispatch($action, $arrPath);
        //logToConsole(json_encode(['returend from: ', get_class($service), 'dispatch', $action]));
        if (!$ret) {
          $arrPath = $oldPath;
          $action = 'index';
          $ret = $service->dispatch($action, $arrPath);
        }
        //logToConsole(json_encode([$action, $arrPath]));
      } else {
        $ret = false;
      }

      if ($ret) {
        return true;
      }
    }
    

    $method = 'action_' . $action;
    //logToConsole(json_encode([static::class, $method]));

    if (method_exists($this, $method)) {
      //logToConsole(json_encode([static::class, $action, 'try '.$method]));
      $return = $this->{$method}($arrPath);
      if (!is_bool($return)) {
        throw new \ErrorException('Action method ' . $action . ' not returning boolean.');
      }
      if ($return) {
        return true;
      }
    }

    
    if (self::$dispatchMode === 'v2') {
      return false;
    }
    
    
    //logToConsole(json_encode([static::class, $action, 'try catchAll']));
    $ret = $this->catchAll($action, $arrPath);
    if ($ret) {
      return $ret;
    }
    
    if ($action !== 'index' && method_exists($this, 'action_index')) {
      //logToConsole(json_encode([static::class, $action, 'try action_index explicit']));
      array_unshift($arrPath, $action);
      $return = $this->action_index($arrPath);
      if (!is_bool($return)) {
        throw new \ErrorException('Action method ' . 'action_index' . ' not returning boolean.');
      }
      if ($return) {
        return true;
      }
      array_shift($arrPath);
    }
    
    return false;

  }

  public function catchAll(string $action, array $arrPath): bool {
    /*$e = new \WEPPO\Routing\RequestException(\WEPPO\Routing\RequestException::ACTION_NOT_HANDLED);
    $e->setRequest($this->getRequest());
    $e->setInfo('Action \'' . $action . '\'');
    throw $e;
    */
    return false;
  }

  public function &getRootController(): Controller {
    if ($this->parentService !== null) {
      return $this->parentService->getRootController();
    }
    return $this;
  }

  public function &getParentService(): Service {
    return $this->parentService;
  }

  public function hasParentService(): bool {
    return !!$this->parentService;
  }

  public function &getPage(): \WEPPO\Routing\PageInterface {
    return $this->getRequest()->getPage();
  }

  public function &getRequest(): \WEPPO\Routing\Request {
    return $this->request;
  }

}
