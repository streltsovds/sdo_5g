<?php
require_once 'Zend/Filter/Interface.php';
require_once 'HTMLPurifier/HTMLPurifier.auto.php';

class HM_Filter_HtmlSanitize implements Zend_Filter_Interface
{
    private $_richHtmlAllowed = false;

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $config = HTMLPurifier_Config::createDefault();

        if($this->_richHtmlAllowed) { //для прохода данных от HTML-редактора с видео
            $config->set('HTML.SafeObject', 1);
            $config->set('HTML.SafeEmbed', 1);
        }

        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        //$config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        //$config->set('CSS.ForbiddenProperties', array('behavior' => true, 'filter' => true, '-ms-filter' => true));
        $config->set('Core.RemoveProcessingInstructions', true);
        //$config->set('Filter.Custom', array());
        //$config->set('Filter.ExtractStyleBlocks', true);
        $config->set('Cache.SerializerPath', APPLICATION_PATH . "/../data/htmlpurifier");
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', new HTMLPurifier_AttrDef_Enum(
                array('_blank','_self','_target','_top')
            ));


        if($this->_richHtmlAllowed) { //для прохода данных от HTML-редактора с видео

        	$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
	            'src' => 'URI',
    	        'type' => 'Text',
        	    'width' => 'Length',
	            'height' => 'Length',
    	        'poster' => 'URI',
        	    'preload' => 'Enum#auto,metadata,none',
	            'controls' => 'Text',
	            'muted' => 'Text',
	            'loop' => 'Text',
	            'autoplay' => 'Text',
    	    ));
        	$def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
	            'src' => 'URI',
    	        'type' => 'Text',
        	    'width' => 'Length',
	            'height' => 'Length',
    	        'poster' => 'URI',
        	    'preload' => 'Enum#auto,metadata,none',
	            'controls' => 'Text',
	            'muted' => 'Text',
	            'loop' => 'Text',
	            'autoplay' => 'Text',
    	    ));
        	$def->addElement('source', 'Block', 'Flow', 'Common', array(
	            'src' => 'URI',
    	        'type' => 'Text',
        	));


	        $def->addElement('object', 'Block', 'Flow', 'Common', array(
    	        'data' => 'URI',
        	    'type' => 'Text',
            	'width' => 'Length',
	            'height' => 'Length',
    	    ));

            $def->addElement('iframe', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
                'src' => 'URI',
                'type' => 'Text',
                'width' => 'Length',
                'height' => 'Length',
                'frameborder' => 'Text',
                'allowfullscreen' => 'Text',
                'class' => 'Text',
                'id' => 'Text',
                'title' => 'Text',
            ));
    	}

        // Core.HiddenElements array ( 'script' => true, 'style' => true, )
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($value);
    }

    protected function _allowRichHtml()
    {
        $this->_richHtmlAllowed = true;
    }
}
