<?php

class Subject_ListController extends HM_Controller_Action_Mobile {

    const MESSAGES_MAX = 100;

    public function sendSupportAction() {
        $request = (array)$this->getInput()->request;
        $request['user_id'] = $this->getService('User')->getCurrentUserId();
        $request['date_'] = date('Y-m-d H:i:s');
        $request['status'] = HM_Techsupport_TechsupportModel::STATUS_NEW;
        if(isset($request['image'])) {
            $file = $this->getService('Files')->addFileFromBinary(base64_decode(substr($request['image'], 23)), 'support_'.date('d.m.Y_H-i-s').'.jpg');
            if($file) {
                $request['file_id'] = $file->file_id;
            }
            unset($request['image']);
        }

        $this->getService('Techsupport')->insert($request);
        die('{}');
    }

    public function pollsAction() {

        $return = $this->view;
        $userId = $this->getService('User')->getCurrentUserId();

        if (!$userId) die_error('not_authorized', 'Авторизация не совершена');

        $subjects = $this->getService('Feedback')->getUserFeedback($userId);
        $quests = array();
        foreach($subjects as $subject) {
            foreach($subject['feedbacks'] as $feedback) {
                $quests[] = array('id'=>$feedback['quest_id'], 'name'=>$feedback['name'], 'url'=>
                                    "quest/feedback/start/quest_id/{$feedback['quest_id']}/feedback_user_id/{$feedback['feedback_user_id']}/redirect_url/close_window/mobile/1");
            }                                      
        }
        $this->view->list = array_values($quests);

        $return->error = "";
        $return->length = count($this->view->list);
    }

    public function sendAction() {
        $data = $this->getInput();

        $messenger = $this->getService('Messenger');
        $messenger->setOptions(
            HM_Messenger::TEMPLATE_PRIVATE,
            array(
                'text' => $data->text,
                'subject' => _(($this->getService('User')->getCurrentUser()->getName()).' оставил(-а) Вам личное сообщение')
            )
        );

        try {
            $messenger->send($this->getService('User')->getCurrentUserId(), $data->to);
        } catch (Exception $e) {
        }


    }
    public function messagesReadedAction() {
        $read = $this->getInput()->read;

        if(count($read)) {

           $this->getService('Message')->getMapper()->getAdapter()->getAdapter()->
                query("UPDATE messages SET readed=1 WHERE message_id IN (".implode(',', $read).")");
        }

        $this->countUnreadMessagesAction();
    }

