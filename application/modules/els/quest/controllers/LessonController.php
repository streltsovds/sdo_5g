<?php
/**
 * Общая реализация оценочного мероприятия на основе Quest
 * Вся специфика в helper'е QuestContextSubject
 */
class Quest_LessonController extends HM_Controller_Action_Multipage_Quest
{
    const NAMESPACE_MULTIPAGE = 'lesson-multipage';

    public function init()
    {
        $this->setNamespaceMultipage(self::NAMESPACE_MULTIPAGE);
        parent::init();
    }

    public function _initModel()
    {
        if (empty($this->_persistentModel)) {
            $this->_persistentModel = $this->_getPersistentModel();
        }
    }

    public function _redirectToIndex($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS, $redirectUrl = false)
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {
            if($redirectUrl) {
                $this->_redirector->gotoUrl($this->_redirector->getRelativePath($redirectUrl));
            } elseif ($this->_persistentModel) {
                $this->_redirector->gotoUrl($this->_persistentModel->getRedirectUrl());
            } else {
                // это совсем нехорошая ситуация ,напрмер кнопка Back в браузере
                $this->_redirector->gotoSimple('index', 'index', 'default');
            }
        }
    }

    public function _redirectToMultipage($msg = '')
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {
            $this->_redirector->gotoSimple('view', 'lesson', 'quest', array('quest_id' => $this->_getMultipageId()));
        }
    }

    public function _getBaseUrl()
    {
        return array('module' => 'quest', 'controller' => 'lesson');
    }

    public function _getPersistentModel($mode = null, $contextEventId = null, $contextEventType = null)
    {
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            // preview занятия
            return parent::_getPersistentModel(HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF);
        }

        $lessonId = $this->_getParam('lesson_id');
        $model = parent::_getPersistentModel($mode, $lessonId, HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING);// Возвращает модель тек. попытки тестирования

        if ($lessonId) {
            $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->fetchAll(array('SHEID = ?' => $lessonId)));
            $model->setContextModel($lesson);
        }

        return $model;
    }


    protected function _isSuspendable()
    {
        $model = $this->_persistentModel->getModel();
        return ($model['quest']->type == HM_Quest_QuestModel::TYPE_TEST) ? false : true;
    }

    public function getControllerModel()
    {
        return $this->_persistentModel->getModel();
    }

    public function _isExecutable()
    {
        $globalError = parent::_isExecutable();
        if ($globalError !== true) {
            return $globalError;
        }

        $contextModel = $this->_persistentModel->getContextModel();
        if($contextModel){
            if (!$contextModel->isExecutable(true)) {
                return _('Это занятие вам более не доступно');
            }
        }
        return true;
    }

    /** @see HM_Controller_Action_Trait_Multipage::viewAction() */
}
