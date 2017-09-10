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
 * Der System-Controller stellt einige Informationen über die Seiten-Konfiguration zur Verfügung.
 * 
 * Er ist über /root/zur/app/::weppo/ erreichbar.
 * 
 * Unbedingt für ein Produktiv-System mit der Einstellung 'systemControllers' auf false deaktivieren.
 */
class SystemController extends \WEPPO\Controller\Controller {
    
    public function __construct(\WEPPO\Routing\Request &$request, \WEPPO\Routing\RequestHandler &$requestHandler) {
        parent::__construct($request, $requestHandler);
        
        if (!\WEPPO\Application\Settings::getInstance()->get('systemControllers')) {
            throw new \Exception("Bad Request");
        }
    }
    
    protected function action_index(array $pd) {
        echo '<h1>SystemController</h1><p>The following actions are defined by the WEPPO system controller:</p>';
        
        $methods = get_class_methods($this);
        
        $class = get_class();
        $this_method =  __METHOD__;
        
        echo '<ul>';
        foreach ($methods as $m) {
            if (strpos($m, 'action_') === 0 && ($class.'::'.$m) != $this_method) {
                $action = substr($m, 7);
                $info = $this->_get_action_info($action);
                $path = $this->getRequestHandler()->buildPath(['::weppo', $action], true);
                echo '<li>';
                echo '<a href="', $path,'">', htmlentities($info['title']), '</a>', '<br/><small><em>',htmlentities($info['description']),'</em></small>';
                echo '</li>';
            }
        }
        echo '<ul>';
        
        return true;
    }
    
    protected $testRequest = null;
    
    protected function action_controllers(array $pd) {
        $this->_print_header('Controllers');
        
        $structure = $this->getRequestHandler()->getStructure();
        if (!$structure->hasRootPage()) {
            echo '<p>no root page</p>';
            return true;
        }
        $controllers = $this->_collect_controllers($structure->getRootPage());
        
        echo '<ul>';
        
        $r = [];
        $p = [];
        $g = [];
        $this->testRequest = new \WEPPO\Routing\Request($r, $g, $p);
        
        $parent = null;
        foreach ($controllers as $classname => $uses) {
            $this->_print_controller_info($parent, $classname, $uses);
        }
        echo '</ul>';
        return true;
    }
    
    protected function _collect_controllers(\WEPPO\Routing\PageInterface &$root): array {
        $controllers = [];
        $c = $root->getControllerName();
        if (!empty($c)) {
            $controllers[$c] = [$root->getPatternPath()];
        }
        if ($root->hasChildren()) {
            $children = $root->getChildren();
            foreach ($children as &$ch) {
                $controllers = array_merge_recursive($controllers, $this->_collect_controllers($ch));
            }
        }
        return $controllers;
    }
    
