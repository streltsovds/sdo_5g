<?php

/**
 * Абстрактный класс, содержащий структуру и общий конструктор для всех коллбэков
 */
abstract class HM_DataGrid_Column_Callback_Abstract
{
    private $value;

    public function __construct(HM_DataGrid $dataGrid, array $args)
    {
        $this->value = array(
            'function' => array($this, 'callback'),
            'params'   => array_merge(array($dataGrid), $args)
        );
    }

    static public function create($dataGrid, array $args)
    {
        $self  = new static($dataGrid, $args);
        return $self->getValue();
    }

    public function getView()
    {
        return Zend_Registry::get('view');
    }

    public function getService($service)
    {
        return Zend_Registry::get('serviceContainer')->getService($service);
    }

    public function getValue()
    {
        return $this->value;
    }

    abstract public function callback(...$args);
}