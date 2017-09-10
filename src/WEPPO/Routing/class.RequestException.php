<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing;

class RequestException extends \Exception {
    private $request = null;
    private $info = '';


    const ROOT_PAGE_NOT_SET = 10;
    const PAGE_NOT_FOUND = 11;
    
    const CONTROLLER_NOT_SET = 20;
    const CONTROLLER_NOT_FOUND = 21;
    
    const ACTION_NOT_HANDLED = 30;
    
    static private $MSGS = [
        self::PAGE_NOT_FOUND => 'Page Not Found.',
        self::CONTROLLER_NOT_SET => 'Controller Not Set.',
        self::CONTROLLER_NOT_FOUND => 'Controller Not Found.',
        self::ROOT_PAGE_NOT_SET => 'Root Page Not Set.',
        self::ACTION_NOT_HANDLED => 'Action Not Handled.',
    ];
    
    public function __construct($code) {
        $msg = isset(self::$MSGS[$code]) ? self::$MSGS[$code] : 'Unknown Error Code '.$code;
        parent::__construct($msg, $code);
    }
    
    public function setInfo(string $i) {
        $this->info = $i;
        $this->message .= '; '.$i;
    }
    public function setRequest(Request &$request) {
        $this->request = $request;
        
        $requestPath = $request->getPath();
        if ($request->hasPage()) {
            $p = $request->getPage();
            $pagePath = $p->getPath();
        } else {
            $pagePath = '';
        }
        
        $this->message .= ' Request to '.$requestPath;
        if ($pagePath) {
            $this->message .= '; page path '.$pagePath;
        }
        
        if ($request->hasController()) {
            $this->message .= '; Controller '.get_class($request->getController());
        }
    }
    
    #public function getPath() {
    #    return $this->path;
    #}
    
}