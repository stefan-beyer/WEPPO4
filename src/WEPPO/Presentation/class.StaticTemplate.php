<?php

/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Presentation;

/**
 * Pseudo Template with static output, set via Constructor, or setOutput()
 */
class StaticTemplate extends TemplateBase {
    protected $output = '';
    
    public function __construct(&$controller, $output = '') {
        parent::__construct($controller);
        $this->setOutput($output);
    }
    
    public function getOutput(bool $trim = true): string {
        return $this->output;
    }

    public function isExisting(): bool {
        return true;
    }
    
    public function setOutput($o) {
        $this->output = $o;
    }
    
}

