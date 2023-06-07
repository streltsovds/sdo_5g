<?php
class Quest_QuestionController extends HM_Controller_Action_Quest
{
    use HM_Controller_Action_Trait_Grid;

    const FILTER_USED = 0;
    const FILTER_ALL  = 1;
    protected $_question;
    protected $_quest;
    protected $_questsCache;

    public function init()
    {
        $form = new HM_Form_Question();
        $this->_setForm($form);
        $this->addModifier($form);

        parent::init();

        $questId = (int) $this->_getParam('quest_id', 0);
        if ($questId) $this->gridId = 'grid' . $questId;
        $questionId = (int) $this->_getParam('question_id', 0);
        if ($questionId) {
            $this->_question = $this->getOne(
                $this->getService('QuestQuestion')->find($questionId)
            );
            $type = $this->_question->type;
        } else {
            $type = $this->_getParam('type');
        }

        if (in_array($type, array(
            HM_Quest_Question_QuestionModel::TYPE_FREE,
            HM_Quest_Question_QuestionModel::TYPE_FILE,
        ))) {
            $form->removeSubForm('questionStep2');
        }

        if(!empty($this->_quest)) {
            $this->setActiveContextMenu('mca:quest:question:list');
        }

    }

    /**
     * @param HM_Form_Question $form
     */
    protected function addModifier($form)
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        $questionElement = $form->getSubForm('questionStep1')->getElement('question');

