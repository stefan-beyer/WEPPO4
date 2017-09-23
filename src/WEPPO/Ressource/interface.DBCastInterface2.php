<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Ressource;


/**
 * Wert ist der Rückgabewert von parse()
 */
interface DBCastInterface2 {

    function parse($v);

    function toString($v);

    function isValid($v);
}

