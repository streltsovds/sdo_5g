<?php

class HM_Grid_Menu extends HM_Grid_AbstractClass
{
    protected $_items = array();

    /**
     * Добавляет элемент меню над гридом
     *
     * @param $options - смотри массив $default
     */
    public function addItem($options)
    {
        static $view = null;

        $default = array(
            'urlParams' => array(),
            'title'     => '',
            'class'     => '',
            'onclick'     => '',
            'target'    => false
        );

        if ($view === null) {
            $view = $this->getView();
        }

        $options = array_merge($default, $options);

        $urlParams = $options['urlParams'];

        if (is_array($urlParams)) {

            if (!$this->_urlAllowed($urlParams)) {
                return;
            }

            $options['url'] = $view->url($urlParams);

        } else {
            $options['url'] = $options['urlParams'];
        }

        unset($options['urlParams']);

        $this->_items[] = $options;

    }

    public function __toString()
    {
        if (!empty($this->_items) && !$this->isAjaxRequest()) {

            $frontController = Zend_Controller_Front::getInstance();

            $baseUrl = $frontController->getBaseUrl();

            $frontController->setBaseUrl(''); // очередной хак

            $result = $this->getView()->Actions('gridMenu', $this->_items);

            $frontController->setBaseUrl($baseUrl);

            return $result;
        }

        return '';
    }
}