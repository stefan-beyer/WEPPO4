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
 * The first Level Controller for a page.
 * 
 * Subclasses of this classes will be used to configure th pages.
 * 
 */
class Controller extends Service {
    
    protected $requestHandler;
    
    /**
     * wird der Controller ohne Action aufgerufen wird diese Action ausgeführt:
     */
    protected $indexAction = 'index';
    
    //protected $rootAction = '';

    /**
     * Ein Controller soll im Konstruktor keine Änderungen an Daten vornehmen oder sonstige persistente Aktionen (Emails versenden z.B.).
     * Und möglichst keine von Request abhängigen aktionen...
     * Sowas sollte erst z.B. in einer überschriebenen dispatch()-Methode passieren.
     * 
     * @param WEPPO::Routing::Request $request
     * @param WEPPO::Routing::RequestHandler $requestHandler
     */
    public function __construct(\WEPPO\Routing\Request &$request, \WEPPO\Routing\RequestHandler &$requestHandler) {
        $parent = null;
        parent::__construct($parent, $request);
        
        $this->requestHandler = $requestHandler;
        $this->request->setController($this);
    }
    
    public function run() {
        
        # Wir müssen den Teil des Pfades ermitteln, der über den Pfad der Seite
        # hinausgeht.
        
        $requestPath = $this->getRequest()->getArrPath();
        $pagePath    = $this->getPage()->getArrPath();
        
        $l = \count($pagePath);
        $restPath = \array_splice($requestPath, $l);
        
        $action = array_shift($restPath);
        
        if (empty($action)) {
            $action = $this->indexAction;
        }
        $this->dispatch($action, $restPath);
    }
    
    public function &getRequestHandler() : \WEPPO\Routing\RequestHandler {
        return $this->requestHandler;
    }
    
    /**
     * Sammelt die Named Matches der Seite und seiner Eltern in einem Array.
     * @return array
     */
    public function collectMaches() : array {
        $matches = [];
        for ($p = $this->getPage(); $p->hasParent(); $p=$p->getParent()) {
            $m = $p->getMatches();
            unset($m['full']); // oder full aneinander hängen
            $matches = array_merge($matches, $m);
        }
        return $matches;
    }
    
    /**
     * Parts are an other possible way of communication between controller and template.
     * 
     * In contrast to params (via @see WEPPO::Presentation::TemplateBase::set() and @see WEPPO::Presentation::TemplateBase::get()),
     * parts are generated on demand.
     * 
     * To give a controller the abillity to generate parts, you just have to 
     * write methods part_foo() or overwrite getParts().
     * 
     * @param string $name
     * @return string
     */
    public function getPart(string $name) : string {
        $methodName = 'part_'.$name;
        if (!method_exists($this, $methodName)) {
            return '';
        }
        return $this->{$methodName}();
    }
    
    
    public function text(string $key, $context = null): string {
        return $key;
    }
    
}