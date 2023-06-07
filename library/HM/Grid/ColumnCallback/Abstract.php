<?php

class HM_Grid_ColumnCallback_Abstract
{
    /**
     * @var HM_Grid_AbstractGrid|Bvb_Grid
     */
    protected $_hmGrid = null;

    protected $_defaultServiceName = '';

    /**
     * @param HM_Grid_AbstractGrid|Bvb_Grid $hmGrid
     */
    public function __construct($hmGrid = null)
    {
        $this->_hmGrid = $hmGrid;
    }

    public function __invoke($ids)
    {
        return '';
    }

    protected function _escape($text)
    {
        return $this->getView()->escape($text);
    }

    /**
     * Алиас к $this->getView()->url($urlParams, null, true)
     *
     * @param $urlParams
     * @return string
     */
    protected function _url($urlParams)
    {
        return $this->getView()->url($urlParams, null, true);
    }

    /**
     * @param string $serviceName
     *
     * @return HM_Service_Abstract
     */
    public function getService($serviceName = '')
    {
        if ($serviceName === '') {
            $serviceName = $this->_defaultServiceName;
        }

        return Zend_Registry::get('serviceContainer')->getService($serviceName);
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function isAjaxRequest()
    {
        return static::getRequest()->isXmlHttpRequest();
    }

    /**
     * @return HM_View_Extended
     */
    public function getView()
    {
        return Zend_Registry::get('view');
    }

    /**
     * Возвращает коллбэк для столбца грида.
     *
     * @params Обычные параметры коллбэка из Bvb_Grid
     *
     * @return array
     */
    public function getCallback()
    {
        return array(
            'function' => $this,
            'params'   => func_get_args()
        );
    }

}