    /**
     * 
     * @param type $parent
     * @param type $classname
     * @param type $uses for Controller: page paths | for Service: service mapping key
     */
    protected function _print_controller_info(&$parent, $classname, $uses) {
        if ($parent === null) {
            echo '<li style="margin-bottom:10px;">';
            echo '<big>';
            echo '<code style="color:blue;"><strong>', htmlentities($classname),'</strong></code>';
            echo '</big>';
            echo '<br/>';
        }
        
        if (is_array($uses)) {
            echo '<strong>Pages:</strong><ul>';
            foreach ($uses as $u) {
                echo '<li><code>', htmlentities($u),'</code></li>';
            }
            echo '</ul>';
        }

        if (class_exists($classname)) {

            $rClass = new \ReflectionClass($classname);
            
            if ($parent === null && $rClass->isSubclassOf('WEPPO\\Controller\\Controller')) {
                $c = new $classname($this->testRequest, $this->getRequestHandler());
            } else if ($parent !== null && $rClass->isSubclassOf('WEPPO\\Controller\\Service')) {
                $c = $parent->getService($uses);
            } else {
                throw new \Exception('Problem printing Controller Info: '.$classname.'; ', var_export($uses, true));
            }
            
            echo '<strong>Services:</strong><ul>';
            
            $services = $c->getServices();
            foreach ($services as $name => &$serv) {
                echo '<li>';
                echo '<code style="color:green;">/', htmlentities($name), '</code> → <code style="color:blue;">'. htmlentities($serv), '</code><br/>';
                
                if (is_string($serv)) $_serv = $serv;
                else $_serv = '\\'.get_class ($_serv);
                
                $this->_print_controller_info($c, $_serv, $name);
                #echo '</ul>';
                echo '</li>';
            }
            echo '</ul>';

            $action_methods = [];


            foreach ($rClass->getMethods() as $rMethod) {
                $mname = $rMethod->getName();
                $m = null;

                if ($mname == 'catchAll' || $mname == 'dispatch') {
                    #if ($rMethod->getDeclaringClass()->getName() == $rClass->getName()) {
                    if ($rMethod->getDeclaringClass()->getName() != 'WEPPO\Controller\Service') {
                        $m = '<strong><code>'.htmlentities($mname).'()</code></strong>';
                    }
                } else if (strpos($mname, 'action_') === 0) {
                    $m = '<code style="color:green;">/'.htmlentities(substr($mname, 7)).'</code>';
                }
                if ($m) {
                    if ($rMethod->getDeclaringClass()->getName() != $rClass->getName()) {
                        $m .= ' <small><em><code>'.$rMethod->getDeclaringClass()->getName().'</code></em></small>';
                    }
                    $action_methods[] =  $m;
                }
            }

            echo '<strong>Actions:</strong><ul>';
            foreach ($action_methods as $am) {
                //if ()
                echo '<li>', $am,'</li>';
            }
            echo '</ul>';
        }

        if ($parent === null) {
            echo '</li>';
        }
    }

    



















    protected function action_pagetree(array $pd) {
        $this->_print_header('Page Tree');
        
        $structure = $this->getRequestHandler()->getStructure();
        if (!$structure->hasRootPage()) {
            echo '<p>no root page</p>';
            return true;
        }
        
        echo '<code>/<strong>Matching_Pattern</strong></code> <small>(Page Name)</small><br/>';
        echo '<code>→ Controller Class Name</code><hr/>';
        
        $root = $structure->getRootPage();
        echo '<ul>';
        $this->_print_pages($root);
        echo '</ul>';
        return true;
    }
    
    protected function _print_pages(\WEPPO\Routing\PageInterface &$root) {
        echo '<li>';
        
        if (!$root->hasParent()) {
            echo '<em>Root Page</em>';
        } else {
            echo '<code>/<strong>', $root->getPattern(), '</strong></code> <small>(', $root->getPageName(), ')</small>';
        }
        
        echo '<br/>';
        
        $ctrl = $root->getControllerName();
        if (empty($ctrl)) {
            echo '→ <em>no controller</em>';
        } else {
            echo '<code>→ ', $ctrl, '</code>';
        }
        
        if ($root->hasChildren()) {
            echo '<ul>';
            $children = $root->getChildren();
            foreach ($children as &$child) {
                $this->_print_pages($child);
            }
            echo '</ul>';
        }
        echo '</li>';
    }
    
    
    protected function _get_action_info($name): array {
        static $info = [
            'pagetree'      => ['title'=>'Page Tree','description'=>'Overview of all defined Pages.'],
            'controllers'   => ['title'=>'Controllers','description'=>'Overview of all used Contrllers and services with all available actions.'],
        ];
        
        if (!isset($info[$name])) {
            return ['title'=>$name,'description'=>''];
        }
        
        return $info[$name];
    }
    
    protected function _print_header($title) {
        $path = $this->getRequestHandler()->buildPath(['::weppo'], true);
        echo '<h1><a href="', $path,'">SystemController</a></h1><h2>',$title,'</h2>';
    }
    
}