<?php
class Question_ListController extends HM_Controller_Action
{
    private $_isEditable = false;

    private $testNameLen = 300;

    private $_cacheKnowledgeBaseQuestions = null;

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $subjectId = (int) $this->_getParam('subject_id', 0);
            $testId    = (int) $this->_getParam('test_id', 0);
            if ($subjectId || ($testId && !$subjectId)) { // Делаем страницу расширенной
                if (!$subjectId) {
                    $this->service = 'TestAbstract';
                    $this->idParamName = 'test_id';
                    $this->idFieldName = 'test_id';
                }

                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $subject = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $subject
                    )
                );
            }
            
            $testAbstract = $this->getService('TestAbstract')->getOne($this->getService('TestAbstract')->findDependence('Question', $testId));
            if ($testAbstract && count($testAbstract->questions) && !empty($testAbstract->data)) {
                $kods = explode(HM_Question_QuestionModel::SEPARATOR, $testAbstract->data);
                if (count($testAbstract->testQuestions) != count($kods)) {
                    $this->getService('TestQuestion')->deleteBy(array('test_id = ?' => $testAbstract->test_id));
                    foreach ($kods as $kod) {
                        $this->getService('TestQuestion')->insert(array(
                            'subject_id' => $testAbstract->subject_id,
                            'test_id' => $testAbstract->test_id,
                            'kod' => $kod,
                        ));
                    }
                }
            }
        }
    }

    public function testAction()
    {
        $subjectId = (int) $this->_request->getParam('subject_id', 0);

        $testId = (int) $this->_request->getParam('test_id', 0);

        $gridId = ($subjectId) ? "grid{$testId}{$subjectId}" : 'grid'.$testId;

        $test = $this->getOne($this->getService('TestAbstract')->find($testId));

        if ($test) {
            $this->view->setSubHeader($test->title);
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);
        }

        $default = new Zend_Session_Namespace('default');
        if ($this->_isEditable && $this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher') && !isset($default->grid['question-list-test'][$gridId])) {
            // По умолчанию показываем только вопросы данного теста
            $default->grid['question-list-test'][$gridId]['filters']['test'] = $testId;
        }

        //$ids = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);

        $select = $this->getService('Test')->getSelect();
