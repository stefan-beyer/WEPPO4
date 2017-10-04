<?php
/**
 * WEPPO 4
 * @package weppo
 * @author Stefan Beyer<info@wapplications.net>
 * @see http://weppo4.wapplications.net/
 * 
 */

namespace WEPPO\Routing\DB;

class Page extends \WEPPO\Ressource\TableRecord  implements \WEPPO\Routing\PageInterface {
    use \WEPPO\Routing\PageImplementation;
    
    public function __construct($id = 0, $cols = '*') {
        parent::__construct($id, $cols);
        $this->Page_Config = null;
        $this->Page_Children = null;
    }
    
    public static function getTablename() {
        return 'pages';
    }
    
    public function loadEmpty() {
        parent::loadEmpty();
    }

    protected function loadConfig() {
        if ($this->Page_Config === null) {
            $this->Page_Config = [];
            Config::where('page_id', $this->id);
            $configs = Config::get();
            foreach ($configs as &$c) {
                $this->setConfig($c->key, $c->value);
            }
        }
        return $this->Page_Config;
    }
    
    private function load_children() {
        if (is_null($this->Page_Children)) {
            Page::where('parent_id', $this->id);
            $this->Page_Children = Page::get();
        }
    }

    public function getChildren(): array {
        $this->load_children();
        return $this->Page_Children;
    }

    public function hasChildren(): bool {
        $this->load_children();
        if (is_null($this->Page_Children)) return false;
        return count($this->Page_Children) > 0;
    }
    
    public function getControllerName(): string {
        return $this->controller;
    }

    public function getMatchMap(): array {
        if (!empty($this->matchmap)) {
            $this->matchmap = explode(',', $this->matchmap);
        } else {
            $this->matchmap = [];
        }
        return $this->matchmap;
    }

    public function getPageName(): string {
        return $this->name;
    }

    public function getPattern(): string {
        return $this->pattern;
    }
    
    private function load_parent() {
        if ($this->Page_Parent === null) {
            if (!$this->parent_id) return;
            try {
                $this->Page_Parent = new Page($this->parent_id);
            } catch (WEPPO\Ressource\TableRecordException $e) {
                $this->Page_Parent = null;
            }
        }
    }


    public function &getParent(): \WEPPO\Routing\PageInterface {
        $this->load_parent();
        return $this->Page_Parent;
    }
    
    
    public function hasParent(): bool {
        return !!$this->parent_id;
    }

}
