<?php
class Programm_SubjectController extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    private $_programmId = 0;
    private $_programm = null;
    private $_mode = 0;

    private $_profile = null;

    public function init()
    {
        $this->_programmId = (int) $this->_getParam('programm_id' , 0);
        $this->_programm = $this->getOne($this->getService('Programm')->findDependence('Event', $this->_programmId));
        $this->_mode = (int) $this->_getParam('mode', 0);

        $profileId = $this->_getParam('profile_id', 0);

        if ($profileId && count($profiles = $this->getService('AtProfile')->find($profileId))) {

            $this->_profile = $profiles->current();
            $this->initContext($this->_profile);
//            $this->view->addSidebar('profile', [
//                'model' => $this->_profile,
//            ]);
            $this->view->setBackUrl($this->view->url([
                'baseUrl' => 'at',
                'module' => 'profile',
                'controller' => 'list',
            ], null, true));
        } else {
            $this->view->setHeader($this->_programm->name);
            $this->view->setSubHeader(_('Редактирование программы'));

            if (isset($_SERVER['HTTP_REFERER'])) {
                $redirectUrl = parse_url($_SERVER['HTTP_REFERER']);
                $path = isset($redirectUrl['path']) ? $redirectUrl['path'] : '';
                $query = isset($redirectUrl['query']) ? '?' . $redirectUrl['query'] : '';
                $redirectUrl = $path . $query;
        
                // $params = [
                //     'redirect_url' => urlencode($redirectUrl),
                // ];

                $this->view->setBackUrl($redirectUrl);
            } else {
                $this->view->setBackUrl($this->view->url([
                    'module' => 'programm',
                    'controller' => 'list',
                    'programm_id' => null,
                ]));
            }
        }

        if (!$this->_getParam('start',0) && !$this->_getParam('end',0)) {
            $this->view->showCopyButton = $this->_programm->item_type === HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY;
        }

        parent::init();
    }

    public function indexAction()
    {
        $subjects = $this->getService('Subject')->fetchAll(
            array(
                'base IN (?)' => array(
                    HM_Subject_SubjectModel::BASETYPE_PRACTICE,
                    HM_Subject_SubjectModel::BASETYPE_BASE,
                ),
                'type != ?' => HM_Tc_Subject_SubjectModel::TYPE_FULLTIME
            ),
            'name'
        );

        $sessions = $this->_profile ? [] : $this->getService('Subject')->fetchAll(
            $this->quoteInto('base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION),
            'name'
        );

        $events = array();

        $collection = $this->getService('ProgrammEvent')->fetchAll(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?'),
                array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)
            ),
            'ordr'
        );

        if (count($collection)) {

            $subjectIds = array_filter($collection->getList('item_id'));
            if (count($subjectIds)) {
                $selectedSubjects = $this->getService('Subject')->fetchAll(
                    $this->quoteInto('subid IN (?)', $subjectIds)
                )->asArrayOfObjects();
            }

            foreach ($subjectIds as $subjectId) {

                $subject = $selectedSubjects[$subjectId];
                $this->_setVueProperties($subject);

                foreach ($collection as $item) {
                    if ($item->item_id == $subject->subid) {
                        $subject->pin = $item->isElective;
                    }
                }
                $events[$subjectId] = $subject;
            }
        }

        $users = $this->getService('Programm')->getProgrammUsers($this->_programmId);
        if (count($users)) {
            $this->view->message = sprintf(_('Внимание! По данной программе уже обучается %s чел.'), count($users)); //._(' человек <br>Удаление не элективного курса уничтожит набранные по нему результаты обучения.<br> Элективный курс у пользователя не удаляется, если он на него подписан.<br> Перевод курса со статуса "Элективный" в "Обязательный" автоматически подпишет всех слушателей программы на этот курс.<br> Перевод курса со статуса "Обязательный" в "Элективный" оставит подписанными всех слушателей программы на этом курсе.');
        }

        $this->view->isDeanLocal = $this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL);

        $subsubjects =
        $subsessions = [];

        foreach ($subjects as $subject) {
            $this->_setVueProperties($subject);
            $subsubjects[$subject->subid] = $subject;
        }

        foreach ($sessions as $session) {
            $this->_setVueProperties($session);
            $subsessions[$session->subid] = $session;
        }

        $subjectsItems = array(
            'name' =>  _("Все учебные курсы"),
            'subsubjects' => $subsubjects,
            'subsessions' => $subsessions,
        );

        $this->view->page = 0;
        $this->view->events = $events;
        $this->view->items = array($subjectsItems);
        $this->view->programmId = $this->_programmId;
    }

    public function assignAction()
    {
        if ($this->isAjaxRequest()) {
            $this->getHelper('viewRenderer')->setNoRender();

            $ids = $this->_getParam('item_id', array());
            $isElectives = $this->_getParam('is_unpin', array());


            $oldSubjects = $this->getService('Programm')->getSubjects($this->_programmId);
            $oldIds = array ('Elektive' => array(), 'noElektive' => array());
            $newIds = array ('Elektive' => array(), 'noElektive' => array());
            if ($oldSubjects) {
                foreach ($oldSubjects as $oldSubject) {
                    if ($oldSubject->isElective) {
                        $oldIds['Elektive'][] =  $oldSubject->item_id;
                    } else {
                        $oldIds['noElektive'][] =  $oldSubject->item_id;
                    }
                }
            }

            if (count($ids)) {

                $collection = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $ids));
                $subjects = $collection->asArrayOfObjects();

                foreach($ids as $key=>$id) {
                    // #28313
                    // принудительно у курса установили ручной старт и режим "Фиксированная длительность"
//                    $subject = $this->getService('Subject')->find($id)->current();
//                    $this->getService('Subject')->update(
//                        array(
//                            'period_restriction_type' => HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
//                            'period'   => HM_Subject_SubjectModel::PERIOD_FIXED,
//                            'state'    => HM_Subject_SubjectModel::STATE_PENDING,
//                            'longtime' => $subject->longtime ? $subject->longtime : 1, // по дефолту 1 день
//                            'subid'    => $id
//                        )
//                    );

                    if ($isElectives[$key]) {
                        $newIds['Elektive'][] =  $id;
                    } else {
                        $newIds['noElektive'][] =  $id;
                    }

                    $this->getService('Programm')->assignSubject($this->_programmId, $subjects[$id], $isElectives[$key], $key);

                    $programm = $this->getService('Programm')->findOne($this->_programmId);

                    if ($this->_mode == 1 && $programm !== false) {

                        try {
                            // Если необходимо скопировать программу в профили
                            $categoryId = $programm->item_id;
                            $profiles = $this->getService('AtProfile')->fetchAllDependence('Position',
                                $this->quoteInto("category_id = ?", $categoryId)
                            );
                            if (count($profiles)) {
                                foreach ($profiles as $profile) {
                                    $this->getService('AtProfile')->assignProgrammFromCategory($profile, $programm);
                                }
                            }
                            echo HM_Json::encodeErrorSkip(array('result' => 1));
                        } catch (Exception $e) {
                            echo HM_Json::encodeErrorSkip(array('result' => 0));
                        }
                        exit();
                    }
                }
            }

            $addIds = array_diff($newIds['noElektive'], $oldIds['noElektive']);
            $removeIds = array_diff($oldIds['noElektive'], $newIds['noElektive']);
            /* обновляем список курсов для пользователей программы, возвращаем МИДы слушателей для которых обновили курсы*/
            $usersIds = $this->getService('Programm')->updateCoursesForUsers($this->_programmId, $addIds, $removeIds);

            /* обновляем список курсов на группах */
            $this->getService('Programm')->updateCoursesForGroups($this->_programmId, $newIds, $oldIds);


            /* Удаляем связь программа курс */
            if (count($ids)) {

                $this->getService('ProgrammEvent')->deleteBy(
                    $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?', ' AND item_id NOT IN (?)'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $ids)
                    )
                );
            } else {
                /* Удаляем все курсы */
                $this->getService('ProgrammEvent')->deleteBy(
                    $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)
                    )
                );
            }
        } else {
            $this->_redirector->gotoSimple('index');
        }
    }

    public function unassignAction()
    {
        $events = explode(',', $this->_getParam('postMassIds_grid', ''));

        if (count($events)) {
            foreach($events as $eventId) {
                $this->getService('ProgrammEvent')->delete($eventId);
            }
        }

        $this->_redirector->gotoSimple('index', 'index', 'programm', array('programm_id' => $this->_programmId));
    }

    public function calendarAction()
    {
        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {

            $begin = HM_Date::getAbstractDay(intval($this->_getParam('start')));
            $end   = HM_Date::getAbstractDay(intval($this->_getParam('end')));

            if (count($this->_programm->events)) {
                $itemIds = $this->_programm->events->getList('item_id');
                $subjects = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $itemIds))->asArrayOfObjects();
            }

            $eventsSources = array();
            foreach ($this->_programm->events as $event) {
                if (($event->day_begin + 1 <= $end) && ($event->day_end + 1 >= $begin) && ! $event->hidden && ! $event->isElective) {

                    $day_begin = (int)$event->day_begin ? (int)$event->day_begin : 1;
                    $day_end = (int)$event->day_end ? (int)$event->day_end : 1;
                    $subject = $subjects[$event->item_id];
                    $eventsSources[] = array(
                        'id'    => $event->programm_event_id,
                        'ordr'    => $event->ordr,
                        'title' => $subject->name,
                        'color' => "#{$subject->base_color}",
                        //первая секунда дня
                        'start' => ($day_begin - 1) * 86400 + 1,//86400 = 60s*60m*24h
                        'end'   => ($day_end - 1) * 86400 + 1,
                        'editable' => true,
                        'borderColor' => 'blue'
                    );
                }
            }

            usort($eventsSources, function($item01, $item02){
                return $item01['ordr'] < $item02['ordr'] ? -1 : 1;
            });

