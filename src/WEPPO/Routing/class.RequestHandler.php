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
 * Handels a Request.
 * 
 * Provides some helpers for path processing e.g. preparing a path by removing
 * the part that was used by a path gate and parse it into an array: preparePath().
 * And the other way arround: buildPath().
 * 
 */
class RequestHandler {

    const OK = 0;
    const FAIL = 1;
    #const ERROR_PAGE_NOT_FOUND = 1;
    #const ERROR_CONTROLLER_NOT_SET = 2;
    #const ERROR_CONTROLLER_NOT_FOUND = 3;
    #const ERROR_ACTION_NOT_FOUND = 4;

    /* @var $structure PageStructure */
    protected $structure;
    
    protected $requestStack = [];
    protected $errors = [];
    protected $gatePath = null;
    protected $exact = false;

    public function __construct(&$structure) {
        $this->structure = $structure;
        
        $this->exact = \WEPPO\Application\Settings::getInstance()->get('findPageExact', false);
        
        # Nur, wenn die Gate-Klasse geladen wurde kann ein Gate aufgerufen worden sein.
        # in diesem Fall holen wir uns den gate path.
        # das machen wir so, um die Klasse nicht unnötig laden zu müssen, wenn sie gar nicht verwendet wurde.
        # (Das Gate kann es allerdings nicht direkt hier setzen, weil der Requesthandler zu der Zeit noch nicht existiert.)
        if (class_exists('\\WEPPO\\Routing\\Gate', false)) {
            $this->gatePath = Gate::$gatePath;
        }
    }
    
    public function &getErrors() {
        return $this->errors;
    }
    
    /**
     * 
     */
    public function buildPath(array $arrPath, bool $full = true) {
        if ($full) {
            $arrPath = $this->prependGatePath($arrPath);
        }
        return '/'.implode('/', $arrPath);
    }

    
    public function removeGatePath($path) : array {
        $gatePath = $this->getGatePath();
        // Falls ein Path-Gate aufgerufen wurde, hier den Gate-Pfad entfernen # TODO: Test
        if ($gatePath === null) {
            return $path;
        }
        $l = \count($gatePath);
        return \array_splice($path, $l);
    }
    
    public function prependGatePath($path) : array {
        $gatePath = $this->getGatePath();
        if ($gatePath === null) {
            return $path;
        }
        return array_merge($gatePath, $path);
    }
    
    
    
    public function &getGatePath() {
        return $this->gatePath;
    }
    
    public function &getCurrentRequest() : Request {
        if (count($this->requestStack) === 0) {
            throw new \Exception('No Request in request stack.');
        }
        return $this->requestStack[count($this->requestStack) - 1];
    }
    
    public function &getOriginalRequest() : Request {
        if (count($this->requestStack) === 0) {
            throw new \Exception('No Request in request stack.');
        }
        return $this->requestStack[0];
    }
    
    public function &getRequestStack() : array {
        return $this->requestStack;
    }
            
    
    function processRequest(Request &$request) {
        array_push($this->requestStack, $request);
        
        # Hier wird das Page-Objekt aus der Struktur abgefragt
        $arrPath = $request->getArrPath();
        
        try {
            $page = $this->structure->findPage($arrPath, $this->exact);
        } catch (\WEPPO\Routing\RequestException $e) {
            # die Exception wird lediglich durch den Request ergänzt und wieder geworfen.
            $e->setRequest($request);
            throw $e;
        }

        if (!$page) {
            # sollte eigentlich nicht vorkommen:
            # Wenn findPage mit exact=true aufgerufen wird und das geht schief wird die Exception dort geworfen
            # Wenn keine Root-Seite vorhanden ist wird die Exception ebefalls in findPage geworfen
            # Also ist ansonsten sichergestellt, dass mindestens das Root Element geliefert wird.
            throw new RequestException(RequestException::PAGE_NOT_FOUND, $request);
        }
        
        $request->setPage($page);

        $classname = trim($request->getPage()->getControllerName());
        
        if (empty($classname)) {
            $e = new RequestException(RequestException::CONTROLLER_NOT_SET);
            $e->setRequest($request);
            throw $e;
        }
        
        if (class_exists($classname, true)) {
            /* @var $controller \WEPPO\Controller\Controller */
            $controller = new $classname($request, $this);
            $controller->run();
            return self::OK;
        }
        
        $e = new RequestException(RequestException::CONTROLLER_NOT_FOUND);
        $e->setRequest($request);
        $e->setInfo('Controller: '.$classname);
        throw $e;
    }
    
    /**
     * Einen internen Request absetzen und den erzeugten Inhalt als String zurückgeben lassen.
     * 
     * @return string Erzeugter Inhalt
     */
    function processRequestBuffered(Request &$request) {
        \ob_start();
        $ret = $this->processRequest($request);
        $content = \ob_get_contents();
        \ob_end_clean();
        return $content;
    }
    

    static public function path2Array(string $path): array {
        #   [/]kaksk/sdfsdf/a => ['kaksk', 'sdfsdf', 'a']
        #   [/]kaksk/sdfsdf/  => ['kaksk', 'sdfsdf', '']
        #   [/]kaksk/sdfsdf   => ['kaksk', 'sdfsdf']

        if (!$path) {
            return [''];
        }

        if ($path[0] == '/') {
            $path = \substr($path, 1);
        }

        return \explode('/', $path);
    }

    static public function array2Path(array $arrPath): string {
        return \implode('/', $arrPath);
    }
    

    public function &getStructure() : PageStructure {
        return $this->structure;
    }
    
    public function preparePath(string $path, $removeGatePath = true) : array {
        $path = \explode('?', $path);
        $path = isset($path[0]) ? $path[0] : '';
        $arrPath = self::path2Array($path);
        if ($removeGatePath) {
            $arrPath = $this->removeGatePath($arrPath);
        }
        if (empty($arrPath)) {
            $arrPath = [''];
        }
        return $arrPath;
    }
    
}
