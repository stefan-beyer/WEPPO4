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
 * The SimpleTemplateController is an automated Content generator with no dynamic functionality.
 * 
 * Page configuration:
 *  - `template.site` The Class name of
 * It is configured with a main template  (a PHPFileTemplate) 
 * 
 */
class SimpleTemplateController extends Controller {

    public function catchAll(string $action, array $arrPath): bool {

        $siteTemplDescr = $this->getPage()->getConfig('site');
        
        if (!$siteTemplDescr) {
            throw new \Exception('Cannot create site Template. \'site\' not set.');
        }
        
        $siteTemplate = \WEPPO\Presentation\TemplateBase::createTemplate($siteTemplDescr, $this);
        
        echo $siteTemplate->getOutput();
        
        return true;
    }
    
    


    /**
     * Automated generation of parts.
     * 
     * The automated generation of parts is configured in the page configuration.
     * A part needs a configuration entry `template.part.[partName]`
     * with a value like so: `[TemplateClassName]:[TemplateFileName]`.
     * 
     * Example for part 'aside':
     * ```
     * $page->setConfig('part.aside', '\\WEPPO\\Presentation\\CurlyTemplate:aside.html')
     * ```
     * 
     * This provides a simple way to merge several parts into one page output.
     * 
     * If you want to pass some params to the template, you can do it this way:
     * `setConfig('part.aside.paramName', 'ParamValue')`
     * In a PHPFileTemplate just do this:
     * `$this->get('paramName')`
     * 
     * 
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function getPart(string $name) : string {
        $_name = 'part.'.$name;
        
        if (!$this->getPage()->hasConfig($_name)) {
            throw new \Exception('Part \'' . $name . '\' could not be created: It is not configured. Set configuration \''.$_name.'\' for page.');
        }
            
        $part = $this->getPage()->getConfig($_name);

        try {
            $template = $this->createTemplate($part);
        } catch (\Exception $e) {
            throw new \Exception('Part \'' . $name . '\' could not be created: '.$e->getMessage());
        }

        # Read params for template from config
        $config = $this->getPage()->getAllConfig();
        foreach ($config as $key => &$value) {
            # filter for the right prefix
            if (strpos($key, $_name) === 0) {
                $paramName = substr($key, strlen($_name)+1);
                $template->set($paramName, $value);
            }
        }
        try {
            return $template->getOutput();
        } catch (\WEPPO\Presentation\TemplateException $e) {
            throw new \Exception('Part \'' . $name . '\' could not be created: ' . $e->getMessage(), 0, $e);
        }
    }

}
