<?php
class HM_Controller_Action_Session extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    protected $_sessionId;
    protected $_session;

    protected $_vacancy;
    protected $_newcomer;
    protected $_reserve;

    public function init()
    {
        if ($vacancyId = $this->_getParam('vacancy_id', 0)) {

            $this->_vacancy = $this->getOne(
                $this->getService('RecruitVacancy')->findDependence('Session', $vacancyId)
            );

            if ($this->_vacancy) {

                $this->initContext($this->_vacancy);
                $this->view->addSidebar('vacancy', [
                    'model' => $this->_vacancy,
                ]);

                if ($this->_vacancy->session) {
                    $this->_session = $this->_vacancy->session->current();
                    $this->_request->setParam('vacancy_id', $vacancyId);
                } else {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не существует сессия оценки')));
                    $this->_redirector->gotoUrl('/', array('prependBase' => false));
                }

            }

        } elseif ($newcomerId = $this->_getParam('newcomer_id', 0)) {

            $this->_newcomer = $this->getOne(
                $this->getService('RecruitNewcomer')->findDependence(['Evaluation', 'User', 'Cycle', 'Session'], $newcomerId)
            );

            if ($this->_newcomer) {

                $this->getService('Process')->initProcess($this->_newcomer);
                $this->initContext($this->_newcomer);

                $this->view->addSidebar('newcomer', [
                    'model' => $this->_newcomer,
                ]);

                $this->_session = $this->_newcomer->session->current();
                $this->_request->setParam('newcomer_id', $newcomerId);
            }
        } elseif ($reserveId = $this->_getParam('reserve_id', 0)) {

            $this->_reserve = $this->getOne(
                $this->getService('HrReserve')->findDependence(['Evaluation', 'User', 'Cycle', 'Session'], $reserveId)
            );

            if ($this->_reserve) {

                $this->getService('Process')->initProcess($this->_reserve);
                $this->initContext($this->_reserve);

                $this->view->addSidebar('reserve', [
                    'model' => $this->_reserve,
                ]);

                $this->_session = $this->_reserve->session->current();
                $this->_request->setParam('reserve_id', $reserveId);
            }
        } else {

            $this->_sessionId = $sessionId = $this->_getParam('session_id', 0);
            $this->_session = $this->getOne($this->getService('AtSession')->find($sessionId));

            if ($this->_session && ($this->_session->programm_type == HM_Programm_ProgrammModel::TYPE_ASSESSMENT)) {
                $this->getService('Process')->initProcess($this->_session);
                $this->initContext($this->_session);

                $this->view->addSidebar('session', [
                    'model' => $this->_session,
                ]);

                $backUrl =  [
                    'module' => 'session',
                    'controller' => 'list',
                    'action' => $this->getService('User')->isEnduser() ? 'my' : 'index',
                    'session_id' => null,
                ];

            } else {

                // @todo: auto-detect вакансий/адаптаций по ID сессии оценки
            }
        }

        if ($backUrl) {
            $this->view->setBackUrl($this->view->url($backUrl));
        }

        parent::init();
    }
}