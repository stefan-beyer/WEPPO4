<?php
/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing\DB;

class Config extends \WEPPO\Ressource\TableRecord   {
    public static function getTablename() {
        return 'pageconfig';
    }
    
}

