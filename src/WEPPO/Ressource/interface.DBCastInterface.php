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
 * Wert ist instanz von dieser Klasse
 */
interface DBCastInterface {

    function parse($v);

    function toString();

    function isValid();
}

