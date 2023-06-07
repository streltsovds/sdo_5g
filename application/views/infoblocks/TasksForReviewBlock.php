<?php


/*
	Виджет 	ЗАДАНИЯ НА ПРОВЕРКУ (№task#12882)
		перечень учебных курсов, доступных данному пользователю с ролью «преподаватель». 
		Под каждым курсов - перечень занятий с типом "Задание". 
		Если в курсе нет заданий - он вообще не попадает в список. 
*/

class HM_View_Infoblock_TasksForReviewBlock extends HM_View_Infoblock_Abstract
{

    protected $id = 'tasksForReviewBlock';

    /**
     * Получение учебных курсов
     * @author Elena.Mirzoyan
     */
    public function TasksForReviewBlock($param = null)
    {
        try {
            $subjects = array();

            /** @var HM_User_UserService $userService */
            $userService = $this->getService('User');

            $currentUserId = (int)$userService->getCurrentUserId();
            $select = $userService->getSelect();
            if ($this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_TEACHER])) {

                $eventTypesSubselect = $userService->getSelect()
                    ->from('events', array('event_id' => '-(event_id)'))
                    ->where('tool = ?', HM_Event_EventModel::TYPE_TASK)
                    ->query()->fetchAll();

                $select->from(array('s' => 'subjects'), array(
                    'subname' => 's.name',
                    'subid' => 's.subid',
                    'sheid' => 'schedule.SHEID',
                    'schetitle' => 'schedule.title'
                ))
                    ->joinInner('Teachers', 's.subid = Teachers.CID', array())
                    ->joinInner('schedule', 's.subid = schedule.CID', array())
                    ->where('Teachers.MID = ?', $currentUserId)
                    ->where($userService->quoteInto(
                        array('schedule.typeID = ? OR ', 'schedule.typeID in (?)'),
                        array(HM_Event_EventModel::TYPE_TASK, isset($eventTypesSubselect) && $eventTypesSubselect ? $eventTypesSubselect : '')
                    ));

                $rows = $select->query()->fetchAll();

                if (count($rows)) { // Проверяем наличие учебных курсов

                    foreach ($rows as $row)
                        $subids[$row['subid']] = 1;

                    $courses = $this->getService('Subject')->fetchAll("subid in (" . implode(',', array_keys($subids)) . ")");

                    /** @var HM_Subject_SubjectModel $course */
                    foreach ($courses as $course) {
                        if ($course->isExpired())
                            $coursesExpired[$course->subid] = 1;
                    }

                    foreach ($rows as $row) {
                        if (isset($coursesExpired[$row['subid']]))
                            continue;


                        $onStatement = $userService->quoteInto(
                            'st.CID = ? AND st.mid = s.mid', $row['subid']
                        );

                        $select = $this->getService('Lesson')->getSelect();
                        $subSelect = clone $select;

                        $subSelect->from(
                            array('ri' => 'task_conversations'),
                            array(
                                'real_user_id' => 'user_id',
                                'real_interview_id' => new Zend_Db_Expr('MAX(ri.conversation_id)'),
                            )
                        )
                            ->where('ri.lesson_id = ?', $row['sheid'])
                            ->group('user_id');

                        $select->from(
                            array('s' => 'scheduleID'),
                            array(
                                'count_' => 'count(i.type)',
                                'i.type'
                            )
                        )
                            ->joinInner(array('ss' => $subSelect), 's.MID = ss.real_user_id', array())
                            ->joinInner(array('i' => 'task_conversations'), 'i.conversation_id = ss.real_interview_id', array())
                            ->joinInner(array('st' => 'Students'), $onStatement, array())
                            ->joinInner(array('p' => 'People'), 'p.mid=s.mid and p.blocked=0', array())
                            ->where('SHEID = ?', $row['sheid'])
                            ->group('i.type');

                        $stmt = $select->query();
                        $stmt->execute();
                        $rows = $stmt->fetchAll();

                        $type = array();

                        foreach ($rows as $value) {
                            $type[$value['type']] = $value['count_'];
                        }

                        for ($i = 5; $i >= 0; $i--) {
                            $type[$i] = empty($type[$i]) ? 0 : $type[$i];
                        }

                        $subjects[$row['subid']]['subname'] = $row['subname'];
                        $subjects[$row['subid']]['lessons'][$row['sheid']] = array(
                            'schetitle' => $row['schetitle'],
                            'task' => $type[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK],
                            'question' => $type[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_QUESTION],
                            'test' => $type[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TO_PROVE],
                            'answer' => $type[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ANSWER],
                            'condition' => $type[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_REQUIREMENTS],
                            'ball' => $type[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ASSESSMENT],
                            'url' => $this->view->url(array('module' => 'subject', 'controller' => 'results', 'action' => 'index', 'subject_id' => $row['subid'], 'lesson_id' => $row['sheid'])),
                        );
                    }
                } else {
                    // Отсутствуют данные для отображения
                    $this->view->empty = true;
                }
                $this->view->subjects = $subjects;
            } else {
                $this->view->subjects = array();
        	//message that you have not enought permissions
            }

            $this->view->subjects = $subjects;
            $content = $this->view->render('TasksForReviewBlock.tpl');

            $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/schedule_table.css');
            $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/infoblocks/tasks_for_review/style.css');

            return $this->render($content);
        } catch (Exception $e) {
            echo 'Выброшено: ', $e->getMessage(), "\n";
        }
    }

}
