<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Presentation;

die('\\WEPPO\\Presentation\\TemplateCalls reimplementieren');

/**
 * Ein Werkzeug, mit dem man spezielle Template-Tags in einem Text erkennen und das entsprechende Template aufrufen und einfügen kann.
 * 
 * ### Anleitung
 * 
 * Mit diesem Modul lassen sich Texte verarbeiten, die spezielle [Template](./WEPPO.View.Template.html)-Aufrufe enthalten.
 * 
 * #### Template-Call-Tags
 * 
 * Die Tags können verschiedene Formen haben:
 *  - Shorttags: `[templatename]` haben keine Argumente/Parameter
 *    Der Name eines Templates, das keine Parameter benötigt wird zwischen
 *    eckigen Klammern gesetzt.
 *    Damit können einfach Code-Schnippsel eingebunden werden.
 *  - Mit den XML-ähnlichen Template-Call-Tags können dem Template auch 
 *    Parameter übergeben werden:
 *      - Variante 1: Parameter werden über Attribute angegeben; der Inhalt
 *        des Tags wird als `content`-Parameter verwendet.
 *        ```html
 *        <t t:name="templatename" parametername="parameter-wert">
 *          content-Parameter
 *        </t>
 *        ```
 *      - Variante 2: Parameter werden über `t:param`-Tags im Inneren definiert;
 *        Taucht ein Parameter-Name mehr als einmal auf, so wird der Parameter
 *        Wert ein Array und es können beliebig viele weitere Werte folgen.
 *        ```xml
 *        <t t:name="templatename">
 *          <t:param name="parametername1">Parameter-Wert</t:param>
 *          <t:param name="parametername2">Wert</t:param>
 *        </t>
 *        ```
 *     Die zwei Varianten können auf vermischt werden, solange jeder Parameter nur einmal gesetzt wird.
 * 
 *     **Achtung:** Dieses Modul arbeitet nicht mit einem 'richtiger' XML-Parser. Man muss vorsichtig sein,
 *     dass die Tags auch richtig wirklich erkennt werden.
 * 
 * #### Aufruf
 * 
 * ```php
 * $text = 'Etwas Text mit Template-Calls...';
 * $oTemplateCalls = new \WEPPO\View\TemplateCalls($text);
 * $newText = $oTemplateCalls->shortcuts(); // Shorttags verarbeiten
 * $newText = $oTemplateCalls->process();   // komplexe Template-Call-Tags verarbeiten
 * echo $newText;
 * ```
 * 
 * Wenn nur Shorttags verwendet werden sollen, kann auch eine statische Abkürzungsfuntion verwendet werden:
 * 
 * ```php
 * $text = 'Etwas Text mit Shorttags...';
 * $newText = \WEPPO\View\TemplateCalls::doShortcuts($text);
 * echo $newText;
 * ```
 * 
 * @TODO begrenzen, welche Templates hierfür verwendet werden dürfen!
 * 
 */
class TemplateCalls
{
	/**
	 * 
	 * @var string $html Text-Inhalt, der verarbeitet wird.
	 */
	protected $html;
	
	/**
	 * Erzeugt ein TemplateCalls-Objekt für den angegebene Text.
	 * 
	 * @param string $html Text- oder HTML-Zeichenkette
	 */
	function __construct($html)
	{
		$this->html = $html;
	}
	
	/**
	 * Liefert den aktuellen Text
	 * 
	 * @return string
	 */
	function getHtml() {
		return $this->html;
	}
	
	/**
	 * Abkürzungsfunktion für Shorttags
	 * 
	 * #### Verwendung
	 * ```php
	 * $text = 'Etwas Text mit Shorttags...';
	 * $newText = \WEPPO\View\TemplateCalls::doShortcuts($text);
	 * echo $newText;
	 * ```
	 * 
	 * @param string $t Text- oder HTML-Zeichenkette
	 * @return string Verarbeitete Zeichenkette
	 */
	static function doShortcuts($t)
	{
		$tc = new TemplateCalls($t);
		return $tc->shortcuts();
	}
	
