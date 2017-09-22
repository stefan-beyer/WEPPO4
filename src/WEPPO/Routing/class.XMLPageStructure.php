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
    
    public function __construct($xmlFilename, $patternMatching = MATCH_MODE_EXACT) {
        parent::__construct($patternMatching);
        $this->filename = $xmlFilename;
        
        
        // TODO cache
        #if (!!$cache && file_exists($cache) && (!file_exists($fn) || filemtime($cache) >= filemtime($fn))) {
        #    $this->Pages = (include $cache);
        #} else {
        
        
            if (!file_exists($this->filename)) {
                throw new \Exception('XMLPageStructure: file not found.');
            }
        
            $this->xml = simplexml_load_file($this->filename);
            $this->rootPage = null;
            $this->readXml($this->rootPage, $this->xml);

            #if (!!$cache) {
            #    //echo 'NEW CACHE';
            #    $this->writeCache();
            #}
        #}
            
        //o($this->rootPage);
    }
    
        // will not be called, unless root was not set
    protected function load_root_page() {
        return null;
    }

    protected function readXML(&$parent, &$xml) {
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

        $page->setPattern($pattern)
                ->setControllerName($controller)
                ->setPageName($name)
                ->setMatchMap($matchmap);
        
        foreach ($xml->config as $ctag) {
            $value = (string) $ctag['value'];
            if (!$value) {
                $value = (string) $ctag;
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
    
    
}