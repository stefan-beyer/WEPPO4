<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing;

/**
 * Holds Information about a request.
 * 
 * It can manage its own GET and POST data with some automated type casting.
 */
class Request {

    protected $arrPath;
    protected $get;
    protected $post;
    
    /* @var $page PageInterface */
    protected $page = null;
    
    /** @var $controller \WEPPO\Controller\Controller */
    protected $controller = null;

    public function __construct($arrPath, &$get, &$post) {
        $this->arrPath = $arrPath;
        $this->get = $get;
        $this->post = $post;
    }

    public function getArrPath() {
        return $this->arrPath;
    }

    public function getPath(bool $full = true) : string {
        return \WEPPO\Application\Context::getInstance()->getRequestHandler()->buildPath($this->getArrPath(), $full);
    }

    public function setPage(&$page) {
        $this->page = $page;
    }

    public function &getPage() : PageInterface {
        if (is_null($this->page)) {
            throw new \Exception('Request::getPage() Page is Null - use hasPage() first.');
        }
        return $this->page;
    }
    
    public function hasPage() : bool {
        return !!$this->page;
    }

    public function setController(\WEPPO\Controller\Controller &$c) {
        $this->controller = $c;
    }

    public function &getController() : \WEPPO\Controller\Controller {
        if (is_null($this->controller)) {
            throw new \Excpetion('Request::getController() Controller is Null - use hasController() first.');
        }
        return $this->controller;
    }
    
    public function hasController() : bool {
        return !!$this->controller;
    }
    
    public function isPost(): bool {
        return !empty($this->post);
    }
    public function &getPost(): array {
        return $this->post;
    }
    
    public function getRawPostData(): string {
        return file_get_contents("php://input");
    }

    /**
     * POST-Variable abrufen
     * 
     * @param string $key Name der POST-Variablen
     * @param mixed $default Default-Wert, falls nicht vorhanden
     * @param null|string|callable $cast Umwandlung (Schlüsselwort int|float|bool oder Callback-Funktion)
     * 
     * @return mixed
     */
    public function p($key, $default = '', $cast = null) {
        return $this->g($key, $default, $cast, 'POST');
    }

    /**
     * GET-Variable abrufen
     * 
     * @param string $key Name der GET-Variablen
     * @param mixed $default Default-Wert, falls nicht vorhanden
     * @param null|string|callable $cast Umwandlung (Schlüsselwort int|float|bool oder Callback-Funktion)
     * @param string $src 'GET' | 'POST'
     * 
     * @return mixed
     */
    public function g($key, $default = '', $cast = null, $src = 'GET') {
        switch ($src) {
            case 'GET':
                $v = isset($this->get[$key]) ? $this->get[$key] : $default;
                break;
            case 'POST':
                $v = isset($this->post[$key]) ? $this->post[$key] : $default;
                break;
        }
        
        if ($cast) {
            if (is_callable($cast)) {
                $v = call_user_func($cast, $v);
            } else if ($v !== null) { # wenn z.B. null als default wert angegeben ist sollte das hier nicht verändert werden
                $v = $this->_basic_cast($cast, $v);
            }
        }
        return $v;
    }
    
    public function hasG($name) {
      return isset($this->get[$name]);
    }
    
    public function hasP($name) {
      return isset($this->post[$name]);
    }
    
    public function collectPostArray(array $cols): array {
        // TODO $cals evtl als assoc array mit cast infos...
        // TODO: was wenn arrays unterschiedlich lang?
        $result = [];
        foreach ($cols as $col=>$key) {
            $data = $this->p($col, []);
            foreach ($data as $k=>$v) {
                if (!array_key_exists($k, $result)) {
                    $result[$k] = [];
                }
                $result[$k][$key] = $v;
            }
        }
        foreach ($result as &$r) {
            foreach ($cols as $col) {
                if (!array_key_exists($col, $r)) return null;
            }
        }
        return $result;
    }
    
    protected function _basic_cast($cast, $v) {
        switch ($cast) {
            case 'int':
                return intval($v);
            case 'float':
                return floatval($v);
            case 'bool':
                return boolval($v);
        }
        return $v;
    }

}
