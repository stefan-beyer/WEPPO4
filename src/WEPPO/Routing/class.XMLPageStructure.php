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
 * Page Structure defined as xml-File
 * 
 * Used with @see WEPPO::Routing::MemoryPage
 */
class XMLPageStructure extends PageStructure {
    
    protected $filename;
    protected $xml = null;
    
    protected $autoNameCounter = 0;
    
    protected $useCache = true;


    public function __construct($xmlFilename, $patternMatching = MATCH_MODE_EXACT) {
        parent::__construct($patternMatching);
        $this->filename = $xmlFilename;
        
        if ($this->useCache) {
            $cacheFileName = dirname($this->filename) . '/cache.' . basename($this->filename, '.xml') . '.php';
        }
        
        // IF  cache ist used
        // AND the cachefile is readable
        // AND if the xml file not existing OR it is older than cache file
        if ($this->useCache && is_readable($cacheFileName) && (!file_exists($this->filename) || (filemtime($this->filename) <= filemtime($cacheFileName))) ) {
            //echo 'READ FROM CACHE';
            $this->rootPage = (include $cacheFileName);
            
            return;
        }
        
        if (!file_exists($this->filename)) {
            throw new \Exception('XMLPageStructure: file not found.');
        }

        $this->xml = simplexml_load_file($this->filename);
        $this->rootPage = null;
        $this->readXml($this->rootPage, $this->xml);

        if ($this->useCache && is_writable(dirname($cacheFileName))) {
            //echo 'NEW CACHE';
            $this->writeCache($cacheFileName);
        }
    }
    
    
    
    
    private function writeCache($cache) {
        if (!!$cache) {
            $fh = fopen($cache, 'w');
            if ($fh) {
                $code = '<?php' . PHP_EOL
                        . '# WEPPO 4 Page-Structure Cache' . PHP_EOL
                        . 'return ';
                ;
                $code .= $this->_wc($this->rootPage);
                $code .= ';';
                
                //echo $code;
                
                fwrite($fh, $code);
                fclose($fh);
            }
        }
    }
    
    private function _wc(MemoryPage &$p, $l = 0) {

        $indent = str_repeat('  ', $l);
        
        $code = '';
        
        $_ = function($a) {
            return str_replace("'", "\\'", str_replace("\\", "\\\\", $a));
        };
        
        // prepare Match Map array
        $mm = array_map(function(&$a) use($_) {
            return "'".$_($a)."'";
        }, $p->getMatchMap());
        
        
        
        
        
        $pvar = '$p_'.$l;
        
        #$code .= ($pvar . ' = new \\WEPPO\\Routing\\MemoryPage();' . PHP_EOL);
        #$code .= ($pvar . PHP_EOL);
        
        $code .= ('(new \\WEPPO\\Routing\\MemoryPage())' . PHP_EOL);
        $code .= ($indent.'->setPageName('."'".$_($p->getPageName())."'".')' . PHP_EOL);
        $code .= ($indent.'->setPattern('."'".$_($p->getPattern())."'".')' . PHP_EOL);
        $code .= ($indent.'->setControllerName('."'".$_($p->getControllerName())."'".')' . PHP_EOL);
        $code .= ($indent.'->setMatchMap(['.implode(',', $mm).'])' . PHP_EOL);
        $code .= ($indent.'->setHandleSubpath('.($p->canHandleSubpath() ? 'true' : 'false').')' . PHP_EOL);
        
        $config = &$p->getAllConfig();
        foreach ($config as $k => $v) {
            if (is_array($v)) {
                $k.='[]';
            }
            $k = "'".$_($k)."'";
            
            if (is_string($v)) {
                $code .= ($indent.'->setConfig(');
                $code .= ($k.', '."'".$_($v)."'");
                $code .= (')' . PHP_EOL);
            } else if (is_int($v) || is_float($v)) {
                $code .= ($indent.'->setConfig(');
                $code .= ($k.', '.''.$v);
                $code .= (')' . PHP_EOL);
            } else if (is_bool($v)) {
                $code .= ($indent.'->setConfig(');
                $code .= ($k.', '.$v ? 'true' : 'false');
                $code .= (')' . PHP_EOL);
            } else if (is_array($v)) {
                foreach ($v as $_v) {
                    $code .= ($indent.'->setConfig(');
                    $code .= ($k.', '."'".$_($_v)."'");
                    $code .= (')' . PHP_EOL);
                }
            }
        }
        
        $children = $p->getChildren();
        if (count($children)) {
            $chs = [];
            foreach ($children as &$ch) {
                $chs[] = PHP_EOL.$indent.'('.$this->_wc($ch, $l+1).$indent.')';
            }
            $code .= ($indent.'->addChildren(['. implode(', ', $chs).PHP_EOL.$indent.'])' . PHP_EOL);
        }

        return $code;
    }
    
    
    
    
    
    
    private function readXML(&$parent, &$xml) {
        if (!$xml) {
            throw new \Exception('XMLPageStructure: could not load file.');
        }
        
        $page = new MemoryPage();
        
        $pattern = (string) $xml->attributes()->pattern;
        $controller = (string) $xml->attributes()->controller;
        $name = (string) $xml->attributes()->name;
        if (empty($name)) {
            $name = 'p-'.$this->autoNameCounter++;
        }
        
        
        # erstes element in den matches ist immer der ganze match
        $matchmap = (string) $xml->attributes()->matchmap;
        if (!!$matchmap) {
            $matchmap = explode(',', $matchmap);
        } else {
            $matchmap = [];
        }
        
        $chs = !!((string) $xml->attributes()->handleSubpath);

        $page->setPattern($pattern)
                ->setControllerName($controller)
                ->setPageName($name)
                ->setMatchMap($matchmap)
                ->setHandleSubpath($chs);
        echo $chs ? 'ja ' : 'nein ';
        
        foreach ($xml->config as $ctag) {
            $type = (string) $ctag['type'];
            if (!$type) {
                $type = 'string';
            }
            
            
            if ($type === 'xml' || $type === 'html') {
                $value = (string) $ctag; // should use CDATA
                #$value = '';
                #foreach($ctag->children() as $c) {
                #    $value .= $c->asXML() . "\n";
                #}
            } else {
                $value = $ctag['value'];
                if (is_null($value)) {
                    $value = (string) $ctag;
                } else {
                    $value = (string) $value;
                }
            }
            
            
            $key = (string) $ctag['name'];
            
            // TODO type
            
            $page->setConfig($key, $value);
        }
        
        if ($parent == null) {
            $parent = $page;
        } else {
            $page->addTo($parent);
        }
        
        foreach ($xml->page as $node) {
            $this->readXml($page, $node);
        }
    }
    
    
    
    
    // will not be called, unless root was not set
    protected function load_root_page() {
        return null;
    }

}