<?php
class Project_IndexController extends HM_Controller_Action_Project
{
    const COURSE_TYPE_LOCAL = 'Учебный курс';

    private $_testsCache = array();
    private $_lessonsCache = array();

    public function indexAction()
    {

		$projectId = (int) $this->_getParam('project_id');
        $courseId = (int) $this->_getParam('course_id', 0);
        $lessonId = $this->view->lessonId = (int) $this->_getParam('lesson_id', 0);

        $this->initLessonTabs();

        $courses = array();
        if ($projectId && $courseId == 0) {
            $courses = $this->getService('Project')->getCourses($projectId, HM_Course_CourseModel::STATUS_ACTIVE);
            $this->view->courses = $courses;
        }elseif($courseId != 0){
            $opened                   = $this->getService('CourseItem')->getOpenedBranch($courseId);
            $course                   = $this->getService('CourseItem')->getTreeContent($courseId, $opened, $projectId);
            $courseObject             = $this->getOne($this->getService('Course')->find($courseId));
            $this->view->courseObject = $courseObject;
            $userId                   = $this->getService('User')->getCurrentUserId();

            $lesson = $this->getService('LessonAssign')
                           ->getOne(
                                    $this->getService('LessonAssign')
                                         ->fetchAll(array('SHEID = ?'=> $lessonId,
                                                          'MID = ?'  => $this->getService('User')->getCurrentUserId()
                                                    ))
                    );

            // @tocheck
            // обновляем scheduleID: для курса из конструктора - успешное прохождение,
            // для импортируемых - статус "в просессе" и нулевой процент прохождения если он еще не был начат
            if ( $lesson ) {
                if ( $lesson->isfree == HM_Lesson_LessonModel::MODE_FREE ) {
                    if ( $courseObject->format == HM_Course_CourseModel::FORMAT_FREE) {
                        $lesson->V_STATUS = 100;
                        $lesson->V_DONE   = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE;
                    } elseif( $lesson->V_DONE == HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART ) {
                        $lesson->V_STATUS = 0;
                        $lesson->V_DONE   = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS;
                    }

                    $this->getService('LessonAssign')
                        ->update($lesson->getData());
                }
            }


            if(
                !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
                //$this->getService('User')->getCurrentUserRole() != HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT
            ){
                $tmpProjectId = 0;
            }else{
                $tmpProjectId = $projectId;
            }
            $this->view->current = $this->getService('CourseItemCurrent')->getCurrent($userId, $tmpProjectId, $courseId, $lessonId);
            if ($this->view->current) {
                $this->view->itemCurrent = $this->getService('CourseItem')->getOne(
                    $this->getService('CourseItem')->find($this->view->current)
                );
            }
            $this->view->tree  = $course;
            $this->view->isDegeneratedTree = $this->getService('CourseItem')->isDegeneratedTree($courseId);

            // @tocheck
                $this->view->setHeader($this->_subject->getName());
                if ($this->view->courseObject) {
                    $this->view->setSubHeader($this->view->courseObject->getName());
                }

        }
        $this->view->projectId = $projectId;
        $this->view->courseContent = true;
    }

    public function cardAction()
    {
        $this->view->project = $this->_subject;
        $projectId = $this->_subject->projid;

        $path = Zend_Registry::get('config')->path->upload->project_protocols . '/' . $projectId;
        $this->view->showProtocol = is_readable($path) &&
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $this->view->project = $this->_subject;
    }

