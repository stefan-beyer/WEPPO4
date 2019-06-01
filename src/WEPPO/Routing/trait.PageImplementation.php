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
 * A Mixin for implementing parts of the PageInterface
 */
trait PageImplementation {

    protected $Page_Children = [];
    protected $Page_Parent = null;
    protected $Page_Matches = [];
    protected $Page_MatchMap = [];
    protected $Page_Pattern = '';
    protected $Page_ControllerName = '';
    protected $Page_Config = [];
    protected $Page_PageName = '';

    abstract public function getChildren(): array;

    abstract public function &getParent() : PageInterface;
    
    abstract public function hasParent() : bool;

    abstract public function getPattern(): string;
    
    abstract public function getPageName(): string;

    abstract public function getControllerName(): string;

    abstract public function getMatchMap(): array;
    
    abstract public function canHandleSubpath() : bool;

    /**
     * Should load the config into $this->Page_Config (not return it)
     */
    abstract protected function loadConfig();

    public function &getAllConfig(): array {
        $this->loadConfig();
        return $this->Page_Config;
    }
    
    public function getConfig($key, $default = null, $inherit = true) {
        $this->loadConfig();
        
        if (isset($this->Page_Config[$key])) {
            return $this->Page_Config[$key];
        }

        if (!$inherit) {
            return $default;
        }
        
        if ($this->hasParent()) {
            $p = &$this->getParent();
            return $p->getConfig($key, $default, $inherit);
        }
        return $default;
    }
    
    
    
    public function getConfigsWithPrefix($prefix, $inherit = true) {
        // todo inherit
        $this->loadConfig();
        $result = [];
        foreach ($this->Page_Config as $k=>&$v) {
            if (preg_match('#^'.preg_quote($prefix, '#').'(.*)$#', $k, $matches)) {
                $key = $matches[1];
                $result[$key] = $v;
            }
        }
        if ($inherit) {
            if ($this->hasParent()) {
                $p = &$this->getParent();
                $parentdata = $p->getConfigsWithPrefix($prefix, true);
                foreach ($parentdata as $k=>&$v) {
                    if (array_key_exists($k, $result)) continue;
                    $result[$k] = $v;
                }
            }
        }
        return $result;
        /*
        \WEPPO\Routing\DB\Config::where('page_id', $page->id);
        \WEPPO\Routing\DB\Config::where('`key`', 'grid2.contentMapping.%', 'LIKE');
        $mappingConfigs = \WEPPO\Routing\DB\Config::get();
        $result = [];
        foreach ($mappingConfigs as &$mc) {
            $key = preg_replace('#^'.preg_quote('grid2.contentMapping.', '#').'#', '', $mc->key);
            $result[$key] = $mc->value;
        }
        o($result);
         * 
         */
    }
    
    
    
    

    public function hasConfig($key): bool {
        $this->loadConfig();

        return (isset($this->Page_Config[$key]));
    }

    public function &setConfig($key, $value) {
        //$this->loadConfig();
        if (!is_array($this->Page_Config)) {
            $this->Page_Config = [];
        }
        
        # Should be an array
        if (substr($key, -2) === '[]') {
            $key = substr($key, 0, -2);
            
            if (!isset($this->Page_Config[$key]) || !is_array($this->Page_Config[$key])) {
                $this->Page_Config[$key] = [];
            #} else if (!is_array($this->Page_Config[$key])) {
            #    $this->Page_Config[$key] = [$this->Page_Config[$key]];
            }
            $this->Page_Config[$key][] = $value;
            return $this;
        }

        $this->Page_Config[$key] = $value;
        return $this;
    }

    
    protected function _match_exact($tid) : bool {
        $pattern = $this->getPattern();
        if ($pattern === $tid) {
            $this->Page_Matches = array($pattern);
            return true;
        }
        return false;
    }

    /**
     * Vereinfachte Muster
     * 
     * # steht für eine Zahl (beliebig viele Ziffern)
     * * steht für eine Zeichenkette (beliebig viele Zeichen)
     * Mit #? bzw. *? können die entsprechenden Teile auch leer sein.
     */
    protected function _match_simple($tid) : bool {
        $pattern = $this->getPattern();
        /**
         * # 		-> #
         * ## 		-> *
         * ### 		-> *?
         * #### 	-> #?
         */
        
        # damit wir keine probleme mit preg_quote bekommen
        # werden alle codes mit einer anderen anzahl von # kodiert
        $pattern = str_replace('*?', '#1#', $pattern);
        $pattern = str_replace('#?', '#2#', $pattern);
        $pattern = str_replace('*', '#3#', $pattern);
        #$pattern = str_replace('|', '#4#', $pattern);

        $pattern = preg_quote($pattern, '`');

        //$pattern = str_replace('.', '\\.', $pattern);
        # reihenfilge wichtig!
        $pattern = str_replace('#2#', '(\\d*)', $pattern);
        $pattern = str_replace('#1#', '(.*)', $pattern);
        $pattern = str_replace('#3#', '(.+)', $pattern);
        #$pattern = str_replace('#4#', '|', $pattern);
        
        $pattern = str_replace('#', '(\\d+)', $pattern);
        
        #echo $pattern, ' | ';
        
        return $this->_match_regex($tid, '`^' . $pattern . '$`u');
    }

