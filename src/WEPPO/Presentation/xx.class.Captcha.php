<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Presentation;


die('\\WEPPO\\Presentation\\Captcha reimplementieren');
/**
 * Abstrakte Grundklasse für Captchas
 */
abstract class Captcha {
	/**
	 * @var string Name des Inputfeldes
	 */
	protected $inputField = 'ca_input';
	
	/**
	 * Zwischenschalten der Post-Aktion
	 * Liefert zurück ob Captcha erfolgreich war
	 * 
	 * @return boolean ob der Captcha erflgreich eingereicht wurde
	 */
	abstract function postAction();
	
	/**
	 * Formular für den Captcha erzeugen
	 * 
	 * @return string HTML-Code
	 */
	abstract function getForm();
	
	/**
	 * Fehler-Meldung abfragen
	 * 
	 * @return string
	 */
	abstract function getErrorMsg();
	
	/**
	 * Input-Feld-Inhalt auslesen aus $_POST
	 * 
	 * @return string
	 */
	protected function getValue() {
		return isset($_POST[$this->inputField]) ? $_POST[$this->inputField] : null;
	}
} // Captcha


/**
 * Captchas, bei dem man in ein Feld frei lassen muss
 */
class EmptyCaptcha extends Captcha {

	/**
	 * {@inheritDoc}
	 */
	function postAction() {
		$v = $this->getValue();  
		return ($v === '');
	}
	
	/**
	 * {@inheritDoc}
	 */
	function getForm() {
		return '<div style="height:0;padding:0;margin:0;position:relative;left:-3000px;">
		<p>Sicherheitsfeld: Geben Sie hier bitte <strong>nichts</strong> ein:<br/>
		<input type="text" name="'.$this->inputField.'" value="" /></p>
		</div>';
	}
	
	/**
	 * {@inheritDoc}
	 */
	function getErrorMsg() {
		return 'Achten Sie auf die Anweisungen für das Sicherheitsfeld.';
	}
	
} //EmptyCaptcha

/**
 * Captcha, bei dem man rechnen muss. UNFERTIG
 */
class CalculatingCaptcha extends Captcha {

	/**
	 * {@inheritDoc}
	 */
	function postAction() {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 */
	function getForm() {
		return 'Sicherheitscode: <input />';
	}
	
	/**
	 * {@inheritDoc}
	 */
	function getErrorMsg() {
		return 'fehler captcha';
	}
	
} //CalculatingCaptcha




?>