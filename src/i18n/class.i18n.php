<?php

namespace {

    function __($id, $mu = null) {
        return \i18n\i18n::getInstance()->getTranslation($id, $mu);
    }

}

namespace i18n {



    class i18n {

        var $lang;
        var $untranslated = array();
        var $translations;
        var $markUntranslated = true;
        static $instance = null;
        static $cookieName = 'i18n';
        static $availableLanguages = array();
        static $defaultLanguage = 'en';
        static $mode = 'cookie';
        static $domain = 'cookie';
        
        static $useFiles = true;

        function __construct($l) {



            $this->translations = array();
            $this->lang = substr(strtolower(\preg_replace('/[^A-Za-z]/', '', $l)), 0, 2);
            $this->loadTranslations();
        }

        function __destruct() {
            //echo 'i18n __destruct';
            if (count($this->untranslated)) {
                $s = 'Missing Translation [' . $this->lang . ']' . PHP_EOL;
                foreach ($this->untranslated as $nt) {
                    $s .= '# ' . $nt[1] . PHP_EOL;
                    $s .= "\t'" . md5($nt[1]) . "' =&gt; '" . str_replace("'", "\\'", $nt[1]) . "'," . PHP_EOL;
                }
                $fn  = APP_ROOT . 'data/untranslated.txt';
                if (file_exists($fn)) {
                    if (file_put_contents($fn, $s) === false) {
                        //echo('ddd');
                    }
                }
            }
        }

        static function init() {

            $hi_code = '';
            
            if (isset($_GET['lang'])) {
                $hi_code = $_GET['lang']; // sanatize
                if (!self::isLangAvailable($hi_code)) {
                    $hi_code = '';
                }
            }
            
            if (empty($hi_code)) {
                if (self::$mode == 'cookie') {
                    if (isset($_COOKIE[self::$cookieName])) {
                        $hi_code = $_COOKIE[self::$cookieName];
                        $hi_code = substr($hi_code, 0, 2);
                        if (!self::isLangAvailable($hi_code)) {
                            $hi_code = '';
                        }
                    }
                } else if (self::$mode == 'subdomain') {
                    $domain = explode('.', $_SERVER['SERVER_NAME']);
                    $l = isset($domain[0]) ? $domain[0] : '';
                    if ($l && self::isLangAvailable($l))
                        $hi_code = $l;
                }
            }

            if (empty($hi_code)) {
                $hi_code = self::guessLang();
            }
            
            self::$instance = new i18n($hi_code);
        }

        static function guessLang() {
            $hi_code = "";
            $hi_quof = 0;
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $langs = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            } else {
                $langs = array('');
            }

            foreach ($langs as $lang) {
                $_l = explode(";", $lang);
                if (count($_l) == 0)
                    $_l[] = self::$defaultLanguage;
                if (count($_l) == 1)
                    $_l[] = 'q=1';
                list($codelang, $quoficient) = $_l;
                $quoficient = floatval(substr($quoficient, 2));
                //if($quoficient == NULL) $quoficient = 1;

                $_l = substr($codelang, 0, 2);

                if ($quoficient > $hi_quof && self::isLangAvailable($_l)) {
                    $hi_code = $_l;
                    $hi_quof = $quoficient;
                }
            }
            
            if (empty($hi_code)) {
                $hi_code = self::$defaultLanguage;
            }
            
            // $hi_code ist the best choice
            return $hi_code;
        }

        static function getLangURL($l, $backlink = null) {
            if (self::$mode == 'cookie') {
                $url = '/language/select/' . $l;
                if ($backlink) {
                    $url .= '?backlink='.urlencode($backlink);
                }
            } else if (self::$mode == 'subdomain') {
                $url = \WEPPO\Routing\Url::getAbsUrl($_SERVER['REQUEST_URI']);
                
                # nicht so super, aber läuft...
                
                # auseinandernehmen
                $url = explode('://', $url);
                $url[1] = explode('/', $url[1]);
                //_o($url);
                $domain = explode('.', self::$domain);
                if (count($domain) === 3) {
                    unset($domain[0]);
                }
                $url[1][0] = $l . '.' . implode('.',$domain);

                # zusammenbauen
                $url[1] = implode('/', $url[1]);
                $url = implode('://', $url);
            }
            return $url;
        }

        static function isLangAvailable($lang) {
            $a = in_array($lang, array_keys(self::$availableLanguages));
            if (self::$useFiles) {
                $b = file_exists(self::getLangInclude($lang));
                return $a && $b;
            }
            return $a;
        }

        static function getLangInclude($lang) {
            $lang = strtolower(\preg_replace('/[^A-Za-z]/', '', $lang));
            return APP_ROOT . 'lang/' . $lang . '.php';
        }

        static function getInstance() {
            if (self::$instance == null) {
                trigger_error('i18n not initialized', E_USER_ERROR);
                die();
            }
            return self::$instance;
        }

        function loadTranslations() {
            $fn = self::getLangInclude($this->lang);
            if (file_exists($fn)) {
                $this->translations = include $fn;
            } else {
                $this->translations = array();
            }
        }

        function getTranslation($id, $mu = null) {
            if ($mu === null)
                $mu = $this->markUntranslated;

            $idhash = md5($id);
            if (isset($this->translations[$idhash])) {
                return $this->translations[$idhash];
            }
            $this->untranslated[] = array($this->lang, $id);
            if ($mu) {
                return '<span class="untranslated">' . $id . '</span>';
            } else {
                return $id;
            }
        }

    }

;
}