        if ($request->isPost()
            && $questionElement
            && in_array($request->getParam('type'), [HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER])) {
            $questionElement->addValidator('Contains', false, array('needle' => ['[', ']'], 'hint' => 'Вы не выставили специальные знаки "[" и "]"'));
        }
    }

    public function listAction()
    {
        $switcher = $this->getSwitcherSetOrder(null, 'used_DESC');
        $subjectId = $this->getParam('subject_id', 0);
        $lessonId = $this->getParam('lesson_id', 0);

        if ($lessonId) {
            $lesson = $this->getService('Lesson')->getOne(
                $this->getService('Lesson')->findDependence('Subject', $lessonId)
            );

            if($lesson && $this->getService('User')->isEndUser()) {

                // В контексте курса показываем следующее занятие
                $this->view->replaceSidebar($this->_quest ? $this->_quest->type : HM_Quest_QuestModel::TYPE_TEST, 'subject-lesson', [
                    'model' => $lesson,
                ]);
            }
        }

        $questionIds = $this->getService('QuestQuestionQuest')
            ->fetchAll($this->quoteInto('quest_id = ?', $this->_quest->quest_id))
            ->getList('question_id');
        if (!count($questionIds)) $questionIds = array(0);

        $select = $this->getService('QuestQuestion')->getSelect();
        $select->from(
            array(
                'qq' => 'quest_questions'
            ),
            array(
                'qq.question_id',
                'question_subject_id' => 'qq.subject_id',
                'qq.shorttext',
                'type' => 'qq.type',
                'quests' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT qqq2.quest_id)'),
                'used' => new Zend_Db_Expr($this->quoteInto(
                    array('CASE WHEN qq.question_id in (?) THEN 1 ELSE 0 END'),
                    array($questionIds)
                )),
                'q_order' => 'qq.order'
            )
        );

        if ($this->_quest->scale_id) {
            $scaleWhere = '(q.scale_id <> 0)';
        } else {
            $scaleWhere = '(q.scale_id = 0)';
        }

        /*
         * q - текущий тест
         * q2 - другие тесты за искл.текущего
         */
        $select
            ->joinLeft(array('qqq' => 'quest_question_quests'), 'qq.question_id = qqq.question_id', array())
            ->joinLeft(array('qqq2' => 'quest_question_quests'), 'qq.question_id = qqq2.question_id AND qqq2.quest_id != ' . $this->_quest->quest_id, array())
            ->joinLeft(array('q' => 'questionnaires'), 'q.quest_id = qqq.quest_id', array())
            ->where('(qq.quest_type is NULL OR qq.quest_type= ?)',  $this->_quest->type)
            ->where($scaleWhere);
           // ->where('(qq.subject_id = 0 OR qq.subject_id = ?)', $this->_quest->subject_id);

        if ($this->_quest->type) {
            $select->where('q.type = ?', $this->_quest->type);
        }

        if ($this->_quest->type == HM_Quest_QuestModel::TYPE_POLL && $switcher) {
            $select
                ->joinLeft(array('qpoll' => 'questionnaires'), 'qqq.quest_id = qpoll.quest_id', array())
                ->where('(qpoll.scale_id = 0)');
        }

        $group = array(
            'qq.question_id',
            'qq.type',
            'qq.shorttext',
            'qq.subject_id',
            'qq.order'
        );

        if (!$switcher) {
            $select
                ->where('q.quest_id = ?', $this->_quest->quest_id)
                ->joinLeft(array('qc' => 'quest_clusters'), 'qqq.cluster_id = qc.cluster_id', array('cluster' => 'qc.name'));
            $group[] = 'qc.name';
        } else {
            // В курсе видим вопросы из других тестов курса, в БЗ - только из тестов БЗ
            $select->where('qq.subject_id = ?', $subjectId);
        }

        $select->group($group);

        $switchLabels = $this->_quest->getQuestionListLabels();

        $grid = $this->getGrid(
            $select,
            [
                'question_id' => array('hidden' => true),
                'used' => array('hidden' => true),
                'question_subject_id' => array('hidden' => true),
                'shorttext' => array(
                    'title' => _('Краткий текст'),
                    'decorator' => '<img src="/images/icons/questions/{{type}}.svg">{{shorttext}}',
                ),
                'type' => array(
                    'title' => _('Тип'),
                    'callback' => array(
                        'function' => array($this, 'updateTypeColumn'),
                        'params' => array('{{type}}')
                    )
                ),
                'quests'  => array(
                    'title' => $switchLabels[2],
                    'callback' => array(
                        'function' => array($this, 'questsCallback'),
                        'params' => array('{{quests}}', $select, $this->_quest->type)
                    ),
                    'color' => HM_DataGrid_Column::colorize('quests')
                ),
                'cluster' => array('title' => _('Блок вопросов')),
                'q_order' => array('title' => _('Порядок'))
            ],
            [
                'type' => array('values' => $this->_quest->getAvailableTypes()),
                'quests' => array(
                    'callback' => array(
                        'function' => array($this, 'guestsFilter'),
                    ),
                ),
                'shorttext' => null,
                'cluster' => null,
            ],
            $this->gridId
        );
        $grid->setClassRowCondition("'{{used}}' == 1", 'success');

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
        ))) {
            $grid->setGridSwitcher(
                [
                    'label' => _('Показать все'),
                    'title' => _('Показать') . ' ' . $switchLabels[1],
                    'param' => HM_DataGrid::SWITCHER_PARAM_DEFAULT,
                    'modes' => [
                        Quest_QuestionController::FILTER_USED,
                        Quest_QuestionController::FILTER_ALL,
                    ],
                ]
            );
        }

        if ($switcher) {
            $grid->addAction(array(
                    'module'     => 'quest',
                    'controller' => 'question',
                    'action'     => 'copy'
                ),
                array('question_id'),
                _('Использовать')
            );

            if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
                HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            ))) {
                $grid->addMassAction(
                    array(
                        'module'     => 'quest',
                        'controller' => 'question',
                        'action'     => 'copy',
                    ),
                    $switchLabels[3],
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }

        } else {
            $grid->addAction(array(
                    'module' => 'quest',
                    'controller' => 'question',
                    'action' => 'edit'
                ),
                array('question_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                    'module' => 'quest',
                    'controller' => 'question',
                    'action' => 'delete'
                ),
                array('question_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
                HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            ))) {
                if ($this->_quest->type != HM_Quest_QuestModel::TYPE_POLL || $this->_quest->scale_id == 0) {
                    $grid->addMassAction(
                        array(
                            'module'     => 'quest',
                            'controller' => 'question',
                            'action'     => 'uncopy',
                        ),
                        $switchLabels[4],
                        $switchLabels[5]
                    );
                }

                $grid->addMassAction(
                    array(
                        'module' => 'quest',
                        'controller' => 'question',
                        'action' => 'delete-by',
                    ),
                    _('Удалить'),
                    $this->_quest->subject_id ? $switchLabels[6] : $switchLabels[7]
                );

                $grid->addMassAction(
                    array(
                        'module' => 'quest',
                        'controller' => 'question',
                        'action' => 'duplicate',
                    ),
                    _('Копировать'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );

                $grid->addMassAction(
                    array(
                        'module' => 'quest',
                        'controller' => 'question',
                        'action' => 'assign-block',
                    ),
                    _('Назначить блок'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );


                $grid->addSubMassActionSelect(
                    $this->view->url(
                        array(
                            'module' => 'quest',
                            'controller' => 'question',
                            'action' => 'assign-block',
                        )
                    ),
                    'cluster_id',
                    $this->getService('QuestCluster')->getQuestClusters($this->_quest->quest_id),
                    false
                );
            }

            $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                    'params'   => array('{{question_subject_id}}', '{{question_id}}')
                ));
        }

        $grid->addAction(array(
                    'module' => 'quest',
                    'controller' => 'lesson',
                    'action' => 'info'
                ),
                array('question_id'),
                $this->view->svgIcon('preview', _('Предварительный просмотр'))
            );


        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
        ))) {
            $grid->addMassAction(
                array(
                    'module'     => 'quest',
                    'controller' => 'question',
                    'action'     => 'export',
                ),
                _('Экспортировать вопросы')
            );
        }

        $this->view->quest = $this->_quest;
        $this->view->grid  = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

        if ($this->_quest->type == HM_Quest_QuestModel::TYPE_POLL) {
            $this->view->import = false;
        } else {
            $this->view->import = true;
        }
    }

    /**
     * Почти копия @see Quest_ListController::importAction()
     */
    public function importAction()
    {
        $this->view->setSubHeader(_('Импорт вопросов'));

        $form = new HM_Form_QuestionImport();

        $request = $this->getRequest();
        $questId = $this->_getParam('quest_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);

        if ($questId && $request->isPost()) {

            if ($form->isValid($request->getParams())) {
                $file = $form->getElement('file');
                $file->receive();

                if ($file->isReceived()) {
                    $filename = $file->getFileName();
                    $fileRealpath = realpath($filename);
                    $fileType = HM_Files_FilesModel::getFileType($filename);

                    /** @var HM_Material_MaterialService $materialService */
                    $materialService = $this->getService('Material');

                    if (is_file($fileRealpath)
                        and is_readable($fileRealpath)
                        and in_array($fileType, [HM_Files_FilesModel::FILETYPE_XLSX, HM_Files_FilesModel::FILETYPE_TEXT])
                    ) {

                        $test = $this->getService('Quest')->find($questId)->current();
                        $result = $materialService->importQuestions($fileRealpath, $subjectId, $test);
                    }
                }

                if ($result) {
                    $this->_flashMessenger->addMessage(_('Импорт вопросов прошел успешно'));

                    if ($result instanceof HM_Quest_QuestModel) {
                        $this->_redirector->gotoSimple('list', 'question', 'quest', array('quest_id' => $result->quest_id, 'subject_id' => $subjectId));
                    }
                } else {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('При импорте вопросов возникла ошибка')
                    ));
                }

                $this->_redirector->gotoSimple('tests', 'list', 'quest');
            }
        }

        $this->view->form = $form;
    }

    protected function _redirectToIndex()
    {
        $params = ['quest_id' => $this->_quest->quest_id];

        if($subjectId = (int) $this->_getParam('subject_id', 0))
            $params['subject_id'] = $subjectId;

        if($lessonId = (int) $this->_getParam('lesson_id', 0))
            $params['lesson_id'] = $lessonId;

        $this->_redirector->gotoSimple('list', null, null, $params);
    }

    public function newAction()
    {
        $this->view->setSubSubHeader(_('Создание вопроса'));
        $form = $this->_getForm();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if ($result != NULL && $result !== TRUE) {
                    $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)]);
                    $this->_redirectToIndex();
                } else {
                    $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_INSERT));
                    $this->_redirectToIndex();
                }
            } else {
                $this->setDefaults($form);
            }
        }

        $this->view->form = $form;
    }

    public function create($form)
    {
        $this->view->setSubSubHeader(_('Создание вопроса'));

        $data = $form->getValues();
        $data['quest_type'] = $this->_quest->type;
        $data['subject_id'] = $this->_quest->subject_id; // нужно сохранить принадлежность к курсу чтобы не были видны в БЗ

        if ($data['variants_use_wysiwyg']) {
            $data = $this->toMultiselectVariants($data);
        }

        if ($data['type'] == HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER) {
            $data= $this->toPlacehoderVariants($data);
        }

        $data = $this->processVariants($data);

        $question = $this->getService('QuestQuestion')->insert($data);
        $this->getService('QuestQuestionQuest')->insert(array(
            'quest_id'    => $this->_quest->quest_id,
            'cluster_id'  => $data['cluster_id'],
            'question_id' => $question->question_id,
        ));
    }

    public function update($form)
    {
        $this->view->setSubSubHeader(_('Редактирование вопроса'));
        if (empty($data['type'])) $data['type'] = $this->_question->type;

        $data = $form->getValues();
        $data['quest_id'] = $this->_quest->quest_id;

        if ($data['variants_use_wysiwyg']) {
            $data = $this->toMultiselectVariants($data);
        }

        if ($data['type'] == HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER) {
            $data= $this->toPlacehoderVariants($data);
        }

        $data = $this->processVariants($data);

        $question = $this->getService('QuestQuestion')->update($data);
        $this->getService('QuestQuestionQuest')->update(array(
            'quest_id'    => $this->_quest->quest_id,
            'cluster_id'  => $data['cluster_id'],
            'question_id' => $question->question_id,
        ));
    }

    public function processVariants($data) {
        foreach($data['variants'] as $key=>&$variant) {
            if($key=='new') {
                foreach($variant['variant']  as $key=>$var) {
                    $variant['category_id'][$key] = serialize(explode(',', $variant['category_id'][$key]));
                }
            } else {
                $variant['category_id'] = serialize(explode(',', $variant['category_id']));
            }

        }

        return $data;
    }

    public function toPlacehoderVariants($data) {
        $count = preg_match_all(HM_Quest_Question_Type_PlaceholderModel::PLACEHOLDER_PATTERN, $data['question'], $matches);

        if($count > 0) {
            $variantName  = 'variants_variant_';
            $variantId  = 'variants_id_';
            $variantType  = 'variants_type_';
            for ($i = 1; $i <= $count; $i++) {

                $variantList = array();

                foreach ($data[$variantName.$i] as $varKey => $variant) {
//                    if ($data[$variantType.$i] == HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_INPUT) {
//                        $variant['is_correct'] = '1';
//                    }
                    if ($varKey === HM_Form_Element_MultiSet::ITEMS_NEW) {
                        foreach ($variant['variant'] as $k => $oneVariant) {
                            $variantText = $oneVariant;
                            if ($variantText) {
                                if ($variant['is_correct'][$k] == "0") {
                                    $variantText = HM_Quest_Question_Type_PlaceholderModel::VARIANT_WRONG_MARKER.$oneVariant;
                                }
                                $variantList[] = $variantText;
                            }
                        }
                    } else {
                        $variantText = $variant['variant'];
                        if ($variantText) {
                            if ($variant['is_correct'] == "0") {
                                $variantText = HM_Quest_Question_Type_PlaceholderModel::VARIANT_WRONG_MARKER.$variantText;
                            }
                            $variantList[] = $variantText;
                        }
                    }

                }

                $varData = array(
                    'is_correct' => 1,
                    'variant' => implode(';',$variantList),
                    'data' => serialize(array('mode_display'=> $data[$variantType.$i]))
                );
                if ($data[$variantId.$i]) {
                    $key = $data[$variantId.$i];
                    $data['variants'][$key] = $varData;

                } else {
                    $data['variants']['new']['is_correct'][] = $varData['is_correct'];
                    $data['variants']['new']['variant'][] = $varData['variant'];
                    $data['variants']['new']['data'][] = $varData['data'];
                }
                unset($data[$variantId.$i]);
                unset($data[$variantName.$i]);
                unset($data[$variantType.$i]);

            }
        }
        return $data;
    }

    public function toMultiselectVariants($data) {
        $types = array(
            HM_Quest_Question_QuestionModel::TYPE_SINGLE,
            HM_Quest_Question_QuestionModel::TYPE_MULTIPLE,
            HM_Quest_Question_QuestionModel::TYPE_SORTING,
            HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION,
            HM_Quest_Question_QuestionModel::TYPE_MAPPING,
            HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER
        );

        $variantsName = HM_Form_QuestionStep2::DEFAULT_ELEMENT;

        if(in_array($data['type'], $types)){
            if($data['type'] == HM_Quest_Question_QuestionModel::TYPE_SORTING ||
                $data['type'] == HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION ||
                $data['type'] == HM_Quest_Question_QuestionModel::TYPE_MAPPING
            ){
                $fieldName = 'data';
            } else {
                $fieldName = 'is_correct';
            }

            $newVariants = array(
                $fieldName => array(),
                'variant'  => array(),
                'weight' => array()
            );
            $oldVariants = array();

            foreach($data as $key => $value) {
                $elementName = explode('_', $key);

                $elementPrefix = $elementName[0];
                $elementType   = $elementName[1];
                $elementNumber = $elementName[2];
                if($elementPrefix.'_'.$elementType == $variantsName.'_'.'variant'){
                    if($data['type'] == HM_Quest_Question_QuestionModel::TYPE_SORTING){
                        $fieldValue = $data[$variantsName.'_'.'number_hidden'.'_'.$elementNumber];
                    } else if(
                        $data['type'] == HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION ||
                        $data['type'] == HM_Quest_Question_QuestionModel::TYPE_MAPPING
                    ){
                        $fieldValue = $data[$variantsName.'_'.'data'.'_'.$elementNumber];
                    } else {
                        $fieldValue = $data[$variantsName.'_'.'checkbox'.'_'.$elementNumber];
                    }

                    $variant    = $value;
                    $variantId  = $data[$variantsName.'_'.'id'.'_'.$elementName[2]];
                    $weight = ($data[$variantsName.'_'.'weight'.'_'.$elementNumber]) ?
                        $data[$variantsName.'_'.'weight'.'_'.$elementNumber] : 0;

                    if($variantId){
                        $oldVariants[$variantId] = array(
                            $fieldName => $fieldValue,
                            'variant'  => $variant,
                            'weight' => $weight
                        );
                    } else {
                        if(trim($variant) != ''){
                            $newVariants[$fieldName][] = $fieldValue;
                            $newVariants['variant'][]  = $variant;
                            $newVariants['weight'][]  = $weight;
                        }
                    }
                    if($data['type'] == HM_Quest_Question_QuestionModel::TYPE_SORTING){
                        unset($data[$variantsName.'_'.'number_hidden'.'_'.$elementName[2]]);
                        unset($data[$variantsName.'_'.'number'.'_'.$elementName[2]]);
                        unset($data[$variantsName.'_'.'data'.'_'.$elementName[2]]);
                    } else if(
                        $data['type'] == HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION ||
                        $data['type'] == HM_Quest_Question_QuestionModel::TYPE_MAPPING
                    ){
                        unset($data[$variantsName.'_'.'data'.'_'.$elementName[2]]);
                    } else {
                        unset($data[$variantsName.'_'.'checkbox'.'_'.$elementName[2]]);
                        unset($data[$variantsName.'_'.'weight'.'_'.$elementName[2]]);
                    }
                    unset($data[$variantsName.'_'.'variant'.'_'.$elementName[2]]);
                    unset($data[$variantsName.'_'.'id'.'_'.$elementName[2]]);
                }
            }
            if(count($newVariants)){
                $oldVariants[HM_Form_Element_MultiSet::ITEMS_NEW] = $newVariants;
            }
            $data['variants'] = $oldVariants;
        }
        return $data;
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('question_id', 0);
        if ($id) {
            $this->getService('QuestQuestion')->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
        if (strlen($postMassIds)) {

            $postMassIds = explode(',', $postMassIds);

            $questions = $this->getService('QuestQuestion')->fetchAll($this->quoteInto(
                array(
                    'question_id IN (?)'
                ),
                array(
                    $postMassIds
                )
            ));

            if (count($questions)) {
                foreach($questions as $question) {
                    if ($this->_quest->subject_id && !$question->subject_id) continue; // нельзя удалять вопросы из БЗ
                    $this->getService('QuestQuestion')->delete($question->question_id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function fromMultiselectVariants($data)
    {
        $types = [
            HM_Quest_Question_QuestionModel::TYPE_SINGLE,
            HM_Quest_Question_QuestionModel::TYPE_MULTIPLE,
            HM_Quest_Question_QuestionModel::TYPE_SORTING,
            HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION,
            HM_Quest_Question_QuestionModel::TYPE_MAPPING,
            HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER
        ];

        if (count($data['variants'])) {
            if (in_array($data['type'], $types)) {
                $i = 1;
                foreach ($data['variants'] as $variantId => $variantValue) {
                    if ($data['type'] == HM_Quest_Question_QuestionModel::TYPE_SORTING) {
                        $data['variants_number_' . $i] = $variantValue['data'];
                        $data['variants_number_hidden_' . $i] = $variantValue['data'];
                    } else if (
                        $data['type'] == HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION ||
                        $data['type'] == HM_Quest_Question_QuestionModel::TYPE_MAPPING
                    ) {
                        $data['variants_data_' . $i] = $variantValue['data'];
                    } else {
                        $data['variants_checkbox_' . $i] = $variantValue['is_correct'];
                        $data['variants_weight_' . $i] = $variantValue['weight'];
                    }
                    $data['variants_variant_' . $i] = $variantValue['variant'];
                    $data['variants_id_' . $i] = $variantId;
                    unset($data[$variantId]);
                    $i++;
                }
            }
        }
        return $data;
    }

    public function setDefaults(Zend_Form $form)
    {
        $questionId = $this->_getParam('question_id', 0);
        $question = $this->getService('QuestQuestion')->findDependence(['Variant', 'QuestionQuest'], $questionId);

        if (count($question)) {
            /** @var HM_Quest_QuestModel $question */
            $question = $question->current();
        } else {
            $this->setDefaultsFromRequest($form);
            return true;

        }
        $data = $question->getData();

        if (count($data['variants'])) {
            $data['variants'] = $data['variants']->asArrayOfArrays();
            ksort($data['variants']);
            foreach($data['variants'] as &$variant) {
                if(intval($variant['category_id']).''!==$variant['category_id']) {//не число - значит сериализованное
                    $variant['category_id'] = @unserialize($variant['category_id']);
                } else {
                    $variant['category_id'] = array($variant['category_id']);
                }
            }

        }
        if ($data['type'] == HM_Quest_Question_QuestionModel::TYPE_IMAGEMAP) {
            $data['variants'] = [
                'show_variants' => (string)$data['show_variants'],
                'file_id' => $data['file_id'],
                'variants' => $data['variants']
            ];
        }

        // TODO запилиль конверт при изменении настройки
        //$data['variants_use_wysiwyg'] = 1;

        if ($data['variants_use_wysiwyg']) {
            $data = $this->fromMultiselectVariants($data);
        }

        if ($data['type'] == HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER) {
            $i = 1;
            if (count($data['variants'])) {
                foreach ($data['variants'] as $variantId => $variantValue) {
                    $subVariants = explode(HM_Quest_Question_Type_PlaceholderModel::VARIANT_VARIANTS_DELIMETER, $variantValue['variant']);
                    foreach ($subVariants as $k => $subVariant) {
                        $data['variants_variant_' . $i][$k] = [
                            'is_correct' => (strpos($subVariant, HM_Quest_Question_Type_PlaceholderModel::VARIANT_WRONG_MARKER) === false) ? '1' : '0',
                            'variant' => trim($subVariant, HM_Quest_Question_Type_PlaceholderModel::VARIANT_WRONG_MARKER),
                            'question_id' => $data['question_id'],
                            'question_variant_id' => "" . $k,

                        ];
                    }

                    $data['variants_id_' . $i] = $variantId;
                    $varData = unserialize($variantValue['data']);
                    $varType = HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_INPUT;
                    if (isset($varData['mode_display'])) {
                        $varType = $varData['mode_display'];
                    }

                    $data['variants_type_' . $i] = $varType;

                    $i++;
                }
            }

        }

        if (count($data['questionQuest'])) {
            foreach ($data['questionQuest'] as $questionQuest) {
                if ($questionQuest->quest_id == $this->_quest->quest_id) {
                    $data['cluster_id'] = $questionQuest->cluster_id;
                }
            }
        }

        // дефолтные значения, которые не меняются через форму (hidden)
        // в самой форме задать нельзя, т.к злой баг
        switch ($this->_quest->type) {
            case HM_Quest_QuestModel::TYPE_PSYCHO:
                $data['show_free_variants'] = HM_Quest_Question_QuestionModel::SHOW_FREE_VARIANT_OFF;
                $data['mode_scoring'] = HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT;
                break;
            case HM_Quest_QuestModel::TYPE_FORM:
                $data['mode_scoring'] = HM_Quest_Question_QuestionModel::MODE_SCORING_OFF;
                break;
            case HM_Quest_QuestModel::TYPE_TEST:
                $data['type_dummy'] = $question->type;
                // @todo
                break;
        }
//pr($data['variants'][3028]['category_id'] = array("4", "6"));
//die();
        $form->populate($data);
    }


    private function setDefaultsFromRequest(Zend_Form $form)
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        $counter = 1;
        $variants =$request->getParam('variants__variant__new', []);
        $isCorrect = $request->getParam('variants__is_correct__new', []);

        $data = [];
        foreach ($variants as $key => $variant) {
            $data['variants'][$key] = [
                'data' => $counter++,
                'variant' => $variant,
                'is_correct' => $isCorrect[$key]
            ];
        }

        $form->populate($data);
    }

    public function copyAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
        $questionIds = explode(',', (strlen($postMassIds)) ? $postMassIds : $this->_getParam('question_id', '0'));

        if ($questionIds) {
            $duplicated = $this->getService('QuestQuestionQuest')->fetchAll($this->quoteInto(
                array('question_id in (?)', 'AND quest_id=?'),
                array($questionIds, $this->_quest->quest_id)))->getList('question_id');

            foreach ($questionIds as $questionId) {
                if (in_array($questionId, $duplicated)) {
                    continue;
                }
                $this->getService('QuestQuestionQuest')->insert(array(
                    'quest_id'    =>  $this->_quest->quest_id,
                    'question_id' =>  $questionId,
                    'cluster_id'  =>  0,
                ));
            }

            $switchLabels = $this->_quest->getQuestionListLabels();

            if (count($questionIds) == 1) {
                $this->_flashMessenger->addMessage($switchLabels[8]);
            } else {
                $this->_flashMessenger->addMessage($switchLabels[9]);
            }
        }
        $this->_redirectToIndex();
    }

    public function uncopyAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
        $questionIds = explode(',', (strlen($postMassIds)) ? $postMassIds : $this->_getParam('question_id', '0'));

        $this->getService('QuestQuestionQuest')->deleteBy($this->quoteInto(
                array('question_id in (?)', 'AND quest_id=?'),
                array($questionIds, $this->_quest->quest_id)));

        $switchLabels = $this->_quest->getQuestionListLabels();

        if (count($questionIds) == 1) {
            $this->_flashMessenger->addMessage($switchLabels[10]);
        } else {
            $this->_flashMessenger->addMessage($switchLabels[11]);
        }
        $this->_redirectToIndex();
    }

    public function duplicateAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('QuestQuestion')->copy($id, $this->_quest->quest_id);
                }
                $this->_flashMessenger->addMessage(_('Вопросы успешно дублированы'));
            }
        }
        $this->_redirectToIndex();
    }

    public function assignBlockAction()
    {
        $clusterId = $this->_getParam('cluster_id');
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');

        if ($clusterId && strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $this->getService('QuestQuestionQuest')->updateWhere(array(
                    'cluster_id' => $clusterId
                ), array(
                    'quest_id=?' => $this->_quest->quest_id,
                    'question_id IN (?)' => $ids
                ));
                $this->_flashMessenger->addMessage(_('Вопросы успешно занесены в блок'));
            }
        }
        $this->_redirectToIndex();
    }

    public function updateActions($subjectId, $questionId, $actions)
    {
        // если тест из базы знаний
        if (!$subjectId && $this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
            $this->unsetAction($actions, ['controller' => 'question', 'action' => 'edit']);
            $this->unsetAction($actions, ['controller' => 'question', 'action' => 'delete']);
        }

        if (!$this->getService('QuestQuestion')->isDeletable($questionId)) {
            $this->unsetAction($actions, ['module' => 'quest', 'controller' => 'question', 'action' => 'delete', 'baseUrl' => '']);
        }
        if (!$this->getService('QuestQuestion')->isEditable($questionId)) {
            $this->unsetAction($actions, ['module' => 'quest', 'controller' => 'question', 'action' => 'edit', 'baseUrl' => '']);
        }

        return $actions;
    }

    public function questsCallback($field, $select, $type)
    {
        if ($this->_questsCache === null) {
            $result = $select->query()->fetchAll();
            $tmp = array();
            foreach ($result as $row) {
                $tmp[] = $row['quests'];
            }
            $tmp = explode(',', implode(',', $tmp));
            $tmp = array_unique(array_filter($tmp));

            $this->_questsCache = array();
            if (is_array($tmp) && count($tmp)) {
                $this->_questsCache = $this->getService('Quest')
                    ->fetchAll($this->quoteInto('quest_id in (?)', $tmp))
                    ->getList('quest_id', 'name');
            }
        }

        $questionIds = explode(',', $field);

        $result = array();
        foreach ($questionIds as $questionId) {
            if (isset($this->_questsCache[$questionId])) {
                $result[] = $this->_questsCache[$questionId];
            }
        }

        if (!$result) {
            return '';
        }

        $txt = (count($result) > 1) ? '<p class="total">'. $this->getService('Quest')->pluralTestCount(count($result), $type) . '</p>' : '';
        foreach ($result as $item) {
            $txt .= "<p>$item</p>";
        }
        return $txt;

    }

    public function exportAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');

        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $txt = $this->getService('QuestQuestion')->getTxt($ids);
                if(strlen($txt)){
                    $oldEncoding = mb_internal_encoding();
                    mb_internal_encoding("Windows-1251");
                    $this->_helper->SendFile->sendData(
                        $txt,
                        'text/plain; charset='.Zend_Registry::get('config')->charset,
                        'questions.txt'
                    );
                    mb_internal_encoding($oldEncoding);
                    die();
                } else {
                    $this->_flashMessenger->addMessage(_('Не выбран ни один подходящий вопрос!'));
                    $this->_redirectToIndex();
                }
            }
        }
    }

    public function guestsFilter($data)
    {
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 0){

            $fetch = $this->getService('Quest')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));
            $data = $fetch->getList('quest_id', 'name');

            if ($data) {
                $select->where('qqq2.quest_id IN (?)', array_keys($data));
            } else {
                $select->where('qqq2.quest_id IN (?)',0);
            }
        }
    }

    public function updateTypeColumn($type)
    {
        $questionTypes = HM_Quest_Question_QuestionModel::getTypes();
        return !empty($questionTypes[$type]) ? $questionTypes[$type] : '';
    }


}
