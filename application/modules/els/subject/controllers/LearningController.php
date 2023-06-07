<?php
class Subject_LearningController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    public function init() {
        parent::init();
        $this->view->withoutContextMenu = $this->_getParam('withoutContextMenu', true);
    }


    public function needAction() 
    {
        $defaultParent = $this->getService('Orgstructure')->getDefaultParent();
        if ($defaultParent && isset($defaultParent->soid)) {
            $defaultParentSoid = $defaultParent->soid;
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR, 
                'message' => _('Ваша учетная запись не связана ни с одним подразделением оргструктуры')
            ));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        $orgId = (int) $this->_getParam('key', $defaultParentSoid);
        $switcher = $this->getSwitcherSetOrder(null, 'fio_ASC');

        // ВНИМАНИЕ!
        // здесь есть проблема: фильтр, установленный таким образом, невозможно сбросить крестиком; невозможно использовать фильтр "Все";
        // если эту проблему решать, не должно пропасть другое свойство данного фильтра: он по умолчинию установлен в значение "ШЕ" и при переключении фокуса в дереве своё значение не теряет!  
        if ($this->_request->getParam('typegrid') === null) {
             $this->_request->setParam('typegrid', HM_Orgstructure_OrgstructureModel::TYPE_POSITION);
        }
        $orgService = $this->getService('Orgstructure');
        $select    = $orgService->getSelect();
        $subSelect = clone $select;
        
        $subSelect->distinct()->from(array('s_sub1' => 'subjects'), array(
            's_subid' => 's_sub1.subid',
            'g_mid' => 'g_sub.MID',
            's_mid' => 'st_sub.MID',
        ));
        
        $subSelect
            ->joinLeft(array('s_sub2' => 'subjects'), 's_sub1.subid = s_sub2.base_id', array())
            ->joinLeft(array('g_sub'  => 'graduated'), 's_sub2.subid = g_sub.CID', array())
            ->joinLeft(array('st_sub' => 'Students'), 'st_sub.CID = s_sub2.subid', array())
            ->joinLeft(array('cm_sub' => 'courses_marks'), 's_sub2.subid = cm_sub.cid', array());
        
        $subSelect->where('s_sub2.base_id IS NOT NULL AND s_sub2.base_id > ?', 0);
        $subSelect->where('((g_sub.SID IS NOT NULL) AND (cm_sub.mark > 0)) OR (st_sub.MID IS NOT NULL)');
        
        
        $select->from(array('p'  => 'People'), array(
            'so.soid',
            'org_id'        => 'so.soid',
            'fio'           => new Zend_Db_Expr(
                "CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"
            ),
            'MID'           => 'so.mid',
            'user_id'       => 'so.mid',
            'criteria_test' => 'ct.name',
            'subject'       => 's.name',
            'subject_id'    => 's.subid',
        ));

        $cycleId = 0;
        if ($cycle = $this->getService('Cycle')->getCurrent()) {
            $cycleId = $cycle->cycle_id;    
        }
            
        $select
            ->joinInner(array('so'      => 'structure_of_organ'), 'so.mid = p.MID', array())
