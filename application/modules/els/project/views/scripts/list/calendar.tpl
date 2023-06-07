<?php
if(!Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),  HM_Role_Abstract_RoleModel::ROLE_ENDUSER)){
    echo $this->headSwitcher(array('module' => 'project', 'controller' => 'list', 'action' => 'calendar', 'switcher' => 'calendar'));
}
echo $this->calendar(
    $this->source,
    array(
        //'eventDropFunctionName'   => 'sendCalendarChange',
        //'eventResizeFunctionName' => 'sendCalendarChange',
        'editable'                => $this->editable,
        'saveDataUrl'             => $this->url(array('module'=>'project', 'controller'=>'list','action'=>'save-calendar'))
    )
);
?>