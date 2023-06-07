<?php
class Session_TestController extends HM_Controller_Action_Session_Crud
{
    protected $listSelect;

    /**
     * Список занятий в оценочной сессии
     */
    public function listAction()
    {
        $select = $this->getService('AtSession')->getSelect();

        $select->from(
          array(
            'se' => 'at_session_events'
          ),
          array(
            'lesson_id' => 'l.SHEID',
            'l.SHEID',
            'TypeID2' => 'l.typeID',
            'l.title',
            't.lim',
            't.startlimit',
            'l.begin',
            'l.end',
            'l.timetype',
            'l.cond_sheid',
            'l.cond_mark',
            'l.cond_progress',
            'l.cond_avgbal',
            'l.cond_sumbal',
            'l.isfree',
            'sort_order' => 'l.order',
            'mids' => 'GROUP_CONCAT(DISTINCT sid.MID)'
          )
        );

        $select->joinInner(array('sel' => 'at_session_event_lessons'), 'se.session_event_id = sel.session_event_id', array());
        $select->joinInner(array('l' => 'lessons'), 'l.SHEID = sel.lesson_id AND '.$this->quoteInto('l.typeID = ?', HM_Event_EventModel::TYPE_TEST), array());
        $select->joinInner(array('t' => 'test'), 't.lesson_id = l.SHEID', array());
        $select->joinInner(array('sid' => 'scheduleID'), 'sid.SHEID = l.SHEID', array());

        $select->where('se.session_id = ?', $this->_session->session_id)
               ->group(array(
                   'l.SHEID',
                   'l.typeID',
                   'l.title',
                   't.lim',
                   't.startlimit',
                   'l.begin',
                   'l.end',
                   'l.timetype',
                   'l.cond_sheid',
                   'l.cond_mark',
                   'l.cond_progress',
                   'l.cond_avgbal',
                   'l.cond_sumbal',
                   'l.isfree',
                   'l.order'
               ))
               ->order(array('sort_order'));

        $this->listSelect = $select;
        //exit($select->__toString());

        $grid = $this->getGrid($select,
          array(
            'sort_order' => array('order' => true,'hidden' => true),
            'SHEID' => array('hidden' => true),
            'TypeID2' => array('hidden' => true),
            'lesson_id' => array('hidden' => true),
            'title' => array('title' => _('Название')),
            'lim' => array(
                'title' => _('Количество вопросов'),
                'callback' => array(
                    'function' => array($this, 'callback_lim'),
                    'params' => '{{lim}}'
                )
            ),
            'startlimit' => array(
                'title' => _('Количество попыток'),
                'callback' => array(
                    'function' => array($this, 'callback_startlimit'),
                    'params' => '{{startlimit}}'
                )
            ),
            'begin' => array('title' => _('Ограничение по времени')),
            'mids' => array('title' => _('Назначено слушателям')),
            'end' => array('hidden' => true),
            'timetype' => array('hidden' => true),
            'cond_sheid' => array('hidden' => true),
            'cond_mark' => array('hidden' => true),
            'cond_avgbal' => array('hidden' => true),
            'cond_sumbal' => array('hidden' => true),
            'cond_progress' => array('hidden' => true),
            'isfree' => array('hidden' => true),
          ),
          array()

        );

        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('title');
        $filters->addFilter('mids', array(
            'callback' => array(
                'function'=>array($this, 'midsFilter'),
                'params'=>array()
            )
        ));

        $grid->addFilters($filters);

        $user_fio = $this->_getParam('user_fio', '');

        if ($user_fio) {
            $default = Zend_Registry::get('session_namespace_default');
            $request = Zend_Controller_Front::getInstance()->getRequest();

            $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
            $default->grid[$page][$grid->getGridId()]['filters']['mids'] = $user_fio;
        }

        /*$grid->addAction(
          array('module' => 'lesson', 'controller' => 'result', 'action' => 'index', 'preview' => 1),
          array('lesson_id'),
          _('Просмотр результатов')
        );*/

        $grid->setActionsCallback(
          array('function' => array($this,'updateActions'),
            'params'   => array('{{TypeID2}}')
          )
        );

        $grid->addAction(array(
          'module' => 'session',
          'controller' => 'test',
          'action' => 'edit'
        ),
          array('lesson_id'),
          $this->view->svgIcon('edit', 'Редактировать')
        );

        /*$grid->addAction(array(
          'module' => 'lesson',
          'controller' => 'list',
          'action' => 'delete'
        ),
          array('lesson_id'),
          $this->view->svgIcon('delete', 'Удалить')
        );*/

        //$grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных занятий? Если занятие было создано на основе информационного ресурса или учебного модуля, эти материалы вновь станут доступными всем слушателям курса в меню <Материалы курса>.'));

        $grid->updateColumn('begin',
          array(
            'callback' =>
            array(
              'function' => array($this, 'getDateTimeString'),
              'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
            )
          )
        );

        /*$grid->updateColumn('title',
          array(
            'callback' =>
            array(
              'function' => array($this, 'updateName'),
              'params' => array('{{title}}', '{{lesson_id}}', '{{typeID}}')
            )
          )
        );*/

        $grid->updateColumn('lim',
          array(
            'callback' =>
            array(
              'function' => array($this, 'updateLim'),
              'params' => array('{{lim}}')
            )
          )
        );

        $grid->updateColumn('startlimit',
          array(
            'callback' =>
            array(
              'function' => array($this, 'updateStartLimit'),
              'params' => array('{{startlimit}}')
            )
          )
        );

        $grid->updateColumn('condition',
          array(
            'callback' =>
            array(
              'function' => array($this, 'getConditionString'),
              'params' => array('{{cond_sheid}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
            )
          )
        );

        $grid->updateColumn('mids',
          array(
            'callback' =>
            array(
              'function' => array($this, 'getMidNames'),
              'params' => array('{{mids}}')
            )
          )
        );

        // exit($select->__toString());

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }
    
    public function callback_lim($lim) 
    {
        return $lim ? $lim : 'Все вопросы';
    }

    public function callback_startlimit($startlimit) 
    {
        return $startlimit ? $startlimit : 'Не ограничено';
    }
   
    /**
     * Список занятий в оценочной сессии
     */
    public function listAdvancedAction()
    {
        $select = $this->getService('AtSession')->getSelect();

        $select->from(
          array(
            'se' => 'at_session_events'
          ),
          array(
            'lesson_id' => 'l.SHEID',
            'l.SHEID',
            'TypeID2' => 'l.typeID',
            'l.title',
            'l.timetype',
            'l.cond_sheid',
            'l.cond_mark',
            'l.cond_progress',
            'l.cond_avgbal',
            'l.cond_sumbal',
            'l.isfree',
            'sort_order' => 'l.order',
            'user_fio' => "CONCAT(CONCAT(p.LastName, ' '), p.FirstName)",
            'lu.status',
            'lu.stop',
            'bal' => new Zend_Db_Expr("CASE WHEN (lu.balmax2 = lu.balmin2) THEN '' ELSE CONCAT(ROUND(100 * (lu.bal - lu.balmin2) / (lu.balmax2 - lu.balmin2),2), '%') END"),
          )
        );

        $select->joinInner(array('sel' => 'at_session_event_lessons'), 'se.session_event_id = sel.session_event_id', array());
        $select->joinInner(array('l' => 'lessons'), 'l.SHEID = sel.lesson_id AND '.$this->quoteInto('l.typeID = ?', HM_Event_EventModel::TYPE_TEST), array());
        $select->joinInner(array('t' => 'test'), 't.lesson_id = l.SHEID', array());
        $select->joinInner(array('sid' => 'scheduleID'), 'sid.SHEID = l.SHEID', array());
        $select->joinInner(array('p' => 'People'), 'p.MID = sid.MID', array());

        $subSelect = $this->getService('AtSession')->getSelect();

        $subSelect->from(
          array(
            'lu' => 'loguser'
          ),
          array(
            'lu.tid',
            'lu.mid',
            'bal_max' => 'MAX(lu.bal)'
          )
        );

        $subSelect->group(array('lu.tid'));

        $select->joinLeft(array('max_lu' => $subSelect), 'max_lu.tid = t.tid AND max_lu.mid = p.MID', array());
        $select->joinLeft(array('lu' => 'loguser'), 'lu.tid = max_lu.tid AND lu.bal = max_lu.bal_max', array());

        $select->where('se.session_id = ?', $this->_session->session_id)
               ->group(array(
                   'l.SHEID',
                   'l.typeID',
                   'l.title',
                   'l.timetype',
                   'l.cond_sheid',
                   'l.cond_mark',
                   'l.cond_progress',
                   'l.cond_avgbal',
                   'l.cond_sumbal',
                   'l.isfree',
                   'l.order',
                   'sid.MID',
                   'p.LastName',
                   'p.FirstName',
                   'lu.stop',
                   'lu.bal',
                   'lu.balmin2',
                   'lu.balmax2',
                   'lu.status'
               ))
               ->order(array('sort_order'));

        $this->listSelect = $select;

        $grid = $this->getGrid($select,
          array(
            'sort_order' => array('order' => true,'hidden' => true),
            'SHEID' => array('hidden' => true),
            'TypeID2' => array('hidden' => true),
            'lesson_id' => array('hidden' => true),
            'title' => array('title' => _('Название')),
            'stop' => array('title' => _('Дата результативной попытки'), 'format' => array('dateTime', array('date_format' => HM_Locale_Format::getDateTimeFormat()))),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                  'function' => array($this, 'getTestStatus'),
                  'params' => array('{{status}}')
                )
            ),
            'bal' => array('title' => _('Результат')),
            'user_fio' => array('title' => _('Слушатель')),
            'end' => array('hidden' => true),
            'timetype' => array('hidden' => true),
            'cond_sheid' => array('hidden' => true),
            'cond_mark' => array('hidden' => true),
            'cond_avgbal' => array('hidden' => true),
            'cond_sumbal' => array('hidden' => true),
            'cond_progress' => array('hidden' => true),
            'isfree' => array('hidden' => true),
          ),
          array()

        );

