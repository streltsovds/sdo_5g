<?php
class Subject_CoursesController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $default = new Zend_Session_Namespace('default');
        if ($subjectId && !isset($default->grid['subject-index-courses'][$gridId])) {
            $default->grid['subject-index-courses'][$gridId]['filters']['subid'] = $subjectId; // по умолчанию показываем только слушателей этого курса
        }

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", 'title_ASC');
        }

        $select = $this->getService('Subject')->getSelect();
        $select->from(
            array('c' => 'Courses'),
            array(
                'c.CID',
                'c.Title',
                'c.chain',
                'chaintemp' =>'c.chain',
                'subid' => 's.subject_id',
                'c.new_window',
                'c.format',
                'tags' => 'c.CID'
            ))
        ->joinLeft(array('s' => 'subjects_courses'), "c.CID = s.course_id AND subject_id = '".$subjectId."'", array())
        ->where('(s.subject_id = ? OR s.subject_id IS NULL)', $subjectId)
        ->where(new Zend_Db_Expr($this->getService('Subject')->quoteInto(array("c.Status = ? ", "OR c.Status = ?"), array(HM_Course_CourseModel::STATUS_STUDYONLY, HM_Course_CourseModel::STATUS_ACTIVE))))
        ->where('c.chain IS NULL OR c.chain = 0 OR c.chain = ?', $subjectId);


        $grid = $this->getGrid($select,
                               array(
                                   'CID' => array('hidden' => true),
                                   'new_window' => array('hidden' => true),
                                   'chaintemp' => array('hidden' => true),
                                   'Title' => array('title' => _('Название')),
                                   'chain' => array(
                                       'title' => _('Место хранения'),
                                       'callback' => array(
                                           'function' => array($this, 'updateTypeColumn'),
                                           'params' => array('{{chain}}', $subjectId)
                                       )
                                       ),
                                       'subid' => array(
                                        'title' => _('Доступ для слушателей'),
                                        'callback' => array(
                                            'function' => array($this, 'updateSubjectColumn'),
                                            'params' => array(HM_Event_EventModel::TYPE_COURSE, '{{CID}}', '{{subid}}', $subjectId)
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
                                           $subjectId => _('Учебный курс'),
                                           0 => _('База знаний')
                                       )
                                   ),
                                   'format' => array('values' => HM_Course_CourseModel::getFormats()),
                                   'tags' => array('callback' => array('function' => array($this, 'filterTags')))
                               ),
                               $gridId);
        
        if($this->getService('Acl')->isCurrentAllowed('privileges:gridswitcher')){
            $options = array(
                    'local' => array('name' => 'local', 'title' => _('используемые в данном учебном курсе'), 'params' => array('subid' => $subjectId)),
                    'global' => array('name' => 'global', 'title' => _('все, включая учебные модули из Базы знаний'), 'params' => array('subid' => null), 'order' => 'subid', 'order_dir' => 'DESC'),
            );
            
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $options);
            $options = $event->getReturnValue();
            
            $grid->setGridSwitcher($options);            
        }
        $grid->setClassRowCondition("'{{subid}}' != ''", "success");


        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'courses', 'action' => 'assign', 'subject_id' => $subjectId),
            _('Использовать в курсе и открыть свободный доступ для слушателей'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'courses', 'action' => 'unassign', 'subject_id' => $subjectId),
            _('Не использовать в курсе и закрыть свободный доступ для слушателей'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array('module' => 'subject', 'controller' => 'courses', 'action' => 'course-delete-by', 'subject_id' => $subjectId),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $subj = $this->getOne($this->getService('Subject')->find($subjectId));

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
                'subject_id' => $subjectId
            ),
            array(
                'CID'
            ),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(
            array(
                'module' => 'subject',
                'controller' => 'index',
                'action' => 'course-delete',
                'subject_id' => $subjectId
            ),
            array(
                'CID'
            ),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                  'params'   => array('{{chain}}')
            )
        );

        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array('{{tags}}', $this->getService('TagRef')->getCourseType(),$subjectId ,'{{chaintemp}}' )
            )
        ));


        $this->view->subjectId = $subjectId;
        $this->view->isGridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid;
    }

    public function delete($subjectId, $courseId)
    {
        $course = $this->getOne($this->getService('Course')->find($courseId));
        if ($course) {
            if ($course->chain == $subjectId) {
                if ($this->getService('Teacher')->isUserExists($subjectId, $this->getService('User')->getCurrentUserId()) ||
                    $this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $subjectId)) {
                    $this->getService('Course')->delete($course->CID);

                    $this->getService('Course')->clearLesson(null, $courseId);

                    return true;
                } else {
                    throw new HM_Exception(_('Вы не являетесь тьютором на данном учебном курсе.'));
                    //$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не являетесь тьютором на данном учебном курсе.')));
                }
            } else {
                throw new HM_Exception(_('Учебный модуль не используется в данном учебном курсе.'));
                //$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Учебный модуль не используется в данном учебном курсе.')));
            }
        }
    }

    public function deleteAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $courseId = (int) $this->_getParam('CID', 0);

        if ($subjectId && $courseId) {
            try {
                $this->courseDelete($subjectId, $courseId);
            } catch (HM_Exception $e) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $e->getMessage()));
            }
        }

        $this->_redirector->gotoSimple('index', 'courses', 'subject', array('subject_id' => $subjectId));
    }

    public function deleteByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_'.$gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            $error = false;
            if (count($ids)) {
                foreach($ids as $id) {
                    try {
                        $this->courseDelete($subjectId, $id);
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

        $this->_redirector->gotoSimple('index', 'courses', 'subject', array('subject_id' => $subjectId));
    }

    public function updateActions($chain, $actions)
    {
        if (false !== strstr($chain, _('Учебный курс'))) {
            return $actions;
        }
        return '';
    }

    public function assignAction()
    {
        $subjectId = $this->_subjectId;
        $gridId = ($this->_subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        $section = $this->getService('Section')->getDefaultSection($subjectId);
        $currentOrder = $this->getService('Section')->getCurrentOrder($section);

        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {
                $assign = $this->getOne(
                    $this->getService('SubjectCourse')->fetchAll(
                        $this->getService('SubjectCourse')->quoteInto(
                            array('subject_id = ?', ' AND course_id = ?'),
                            array($subjectId, $id)
                        )
                    )
                );
                if (!$assign) {
                    $this->getService('Subject')->linkCourse($subjectId, $id);
                    $this->getService('Subject')->update(array(
                        'last_updated' => $this->getService('Subject')->getDateTime(),
                        'subid' => $subjectId
                    ));
                }

                $this->getService('Course')->createLesson($this->_subject->subid, $id, HM_Lesson_LessonModel::MODE_FREE, $section, ++$currentOrder);
                }
            }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('index', 'courses', 'subject', array('subject_id' => $subjectId));
    }

    public function unassignAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $gridId = ($this->_subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');

        $ids = explode(',', $postMassIds);
        if (is_array($ids) && count($ids)) {
            foreach($ids as $id) {

                    $this->getService('Course')->clearLesson($this->_subject, $id);
                $this->getService('Subject')->unlinkCourse($subjectId, $id);
            }
        }

        $this->_flashMessenger->addMessage(_('Связи с учебными модулями успешно изменены'));
        $this->_redirector->gotoSimple('index', 'courses', 'subject', array('subject_id' => $subjectId));

    }

    public function updateTypeColumn($gridSubjectId, $subjectId)
    {
        if ($gridSubjectId == $subjectId) {
            $return = _('Учебный курс');
        } else {
            $return = _('База знаний');
        }
        return "<span class='nowrap'>{$return}</span>";
    }

    public function updateName($title, $courseId, $newWindow)
    {
        if($newWindow == 1) {
            $itemId = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $this->_getParam('subject_id', 0), $courseId);
            if ($itemId != false){
                return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $title . '</a>';
            }
        }


        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
        //    $this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_STUDENT
        ){
            if ($lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->fetchAll(array(
                'CID = ?' => $this->_subject->subid,
                "params LIKE '%module_id=" . $courseId . ";'",
                'isfree != ?' => HM_Lesson_LessonModel::MODE_FREE_BLOCKED
            )))) {
            return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $lesson->SHEID)). '">'. $title.'</a>';
            } else {
                return $title;
        }
        }

        return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'course', 'action' => 'index', 'course_id' => $courseId)). '">'. $title.'</a>';
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
}