	/**
	 * Verarbeitet Shorttags im aktuellen Text
	 * 
	 * Der Interne Code wird dabei verändert!
	 * 
	 * @param mixed $context (wird zur Zeit nicht verwendet)
	 * @return string Verarbeitete Zeichenkette
	 */
	function shortcuts($context=null)
	{
		$scPattern = '#\\[(.*)\\]#imsu';
		
		$start = 0;
		while (true) {
			$matches = array();
			$ret = preg_match($scPattern, $this->html, $matches, PREG_OFFSET_CAPTURE, $start);
			if ($ret) {
				//_o($matches);
				$tagstart_pos = $matches[0][1];
				$length = strlen($matches[0][0]);
				$tempname = $matches[1][0];
				$tempname = str_replace('/', '', $tempname);
				$tempname = str_replace('..', '', $tempname);
				$tempname = str_replace('..', '', $tempname);
				
				$result = static::getShortcutContent($tempname);
				
				
				$this->html = substr($this->html, 0, $tagstart_pos) . $result . substr($this->html, $tagstart_pos+$length);
				
				
				$start = $tagstart_pos + strlen($result);

			} else {
				break;
			}
		}
		return $this->html;
	}
	
	/**
	 * Verarbeitet die komplexen Template-Tags im aktuellen Text
	 * 
	 * Der Interne Code wird dabei verändert!
	 * 
	 * @param mixed $context (wird zur Zeit nicht verwendet)
	 * @return string Verarbeitete Zeichenkette
	 */
	function process($context = null) {
		$n = 0;
		$start = 0;
		while ($t = self::getTag($this->html, 't', $start)) {
			//_o($t);
			
			$result = self::callTemplate($t, $context);
			//_o($result);
			
			$this->html = substr($this->html, 0, $t['tag_start']) . $result . substr($this->html, $t['tag_end']);
			
			$start = $t['tag_start'] + strlen($result); //$t['tag_end'];
			//echo ' neustart: '.$start;
			//$n++;
			//if ($n>0) break;
		}
		return $this->html;
	} // process
	
	
	
	
	
	
	
	/**
	 * Extrahiert Tags aus dem angegebenen Text
	 * 
	 * Das heißt, liest Tags mit dem angegebenen Name aus und entfernt sie anschließend
	 * aus dem Text. Die Tags werden zurückgegeben; die Methode hat einen Seiteneffekt
	 * auf den übergebenen Text
	 * 
	 * @param string $html Referenz auf den text (wird verändert!)
	 * @param string $tn Tag-Name
	 * @return array Tag-Beschreibung für alle gefundenen Tags [getTag()](#method_getTag)
	 */
	protected static function extractAllTags(&$html, $tn) {
		$tags = array();
		$start = 0;
		while ($t = self::getTag($html, $tn, $start)) {
			//_o($t);
			
			
			$html = substr($html, 0, $t['tag_start']) . substr($html, $t['tag_end']);
			
			$start = $t['tag_start'];
			
			unset($t['tag_start']);
			unset($t['tag_end']);
			$tags[] = $t;
		}
		return $tags;
	}
	
	/**
	 * Sucht einen Tag im angegebenen Text
	 * 
	 * @param string $html Text
	 * @param string $tn Tagname
	 * @param integer $start Ab diesem Index beginnt die Suche
	 * @param integer $end Nur bis zu diesem Index wird gesucht
	 * @return array Tag-Beschreibung für alle gefundenen Tags
	 * Ein Array von Arrays dieser Form:
	 * ```php
	 *		array(
	 *			'tag_start'		=> Beginn des erkannten Tags,
	 *			'tag_end'		=> Ende des erkannten Tags,
	 *			'content'		=> Tag-Inhalt,
	 *			'attributes'	=> Array mit Attributen,
	 *		)
	 * ```
	 */
	protected static function getTag($html, $tn, $start=0, $end=null)
	{
		if ($end === null) $end = strlen($html);
		//$startTag = '<'.$tn.'[>\s]';
		//$endTag = '</'.$tn.'>';
		
		
		$result = '';
		
		//$startPatt = '#<'.$tn.'(\s([^>]*)>|>)#imsu';
		$startPatt = '#<'.$tn.'(\s([^>]*)>|>)#imsu';
		$endPatt = '#</'.$tn.'>#ims';
		
		$matches = array();
		$ret = preg_match($startPatt, $html, $matches, PREG_OFFSET_CAPTURE, $start);
		if ($ret) {
			$tagstart_pos = $matches[0][1];
			$content_start = $tagstart_pos + strlen($matches[0][0]);
			$attributes = isset($matches[2][0]) ? $matches[2][0] : '';
			//_o($matches);
			if (strlen($attributes)>0 && $attributes[strlen($attributes)-1]=='/') {
				//selfclosing tag, das / am ende sollte für attribute kein problem sein
				$content = '';
				$tagend_pos = $content_start;
			} else {
				// ein bisschen was drauf addieren um zeichen zu über springen...
				$endsearchstart = $content_start;
				//echo $endsearchstart;
				
				//$matches = array();
				do {
					$retend = preg_match($endPatt, $html, $matches_end, PREG_OFFSET_CAPTURE, $endsearchstart);
					$retstart = preg_match($startPatt, $html, $matches_start, PREG_OFFSET_CAPTURE, $endsearchstart);
					if ($retend) {
						$content_end = $matches_end[0][1];
						$tagend_pos = $content_end + strlen($matches_end[0][0]);
						//_o($matches_end);
						//_o($matches_start);

						
						if ($retstart) {
							// weiterer  start gefunden. ist er vor dem gefundenen ende?
							$start2pos = $matches_start[0][1];
							
							$endsearchstart = $tagend_pos;
							
						} else $start2pos = false;
						
						
						
						//echo 'Tag von ' . $tagstart_pos . ' bis ' . $tagend_pos . ' Content von ' . $content_start . ' bis ' . $content_end;
					} else return false;
				} while ($start2pos!==false && $start2pos < $tagend_pos);
				
				$content = trim(substr($html, $content_start, $content_end-$content_start));
			}
			
			$result = array(
				'tag_start'		=> $tagstart_pos,
				'tag_end'		=> $tagend_pos,
				'content'		=> $content,
				'attributes'	=> self::parseAttributes($attributes),
			);
			return $result;
			
			//$result = var_export(, true);
		}
		return false;
	} // getTag
	