        $grid->addAction(
          array('baseUrl' => '', 'module' => 'lesson', 'controller' => 'result', 'action' => 'index'),
          array('lesson_id'),
          _('Просмотр результатов')
        );

        $grid->updateColumn('begin',
          array(
            'callback' =>
            array(
              'function' => array($this, 'getDateTimeString'),
              'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
            )
          )
        );

        $grid->updateColumn('condition',
          array(
            'callback' =>
            array(
              'function' => array($this, 'getConditionString'),
              'params' => array('{{cond_sheid}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
            )
          )
        );


        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function getTestStatus($status)
    {
        static $statuses;

        if (!$statuses) {
           $statuses = HM_Test_Result_ResultModel::getStatuses();
        }

        if (isset($statuses[$status])) {
            return $statuses[$status];
        }

        return '';

    }

    public function midsFilter($data)
    {
       $select = $data['select'];
       $value = $data['value'];
       $select->where("sid.MID IN (SELECT MID FROM People WHERE CONCAT(CONCAT(CONCAT(CONCAT(LastName, ' '), FirstName), ' '), Patronymic) LIKE ? )", "%$value%");
    }

    protected function setExtendedFile()
    {
        // блокируем подгрузку ненужного нам шаблона от родительского контроллера
    }

    protected $midNamesLoaded = false;

    public function getMidNames($mids)
    {
       if (!$this->midNamesLoaded) {
          $items = $this->listSelect->query()->fetchAll();
          $ids = array();

          foreach ($items as $item) {
              $itemMids = explode(',', $item['mids']);
              foreach ($itemMids as $mid) {
                  if (!$mid) {
                      continue;
                  }

                  $ids[] = (int) $mid;
              }
          }

          $where = $this->quoteInto('MID IN (?)', new Zend_Db_Expr(implode(',', $ids)));

          $usersModels = $this->getService('User')->fetchAll($where);
          $users = array();

          foreach ($usersModels as $userModel) {
              $users[$userModel->MID] = $userModel->getName();
          }

          $this->midNamesLoaded = $users;
       }

       $mids = explode(',', $mids);
       $result = array();

       foreach ($mids as $mid) {
           if (isset($this->midNamesLoaded[$mid])) {
               $result[] = $this->midNamesLoaded[$mid];
           }
       }

       return implode(', ', $result);
    }


    public function updateActions($typeID, $actions) {
        $lesson = HM_Lesson_LessonModel::factory(array('typeID' => $typeID));

        if(!$lesson) return $actions;

        if ( $lesson->isResultInTable() ) {
            return $actions;
        } else {
            $tmp = explode('<li>', $actions);
            unset($tmp[1]);
            return implode('<li>', $tmp);
        }
    }


    public function getConditionString($condSheid, $condMark, $condProgress, $condAvg, $condSum)
    {
        $conditions = HM_Lesson_LessonModel::getConditionTypes();
        if ($condSheid > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_LESSON];
        }
        if ($condProgress > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_PROGRESS];
        }
        if ($condAvg > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_AVGBAL];
        }
        if ($condSum > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_SUMBAL];
        }
        return _('Нет');
    }

    public function getDateTimeString($begin, $end, $timetype)
    {
        switch($timetype) {
            case 1:
                if (($end == 0) || ($begin == 0)) {
                	$beginOrEnd = ($begin == 0) ? $end : $begin;
                	return sprintf(_('%s-й день'), floor($beginOrEnd / 60 /60 /24));
                } elseif ($begin != $end) {
                    return sprintf(_('%s-й день - %s-й день'), floor($begin / 60 /60 /24), floor($end / 60 /60 /24));
                } else {
                    return sprintf(_('%s-й день'), floor($begin / 60 /60 /24));
                }
                break;
            case 2:
                return _('Без ограничений');
                break;
            default:
                $begin = new HM_Date($begin);
                $end = new HM_Date($end);
                return sprintf('%s - %s', $begin->get(Zend_Date::DATETIME_SHORT), $end->get(Zend_Date::DATETIME_SHORT));
                break;
        }
    }

    public function updateLim($lim){
        return $lim ? $lim : _('все вопросы теста');
    }

    public function updateStartLimit($startlimit){
        return $startlimit ? $startlimit: _('не ограничено');
    }

    public function updateName($field, $id, $type){

        if($type == HM_Event_EventModel::TYPE_COURSE){

            $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($id));

            $courseId = $lesson->getModuleId();

            $course = $this->getService('Course')->getOne($this->getService('Course')->find($courseId));

            if($course->new_window == 1){
                $itemId = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $this->_getParam('subject_id', 0), $courseId);
                if($itemId != false){
                    return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'baseUrl' => '','course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $field.'</a>';
                }
            }
        }

        $target = ($type == HM_Event_EventModel::TYPE_WEBINAR) ? ' target="_blank" ' : '';

        return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'baseUrl' => '', 'lesson_id' =>$id, 'subject_id' => $this->_getParam('subject_id'))). '" title="' . _('Просмотр занятия') . '"'. $target . '>'. $field.'</a>';
    }

    public function editAction()
    {
        $form = new HM_Form_TestEdit();

        $lessonId = $this->_getParam('lesson_id');

        $test = $this->getOne($this->getService('Test')->fetchAll(
            $this->getService('Test')->quoteInto(
                array('lesson_id = ?'),
                array($lessonId)
            )
        ));

        /*
         * questions
         */

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {

                $test->lim = $form->getValue('lim');
                $test->qty = $form->getValue('qty');
                $test->startlimit = $form->getValue('startlimit');
                $test->limitclean = $form->getValue('limitclean');
                $test->timelimit = $form->getValue('timelimit');
                $test->random = $form->getValue('random');
                $test->endres = $form->getValue('endres');
                $test->skip = $form->getValue('skip');
                $test->allow_view_log = $form->getValue('allow_view_log');

                $test = $this->getService('Test')->update($test->getValues());


                $this->_flashMessenger->addMessage(_('Тест успешно изменен'));

                $url = array(
                  'module'     => 'session',
                  'controller' => 'test',
                  'action'     => 'list',
                  'baseUrl'    => '',
                  'session_id' => $this->_getParam('session_id')
                );

                $this->_redirector->gotoUrl($this->view->url($url, null,true));
            }
        } else {
            $form->setDefaults(array(
                'lim'            => $test->lim,
                'qty'            => $test->qty,
                'startlimit'     => $test->startlimit,
                'limitclean'     => $test->limitclean,
                'timelimit'      => $test->timelimit,
                'random'         => $test->random,
                'endres'         => $test->endres,
                'skip'           => $test->skip,
                'allow_view_log' => $test->allow_view_log
            ));
        }

        $this->view->form = $form;
    }
}