/*
        $joinSubSelect = clone $select;
        $joinSubSelect->from(array('n' => 'testneed'))->where('tid = ?', $testId);

        $subSelect = clone $select;
        $subSelect->from(
            array('t' => 'list'),
            array(
                'kod'  => 't.kod',
                'qdata' => 't.qdata',
                'qtype'  => 't.qtype',
                'test'  => new Zend_Db_Expr($testId)
            )
        )
        ->joinLeft(array('n' => $joinSubSelect) , 't.kod = n.kod', array('tid'))
       	->where("t.kod IN (?)", $ids);

        $select->from(
            array('t' => $subSelect),
            array(
                'kod',
                'qdata',
                'qtype',
                'test',
            )
        )
       	->where("kod IN (?)", $ids);*/

        $select->from(
            array('tq' => 'tests_questions'),
            array(
                'kod' => 'tq.kod',
                'qdata' => 'l.qdata',
                'qtype' => 'l.qtype',
                'tid' => 'n.tid',
                'test' => 'tc.test_id',
                'test_name' => 'ta.title',
                'subjectId' => 'tq.subject_id',
                'subjectName' => 'sbj.name',
                'qtema' => 'l.qtema',
                'ordr' => 'l.ordr'
            )
        )->joinLeft(
            array('l' => 'list'),
            'l.kod = tq.kod',
            array()
        )->joinLeft(
            array('n' => 'testneed'),
            'n.kod = tq.kod AND n.tid = tq.test_id',
            array()
        )->joinInner(
            array('ta' => 'questionnaires'),
            'ta.quest_id = tq.test_id',
            array()
        )->joinLeft(
            array('tc' => 'test_abstract'),
            'tc.test_id = tq.test_id AND tc.test_id = '.$testId,
            array()
        )->joinLeft(
            array('sbj' => 'subjects'),
            'tq.subject_id = sbj.subid',
            array()
        )->where("tq.kod <> '' OR tq.kod IS NOT NULL");

        if ($this->_isEditable
            && $this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')
            && !$default->grid['question-list-test'][$gridId]['filters']['test']
            && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
            $subjects = $this->getService('Teacher')->getSubjects();
            if (count($subjects)) {
                $select->where('tq.subject_id IN (?) OR tq.subject_id = 0 OR tq.subject_id IS NULL', $subjects->getList('subid'));
            }
        }

        if (!$this->_isEditable || !$this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')) {
            $select->where('tq.test_id = ?', $testId);
        }

      	$grid = $this->getGrid(
            $select,
            array('kod'   => array('hidden' => true),
                  'qdata' => array(
                      'title' => _('Вопрос'),
                      'decorator' => $this->view->lightDialogLink($this->view->baseUrl('test_vopros.php?kod={{kod}}&cid='.$subjectId.'&mode=2'), '{{qdata}}')
                  ),
                  'qtype' => array('title' => _('Тип')),
                  'tid'   => array('title' => _('Обяз. вопрос')),
                  'test_name' => array('title' => _('Место хранения')),
                  'test'  => array(
                      'title' => _('Используется в данном тесте'),
                      'callback' => array(
                          'function' => array($this, 'updateTest'),
                          'params' => array('{{test}}')
                      ),
                      'searchType' => '='
                  ),
                  'subjectId' => array('hidden' => true),
                  'subjectName' => array('hidden' => true),
                  'qtema' => array('title' => _('Тема')),
                  'ordr' => array('title' => _('Порядок следования'))
            ),
            array(
                'qdata' => null,
                'qtype'  => array('values'=> $this->getService('Question')->getTypes()),
                'tid'   => array('values'=> array($testId => _('Да'),'ISNULL' => _('Нет'))),
                'test_name' => null,
                'test' => array('values' => array($testId => _('Да'), 'ISNULL' => _('Нет'))),
                'qtema' => null
            ),
            $gridId
           );

        $grid->setPrimaryKey(array('kod'));

        if ($this->_isEditable && $this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')) {
            $grid->setGridSwitcher(array(
                array('name' => 'local', 'title' => _('вопросы, используемые в данном тесте'), 'params' => array('test' => $testId)),
                array('name' => 'global', 'title' => _('все, включая вопросы тестов из Базы знаний'), 'params' => array('test' => null), 'order' => 'test', 'order_dir' => 'DESC'),
            ));

            $grid->setClassRowCondition("'{{test}}' == '$testId'", "success");

            if ((!$default->grid['question-list-test'][$gridId]['filters']['test'] && !$this->isGridAjaxRequest()) || ($this->isGridAjaxRequest() && (!$this->_getParam('test'.$gridId, 0) || ($this->_getParam('test'.$gridId, 0) == 'ISNULL')))) {
                $grid->addMassAction(
                    array(
                        'module'     => 'question',
                        'controller' => 'list',
                        'action'     => 'assign-to-test'
                    ),
                    _('Использовать в данном тесте'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );

/*                $grid->addMassAction(
                    array(
                        'module'     => 'question',
                        'controller' => 'list',
                        'action'     => 'unassign-from-test'
                    ),
                    _('Не использовать в данном тесте'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );*/

            }
        }

        $grid->updateColumn('test_name', array('callback' =>
                                                array('function' => array($this,'updateTestName'),
                                					  'params'   => array(
                                                          '{{test_name}}',
                                                          '{{subjectId}}',
                                                          '{{kod}}',
                                                          '{{subjectName}}',
                                                          ((isset($default->grid['question-list-test'][$gridId]) && $default->grid['question-list-test'][$gridId]['filters']['test'] && !$this->isGridAjaxRequest())
                                                            ||($this->_getParam('test'.$gridId, 0) && $this->_getParam('test'.$gridId, 0) != 'ISNULL'))
                                                      )
                                                )
                                          )
                            );

        $grid->updateColumn('qdata', array('callback' =>
                                                array('function' => array($this,'updateQdata'),
                                					  'params'   => array('{{qdata}}')
                                                )
                                          )
                            );

        $grid->updateColumn('tid', array('callback' =>
                                                array('function' => array($this,'updateNeedle'),
                                                      'params'   => array('{{tid}}'))
                                         )
                            );
        $grid->updateColumn('qtype', array('callback' =>
                                                array('function' => array($this,'updateType'),
                                                      'params'   => array('{{qtype}}')
                                                )
                                         )
                            );


        if ($this->_isEditable) {
            $grid->addAction(array('module'     => 'question',
                                   'controller' => 'list',
                                   'action'     => 'edit'),
                             array('kod'),
                             $this->view->svgIcon('edit', 'Редактировать'));

            $grid->addAction(array('module'     => 'question',
                                   'controller' => 'list',
                                   'action'     => 'delete'),
                             array('kod'),
                             $this->view->svgIcon('delete', 'Удалить'));

            $grid->addMassAction(array('module'     => 'question',
                                       'controller' => 'list',
                                       'action'     => 'delete-by'),
                                _('Удалить'),
                                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));


            $grid->addMassAction(array('module'  => 'question',
                                    'controller' => 'list',
                                    'action'     => 'necessary'),
                                _('Пометить как обязательные'),
                                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));


            $grid->addMassAction(array('module'     => 'question',
                                       'controller' => 'list',
                                       'action'     => 'unnecessary'),
                                _('Пометить как необязательные'),
                                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

            $grid->addMassAction(array('module'  => 'question',
                                    'controller' => 'export',
                                    'action'     => 'txt'),
                                _('Экспортировать в текстовой файл'),
                                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

            $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{qtype}}', '{{kod}}', '{{test}}')
                )
            );
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->isEditable = $this->_isEditable;

    }

    public function assignToTestAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $testId = (int) $this->_getParam('test_id', 0);

        $gridId = ($subjectId) ? "grid{$testId}{$subjectId}" : 'grid'.$testId;

        $kods = $this->_getParam('postMassIds_'.$gridId, '');

        $test = $this->getOne($this->getService('TestAbstract')->find($testId));

        if ($test) {
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);

            if ($this->_isEditable) {
                if ($kods) {
                    $kods = explode(',', $kods);
                    if (count($kods)) {

                        $questions = $this->getService('Question')->findDependence('TestQuestion', $kods);
                        if (count($questions)) {

                            try {
                                $this->getService('Question')->getMapper()->getAdapter()->getAdapter()->beginTransaction();

                                $questionsToAdd = array();
                                foreach($questions as $question) {
                                    if ($question->isKnowledgeBaseQuestion()) {
                                        // Назначить
                                        if ($test->isQuestionExists($question->kod)) continue;
                                        $questionsToAdd[$question->kod] = $question->kod;
                                    } else {
                                        // Копировать и назначить
                                        $questionCopy = $this->getService('Question')->copy($question->kod, $subjectId);
                                        if ($questionCopy) {
                                            $questionsToAdd[$questionCopy->kod] = $questionCopy->kod;
                                        }
                                    }
                                }

                                if (count($questionsToAdd)) {
                                    $test->addQuestionsIds($questionsToAdd);
                                }

                                $test = $this->getService('TestAbstract')->update($test->getValues());

                                $this->getService('Question')->getMapper()->getAdapter()->getAdapter()->commit();

                                $this->_flashMessenger->addMessage(_('Вопросы успешно добавлены в тест'));

                            } catch(Zend_Db_Exception $e) {
                                $this->getService('Question')->getMapper()->getAdapter()>getAdapter()->rollBack();
                                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()));
                            }
                        } else {
                            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вопросы не найдены')));
                        }
                    }
                }
            } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете добавлять вопросы в глобальный тест')));
            }
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Тест не найден')));
        }

        $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $testId, 'subject_id' => $subjectId));
    }

    //==========================================================================
    public function exerciseAction()
    {
        $subjectId = $this->_request->getParam('subject_id', 0);
        $testId = $this->_request->getParam('exercise_id', 0);
        $test = $this->getOne($this->getService('Exercises')->find($testId));
        if ($test) {
            $this->view->setSubHeader($test->title);
        }
        /*
        // Делаем запись на создание кода вопроса и выполняем редирект
        // Это тестовый запрос. поле kod является уникальным
        $listquestion = array(
            'kod'   => '0-161',
            'qtype' => '9'
        );

        $this->getService('List')->insert($listquestion);
         *
         */


        $ids = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);

        $select = $this->getService('Exercises')->getSelect();
        $subselect = clone $select;
        $subselect->from(array('n' => 'testneed'))
        ->where('tid = ?', $testId);
        $select->from(array('t' => 'list'),array('kod'  => 't.kod',
                                                'qdata' => 't.qdata',
                                			    'qtype'  => 't.qtype')
                     )
        ->joinLeft(array('n' => $subselect) , 't.kod = n.kod', array('tid'))
       	->where("t.kod IN (?)", $ids);
