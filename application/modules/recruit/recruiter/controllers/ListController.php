<?php
class Recruiter_ListController extends HM_Controller_Action_Vacancy
{
    use HM_Controller_Action_Trait_Grid;

    const ACTION_INSERT    = 1;
    const ACTION_UPDATE    = 2;
    const ACTION_DELETE    = 3;
    const ACTION_DELETE_BY = 4;

    protected function _getMessages() {

        return array(
            self::ACTION_INSERT    => _('Менеджер успешно добавлен'),
            self::ACTION_UPDATE    => _('Менеджер успешно обновлен'),
            self::ACTION_DELETE    => _('Менеджер успешно удален'),
            self::ACTION_DELETE_BY => _('Менеджеры успешно удалены')
        );
    }

    public function indexAction()
    {
        session_start();
        $all = $this->_getParam('all', $_SESSION['all_recruiters']);
        $_SESSION['all_recruiters'] = $all;
        $default = new Zend_Session_Namespace('default');
        if (!$this->isAjaxRequest() && isset($default->grid['recruit-recruiter-list']['grid']['all'])) {
            $all = (int) $default->grid['recruit-recruiter-list']['grid']['all'];
        }

        $gridId = 'grid';

        $select = $this->getService('RecruitVacancy')->getSelect();
        
        if (!$all) {
            $select->from(array('r' => 'recruiters'), array(
                'r.recruiter_id',
                'p.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' '), p.FirstName), ' '), p.Patronymic)"),
                'assigned' => new Zend_Db_Expr('1'),
                'events' => new Zend_Db_Expr("COUNT(ase.session_event_id)"),
            ))
                ->join(array('rvr' => 'recruit_vacancy_recruiters'), $this->quoteInto('rvr.recruiter_id = r.recruiter_id AND rvr.vacancy_id = ?', $this->_vacanacyId), array())
                ->join(array('p' => 'People'), 'p.MID = r.user_id', array())
                ->joinLeft(array('rv' => 'recruit_vacancies'), 'rvr.vacancy_id = rv.vacancy_id', array())
                ->joinLeft(array('ase' => 'at_session_events'), 'ase.session_id = rv.session_id AND ase.respondent_id = r.user_id', array());
        } else {
            
            $sorting = $this->_request->getParam("order{$gridId}");
            if ($sorting == ""){
                $this->_request->setParam("order{$gridId}", 'assigned_DESC');
            }
            
//            $responsibleUsers = $this->getService('Responsibility')->getResponsibleForPosition($this->_vacancy->position_id);

            $select->from(array('r' => 'recruiters'), array(
                'r.recruiter_id',
                'p.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' '), p.FirstName), ' '), p.Patronymic)"),
                'assigned' => new Zend_Db_Expr('(CASE WHEN rvr.recruiter_id IS NULL THEN 0 ELSE 1 END)'),
                'events' => new Zend_Db_Expr("0"),
            ))
                ->join(array('p' => 'People'), 'r.user_id = p.MID', array())
                ->joinLeft(array('rsp' => 'responsibilities'), 'p.MID = rsp.user_id', array())
                ->joinLeft(array('rvr' => 'recruit_vacancy_recruiters'), $this->quoteInto('rvr.recruiter_id = r.recruiter_id AND rvr.vacancy_id = ?', $this->_vacanacyId), array())
                ->where('p.blocked != 1');
            
//                if (count($responsibleUsers)) {
//                    $select->orWhere('rsp.user_id IN (?)', array_keys($responsibleUsers));
//                }
        }
        
        $select->group(array('r.recruiter_id', 'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'rvr.recruiter_id', 'r.user_id', 'rvr.vacancy_id'));
//         exit($select->__toString());
        $gridColumns = array(
            'recruiter_id' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО')
            ),
            'events' => array('hidden' => true),
//            array(
//                'title' => _('Количество анкет'),
//                //'decorator' => '<a href="'.$this->view->url(array('module' => 'session', 'controller' => 'test', 'action' => 'list', 'gridmod' => null, 'user_fio' => '')).'{{name}}">{{events}}</a>'
//            ),
            'assigned' => array(
                'title' => _('Назначен'),
                'callback' => array(
                    'function' => array($this, 'updateAssigned'),
                    'params' => array('{{assigned}}')
                )
            )
        );
        $gridFilters = array(
            'fio' => null,
            'assigned' => array('values' => array(_('Нет'), _('Да')))
        );
        
        $grid = $this->getGrid($select, $gridColumns, $gridFilters, $gridId);

        $grid->setGridSwitcher(array(
            array('name' => 'vacancy_recruters', 'title' => _('специалисты данной сессии'), 'params' => array('all' => 0)),
            array('name' => 'all', 'title' => _('все специалисты по подбору'), 'params' => array('all' => 1)),
        ));
        
        if ($all) {
            $grid->addMassAction(
                array(
                    'module' => 'recruiter',
                    'controller' => 'list',
                    'action' => 'assign'
                ),
                _('Назначить менеджеров на данную сессию подбора'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        } else {
            $grid->addMassAction(
                array(
                    'module' => 'recruiter',
                    'controller' => 'list',
                    'action' => 'unassign'
                ),
                _('Отменить назначение'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $grid->setClassRowCondition("'{{assigned}}' == 1", "success");

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();

    }
    
    public function updateAssigned($assigned)
    {
        return ((int) $assigned) ? _('Да') : _('Нет');
    }

    public function assignAction()
    {
        $vacancyId = $this->_getParam('vacancy_id', false);
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $ids = explode(',', $postMassIds);
        foreach ($ids as $recruiterId) {
            $this->getService('RecruitVacancyRecruiterAssign')->assign($vacancyId, $recruiterId);
        }
        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Специалисты успешно назначены')
        ));
        $this->_redirector->gotoSimple('index', 'list', 'recruiter', array('vacancy_id' => $vacancyId));
    }

    public function unassignAction()
    {
        $vacancyId = $this->_getParam('vacancy_id', false);
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $ids = explode(',', $postMassIds);
        foreach ($ids as $recruiterId) {
            $this->getService('RecruitVacancyRecruiterAssign')->unassign($vacancyId, $recruiterId);
        }
        $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Назначения успешно отменены')
        ));
        $this->_redirector->gotoSimple('index', 'list', 'recruiter', array('vacancy_id' => $vacancyId));

    }


}

