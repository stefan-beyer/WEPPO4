<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing;

interface PageInterface {

    public function getChildren() : array;
    
    public function hasChildren() : bool;

    public function &getParent() : PageInterface ;
    
    public function hasParent() : bool;

    public function getPattern() : string;

    public function getMatchMap() : array;
    
    public function getPageName() : string;

    public function getControllerName() : string;

    public function getConfig($key, $default = null, $inherit = true);
    
    public function &getAllConfig(): array;

    public function hasConfig($key) : bool;

    public function &setConfig($key, $value);

    public function isMatch($tid, $mode): bool;

    public function getArrPath(): array;
    
    public function getArrPatternPath(): array;
    
    public function getPatternPath(): string;
    
    public function getPath(bool $full = true) : string;

    public function getMatches() : array;

    public function getFullMatch(): string;
}