//            $tempView = $this->view->assign($eventsSources);
//            unset($tempView->lists);

            exit(HM_Json::encodeErrorSkip($eventsSources));

        } else {
            $this->view->source = array('module'=>'programm', 'controller'=>'subject', 'action'=>'calendar', 'no_user_events' => 'y');
            $this->view->editable = !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
        }
    }

    public function saveCalendarAction()
    {
        $eventId = $this->_getParam('eventid',0);
        $begin = HM_Date::getAbstractDay(floatval($this->_getParam('start'))/1000); //в миллисекундах
        $end   = HM_Date::getAbstractDay($endSeconds = floatval($this->_getParam('end'))/1000);

        $result    = _('При сохранении данных произошла ошибка');
        $status    = 'fail';

        if ($this->_request->isPost() && $eventId && $begin && $end) {

            $event = $this->getService('ProgrammEvent')->getOne($this->getService('ProgrammEvent')->find($eventId));
            if ($event) {
                $data = $event->getData();
                $data['day_begin'] = $begin;
                $data['day_end'] = $endSeconds ? $end : $begin;
                if ($this->getService('ProgrammEvent')->update($data)) {
                    $result = _('Данные успешно сохранены');
                    $status = 'success';
                }
//                $this->getService('Subject')->update(
//                    array(
//                        'longtime' => $end - $begin + 1, // +1 т.к. стартовый день должен учитываться
//                        'subid' => $data['item_id'],
//                        )
//                );
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }

    /**
     * @param HM_Subject_SubjectModel $subject
     */
    private function _setVueProperties(HM_Model_Abstract $subject): void
    {
        $subject->pin = false;
        $subject->freemode = in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN));
        $subject->class = $subject->is_labor_safety ? "highlighted" : "";
    }
}