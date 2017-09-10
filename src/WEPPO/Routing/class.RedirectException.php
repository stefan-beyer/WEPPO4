<?php
/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing;

class RedirectException extends \Exception {
    
    public function __construct($url, $code = 303) {
        parent::__construct($url, $code);
    }
    
    public function getUrl() {
        return $this->getMessage();
    }
}