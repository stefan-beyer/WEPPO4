<?php
/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Controller;

/**
 * Controller that can be configurated to redirect to an other location.
 * 
 * @TODO documentation for modes:
 *  - intern
 *  - extern
 *  - web
 * @TODO implement intern and web
 */
class RedirectController extends Controller {

    public function catchAll(string $action, array $arrPath): bool {

        $to = $this->getPage()->getConfig('redirect.to');
        
        # remove '/' at the beginning
        if (substr($to, 0, 1) == '/') {
            $to = substr($to, 1);
        }
        $to = $this->getRequestHandler()->buildPath([$to]);
        
        
        
        $mode = $this->getPage()->getConfig('redirect.mode');
        if (!$mode) {
            $mode = 'extern';
        }

        if ($mode === 'extern') {
            #\WEPPO\System::redirect($to);
            echo '
            <p>Den gewünschten Inhalt finden Sie unter <a href="'. htmlspecialchars($to) .'">'. htmlspecialchars(\WEPPO\Routing\Url::getAbsUrl($to)) .'</a></p>
            ';
            throw new \WEPPO\Routing\RedirectException($to);
        } else if ($mode === 'intern') {
            //$p = \WEPPO\System::$requestHandler->structure->getPageObject($m);
            #\WEPPO\System::$requestHandler->processRequest($to);
            throw new \Ecxeption('RedirectController: intern NYI.');
        } else if ($mode === 'web') {
            $allowed = $this->getPage()->getConfig('redirect.allowed');
            
            throw new \Ecxeption('RedirectController: web NYI.');
        }

        return true;
    }

}
