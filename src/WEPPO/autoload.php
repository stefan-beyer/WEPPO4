<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */


/**
 * Debug-Ausgabe einer beliebigen Variable
 * 
 * @param mixed $o Was ausgegeben werden soll
 * @param string $t Title
 */
function o($o, $t='') {
	echo '<pre>';
	echo $t ? "<strong>$t</strong>:\n" : '';
	\print_r($o);
	echo '</pre><hr/>';
}

include WEPPO_ROOT . 'WEPPO/Helpers/inc.functions.php';

/**
 * WEPPO-Autoloader
 * 
 * Namespaces werden als Ordnerstruktur abgebildet.
 */
spl_autoload_register(function ($class) {
    # namespaces auftrennen
    $cn = explode('\\', $class);
    
    # mindestens WEPPO\xyz
    if (count($cn)<2) {
        return;
    }
    
    # erster Teil muss WEPPO sein, um von diesem Autoloader verarbeitet zu werden
    if (!in_array($cn[0], ['WEPPO', 'i18n'])) {
        return;
    }
    
    # letzter teil wird zu dateiname
    $lidx = count($cn)-1;
    $classbasename = '.'.$cn[$lidx].'.php';
    foreach (['class', 'trait', 'interface'] as $ct) {
        $cn[$lidx] = $ct.$classbasename;

        # Pfad zu Dateiname zusammenfügen
        $path = WEPPO_ROOT . implode('/', $cn);
        
        //echo $path, '<br/>';

        if (!file_exists($path)) {
            continue;
        }

        require_once $path;
        break;
    }
    
});