    public function editAction()
    {
        if ($projid = $this->_getParam('project_id')) {
            $this->_setParam('projid', $projid);
        }
        $projectId = (int) $this->_getParam('projid', 0);

        $form = new HM_Form_Projects();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $projectId = $form->getValue('projid');
                $project = $this->getService('Project')->update(
                    array(
                        'projid' => $projectId,
                        'name' => $form->getValue('name'),
                        'shortname' => $form->getValue('shortname'),
                        'description' => $form->getValue('description'),
                        'external_id' => $form->getValue('external_id'),
                        'code' => $form->getValue('code'),
                        'type' => $form->getValue('type'),
                        'reg_type' => $form->getValue('reg_type'),
                        'begin' => $form->getValue('begin'),
                        'end' => $form->getValue('end'),
                        'period' => $form->getValue('period')
                    )
                );

                $this->getService('Project')->linkClassifiers($projectId, $form->getClassifierValues());
                $this->getService('Project')->updateIcon($projectId, $form->getElement('icon'));

                $this->_flashMessenger->addMessage(_('Учебный курс успешно изменён'));
                $this->_redirector->gotoSimple('index', 'index', 'project', array('project_id' => $projectId));
            }
        } else {
            $project = $this->getService('Project')->getOne($this->getService('Project')->find($projectId));
            if ($project) {
                $form->setDefaults(
                    $project->getValues()
                );
            }
        }
        $this->view->form = $form;
    }

    public function coursesAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);

        $gridId = ($projectId) ? "grid{$projectId}" : 'grid';

    	$default = new Zend_Session_Namespace('default');
    	if ($projectId && !isset($default->grid['project-index-courses'][$gridId])) {
    		$default->grid['project-index-courses'][$gridId]['filters']['projid'] = $projectId; // по умолчанию показываем только слушателей этого курса
    	}

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $select = $this->getService('Project')->getSelect();
        $select->from(
            array('c' => 'Courses'),
            array(
                'c.CID',
                'c.Title',
                'c.chain',
                'projid' => 's.project_id',
                'c.new_window',
                'c.format',
                'tags' => 'c.CID'
            ))
        ->joinLeft(array('s' => 'projects_courses'), "c.CID = s.course_id AND project_id = '".$projectId."'", array())
        ->where('(s.project_id = ? OR s.project_id IS NULL)', $projectId)
        ->where(new Zend_Db_Expr($this->getService('Project')->quoteInto(array("c.Status = ? ", "OR c.Status = ?"), array(HM_Course_CourseModel::STATUS_STUDYONLY, HM_Course_CourseModel::STATUS_ACTIVE))))
        ->where('c.chain IS NULL OR c.chain = 0 OR c.chain = ?', $projectId);


        $grid = $this->getGrid($select,
                               array(
                                   'CID' => array('hidden' => true),
                                   'new_window' => array('hidden' => true),
                                   'Title' => array('title' => _('Название')),
                                   'chain' => array(
                                       'title' => _('Место хранения'),
                                       'callback' => array(
                                           'function' => array($this, 'updateTypeColumn'),
                                           'params' => array('{{chain}}', $projectId)
                                       )
                                   	),
                               		'projid' => array(
                                        'title' => _('Доступ для слушателей'),
                                        'callback' => array(
                                            'function' => array($this, 'updateProjectColumn'),
                                            'params' => array(HM_Event_EventModel::TYPE_COURSE, '{{CID}}', '{{projid}}', $projectId)
                                        )
                                    ),
                                   'format' => array(
                                       'title' => _('Формат'),
                                       'callback' => array(
                                           'function' => array($this, 'updateFormatColumn'),
                                           'params' => array('{{format}}')
                                       )
                               ),
                                   'tags' => array('title' => _('Метки'))
                               ),
                               array(
                                   'Title' => null,
                                   'chain' => array(
                                       'values' => array(
                                           $projectId => _(self::COURSE_TYPE_LOCAL),
                                           0 => _('База знаний')
                                       )
                                   ),
                                   'format' => array('values' => HM_Course_CourseModel::getFormats()),
                                   'tags' => array('callback' => array('function' => array($this, 'filterTags')))
                               ),
                               $gridId);
        if($this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')){
            $grid->setGridSwitcher(array(
      			array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('projid' => $projectId)),
      			array('name' => 'global', 'title' => _('все, включая учебные модули из Базы знаний'), 'params' => array('projid' => null), 'order' => 'projid', 'order_dir' => 'DESC'),
      		));
        }
        $grid->setClassRowCondition("'{{projid}}' != ''", "success");


        $grid->addMassAction(
            array('module' => 'project', 'controller' => 'index', 'action' => 'assign', 'project_id' => $projectId),
            _('Использовать в курсе и открыть свободный доступ для слушателей'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array('module' => 'project', 'controller' => 'index', 'action' => 'unassign', 'project_id' => $projectId),
            _('Не использовать в курсе и закрыть свободный доступ для слушателей'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array('module' => 'project', 'controller' => 'index', 'action' => 'course-delete-by', 'project_id' => $projectId),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $subj = $this->getOne($this->getService('Project')->find($projectId));

            $grid->addSubMassActionSelect(
                array(
                $this->view->url(array('action' => 'assign', 'isfree' => HM_Lesson_LessonModel::MODE_PLAN))
                ),
                'lesson',
            array(0 => '', 1 => _('Автоматически сгенерировать занятие'))
            );
        $grid->updateColumn('Title',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{Title}}', '{{CID}}', '{{new_window}}')
                )
            )
        );


        $grid->addAction(
            array(
                'module' => 'course',
                'controller' => 'list',
                'action' => 'edit',
                'project_id' => $projectId
            ),
            array(
                'CID'
            ),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(
            array(
                'module' => 'project',
                'controller' => 'index',
                'action' => 'course-delete',
                'project_id' => $projectId
            ),
            array(
                'CID'
            ),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateCoursesActions'),
                  'params'   => array('{{chain}}')
            )
        );

        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array('{{tags}}', $this->getService('TagRef')->getCourseType())
            )
        ));


        $this->view->projectId = $projectId;
        $this->view->isGridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid;

        /*
        $form = new HM_Form_Courses();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $courses = $form->getValue('courses');
                $this->getService('Project')->unlinkCourses($form->getValue('project_id'));
                if (is_array($courses) && count($courses)) {
                    foreach($courses as $courseId) {
                        $this->getService('Project')->linkCourse($form->getValue('project_id'), $courseId);
                    }
                }

                $this->_flashMessenger->addMessage(_('Связи с электронными курсами успешно изменены'));
                $this->_redirector->gotoSimple('index', 'index', 'project', array('project_id' => $projectId));
            }
        } else {
            $form->setDefaults(array('project_id' => $projectId));
        }
        $this->view->form = $form;

         */
    }

    public function courseDelete($projectId, $courseId)
    {
        $course = $this->getOne($this->getService('Course')->find($courseId));
        if ($course) {
            if ($course->chain == $projectId) {
                if ($this->getService('Teacher')->isUserExists($projectId, $this->getService('User')->getCurrentUserId())) {
                    $this->getService('Course')->delete($course->CID);

                    $this->getService('Course')->clearLesson(null, $courseId);

                    return true;
                } else {
                    throw new HM_Exception(_('Вы не являетесь преподавателем на данном учебном курсе.'));
                    //$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не являетесь преподавателем на данном учебном курсе.')));
                }
            } else {
                throw new HM_Exception(_('Учебный модуль не используется в данном учебном курсе.'));
                //$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Учебный модуль не используется в данном учебном курсе.')));
            }
        }
    }

    public function courseDeleteAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $courseId = (int) $this->_getParam('CID', 0);

        if ($projectId && $courseId) {
            try {
                $this->courseDelete($projectId, $courseId);
            } catch (HM_Exception $e) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()));
            }
        }

        $this->_redirector->gotoSimple('courses', 'index', 'project', array('project_id' => $projectId));

    }

    public function courseDeleteByAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);

        $gridId = ($projectId) ? "grid{$projectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_'.$gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            $error = false;
            if (count($ids)) {
                foreach($ids as $id) {
                    try {
                        $this->courseDelete($projectId, $id);
                    } catch (HM_Exception $e) {
                        $error = true;
                    }
                }

                if($error === false){
                    $this->_flashMessenger->addMessage(_('Учебные модули успешно удалены.'));
                }else{
                    $this->_flashMessenger->addMessage(_('Глобальные учебные модули невозможно удалить из учебного курса.'));
                }
            }
        }

        $this->_redirector->gotoSimple('courses', 'index', 'project', array('project_id' => $projectId));
    }

    public function updateCoursesActions($chain, $actions)
    {
        if (false !== strstr($chain, _(self::COURSE_TYPE_LOCAL))) {
            //return str_replace('gridmod//', '', $actions);
            return $actions;
        }
        return '';
    }


    public function incrementTestLimitAction()
    {
        $status = $this->_changeTestLimit($this->_getParam('MID',0), $this->_getParam('SHEID',false), $this->_getParam('project_id',0));
         if ( $status ) {
             $this->_flashMessenger->addMessage(($status == 1)? _('Число попыток для пользователя успешно увеличено'): _('Нельзя превышать количество попыток, установленных в настройках теста'));
         } else {
         	$this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                     'message' => _('При выполнении операции произошла ошибка')));
         }
         $this->_redirector->gotoUrl($this->view->url(array('module' => 'project','controller' => 'index','action' => 'result','project_id'=>$this->_getParam('project_id',0),'lesson_id'=>$this->_getParam('lesson_id',0)),null,true));
    }


    public function decrementTestLimitAction()
    {
    if ( $this->_changeTestLimit($this->_getParam('MID',0), $this->_getParam('SHEID',0), $this->_getParam('project_id',0), 'decrement') ) {
             $this->_flashMessenger->addMessage(_('Число попыток для пользователя успешно уменьшено'));
         } else {
         	$this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                     'message' => _('При выполнении операции произошла ошибка')));
         }

         $this->_redirector->gotoUrl($this->view->url(array('module' => 'project','controller' => 'index','action' => 'result','project_id'=>$this->_getParam('project_id',0),'lesson_id'=>$this->_getParam('lesson_id',0)),null,true));
    }

    /**
     * Изменение количества попыток пользователя пройти тест
     * @param $mid
     * @param $lessonId
     * @param $projectId
     * @param string $operation
     * @return int 0-ошибка 1-успешно 2-успешно, результат установлен в 0 (поптка сделать меньше 0)
     */
    private function _changeTestLimit($mid, $lessonId, $projectId, $operation = 'increment')
    {
        $status = 0;
        if (!$mid || !$lessonId || !$projectId) return $status;
        $testCount = $this->getOne($this->getService('TestAttempt')->fetchAll(
                        $this->getService('TestAttempt')->quoteInto(
                            array('mid = ?', ' AND cid = ?', ' AND lesson_id = ?'),
                            array($mid, $projectId, $lessonId)
                        )
                    ));
        if ( $testCount ) {
            $status = 1;
        	$testCount->qty = ($operation == 'increment')? $testCount->qty - 1 : $testCount->qty + 1;
        	if ($testCount->qty < 0) {
                $testCount->qty = 0;
                $status         = 2;
            }
        	$this->getService('TestAttempt')->update($testCount->getValues());
        	return $status;
        }
	    return $status;
    }



    public function resultAction()
    {

        $projectId = $this->_getParam('project_id', 0);
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
            $isTeacher = true;
        } else {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        if($this->_getParam('progressgrid', '') != '' && strpos($this->_getParam('progressgrid', ''), '=') !==0){
            $this->_setParam('progressgrid', '=' . $this->_getParam('progressgrid', ''));
        }


/*
        $lessons = $this->getService('Lesson')->fetchAllJoinInner('Assign', 'Assign.MID = ' . (int) $userId . ' AND self.CID = ' . (int) $projectId);
        if(count($lessons) == 1){
            $lesson = $this->getOne($lessons);
//            $this->_redirector->gotoUrl($lesson->getResultsUrl()); // не надо этого делать
        }
*/
        $select = $this->getService('Project')->getSelect();

        $columnOptions = array(
           'SHEID' => array('hidden' => true),
           'MID' => array('hidden' => true),
           'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink(
                $this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}',
                _('Карточка пользователя')).'{{fio}}'),
           'Title' => array(
               'title' => _('Название'),
            ),
       		'typeID' => array(
                'hidden' => true,
           		'title' => _('Тип'),
                   'callback' => array(
                       'function' => array($this, 'updateType'),
                       'params' => array('{{typeID}}')
                   )
            ),
           	'V_DONE' => array(
               'title' => _('Состояние'),
                   'callback' => array(
                       'function' => array($this, 'updateDoneStatus'),
                       'params' => array('{{V_DONE}}')
                   )
            ),
           	'progress' => array(
               'title' => _('Результат'),
                   'callback' => array(
                       'function' => array($this, 'updateProgress'),
                       'params' => array('{{progress}}')
                   )
            ),
            'tryLast' => array(
                'title' => _('Дата последней попытки'),
                'format' => array(
                    'DateTime',
                    array('date_format' => Zend_Locale_Format::getDateTimeFormat())
            ),
            ),
        );

        $group = array(
                            'schid.MID',
                            'sch.SHEID',
                            'sch.Title',
                            'sch.typeID',
                            'schid.V_STATUS',
                            'schid.V_DONE',
            'schid.launched',
                    );
        if ($isTeacher) {
            $select->joinInner(array('p' => 'People'), 'p.MID = schid.MID', array('fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")));
            array_push($group,'p.LastName', 'p.FirstName', 'p.Patronymic');
        } else {
            $select->where('schid.MID = ?', $userId);
            unset($columnOptions['fio']);
        }

        $select->from(array('schid' => 'scheduleID'), array('MID'))
            ->joinInner(array('sch' => 'schedule'),
            		    'sch.SHEID = schid.SHEID',
                        array(
                        		'SHEID',
                        		'Title',
                        		'typeID',
                                'V_DONE'         => 'schid.V_DONE',
                                'progress'  	 => 'schid.V_STATUS',
                        		'tryLast'  		 => 'schid.launched',
                        ))
            ->where($this->quoteInto(array('sch.CID = ?'), array($projectId,0)))
            ->group($group);

        $people = $this->getService('User')->fetchAllJoinInner('Participant', 'Participant.CID = ' . (int) $projectId );
        $fios = array();
        foreach($people as $man){
            //Адский хак
            $fios['=' . $man->getName()] = $man->getName();
        }
        asort($fios);
        $lessons = $this->getService('Lesson')->fetchAll(array(
            'CID = ?' => $projectId,
            'isfree = ?' => HM_Lesson_LessonModel::MODE_FREE
        ), 'title');
        $lessonsName = $lessons->getList('title', 'title');

        $statuses = array('0' => _('Не начат'), '2' => _('Пройден'), '1' => 'В процессе');

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'Title_ASC');
        }


        $filterOptions =  array(
               'fio' => array('values' => $fios),
               'Title' => array('values' => $lessonsName),
               'V_DONE' => array('values' => $statuses),
               'progress' => array(null),
               'tryLast' => array('render' => 'DateTimeStamp')
        );

        if ( $this->_getParam('lesson_id',0) ) {
        	$select->where('sch.SHEID = ?', $this->_getParam('lesson_id',0)); // занятие
        	$columnOptions['Title'] = array('hidden' =>true);
            $columnOptions['V_DONE'] = array('hidden' =>true);
        } else {
            $select->where('sch.isfree = ?', HM_Lesson_LessonModel::MODE_FREE);
        }

        $grid = $this->getGrid(
            $select,
            $columnOptions,
            $filterOptions,
           'grid'
       );

        $grid->addAction(
            array('module' => 'project', 'controller' => 'index', 'action' => 'redirect-result'),
            array('SHEID','MID'),
            _('Подробнее')
        );