//        echo $select;
//
//       	 exit();
       	$grid = $this->getGrid( $select,
                               	array('kod'   => array('hidden' => true),
                                      'qdata' => array('title' => _('Вопрос'), 'decorator' => $this->view->cardLink($this->view->baseUrl('test_vopros.php?kod={{kod}}&cid='.$subjectId.'&mode=2'),_('Карточка вопроса')).'{{qdata}}'),
                                      'qtype'  => array('title' => _('Тип')),
                                      'tid'   => array('title' => _('Обяз. вопрос'))),
                               	array('qdata' => null,
                               		  'qtype'  => array('values'=> $this->getService('Question')->getTypes()),
                                      'tid'   => array('values'=> array($testId => _('Да'),'ISNULL' => _('Нет'))))
                               );





        $grid->updateColumn('qdata', array('callback' =>
                                                array('function' => array($this,'updateQdata'),
                                					  'params'   => array('{{qdata}}')
                                                )
                                          )
                            );

        $grid->updateColumn('tid', array('callback' =>
                                                array('function' => array($this,'updateNeedle'),
                                                      'params'   => array('{{tid}}'))
                                         )
                            );
        $grid->updateColumn('qtype', array('callback' =>
                                                array('function' => array($this,'updateType'),
                                                      'params'   => array('{{qtype}}')
                                                )
                                         )
                            );




        $grid->addAction(array('module'     => 'question',
                               'controller' => 'list',
                               'action'     => 'edit'),
                         array('kod'),
                         $this->view->svgIcon('edit', 'Редактировать'));

        $grid->addAction(array('module'     => 'question',
                               'controller' => 'list',
                               'action'     => 'delete'),
                         array('kod'),
                         $this->view->svgIcon('delete', 'Удалить'));

        $grid->addMassAction(array('module'     => 'question',
                                   'controller' => 'list',
                                   'action'     => 'delete-by'),
                            _('Удалить'),
                            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));


        $grid->addMassAction(array('module'  => 'question',
        						'controller' => 'list',
        						'action'     => 'necessary'),
                            _('Пометить как обязательные'),
                            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));


        $grid->addMassAction(array('module'     => 'question',
        						   'controller' => 'list',
        						   'action'     => 'unnecessary'),
                            _('Пометить как необязательные'),
                            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

    }
    //==========================================================================


    public function quizAction()
    {
        $subjectId = $this->_request->getParam('subject_id', 0);
        $quizId = $this->_request->getParam('quiz_id', 0);

        $quiz = $this->getOne($this->getService('Poll')->find($quizId));

        if ($quiz) {
            $this->view->setSubHeader($quiz->title);

            $this->_isEditable = $this->getService('Poll')->isEditable($quiz->subject_id, $subjectId, $quiz->location);
        }

        $ids = explode(HM_Poll_PollModel::QUESTION_SEPARATOR, $quiz->data);

        $select = $this->getService('Test')->getSelect();

        $select->from(array('t' => 'list'),array('kod'  => 't.kod',
                                                'qdata' => 't.qdata',
                                			    'qtype' => 't.qtype',
                                                'ordr'  => 't.ordr')
                     )
       	->where("t.kod IN (?)", $ids);
        //echo $select;

       	// exit();
       	$grid = $this->getGrid( $select,
                               	array('kod'   => array('hidden' => true),
                                      'qdata' => array('title' => _('Вопрос'), 'decorator' => $this->view->cardLink($this->view->baseUrl('test_vopros.php?kod={{kod}}&cid='.$subjectId.'&mode=2'),_('Карточка вопроса')).'{{qdata}}'),
                                      'qtype'  => array('title' => _('Тип')),
                                      'ordr'  => array('title' => _('Порядок следования'))
                                ),
                               	array('qdata' => null,
                               		  'qtype'  => array('values'=> $this->getService('Question')->getTypes(HM_Test_TestModel::TYPE_POLL))
                                )
                                );





        $grid->updateColumn('qdata', array('callback' =>
                                                array('function' => array($this,'updateQdata'),
                                					  'params'   => array('{{qdata}}')
                                                )
                                          )
                            );

        $grid->updateColumn('tid', array('callback' =>
                                                array('function' => array($this,'updateNeedle'),
                                                      'params'   => array('{{tid}}'))
                                         )
                            );
        $grid->updateColumn('qtype', array('callback' =>
                                                array('function' => array($this,'updateType'),
                                                      'params'   => array('{{qtype}}')
                                                )
                                         )
                            );



        if ($this->_isEditable) {
            $grid->addAction(array('module'     => 'question',
                                   'controller' => 'list',
                                   'action'     => 'edit-quiz'),
                             array('kod'),
                             $this->view->svgIcon('edit', 'Редактировать'));

            $grid->addAction(array('module'     => 'question',
                                   'controller' => 'list',
                                   'action'     => 'delete-quiz'),
                             array('kod'),
                             $this->view->svgIcon('delete', 'Удалить'));

            $grid->addMassAction(array('module'     => 'question',
                                       'controller' => 'list',
                                       'action'     => 'delete-by-quiz'),
                                _('Удалить'),
                                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->isEditable = $this->_isEditable;

    }

    public function newAction()
    {
        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $testId = $_GET['tid'] = (int) $this->_getParam('test_id', 0);
        $test = $this->getOne($this->getService('TestAbstract')->find($testId));

        if ($test) {
            $this->view->setSubHeader($test->title);
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете добавлять вопросы в глобальные тесты')));
            $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $testId, 'subject_id' => $subjectId));
        }

        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $testId);
        if($this->_getParam('c') == 'add_submit'){
            $this->_setParam('c', 'add_submit');
            if($_POST['type'] == ''){
                $_POST['type'] = 1;
            }
        }elseif($this->_getParam('c') == 'main'){
            $this->_setParam('c', 'main');
        }elseif($_POST['c'] == 'edit_post'){
            $this->_setParam('c', 'edit_post');
        }elseif($this->_getParam('c') == 'b_edit'){
            $this->_setParam('c', 'b_edit');
        }else{
            $this->_setParam('c', 'add');
        }
        $GLOBALS[brtag]=HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR;
        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }
        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
        $currentDir = getcwd();
        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_list.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));
        chdir($currentDir);

        if($res=='Ok' && $this->_getParam('c') == 'main'){
        	// stupid unmanaged! we need cute update!
            $test = $this->getOne($this->getService('TestAbstract')->find($testId));
            $result = $this->getService('TestAbstract')->update(array('data' => $test->data, 'test_id' => $testId));
            //pr($result);
            //die();
            $this->_flashMessenger->addMessage(_('Вопрос успешно добавлен!'));

            $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
        }elseif($this->_getParam('c') == 'main'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вопрос не был добавлен!'));
            $this->_redirector->gotoSimple('new', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
        }
        $this->view->content = $content;
       // print_r($content); exit;
    }

    /**
     * добавление вопроса в опрос
     *
     */
    public function newQuizAction(){
        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $quizId = $_GET['tid'] = $_GET['quiz_id'] = (int) $this->_getParam('quiz_id', 0);
        $quiz = $this->getOne($this->getService('Poll')->find($quizId));
        if ($quiz) {
            $this->view->setSubHeader($quiz->title);
            $this->_isEditable = $this->getService('Poll')->isEditable(
                $quiz->subject_id,
                $subjectId,
                $quiz->location
            );
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(
                array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Вы не можете добавлять вопросы в глобальные опросы'),
                )
            );
            $this->_redirector->gotoSimple(
                'quiz',
                'list',
                'question',
                array('quiz_id' => $quizId, 'subject_id' => $subjectId)
            );
        }

        $this->_setParam('quiz_id', $quizId);
        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $quizId);

        $c = $this->_getParam('c');
        if($c == 'add_submit' || $c == 'main' || $c == 'b_edit') {
        	$this->_setParam('c', $c);
        } elseif($_POST['c'] == 'edit_post') {
        	$this->_setParam('c', 'edit_post');
        } else{
        	$this->_setParam('c', 'add');
        }
        if ($c == 'add_submit' && $_POST['type'] == '') {
                $_POST['type'] = 1;
        }

        $GLOBALS[brtag]=HM_Poll_PollModel::QUESTION_SEPARATOR;
        $s = Zend_Registry::get('session_namespace_unmanaged')->s;

        $params = $this->_getAllParams();

        if(is_array($params['form']) && count($params['form'])) {

            $counter = 0;
        	$answers = array();
        	foreach($params['form'] as $form){
        		foreach($form['variant'] as $adata){
        			$answers[$adata['kodvar']] = $adata['variant'];
        		}
        		$this->getService('PollAnswer')->synchronize(
                    array(
                        'quiz_id' => $params['quiz_id'],
                        'question_id' => $params['che'][$counter],
                        'theme' => $form['qtema'],
                        'question_title' => $form['string']['vopros'],
                        'answers' => $answers,
                    )
                );
                $counter++;
        	}

        }

        extract($params);

        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
        $currentDir = getcwd();
        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_list.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));
        chdir($currentDir);
        if($res=='Ok' && $this->_getParam('c') == 'main'){
        	// stupid unmanaged! we need cute update!
            $quiz = $this->getOne($this->getService('Poll')->find($quizId));
            $this->getService('Poll')->update(array('data' => $quiz->data));

            $this->_flashMessenger->addMessage(_('Вопрос успешно добавлен!'));
            $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
        }elseif($this->_getParam('c') == 'main'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вопрос не был добавлен!'));
            $this->_redirector->gotoSimple('new-quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
        }
        $this->view->content = $content;
    }


    public function newTaskAction(){
        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $taskId = $_GET['tid'] = $_GET['task_id'] = (int) $this->_getParam('task_id', 0);
        $task = $this->getOne($this->getService('Task')->find($taskId));

        if ($task) {
            $this->view->setSubHeader($task->title);
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($task->subject_id, $subjectId, $task->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете добавлять варианты в глобальные задания')));
            $this->_redirector->gotoSimple('task', 'list', 'question', array('task_id' => $taskId, 'subject_id' => $subjectId));
        }

        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $taskId);
        $this->_setParam('task_id', $taskId);

        if($this->_getParam('c') == 'add_submit'){
            $this->_setParam('c', 'add_submit');
            if($_POST['type'] == ''){
                $_POST['type'] = 6;
            }
        }elseif($this->_getParam('c') == 'main'){
            $this->_setParam('c', 'main');
        }elseif($_POST['c'] == 'edit_post'){
            $this->_setParam('c', 'edit_post');
        }elseif($this->_getParam('c') == 'b_edit'){
            $this->_setParam('c', 'b_edit');
        }else{
            $variantId = 0;

/*
            $select = $this->getService('Test')->getSelect();

            $select->from(array('c' => 'conf_cid'), array('autoindex'))
                   ->where('CID = ?', $subjectId);

            $res = $select->query()->fetch();

            $variantId = $res['autoindex'];

            $select = $this->getService('Test')->getSelect()->getAdapter()->query('UPDATE conf_cid SET autoindex = autoindex + 1 WHERE CID = ' . intval($subjectId));
            */
            //pr($variantId);


             //$this->_setParam('c', 'add');


              $this->_setParam('c', 'add_submit');
             $this->_setParam('type', 6);
             $this->_setParam('adding2tid', $taskId);
             $this->_setParam('kod', 'autoindex');
             $this->_setParam('cid', $subjectId);
             //attach[OTItODE][0][fnum]
             $this->_setParam('attach', array('OTItODE' => array(array('fnum' => 1, 'ftype' => 'autodetect'))));

            //$this->_setParam('che', array($subjectId . '-' . $variantId));

        }
        $GLOBALS[brtag]= HM_Task_TaskModel::QUESTION_SEPARATOR;
        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }
        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
        $currentDir = getcwd();
        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_list.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));
        chdir($currentDir);

        if($res=='Ok' && $this->_getParam('c') == 'main'){
        	// stupid unmanaged! we need cute update!
            $test = $this->getOne($this->getService('Task')->find($taskId));
            $result = $this->getService('Task')->update(array('data' => $test->data, 'task_id' => $taskId));
            //pr($result);
            //die();
            $this->_flashMessenger->addMessage(_('Вариант успешно добавлен!'));

            $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $task_id )));
        }elseif($this->_getParam('c') == 'main'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вариант не был добавлен!'));
            $this->_redirector->gotoSimple('new-task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $task_id )));
        }
        $this->view->content = $content;
       // print_r($content); exit;
    }





    public function editAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $testId = $_GET['tid'] = (int) $this->_getParam('test_id', 0);
        $test = $this->getOne($this->getService('TestAbstract')->find($testId));

        if ($test) {
            $this->view->setSubHeader($test->title);
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете редактировать вопросы в глобальном тесте')));
            $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $testId, 'subject_id' => $subjectId));
        }

        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $testId);
        if($this->_getParam('c') == 'add_submit'){
            $this->_setParam('c', 'add_submit');
        }elseif($this->_getParam('c') == 'main'){
            $this->_setParam('c', 'main');
        }elseif($_POST['c'] == 'edit_post'){
            $this->_setParam('c', 'edit_post');
        }elseif($this->_getParam('c') == 'b_edit'){
            $this->_setParam('c', 'b_edit');
        }else{
            $this->_setParam('che', array($this->_getParam('kod')));
            $this->_setParam('c', 'b_edit');
        }
        $GLOBALS['brtag']=HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR;
        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }

        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));

        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

        $currentDir = getcwd();

        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');

        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_list.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));

        chdir($currentDir);
        if($res=='Ok' && $this->_getParam('c') == 'edit_post'){
            $this->_flashMessenger->addMessage(_('Вопрос успешно изменен!'));
            $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
        }elseif($this->_getParam('c') == 'edit_post'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вопрос не был изменен!'));
            $this->_redirector->gotoSimple('new', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
        }
       $this->view->content = $content;
    }


    public function editTaskAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $taskId = $_GET['tid'] = $_GET['task_id'] = (int) $this->_getParam('task_id', 0);
        $task = $this->getOne($this->getService('Task')->find($taskId));

        if ($task) {
            $this->view->setSubHeader($task->title);
            $this->_isEditable = $this->getService('Task')->isEditable($task->subject_id, $subjectId, $task->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете редактировать варианты в глобальном задании')));
            $this->_redirector->gotoSimple('task', 'list', 'question', array('test_id' => $taskId, 'subject_id' => $subjectId));
        }

        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $taskId);
        if($this->_getParam('c') == 'add_submit'){
            $this->_setParam('c', 'add_submit');
        }elseif($this->_getParam('c') == 'main'){
            $this->_setParam('c', 'main');
        }elseif($_POST['c'] == 'edit_post'){
            $this->_setParam('c', 'edit_post');
        }elseif($this->_getParam('c') == 'b_edit'){
            $this->_setParam('c', 'b_edit');
        }else{
            $this->_setParam('che', array($this->_getParam('kod')));
            $this->_setParam('c', 'b_edit');
        }
        $GLOBALS['brtag']=HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR;
        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }

        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));

        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

        $currentDir = getcwd();

        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');

        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_list.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));

        chdir($currentDir);
        if($res=='Ok' && $this->_getParam('c') == 'edit_post'){
            $this->_flashMessenger->addMessage(_('Вариант успешно изменен!'));
            $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));
        }elseif($this->_getParam('c') == 'edit_post'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вариант не был изменен!'));
            $this->_redirector->gotoSimple('new-task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));
        }
       $this->view->content = $content;
    }




    public function editQuizAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $quizId = $_GET['tid'] = $_GET['quiz_id'] = (int) $this->_getParam('quiz_id', 0);
        $quiz = $this->getOne($this->getService('Poll')->find($quizId));

        if ($quiz) {
            $this->view->setSubHeader($quiz->title);
            $this->_isEditable = $this->getService('Poll')->isEditable($quiz->subject_id, $subjectId, $quiz->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете редактировать вопросы в глобальном опросе')));
            $this->_redirector->gotoSimple('quiz', 'list', 'question', array('quiz_id' => $quizId, 'subject_id' => $subjectId));
        }

        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $quizId);

        $c = $this->_getParam('c');
        if($c == 'add_submit' || $c == 'main' || $c == 'b_edit')
        	$this->_setParam('c', $c);
        elseif($_POST['c'] == 'edit_post')
        	$this->_setParam('c', 'edit_post');
        else {
            $this->_setParam('che', array($this->_getParam('kod')));
        	$this->_setParam('c', 'b_edit');
        }
        if($c == 'add_submit' && $_POST['type'] == ''){
                $_POST['type'] = 1;
        }
        $GLOBALS['brtag']=HM_Poll_PollModel::QUESTION_SEPARATOR;
        $s = Zend_Registry::get('session_namespace_unmanaged')->s;

        $params = $this->_getAllParams();

        if(is_array($params['form']) && count($params['form'])){

        	$answers = array();
        	foreach($params['form'] as $form){
        		foreach($form['variant'] as $adata){
        			$answers[$adata['kodvar']] = $adata['variant'];
        		}
        		$this->getService('PollAnswer')->synchronize(array(
                                                                'quiz_id' => $params['quiz_id'],
                                                                'question_id' => $params['kod'],
                                                                'theme' => $form['qtema'],
                                                                'question_title' => $form['string']['vopros'],
                                                                'answers' => $answers
                                                            )
                );
        	}

        }

        extract($params);

        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));

        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

        $currentDir = getcwd();

        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');

        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_list.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));

        chdir($currentDir);
        if($res=='Ok' && $this->_getParam('c') == 'edit_post'){
            $this->_flashMessenger->addMessage(_('Вопрос успешно изменен!'));
            $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
        }elseif($this->_getParam('c') == 'edit_post'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вопрос не был изменен!'));
            $this->_redirector->gotoSimple('edit-quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
        }
       $this->view->content = $content;
    }






    public function deleteAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $testId = $_GET['tid'] = (int) $this->_getParam('test_id', 0);
        $kodId = $this->_getParam('kod', 0);

        $GLOBALS['brtag'] = HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR;
        $test = $this->getService('TestAbstract')->getOne($this->getService('TestAbstract')->find($testId));

        if($test){

            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);

            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять вопросы из глобального теста')));
                $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $testId, 'subject_id' => $subjectId));
            }

            $temp = explode( $GLOBALS['brtag'],$test->data);

            foreach($temp as $key => $val){
                if($kodId == $val){
                    unset($temp[$key]);
                    break;
                }
            }

            $up = $this->getService('TestAbstract')->update(
                                    array('test_id' => $testId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );

            $this->getService('TestNeed')->unnecessary($kodId, $testId);

            if($up){

                $this->_flashMessenger->addMessage(_('Вопрос успешно удален!'));
                $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вопрос не был удален!'));
                $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
            }


        }else{
            $this->_flashMessenger->addMessage(_('Тест не найден!'));
            $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));

        }

    }


    public function deleteQuizAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $quizId = $_GET['tid'] = $_GET['quiz_id'] = (int) $this->_getParam('quiz_id', 0);
        $kodId = $this->_getParam('kod', 0);

        $GLOBALS['brtag'] = HM_Poll_PollModel::QUESTION_SEPARATOR;
        $quiz = $this->getService('Poll')->getOne($this->getService('Poll')->find($quizId));

        if($quiz){

            $this->_isEditable = $this->getService('Poll')->isEditable($quiz->subject_id, $subjectId, $quiz->location);

            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять вопросы в глобальных опросах')));
                $this->_redirector->gotoSimple('quiz', 'list', 'question', array('quiz_id' => $quizId, 'subject_id' => $subjectId));
            }

            $temp = explode( $GLOBALS['brtag'],$quiz->data);

            //$this->getService('PollAnswer')->deleteBy(array('quiz_id = ?' => $quiz_id, 'answer_id = ?' => $kodId));
            foreach($temp as $key => $val){
                if($kodId == $val){
                    unset($temp[$key]);
                    break;
                }
            }

            $up = $this->getService('Poll')->update(
                                    array('quiz_id' => $quizId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );


            if($up){

                $this->_flashMessenger->addMessage(_('Вопрос успешно удален!'));
                $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вопрос не был удален!'));
                $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
            }


        }else{
            $this->_flashMessenger->addMessage(_('Опрос не найден!'));
            $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('quiz_id' => $quizId )));

        }

    }


    public function deleteTaskAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $taskId = $_GET['tid'] = $_GET['task_id'] = (int) $this->_getParam('task_id', 0);
        $kodId = $this->_getParam('kod', 0);

        $GLOBALS['brtag'] = HM_Poll_PollModel::QUESTION_SEPARATOR;
        $task = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));

        if($task){
            $this->_isEditable = $this->getService('Task')->isEditable($task->subject_id, $subjectId, $task->location);
            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять варианты в глобальных заданиях')));
                $this->_redirector->gotoSimple('task', 'list', 'question', array('task_id' => $taskId, 'subject_id' => $subjectId));
            }

            $temp = explode( $GLOBALS['brtag'],$task->data);

            foreach($temp as $key => $val){
                if($kodId == $val){
                    unset($temp[$key]);
                    break;
                }
            }

            $up = $this->getService('Task')->update(
                                    array('task_id' => $taskId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );


            if($up){

                $this->_flashMessenger->addMessage(_('Вариант успешно удален!'));
                $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вариант не был удален!'));
                $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));
            }


        }else{
            $this->_flashMessenger->addMessage(_('Задание не найдено!'));
            $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));

        }

    }

    public function deleteByAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $testId = $_GET['tid'] = (int) $this->_getParam('test_id', 0);

        $gridId = ($subjectId) ? "grid{$testId}{$subjectId}" : 'grid'.$testId;

        $kods = $this->_getParam('postMassIds_'.$gridId, '');

        if ($kods) {
            $test = $this->getService('TestAbstract')->getOne($this->getService('TestAbstract')->find($testId));
            if ($test) {

                $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);

                if ($this->_isEditable) {
                    $kods = explode(',', $kods);
                    $questionNotInTest = false;
                    if (count($kods)) {
                        $testQuestions = $test->getQuestionsIds();
                        foreach($kods as $kod) {
                            if (!in_array($kod, $testQuestions)) {
                                $questionNotInTest = true;
                            }
                        }
                        if (!$questionNotInTest) {
                            $test->removeQuestionsIds($kods);
                            $test = $this->getService('TestAbstract')->update($test->getValues());

                            foreach($kods as $kod) {
                                $this->getService('TestNeed')->unnecessary($kod, $testId);
                            }
                            $this->_flashMessenger->addMessage(_('Вопросы успешно удалены'));
                        } else {
                            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Выбраны вопросы, которые не принадлежат данному тесту')));
                        }
                    }

                } else {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять вопросы из глобального теста')));
                }

            } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Тест не найден')));
            }
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не выбраны вопросы')));
        }

        $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));

    }

    public function deleteByQuizAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $quizId = $_GET['tid'] = (int) $this->_getParam('quiz_id', 0);
        $kodId = $this->_getParam('postMassIds_grid', 0);

        $kodId =explode(',', $kodId);
        $GLOBALS['brtag']=HM_Poll_PollModel::QUESTION_SEPARATOR;

        $test = $this->getService('Poll')->getOne($this->getService('Poll')->find($quizId));

        if($test){

            $this->_isEditable = $this->getService('Poll')->isEditable($test->subject_id, $subjectId, $test->location);

            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять вопросы в глобальных опросах')));
                $this->_redirector->gotoSimple('quiz', 'list', 'question', array('quiz_id' => $quizId, 'subject_id' => $subjectId));
            }

            $temp = explode( $GLOBALS['brtag'],$test->data);

            foreach($temp as $key => $val){
                if(in_array($val, $kodId)){
                    unset($temp[$key]);
                }
            }

            $up = $this->getService('Poll')->update(
                                    array('quiz_id' => $quizId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );

            if($up){
                $this->_flashMessenger->addMessage(_('Вопросы успешно удалены!'));
                $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вопросы не были удалены!'));
                $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
            }
        }else{
            $this->_flashMessenger->addMessage(_('Тест не найден!'));
            $this->_redirector->gotoSimple('quiz', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quiz_id' =>array('quiz_id' => $quizId )));
        }
    }



    public function deleteByTaskAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $taskId = $_GET['tid'] = (int) $this->_getParam('task_id', 0);
        $kodId = $this->_getParam('postMassIds_grid', 0);

        $kodId =explode(',', $kodId);
        $GLOBALS['brtag']=HM_Poll_PollModel::QUESTION_SEPARATOR;

        $test = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));

        if($test){

            $this->_isEditable = $this->getService('Task')->isEditable($test->subject_id, $subjectId, $test->location);

            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять варианты в глобальных заданиях')));
                $this->_redirector->gotoSimple('task', 'list', 'question', array('task_id' => $taskId, 'subject_id' => $subjectId));
            }

            $temp = explode( $GLOBALS['brtag'],$test->data);

            foreach($temp as $key => $val){
                if(in_array($val, $kodId)){
                    unset($temp[$key]);
                }
            }

            $up = $this->getService('Task')->update(
                                    array('task_id' => $taskId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );

            if($up){
                $this->_flashMessenger->addMessage(_('Вопросы успешно удалены!'));
                $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вопросы не были удалены!'));
                $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));
            }
        }else{
            $this->_flashMessenger->addMessage(_('Тест не найден!'));
            $this->_redirector->gotoSimple('task', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'task_id' =>array('task_id' => $taskId )));
        }
    }







    public function necessaryAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $testId = $_GET['tid'] = (int) $this->_getParam('test_id', 0);

        $gridId = ($subjectId) ? "grid{$testId}{$subjectId}" : 'grid'.$testId;

        $kodId = $this->_getParam('postMassIds_'.$gridId, 0);
        $kodId =explode(',', $kodId);

        $test = $this->getOne($this->getService('TestAbstract')->find($testId));
        if ($test) {
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете редактировать вопросы глобального теста')));
            $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $testId, 'subject_id' => $subjectId));
        }

        foreach($kodId as $value){
            $ret=$this->getService('TestNeed')->necessary($value, $testId);
        }
        $this->_flashMessenger->addMessage(_('Вопросы помечены как обязательные!'));
        $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));

    }


    public function unnecessaryAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $testId = $_GET['tid'] = (int) $this->_getParam('test_id', 0);

        $gridId = ($subjectId) ? "grid{$testId}{$subjectId}" : 'grid'.$testId;

        $kodId = $this->_getParam('postMassIds_'.$gridId, 0);
        $kodId =explode(',', $kodId);

        $test = $this->getOne($this->getService('TestAbstract')->find($testId));
        if ($test) {
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете редактировать вопросы глобального теста')));
            $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $testId, 'subject_id' => $subjectId));
        }

        foreach($kodId as $value){
            $this->getService('TestNeed')->unnecessary($value, $testId);
        }
        $this->_flashMessenger->addMessage(_('Вопросы помечены как необязательные!'));
        $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));

    }

    public function updateTest($testId)
    {
        if ($testId) {
            return _('Да');
        }

        return _('Нет');
    }

    public function updateTestName($testName, $subjectId, $kod, $subjectName='', $showOne=true)
    {
        if (!$subjectId) {
            $testName = sprintf(_('%s (БЗ)'), $testName);
        } else {
            $parts = explode('-', $kod);
            if (count($parts)) {
                if ($parts[0] == '0') {
                    $testName = sprintf(_('%s (БЗ)'), $testName);
                } elseif($subjectName && !$showOne) {
                    $testName = sprintf(_('%s (%s)'), $testName,$subjectName);
                }
            }
        }

        return $testName;
    }

    public function updateQdata($field){
        list($str) =explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $field);
        if(strlen($str) > $this->testNameLen){
            $str = substr($str, 0, $this->testNameLen);
        }
        return trim(strip_tags($str));
    }

    public function updateNeedle($field){
        if($field!=''){
            return _('Да');
        }else{
            return _('Нет');
        }
    }


    public function updateType($field){
        $res = $this->getService('Question')->getTypes();
        return $res[$field];
    }

    public function updateActions($type, $kod, $test, $actions) {

        if (null === $this->_cacheKnowledgeBaseQuestions) {
            $kbQuestions = $this->getService('TestQuestion')->fetchAll($this->quoteInto('subject_id = ?', 0));
            if (count($kbQuestions)) {
                $this->_cacheKnowledgeBaseQuestions = $kbQuestions->getList('kod');
            }
        }

        if ($test == _('Нет')) return ''; // Убираем действия для вопросов не из теста

        $isKnowledgeBaseQuestion = false;
        $parts = explode('-', $kod);

        if (count($parts)) {
            if ($parts[0] == '0') {
                $isKnowledgeBaseQuestion = true;
            } else {
                if (is_array($this->_cacheKnowledgeBaseQuestions) && in_array($kod, $this->_cacheKnowledgeBaseQuestions)) {
                    $isKnowledgeBaseQuestion = true;
                }
            }
        }

        if (!in_array($type, $this->getService('Question')->getUneditableTypes()) && (!$isKnowledgeBaseQuestion || !$this->_getParam('subject_id', 0))) {
            return $actions;
        } else {
            $tmp = explode('<li>', $actions);
            unset($tmp[1]);
            return implode('<li>', $tmp);
        }
    }

}

