<?php

/**
 * Универсальный метод создания action удаления.
 * @example HM_DataGrid_Action_Edit::create($this, $this->getView()->icon('edit'), ['module' => 'user', 'controller' => 'list', 'params' => ['MID']])
 *
 *
 */
class HM_DataGrid_Action_Delete extends HM_DataGrid_Action
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $self = parent::create($dataGrid, $name);

        $request = $dataGrid->getView()->getRequest();

        $module     = isset($options['module']) && $options['module'] ? $options['module'] : $request->getParam('module');
        $controller = isset($options['controller']) && $options['controller'] ? $options['controller'] : $request->getParam('controller');
        $action     = isset($options['action']) && $options['action'] ? $options['action'] : 'delete';

        $self->setName($name);
        $self->setUrl(
            array(
                'module' => $module,
                'controller' => $controller,
                'action' => $action,
            )
        );

        $self->setParams($options['params']);

        return $self;
    }
}