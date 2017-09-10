<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Controller;

/**
 * This Controller smply takes the 'content' configuration and outputs it. Period.
 * This is mostly for testing reasons.
 * 
 * Okay, there is one secret: all other configuration settings can be used in the template via {curly} placeholders.
 * You need to set the placeholders[] setting with every placeholder you want to use.
 */
class DirectContentController extends \WEPPO\Controller\Controller {
    
    protected function action_index(array $pd) {
        $page = &$this->getPage();
        
        $content = $page->getConfig('content', '');
        $placeholders = $page->getConfig('placeholders', []);
        if (!is_array($placeholders)) {
            $placeholders = [];
        }
        
        #$config = $this->getPage()->getAllConfig();
        #foreach ($config as $key=>&$value) {
        foreach ($placeholders as $key) {
            if ($key === 'content') {
                continue;
            }
            $value = $page->getConfig($key);
            if (is_string($value)) {
                $content = str_replace('{'.$key.'}', $value, $content);
            }
        }
        echo $content;
        return true;
    }
    
    
}