//        $grid->updateColumn('tryLast', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
            $grid->updateColumn('Title',
                                 array(
                                       'callback' =>
                                        array(
                                            'function' => array($this, 'getTitleString'),
                                            'params' => array('{{Title}}','{{typeID}}')
                                        )
                                    )
                                );

        $grid->setActionsCallback(
        		array('function' => array($this,'updateActions'),
        				'params'   => array('{{progress}}')
        		)
        );

        $this->view->grid = $grid;
    }


    public function updateActions($progress, $actions)
    {
        if ($progress) return $actions;
        }

    public function getTitleString($title,$typeID)
    {
        return '<span class="' . HM_Lesson_LessonModel::getIconClass($typeID) . '">' . $title . '</span>';
    }

    public function getTryCountString($count)
    {
        return (intval($count) > 0)? (int) $count : '';
    }

    public function getLeftCountString($count, $SHEID, $lessons) {
        $lesson = $lessons->exists('SHEID',$SHEID);
		$count = (int) $count;
        if ($lesson && $lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
	        if (isset($this->_testsCache[$lesson->getModuleId()])) {
	            $test = $this->_testsCache[$lesson->getModuleId()];
	        } else {
            $test = $this->getService('Test')->getOne($this->getService('Test')->find($lesson->getModuleId()));
	            $this->_testsCache[$lesson->getModuleId()] = $test;
	        }
            $startLimit = (int) $test->startlimit;

            if ($startLimit == 0) {
                 return _('Без ограничения');
            } else {
                return ( ($startLimit - $count) > 0)? $startLimit - $count : 0;
            }
        }

        return '';
    }

    public function redirectResultAction()
    {
        $lessonId = $this->_getParam('SHEID', 0);
        $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
        if($lesson){
            $this->_redirector->gotoUrl($lesson->getResultsUrl(array(
                                                                       'user_id'    => $this->_getParam('MID', 0),
                   'project_id' => $this->_getParam('project_id', 0),
                   'userdetail' => 'yes',
            )));
        }
    }

    public function assignAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $gridId = ($this->id) ? "grid{$projectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        $section = $this->getService('Section')->getDefaultSection($projectId);
        $currentOrder = $this->getService('Section')->getCurrentOrder($section);

        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $assign = $this->getOne(
                    $this->getService('ProjectCourse')->fetchAll(
                        $this->getService('ProjectCourse')->quoteInto(
                            array('project_id = ?', ' AND course_id = ?'),
                            array($projectId, $id)
                        )
                    )
                );
                if (!$assign) {
                    $this->getService('Project')->linkCourse($projectId, $id);
					$this->getService('Project')->update(array(
	                    'last_updated' => $this->getService('Project')->getDateTime(),
	                    'projid' => $projectId
	                ));
                }

                $this->getService('Course')->createLesson($this->_subject->projid, $id, HM_Lesson_LessonModel::MODE_FREE, $section, ++$currentOrder);
                }
            }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('courses', 'index', 'project', array('project_id' => $projectId));
    }

    public function unassignAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $gridId = ($this->id) ? "grid{$projectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {

                    $this->getService('Course')->clearLesson($this->_subject, $id);
                $this->getService('Project')->unlinkCourse($projectId, $id);
            }
        }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('courses', 'index', 'project', array('project_id' => $projectId));

    }

    public function updateTypeColumn($gridProjectId, $projectId)
    {
        if ($gridProjectId == $projectId) {
            $return = _('Учебный курс');
        } else {
            $return = _('База знаний');
        }
        return "<span class='nowrap'>{$return}</span>";
    }

    public function coursesListAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $project = $this->getService('Project')->getOne($this->getService('Project')->findDependence('CourseAssign', $projectId));

        $q = urldecode($this->_getParam('q', ''));

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $where = 'Status = 1';
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where .= ' AND '.$this->getService('Course')->quoteInto('LOWER(Title) LIKE LOWER(?)', $q);
        }

        $collections = $this->getService('Course')->fetchAll($where, 'Title');
        $courses = $collections->getList('CID', 'Title');
        if (is_array($courses) && count($courses)) {
            $count = 0;
            foreach($courses as $courseId => $title) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($project && $project->isCourseExists($courseId)) {
                    $courseId .= '+';
                }
                echo sprintf("%s=%s", $courseId, $title);
                $count++;
            }
        }
    }

    public function updateName($title, $courseId, $newWindow)
    {
/*        if($newWindow == 1) {
            $itemId = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $this->_getParam('project_id', 0), $courseId);
            if ($itemId != false){
                return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $title . '</a>';
            }
        }*/


        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
        //    $this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT
        ){
            if ($lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->fetchAll(array(
                'CID = ?' => $this->_subject->projid,
                "params LIKE '%module_id=" . $courseId . ";'",
                'isfree != ?' => HM_Lesson_LessonModel::MODE_FREE_BLOCKED
            )))) {
            return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $lesson->SHEID)). '">'. $title.'</a>';
            } else {
                return $title;
        }
        }

        return '<a href="' . $this->view->url(array('module' => 'project', 'controller' => 'course', 'action' => 'index', 'course_id' => $courseId)). '">'. $title.'</a>';
    }

    public function updateFormatColumn($format)
    {
        return HM_Course_CourseModel::getFormat($format);
    }

    public function updateType($type)
    {
        $types = HM_Event_EventModel::getTypes();
        return $types[$type];
    }

    public function updateDoneStatus($status)
    {
        if(!$status)     return _('Не начат');  // $status ==0 OR IS NULL
        if($status == 2) return _('Пройден');   // $status == 2

        return _('В процессе');                 // $status == 1
    }

    public function updateProgress($score)
    {
        if(empty($score) || $score < 0) return '';
        return $score;
                 }

    /**
     * Смена режима прохождения курса
     **/
    public function changemodeAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $project_id  = $this->_getParam('project_id',0);
        $access_mode = (int) $this->_getParam('access_mode',0);

        $project = $this->getService('Project')
                        ->getOne( $this->getService('Project')
                                       ->findDependence(array('Lesson',
                                                              'CourseAssign',
                                                              'ResourceAssign',
                                                              'TestAssign',
                                                              'TaskAssign'),
                                                        $project_id));
        if ($project) {
            $project->access_mode = $access_mode;

            $this->getService('Project')
                 ->update($project->getValues());

            // Удаляем плановые занятия и информацию о их прохождении и создаем новые записи
            if ( $project->access_mode == HM_Project_ProjectModel::MODE_FREE ) {

                // Удаление: записи в SheduleID удаляются автоматом (onDelete' => self::CASCADE)
                if ( count($project->lessons) ) {
                    foreach ( $project->lessons as $lesson ) {
                        $this->getService('Lesson')->delete($lesson->SHEID);
                    }
                }

                // Создание занятий для уч. модулей
                if ( count($project->courses) ) {
                    foreach ($project->courses as $course) {
                        $this->getService('Course')->createLesson($project->projid, $course->course_id);
                    }
                }

                // Создание занятий для ресурсов
                if ( count($project->resources) ) {
                    foreach ($project->resources as $resource) {
                        $this->getService('Resource')->createLesson($project->projid, $resource->resource_id);
                    }
                }

                // Создание занятий для тестов
                if ( count($project->tests) ) {
                    foreach ($project->tests as $test) {
                        $this->getService('TestAbstract')->createLesson($project->projid, $test->test_id);
                    }
                }
            }
        }

        $this->_redirector
             ->gotoUrl( $this->view
                             ->url( array('module'     => 'project',
                                          'controller' => 'index',
                                          'action'     => 'card',
                                          'project_id' => $project_id),
                                    null,
                                    true));
    }

    public function changeStateAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $projectId  = $this->_getParam('project_id',0);
        $state = (int) $this->_getParam('state', 0);

        $project = $this->getService('Project')->getOne($this->getService('Project')->findDependence(array('Participant'), $projectId));
        if ($project && $project->isStateAllowed($state)) {

            switch ($state) {
            	case HM_Project_ProjectModel::STATE_ACTUAL:

                    $this->getService('Project')->updateWhere(array(
                        'begin' => date('Y-m-d'),
                        'state' => $state,
                    ), array('projid = ?' => $projectId));

            	    foreach ($project->participants as $participant) {
                        $this->getService('Project')->startProjectForParticipant($projectId, $participant->MID);
                    }

            		break;
            	case HM_Project_ProjectModel::STATE_CLOSED:

                    $this->getService('Project')->updateWhere(array(
                        'end' => date('Y-m-d H:i:s'),
                        'state' => $state,
                    ), array('projid = ?' => $projectId));

                    foreach ($project->participants as $participant) {
                    	// #14371 - не нужно ничего делать с участниками конкурса
                        //$this->getService('Project')->assignGraduated($projectId, $participant->MID);
                    }

                    break;

            	default:
            	    // something wrong..
            	    return false;
            		break;
            }
        }
        $messenger = $this->getService('Messenger');
        foreach ($project->participants as $participant){
            $messenger->addMessageToChannel(HM_Messenger::SYSTEM_USER_ID,
                $participant->MID,
                HM_Messenger::TEMPLATE_PROJECT_STATE_CHANGED,
                array(
                    'PROJECT' => $project->title,
                    'STATE' =>$project->getStateTitle($state)
                )
            );
        }
        $messenger->sendAllFromChannels();

        $this->_redirector
             ->gotoUrl( $this->view
                             ->url( array('module'     => 'project',
                                          'controller' => 'index',
                                          'action'     => 'card',
                                          'project_id' => $projectId),
                                    null,
                                    true));

    }

    public function pinAction()
    {
        $this->pin($this->_getParam('subject_id'), $this->_getParam('uri'), true);
    }

    public function unpinAction()
    {
        $this->pin($this->_getParam('subject_id'), $this->_getParam('uri'), false);
    }

    protected function pin($subjectId, $uri, $pin = true)
    {
        if (!is_numeric($subjectId)) {
            $uriParts = explode('/', $uri);
            $subjectId = $uriParts[count($uriParts) - 1];
        }

        $this->getService('Project')->setDefaultUri($pin ? $uri : null, $subjectId);

        exit('1');
    }

    public function statementAction()
    {
        $projectID = $this->_getParam('projid', $this->_getParam('project_id', 0));
        $project   = $this->getService('Project')->getOne($this->getService('Project')->find($projectID));

        if (!$project) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Курс не найден')
            ));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        if ($project->state != HM_Project_ProjectModel::STATE_CLOSED) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Курс не закрыт')
            ));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }

        $score = $this->getService('Lesson')->getUsersScore($project->projid ,'' ,'', null, true);
        $this->view->persons    = $score[0];
        $this->view->schedules  = $score[1];
        $this->view->scores     = $score[2];
    }

    public function downloadProtocolAction()
    {
        $projectID = $this->_getParam('projid', $this->_getParam('project_id', 0));
        $project   = $this->getService('Project')->getOne($this->getService('Project')->find($projectID));

        $options = array('filename' => $project->protocol);
        $path = Zend_Registry::get('config')->path->upload->project_protocols . '/' . $project->projid;

        $this->_helper->SendFile(
            $path,
            'application/unknown',
            $options
        );
        die();
    }
}
