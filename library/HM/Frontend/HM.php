<?php

class HM_Frontend_HM extends HM_View_Helper_Abstract
{
    protected static $_instance;
    protected $_view = null;
    protected $_inlineScript = null;

    public static function get()
    {
        if (!self::$_instance) {
            new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        self::$_instance = $this;

        $this->_view = Zend_Registry::get('view');
        $this->_inlineScript = $this->_view->inlineScript();
    }

    public function createComponent($className, $config = array())
    {
        $result = '';

        if (!isset($config['renderTo'])) {

            $id = $this->_view->id('hm-placement');

            $result = '<span style="display: none" id="'.$id.'"></span>';

            $config['renderTo'] = '#'.$id;
            $config['replaceRenderTo'] = true;

        }

        $this->create($className, $config);

        return $result;

    }

    public function create($className, $config = array())
    {
        $inlineScript = $this->_inlineScript;
        $inlineScript->captureStart();
        echo 'HM.create('.json_encode($className).', '.json_encode($config).');';
        $inlineScript->captureEnd();

    }

}