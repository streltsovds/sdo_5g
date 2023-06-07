<?php
// используем те же формы, что и в БЗ
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/modules/els/quest/forms/'),
    get_include_path(),
)));

class Subject_QuestController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $service     = 'Quest';
    protected $idParamName = 'quest_id';
    protected $idFieldName = 'quest_id';
    protected $id          = 0;

    protected $_quest;

    const SHOW_IN_COURSE_TESTS = 0;
    const SHOW_ALL_TESTS       = 1;

    public function init()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

//         $form = new HM_Form_Courses();
        $form = new HM_Form_Quest();
        $this->_setForm($form);

        $this->id = (int) $this->_getParam($this->idParamName, 0);
        $this->_quest = $subject = $this->getOne($this->getService($this->service)->findDependence(array('Settings', 'SubjectAssign'), $this->id));
        if ($this->_quest) {
            HM_Quest_Settings_SettingsService::detectScope($this->_quest);
        }

        if (!$this->isAjaxRequest() && ($subjectId || !empty($subject))) {
            $this->view->setExtended(
                array(
                    'subjectName' =>        $subjectId ? 'Subject'   : $this->service,
                    'subjectId' =>          $subjectId ? $subjectId  : $this->id,
                    'subjectIdParamName' => $subjectId ? 'subject_id': $this->idParamName,
                    'subjectIdFieldName' => $subjectId ? 'subject_id': $this->idFieldName,
                    'subject' =>            $subjectId ? $this->getOne($this->getService('Subject')->find($subjectId)):$subject
                )
            );

            if ($this->_quest && $this->_quest->type != HM_Quest_QuestModel::TYPE_PSYCHO) {
                 $this->view->addContextNavigationModifier(
                     new HM_Navigation_Modifier_Remove_Page('resource', 'cm:quest:page4')
                 );
            }
        }
        parent::init();
    }

    public function listAction()
    {
        $onlyType = $this->_getParam('only-type');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }

        $select = $this->getService('Quest')->getSelect();
        $select->from(
            array(
                'q' => 'questionnaires'
            ),
            array(
                'q.quest_id',
                'name' => 'q.name',
                'q.type',
                'type_id' => 'q.type',
            	'count_questions' => new Zend_Db_Expr('COUNT(DISTINCT qq.question_id)'),
            )
        );

        $select
            ->joinLeft(array('qqq' => 'quest_question_quests'), 'q.quest_id = qqq.quest_id', array())
            ->joinLeft(array('qq' => 'quest_questions'), 'qqq.question_id = qq.question_id', array())
            ->where('q.quest_id NOT IN (?)', HM_Quest_QuestModel::getBuiltInTypeIds())
            ->group(array(
                'q.quest_id',
                'q.name',
                'q.type',
            ));
        ;

        if ($onlyType) {
            $select->where('q.type = ?', $onlyType);
        }

        $testFilter = $this->_getParam('all', Quest_ListController::SHOW_IN_COURSE_TESTS);
        if(!($subjectId && $testFilter==Quest_ListController::SHOW_ALL_TESTS))
            if($subjectId)
                $select->joinLeft(array('sq' => 'subjects_quests'),'q.quest_id = sq.quest_id', array())
                       ->where('sq.subject_id = '.(int) $subjectId.' OR q.subject_id = '.$subjectId);
            else
                $select->where('q.subject_id is null');

        $grid = $this->getGrid($select, array(
            'quest_id' => array('hidden' => true),
            'type_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),

                'decorator' => $subjectId ?
                    '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'question', 'action' => 'list', 'gridmod' => null, 'quest_id' => '')) . '{{quest_id}}'.'">'.'{{name}}</a>'
                    :
                    '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'index', 'action' => 'card', 'gridmod' => null, 'quest_id' => '')) . '{{quest_id}}'.'">'.'{{name}}</a>',
            ),
            'description' => array(
                'title' => _('Описание'),
            ),
            'type' => $onlyType ? array('hidden' => true) : array(
                'title' => _('Тип'),
                'callback' => array(
                    'function'=> array($this, 'updateType'),
                    'params'=> array('{{type}}')
                )
            ),
            'count_questions' => array(
                'title' => _('Количество вопросов'),
                'decorator' => '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'question', 'action' => 'list', 'gridmod' => null, 'quest_id' => '')) . '{{quest_id}}'.'">'.'{{count_questions}}</a>',
            ),
        ),
        array(
            'name' => null,
            'count_questions' => null,
        ));

