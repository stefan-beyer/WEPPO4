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
    
    // db string to value
    public function getValue() {
        $v = $this->value;
        switch ($this->type) {
            case 'json':
                $v = json_decode($v);
                break;
            case 'integer':
            case 'number':
            case 'int':
                $v = intval($v);
                break;
            case 'float':
            case 'double':
            case 'decimal':
                $v = floatval($v);
                break;
            case 'bool':
            case 'boolean':
                $v = $v === 'true';
                break;
            // TODO ...
        }
        return $v;
    }
    
    // db string to value
    public function setValue($v) {
        switch ($this->type) {
            case 'json':
                $v = json_encode($v);
                break;
            case 'integer':
            case 'number':
            case 'int':
            case 'float':
            case 'double':
            case 'decimal':
                $v = ''.$v;
                break;
            case 'bool':
            case 'boolean':
                $v = $v ? 'true' : 'false';
                break;
            // TODO ...
        }
        $this->value = $v;
    }
    
}