    public function messagesAction() {
        $read = $this->getInput()->read;
        if(count($read)) {

           $this->getService('Message')->getMapper()->getAdapter()->getAdapter()->
                query("UPDATE messages SET readed=1 WHERE message_id IN (".implode(',', $read).")");
        }

        $return = $this->view;
        $userId = $this->getService('User')->getCurrentUserId();
        if (!$userId) die_error('not_authorized', 'Авторизация не совершена');

        $select = $this->getService('Message')->getSelect();
        $select->from(array('m' => 'messages'), array(
            'id'=>'m.message_id',
            'body'=>'m.message',
            'readed'=>'m.readed',
            'date'=>'m.created',
            'receiverId'=>'m.to',
            'receiverFio'=>new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p2.LastName, ' ') , p2.FirstName), ' '), p2.Patronymic)"),
            'senderId'=>'m.from',
            'senderFio'=>new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p1.LastName, ' ') , p1.FirstName), ' '), p1.Patronymic)"),
            ))
            ->joinLeft(array('p1' => 'People'), 'p1.MID = m.from', array())
            ->joinLeft(array('p2' => 'People'), 'p2.MID = m.to', array());
        
        $select->where('(m.to = ? OR m.from = ?)', $userId);
        $select->where('m.created > ?', date('Y-m-d 00:00', time()-3600*24*30));
        $select->order('senderFio', 'm.created DESC');
        $messages = $select->query()->fetchAll();

        $this->view->list = array();
        foreach($messages as $message) {

            $message['date'] = date('d.m.Y H:i', strtotime($message['date']));
            $message['read'] = $message['readed'];

            $bOutBox = $message['senderId']==$userId;
            $message['outbox'] = $bOutBox;

            $message['groupId'] = $bOutBox ? $message['receiverId'] : $message['senderId'];
            $message['groupName'] = $bOutBox ? $message['receiverFio'] : $message['senderFio'];
/*
            unset($message['receiverId']);
            unset($message['senderId']);
            unset($message['receiverFio']);
            unset($message['senderFio']);
*/
  
            $this->view->list[] = $message;
            if(count($this->view->list) >= self::MESSAGES_MAX) break;
        }

        $return->error = "";
        $return->length = count($this->view->list);
    }


    public function countUnreadMessagesAction() {

        $return = $this->view;
        $userId = $this->getService('User')->getCurrentUserId();
        if (!$userId) die_error('not_authorized', 'Авторизация не совершена');

        $messages = $this->getService('Message')->fetchAll(array('readed = ?'=>0,'to = ?'=>$userId, 'created > ?'=>date('Y-m-d 00:00', time()-3600*24*30)), 'created DESC');
        $return->count = count($messages);
    }

    public function newsAction() 
    {
        $request = (array)$this->getInput();

        $b4Homepage = $request['homepage'];
        $lastDate = $request['last_date'];

        $select = $this->getService('News')->getSelect();
        $select->from(array('n' => 'news'),array(
            'id', 'created', 'author', 'title'=>'n.name',
            'announce' => new Zend_Db_Expr("CAST(n.announce AS VARCHAR(MAX))"),
            'message' => new Zend_Db_Expr("CAST(n.message AS VARCHAR(MAX))"),
            'icon_url',
            'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT c.name)"),
            'classifiers_ids' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT c.classifier_id)"),
            ))
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                'n.id = cl.item_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_CLASSIFIER_NEWS,
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                'c.classifier_id = cl.classifier_id', 
                array()
            )
            ->group(array('id', 'created', 'author', 'n.name', 'n.icon_url', new Zend_Db_Expr("CAST(n.announce AS VARCHAR(MAX))"), new Zend_Db_Expr("CAST(n.message AS VARCHAR(MAX))")))
            ->order('created DESC');

        $select->limit(HM_News_NewsModel::MOBILE_PAGE_SIZE);
        if($b4Homepage) {
            $select->where('n.mobile = ?', 1);
        } 
        else 
        if($lastDate)  {
            $select->where('n.created < ?', $lastDate);
        }
        $select->where('(n.subject_id = 0 OR n.subject_id IS NULL)', 1);

        $news = $select->query()->fetchAll();
        $news = $this->getService('News')->getMapper()->fetchAllFromArray($news);
        $result = array();
        $result['news'] = array();
        $classifiers = array();
        if($subject['classifiers_ids'])  {
                $subClassifiers  = explode(',', $subject['classifiers']);
                $subid2classifier[$subject['subid']] = $subClassifiers;
                $classifiers = array_merge($classifiers, $subClassifiers);
        }

        $classifiers = array();
        $lastDateOut = false;

        foreach ($news as $item) {

            if($item->classifiers_ids)  {
                $subClassifiers  = explode(',', $item->classifiers_ids);
                $classifiers = array_merge($classifiers, $subClassifiers);
            }

            $result['news'][] = array(
                'id' => $item->id,
                'date' => date("Y-m-d", strtotime($item->created)),
                'author' => $item->author,
                'title' => mb_substr(strip_tags($item->title ? $item->title : ''), 0, 80),
                'announce' => strip_tags($item->getAnnounce() ? $item->getAnnounce() : ''),
//                'title' => $item->title,
                'message' => $item->message,
                'body' => $item->message,
                'attach_url' => $item->url,
                'url' => "/news/index/view/news_id/{$item->id}",
                'image' => $item->icon_url,
                'classifiers' => $item->classifiers,
                'classifiers_ids' => $item->classifiers_ids ? explode(',', $item->classifiers_ids) : array(),
            );
            $lastDateOut = $item->created;
        }

        $classifiers = !count($classifiers) ? array() : $this->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $classifiers), false, null, 'name ASC');
        $result['classifiers'] = $lastDate ? array() : array(array('id'=>0, 'name'=>'Все'));

        foreach($classifiers as $classifier) {
            $result['classifiers'][] = array('id'=>$classifier->classifier_id, 'name'=>$classifier->name);
        }
        $result['last_date'] = $lastDateOut;
        $result['bNoMore'] = count($result['news']) != HM_News_NewsModel::MOBILE_PAGE_SIZE;

        die(json_encode($result));
    }

    public function courseAction() {

        $return = $this->view;
        $subjectId = $this->_getParam('subject_id', 0);
        $lessonId = $this->_getParam('lesson_id', 0);
        $userId = $this->getService('User')->getCurrentUserId();

        $CID = false;
        if ($subjectId) {
            $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->fetchAll(
                array(
                    ' CID = ? ' => $subjectId,
                    ' SHEID = ? ' => $lessonId,
                )));

            if ($lesson->typeID == HM_Event_EventModel::TYPE_COURSE) {
                $params = explode(';', $lesson->params);
                foreach ($params as $param) {
                    $p = explode('=', $param);
                    if ($p[0] == 'module_id') {
                        $CID = $p[1];
                    }
                }
                $editable = $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_DEAN) ||
                    $this->getService('Subject')->isTeacher($subjectId, $userId) ? '&editable' : '';

            } elseif ($lesson->typeID == HM_Event_EventModel::TYPE_TASK) {
                $taskMessageCondition = array(
                    'type = ?' => HM_Interview_InterviewModel::MESSAGE_TYPE_TASK,
                    'lesson_id = ?' => $lessonId,
                    'to_whom' => $userId,
                );
                $taskMessages = Zend_Registry::get('serviceContainer')->getService('Interview')->fetchAll($taskMessageCondition);

                $taskMessage = $taskMessages->current();
                $CID = $taskMessage->resource_id;

                $editable = '&editable&finalizable&mode_personal&user_id='.$userId;
            }

            $organisations = $this->getService('CourseItem')->getOne($this->getService('CourseItem')->fetchAll(array(' CID = ? ' => $CID, 'module != ?' => 0), 'oid ASC'));

            $this->_redirect('/course/item/view/lesson_id/'.$lesson->SHEID.'/course_id/'.$CID.'/item_id/'.$organisations->oid.'?app'.$editable, array('prependBase' => false));

        }

    }
