<?php
/**
 * WEPPO 1.0
 * © Stefan Beyer 2016
 * http://weppo.wapplications.net/
 * 
 * @package WEPPO
 */
namespace WEPPO\Helpers {

/**
 * Gibt den Elementen eines Arrays der Reihenfolge nach bestimmte Schlüssel
 * 
 * Wird verwendet um ein URL-Muster aufzuschlüsseln
 * 
 * Sind in $values weniger Einträge als in $keys so werden die Werte null gesetzt.
 * Sind in $values mehr Einträge als in $keys so werden die übrigen ignoriert (mit nummerischen idex versehen).
 * 
 * @param array $values Werte
 * @param array $keys Namen für die Werte
 * 
 * @return array
 */
function nameArray($values, $keys, $defaults=null) {
	if (!is_array($values) || !is_array($keys)) return $values;
	
	
	
	$result = array();
	$n = -1;
	foreach ($keys as $n=>$k) {
		$result[$k] = isset($values[$n]) ? $values[$n] :  (isset($defaults[$n]) ? $defaults[$n] : null);
	}
	for ($i = $n+1; $i < count($values); $i++) {
		$result[$i] = $values[$i];
	}
	
	return $result;
}


/**
 * Mail-Adresse überprüfen
 * 
 * @param string $mail Mailadresse
 * @return boolean
 */
function isValidEmail($mail) {
	//$mailRegEx = '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' .
	'(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i';
	//return preg_match ($mailRegEx, $mail);
	return \filter_var($mail, \FILTER_VALIDATE_EMAIL);
}

/**
 * Dateiendung ermitteln
 * 
 * @param string $file Dateiname
 * @return string Dateiendung
 */
function getExtention($file) {
	return \strtolower(\array_pop(\explode(".", $file)));
}

/**
 * Ist UTF8-Codiert?
 * 
 * Wird duch dekodieren und erneutes kodieren ermitteln
 * 
 * @param string $string Text
 * @return boolean
 */
function isUTF8($string) {
	return (\utf8_encode(\utf8_decode($string)) == $string);
}

/**
 * Sicherstellen, dass ein Text UTF8 codiert ist
 * 
 * @param string $t
 * @return string
 */
function ensureUTF8($t) {
	if (!isUTF8($t)) {
		return \utf8_encode($t);
	}
	return $t;
}


/**
 * Mail-Betreff richtig codieren
 * 
 * @param string $s Betreff
 * @return string
 */
function mailSubjectEncode($s) {
	//$s = utf8_decode($s);
	//return "=?ISO-8859-1?B?" . base64_encode($s) . "?=";
	return "=?UTF-8?B?" . \base64_encode($s) . "?=";
}





/**
 * Browser ermitteln
 * 
 * @return string
 */
function getBrowser() {
	$HTTP_USER_AGENT = $_SERVER[ 'HTTP_USER_AGENT'];
	if( \eregi("(opera) ([0-9]{1,2}.[0-9]{1,3}){0,1}",$HTTP_USER_AGENT,$regs) || \eregi("(opera/)([0-9]{1,2}.[0-9]{1,3}){0,1}",$HTTP_USER_AGENT,$regs)) 
	{ 
		$browser = "Opera $regs[2]"; 
	} 
	else if( \eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) ) 
	{ 
		$browser = "MSIE $regs[2]"; 
	} 
	else if( \eregi("(konqueror)/([0-9]{1,2}.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) ) 
	{ 
		$browser = "Konqueror $regs[2]"; 
	} 
	else if( \eregi("(lynx)/([0-9]{1,2}.[0-9]{1,2}.[0-9]{1,2})",$HTTP_USER_AGENT,$regs) ) 
	{ 
		$browser = "Lynx $regs[2]"; 
	} 
	else if( \eregi("(netscape6)/(6.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) ) 
	{ 
		$browser = "Netscape $regs[2]"; 
	} 
	else if( \eregi("mozilla/5",$HTTP_USER_AGENT) ) 
	{ 
		$browser = "Netscape"; 
	} 
	else if( \eregi("(mozilla)/([0-9]{1,2}.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) ) 
	{ 
		$browser = "Netscape $regs[2]"; 
	} 
	else if( \eregi("w3m",$HTTP_USER_AGENT) ) 
	{ 
		$browser = "w3m"; 
	} 
	else 
	{ 
		$browser = "?"; 
	}
	return $browser; //. ' ('.$HTTP_USER_AGENT.')';
}

/**
 * Ist es der IE6 oder älter?
 * 
 * @return boolean
 */
function isIE_le6() {
	$sp = \explode(' ', getBrowser());
	if ($sp[0] == 'MSIE') {
		if (\intval($sp[1])<=6) {
			return true;
		}
	}
	return false;
}

/**
 * Formatierung von Byte-Angaben in der größt möglichen Tausender-Einheit
 * 
 * @param integer $wert Die Anzahl Bytes
 * @param boolean $dec Sollen 10er Potenzen verwendet werden (true) oder Binär (false)?
 * @return string
 */
function formatedBytes($Wert, $dec = false){
	$div = ($dec ? 1000 : 1024);
	$i   = ($dec ? "" : "i");
	$v = ($dec ? 1000000000000 : 1099511627776);
	
	if($Wert >= $v) $Wert = \number_format($Wert/$v, 2, ",", ".")." T{$i}B";
	else {
		$v /= $div;
		if($Wert >= $v) $Wert = \number_format($Wert/$v, 2, ",", ".")." G{$i}B";
		else {
			$v /= $div;
			if($Wert >= $v) $Wert = \number_format($Wert/$v, 2, ",", ".")." M{$i}B";
			else {
				$v /= $div;
				if($Wert >= $v) $Wert = \number_format($Wert/$v, 2, ",", ".")." K{$i}B";
				else {
					$Wert = \number_format($Wert, 2, ",", ".")." B";
				}
			}
		}
	}
	return $Wert;
}


/**
 * Fügt ein Zeilenumbruch nach einer bestimmten Azahl von Zeichen ein
 * 
 * Die stelle zum Einfügen wird so gesucht, dass von der Soll-Stelle
 * nach vorne und nach hinten im String die nächste Bruchmöglichkeit
 * gesucht wird: Diejenige, die näher am Soll ist, wird verwendet
 * 
 * @param string $t Text
 * @param integer $n Soll-Umbruchbreite in Anzahl Zeichen
 * @param string $break An der Bruchstelle einzufügender String
 * @return string
 */
function insertBreaks($t, $n, $break = '<br/>') {
	$result = '';
	

	while ($n < strlen($t)) {
		//echo $t,"\n";
		$next = strpos($t, ' ', $n);
		$prev = strrpos($t, ' ', -(strlen($t)-$n));
		//echo $prev, ' ', $next, "\n";
		$space = false;
		if ($next === false) $space = $prev;
		else if ($prev == false) $space = $next;
		else {
			// prev     n     next
			if (($n-$prev) <= ($next-$n)) {
				$space = $prev;
			} else {
				$space = $next;
			}
		}
		if ($space !== false) {
			$result .= substr($t, 0, $space) . $break;
		}
		$t = substr($t, $space+1);
	}
	$result .= $t;
	
	
	return $result;
}
// zum testen
	//           1         2         3         4         5         6         7         8         9
	// 0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890
	// Geben: . Beim Einkaufen nicht zu zuviel, weil zu schwer! . beim Spielen ... ohne stricken
//$t = 'Geben: . Beim Einkaufen nicht zu zuviel, weil zu schwer! . beim Spielen ... ohne stricken';
//echo insertBreaks($t, 30);


} //namespace WEPPO\Helpers







namespace {
	
/**
 * Ensures an element is returned as array. 
 * 
 * When empty, returns an empty array. 
 * When an array, returns itself. 
 * Otherwise, an array containg the just the element
 * @param mixed $element Element oder Array
 * @return array
 */
function ensure_array($element) {
	if (empty($element)) {
		return array();
	}
	if (array_key_exists(0, $element)) {
		return $element;
	}
	return array($element);
}
	
}//namespace