//            ->joinLeft(array('ap'       => 'at_profiles'), 'so.profile_id = ap.profile_id', array())
            ->joinLeft(array('apc'      => 'at_profile_criterion_values'), 'apc.profile_id=so.profile_id', array())
            ->joinLeft(array('ct'       => 'at_criteria_test'), 'ct.criterion_id = apc.criterion_id', array())
            ->joinLeft(array('s'        => 'subjects'), 's.subid = ct.subject_id', array())
            ->joinLeft(array('g'        => 'graduated'), 's.subid = g.CID AND p.MID = g.MID', array())
            ->joinLeft(array('st'       => 'Students'), 's.subid = st.CID AND st.MID = p.MID', array())
            ->joinLeft(array('cm'       => 'courses_marks'), 's.subid = cm.cid AND p.MID = cm.mid', array())
            ->joinLeft(array('session'  => new Zend_Db_Expr('('.$subSelect.')')),
//                'session.g_mid = p.MID OR session.s_mid = p.MID', array());
                '(session.g_mid = p.MID AND s_subid = s.subid) OR (session.s_mid = p.MID AND s_subid = s.subid)', array());
                
        $where_pblocked = 'p.blocked = 0 OR p.blocked IS NULL';
        $select->where($where_pblocked);

        //Тут добавляем $switcher

        $where_filter = array();
        if ($orgId > 0) {
            $orgElement = $this->getService('Orgstructure')->find($orgId)->current();

            if ($switcher) {
                $where_filter[] = array(
                    'query' => 'so.lft > ?',
                    'value' => $orgElement->lft
                );
                
                $where_filter[] = array(
                    'query' => 'so.rgt < ?',
                    'value' => $orgElement->rgt
                );

            } else {
                $where_filter[] = array(
                    'query' => 'so.lft > ?',
                    'value' => $orgElement->lft
                );
                $where_filter[] = array(
                    'query' => 'so.rgt < ?',
                    'value' => $orgElement->rgt
                );
                $where_filter[] = array(
                    'query' => 'so.level = ?',
                    'value' => $orgElement->level + 1
                );
            }
        } else {
            if (!$switcher) {
                $where_filter[] = array(
                    'query' => 'so.level = ?',
                    'value' => 0
                );
            }
        }
        foreach ($where_filter as $value) {
            $select->where($value['query'], $value['value']);
        }
        
        $where_blocked = 'so.blocked = ?';
        $select->where($where_blocked, 0);
        $where_subid = 's.subid <> ? OR s.subid IS NOT NULL';
        $select->where($where_subid, 0);
        $select->where('s.base_id IS NULL OR s.base_id = ?', 0);
        $select->where('(g.SID IS NULL) OR (cm.mark = ?)', HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF);
        $select->where('st.MID IS NULL');
        $select->where('session.s_subid IS NULL');
        
        $select->group(array('s.subid', 's.name', 'ct.name', 'so.mid', 'so.soid', 'p.FirstName', 'p.Patronymic', 'p.LastName'));

        $columns = array(
            'soid' => array('hidden' => true),
            'org_id' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'fio' => array(
                'title' => _('Пользователь')
            ),
            'criteria_test' => array(
                'title' => _('Квалификация')
            ),
            'subject' => array(
                'title' => _('Будет назначен курс')
            ),
            'subject_id' => array('hidden' => true),
        );

        /*
         * убираем все лишние фильтры из запроса для рассчета
         * общего количества людей в данном подразделении
         */
        $select_total         = clone $select;
        $select_need_learning = clone $select;
        
        $select_total->reset('where');
        $select_total->where($where_blocked, 0);
        $select_total->where($where_subid, 0);
        $select_total->where($where_pblocked);
        foreach ($where_filter as $value) {
            $select_total->where($value['query'], $value['value']);
        }
        $select_total->reset('columns');
        $select_total->columns('p.MID');
        $select_total->group('p.MID');
        $stmt = $select_total->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $people_total = count($rows);
        
        $select_need_learning->group('p.MID');
        $stmt = $select_need_learning->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $people_need_learning = count($rows);
        /**/
        
//        echo $select->__toString();
        $grid = $this->getGrid($select, $columns,
            array(
                'fio' => null,
            )        
        );
        