    protected function _match_regex($tid, $regex) : bool {
        $matches = [];
        #o($regex);
        #o($tid);
        $a = \preg_match($regex, $tid, $matches);
        #o($matches);
        if ($a) {
            $this->Page_Matches = $matches;
            return true;
        }
        return false;
    }
    
    public function isMatch($tid, $mode) : bool {
        
        if ($mode === PageStructure::MATCH_MODE_EXACT) {
            return $this->_match_exact($tid);
        }

        if ($mode == PageStructure::MATCH_MODE_SIMPLE) {
            return $this->_match_simple($tid);
        }

        if ($mode == PageStructure::MATCH_MODE_REGEX) {
            return $this->_match_regex($tid, '`^' . $this->getPattern() . '$`u');
        }
        

        return false;
    }
    
    //private
    public function map_matches() {
        $mm = $this->getMatchMap();
        
        if (!isset($mm[0]) || $mm[0] !== 'full') {
            array_unshift($mm, 'full');
        }
        
        // mehr matches als keys
        // matches die zu viel sind abschneiden / ignorieren
        if (count($this->Page_Matches) > count($mm)) {
            $this->Page_Matches = array_slice($this->Page_Matches, 0, count($mm), true);
        } // TODO: test
        
        // mehr keys als matches
        // matches mit null auffüllen
        else if (count($this->Page_Matches) < count($mm)) {
            $this->Page_Matches = array_pad($this->Page_Matches, count($mm), null);
        } // TODO: test
        
        if (count($this->Page_Matches) === count($mm)) {
            $this->Page_Matches = array_combine($mm, $this->Page_Matches);
            return;
        }
    }

    /**
     * Eine Pfad zu dieser Seite als Array erzeugen.
     * 
     * @return string[]
     */
    public function getArrPath() : array {
        $v = [];
        return $this->_getArrPath($v);
    }
    
    /**
     * Intern
     * @param array $v wird intern verwendet, um Rekursion zu erkennen
     */
    private function _getArrPath(array &$v) : array {
        if (\in_array($this->getPageName(), $v)) {
            throw new \Exception('Circular Path');
        }
        $v[] = $this->getPageName();

        $url = array();
        
        if ($this->hasParent()) {
            $parent = $this->getParent();
            $url = \array_merge($url, $parent->_getArrPath($v));
        }
        //else $url = '/';
        $fm = $this->getMatch('full');
        
        if ($fm) {
            $url[] = $fm;
        }
        return $url;
    }
    
    /**
     * Eine Pfad zu dieser Seite als Array erzeugen.
     * 
     * @return string[]
     */
    public function getArrPatternPath() : array {
        $url = [];
        
        if ($this->hasParent()) {
            $parent = $this->getParent();
            $url = \array_merge($url, $parent->getArrPatternPath());
        }
        $url[] = $this->getPattern();
        return $url;
    }
    
    /**
     * Eine Pfad zu dieser Seite als Array erzeugen.
     * 
     * @return string[]
     */
    public function getArrNamePath() : array {
        $url = [];
        
        if ($this->hasParent()) {
            $parent = $this->getParent();
            $url = \array_merge($url, $parent->getArrNamePath());
        }
        $url[] = $this->getPageName();
        return $url;
    }
    
    public function getPath(bool $full = true) : string {
        return \WEPPO\Application\Context::getInstance()->getRequestHandler()->buildPath($this->getArrPath(), $full);
    }
    
    public function getPatternPath() : string {
        return \WEPPO\Application\Context::getInstance()->getRequestHandler()->buildPath($this->getArrPatternPath(), false);
    }
    
    public function getNamePath() : string {
        return implode('/', $this->getArrNamePath());
    }

    /**
     */
    public function getMatch($key): string {
        $m = $this->getMatches();
        if (isset($m[$key])) {
            return $m[$key];
        }
        return '';
    }

    /**
     * Matches abfragen
     * 
     * Die Elemente des Arrays werden, wenn nicht schon geschehen, mit der matchmap benannt.
     * 
     * @return array
     */
    public function getMatches() : array {
        if (!isset($this->Page_Matches['full'])) {
            $this->map_matches();
        }
        return $this->Page_Matches;
    }

}
