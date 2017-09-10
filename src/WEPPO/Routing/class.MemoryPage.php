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
 * To define an in-memory page structure.
 * 
 * Method chaining can be used for the setters.
 */
class MemoryPage implements PageInterface {
    use PageImplementation;
    
    protected static $autoId = 0;
    
    public function __construct() {
        # automatische eindeutige namen vergeben: p_1, p_2, ...
        $this->Page_PageName = 'p_'.(++self::$autoId);
    }
    
    // nothing to do
    protected function loadConfig() {
        return;
    }

    public function getChildren(): array {
        return $this->Page_Children;
    }
    
    public function hasChildren(): bool {
        return count($this->getChildren()) > 0;
    }

    public function getControllerName(): string {
        return $this->Page_ControllerName;
    }

    public function &getParent() : PageInterface {
        if (is_null($this->Page_Parent)) {
            throw new \Exception('MemoryPage::getParent() can not be called without a Parent. Call hasParent() first.');
        }
        return $this->Page_Parent;
    }
    
    public function hasParent() : bool {
        return !is_null($this->Page_Parent);
    }

    public function getPattern(): string {
        return $this->Page_Pattern;
    }
    
    public function getPageName(): string {
        return $this->Page_PageName;
    }
    
    public function getMatchMap(): array {
        return $this->Page_MatchMap;
    }
    
    
    
    public function &setPageName($n) {
        $this->Page_PageName = $n;
        return $this;
    }
    
    public function &setMatchMap($mm) {
        $this->Page_MatchMap = $mm;
        return $this;
    }
    
    public function &setPattern($p) : MemoryPage {
        $this->Page_Pattern = $p;
        return $this;
    }
    
    public function &setControllerName($cd) : MemoryPage {
        $this->Page_ControllerName = $cd;
        return $this;
    }
    
    public function &addChild($ch) : MemoryPage {
        if (!is_array($this->Page_Children)) {
            $this->Page_Children = [];
        }
        $this->Page_Children[$ch->getPattern()] = $ch;
        $ch->setParent($this);
        return $this;
    }
    
    public function &addTo(MemoryPage &$parent) {
        $parent->addChild($this);
        return $this;
    }

    protected function setParent(&$p) { // nicht von außen!
        $this->Page_Parent = $p;
    }


}
