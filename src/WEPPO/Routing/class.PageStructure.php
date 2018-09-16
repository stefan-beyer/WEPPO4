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
 * Abstract Basclass for the Page Structure handling.
 * 
 * It manages a root page and different maching modes.
 *  - MATCH_MODE_EXACT: only exact names will match
 *  - MATCH_MODE_SIMPLE: some simple patterns can be used
 *  - MATCH_MODE_REGEX: complex regex pattern can be used
 * 
 * It finds a matching Page object for a array path.
 * If some pattern matching is used, it stores the matches in the page object.
 */
abstract class PageStructure {

    const MATCH_MODE_EXACT = 0;
    const MATCH_MODE_SIMPLE = 1;
    const MATCH_MODE_REGEX = 2;

    protected $matchMode;
    protected $rootPage = null;

    
    public function __construct($patternMatching = self::MATCH_MODE_EXACT) {
        $this->matchMode = $patternMatching;
    }

    public function &findPage(array $pathArr, $exact) {
        // TODO kann man exact auch in den page-obekten angeben um das von der seite abhängig zu machen?
        
        if (!is_array($pathArr)) {
            throw new \Exception('PageStructure::findPage $pathArr is no Array');
        }
        
        if (!count($pathArr)) {
            throw new RequestException(RequestException::PAGE_NOT_FOUND);
        }

        if (!$this->hasRootPage()) {
            throw new RequestException(RequestException::ROOT_PAGE_NOT_SET);
        }
        $last_page_hit = $this->getRootPage();
        
        // Für Startseite die Sache etwas abkürzen
        if (count($pathArr) == 1 && !$pathArr[0]) {
            return $last_page_hit;
        }
        


        $treffer = false;
        
        
        if ($pathArr[0] == '::weppo') {
            $sysC = \WEPPO\Application\Settings::getInstance()->get('systemControllers');
            if ($sysC === true) {
                if (!isset($last_page_hit->getChildren()['::weppo'])) {
                    $page = new MemoryPage();
                    $page->setControllerName('\\WEPPO\\Controller\\SystemController');
                    $page->setPageName('::weppo');
                    $page->setPattern('::weppo');
                    $page->addTo($last_page_hit);
                }
            }
        }
        
        

        # TODO bei MATCH_MODE_EXACT müsste dass ggf über array-key schneller gehen
        
        foreach ($pathArr as $k) {
            if (!$k) {
                continue;
            }
            
            $k = urldecode($k);
            

            $children = $last_page_hit->getChildren();

            if (!$children) {
                if ($exact) {
                    throw new RequestException(RequestException::PAGE_NOT_FOUND);
                }
                break;
            }

            foreach ($children as &$cp) {
                if ($cp->isMatch($k, $this->matchMode)) {
                    $last_page_hit = &$cp;
                    $treffer = true;
                    break;
                }
            }

            if (!$treffer) {
                if ($exact) {
                    throw new RequestException(RequestException::PAGE_NOT_FOUND);
                }
                break;
            }
        }
        
        return $last_page_hit;
    }

    public function hasRootPage() : bool {
        if ($this->rootPage === null) {
            $this->rootPage = $this->load_root_page();
        }
        return $this->rootPage !== null;
    }
    
    public function &getRootPage() : PageInterface {
        if ($this->rootPage === null) {
            $this->rootPage = $this->load_root_page();
        }
        if ($this->rootPage === null) {
            throw new \Exception('PageStructure::getRootPage() rootPage is Null. Call hasRootPage() first.');
        }
        return $this->rootPage;
    }
    
    /**
     * Should return the Root-Page-Object.
     */
    abstract protected function load_root_page();


}