//        $grid->updateColumn('fio',
//            array('callback' =>
//                array('function' => array($this, 'updateFio'),
//                      'params'   => array('{{fio}}', '{{MID}}')
//                )
//            )
//        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'learning',
            'action' => 'assign-student'
        ),
            array('subject_id', 'MID'),
            _('Назначить')
        );
        
        $grid->addMassAction(array(
            'module' => 'subject' , 
            'controller' => 'learning',
            'action' => 'assign-students', 
          ),
         _('Назначить пользователей на курсы'),
         _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        
        
        $grid->setGridSwitcher(array(
            array('name' => 'strictly', 'title' => _('непосредственное подчинение'), 'params' => array('all' => 0)),
            array('name' => 'all', 'title' => _('все уровни вложенности'), 'params' => array('all' => 1)),
        ));

        if (!$this->isAjaxRequest()) {
            $tree = $this->getService('Orgstructure')->getTreeContent($defaultParent->soid, true, $orgId);

            $tree = array(
                0 => array(
                    'title' => $defaultParent->name,
                    'count' => 0,
                    'key' => $defaultParent->soid,
                    'isLazy' => true,
                    'isFolder' => true,
                    'expand' => true
                ),
                1 => $tree
            );
            $this->view->tree = $tree;
        }

//        $grid->setActionsCallback(
//            array('function' => array($this,'updateActions'),
//                  'params'   => array('{{type_id}}', '{{profile_id}}', '{{MID}}', '{{vacancy_id}}', '{{newcomer_id}}')
//            )
//        );

        $grid->setMassActionsCallback(
            array('function' => array($this, 'updateMassActions'),
                'params'     => array('{{MID}}', '{{subject_id}}')
            )
        );

        $this->view->treeajax= $this->_getParam('treeajax', 'none');
        $this->view->all = $switcher;
        $this->view->orgId = $orgId;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->people_need_learning = $people_need_learning;
        $this->view->people_total = $people_total;
    }
    
    public function updateMassActions($mid, $subject_id, $action) {
        $result = str_replace($mid, (int)$mid.'_'.(int)$subject_id, $action);
        return $result;
    }
    
    
    public function assignStudentAction() {
        $subject_id = (int)$this->_getParam('subject_id', 0);
        $mid        = (int)$this->_getParam('MID', 0);
        
        $subjectService = $this->getService('Subject');
        
        if($subject_id && $mid){
            $subject  = $subjectService->fetchAll(array('subid = ?' => $subject_id))->current();
            if($subject->type !== null && $subject->type == HM_Subject_SubjectModel::TYPE_FULLTIME){
                $sessions = $subjectService->fetchAll(
                    array('base_id = ? AND begin > now()' => $subject_id), 
                    'begin ASC'
                );
                if(count($sessions)){
                    $subjectService->assignStudent($sessions[0]->subid, $mid);
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                        'message' => _('Слушатель успешно назначен на сессию!')
                    ));
                } else {
                    $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не найдено подходящей сессии'),
            ));
                }
            } else {
                $subjectService->assignStudent($subject_id, $mid);
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => _('Слушатель успешно назначен на курс!')
                ));
            }
            
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Ошибка')
            ));
        }
        
        $this->_redirector->gotoSimple('need', null, null, array());
        
    }
    
    public function assignStudentsAction() {
        $subjectService = $this->getService('Subject');
        
        $id_pairs = $this->_getParam('postMassIds_grid', array());
        $id_pairs = explode(',', $id_pairs);
        $success = 0;
        foreach ($id_pairs as $id_pair) {
            $id_pair_array = explode('_', $id_pair);
            $mid = (int)$id_pair_array[0];
            $subject_id = (int)$id_pair_array[1];
            if($subject_id && $mid){
                $subject  = $subjectService->fetchAll(array('subid = ?' => $subject_id))->current();
                if($subject->type !== null && $subject->type == HM_Subject_SubjectModel::TYPE_FULLTIME){
                    $sessions = $subjectService->fetchAll(
                        array('base_id = ? AND begin > now()' => $subject_id), 
                        'begin ASC'
                    );
                    if(count($sessions)){
                        $subjectService->assignStudent($sessions[0]->subid, $mid);
                        $success++;
                    }
                } else {
                    $subjectService->assignStudent($subject_id, $mid);
                    $success++;
                }
            }
        }
        
        
        if($success){
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Назначено слушателей: '.$success)
            ));
        } else {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Слушатели не были назначены.')
            ));
        }
        
        $this->_redirector->gotoSimple('need', null, null, array());
        
    }
}