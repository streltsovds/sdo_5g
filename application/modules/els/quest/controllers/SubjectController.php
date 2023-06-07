<?php
class Quest_SubjectController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    protected $_subject;
    protected $_quest;

    public function init()
    {
        $this->questRestrict();

        $questId = (int) $this->_getParam('quest_id');
        $this->_quest = $this->getOne($this->getService('Quest')->findDependence(array('Settings', 'SubjectAssign'), $questId));
        if ($this->_quest) {
            HM_Quest_Settings_SettingsService::detectScope($this->_quest);
        }

        if (!$this->isAjaxRequest()) {
            $form = new HM_Form_Quest();
            $this->_setForm($form);
            $form->getSubForm('questStep1')->getElement('status')->setAttrib('disabled', 'disabled'); // всегда огр.использование
        }
        parent::init();
    }

    public function testAction()
    {
        $onlyType = $this->_setParam('only_type', HM_Quest_QuestModel::TYPE_TEST);
        $this->listAction();

        $this->_helper->viewRenderer->setNoRender();
        echo $this->view->render('subject/list.tpl');
    }

    public function pollAction()
    {
        $onlyType = $this->_setParam('only_type', HM_Quest_QuestModel::TYPE_POLL);
        $this->listAction();

        $this->_helper->viewRenderer->setNoRender();
        echo $this->view->render('subject/list.tpl');
    }


    public function listAction()
    {
        $onlyType  = $this->_getParam('only_type', HM_Quest_QuestModel::TYPE_TEST);
        $subjectId = $this->_subject->subid;
        $switcher  = $this->getSwitcherSetOrder($subjectId, $subjectId ? 'subject_DESC' : 'name_ASC');

        $select = $this->getService('Quest')->getSelect();
        $select->from(
            array(
                'q' => 'questionnaires'
            ),
            array(
                'q.quest_id',
                'subject' => 'sq.subject_id',
                'name' => 'q.name',
                'assigned' => new Zend_Db_Expr('MIN(CASE WHEN sq.subject_id='.$subjectId.' THEN 0 ELSE 1 END)'),
                'location_id' => new Zend_Db_Expr('CASE WHEN q.subject_id IS NULL OR q.subject_id=0 THEN 1 ELSE 0 END'),
                'location' => new Zend_Db_Expr('CASE WHEN q.subject_id IS NULL OR q.subject_id=0 THEN 1 ELSE 0 END'),
                'is_used' => new Zend_Db_Expr(//используется в курсе
                        $this->quoteInto(
                            array('CASE WHEN sq.subject_id = ? THEN 1 ELSE 0 END'),
                            array($subjectId)
                        )
                    ),
                'lessons' => 'q.quest_id',
                'count_questions' => new Zend_Db_Expr('COUNT(DISTINCT qq.question_id)'),
                'tags' => 'q.quest_id',
            )
        );

        $select
            ->joinLeft(array('sq' => 'subjects_quests'), "q.quest_id = sq.quest_id AND sq.subject_id = {$subjectId}", array())
            ->joinLeft(array('qqq' => 'quest_question_quests'), 'q.quest_id = qqq.quest_id', array())
            ->joinLeft(array('qq' => 'quest_questions'), 'qqq.question_id = qq.question_id', array())
            ->where('q.type = ?', $onlyType)
            ->where('q.status = ?', HM_Quest_QuestModel::STATUS_RESTRICTED)
            ->group(array(
                'q.quest_id',
                'q.name',
                'q.type',
                'sq.subject_id',
                'q.subject_id',
            ));
        ;

        $currentUserRole = $this->getService('User')->getCurrentUserRole();

        if(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER != $currentUserRole) {
            $select->where(
                'creator_role <> ? or creator_role is null',
                array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)
            );
        }

        if (count($builtInTypeIds = HM_Quest_QuestModel::getBuiltInTypeIds())) {
            $select->where('q.quest_id NOT IN (?)', $builtInTypeIds);
        }
        if ($switcher && $switcher == 1) {
            $select->where('q.subject_id = 0 OR q.subject_id = ?', $subjectId);
        } else {
            $select->where( $this->quoteInto(
                array(' ( q.subject_id = ? OR ' , 'sq.subject_id = ? ) '),
                array($subjectId, $subjectId)
            ));
        }

        $grid = $this->getGrid($select,
            array(
                'quest_id' => array('hidden' => true),
                'subject' => array('hidden' => true),
                'location_id' => array('hidden' => true),
                'is_used' => array('hidden' => true),
                'assigned' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'question', 'action' => 'list', 'gridmod' => null, 'quest_id' => '')) . '{{quest_id}}'.'">'.'{{name}}</a>',
                ),
                'location' => $onlyType == HM_Quest_QuestModel::TYPE_POLL
                        ? array('hidden' => true)
                        : array(
                            'title' => _('Место хранения'),
                            'callback' => array(
                                'function' => array($this, 'updateLocation'),
                                'params'   => array('{{location}}')
                            )),
                'lessons'   => $onlyType == HM_Quest_QuestModel::TYPE_POLL
                        ? array('hidden' => true)
                        : array(
                            'title' => _('Доступ для слушателей'),
                            'callback' => array(
                                'function' => array($this, 'updateSubjectColumnQuests'),
                                'params' => array(HM_Event_EventModel::TYPE_TEST, '{{quest_id}}', '{{subject}}', $subjectId)
                            )),
                'count_questions' => array(
                    'title' => _('Количество вопросов'),
                    'decorator' => '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'question', 'action' => 'list', 'gridmod' => null, 'quest_id' => '')) . '{{quest_id}}'.'">'.'{{count_questions}}</a>',
                ),
                'tags' => $onlyType == HM_Quest_QuestModel::TYPE_POLL
                        ? array('hidden' => true)
                        : array(
                            'title' => _('Метки'),
                            'callback' => array(
                                'function'=> array($this, 'displayTags'),
                                'params'=> array('{{tags}}', $this->getService('TagRef')->getTestType())
                )),
            ),
            array(
                'name' => null,
                'location' => array('values' => HM_Resource_ResourceModel::getLocaleStatuses()),
                'count_questions' => null,
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
            ), $this->gridId
        );

        $grid->addAction(array(
            'module' => 'quest',
            'controller' => 'subject',
            'action' => 'edit'
        ),
            array('quest_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'quest',
            'controller' => 'subject',
            'action' => 'delete'
        ),
            array('quest_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'index',
                'action' => 'index',
                'mode' => HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF,
                'subject_id' => $subjectId
            ),
            array('quest_id'),
            $this->view->svgIcon('preview', _('Предварительный просмотр'))
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'cluster',
                'action' => 'list',
//                'mode' => HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF,
                'subject_id' => $subjectId
            ),
            array('quest_id'),
            _('Список блоков')
        );

        /*if ($onlyType == HM_Quest_QuestModel::TYPE_POLL) {
            $grid->addAction(array(
                    'module' => 'quest',
                    'controller' => 'subject',
                    'action' => 'feedback_result',
                    'subject_id' => $subjectId
                ),
                array('quest_id'),
                _('Результаты')
            );
            $grid->addAction(array(
                    'module' => 'quest',
                    'controller' => 'report',
                    'action' => 'feedback',
                    'subject_id' => $subjectId
                ),
                array('quest_id'),
                _('Общий отчёт по опросу')
            );
        }*/

        if ($onlyType == HM_Quest_QuestModel::TYPE_POLL
            || $onlyType == HM_Quest_QuestModel::TYPE_TEST
        ) {

            $grid->addAction(array(
                    'module' => 'quest',
                    'controller' => 'report',
                    'action' => 'index'
                ),
                array('quest_id'),
                'Статистика ответов'
            );
        }

        $grid->addMassAction(
            array(
                'module' => 'quest',
                'controller' => 'subject',
                'action' => 'delete-by',
            ),
            _('Удалить'),
            _('Вы действительно желаете удалить тест? Применимо только для тестов, созданных на курсе.')
        );

        $grid->setGridSwitcher(array(
            array('name' => 'strictly', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subject' => $subjectId, 'all' => 0)),
            array('name' => 'all', 'title' => _('все, включая из Базы знаний'), 'params' => array('subject' => null, 'all' => 1), 'order' => 'assigned', 'assigned' => 'DESC'),
        ));

        $grid->addMassAction(
            array('module' => 'quest', 'controller' => 'subject', 'action' => 'assign'),
            _('Использовать в данном курсе')
        );

        $grid->addMassAction(
            array('module' => 'quest', 'controller' => 'subject', 'action' => 'unassign'),
            _('Не использовать в данном курсе')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{location_id}}', '{{subject}}')
        ));

        $grid->setClassRowCondition("'{{is_used}}' == 1", "success");

        $this->view->type = $onlyType;
        $this->view->switcher = $switcher;
        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function cardAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $this->view->quest = $this->_quest;
    }

    public function newAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        $subjectId = $request->getParam('subject_id');
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirector->gotoSimple('list', 'question', 'quest', array('subject_id' => $subjectId, 'quest_id' => $this->_quest->quest_id));
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirector->gotoSimple('list', 'question', 'quest', array('subject_id' => $subjectId, 'quest_id' => $this->_quest->quest_id));
                }
            }
        }
        $this->view->form = $form;
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['quest_id']);

        if ($this->_subject->subid) {
            $values['subject_id'] = $this->_subject->subid;
        }
        $values['status'] = HM_Quest_QuestModel::STATUS_RESTRICTED;

        if ($values['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
            $values['mode_selection_questions'] = $values['mode_selection_questions_cluster'];
        }
        unset($values['mode_selection_questions_cluster']);

        if ($values['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS) {
            $values['mode_display_clusters'] = new Zend_Db_Expr('NULL');
        }

        if ($values['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS) {
            $values['mode_display_questions'] = new Zend_Db_Expr('NULL');
        }

        if (!$values['limit_time']) {
            $values['limit_time'] = new Zend_Db_Expr('NULL');
        }
        if (!$values['limit_attempts']) {
            $values['limit_attempts'] = new Zend_Db_Expr('NULL');
        }

        if (isset($values['poll_mode'])) {
            if ($values['poll_mode'] != HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE) {
                $values['scale_id'] = 0;
            }
            unset($values['poll_mode']);
        }

        if ($this->_quest = $this->getService('Quest')->insert($values)) {

            if ($this->_subject->subid) {
                $this->getService('SubjectQuest')->insert(array('subject_id' => $this->_subject->subid, 'quest_id' => $this->_quest->quest_id));
            }
            if (count($values['tags'])) {
                $this->getService('Tag')->updateTags($values['tags'], $this->_quest->quest_id, $this->getService('TagRef')->getTestType() );
            }
        }
    }

    public function editAction()
    {
        $form = $this->_getForm();

        if (!$this->_quest->subject_id) {
            $elements = $form->getSubForm('questStep1')->getElements() + $form->getSubForm('questStep2')->getElements();
            foreach ($elements as $element) {
                switch ($element->getType()) {
                    case 'Zend_Form_Element_Select':
                    case 'HM_Form_Element_FcbkComplete': // не работает .(
                        $element->setAttrib('disabled', 'disabled');
                        break;
                    case 'Zend_Form_Element_Submit':
                    case 'Zend_Form_Element_Button':
                        // do nothing
                        break;
                    default:
                        if (!in_array($element->getName(), HM_Quest_Settings_SettingsModel::getSettingsAttributes()) && (substr($element->getName(), 0, 14) != 'cluster_limit_')) {
                            $element->setAttrib('readonly', 'readonly');
                        }
                        break;
                }
            }
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($data = $request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex($this->_quest->type);
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function update($form)
    {
        $values = $form->getValues();
        $values['type']   = $this->_quest->type;
        $values['status'] = $this->_quest->status;

        if ($values['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
            $values['mode_selection_questions'] = $values['mode_selection_questions_cluster'];
        }
        unset($values['mode_selection_questions_cluster']);

        if ($values['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS) {
            $values['mode_display_clusters'] = new Zend_Db_Expr('NULL');
        }

        if ($values['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS) {
            $values['mode_display_questions'] = new Zend_Db_Expr('NULL');
        }

        if (!$values['limit_time']) {
            $values['limit_time'] = new Zend_Db_Expr('NULL');
        }
        if (!$values['limit_attempts']) {
            $values['limit_attempts'] = new Zend_Db_Expr('NULL');
        }

        if (isset($values['poll_mode'])) {
            if ($values['poll_mode'] != HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE) {
                $values['scale_id'] = 0;
            }
            unset($values['poll_mode']);
        }

        $res = $this->getService('Quest')->update($values, HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $this->_subject->subid);

        if (count($values['tags'])) {
            $this->getService('Tag')->updateTags($values['tags'], $values['quest_id'], $this->getService('TagRef')->getTestType());
        }
    }

    public function deleteAction()
    {
        $questId = $this->_getParam('quest_id', 0);

        if ($quest = $this->getService('Quest')->getOne($this->getService('Quest')->find($questId))) {
            $this->delete($questId);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex($quest ? $quest->type : HM_Quest_QuestModel::TYPE_TEST);
    }

    public function deleteByAction()
    {
        $subjectId = $this->_subject->subid;
        $gridId = "grid_{$subjectId}";

        $postMassIds = $this->_getParam('postMassIds_'.$gridId, '');

        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            $dels = 0;
            if (count($ids)) {
                foreach($ids as $id) {
                    if (!isset($quest)) $quest = $this->getService('Quest')->getOne($this->getService('Quest')->find($id));
                    if ($this->delete($id)) {
                        $dels++;
                    }
                }

                if ($dels == count($ids)) {
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
                } elseif ($dels == 0) {
                    $this->_flashMessenger->addMessage(_('Невозможно удалить выбраные элементы.'));
                } else {
                    $this->_flashMessenger->addMessage(_('Удалены ' . $dels .  'из ' . count($ids) . ' элементов.'));
                }

            }
        }
        $this->_redirectToIndex($quest ? $quest->type : HM_Quest_QuestModel::TYPE_TEST);
    }

    public function delete($questId)
    {
        $del = false;
        $quest  = $this->getOne($this->getService('Quest')->findDependence(array('Settings', 'SubjectAssign'), $questId));
        if ($quest) {
            $feedback = $this->getService('Feedback')->fetchAll(array('quest_id = ?' => $questId, 'subject_id = ?' => $this->_subject->subid))->current();
            $lesson = $this->getService('Lesson')->fetchAll(array('typeID = ?' => HM_Event_EventModel::TYPE_POLL, 'CID = ?' => $this->_subject->subid, 'params LIKE ?' => '%module_id='.$questId.';%'))->current();

            if (!$feedback->feedback_id && !$lesson->SHEID) {
                //если создан в этом курсе - удаляем совсем
                if ($quest->subject_id == $this->_subject->subid) {
                    $this->getService('QuestSettings')->deleteBy(array('quest_id = ?' => $questId));
                    $this->getService('SubjectQuest')->deleteBy(array('quest_id = ?' => $questId));
                    $this->getService('Quest')->delete($questId);
                    $del = true;
                } else {
                    // ничего не делаем
                    // для открепления ест ьметод unassign()
                }
            } else {
                if ($feedback->feedback_id) {
                    $this->_flashMessenger->addMessage(array('message' =>_('Опрос используется в мероприятии по сбору обратной связи') .': "'.$feedback->name.'"', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                }
                if ($lesson->SHEID) {
                    $this->_flashMessenger->addMessage(array('message' =>_('Опрос используется в занятии') .': "'.$feedback->name.'"', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                }
            }

        }
        return $del;
    }

    public function setDefaults(Zend_Form $form)
    {
        $data = $this->_quest->getData();

        if ($data['limit_attempts'] === '0') $data['limit_attempts'] = '';
        if ($data['limit_time'] === '0') $data['limit_time'] = '';
        $data['status'] = HM_Quest_QuestModel::STATUS_RESTRICTED;

        $data['tags'] = $this->getService('Tag')->getTags($this->_quest->quest_id, $this->getService('TagRef')->getTestType());

        if ($data['cluster_limits']) {
            $clusterLimits = explode(';', $data['cluster_limits']);
            for($i=0; $i<count($clusterLimits); $i+=2) {
                $data['cluster_limit_' . $clusterLimits[$i]] = $clusterLimits[$i+1];
            }
        }

        if ($data['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
            $data['mode_selection_questions_cluster'] = $data['mode_selection_questions'];
            unset($data['mode_selection_questions']);
        }

        $form->populate($data);
    }


    protected function _redirectToIndex($defaultType = HM_Quest_QuestModel::TYPE_TEST)
    {
        $this->_redirector->gotoSimple($this->_quest ? $this->_quest->type : $defaultType, 'subject', null, array(
            'subject_id' => $this->_subject->subid,
        ));
    }

    public function updateType($type)
    {
        $types = HM_Quest_QuestModel::getTypes();
        return isset($types[$type]) ? $types[$type] : '';
    }

    public function assignAction()
    {
        $gridId = "grid_{$this->_subject->subid}";
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $onlyType = HM_Quest_QuestModel::TYPE_TEST;
        if ($this->_subject->subid && strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $quest = $this->getService('Quest')->getOne($this->getService('Quest')->findDependence(array('Settings', 'SubjectAssign'), $id));
                    if ($quest){
                        $assigned = false;
                        if (count($quest->subjects)) {
                            $questAssign = $quest->subjects->getList('subject_id');
                            $assigned = isset($questAssign[$this->_subject->subid]);
                        }
                        if (!$assigned) {
                            $this->getService('SubjectQuest')->insert(array('subject_id' => $this->_subject->subid, 'quest_id' => $id));
                            $this->getService('QuestSettings')->copyToScope($quest, HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $this->_subject->subid);
                        }
                        $onlyType = $quest->type;
                    }
                    /** was different variable instead of $this->_subject->subid. Was it necessary? */
//                     $this->getService('Quest')->createLesson($this->_subject->subid, $id);
                }

                $this->_flashMessenger->addMessage(_('Материалы успешно привязаны к курсу'));
            }
        }
        $this->_redirectToIndex($onlyType);
    }


    public function unassignAction()
    {
        $gridId = "grid_{$this->_subject->subid}";
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        $dels = 0;
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (!empty($ids)) {
                foreach($ids as $id) {
                    if (!isset($quest)) $quest = $this->getService('Quest')->getOne($this->getService('Quest')->find($id));

                    $feedback = $this->getService('Feedback')->fetchAll(array('quest_id = ?' => $id, 'subject_id = ?' => $this->_subject->subid))->current();
                    $lesson = $this->getService('Lesson')->fetchAll(array('typeID = ?' => HM_Event_EventModel::TYPE_POLL, 'CID = ?' => $this->_subject->subid, 'params LIKE ?' => '%module_id='.$id.';%'))->current();

                    if (!$feedback->feedback_id && !$lesson->SHEID) {
//@D                    $this->getService('Quest')->clearLesson($this->_subject, $id);
                        $this->getService('SubjectQuest')->deleteBy(array('subject_id = ?' => $this->_subject->subid, 'quest_id = ?' => $id));
                        $this->getService('QuestSettings')->deleteByScope($id, HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $this->_subject->subid);
                        $dels++;
                    } else {
                        if ($feedback->feedback_id) {
                            $this->_flashMessenger->addMessage(array('message' =>_('Опрос используется в мероприятии по сбору обратной связи') .': "'.$feedback->name.'"', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                        }
                        if ($lesson->SHEID) {
                            $this->_flashMessenger->addMessage(array('message' =>_('Опрос используется в занятии') .': "'.$lesson->title.'"', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                        }
                    }
                }
                if ($dels == count($ids)) {
                    $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
                } elseif ($dels == 0) {
                    $this->_flashMessenger->addMessage(_('Невозможно отменить назначение выбраных элементов.'));
                } else {
                    $this->_flashMessenger->addMessage(_('Назначение отменено у ' . $dels .  ' из ' . count($ids) . ' элементов.'));
                }
            }
        }
        $this->_redirectToIndex($quest ? $quest->type : HM_Quest_QuestModel::TYPE_TEST);
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = $this->getService('Quest')->getDefaults();
        $defaults['name'] = $this->_getParam('title');
        $subjectId = $defaults['subject_id'] = $this->_getParam('subject_id');
        if (strlen($defaults['name']) && $subjectId) {
            if ($quest = $this->getService('Quest')->insert($defaults)) {
                $this->getService('Subject')->update(array(
                    'last_updated' => $this->getService('Subject')->getDateTime(),
                    'subid' => $subjectId
                ));
                $result = $quest->quest_id;
            }
        }
        exit(HM_Json::encodeErrorSkip($result));
    }

    public function updateActions($location, $subject, $actions)
    {
        if(!$subject) {
            $this->unsetAction($actions, array('module' => 'quest', 'controller' => 'cluster', 'action' => 'list'));
        }

        if ($location) { // если тест из базы знаний
            // редактировать можно - для этого существует quest_settings
            // $this->unsetAction($actions, array('controller' => 'subject', 'action' => 'edit'));
            $this->unsetAction($actions, array('controller' => 'subject', 'action' => 'delete'));
        }
        return $actions;
    }

    public function updateLocation($location)
    {
        $locations = HM_Test_Abstract_AbstractModel::getLocaleStatuses();
        return $locations[$location];
    }


    /*
    public function feedbackresultAction()
    {
        $questId = $this->_getParam('quest_id', 0);
        if ($questId) {
            $quest   = $this->getService('Quest')->find($questId)->current();

            if ($quest && ($quest->type != HM_Quest_QuestModel::TYPE_POLL)) {
                $this->_redirectToIndex();
            }
        }

        $select = $this->getService('SubjectFeedback')->getSelect();
        $select->from(array('sf' => 'subjects_feedback'), array(
                'sf.feedback_id',
                'p.MID',
                's.subid',
                'attempt_id' =>  new Zend_Db_Expr('MAX(a.attempt_id)'),
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'subject' => 's.name',
                'poll'    => 'q.name',
                'graduated' => new Zend_Db_Expr('MAX(g.end)'),
                'feedback'  => 'sf.date_finished'
            ))
            ->joinInner(array('a' => 'quest_attempts'),  'sf.feedback_id=a.context_event_id AND a.context_type='.HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK, array())
            ->joinInner(array('s' => 'subjects'),  's.subid=sf.subject_id', array())
            ->joinInner(array('p' => 'People'),    'p.MID=sf.user_id', array())
            ->joinInner(array('g' => 'graduated'), 'g.MID=sf.user_id AND g.CID=sf.subject_id AND g.end<sf.date_finished', array())
            ->joinInner(array('q' => 'questionnaires'), 'q.quest_id=sf.quest_id', array())
                ->where('sf.status=?', HM_Subject_Feedback_FeedbackModel::STATUS_FINISHED)
            ->group(array(
                'sf.feedback_id', 'sf.date_finished',
                'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic',
                's.subid', 's.name',
                'q.quest_id', 'q.name'
            ));

        if ($questId) {
            $select->where('sf.quest_id=?', $questId);
        }
        if ($this->_subject) {
            $select->where('s.subid=?', $this->_subject->subid);
        }

        $grid = $this->getGrid($select,
            array(
                'feedback_id' => array('hidden' => true),
                'MID'         => array('hidden' => true),
                'subid'       => array('hidden' => true),
                'attempt_id'  => array('hidden' => true),
                'fio'         => array('title'  => _('ФИО')),
                'subject'     => $this->_subject
                        ? array('hidden' => true)
                        : array('title'  => _('Курс')),
                'poll'        => array('title'  => _('Опрос')),
                'graduated'   => array('title'  => _('Дата окончания обучения')),
                'feedback'    => array(
                    'title'  => _('Дата прохождения опроса'),
                    'decorator' => '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'report', 'action' => 'attempt', 'attempt_id' => '')).'{{attempt_id}}">{{feedback}}</a>'
                ),
            ),
            array(
                'fio'       => true,
                'subject'   => true,
                'poll'      => true,
                'graduated' => array('render' => 'DateSmart'),
                'feedback'  => array('render' => 'DateSmart'),
            ));
        $grid->updateColumn('graduated', array('format' => array('DateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
        $grid->updateColumn('feedback',  array('format' => array('DateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }*/
}
?>
