<?php
class HM_Controller_Plugin_Loader extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $frontController = Zend_Controller_Front::getInstance();

        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath'  => $frontController->getModuleDirectory(),
            'namespace' => 'HM',
        ));
        $resourceLoader
            ->addResourceType('acl', 'acls/', 'Acl')
            ->addResourceType('parent_from', '../forms/', 'ParentForm')
            ->addResourceType('form', 'forms/', 'Form');

        // для гридов новая логика именования классов
        $moduleName = $frontController->getRequest()->getModuleName();

        $camelCaseFilter = new Zend_Filter_Word_DashToCamelCase();
        $moduleName = ucfirst($camelCaseFilter->filter($moduleName));

        $resourceLoader->addResourceType('select', 'selects/', "{$moduleName}_Select");
        $resourceLoader->addResourceType('data-grid', 'data-grids/', "{$moduleName}_DataGrid");
        $resourceLoader->addResourceType('actions', 'data-grids/actions/', "{$moduleName}_DataGrid_Action");
        $resourceLoader->addResourceType('mass-actions', 'data-grids/mass-actions/', "{$moduleName}_DataGrid_MassAction");
        $resourceLoader->addResourceType('callbacks', 'data-grids/callbacks/', "{$moduleName}_DataGrid_Callback");
        $resourceLoader->addResourceType('decorators', 'data-grids/decorators/', "{$moduleName}_DataGrid_Decorator");
        $resourceLoader->addResourceType('filters', 'data-grids/filters/', "{$moduleName}_DataGrid_Filter");
        $resourceLoader->addResourceType('grid', 'grids/', "{$moduleName}_Grid");
        $resourceLoader->addResourceType('view', 'views/', "{$moduleName}_View");
    }
}
