<?php
class HM_Programm_ProgrammService extends HM_Service_Abstract
{
    protected $_cache = [
        'getById' => []
    ];

    /**
     * Назначаем программу пользователю
     *
     * @param $userId
     * @param $programmId
     * @return bool
     */
    public function assignToUser($userId, $programmId, $assignContextEvents = false, $newcomer_id = null)
    {
        if ($this->isAssigned($userId, $programmId)){

            if ($assignContextEvents) {
                return $this->getService('ProgrammUser')->assignContextEvents($userId, $programmId, $newcomer_id);
            } else {
                return false;
            }
        }

        return $this->getService('ProgrammUser')->assign($userId, $programmId, $assignContextEvents, $newcomer_id);
    }

    public function delete($programmId)
    {
        /* Удаляем связь Программа - Учебная группа */
        $groups =  $this->getService('StudyGroupProgramm')->getProgrammGroups($programmId);
        if (count($groups)) {
            foreach ($groups as $group) {
                $this->getService('StudyGroupProgramm')->removeGroupFromProgramm($group->group_id, $programmId);
            }
        }

        /* Отписываем слушателей программы */
        $users = $this->getProgrammUsers($programmId);
        if (count($users)) {
            foreach ($users as $user) {
                $this->getService('ProgrammUser')->unassign($user->user_id, $programmId);
            }
        }

        /* Удаляем привязку программа - евенты */
        $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(
                array('programm_id = ?'),
                array($programmId)
            )
        );
        
        if (count($collection = $this->getService('Process')->fetchAll(array(
                'programm_id = ?' => $programmId,
        )))) {
            $process = $collection->current();
        
            $this->getService('State')->deleteBy(array(
                    'process_id = ?' => $process->process_id,
            ));
            $this->getService('Process')->delete($process->process_id);
        }        