	/**
	 * Ausführen eines Template-Calls
	 * 
	 * Das Tag-Objekt wird ausgewertet, das Template geladen und der
	 * erzeugte Inhalt zurückgegeben.
	 * 
	 * @param array $tobj Tag-Beschreibung (siehe [getTag()](#method_getTag))
	 * @param mixed $context (wird zur Zeit nicht verwendet)
	 * @return string Erzeugter Inhalt
	 */
	protected static function callTemplate($tobj, $context = null)
	{
		
		
		$paramtags = self::extractAllTags($tobj['content'], 't:param');
		//_o($paramtags);
		//_o($tobj['content']);
		
		$tc = new TemplateCalls($tobj['content']);
		$content = $tc->process($context);
		unset($tc);
		
		# Name des aufgerufenen Templates ermitteln
		$tname = '';
		if (isset($tobj['attributes']['t:name'])) { # t:name ist neu!
			$tname = $tobj['attributes']['t:name'];
			unset($tobj['attributes']['t:name']);
		} else if (isset($tobj['attributes']['name'])) { # für abwärtskompatibilität
			$tname = $tobj['attributes']['name'];
			unset($tobj['attributes']['name']);
		}
		
		if ($tname) {
			$T = new Template($tname);
			
			if (!$T->exists()) return '[TemplateCall "'.$tname.'" mit {'.$content.'}]';
			
			
			$T->setParam('content', $content);
			
			foreach ($tobj['attributes'] as $k=>$v) {
				$T->setParam($k, $v);
			}
			
			foreach ($paramtags as $pt) {
			
				if (isset($pt['attributes']['name'])) {
					$tc = new TemplateCalls($pt['content']);
					$c = $tc->process($context);
					unset($tc);
					
					# falls schon vorhanden: array draus machen und anhängen TODO: add() verwenden?
					if ($T->hasParam($pt['attributes']['name'])) {
						//echo 'has '.$pt['attributes']['name'];
						$p = $T->getParam($pt['attributes']['name']);
						if (!is_array($p)) $p = array($p);
						$p[] = $c;
						$T->setParam($pt['attributes']['name'], $p);
					} else {
						$T->setParam($pt['attributes']['name'], $c);
					}
				}
			}
			
			return $T->getOutput();
		} else {
			return '[Template-Name nicht angegeben.]';
		}
	} // callTemplate
	
	/**
	 * Attribute eines Tags einlesen
	 * 
	 * @param string $attr Die Zeichenkette, die die Attribute enthält
	 * @return array Array der Attribute
	 */
	protected static function parseAttributes($attr)
	{
		$patt =  '#(\w*)="([^"]*)"#imsu';
		$retend = preg_match_all($patt, $attr, $matches);
		if ($retend) {
			$names = $matches[1];
			$values = $matches[2];
			return array_combine($names, $values);
		}
		//_o($matches);
		return array();
	} // parseAttributes
	
	/**
	 * Holt den Tamplate-Inhalt für Shortcuts
	 * 
	 * Für Shortcuts braucht man keine Parameter einlesen und setzen, daher
	 * ist das hier schneller.
	 * 
	 * @param string $tn Tagname
	 * @return string Template-Inhalt
	 */
	protected static function getShortcutContent($tn)
	{
		$T = new Template($tn, null, 'htm');
		
		if ($T->exists()) {
			return $T->getOutput();
		}		
		return "[$tn]";
	}

} // class TemplateCalls
?>
