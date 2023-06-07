<?php
class HM_Controller_Action_Resource extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Context;

    protected $_resourceId = null;
    protected $_resource = null;

    protected $_backUrl = null;

    public function init()
    {
        $isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $this->view->lessonId = $lessonId = $this->_getParam('lesson_id');
        $this->view->resourceId = $this->_resourceId = $resourceId = (is_array( $this->_getParam('resource_id') )) ? $this->_getLastId($this->_getParam('resource_id')) : $this->_getParam('resource_id');
        $this->view->resource = $this->_resource = $this->getOne($this->getService('Resource')->find($resourceId));

        if ($this->_resource) {
            /* Логирование захода пользователя в ресурс */
            $this->getService('Session')->toLog(array('resource_id' => $resourceId));

            $this->initContext($this->_resource);

            $this->view->subjectId = $subjectId = $this->getParam('subject_id', $this->_resource->subject_id);

            if ($lessonId && $this->getService('User')->isEndUser()) {
                $lesson = $this->getService('Lesson')->getOne(
                    $this->getService('Lesson')->findDependence('Subject', $lessonId)
                );

                if($lesson) {

                    // В контексте курса показываем следующее занятие
                    $this->view->replaceSidebar('subject', 'subject-lesson', [
                        'model' => $lesson,
                    ]);
                }
            } elseif($this->_resource) {
                $this->view->replaceSidebar('subject', 'resource', [
                    'model' => $this->_resource
                ]);
            }

            if ($isEnduser) {

                if ($subjectId) {

                    // всегда кидаем на план занятий
                    $this->_backUrl = $this->view->url([
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'index',
                        'subject_id' => $subjectId,
                    ], null, true);

                    $this->view->setSwitchContextUrls(false);

                } else{

                    $this->_backUrl = $this->view->url([
                        'module' => 'kbase',
                        'controller' => 'index',
                        'action' => 'index',
                    ], null, true);
                }

            } else {

                if ($subjectId) {

                    // кидаем на план занятий или на исходные материалы
                    if ($lessonId) {
                        $this->_backUrl = $this->view->url([
                            'module' => 'subject',
                            'controller' => 'lessons',
                            'action' => 'edit',
                            'subject_id' => $subjectId,
                        ], null, true);
                    } else {
                        $this->_backUrl = $this->view->url([
                            'module' => 'subject',
                            'controller' => 'lessons',
                            'action' => 'edit',
                            'subject_id' => $subjectId,
                        ], null, true);
                    }

                    $this->view->setSwitchContextUrls(false);

                } else{
                    $this->_backUrl = $this->view->url([
                        'module' => 'kbase',
                        'controller' => 'resources',
                        'action' => 'index',
                    ], null, true);
                }
            }

            $this->view->setBackUrl($this->_backUrl);
        }

        parent::init();
    }
    public function _getLastId( $idList ) {
        if (($idList) && is_array($idList) && count($idList)) {
            for ($i = count($idList) - 1; $i >= 0; $i--) {
                if ( $idList[$i] > 0 ) {
                    $this->_setParam('resource_id', $idList[$i]);
                    return $idList[$i];
                }
            }
        } elseif (($idList) && ($idList > 0) ){
            return $idList;
        }
        return 0;
    }
}