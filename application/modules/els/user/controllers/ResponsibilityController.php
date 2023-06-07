<?php
class User_ResponsibilityController extends HM_Controller_Action_User
{
    protected $_userId;
    /** @var HM_User_UserModel $_user */
    protected $_user;

    public function init()
    {
        parent::init();

        $userId = $this->_getParam('user_id', 0);
        $this->_userId = $userId;

        $this->_user = $this->getOne($this->getService('User')->find($userId));
        if (!$this->_user ) {
            $this->_redirector->gotoSimple('index', 'list', 'user');
        }
    }

    public function assignAction()
    {
        /** @var HM_Responsibility_ResponsibilityService $respService */
        $respService = $this->getService('Responsibility');

        $form = new HM_Form_Responsibility();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getParams())) {

            $values = $form->getValues();
            if (!$values['useResponsibility']){
                $values['soid'] = array();
            }
            $subjectType = ($values['role'] == 'ot') ?
                HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT :
                HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT;
            $respService->set($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, $values['soid']);
            $respService->set($this->_user->MID, $subjectType,  $values['subjects']);
            $respService->set($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_GROUP,    $values['groups']);
            $respService->set($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM, $values['programms']);

            $params = array('user_id' => $this->_user->MID);
            if ($values['role']) $params[$values['role']] = 1;
            $this->_flashMessenger->addMessage(_('Области ответственности успешно изменены'));
            $this->_redirector->gotoSimple('assign', 'responsibility', 'user', $params);
        } else {
            $ot   = $this->getRequest()->getParam('ot'  );
            $dean = $this->getRequest()->getParam('dean');
            $supervisor = $this->getRequest()->getParam('supervisor');
            $values = array(
                'limited' => 0,
                'subjects' => array()
            );

            if (count($responsibility = $respService->get($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))) {
                $values['soid'] = array_shift($responsibility);
                $values['useResponsibility'] = 1;
            }

            if ($ot) {
                if (count($responsibility = $respService->get($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT))) {
                    $values['limited']  = HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT;
                    $values['subjects'] = $responsibility;
                }
            } elseif ($dean || $supervisor) {
                if (count($responsibility = $respService->get($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT))) {
                    $values['limited']  = HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT;
                    $values['subjects'] = $responsibility;
                } elseif (count($responsibility = $respService->get($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_GROUP))) {
                    $values['limited']  = HM_Responsibility_ResponsibilityModel::TYPE_GROUP;
                    $values['groups'] = $responsibility;
                } elseif (count($responsibility = $respService->get($this->_user->MID, HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM))) {
                    $values['limited']  = HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM;
                    $values['programms'] = $responsibility;
                }
            }

            $form->populate($values);

        }
        $this->view->form = $form;

    }
}

