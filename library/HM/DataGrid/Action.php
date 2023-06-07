<?php

/**
 *
 */
class HM_DataGrid_Action
{
    protected $name;
    protected $url;
    protected $params;
    protected $confirm;
    protected $options;

    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = new self;
        $self->setName($name);

        $request = $dataGrid->getView()->getRequest();

        $module = isset($options['module']) ? $options['module'] : $request->getParam('module');
        $controller = isset($options['controller']) ? $options['controller'] : $request->getParam('controller');
        $action = isset($options['action']) ? $options['action']: 'index';

        $self->setName($name);
        $self->setUrl(
            array(
                'module' => $module,
                'controller' => $controller,
                'action' => $action,
            )
        );

        if (isset($options['params'])) $self->setParams($options['params']);

        return $self;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * @param mixed $confirm
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}