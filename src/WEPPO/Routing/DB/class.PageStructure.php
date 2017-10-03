<?php
/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing\DB;

/**
 * 
 */
class PageStructure extends \WEPPO\Routing\PageStructure {
    
    protected $autoNameCounter = 0;


    public function __construct($patternMatching = \WEPPO\Routing\MATCH_MODE_EXACT) {
        parent::__construct($patternMatching);
    }
    
    
    
    
    
    // will not be called, unless root was not set
    protected function load_root_page() {
        Page::where('parent_id IS NULL');
        return Page::getOne();
    }

}