/*
    public function courseAction() {
//        $return = $this->view;
        $subjectId = $this->_getParam('subject_id', 0);
        $lessonId = $this->_getParam('lesson_id', 0);
        $resourceId = $this->_getParam('resource_id', 0);
        $userId = $this->getService('User')->getCurrentUserId();

//        $this->_redirect('/course/item/view/lesson_id/'.$lesson->SHEID.'/course_id/'.$CID.'/item_id/'.$organisations->oid.'?app'.$editable, array('prependBase' => false));

    }
*/
    public function catalogAction()
    {
        $return = $this->view;
        $userId = $this->getService('User')->getCurrentUserId();
        $now = date('Y-m-d H:i:s');

        $select = $this->getService('Subject')->getSelect();
        $condition = $select->orWhere('period = ?', HM_Subject_SubjectModel::PERIOD_FREE)
                    ->orWhere($this->getService('Subject')->quoteInto(
                        array('period = ? '),
                        array(HM_Subject_SubjectModel::PERIOD_FIXED)
                    ))
                    ->orWhere($this->getService('Subject')->quoteInto(
                        array('period = ? ', ' AND period_restriction_type = ?'),
                        array(HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT)
                    ))
                    ->orWhere($this->getService('Subject')->quoteInto(
                        array('s.begin < ?',' AND s.end > ?',' AND period = ?',' AND period_restriction_type = ?'),
                        array($now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT)
                    ))
                    ->orWhere($this->getService('Subject')->quoteInto(
                        array('state = ?',' AND period = ?',' AND period_restriction_type = ?'),
                        array(HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)
                    ))
                    ->getPart( Zend_Db_Select::WHERE );
        $condition = is_array( $condition ) ? implode( " ", $condition ) : $condition;
        $select->reset( Zend_Db_Select::WHERE);

        $select->from(array('s' => 'subjects'),array(
            'subid',
            'classifiers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT cl.classifier_id)"),
            'teachers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT t.MID)"),
            'claimant' => new Zend_Db_Expr("MAX(c.SID)"),
            ))
            ->joinLeft(array('st' => 'Students'), 'st.CID = s.subid AND st.MID = '.$userId, array())
            ->joinLeft(array('c' => 'claimants'), 'c.CID = s.subid AND c.MID = '.$userId.' AND c.status='.HM_Role_ClaimantModel::STATUS_NEW, array())
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                's.subid = cl.item_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT, // классификатор уч.курсов
                array()
            )
            ->joinLeft(
                array('t' => 'Teachers'),
                't.CID = s.SUBID',
                array()
            )
            ->where('st.SID IS NULL')
            ->where('s.reg_type = ?', HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN)
            ->where($condition)
            ->group(array('subid', 's.name'))
            ->order('s.name');

        $subjects = $select->query()->fetchAll();

        $ids = array();
        $subid2teacher = $subid2classifier =  array();
        $claimants = $teachers = $classifiers = array();
        foreach($subjects as $subject) {
            $ids[] = $subject['subid'];
            $claimants[$subject['subid']] = $subject['claimant'];
            if($subject['classifiers'])  {
                $subClassifiers  = explode(',', $subject['classifiers']);
                $subid2classifier[$subject['subid']] = $subClassifiers;
                $classifiers = array_merge($classifiers, $subClassifiers);
            }

            if($subject['teachers']) {
                $subTeachers = explode(',', $subject['teachers']);
                $subid2teacher[$subject['subid']] = $subTeachers;
                $teachers = array_merge($teachers, $subTeachers);
            }
        }
        $ids[] = -1;
        $subjects = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $ids));
        $return->list = array();

        foreach ($subjects as $subject) {
            $mobileLessons[$subject->subid] = array();
            $addToList = false;
            $dates = $this->_DatesFormat($subject);
            $image = $subject->getUserIcon();
            if(!$image) {
                $image = $subject->getDefaultIcon();
            }

            $item = array(
                'id' => $subject->subid,
                'name' => $subject->name, //strlen($subject->name)>35 ? mb_substr($subject->name, 0, 35).'...' : $subject->name,
                'price' => $subject->price,                        
                'price_currency' => $subject->price_currency,                        
                'reg_type' => $subject->reg_type,                        
                'categories' => array(),
                'base_color' => $subject->base_color ? $subject->base_color : '555',
                //'url' => 'mobile/subject/list/course/subject_id/'.$subject->subid,
                'rating' => 0,
                'date_start' => strtotime($subject->begin),
                'date_end' => strtotime($subject->end),
                'dates' => str_replace('.'.date('Y'), '', $dates),//$dates,
                'type' => $subject->type,
                'image' => $image,
                'description' => $subject->description,
                'short_description' => $subject->short_description,
                'classifiers' => $subid2classifier[$subject->subid],
                'teachers' => $subid2teacher[$subject->subid],
                'claimant' => $claimants[$subject->subid] ? 1:0

            );
            $return->list[] = $item;
        }

        $classifiers[] = -1;
        $classifiers = $this->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $classifiers), false, null, 'name ASC');//->getList('classifier_id', 'name');
        $return->classifiers = array(array('id'=>0, 'name'=>'Все'));

        foreach($classifiers as $classifier) {
            $return->classifiers[] = array('id'=>$classifier->classifier_id, 'name'=>$classifier->name);
        }
        $teachers[] = -1;
        $teachers = $this->getService('User')->fetchAll(array('MID IN (?)' => $teachers), false, null, 'LastName ASC');//->getList('classifier_id', 'name');
        $return->teachers = [];
        foreach($teachers as $teacher) {
            $return->teachers[] = array(        
                'id'=>$teacher->MID, 
                'name'=>"{$teacher->LastName} {$teacher->FirstName} {$teacher->Patronymic}", 
                'photo'=>$this->getService('User')->getPhoto($teacher->MID)
            );
        }
    }

    public function claimantAction()
    {
        $return = $this->view;
        $userId = $this->getService('User')->getCurrentUserId();
        $subjectId = $this->_getParam('course_id', 0);
        $this->getService('Subject')->assignUser($subjectId, $userId);

        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));

        if ($subject->reg_type == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN && $subject->claimant_process_id != HM_Subject_SubjectModel::APPROVE_NONE) {
            $return->message = sprintf(_('Ваша заявка на учебный курс "%s" успешно отправлена'), $subject->name);
            $return->isRegistered = false;
        } else {
            $return->message = sprintf(_('Вы успешно зарегистрировались на учебный курс "%s"'), $subject->name);
            $return->isRegistered = true;
        }
    }

    public function myAction()
    {
        $return = $this->view;
        $userId = $this->getService('User')->getCurrentUserId();
        if(!$userId) die_error('not_authorized', 'Авторизация не совершена');

//        $ratedSubjects = $this->getService('Subject')->getRatedFreeDateSubjectsIds($userId);
//        $ratedSubjects[]  = -1;

            /* Только микрокурсы
            $where = $this->getService('Subject')->quoteInto(
                array('Student.MID = ?', ' AND self.type = ?'),
                array($userId, HM_Subject_SubjectModel::TYPE_MICRO)
            );
            */
             /* Все курсы */
            $now = date('Y-m-d H:i:s');

            $where = $this->getService('Subject')->quoteInto(
                array('Student.MID = ? AND ', 
//                      'Student.CID NOT IN (?) AND ',
                      '((self.period = ?) OR ',
                      '(self.period = ?', ' AND self.period_restriction_type = ?) OR ',
                      '(self.begin < ?',' AND self.end > ?',' AND self.period = ?',' AND self.period_restriction_type = ?) OR ',
                      '(self.state = ?',' AND self.period = ?',' AND self.period_restriction_type = ?) OR',
                      '(Student.time_registered < ?', ' AND self.period = ?',' AND Student.time_ended_planned > ?))'
                ),
                array($userId, 
//                      $ratedSubjects,
                      HM_Subject_SubjectModel::PERIOD_FREE,
                      HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                      $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                      HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                      $now, HM_Subject_SubjectModel::PERIOD_FIXED, $now
                )
            );

            $subjects = $this->getService('Subject')->fetchAllJoinInner('Student' , $where);
            $return->list = array();

            $mobileLessons = array();

            $cids = array();
            foreach ($subjects as $subject) {
                $cids[] = $subject->subid;
            }
            $cids[] = -1;
            $marks = $this->getService('SubjectMark')->fetchAll(array('mark <> ?' => -1, 'mid = ?' => $userId, 'cid in (?)' => $cids))->getList('cid', 'mark');

            foreach ($subjects as $subject) {
                $cids[] = $subject->subid;
                $mobileLessons[$subject->subid] = array();
                $addToList = true;//false;//Пустые курсы - тоже
                $dates = $this->_DatesFormat($subject);

                $typeIds = array(HM_Event_EventModel::TYPE_POLL, HM_Event_EventModel::TYPE_TEST, HM_Event_EventModel::TYPE_COURSE, HM_Event_EventModel::TYPE_TASK, HM_Event_EventModel::TYPE_RESOURCE);
                $customTypes = $this->getService('Event')->fetchAll(array('tool IN (?)'=>$typeIds))->getList('event_id');
                foreach($customTypes as $customType) {
                    $typeIds[] = -$customType;
                }

                //Занятия
                $lessons = $this->getService('Lesson')->getStudentLessons($userId, $subject->subid);//MODE_FREE

                /** @var HM_Lesson_LessonModel $lesson */
                foreach ($lessons as $lesson) {

                    if(array_search($lesson->typeID, $typeIds)===false) continue;
                    
                    $bBlocked = $skip = false;

                    try {

                        if(!$lesson->isExecutable()) $bBlocked = true;
                        if(!$this->getService('Lesson')->isLaunchConditionSatisfied($lesson->SHEID, $lesson)) $bBlocked = true;
                    } catch (Exception $e) {
                        $bBlocked = true;
//                        continue;//Чтобы выводить тесты, у которых закончились попытки
                    }

                    if ($lesson->has_proctoring) {
                        $bBlocked = true;
                    }

                    $params = explode(';', $lesson->params);
                    $object = false;
                    foreach ($params as $param) {
                        $p = explode('=', $param);
                        if ($p[0] == 'module_id') {
                            $object = $p[1];
                        }
                    }
                    $result = '';
                    if($lesson->V_STATUS!=HM_Scale_Value_ValueModel::VALUE_NA) {
                        switch ($lesson->getScale()) {
                        case HM_Scale_ScaleModel::TYPE_BINARY:
                            if($lesson->V_STATUS==HM_Scale_Value_ValueModel::VALUE_BINARY_ON)
                                $result = '+';
                            break;
                        case HM_Scale_ScaleModel::TYPE_TERNARY:
                            $result = $lesson->V_STATUS==HM_Scale_Value_ValueModel::VALUE_TERNARY_ON ? '+' : '-';
                            break;
                        case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                            $result = $lesson->V_STATUS;
                            break;
                        }
                    }

                    $toc = false;
                    switch($lesson->getType()) {
                        case HM_Event_EventModel::TYPE_TEST: 
                        case HM_Event_EventModel::TYPE_POLL:
//                            $url = "quest/lesson/start/quest_id/{$object}/lesson_id/{$lesson->SHEID}/redirect_url/close_window/advance/1";
                            $url = "quest/lesson/info/quest_id/{$object}/lesson_id/{$lesson->SHEID}/redirect_url/close_window/mobile/1";
                            break;
                        case HM_Event_EventModel::TYPE_COURSE:

                            $course = $this->getService('Course')->find($lesson->getModuleId());
                            if(!$course) continue(2); // Пропускаем цикл foreach

                            $url = 'mobile/subject/list/course/subject_id/'.$subject->subid.'/lesson_id/'.$lesson->SHEID."/mobile/1";
                            $toc = $this->buildTOC($subject->subid, $lesson->SHEID);
                            break;
                        case HM_Event_EventModel::TYPE_RESOURCE:
                            $url = 'subject/lesson/index/subject_id/'.$subject->subid.'/lesson_id/'.$lesson->SHEID."/mobile/1";

                            break;
                    }

                    $addToList = true;
                    $mobileLesson = array(
                                'name' => $lesson->title,
//                                'name' => strlen($lesson->title)>60 ? substr(),
                                'id'  => $lesson->SHEID,
                                'type' => HM_Event_EventModel::convertType($lesson->typeID),
                                'description' => $lesson->descript,
                                'url' => $url,
                                'dates' => $lesson->getDateInfo(),
                                'isfree' => $lesson->isfree,//0||1 //????????????????????????????????????????????????
                                'result' => $result,
                                'blocked' => $bBlocked ? 1: 0,
                                'TOC' => $toc
                            );

                    $mobileLessons[$subject->subid][] = $mobileLesson;
                }


                // Дополнительные материалы
                $subjectResourcesIs = $this->getService('SubjectResource')->fetchAll([
                    'subject_id = ?' => $subject->subid,
                    'subject = ?'    => 'subject'
                ])->getList('resource_id');

                $subjectResourcesIs[] = -1;
                $resources = $this->getService('Resource')->fetchAll([
                    'resource_id in (?)'    => $subjectResourcesIs
                ]);

                foreach($resources as $resource) {
                    $addToList = true;
                    $mobileLesson = array(
                        'name' => $resource->title,
                        'id'  => 'm_'.$resource->resource_id,
                        'type' => HM_Event_EventModel::convertType(HM_Event_EventModel::TYPE_RESOURCE),
                        'description' => '',
                        'url' => '/kbase/resource/index/subject_id/'.$subject->subid.'/resource_id/'.$resource->resource_id."/mobile/1",
                        'dates' => '',
                        'isfree' => 1,
                        'result' => '',
                        'blocked' => 0,
                        'TOC' => false
                            );
                    $mobileLessons[$subject->subid][] = $mobileLesson;
                }

                if ($addToList) {
                    $image = $subject->getUserIcon();
                    if(!$image) {
                        $image = $subject->getDefaultIcon();
                    }

                    $item = array(
                        'id' => $subject->subid,
                        'name' => $subject->name, //strlen($subject->name)>35 ? mb_substr($subject->name, 0, 35).'...' : $subject->name,
                        'categories' => array(),
                        'base_color' => $subject->base_color ? $subject->base_color : '555',
                        //'url' => 'mobile/subject/list/course/subject_id/'.$subject->subid,
                        'rating' => 0,
                        'date_start' => strtotime($subject->begin),
                        'date_end' => strtotime($subject->end),
                        'dates' => str_replace('.'.date('Y'), '', $dates),//$dates,
                        'subject-type' => $subject->type,
                        'image' => $image,
                        'description' => $subject->description,
                        'short_description' => $subject->short_description,
                        'lessons' => $mobileLessons[$subject->subid],
                        'mark' => $marks[$subject->subid]
                    );
                    $return->list[] = $item;
                }

            }

            $return->error = "";
            $return->length = count($this->view->list);
    }


    public function buildTOC($subjectId, $lessonId) 
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
        $courseId = $lesson->getModuleId();
        if(!$courseId) return false;
