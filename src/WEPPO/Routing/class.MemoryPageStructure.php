<?php
/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing;

class MemoryPageStructure extends PageStructure {
    
    
    public function setRootPage(&$rp) {
        $this->rootPage = $rp;
    }

        // will not be called, unless root was not set
    protected function load_root_page() {
        return null;
    }

}