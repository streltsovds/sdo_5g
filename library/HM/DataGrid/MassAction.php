<?php

/**
 *
 */
class HM_DataGrid_MassAction
{
    protected $name;
    protected $url;
    protected $params;
    protected $confirm;
    protected $sub;
    protected $options;

    const SUB_MASS_ACTION_INPUT  = 'addSubMassActionInput';
    const SUB_MASS_ACTION_FCBK   = 'addSubMassActionFcbk';
    const SUB_MASS_ACTION_SELECT = 'addSubMassActionSelect';

    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = new self;
        $self->setName($name);
        $self->setOptions($options);

        // Для массовых действий пожалуй всегда лучше уточнять.
        $self->setConfirm($self->confirm ?: _('Вы уверены?'));

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
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * @param mixed $sub
     */
    public function setSub($sub)
    {
        $this->sub = $sub;
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