/*
        $courseObject = $this->getService('Course')->find($courseId)->current(); //MAPS, мб что-то еще... надо понять что такое is_assemble
        if($courseObject->format==HM_Course_CourseModel::FORMAT_FREE && 
            $this->getService('CourseItem')->fetchRow(array('cid = ?' => $courseId, 'is_assemble = ?' => 1))
        ) {
            return false;
        }
*/
        $treeData = $this->getService('CourseItem')->getHmTreeData($courseId);
//        $bTOC = !$this->getService('CourseItem')->isDegeneratedTree($courseId);
        if(!count($treeData) || (count($treeData)==1 && !count($treeData[0]['children']))) return false;

        $outTree = array('title'=>'ZERO LEVEL', 'children'=>array());
        $this->_getTree($treeData, $outTree, $subjectId, $lessonId, $courseId);

        return $outTree['children'];
    }

    public function _getTree($treeData, &$outTree, $subjectId, $lessonId, $courseId)
    {   
        foreach($treeData as $branch){
            $bIsLeaf = !count($branch['children']);
            $bIsResource = count($branch['res_data']);
            $newItem = array('title'=>$branch['title'], 'isLeaf'=>$bIsLeaf, 'children'=>array(), 'iconClass'=>$branch['iconClass']);
            $url = '';
            if($bIsLeaf) {
                if($bIsResource) {
                    $url = 'resource/index/view/subject_id/'.$subjectId.'/lesson_id/'.$lessonId.'/resource_id/'.$branch['res_data']['resource_id'].'';
                } else {
                    $url = "/course/item/view/lesson_id/{$lessonId}/course_id/{$courseId}/item_id/{$branch['sql_data']['oid']}";
                }
            }
            $newItem['url'] = $url;
            $newItem['id'] = $branch['sql_data']['oid'];

            $this->_getTree($branch['children'], $newItem, $subjectId, $lessonId, $courseId);

            $outTree['children'][] = $newItem;
        }
    }


    public function taskAction() 
    {
        $userId = $this->getService('User')->getCurrentUserId();
        if (!$userId) die_error('not_authorized', 'Авторизация не совершена');
        $input = $this->getInput();


        $task = $this->getService('Task')->getTask($input->task_id, $this->getService('User')->getCurrentUserId());
        foreach($task as $i=>$v) {
            $this->view->$i = $v;
        }
    }

    public function taskSaveAction() 
    {
        $input = $this->getInput();
        $data = $input->data;

        try {
            $savedOk = $this->getService('Task')->saveTask($data);
        } catch (Exception $e) {
            $this->die_error('not_saved', 'Ошибка сохранения данных задания.');
        }

        $task = $this->getService('Task')->getTask($data->task_id, $this->getService('User')->getCurrentUserId());
        if(!$task) {
            $this->die_error('not_saved', 'Ошибка сохранения данных задания!');
        }    

        foreach($task as $i=>$v) {
            $this->view->$i = $v;
        }
    }

    public function _DatesFormat($subject)
    {
        switch($subject->period) 
        {
            case HM_Subject_SubjectModel::PERIOD_FREE:
                return 'Не ограничен';
            break;
            case HM_Subject_SubjectModel::PERIOD_FIXED:
                return  _('Дней: '). $subject->longtime;
            break;
            default:
                return  $subject->getBeginForStudent() . '-' .$subject->getEndForStudent();
        }
    }
}
