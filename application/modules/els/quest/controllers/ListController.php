<?php
class Quest_ListController extends HM_Controller_Action_Quest
{
    use HM_Controller_Action_Trait_Grid {
        editAction as editActionTraitGrid;
    }

    protected $_quest;

    public function init()
    {
        $form = new HM_Form_Quest();
        $this->_setForm($form);

        parent::init();
    }

	public function testsAction()
	{
		$this->_indexAction(HM_Quest_QuestModel::TYPE_TEST);
	}

	public function pollsAction()
	{
		$this->_indexAction(HM_Quest_QuestModel::TYPE_POLL);
	}

	public function psychoAction()
	{
		$this->_indexAction(HM_Quest_QuestModel::TYPE_PSYCHO);
	}

	public function formAction()
	{
		$this->_indexAction(HM_Quest_QuestModel::TYPE_FORM);
	}

	public function _indexAction($onlyType = '')
    {
    	// Отключаем рендер и включаем внизу только в _index(), чтобы не отключать во всех трёх функциях выше
    	$this->getHelper('viewRenderer')->setNoRender();

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
                'q.status',
                'count_questions' => new Zend_Db_Expr('COUNT(DISTINCT qq.question_id)'),
                'tags' => 'q.quest_id',
            )
        );

        $select
            ->joinLeft(array('qqq' => 'quest_question_quests'), 'q.quest_id = qqq.quest_id', array())
            ->joinLeft(array('qq' => 'quest_questions'), 'qqq.question_id = qq.question_id', array())
            ->where('q.subject_id = ? OR q.subject_id IS NULL', 0)
            ->where('q.quest_id NOT IN (?)', array(HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_ADAPTING, HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_RECRUIT, HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_RESERVE))
            ->group(array(
                'q.quest_id',
                'q.name',
                'q.type',
                'q.status',
            ));

        if ($onlyType) {
            $select->where('q.type = ?', $onlyType);
        }

        $currentUserRole = $this->getService('User')->getCurrentUserRole();

        if (HM_Role_Abstract_RoleModel::ROLE_ATMANAGER != $currentUserRole) {
            $select
                ->where('q.creator_role is null or q.creator_role <> ?', [HM_Role_Abstract_RoleModel::ROLE_ATMANAGER])
                ->where('q.quest_id != ?', HM_Quest_QuestModel::NEWCOMER_POLL_ID);
        }


        $grid = $this->getGrid($select, array(
            'quest_id' => array('hidden' => true),
            'type_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'decorator' => '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'question', 'action' => 'list', 'gridmod' => null, 'quest_id' => '')) . '{{quest_id}}'.'">'.'{{name}}</a>',
            ),
            /*
            'description' => array(
                'title' => _('Описание'),
                'hidden' => $this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_DEAN),
            ),
            */
            'type' => $onlyType ? array('hidden' => true) : array(
                'title' => _('Тип'),
                'callback' => array(
                    'function'=> array($this, 'updateType'),
                    'params'=> array('{{type}}')
                )
            ),
            'status' => array(
                'title' => _('Статус ресурса БЗ'),
                'callback' => array(
                        'function'=> array($this, 'updateStatus'),
                        'params'=> array('{{status}}')
                )
            ),
            'count_questions' => array(
                'title' => _('Количество вопросов'),
            ),
            'tags' => array(
                'title' => _('Метки'),
                'callback' => array(
                    'function'=> array($this, 'displayTags'),
                    'params'=> array('{{tags}}', HM_Tag_Ref_RefModel::TYPE_TEST)
                ),
                'color' => HM_DataGrid_Column::colorize('tags')
            ),

        ),
        array(
            'name' => null,
            'count_questions' => null,
            'status' => array('values' => HM_Quest_QuestModel::getStatuses()),
            'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
        ));

        $showFormsActions = true;
        if (($onlyType == 'form') && $this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL))
            $showFormsActions = false;

        if ($showFormsActions) {
            $grid->setActionsCallback(
                array('function' => array($this, 'updateActions'),
                    'params'   => array('{{quest_id}}')
                )
            );

            $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'list',
                'action' => 'edit'
            ),
                array('quest_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $questName = HM_Quest_QuestModel::TYPE_POLL == $onlyType ? 'опрос' : 'тест';
            $msg = _("Вы действительно хотите удалить {$questName}? При этом вопросы, входящие в {$questName}, не будут удалены; для удаления вопросов используйте групповое действие на странице со списком вопросов.");
            $grid->addAction(
                array(
                    'module' => 'quest',
                    'controller' => 'list',
                    'action' => 'delete',
                    'only-type' => $onlyType,
                ),
                array(
                    'quest_id',
                ),
                $this->view->svgIcon('delete', _('Удалить')) //  "if (confirm('{$msg}')) return true; return false;")
            );

            if ($onlyType == HM_Quest_QuestModel::TYPE_POLL ||
                $onlyType == HM_Quest_QuestModel::TYPE_TEST
            ) {
                //$grid->addAction(array(
                //        'module' => 'quest',
                //        'controller' => 'subject',
                //        'action' => 'feedback_result',
                //    ),
                //    array('quest_id'),
                //    _('Результаты')
                // );

                $grid->addAction(
                    array(
                        'module' => 'quest',
                        'controller' => 'report',
                        'action' => 'index'
                    ),
                    array(
                        'quest_id',
                    ),
                    $this->view->svgIcon('bar-chart', _('Статистика ответов'))
                );
            }

            $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'lesson',
                'action' => 'info',
//                'mode' => HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF,
            ),
                array('quest_id'),
                $this->view->svgIcon('preview', _('Предварительный просмотр'))
            );



            $questName = HM_Quest_QuestModel::TYPE_POLL == $onlyType ? 'опросы' : 'тесты';
            $grid->addMassAction(
                array(
                    'module' => 'quest',
                    'controller' => 'list',
                    'action' => 'delete-by',
                    'only-type' => $onlyType,
                ),
                _('Удалить'),
                _("Вы действительно хотите удалить {$questName}? При этом вопросы, входящие в {$questName}, не будут удалены; для удаления вопросов используйте групповое действие на странице со списком вопросов.")
            );
        }

        $this->view->showFormsActions = $showFormsActions;
        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

	    echo $this->view->render('list/index.tpl');

    }

    public function cardAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $this->view->quest = $this->_quest;
    }

    public function importAction()
    {
        $this->view->setSubHeader(_('Импорт теста'));
        $form = new HM_Form_QuestImport();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $successImport = false;
            if ($form->isValid($request->getParams())) {
                $file = $form->getElement('file');
                $file->receive();

                if ($file->isReceived()) {
                    $filename = $file->getFileName();
                    $pathinfo = pathinfo($filename);
                    $title = $form->getValue('title') ? : $pathinfo['filename'];
                    $fileRealpath = realpath($filename);
                    $fileType = HM_Files_FilesModel::getFileType($filename);
                    $unzipPath = $this->getService('Files')->unzip($fileRealpath, false);
                    /** @var HM_Material_MaterialService $materialService */
                    $materialService = $this->getService('Material');

                    if (is_file($fileRealpath) and
                        is_readable($fileRealpath) and
                        (
                            HM_Files_FilesModel::FILETYPE_XLSX == $fileType or
                            HM_Files_FilesModel::FILETYPE_TEXT == $fileType
                        )
                    ) {
                        $result = $materialService->insertQuest($fileRealpath, $title);
                    } elseif($materialService::autodetectScorm($unzipPath)) {
                        $result = $materialService->insertEau3Quests($fileRealpath);
                    }
                }

                if($result) {
                    $this->_flashMessenger->addMessage(_('Импорт теста прошел успешно'));

                    if($result instanceof HM_Quest_QuestModel) {
                        $this->_redirector->gotoSimple('list', 'question', 'quest', array('quest_id' => $result->quest_id));
                    }
                } else {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('При импорте теста возникла ошибка')
                    ));
                }

                $this->_redirector->gotoSimple('tests', 'list', 'quest');
            }
        }

        $this->view->form = $form;
    }

    public function newAction()
    {
        $type = $this->_request->getParam('type', HM_Quest_QuestModel::TYPE_TEST);
        switch ($type) {
            case HM_Quest_QuestModel::TYPE_TEST:
                $titleText = _('Создание теста');
                break;
            case HM_Quest_QuestModel::TYPE_POLL:
                $titleText = _('Создание опроса');
                break;
            case HM_Quest_QuestModel::TYPE_PSYCHO:
                $titleText = _('Создание психологического опроса');
                break;
            case HM_Quest_QuestModel::TYPE_FORM:
                $titleText = _('Создание произвольной оценочной формы');
                break;
            default:
                $titleText = '';
                break;
        }

        $this->view->setSubHeader($titleText);

        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirector->gotoSimple('list', 'question', 'quest', array('only-type' => $type, 'quest_id' => $this->_quest->quest_id));
                } else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirector->gotoSimple('list', 'question', 'quest', array('only-type' => $type, 'quest_id' => $this->_quest->quest_id));
                }
            }
        }
        $this->view->form = $form;
    }

    public function editAction()
    {
        switch ($this->_quest->type) {
            case HM_Quest_QuestModel::TYPE_TEST:
                $titleText = _('Редактирование теста');
                break;
            case HM_Quest_QuestModel::TYPE_POLL:
                $titleText = _('Редактирование опроса');
                break;
            case HM_Quest_QuestModel::TYPE_PSYCHO:
                $titleText = _('Редактирование психологического опроса');
                break;
            case HM_Quest_QuestModel::TYPE_FORM:
                $titleText = _('Редактирование произвольной оценочной формы');
                break;
            default:
                $titleText = '';
                break;
        }

        $this->view->setSubSubHeader(($titleText));
        $this->editActionTraitGrid();
    }

    // есть еще аналогичный метод для создания тестов в курсе - см. SubjectController
    public function create($form)
    {
        $values = $form->getValues();

        $subjectId = (int) $this->_getParam('subject_id', 0);
        unset($values['quest_id']);
        unset($values['stepper']);
        $classifierLinkType = $this->getService('Quest')->getClassifierLinkType($values['type']);

        $classifiers = $form->getClassifierValues();

        foreach($values as $key => $value) {
            if (preg_match('/classifier_/', $key)) {
                unset($values[$key]);
            }
        }

        if ($values['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
            $values['mode_selection_questions'] = $values['mode_selection_questions_cluster'];
        }
        unset($values['mode_selection_questions_cluster']);

        if ($values['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS) {
            $values['mode_display_clusters'] = null;
        }
        if ($values['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS) {
            $values['mode_display_questions'] = null;
        }

        if (isset($values['poll_mode'])) {
            if ($values['poll_mode'] != HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE) {
                $values['scale_id'] = 0;
            }
            unset($values['poll_mode']);
        }

        if (!isset($values['mode_test_page'])) $values['mode_test_page'] = 1;

        $this->_quest = $this->getService('Quest')->insert($values);

        if ($this->_quest && count($values['tags'])) {
            $this->getService('Tag')->updateTags($values['tags'], $this->_quest->getValue('quest_id'), $this->getService('TagRef')->getTestType() );
        }

        if ($this->_quest) {
            $this->getService('Classifier')->unlinkItem($this->_quest->getValue('quest_id'), $classifierLinkType);
            if (is_array($classifiers) && count($classifiers)) {
                foreach ($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($this->_quest->getValue('quest_id'), $classifierLinkType, $classifierId);
                    }
                }
            }
        }
    }

    // есть еще аналогичный метод для редактирования тестов в курсе - см. SubjectController
    public function update($form)
    {
        $values = $form->getValues();

        $subjectId = (int) $this->_getParam('subject_id', 0);
        $quest = $this->getService('Quest')->getOne($this->getService('Quest')->find($values['quest_id']));
        if (!$quest || !$this->getService('Quest')->isEditable($values['quest_id'], $subjectId)) return false;

        unset($values['stepper']);
        $values['type'] = $this->_quest->type;
        $classifierLinkType = $this->getService('Quest')->getClassifierLinkType($values['type']);

        $classifiers = $form->getClassifierValues();

        foreach($values as $key => $value) {
            if (preg_match('/classifier_/', $key)) {
                unset($values[$key]);
            }
        }

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

        if ($this->_quest->scale_id > 0) {
            if (!$values['scale_id']) {
                $values['scale_id'] = $this->_quest->scale_id;
            }
        } else {
            $values['scale_id'] = 0;
        }
        unset($values['poll_mode']);

        $res = $this->getService('Quest')->update($values);

        if (count($values['tags'])) {
            $this->getService('Tag')->updateTags($values['tags'], $values['quest_id'], $this->getService('TagRef')->getTestType());
        }

        if ($res) {
            $this->getService('Classifier')->unlinkItem($values['quest_id'], $classifierLinkType);
            if (is_array($classifiers) && count($classifiers)) {
                foreach ($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($values['quest_id'], $classifierLinkType, $classifierId);
                    }
                }
            }
        }
    }

    public function delete($id)
    {
        if ($this->getService('Quest')->isDeletable($id)) {
            $this->getService('Quest')->delete($id);
        }
    }

    public function setDefaults(Zend_Form $form)
    {
        $data = $this->_quest->getData();
        if ($data['limit_attempts'] === '0') $data['limit_attempts'] = '';
        if ($data['limit_time'] === '0') $data['limit_time'] = '';

        $data['tags'] = $this->getService('Tag')->convertAllToStrings($this->getService('Tag')->getTags($this->_quest->quest_id, $this->getService('TagRef')->getTestType()));

        if ($data['scale_id']) {
            $data['poll_mode'] = HM_Quest_Type_PollModel::QUESTIONS_TYPE_SCALE;
        }

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

//        $classifiers = [];
//        $classifierLinkType = $this->getService('Quest')->getClassifierLinkType($data['type']);
//        $itemClassifiers = $this->getService('Classifier')->getItemClassifiers($data['quest_id'], $classifierLinkType);
//        foreach ($itemClassifiers as $itemClassifier) {
//            $classifiers['classifier_' . $itemClassifier->type] = $itemClassifier->classifier_id;
//        }
//        $data = array_merge($data, $classifiers);

        $form->populate($data);
    }


    protected function _redirectToIndex()
    {
        $onlyType   = $this->_getParam('only-type', HM_Quest_QuestModel::TYPE_TEST);
        $actionName = $this->getRequest()->getActionName();
        if ($this->_quest->quest_id && !in_array($actionName, array('delete', 'delete-by'))) {
            $this->_redirector->gotoSimple('list', 'question', null, array('quest_id' => $this->_quest->quest_id));
        }

        $typeMap = array(
            HM_Quest_QuestModel::TYPE_TEST => 'tests',
            HM_Quest_QuestModel::TYPE_POLL => 'polls',
            HM_Quest_QuestModel::TYPE_PSYCHO => 'psycho',
            HM_Quest_QuestModel::TYPE_FORM => 'form'
        );

        $this->_redirector->gotoSimple($typeMap[$onlyType], 'list');
    }

    public function updateType($type)
    {
        $types = HM_Quest_QuestModel::getTypes();
        return isset($types[$type]) ? $types[$type] : '';
    }

    public function updateActions($questId, $actions)
    {
        if (!$this->getService('Quest')->isDeletable($questId)) {
            $this->unsetAction($actions, array('module' => 'quest', 'controller' => 'list', 'action' => 'delete', 'baseUrl' => ''));
        }
        if (!$this->getService('Quest')->isEditable($questId)) {
            $this->unsetAction($actions, array('module' => 'quest', 'controller' => 'list', 'action' => 'edit', 'baseUrl' => ''));
        }

        return $actions;
    }

    public function updateStatus($status)
    {
        $statuses = HM_Quest_QuestModel::getStatuses();
        return $statuses[$status];
    }
}
?>
