<?php
class Question_ListController extends HM_Controller_Action
{

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    private $_isEditable = false;

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $subjectId = (int) $this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
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
        }

    }

    private $testNameLen = 300;
    
    public function testAction()
    {
        $subjectId = $this->_request->getParam('subject_id', 0);
        $testId = $this->_request->getParam('test_id', 0);

        $test = $this->getOne($this->getService('TestAbstract')->find($testId));

        if ($test) {
            $this->view->setSubHeader($test->title);
            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);
        }

        $ids = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);

        $select = $this->getService('Test')->getSelect();
        $subselect = clone $select;
        $subselect->from(array('n' => 'testneed'))
        ->where('tid = ?', $testId);
        $select->from(array('t' => 'list'),array('kod'  => 't.kod',
                                                'qdata' => 't.qdata',
                                			    'qtype'  => 't.qtype')
                     )
        ->joinLeft(array('n' => $subselect) , 't.kod = n.kod', array('tid'))
       	->where("t.kod IN (?)", $ids);
        //echo $select;

       	// exit();
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
            
            $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{qtype}}')
                )
            );

        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->isEditable = $this->_isEditable;

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


    public function questAction()
    {
        $subjectId = $this->_request->getParam('subject_id', 0);
        $questId = $this->_request->getParam('quest_id', 0);

        $quest = $this->getOne($this->getService('Poll')->find($questId));

        if ($quest) {
            $this->view->setSubHeader($quest->title);

            $this->_isEditable = $this->getService('Poll')->isEditable($quest->subject_id, $subjectId, $quest->location);
        }

        $ids = explode(HM_Poll_PollModel::QUESTION_SEPARATOR, $quest->data);

        $select = $this->getService('Test')->getSelect();

        $select->from(array('t' => 'list'),array('kod'  => 't.kod',
                                                'qdata' => 't.qdata',
                                			    'qtype'  => 't.qtype')
                     )
       	->where("t.kod IN (?)", $ids);
        //echo $select;

       	// exit();
       	$grid = $this->getGrid( $select,
                               	array('kod'   => array('hidden' => true),
                                      'qdata' => array('title' => _('Вопрос'), 'decorator' => $this->view->cardLink($this->view->baseUrl('test_vopros.php?kod={{kod}}&cid='.$subjectId.'&mode=2'),_('Карточка вопроса')).'{{qdata}}'),
                                      'qtype'  => array('title' => _('Тип'))
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
                                   'action'     => 'edit-quest'),
                             array('kod'),
                             $this->view->svgIcon('edit', 'Редактировать'));

            $grid->addAction(array('module'     => 'question',
                                   'controller' => 'list',
                                   'action'     => 'delete-quest'),
                             array('kod'),
                             $this->view->svgIcon('delete', 'Удалить'));

            $grid->addMassAction(array('module'     => 'question',
                                       'controller' => 'list',
                                       'action'     => 'delete-by-quest'),
                                _('Удалить'),
                                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->isEditable = $this->_isEditable;

    }
    
    
    
    public function taskAction()
    {
        $subjectId = $this->_request->getParam('subject_id', 0);
        $taskId = $this->_request->getParam('task_id', 0);

        $task = $this->getOne($this->getService('Task')->find($taskId));

        if ($task) {
            $this->view->setSubHeader($task->title);

            $this->_isEditable = $this->getService('Task')->isEditable($task->subject_id, $subjectId, $task->location);
        }

        $ids = explode(HM_Task_TaskModel::QUESTION_SEPARATOR, $task->data);

        $select = $this->getService('Test')->getSelect();

        $select->from(array('t' => 'list'),array('kod'  => 't.kod',
                                                'qdata' => 't.qdata',
                                			   // 'qtype'  => 't.qtype'
                                            )
                     )
       	->where("t.kod IN (?)", $ids);
        //echo $select;

       	// exit();
       	$grid = $this->getGrid( $select,
                               	array('kod'   => array('hidden' => true),
                                      'qdata' => array('title' => _('Вариант'), 'decorator' => $this->view->cardLink($this->view->baseUrl('test_vopros.php?kod={{kod}}&cid='.$subjectId.'&mode=2'),_('Карточка варианта')).'{{qdata}}'),
                                      'qtype'  => array('title' => _('Тип'))
                                ),
                               	array('qdata' => null,
                               		  'qtype'  => array('values'=> $this->getService('Question')->getTypes())
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
                                   'action'     => 'edit-task'),
                             array('kod'),
                             $this->view->svgIcon('edit', 'Редактировать'));

            $grid->addAction(array('module'     => 'question',
                                   'controller' => 'list',
                                   'action'     => 'delete-task'),
                             array('kod'),
                             $this->view->svgIcon('delete', 'Удалить'));

            $grid->addMassAction(array('module'     => 'question',
                                       'controller' => 'list',
                                       'action'     => 'delete-by-task'),
                                _('Удалить'),
                                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->isEditable = $this->_isEditable;

    }
    
    public function newAction(){
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
    public function newQuestAction(){
        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $questId = $_GET['tid'] = $_GET['quest_id'] = (int) $this->_getParam('quest_id', 0);
        $quest = $this->getOne($this->getService('Poll')->find($questId));
        if ($quest) {
            $this->view->setSubHeader($quest->title);

            $this->_isEditable = $this->getService('Poll')->isEditable($quest->subject_id, $subjectId, $quest->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете добавлять вопросы в глобальные опросы')));
            $this->_redirector->gotoSimple('quest', 'list', 'question', array('quest_id' => $questId, 'subject_id' => $subjectId));
        }

        $this->_setParam('quest_id', $questId);
        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $questId);
        
        $c = $this->_getParam('c');
        if($c == 'add_submit' || $c == 'main' || $c == 'b_edit')
        	$this->_setParam('c', $c);
        elseif($_POST['c'] == 'edit_post')
        	$this->_setParam('c', 'edit_post');
        else 
        	$this->_setParam('c', 'add');
        if($c == 'add_submit' && $_POST['type'] == ''){
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
        		$this->getService('PollAnswer')->synchronize(array(
                                                                'quest_id' => $params['quest_id'],
                                                                'question_id' => $params['che'][$counter],
                                                                'theme' => $form['qtema'],
                                                                'question_title' => $form['string']['vopros'],
                                                                'answers' => $answers
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
            $quest = $this->getOne($this->getService('Poll')->find($questId));
            $this->getService('Poll')->update(array('data' => $quest->data));
            
            $this->_flashMessenger->addMessage(_('Вопрос успешно добавлен!'));
            $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
        }elseif($this->_getParam('c') == 'main'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вопрос не был добавлен!'));
            $this->_redirector->gotoSimple('new-quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
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
    
    
    

    public function editQuestAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $questId = $_GET['tid'] = $_GET['quest_id'] = (int) $this->_getParam('quest_id', 0);
        $quest = $this->getOne($this->getService('Poll')->find($questId));

        if ($quest) {
            $this->view->setSubHeader($quest->title);
            $this->_isEditable = $this->getService('Poll')->isEditable($quest->subject_id, $subjectId, $quest->location);
        }

        if (!$this->_isEditable) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете редактировать вопросы в глобальном опросе')));
            $this->_redirector->gotoSimple('quest', 'list', 'question', array('quest_id' => $questId, 'subject_id' => $subjectId));
        }

        $this->_setParam('cid', $subjectId);
        $this->_setParam('tid', $questId);
        
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
                                                                'quest_id' => $params['quest_id'],
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
            $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
        }elseif($this->_getParam('c') == 'edit_post'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Вопрос не был изменен!'));
            $this->_redirector->gotoSimple('edit-quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
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
        
    
    public function deleteQuestAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $questId = $_GET['tid'] = $_GET['quest_id'] = (int) $this->_getParam('quest_id', 0);
        $kodId = $this->_getParam('kod', 0);

        $GLOBALS['brtag'] = HM_Poll_PollModel::QUESTION_SEPARATOR;
        $quest = $this->getService('Poll')->getOne($this->getService('Poll')->find($questId));

        if($quest){

            $this->_isEditable = $this->getService('Poll')->isEditable($quest->subject_id, $subjectId, $quest->location);

            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять вопросы в глобальных опросах')));
                $this->_redirector->gotoSimple('quest', 'list', 'question', array('quest_id' => $questId, 'subject_id' => $subjectId));
            }

            $temp = explode( $GLOBALS['brtag'],$quest->data);

            //$this->getService('PollAnswer')->deleteBy(array('quest_id = ?' => $quest_id, 'answer_id = ?' => $kodId));
            foreach($temp as $key => $val){
                if($kodId == $val){
                    unset($temp[$key]);
                    break;
                }
            }

            $up = $this->getService('Poll')->update(
                                    array('quest_id' => $questId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );

            
            if($up){

                $this->_flashMessenger->addMessage(_('Вопрос успешно удален!'));
                $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вопрос не был удален!'));
                $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
            }


        }else{
            $this->_flashMessenger->addMessage(_('Опрос не найден!'));
            $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('quest_id' => $questId )));

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
        $kodId = $this->_getParam('postMassIds_grid', 0);

        $kodId =explode(',', $kodId);
        $GLOBALS['brtag']=HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR;

        //$test = $this->getService('TestAbstract')->find($testId);
        $test = $this->getService('TestAbstract')->getOne($this->getService('TestAbstract')->find($testId));
         
        if($test){

            $this->_isEditable = $this->getService('TestAbstract')->isEditable($test->subject_id, $subjectId, $test->location);

            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять вопросы из глобального теста')));
                $this->_redirector->gotoSimple('test', 'list', 'question', array('test_id' => $testId, 'subject_id' => $subjectId));
            }


            $temp = explode( $GLOBALS['brtag'],$test->data);

            foreach($temp as $key => $val){
                if(in_array($val, $kodId)){
                    unset($temp[$key]);
                }
            }

            $up = $this->getService('TestAbstract')->update(
                                    array('test_id' => $testId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );

            if (is_array($kodId) && count($kodId)) {
                foreach($kodId as $questionKod) {
                    $this->getService('TestNeed')->unnecessary($questionKod, $testId);
                }
            }

            if($up){
                $this->_flashMessenger->addMessage(_('Вопросы успешно удалены!'));
                $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вопросы не были удалены!'));
                $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
            }
        }else{
            $this->_flashMessenger->addMessage(_('Тест не найден!'));
            $this->_redirector->gotoSimple('test', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'test_id' =>array('test_id' => $testId )));
        }
    }
    
    public function deleteByQuestAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $questId = $_GET['tid'] = (int) $this->_getParam('quest_id', 0);
        $kodId = $this->_getParam('postMassIds_grid', 0);

        $kodId =explode(',', $kodId);
        $GLOBALS['brtag']=HM_Poll_PollModel::QUESTION_SEPARATOR;

        $test = $this->getService('Poll')->getOne($this->getService('Poll')->find($questId));

        if($test){

            $this->_isEditable = $this->getService('Poll')->isEditable($test->subject_id, $subjectId, $test->location);

            if (!$this->_isEditable) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не можете удалять вопросы в глобальных опросах')));
                $this->_redirector->gotoSimple('quest', 'list', 'question', array('quest_id' => $questId, 'subject_id' => $subjectId));
            }

            $temp = explode( $GLOBALS['brtag'],$test->data);

            foreach($temp as $key => $val){
                if(in_array($val, $kodId)){
                    unset($temp[$key]);
                }
            }

            $up = $this->getService('Poll')->update(
                                    array('quest_id' => $questId,
                                          'data' => implode($GLOBALS['brtag'], $temp),
                                          'questions' => count($temp)
                                    )
            );

            if($up){
                $this->_flashMessenger->addMessage(_('Вопросы успешно удалены!'));
                $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
            }
            else{
                $this->_flashMessenger->addMessage(_('Вопросы не были удалены!'));
                $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
            }
        }else{
            $this->_flashMessenger->addMessage(_('Тест не найден!'));
            $this->_redirector->gotoSimple('quest', 'list', 'question', array('subject_id' =>array('subject_id' => $subjectId ),'quest_id' =>array('quest_id' => $questId )));
        }
    }

    
    
    public function deleteByTaskAction(){

        $subjectId = $_GET['cid'] = (int) $this->_getParam('subject_id', 0);
        $taskId = $_GET['tid'] = (int) $this->_getParam('task_id', 0);
        $kodId = $this->_getParam('postMassIds_grid', 0);

        $kodId =explode(',', $kodId);
        $GLOBALS['brtag']=HM_Poll_PollModel::QUESTION_SEPARATOR;

        $test = $this->getService('Task')->geOne($this->getService('Task')->find($taskId));

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

            $up = $this->getService('Poll')->update(
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
        $kodId = $this->_getParam('postMassIds_grid', 0);
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
        $kodId = $this->_getParam('postMassIds_grid', 0);
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
    
    public function updateActions($type, $actions) {
        if (!in_array($type, $this->getService('Question')->getUneditableTypes())) {
            return $actions;
        } else {
            $tmp = explode('<li>', $actions);
            unset($tmp[1]);
            return implode('<li>', $tmp);
        }
    }

    
}

