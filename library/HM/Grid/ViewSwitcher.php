<?php

class HM_Grid_ViewSwitcher extends HM_Grid_ConfigurableClass
{
    const STATE_DEFAULT = 'default';
    const STATE_TABLE   = 'table';

    protected static $_defaultOptions = array(
        'url' => ''
    );

    protected $_url = '';
    protected $_state = '';
    protected $_visible = true;

    public function __construct($options = array())
    {
        parent::__construct($options);

        $view = $this->getView();

        $url = $this->getOption('url');

        if (is_array($url)) {
            $url = $view->url($url, null, true);
        }

        if (!$url) {
            $url = $view->url();
        }

        $this->_url = $url;

        $this->_initSession();
        $this->_initState();

        $this->_visible = !$this->isAjaxRequest();

    }

    protected function _initSession()
    {
        $request = $this->getRequest();

        $sessionKey = implode('/', array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName(),
        ));

        $session = new Zend_Session_Namespace('viewSwitcher');

        if (empty($session->states)) {
            $session->states = array();
        }

        $states = &$session->states;

        if (!isset($states[$sessionKey])) {
            $states[$sessionKey] = self::STATE_DEFAULT;
        }

        $this->_state = &$states[$sessionKey];
    }

    protected function _initState()
    {
        $state = $this->getRequest()->getParam('viewType');

        if (!$state) {
            $state = $this->_state;
        }

        $this->setState($state);

    }

    public function setVisibility($visible)
    {
        $this->_visible = $visible;
    }

    public function isVisible()
    {
        return $this->_visible;
    }

    public function getState()
    {
        return $this->_state;
    }

    public function setState($state)
    {
        switch ($state) {
            case self::STATE_DEFAULT:
            case self::STATE_TABLE:
                break;
            default:
                $state = self::STATE_DEFAULT;
        }

        $this->_state = $state;

    }

    public function isTableView()
    {
        return ($this->_state === self::STATE_TABLE);
    }

    public function isDefaultView()
    {
        return ($this->_state === self::STATE_DEFAULT);
    }

    public function __toString()
    {
        if (!$this->_visible) {
            return '';
        }

        $view = $this->getView();

        $view->viewType = $this->_state;

        return $view->ViewType('actions', array(
            'url' => $this->_url
        ));

    }
}