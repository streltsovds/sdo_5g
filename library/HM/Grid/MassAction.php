<?php

class HM_Grid_MassAction extends HM_Grid_AbstractClass
{
    /**
     * @var HM_Grid_AbstractGrid
     */
    protected $_hmGrid = null;
    protected $_url = '';
    protected $_caption = '';
    protected $_confirm = null;
    protected $_view = null;

    public function __construct($options = array())
    {
        $default = array(
            'hmGrid' => null,
            'url' => '/',
            'caption' => 'New mass action',
            'confirm' => null
        );

        $options = array_merge($default, $options);

        $this->_hmGrid  = $options['hmGrid'];
        $this->_caption = $options['caption'];
        $this->_confirm = $options['confirm'];
        $this->_view    = $this->_hmGrid->getView();

        if ($this->_urlAllowed($options['url'])) {
            $this->_setUrl($options['url']);
            $this->_getBvbGrid()->addMassAction($this->_url, $this->_caption, $this->_confirm);
        }

    }

    protected function _setUrl($url)
    {
        if (is_array($url)) {
            $request = $this->getRequest();

            $defaultUrlParams = array(
                'module'     => $request->getModuleName(),
                'controller' => $request->getControllerName(),
                'action'     => $request->getActionName()
            );

            $url = array_merge($defaultUrlParams, $url);

            $url = $this->_view->url($url, null, true);
        }

        $this->_url = $url;
    }

    protected function _getBvbGrid()
    {
        return $this->_hmGrid->getBvbGrid();
    }

    public function addSelect($name, $selectOptions, $allowMultiple = false)
    {
        $this->_getBvbGrid()->addSubMassActionSelect($this->_url, $name, $selectOptions, $allowMultiple);
    }

    public function addInput($url, $name, $options)
    {
        $this->_getBvbGrid()->addSubMassActionInput($url, $name, $options);
    }

    public function addAutoComplete($name, $options)
    {
        $default = array(
            'DataUrl'        => '/',
            'MaxItems'       => 1,
            'AllowNewItems'  => false
        );

        $options = array_merge($default, $options);

        if (is_array($options['DataUrl'])) {
            $options['DataUrl'] = $this->_view->url($options['DataUrl'], null, true);
        }

        $this->_getBvbGrid()->addSubMassActionFcbk($this->_url, $name, $options);
    }

}