        return parent::delete($programmId);
    }

    public function getEvents($programmId)
    {
        //$events = $this->getService('ProgrammEvent')->fetchAllDependence('Evaluation', array('programm_id = ?' => $programmId));
        $events = $this->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programmId), 'ordr');
        return $events;
    }

    public function getProgrammUsers($programmId)
    {
        return $this->getService('ProgrammUser')->fetchAll(array('programm_id = ?' => $programmId));
    }

    public function updateCoursesForUsers($programmId, $addCourses = array(), $removeCourses = array())
    {

        $users = $this->getProgrammUsers($programmId);
        if ($users) {
            if ($addCourses) {
               foreach ($addCourses as $subjectId) {
                   $subject = $this->getSubject($programmId, $subjectId);
                   if ($subject && !$subject->isElective) {
                       foreach ($users as $user) {
                           $this->getService('Subject')->assignStudent($subjectId, $user->user_id, array(
//                               'event' => $event
                           ));
                       }
                   }
               }
            }
            if ($removeCourses) {
                foreach ($addCourses as $subjectId) {
                    $subject = $this->getSubject($programmId, $subjectId);
                    /* Удаление с курса, и все равно элективный или нет */
                    //if ($subject && !$subject->isElective) {
                        foreach ($users as $user) {
                            $this->getService('Subject')->unassignStudent($subjectId, $user->user_id);
                        }
                    //}
                }
            }
        }
    }

    public function updateCoursesForGroups($programmId, $newIds = array(), $oldIds = array())
    {
        $oldCourses = array_merge($oldIds['Elektive'], $oldIds['noElektive']);
        $newCourses = array_merge($newIds['Elektive'], $newIds['noElektive']);

        $addCourses = array_diff($newCourses, $oldCourses);
        $removeCourses = array_diff($oldCourses, $newCourses);

        $addNoElectiveCourses = array_diff($newIds['noElektive'], $oldIds['noElektive']);

        $groups = $this->getService('StudyGroupProgramm')->getProgrammGroups($programmId);
        if ($groups) {
            if ($addCourses) {
                foreach ($addCourses as $courseId) {
                    $course = $this->getSubject($programmId, $courseId);
                    if ($course) {
                        foreach ($groups as $group) {
                            $this->getService('StudyGroupCourse')->addCourseOnGroup($course->item_id, $group->group_id, $course->isElective);
                        }
                    }
                }
            }
            if ($removeCourses) {
                foreach ($removeCourses as $courseId) {
                    $course = $this->getSubject($programmId, $courseId);
                    if ($course) {
                        foreach ($groups as $group) {
                            $this->getService('StudyGroupCourse')->removeGroupFromCourse($group->group_id, $course->item_id, false); //$course->isElective); Удаляем все курсы со слушателей, группа уже не подписана на программу
                        }
                    }
                }
            }
            /* Изменение статуса на неэлективный ToDo покачто не трогаем когда неэлективный стал элективным*/
            if (!$addCourses && !$removeCourses && $addNoElectiveCourses) {
                foreach ($addNoElectiveCourses as $courseId) {
                    $course = $this->getSubject($programmId, $courseId);
                    if ($course) {
                        foreach ($groups as $group) {
                            $this->getService('StudyGroupCourse')->addCourseOnGroup($course->item_id, $group->group_id, $course->isElective);
                        }
                    }
                }
            }

        }

    }

    public function getProgrammsBySubjectId($subjectId, $currentUserId)
    {
        $programms = $this->fetchAllJoinInner(//Dependence
            'ProgrammEvents',
            $this->quoteInto(array(
                    'ProgrammEvents.item_id = ?',
                    'AND type = ?'
                ),
                array(
                    $subjectId,
                    HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT
                ))
        );
        $output = array();

        if (count($programms)) {
            foreach ($programms as $programm) {
                if ($this->isAssigned($currentUserId, $programm->programm_id)) {
                    $output[] = $programm;
                }
            }

        }
        return $output;
    }

    public function getSubjects($programmId)
    {
        $subjects = $this->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programmId, 'type = ?' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
        return $subjects;
    }

    public function getSubject($programmId, $subjectId)
    {
        $subjects = $this->getOne($this->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programmId, 'type = ?' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, 'item_id = ?' => $subjectId)));
        return $subjects;
    }

    /**
     * @param $userId
     * @param $programmId
     * @param null $cycleId - похоже это пришло из филиппмориса; в базовой версии программы не привязаны к циклам
     */
    public function isAssigned($userId, $programmId, $cycleId = null)
    {
        $fetch = $this->getService('ProgrammUser')->fetchAll(array('user_id = ?' => $userId, 'programm_id = ?' => $programmId));

        if(count($fetch) > 0){
            return true;
        }
        return false;
    }

    public function getUserProgramms($userId, $programmType = null)
    {
        $select = $this->getSelect();
        $select->from(
            array('p' => 'programm'),
            array('p.*')
        )
            ->joinInner(
                array('pu' => 'programm_users'),
                'p.programm_id = pu.programm_id',
                array()
            )
            ->where('pu.user_id = ?', $userId);
        if($programmType !== null) {
            $select->where('p.programm_type = ?', $programmType);
        }
        return $select->query()->fetchAll();
    }


    public function getUserElsProgramms($userId)
    {

        $select = $this->getSelect();
        $select->from(
            array('p' => 'programm'),
            array('p.*')
        )
            ->joinInner(
                array('pu' => 'programm_users'),
                'p.programm_id = pu.programm_id',
                array()
            )
            ->where('pu.user_id = ?', $userId)
            ->where('(p.programm_type = ? OR p.programm_type IS NULL)', HM_Programm_ProgrammModel::TYPE_ELEARNING)
        ;


        $s = $select->query()->fetchAll();
        return $select->query()->fetchAll();
    }

    public function getUserProgress($programmId,$userID)
    {
        $events = $this->getEvents($programmId);

        $isEnded = 0;
        $count = 0;
        $graduated = $this->getService('Graduated')->fetchAll(array('MID = ?' => $userID));

        foreach ($events as $event) {
            if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                $count++;
                if (count($graduated) && $graduated->exists('CID', $event->item_id)) {
                    $isEnded ++;
                }
            }
        }

        return sprintf(_('пройдено %s из %s'), $isEnded, $count);
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('программа plural', '%s программа', $count), $count);
    }

    public function assignItem($data)
    {
        $event = false;
        $collection = $this->getService('ProgrammEvent')->fetchAll(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?', ' AND item_id = ?'),
                array($data['programm_id'], $data['type'], $data['item_id'])
            )
        );

        if (!count($collection)) {
            $event = $this->getService('ProgrammEvent')->insert(
                array(
                    'programm_id' => $data['programm_id'],
                    'type' => $data['type'],
                    'item_id' => $data['item_id'],
                    'name' => $data['name'],
                    'ordr' => $data['ordr'],
                )
            );
            
            $this->getService('ProgrammEvent')->assignToUsers($event);
            
        } else {
            $values = $collection->current()->getData();
            $values['ordr'] = $data['ordr'];
            $event = $this->getService('ProgrammEvent')->update($values);
        }

        return $event;
    }

    public function unassignItem($programmId, $itemId, $type = HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)
    {
        return $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?', ' AND item_id = ?'),
                array($programmId, $type, $itemId)
            )
        );
    }
	
	// то же смое, что assignItem() - частный случай для программ обучения
	// наверное, надо рефакторить в assignItem()
    public function assignSubject($programmId, $subject, $isElective = 0, $ordr = 0)
    {
        $collection = $this->getService('ProgrammEvent')->fetchAll(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?', ' AND item_id = ?'),
                array($programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $subject->subid)
            )
        );

        if (!count($collection)) {
            return $this->getService('ProgrammEvent')->insert(
                array(
                    'programm_id' => $programmId,
                    'type' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
                    'item_id' => $subject->subid,
                    'isElective' => $isElective,
                    'name' => $subject->name,
                    'ordr' => $ordr,
                    'day_end' => 14
                )
            );
        } else {
            return $this->getService('ProgrammEvent')->updateWhere(
                array(
                    'name' => $subject->name,
                    'isElective' => $isElective,
                    'ordr' => $ordr
                ),
                array(
                    'programm_id = ?' => $programmId,
                    'type = ?' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
                    'item_id = ?' => $subject->subid,
                )
            );
        }

    }

    public function unassignSubject($programmId, $subjectId)
    {
        return $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?', ' AND item_id = ?'),
                array($programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $subjectId)
            )
        );
    }
    public function getById($id)
    {
        $cacheName = 'getById';

        if ($this->_cache[$cacheName][$id]) {
            $output = $this->_cache[$cacheName][$id];
        } else {

            $output = $this->getOne($this->fetchAll($this->quoteInto('programm_id = ?', $id)));
            $this->_cache[$cacheName][$id] = $output;
        }

        return $output;
    }

    public function copy($programm, $data)
    {
        $events = $this->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programm->programm_id));
        $data = array_merge($programm->getData(), $data);
        unset($data['programm_id']);
        
        $programm = parent::insert($data);
        
        foreach ($events as $event) {
            $this->getService('ProgrammEvent')->copy($event, array(
                'programm_id' => $programm->programm_id,
            ));
        }
        
        // evaluation'ы еще привязаны к старой программе; нужно их перепривязать
        if ($programm->isEvaluation()) {
        
            $programm = $this->getOne($this->findManyToMany('Evaluation', 'Event', $programm->programm_id));
            if (count($programm->evaluation)) {
                $evaluationIds = $programm->evaluation->getList('evaluation_type_id');
                switch ($programm->item_type) {
                    case HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY:
                        $data = array(
                            'category_id' => $programm->item_id,
                        );
                    break;
                    case HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE:
                        $data = array(
                            'profile_id' => $programm->item_id,        
                            'category_id' => 0,        
                        );                        
                    break;
                    case HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY:
                        $data = array(
                            'vacancy_id' => $programm->item_id,        
                            'profile_id' => 0,        
                            'category_id' => 0,        
                        );                        
                    break;
                    case HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER:
                        $data = array(
                            'newcomer_id' => $programm->item_id,        
                            'profile_id' => 0,        
                            'category_id' => 0,        
                        );
                    break;
                    case HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE:
                        $data = array(
                            'reserve_id' => $programm->item_id,
                            'profile_id' => 0,
                            'category_id' => 0,
                        );
                    break;
                }
                $this->getService('AtEvaluation')->updateWhere($data, array('evaluation_type_id IN (?)' => $evaluationIds));
                foreach ($evaluationIds as $evaluationId) {
                    // нужно еще связать evaluation с компетенциями (всех типов) профиля
                    $this->getService('AtEvaluation')->updateCriteria($evaluationId);
                }
            }        
        }        
        
        return $programm;
    }
    
    public function getProgramms($itemType, $itemId, $programmType = null)
    {
        $condition = array(
            'item_type = ?' => $itemType,
        );
        if (!is_array($itemId)) {
            $condition['item_id = ?'] = (int)$itemId;
        } else {
            $condition['item_id IN (?)'] = $itemId;
        }
        if ($programmType !== null) {
            $condition['programm_type = ?'] = $programmType;
        }

        return $this->fetchAllDependence(array('User', 'Event', 'EventUser', 'Process'), $condition);
    }    
    
    public function getActiveProcesses($programm)
    {
        $return = array();
        $collection = $this->getService('State')->fetchAllDependenceJoinInner('Process', $this->getService('Process')->quoteInto(array(
            'Process.programm_id = ? AND ',
            'self.status IN (?)',
        ), array(
            $programm->programm_id,
            array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING)
        )));
        if (count($collection)) {
            // нужно еще проверить насколько живые эти процессы
            // возможно просто артефакты в базе; не должны блокировать
            // конструкция вида "IN (много-премного)" не работает в оракле 
            $itemIds = $collection->getList('item_id');

            if (count($itemIds)) {
                switch ($programm->programm_type) {
                    case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                        $collection = $this->getService('AtSessionUser')->fetchAll(array(
                            'session_user_id IN (?)' => $itemIds,
                            'status != ?' => HM_At_Session_User_UserModel::STATUS_COMPLETED
                        ));
                        break;
                    case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                        $collection = $this->getService('RecruitVacancyAssign')->fetchAll(array(
                            'vacancy_candidate_id IN (?)' => $itemIds,
                            'status != ?' => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED
                        ));
                        break;
                    case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                        $collection = $this->getService('RecruitNewcomer')->fetchAll(array(
                            'newcomer_id IN (?)' => $itemIds,
                            'status != ?' => HM_Recruit_Newcomer_NewcomerModel::STATE_CLOSED
                        ));
                        break;
                    default:
                        $collection = array();
                        break;
                }
                $return = $collection;
            }
        }
        return $return;
    }
    
    public function getDuration($programm)
    {
        if (!count($programm->events)) {
            // @todo
        } else {
            $daysEnd = $programm->events->getList('day_end');
            return max($daysEnd);
        }
        return 0;
    }

    public static function updateIcon($programId, $photo, $destination = null, $skipResize = false, $removeIcon = 0)
    {
        if (empty($destination)) {
            $destination = HM_Programm_ProgrammModel::getIconFolder($programId);
            $isSubject = true;
        } else {
            $isSubject = false;
        }
        $w = HM_Programm_ProgrammModel::THUMB_WIDTH;
        $h = HM_Programm_ProgrammModel::THUMB_HEIGHT;

        $path = rtrim($destination, '/') . '/' . $programId . '.jpg';

        if ($removeIcon) {
            unlink($path);
            return true;
        }

        if (is_null($photo)) return false;

        if ($photo instanceof HM_Form_Element_ServerFile) {
            $photoVal = $photo->getValue();
            //если инпут пустой - удаляем текущее изображение
            if (empty($photoVal)) {
                unlink($path);
                return true;
            }
            $original = APPLICATION_PATH . '/../public' . $photoVal;
            //если новая картинка = старой, то ничего не меняем
            if (md5_file($original) == md5_file($path)) {
                return true;
            }
            if ($skipResize) {
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);
            $img->save($path);
        } elseif ($photo->isUploaded()){
            $original = rtrim($photo->getDestination(), '/') . '/' . $photo->getValue();
            if ($skipResize) {
                $path = rtrim($destination, '/') . '/' . $programId . '-full.jpg';
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);
            $img->save($path);
            unlink($original);
        }
        return true;
    }

    /**
     * @return Zend_Db_Select
     */
    public function getSelectElearningProgramms()
    {
        $select = $this->getSelect();

        $select->from(
            array('p' => 'programm'),
            array(
                'p.programm_id',
                'p.name',
                'items' => new Zend_Db_Expr('GROUP_CONCAT(pe.item_id)'),
                'groups' => 'p.programm_id'
            )
        )->joinLeft(
            array('pe' => 'programm_events'),
            'pe.programm_id = p.programm_id',
            array()
//        )->joinLeft(
//            array('s' => 'subjects'),
//            'pe.item_id = s.subid AND pe.type = '.HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
//            array()
        )->where(
            'pe.type = ? OR pe.type IS NULL', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT
        )
            // здесь показываем только уч.программы, не привязанные к категориям/профилям
            ->where('(p.programm_type = ? OR p.programm_type IS NULL) AND p.item_id IS NULL', HM_Programm_ProgrammModel::TYPE_ELEARNING)
            ->group(array('p.programm_id', 'p.name'));

        return $select;
    }
}