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
 * One hell of a special Exception.
 * 
 * Throw this Exception and it will be catched by @see WEPPO::Application::Application::autoRequest()
 * that will perform a http redirect.
 * This is used so that no other code of the controller will be executed but the system can
 * shut down correctly. No need to call exit() or die().
 */
class RedirectException extends \Exception {
    
    public function __construct($url, $code = 303) {
        parent::__construct($url, $code);
    }
    
    public function getUrl() {
        return $this->getMessage();
    }
}