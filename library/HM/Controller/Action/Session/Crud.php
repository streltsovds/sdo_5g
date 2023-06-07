<?php
/*
 * @todo: Рефакторить! 
 * 
 */
class HM_Controller_Action_Session_Crud extends HM_Controller_Action_Crud
{
    protected $_session;
    protected $_vacancy;
    protected $_newcomer;
    protected $_reserve;
    protected $_currentPosition;

    protected $service     = 'AtSession';
    protected $idParamName = 'session_id';
    protected $idFieldName = 'session_id';
    protected $id          = 0;

    public function init()
    {
        parent::init();

        if ($vacancyId = $this->_getParam('vacancy_id', 0)) {
            
            $this->_vacancy  = $this->getOne(
                $this->getService('RecruitVacancy')->findDependence('Session', $vacancyId)
            );
	    if ($this->_vacancy->session) {
                $this->_session = $this->_vacancy->session->current();
                $this->_request->setParam('vacancy_id', $vacancyId);
	    } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не существует сессия оценки')));
                $this->_redirector->gotoUrl('/', array('prependBase' => false));
            }
            
        } elseif ($newcomerId = $this->_getParam('newcomer_id', 0)) {
            
            $this->_newcomer  = $this->getOne(
                $this->getService('RecruitNewcomer')->findDependence('Session', $newcomerId)
            );
            $this->_session = $this->_newcomer->session->current();
            $this->_request->setParam('newcomer_id', $newcomerId);
                        
        } elseif ($reserveId = $this->_getParam('reserve_id', 0)) {

            $this->_reserve  = $this->getOne(
                $this->getService('HrReserve')->findDependence('Session', $reserveId)
            );
            $this->_session = $this->_reserve->session->current();
            $this->_request->setParam('reserve_id', $reserveId);

        } else {
        
            if ($sessionId = $this->_getParam('session_id', 0)) {
                if (count($sessions = $this->getService('AtSession')->findDependence(array('Vacancy', 'Newcomer', 'Reserve'), $sessionId))) {
                    $this->_session = $sessions->current();
                    if (count($this->_session->vacancy)) {
                        $this->_vacancy = $this->_session->vacancy->current();
                        $this->_request->setParam('vacancy_id', $this->_vacancy->vacancy_id);
                    } elseif (count($this->_session->newcomer)) {
                        $this->_newcomer = $this->_session->newcomer->current();
                        $this->_request->setParam('newcomer_id', $this->_newcomer->newcomer_id);
                    } elseif (count($this->_session->reserve)) {
                        $this->_reserve = $this->_session->reserve->current();
                        $this->_request->setParam('reserve_id', $this->_reserve->reserve_id);
                    }
                }
            }
        }
        
        if (!$this->isAjaxRequest()) {
            
            if ($this->_vacancy) {
                $this->view->setExtended(
                    array(
                        'subjectName' => 'RecruitVacancy',
                        'subjectId' => $this->_vacancyId,
                        'subjectIdParamName' => 'vacancy_id',
                        'subjectIdFieldName' => 'vacancy_id',
                        'extraSubjectIdParamName' => 'session_id',
                        'extraSubjectId' => $this->_vacancy->session_id,
                        'subject' => $this->_vacancy
                    )
                );
            } elseif ($this->_newcomer) {

                $this->view->setExtended(
                    array(
                        'subjectName' => 'RecruitNewcomer',
                        'subjectId' => $this->_newcomerId,
                        'subjectIdParamName' => 'newcomer_id',
                        'subjectIdFieldName' => 'newcomer_id',
                        'extraSubjectIdParamName' => 'session_id',
                        'extraSubjectId' => $this->_newcomer->session_id,
                        'subject' => $this->_newcomer
                    )
                );
                
            } elseif ($this->_reserve) {

                $this->view->setExtended(
                    array(
                        'subjectName' => 'HrReserve',
                        'subjectId' => $this->_reserve->reserve_id,
                        'subjectIdParamName' => 'reserve_id',
                        'subjectIdFieldName' => 'reserve_id',
                        'extraSubjectIdParamName' => 'session_id',
                        'extraSubjectId' => $this->_reserve->session_id,
                        'subject' => $this->_reserve
                    )
                );

            } elseif ($this->_session) {
                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->_session->session_id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $this->_session
                    )
                );
                
                // эти контекстные меню нужны только рук-лю и только при регуляоной оценке 
                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
                    if (!($this->_currentPosition = $this->getService('User')->isManager(false, true))) {
                        $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page7'));
                    }
                    if (count($this->_session->vacancy) || count($this->_session->newcomer)) {
                        $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page2'));
                        $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page7'));
                    }
                    
                }                
            } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не существует сессия оценки')));
                $this->_redirector->gotoUrl('/', array('prependBase' => false));
            }
        }        
    }
}