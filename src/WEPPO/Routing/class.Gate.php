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
 * Start-Skript-Wechsel nach Domain- und Pfad
 * 
 * Um unterschiedliche Konfigurationen je nach Domain oder Pfad zu laden.
 * 
 * Bevor die eigentliche Konfigurstion vorgenommen wird, kann eine Weiche
 * (Gate) aufgerufen werden, die die weitere Konfiguration abhängig von
 * der aufgerufenen URL (Domain, Subdomain, Pfad) durchführt.

 * Damit kann z.B. für die selbe WEPPO-Installation je nach Subdomain
 * eine andere Konfiguration geladen werden, das heißt, eine andere
 * Seitenstruktur, eine andere Datenbank etc...
 * Dafür stellt man z.B.  `$cfg['xmldb_path']` und `$cfg['xml_pages']` je
 * nach App unterschiedlich ein.

 * 	Die Zuordnung ist ein Array wie z.B.:
 * ```php
 * array(
 *      'subdomain' => '<Start-Skript relativ zu APP_ROOT>',
 * );
 * ```
 * 
 * Wird der zugeordneten Konfigurationsdatei ein `/` vorangestellt, so wird
 * der System-cfg-Ordner verwendet, sonst der APP-cfg-Ordner.
 * 
 * Quellen können sein: `Gate::MODE_DOMAIN` oder  `Gate::MODE_PATH`
 */
class Gate {

    /**
     * Die Domain wird als Quelle verwendet
     */
    const MODE_DOMAIN = 1;

    /**
     * Der Request-Path (Pfad) wird als Quelle verwendet
     */
    const MODE_PATH = 3;

    /**
     * TLD und Second-Level-Domain werden ignoriert, also wird nur ab der Subdomain geprüft. Für MODE_DOMAIN.
     */
    const LEVEL_SUBDOMAIN = 2;

    /**
     * Vollständige Weiche wird verwendet
     */
    const LEVEL_FULL = 0;

    /**
     * @var array $definition Zuordnungen
     */
    protected $definition;

    /**
     * @var integer $mode Welche Quelle wird verwendet?
     */
    protected $mode;

    /**
     * @var integer $level Wieviele Teile der Quelle sollen ignoriert werden?
     */
    protected $level;

    /**
     * @var string $separator '.' oder '/'
     */
    protected $separator;

    public static $gatePath = null;
    
    /**
     * Erstellt ein Gate
     * 
     * @param array $def Zuordnung
     * @param integer $mode Modus
     * @param integer $lvl Level
     */
    function __construct($def, $mode, $lvl = false) {
        $this->definition = $def;
        $this->mode = $mode;
        $this->level = $lvl;
        if ($this->level === false) {
            $this->level = self::LEVEL_FULL;
        }

        switch ($this->mode) {
            case self::MODE_PATH:
                $this->separator = '/';
                break;
            case self::MODE_DOMAIN:
                $this->separator = '.';
                break;
            default:
                $this->separator = '';
        }
    }

    protected function _get_parts() {
        $parts = [];
        switch ($this->mode) {
            case self::MODE_DOMAIN:
                # Domainname auseinandernehmen
                $parts = \array_reverse(\explode('.', Url::getHost()));
                break;
            case self::MODE_PATH:
                # Pfad auseinandernehmen
                if (is_string($_SERVER['REQUEST_URI'])) {
                    $parts = \substr($_SERVER['REQUEST_URI'], 1); # gleich das erste / weg machen
                } else {
                    $parts = '';
                }
                if (empty($parts)) {
                    return [];
                }
                if ($parts[\strlen($parts) - 1] == '/') {
                    $parts = \substr($parts, 0, -1);
                }
                $parts = \explode('/', $parts);
                break;
        }
        return $parts;
    }
    
    /**
     * Die Weiche ausführen.
     */
    function call() {
        $parts = $this->_get_parts();

        if (count($parts) >= $this->level) {
            \array_splice($parts, 0, $this->level);
        } else {
            $parts = array();
        }

        if (\count($parts) == 0) {
            if ($this->_call('')) {
                return;
            }
            $this->error();
        }
        
        $key = '';
        foreach ($parts as $p) {
            switch ($this->mode) {
                case self::MODE_DOMAIN:
                    if ($key) {
                        $key = $this->separator . $key;
                    }
                    $key = $p . $key;
                    break;
                case self::MODE_PATH:
                    if ($key) {
                        $key = $key . $this->separator;
                    }
                    $key = $key . $p;
                    break;
            }
            if ($this->_call($key)) {
                return;
            }
        }
        $this->error();
    }

    /**
     * Versuche, die Weiche nach dem key zu stellen.
     * 
     * Wenn dafür keine Zuordnung vorhanden ist, wird false zurückgeliefert
     * 
     * @param string $key Zuordnungs-Schlüssel
     * @return true die Weiche gestellt wurde
     * @return false wenn die Zuordnung oder die Konfigurations-Datei nicht vorhanden ist
     */
    protected function _call($key) {
        
        if (isset($this->definition[$key])) {
            $fn = $this->definition[$key];
            $fn = APP_ROOT . $fn;
            if (\file_exists($fn)) {
                if ($this->mode == self::MODE_PATH) {
                    self::$gatePath = explode('/', $key);
                }
                include $fn;
                return true;
            }
        }
        return false;
    }
    
    function error() {
        die('Web-Applikation nicht verfügbar.');
    }

    
}
