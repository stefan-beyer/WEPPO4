<?php
/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing;


const REDIRECT_INTERN = 1;
const REDIRECT_EXTERN = 2;

/**
 * One hell of a special Exception.
 * 
 * Throw this Exception and it will be catched by @see WEPPO::Application::Application::autoRequest()
 * that will perform a http redirect.
 * This is used so that no other code of the controller will be executed but the system can
 * shut down correctly. No need to call exit() or die().
 */
class RedirectException extends \Exception {
    
    protected $mode;
    
    public function __construct($url, $mode = REDIRECT_EXTERN, $code = 303) {
        parent::__construct($url, $code);
        $this->mode = $mode;
    }
    
    public function getUrl() {
        return $this->getMessage();
    }
    
    public function getMode() {
        return $this->mode;
    }
}