//         $grid->setActionsCallback(
//             array('function' => array($this,'updateActions'),
//                 'params'   => array('{{type_id}}')
//             )
//         );

        $grid->addAction(array(
            'module' => 'quest',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('quest_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        if(!$subjectId) {
            $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'list',
                'action' => 'delete'
            ),
                array('quest_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );
        }
        $grid->addAction(array(
            'module' => 'quest',
            'controller' => 'index',
            'action' => 'index',
            'mode' => HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF,
            'subject_id'=>$subjectId
        ),
            array('quest_id'),
            $this->view->svgIcon('preview', _('Предварительный просмотр'))
        );

        $grid->addMassAction(
            array(
                'module' => 'quest',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );


        if($subjectId) {
            $grid->setGridSwitcher(array(
                array('name' => 'strictly', 'title' => _('тесты курса'), 'params' => array('all' => Quest_ListController::SHOW_IN_COURSE_TESTS)),
                array('name' => 'all', 'title' => _('все тесты'), 'params' => array('all' => Quest_ListController::SHOW_ALL_TESTS)),
            ));

            $grid->addMassAction(
                array('module' => 'quest', 'controller' => 'list', 'action' => 'assign'),
                _('Использовать в данном курсе')
            );

            $grid->addMassAction(
                array('module' => 'quest', 'controller' => 'list', 'action' => 'unassign'),
                _('Не использовать в данном курсе')
            );
        }


        $this->view->grid = $grid;
        $this->view->subjectId = $subjectId;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function cardAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $this->view->quest = $this->_quest;
    }

    public function create($form)
    {

        $values = $form->getValues();
        unset($values['quest_id']);

        $subjectId = (int) $this->getRequest()->getParam('subject_id');
        if($subjectId) {
            $values['subject_id'] = $subjectId;
        }

        $this->_quest = $this->getService('Quest')->insert($values);

        if ($subjectId && $this->_quest) {
            $this->getService('SubjectQuest')->insert(array('subject_id'=>$subjectId, 'quest_id'=>$this->_quest->quest_id));
        }
    }

    public function update($form)
    {
        $subjectId = (int) $this->getRequest()->getParam('subject_id');

        $values = $form->getValues();
        $values['type'] = $this->_quest->type;
        if ($subjectId) {
            $res = $this->getService('Quest')->update($values, HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $subjectId);
        } else {
            $res = $this->getService('Quest')->update($values);
        }
    }

    public function delete($id)
    {
            $this->getService('Quest')->delete($id);
        $this->getService('SubjectQuest')->deleteBy(array('quest_id = ?' => $id));
    }

    public function setDefaults(Zend_Form $form)
    {
        $data = $this->_quest->getData();
        if ($data['limit_attempts'] === '0') $data['limit_attempts'] = '';
        if ($data['limit_time'] === '0') $data['limit_time'] = '';
        $form->populate($data);
    }


    protected function _redirectToIndex()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        if($subjectId)
            $this->_redirector->gotoSimple('index', 'materials', 'subject', array('subject_id' => $subjectId));

        if ($this->_quest->quest_id) {
            $this->_redirector->gotoSimple('card', 'index', null, array('quest_id' => $this->_quest->quest_id));
        }

        $this->_redirector->gotoSimple('index', 'list', null, array('only-type' => HM_Quest_QuestModel::TYPE_TEST)); // @todo: редиректить на правильный only-type
    }

    public function updateType($type)
    {
        $types = HM_Quest_QuestModel::getTypes();
        return isset($types[$type]) ? $types[$type] : '';
    }

    public function updateActions($actions)
    {
        return $actions;
    }


    public function assignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $subjectId = (int) $this->_getParam('subject_id', 0);

        if ($subjectId && strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $quest = $this->getService('Quest')->getOne($this->getService('Quest')->findDependence(array('Settings', 'SubjectAssign'), $id));
                    if ($quest){
                        $assigned = false;
                        if (count($quest->subjects)) {
                            $questAssign = $quest->subjects->getList('subject_id');
                            $assigned = isset($questAssign[$subjectId]);
                        }
                        if (!$assigned) {
                            $this->getService('SubjectQuest')->insert(array('subject_id' => $subjectId, 'quest_id' => $id));
                            $this->getService('QuestSettings')->copyToScope($quest, HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $subjectId);
                        }
                    }
                    /** was different variable instead of $this->id. Was it necessary? */
//                     $this->getService('Quest')->createLesson($this->id, $id);
                }

                $this->_flashMessenger->addMessage(_('Тесты успешно привязаны к курсу'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
        $gridId = ($this->id) ? "grid{$this->id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        $subjectId = $this->_getParam('subject_id', 0);
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (!empty($ids)) {
                foreach($ids as $id) {
//@D                    $this->getService('Quest')->clearLesson($this->_subject, $id);
                    $this->getService('SubjectQuest')->delete(array($subjectId, $id));
                    $this->getService('QuestSettings')->deleteByScope($id, HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $subjectId);
                }
                $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
            }
        }
        $this->_redirectToIndex();
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

}
?>
