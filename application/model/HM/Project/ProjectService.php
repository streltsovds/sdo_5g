<?php
class HM_Project_ProjectService extends HM_Service_Abstract
{
    /**
     * кеш занятий пользователя по курсам
     * используется при подсчете статуса и процента прохождения
     * @var array
     */
    private $_userLessonsCache = array();

    private $_projectsColorsCache = null;

    /**
     * Кеш соответствий ID оригинальных сущностей в курсе и их копий
     * @var array
     */
    private $_projectCopyCache = array();

    /**
     * Кеш модераторов конкурсов
     * @var array
     */
    private $_moderatorsProjectsCache = array();

    public function insert($data, $unsetNull = true)
    {
        $data = $this->_prepareData($data);
        $project = null;
        if ($project = parent::insert($data, $unsetNull)) {

            // создаем дефолтную секцию для материалов в своб.доступе
            //$this->getService('Section')->createSection($project->projid);


        }
        return $project;
    }

    public function update($data, $unsetNull = true)
    {
        $data = $this->_prepareData($data);
        $project = parent::update($data, $unsetNull);
        return $project;
    }

    public function updateIcon($projectId, $photo)
    {
        $w = HM_Subject_SubjectModel::THUMB_WIDTH;
        $h = HM_Subject_SubjectModel::THUMB_HEIGHT;

        if($photo->isUploaded()){
            $path = Zend_Registry::get('config')->path->upload->project . $projectId . '.jpg';
            $photo->addFilter('Rename', $path, 'photo', array( 'overwrite' => true));
            unlink($path);
            $photo->receive();
            $img = PhpThumb_Factory::create($path);
            $img->adaptiveResize($w, $h);
	        $img->save($path);
        }
        return true;
    }

    private function _prepareData($data)
    {
        if (isset($data['period']) && ($data['period'] !== '')) {
            switch($data['period']) {
                case HM_Project_ProjectModel::PERIOD_FREE:
                case HM_Project_ProjectModel::PERIOD_FIXED:
                    $today = new HM_Date();
                    $data['begin'] = (string) $today->getDate();
                    //$today->add(1, HM_Date::MONTH);
                    $data['end'] = (string) $today->getDate();
//                 	$data['begin'] = $data['end'] = '';
                    break;
                case HM_Project_ProjectModel::PERIOD_DATES:
// если дата начала и дата окончания совпадают - это значит курс идет один день!!!
//                    if (!empty($data['begin']) && ($data['begin'] == $data['end'])) {
//                        $data['begin'] = $data['end'] = '';
//                    }
//                    $today = new HM_Date();
//                    if (!$data['begin']) {
//                        $data['begin'] = (string) $today->getDate();
//                    }
//                    $today->add(1, HM_Date::MONTH);
//                    if (!$data['end']) {
//                        $data['end'] = (string) $today->getDate();
//                    }
                    if (!empty($data['end'])) {
                        $date = new Zend_Date(strtotime($data['end']));
                        $date->add(1, HM_Date::DAY);
                        $date->sub(1, HM_Date::SECOND);
                        $data['end'] = $date->toString('dd.MM.y H:m:s');
                    }
                    break;
                default:
                    unset($data['period']);
                    break;
            }
        } else {
            unset($data['period']);
        }
        return $data;
    }

    public function delete($projectId)
    {
        //$this->getService('Section')->deleteBy($this->quoteInto('project_id = ?', $projectId));
        $this->getService('Participant')->deleteBy($this->quoteInto('CID = ?', $projectId));
        $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(array('type = ?', ' AND item_id = ?'), array(HM_Programm_Event_EventModel::EVENT_TYPE_PROJECT, $projectId))
        );
        return parent::delete($projectId);
    }

    public function linkRooms($projectId, $rooms)
    {
        $this->unlinkRooms($projectId);
        if (is_array($rooms) && count($rooms)) {
            foreach($rooms as $roomId) {
                if ($roomId > 0) {
                    $this->linkRoom($projectId, $roomId);
                }
            }
        }
        return true;
    }

    public function linkRoom($projectId, $roomId)
    {
        $this->getService('ProjectRoom')->deleteBy(array('cid = ?' => $projectId));
        return $this->getService('ProjectRoom')->insert(
            array(
                'cid' => $projectId,
                'rid'  => $roomId
            )
        );
    }

    public function unlinkRooms($projectId)
    {
        return $this->getService('ProjectRoom')->deleteBy(
            $this->quoteInto('cid = ?', $projectId)
        );
    }

    public function linkClassifiers($projectId, $classifiers)
    {
        $this->getService('Classifier')->unlinkItem($projectId, HM_Classifier_Link_LinkModel::TYPE_PROJECT);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($projectId, HM_Classifier_Link_LinkModel::TYPE_PROJECT, $classifierId);
                }
            }
        }
        return true;
    }

    public function linkClassifier($projectId, $classifierId)
    {
        return $this->getService('ProjectClassifier')->insert(
            array(
                'project_id' => $projectId,
                'classifier_id'  => $classifierId
            )
        );
    }

    public function unlinkClassifiers($projectId)
    {
        return $this->getService('ProjectClassifier')->deleteBy(
            $this->quoteInto('project_id = ?', $projectId)
        );
    }

    public function unlinkCourse($projectId, $courseId)
    {
        return $this->getService('ProjectCourse')->deleteBy(
            $this->quoteInto(array('project_id = ?', ' AND course_id = ?'), array($projectId, $courseId))
        );
    }

    public function linkCourse($projectId, $courseId)
    {
        return $this->getService('ProjectCourse')->insert(
            array(
                'project_id' => $projectId,
                'course_id'  => $courseId
            )
        );
    }

    public function unlinkCourses($projectId)
    {
        return $this->getService('ProjectCourse')->deleteBy(
            $this->quoteInto('project_id = ?', $projectId)
        );
    }

    public function getCourses($projectId, $status = null)
    {
        if (null == $status) {
            return $this->getService('Course')->fetchAllDependenceJoinInner(
                'ProjectAssign',
                $this->quoteInto('ProjectAssign.project_id = ?', $projectId),
                'self.Title'
            );
        } else {
            return $this->getService('Course')->fetchAllDependenceJoinInner(
                'ProjectAssign',
                $this->quoteInto(array('ProjectAssign.project_id = ?', ' AND self.Status = ?'), array($projectId, $status)),
                'self.Title'
            );
        }
    }

    public function getFreeProjects($count = 20, $user_id = null){

/*    	$projects = $this->fetchAllDependenceJoinInner(
    					'Participant',
    					$this->quoteInto(array(
    										'(reg_type = ?',
    										' OR reg_type = ?)',
    										' AND end > ?',
    										' AND registered IS NULL'
    									),
    									array(
    										HM_Project_ProjectModel::REGTYPE_FREE,
    										HM_Project_ProjectModel::REGTYPE_SELF_ASSIGN,
    										$this->getDateTime()
    									))
    	);*/
	    $select = $this->getSelect();
	    if (!$count) $select->distinct();
		$select->from(
						array('s' => 'projects'),
						array('project_id' => 's.projid')
					);
		if($user_id){
			$select->joinLeft(
							array('st' => 'Participants'),
							'st.CID = s.projid AND st.MID = '.$user_id,
							array('registeged' => 'st.registered')
						);
			$select->where('registered IS NULL');
		}
		$select->where($this->quoteInto(
            array('s.reg_type = ?', ' OR s.reg_type = ?'),
            array(HM_Project_ProjectModel::REGTYPE_FREE, HM_Project_ProjectModel::REGTYPE_SELF_ASSIGN)
		));
		$select->where($this->quoteInto(
            array('s.period <> ?', ' OR s.end > ?'),
            array(HM_Project_ProjectModel::PERIOD_DATES, $this->getDateTime())
		));
		if($count) $select->limit($count);

		$tmp = $select->query()->fetchAll();
		$tmp = (is_array($tmp)) ? $tmp : array();

		$free_projects = array();
		foreach ($tmp as $value) {
            $free_projects[] = $value['project_id'];
		}

		return $free_projects;
    }

    public function isCurator($projectId, $userId)
    {
        return $this->getService('Curator')->userIsCurator($userId);
    }

    public function isParticipant($projectId, $userId)
    {
        return $this->getService('Participant')->isUserExists($projectId, $userId);
    }
    public function isModerator($projectId, $userId)
    {
        return $this->getService('Moderator')->isUserExists($projectId, $userId);
    }
    /**
     * Возвращает модели юзеров, присвоенных определенному курсу
     * @param unknown_type $project_id  Id Курса
     * @return multitype:
     */
    public function getAssignedUsers($project_id){
        $collection = $this->getService('User')->fetchAllJoinInner('Participant', 'Participant.CID = '. (int) $project_id);
        return $collection;
    }

    public function getAssignedTeachers($project_id){
        $collection = $this->getService('User')->fetchAllJoinInner('Teacher', 'Teacher.CID = '. (int) $project_id);
        return $collection;
    }

    public function getAssignedGraduated($project_id){
        $collection = $this->getService('User')->fetchAllJoinInner('Graduated', 'Graduated.CID = '. (int) $project_id);
        return $collection;
    }

    public function assignUser($projectId, $participantId)
    {
        $project = $this->getOne($this->find($projectId));
        if ($project) {
            if ($project->claimant_process_id == 0) {
                $this->assignParticipant($projectId, $participantId);
            } else {
                $this->assignClaimant($projectId, $participantId);
            }
        }
    }

    public function assignCuratorPolls($projectId, $participantId)
    {
        $polls = $this->getService('LessonCuratorPoll')->fetchAll(
            $this->quoteInto(
                array($this->quoteIdentifier('all').' = ?', ' AND CID = ?'),
                array('1', $projectId)
            )
        );

        if (count($polls)) {
            foreach($polls as $poll) {
                $poll->getService()->assignParticipants($poll->SHEID, array($participantId), false);
            }
        }
    }

    public function assignGraduated($projectId, $participantId, $status = NULL)
    {
        $result = false;
        $participant = $this->getOne(
            $this->getService('Participant')->fetchAll(
                array(
                    'CID = ?' => $projectId,
                    'MID = ?' => $participantId
                )
            )
        );
        if ($participant) {

        	$certificate = $this->getService('Certificates')->addCertificate($participantId, $projectId);
        	$certificate_id = ($certificate)? $certificate->certificate_id : 0;
            if ($status === NULL) {
                $status = HM_Role_GraduatedModel::STATUS_SUCCESS;
            }
            $result = $this->getService('Graduated')->insert(
                array(
                     'MID'            => $participantId,
                     'CID'            => $projectId,
                     'begin'          => $participant->time_registered,
                     'status'         => (int) $status,
                	 'certificate_id' => $certificate_id)
            );

            $this->getService('Participant')->deleteBy(array('CID = ?'=> $projectId, 'MID = ?' => $participantId));

            // назначение кураторских опросов "всем новым"
            //$this->assignCuratorPolls($projectId, $participantId);
        }
        return $result;
    }

    public function unassignGraduated($projectId, $participantId)
    {
        $project = $this->getOne($this->findDependence(array('Graduated', 'Lesson'), $projectId));
        if ($project) {
            if ($project->isGraduated($participantId)) {
                $this->getService('Graduated')->deleteBy(sprintf("MID = '%d' AND CID = '%d'", $participantId, $projectId));
            }

            $lessons = $project->getLessons();
            if (count($lessons)) {
                foreach($lessons as $lesson) {
                    $lesson->getService()->unassignParticipant($lesson->SHEID, $participantId);
                    /*
                    if (!in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
                        $this->getService('Lesson')->unassignParticipant($lesson->SHEID, $participantId);
                    }
                    if (in_array($lesson->typeID, array_keys(HM_Event_EventModel::getCuratorPollTypes()))) {
                        $this->getService('LessonCuratorPoll')->unassignParticipant($lesson->SHEID, $participantId);
                    }

                     */
                }
            }
        }
    }

    /**
     * Добавляет заявку со статусом HM_Role_ClaimantModel::STATUS_ACCEPTED
     * @param int $projectId
     * @param int $participantId
     */
    public function assignAcceptedClaimant($projectId, $participantId)
    {
        $project = $this->getOne($this->findDependence('Claimant', $projectId));
        if ($project) {
            if (!$project->isClaimant($participantId)) {
                $user = $this->getOne($this->getService('User')->find($participantId));
                $this->getService('Claimant')->insert(
                    array(
                        'MID' => $participantId,
                        'CID' => $projectId,
                        'created' => $this->getDateTime(),
                        'begin' => $this->getDateTime(),
                        'end' => $project->end,
                        'status' => HM_Role_ClaimantModel::STATUS_ACCEPTED,
                        'lastname' => $user->LastName,
                        'firstname' => $user->FirstName,
                        'patronymic' => $user->Patronymic
                    )
                );
            }
        }
    }


    public function assignClaimant($projectId, $participantId)
    {
        $project = $this->getOne($this->findDependence('Claimant', $projectId));
        if ($project) {
            if (!$project->isClaimant($participantId)) {
                $user = $this->getOne($this->getService('User')->find($participantId));
				$lastName      =   $user->LastName;
				$firstName     =   $user->FirstName;
				$patronymic    =   $user->Patronymic;
				$mid           =   $user->MID;
				$mid_external   =   $user->mid_external;		
				//Делаем запрос в БД(Table)`Claimant` и проверяем существует ли такой пользователь
				//если существует, то кладем в переменную dublicated MID пользователя на которого
				//похож, регистрирующийся пользователь - дубликат
				//$dublicated = $this->getService('Claimant')->checkDublicate($lastName, $firstName, $patronymic, $mid, $mid_external);  
                $this->getService('Claimant')->insert(
                    array(
                        'MID' => $participantId,
                        'CID' => $projectId,
                        'created' => $this->getDateTime(),
                        'begin' => $this->getDateTime(),
                        'end' => $project->end
                        //'lastname' => $user->LastName,
                        //'firstname' => $user->FirstName,
                        //'patronymic' => $user->Patronymic,
						//'dublicate' => $dublicated,
						//'mid_external'=> $user->mid_external,
                    )
                );
				$dublicated = $this->getService('Claimant')->updateClaimant();
                // Сообщение администрации
                $messenger = $this->getService('Messenger');
                $messenger->addMessageToChannel(HM_Messenger::SYSTEM_USER_ID,
                                              HM_Messenger::SYSTEM_USER_ID,
                                              HM_Messenger::TEMPLATE_ORDER,
                                              array(
                                                    'project_id' => $projectId,
                                                    'url_user' => Zend_Registry::get('view')->serverUrl(
                                                        Zend_Registry::get('view')->url(array(
                                                            'module' => 'user',
                                                            'controller' => 'edit',
                                                            'action' => 'card',
                                                            'user_id' => $participantId
                                                        ), null, true)
                                                    ),
                                                    'user_login'       => $user->Login,
                                                    'user_name'        => $user->LastName .' '. $user->FirstName .' '. $user->Patronymic,
                                                    'user_lastname'    => $user->LastName,
                                                    'user_firstname'   => $user->FirstName,
                                                    'user_patronymic'  => $user->Patronymic,
                                                    'user_mail'        => $user->EMail,
                                                    'user_phone'       => $user->Phone,
                                                    'project_price'    => $project->price,
                                                    'project_currency' => $project->price_currency
                                              )
                                             );
                /*$messenger->setOptions(
                    HM_Messenger::TEMPLATE_ORDER,
                    array(
                        'project_id' => $projectId,
                        'url_user' => Zend_Registry::get('view')->serverUrl(
                                        Zend_Registry::get('view')->url(array(
                                            'module' => 'user',
                                            'controller' => 'edit',
                                            'action' => 'card',
                                            'user_id' => $participantId
                                        ), null, true)
                                    ),
                        'user_login'       => $user->Login,
                        'user_name'        => $user->LastName .' '. $user->FirstName .' '. $user->Patronymic,
                        'user_lastname'    => $user->LastName,
                        'user_firstname'   => $user->FirstName,
                        'user_patronymic'  => $user->Patronymic,
                        'user_mail'        => $user->EMail,
                        'user_phone'       => $user->Phone,
                        'project_price'    => $project->price,
                        'project_currency' => $project->price_currency
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::SYSTEM_USER_ID);
                */
                // Сообщение пользователю
                $messenger->addMessageToChannel(HM_Messenger::SYSTEM_USER_ID,
                    $participantId,
                    HM_Messenger::TEMPLATE_ORDER_REGGED,
                    array(
                        'project_id' => $projectId
                    )
                );
                /*$messenger->setOptions(
                    HM_Messenger::TEMPLATE_ORDER_REGGED,
                    array(
                        'project_id' => $projectId
                    )
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $participantId);
                */

            }
        }
    }

    public function assignParticipant($projectId, $participantId)
    {
        $project = $this->getOne($this->findDependence(array('Participant', 'Meeting'), $projectId));

        if ($project) {
            if (!$project->isParticipant($participantId)) {

            	$beginPlanned = new HM_Date($project->begin);
                $this->getService('Participant')->insert(
                    array(
                        'MID' => $participantId,
                        'CID' => $projectId,
                        'Registered' => time(),
                        'time_registered' => (($project->period_restriction_type != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) || ($project->state == HM_Project_ProjectModel::STATE_ACTUAL )) ? date('Y-m-d H:i:s') : $beginPlanned->get('Y-MM-dd'),
                    )
                );

                if (count($project->meetings)) {
                    foreach ($project->meetings as $meeting) {
                        if ($meeting->all == 1) {
                            $this->getService('Meeting')->assignParticipant($meeting->meeting_id, $participantId);
                        }
                    }
                }

                // Отправка сообщения о назначении на учебный курс
                $roles = HM_Role_Abstract_RoleModel::getBasicRoles();

                // если курс стартует вручную - заранее емайл не посылаем
                if (($project->period_restriction_type != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) || ($project->state == HM_Project_ProjectModel::STATE_ACTUAL )) {

                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                        HM_Messenger::TEMPLATE_ASSIGN_PROJECT,
                        array(
                            'project_id' => $projectId,
                            'project' => $project->name,
                            'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT]
                        ),
                        'project',
                        $projectId
                    );
                    //$messenger->setIcal($this->getIcal($project, $participantId));

                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $participantId);
                }
            }
        }
    }
    
    public function assignModerator($projectId, $moderatorId)
    {
        $project = $this->getOne($this->findDependence(array('Participant'), $projectId));

        if ($project) {
            if (!$project->isModerator($moderatorId)) {

                $this->getService('Moderator')->insert(
                    array(
                        'user_id'    => $moderatorId,
                        'project_id' => $projectId
                    )
                );
                $roles = HM_Role_Abstract_RoleModel::getBasicRoles();
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ASSIGN_PROJECT,
                    array(
                        'project_id' => $projectId,
                        'project' => $project->name,
                        'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_MODERATOR]
                    ),
                    'project',
                    $projectId
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $moderatorId);
            }
        }
    }

    public function startProjectForParticipant($projectId, $participantId)
    {
        $project = $this->getOne($this->findDependence(array('Participant', /*'Lesson'*/), $projectId));

        if ($project) {
            if ($project->isParticipant($participantId)) {

        		$timeEndedPlanned = new Zend_Date($project->end);

                $this->getService('Participant')->updateWhere(
                    array(
                        'time_registered' => date('Y-m-d H:i:s'),
                    	'end_personal' => $timeEndedPlanned ? $timeEndedPlanned->get('Y-MM-dd') . ' 23:59:59' : null,
                    ), array(
                        'MID = ?' => $participantId,
                        'CID = ?' => $projectId,
                    )
                );


// 	            // assign course lessons
// 	            $lessons = $project->getLessons();
// 	            if (count($lessons)) {
// 	                foreach($lessons as $lesson) {
// 	                    if ($lesson->all && !in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
// 	                        $lesson->getService()->assignParticipant($lesson->SHEID, $participantId);
// 	                    }
// 	                }
// 	            }

                // Отправка сообщения о назначении на учебный курс
                $roles = HM_Role_Abstract_RoleModel::getBasicRoles();

                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ASSIGN_PROJECT,
                    array(
                        'project_id' => $projectId,
                        'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT]
                    ),
                    'project',
                    $projectId
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $participantId);
            }
        }
    }

    public function unassignParticipant($projectId, $participantId)
    {
        $project = $this->getOne($this->findDependence(array('Participant'), $projectId));
        if ($project) {
            if ($project->isParticipant($participantId)) {
                $this->getService('Participant')->deleteBy(sprintf("(MID = '%d' AND CID = '%d') OR (MID = '%d' AND CID = 0)", $participantId, $projectId, $participantId));
            }
        }
    }

    public function unassignModerator($projectId, $moderatorId)
    {
        $project = $this->getOne($this->findDependence(array('Moderator'), $projectId));
        if ($project) {
            if ($project->isModerator($moderatorId)) {
                $this->getService('Moderator')->deleteBy(sprintf("(user_id = '%d' AND project_id = '%d') OR (user_id = '%d' AND project_id = 0)", $moderatorId, $projectId, $moderatorId));
            }
        }
    }

    public function assignTeacher($projectId, $teacherId)
    {
        $teacher = $this->getOne(
            $this->getService('Teacher')->fetchAll(
                $this->quoteInto(
                    array('MID = ?', ' AND CID = ?'),
                    array($teacherId, $projectId)
                )
            )
        );

        if (!$teacher) {
            return $this->getService('Teacher')->insert(
                array('MID' => $teacherId, 'CID' => $projectId)
            );
        }
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('конкурс plural', '%s конкурс', $count), $count);
    }

    /**
	 *  Дата - время последнего обновления содержимого курса.
	 *  Что считаем обновлением содержимого:
	 *   - включение эл.курсов
	 *   - включение инф.ресурсов
	 *   - создание занятий в плане
	 *
	 *  Если дата в диапазоне [-бесконечность; 1год1месяц назад] - 0 баллов;
	 *  [1год1месяц назад - 1месяц назад] - XX балов пропорционально;
	 *  [1месяц назад - сейчас] - 100 баллов;
     */
    static public function calcFreshness($timestamp)
 	{
		if ($timestamp > ($ceil = time() - 2592000)) { // 1месяц назад
			return 100;
		} elseif ($timestamp > ($floor = time() - 31104000)) { // 1год1месяц назад
			return 0;
		} else {
			return 100* ($timestamp - $floor)/($ceil - $floor);
		}
 	}

 	/**
 	 * Возвращает массив типов регистрации с наименованиями
 	 * @return multitype:NULL
 	 */
 	public function getRegTypes()
 	{
 	    return HM_Project_ProjectModel::getRegTypes();
 	}
	/**
 	 * Возвращает наименование типа регистрации
 	 * @return string
 	 */
 	public function getRegType($typeId)
 	{
 	    $arrTypes =  HM_Project_ProjectModel::getRegTypes();
 	    if ( !array_key_exists($typeId, $arrTypes) ) {
 	        return '';
 	    }
 	    return $arrTypes[$typeId];
 	}

    public function copyClassifiers($fromProjectId, $toProjectId)
    {
        $classifiers = $this->getService('ClassifierLink')->fetchAll(
            $this->quoteInto(
                array('item_id = ?', ' AND type = ?'),
                array($fromProjectId, HM_Classifier_Link_LinkModel::TYPE_PROJECT)
            )
        );

        if (count($classifiers)) {
            $this->linkClassifiers($toProjectId, $classifiers->getList('classifier_id', 'classifier_id'));
        }
    }

    public function copyExercises($fromProjectId, $toProjectId)
    {
        $links = $this->getService('ProjectExercise')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                $link->project_id = $toProjectId;
                $this->getService('ProjectExercise')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copyQuizzes($fromProjectId, $toProjectId)
    {

        $pollsLinks = array();

        $polls = $this->getService('Poll')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($polls)) {
            foreach($polls as $poll) {
                $newPoll = $this->getService('Poll')->copy($poll, $toProjectId);
                if ($newPoll) {
                    $pollsLinks[$poll->quiz_id] = $newPoll->quiz_id;
                    $this->_projectCopyCache[HM_Event_EventModel::TYPE_POLL][$poll->quiz_id] = $newPoll->quiz_id;
                }
            }
        }

        $links = $this->getService('ProjectPoll')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                $link->project_id = $toProjectId;
                if (isset($pollsLinks[$link->quiz_id])) {
                    $link->quiz_id = $pollsLinks[$link->quiz_id];
                }

                $this->getService('ProjectPoll')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copySections($fromProjectId, $toProjectId)
    {
        $sections = $this->getService('Section')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        $this->getService('Section')->deleteBy(
            $this->quoteInto('project_id = ?', $toProjectId)
        );

        if (count($sections)) {
            foreach($sections as $section) {
                $newSection = $this->getService('Section')->copy($section, $toProjectId);
                if ($newSection) {
                    $this->_projectCopyCache['sections'][$section->section_id] = $newSection->section_id;
                }
            }
        }
    }

    public function copyResources($fromProjectId, $toProjectId)
    {
        $resourcesLinks = array();

        $resources = $this->getService('Resource')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($resources)) {
            foreach($resources as $resource) {
                $newResource = $this->getService('Resource')->copy($resource, $toProjectId);
                if ($newResource) {
                    $resourcesLinks[$resource->resource_id] = $newResource->resource_id;
                    $this->_projectCopyCache[HM_Event_EventModel::TYPE_RESOURCE][$resource->resource_id] = $newResource->resource_id;
                }
            }
        }

        $links = $this->getService('ProjectResource')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                if (isset($resourcesLinks[$link->resource_id])) {
                    continue;
                }

                $link->project_id = $toProjectId;
                $this->getService('ProjectResource')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copyTasks($fromProjectId, $toProjectId)
    {
        $tasksLinks = array();

        $tasks = $this->getService('Task')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($tasks)) {
            foreach($tasks as $task) {
                $newTask = $this->getService('Task')->copy($task, $toProjectId);
                if ($newTask) {
                    $tasksLinks[$task->task_id] = $newTask->task_id;
                    $this->_projectCopyCache[HM_Event_EventModel::TYPE_TASK][$task->task_id] = $newTask->task_id;
                }
            }
        }

        $links = $this->getService('ProjectTask')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                $link->project_id = $toProjectId;
                if (isset($tasksLinks[$link->task_id])) {
                    $link->task_id = $tasksLinks[$link->task_id];
                }

                $this->getService('ProjectTask')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copyTests($fromProjectId, $toProjectId)
    {
        $testsLinks = array();

        $tests = $this->getService('TestAbstract')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($tests)) {
            foreach($tests as $test) {
                $newTest = $this->getService('TestAbstract')->copy($test, $toProjectId);
                if ($newTest) {
                    $testsLinks[$test->test_id] = $newTest->test_id;
                    $this->_projectCopyCache[HM_Event_EventModel::TYPE_TEST][$test->test_id] = $newTest->test_id;
                }
            }
        }

        $links = $this->getService('ProjectTest')->fetchAll(
            $this->quoteInto('project_id = ?', $fromProjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                $link->project_id = $toProjectId;
                if (isset($testsLinks[$link->test_id])) {
                    $link->test_id = $testsLinks[$link->test_id];
                }

                $this->getService('ProjectTest')->insert(
                    $link->getValues()
                );
            }
        }
    }


    public function copyLessons($fromProjectId, $toProjectId)
    {
        // копируем только то, что можно скопировать (отн.даты и без ограничений); здесь же псевдо-занятия для своб.доступа
        $lessons = $this->getService('Lesson')->fetchAll(
            $this->quoteInto(
                array('CID = ?', ' AND timetype IN (?)'),
                array($fromProjectId, new Zend_Db_Expr(implode(',', array(
                    HM_Lesson_LessonModel::TIMETYPE_FREE,
                    HM_Lesson_LessonModel::TIMETYPE_RELATIVE,
                ))))
            )
        );
        if (count($lessons)) {
            $lessonsLink = array();
            foreach($lessons as $lesson) {

                $lessonID = $lesson->SHEID;
                unset($lesson->SHEID);
                //$lesson->teacher = 0; //#10586
                $lesson->CID = $toProjectId;
                //$lesson->section_id = isset($this->_projectCopyCache['sections'][$lesson->section_id]) ? $this->_projectCopyCache['sections'][$lesson->section_id] : null;

                // привязываем занятие к новым сущностям
                $params = $lesson->getParams();
                $type = ($lesson->typeID >= 0)? $lesson->typeID : $lesson->tool;
                if ( isset($params['module_id']) && isset($this->_projectCopyCache[$type][$params['module_id']]) ) {
                    $params['module_id'] = $this->_projectCopyCache[$type][$params['module_id']];
                    $lesson->setParams($params);
                }

                $newLesson = $this->getService('Lesson')->insert($lesson->getValues());
                $lessonsLink[$lessonID] = $newLesson->SHEID;
                //для опросов, тасков и тестов необходимо дублировать записи в таблице test
                if (in_array($type/*$newLesson->typeID*/,
                    array(
                        HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER,
                        HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT,
                        HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_TEACHER,
                        HM_Event_EventModel::TYPE_POLL,
                        HM_Event_EventModel::TYPE_TASK,
                        HM_Event_EventModel::TYPE_TEST))
                ) {

                    $test = $this->getOne($this->getService('Test')->fetchAll(
                        $this->getService('Test')->quoteInto(
                            array('lesson_id = ?'),
                            array($lessonID)
                        )
                    ));

                    if ($test) {
                        $newType = ($newLesson->typeID >= 0)? $newLesson->typeID : $newLesson->tool;
                        if ($this->_projectCopyCache[$newType][$test->test_id]) {
                            $test->test_id = $this->_projectCopyCache[$newType][$test->test_id];
                        }
                        $test->lesson_id = $newLesson->SHEID;
                        $testVals = $test->getValues();
                        unset($testVals['tid']);
                        $newTest = $this->getService('Test')->insert($testVals);
                        if ($newTest) {
                            $params['module_id'] = $newTest->tid;
                            $newLesson->setParams($params);
                            $this->getService('Lesson')->update($newLesson->getValues());
                        }
                    }
                }
            }

            // обновление связей между новыми занятиями (например, условие выполнения)
            $newLessons = $this->getService('Lesson')->fetchAll(array('CID=?' => $toProjectId));
            if ( count($newLessons) && count($lessonsLink) ) {
                foreach($newLessons as $newLesson) {
                    if ($newLesson->cond_sheid && isset($lessonsLink[$newLesson->cond_sheid]) ) {
                        $newLesson->cond_sheid = $lessonsLink[$newLesson->cond_sheid];
                        $this->getService('Lesson')->update($newLesson->getValues());
                    }
                }
            }
        }
    }

    public function copyElements($oldId, $newId)
    {
        $this->copySections($oldId, $newId);

        $this->_projectCopyCache[HM_Event_EventModel::TYPE_COURSE] = $this->getService('ProjectCourse')->copy($oldId, $newId);
        $this->copyClassifiers($oldId, $newId);
        //$this->copyExercises($projectId, $newProject->projid);
        $this->copyQuizzes($oldId, $newId);
        $this->copyResources($oldId, $newId);
        $this->copyTasks($oldId, $newId);
        $this->copyTests($oldId, $newId);
        $this->copyLessons($oldId, $newId);

    }

    public function copy($projectId)
    {
        if ($projectId) {
            $project = $this->getOne($this->find($projectId));
            if ($project) {
                $project->name = sprintf(_('%s (Копия)'), $project->name);
                $project->external_id = '';
                unset($project->projid);


                $values = $project->getValues();

                if($values['end'] !=''){
                    list($date, $time) = explode(' ', $values['end']);
                    $values['end'] = $date;
                }

                $newProject = $this->insert($values);

                if ($newProject) {
                    $this->copyElements($projectId, $newProject->projid);
                }

                $teachers = $this->getService('Teacher')->fetchAll(
                    $this->quoteInto('CID = ?', $projectId)
                );

                if (count($teachers)) {
                    foreach($teachers as $teacher) {
                        $this->assignTeacher($newProject->projid, $teacher->MID);
                    }
                }
/*                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
                    $this->assignTeacher($newProject->projid, $this->getService('User')->getCurrentUserId());
                }*/

                return $newProject;
            }
        }

        return false;
    }

    public function getSessions($projectId)
    {
        $sessions = array();
        if ($project = $this->find($projectId)->current()) {
            if ($project->base == HM_Project_ProjectModel::BASETYPE_BASE) {
                $sessions = $this->fetchAll(array('base_id = ?' => $projectId));
            }
        }
        return $sessions;
    }

    /**
     * Возвращает список занятий на оценку пользователя по курсу
     * результат кешируется
     * @param $projectID
     * @param $userID
     * @return mixed
     */
    public function getUserVedomostLessons($projectID,$userID)
    {
        if (!isset($this->_userLessonsCache[$projectID][$userID])) {
            $this->_userLessonsCache[$projectID][$userID] = $this->getService('LessonAssign')
                ->fetchAllDependenceJoinInner(
                'Lesson',
                $this->getService('Lesson')->quoteInto(array('Lesson.CID  = ?', ' AND Lesson.vedomost = ?', ' AND self.MID = ?'), array($projectID, 1, $userID))
            );
        }
        return $this->_userLessonsCache[$projectID][$userID];
    }

    /**
     * Возвращает среднюю оценку прохождения пользователем занятий курса
     * @param HM_Project_ProjectModel | int $project - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return int
     */
    public function getUserMeanScore($project,$userID)
    {
        $projectID = ($project instanceof HM_Model_Abstract)? $project->projid : (int) $project;
        $lessons = $this->getUserVedomostLessons($projectID,$userID);
        $amount = count($lessons);
        $total = 0;
        foreach($lessons as $lesson){
            if($lesson->V_STATUS != -1){
                $total+= $lesson->V_STATUS;
            }
        }
        if ($amount) {
            $total = (ceil($total / $amount) <= 100) ? ceil($total / $amount) : 100;
        } else {
            $total = 0;
        }
        return $total;
    }

    /**
     * Возвращает процент прохождения пользователем курса
     * @param HM_Project_ProjectModel | int $project - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return int
     */
    public function getUserProgress($project,$userID)
    {
        $projectID = ($project instanceof HM_Model_Abstract)? $project->projid : (int) $project;
        $scoreLessonsTotal = $this->getService('Lesson')
            ->countAllDependenceJoinInner(
            'Assign',
            $this->getService('Lesson')
                 ->quoteInto(array('CID = ? AND vedomost = 1 ', ' AND MID = ?', ' AND isfree = ?'), array($projectID, $userID, HM_Lesson_LessonModel::MODE_PLAN))
        );

        $scoreLessonsScored = $this->getService('Lesson')
            ->countAllDependenceJoinInner(
            'Assign',
            $this->getService('Lesson')
                 ->quoteInto(array('CID = ? AND vedomost = 1 ', ' AND MID = ? AND V_STATUS > -1', ' AND isfree = ?'), array($projectID, $userID, HM_Lesson_LessonModel::MODE_PLAN))
        );

        return ($scoreLessonsTotal)? floor(($scoreLessonsScored / $scoreLessonsTotal) * 100) : 0;
    }

    /**
     * Функция возвращает TRUE в случае, если все занятия пользователя $userID по курсу $project исмеют статус "выполнено"
     * @param HM_Project_ProjectModel | int $project - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return bool
     */
    public function isAllLessonsDone($project,$userID)
    {
        $projectID = ($project instanceof HM_Model_Abstract)? $project->projid : (int) $project;
        $lessons = $this->getUserVedomostLessons($projectID,$userID);
        $finish = TRUE;
        foreach($lessons as $lesson){
            if ( $lesson->V_DONE != HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE ) {
                $finish = FALSE;
            }
        }
        return $finish;
    }

    public function generateColor()
    {
        $rand = array(0,1,2);
        shuffle($rand);
        $c[0] = rand(130,200);
        $c[1] = rand(130,200);
        $c[2] = 130;


        $color_r = $c[$rand[0]];
        $color_g = $c[$rand[1]];
        $color_b = $c[$rand[2]];
        return sprintf("%02x%02x%02x",$color_r,$color_g,$color_b);
    }

    public function getProjectColor($projid)
    {
        if ($this->_projectsColorsCache === null) {
            $this->_projectsColorsCache = $this->fetchAll()->getList('projid','base_color');
        }
        if ($projid && array_key_exists($projid,$this->_projectsColorsCache)) {
            return $this->_projectsColorsCache[$projid];
        }

        return '';
    }

    public function getCalendarSource($source, $defaultColor = '0000ff', $inText = false, $forUsers = null)
    {
        if (!$source instanceof HM_Collection) return '';

        $events        = array();
        $eventsSources = array();

        $forUsers = (array) $forUsers;

        foreach ( $source as $event ) {
            if (!$event || !$event->begin || !$event->end) continue;

            $start   = new HM_Date($event->begin);
            $end     = new HM_Date($event->end);
            $data = array(
                'id'    => $event->projid,
                'title' => $event->name,
                'start' => ($inText)? $start->toString("YYYY-MM-dd") : $start->getTimestamp(),
                'end'   => ($inText)? $end->toString("YYYY-MM-dd") : $end->getTimestamp(),
                'color' => "#$color",
                'textColor' => (lum($color) < 130) ? '#fff' : '#000'
            );

            if (count($event->teachers)) {
                $teacher = $event->teachers->current();

                if (count($forUsers) && !in_array($teacher->MID, $forUsers)) {
                    continue;
                }
                $teachers[] = $teacher->getName();

                array_unique($teachers);
                $data['title'] .= ' ' . _('Тьюторы') . ': ' . implode(', ', $teachers);
                unset($teachers);
            } elseif (count($forUsers)) {
                continue;
            }

            $events[] = $data;
        }

        return $events;
    }

	// ВНИМАНИЕ! создает сессию с ручным стартом
    public function createSession($baseId, $appentTitle = false)
    {
        if (!$appentTitle) $appentTitle = _('сессия');
        if ($base = $this->getOne($this->find($baseId))) {
            if ($base->base != HM_Project_ProjectModel::BASETYPE_SESSION) {

                if ($base->base == HM_Project_ProjectModel::BASETYPE_PRACTICE ) {
                    $changes = array(
                        'base'      => HM_Project_ProjectModel::BASETYPE_BASE,
                        'period'    => HM_Project_ProjectModel::PERIOD_FREE,
                        'claimant_process_id' => array_shift(HM_Project_ProjectModel::getTrainingProcessIds()),
                    );
                    $this->getService('Project')->updateWhere($changes, array('projid = ?' => $baseId));
                    $this->getService('Project')->unlinkRooms($baseId);
                }

                $data = $base->getValues();
                $data['name'] = sprintf(_('%s (%s)'), $base->name, $appentTitle);
                $data['base'] = HM_Project_ProjectModel::BASETYPE_SESSION;
                $data['begin'] = date('Y-m-d');
                $data['end'] = date('Y-m-d') . ' 23:59:59';
                $data['period'] = HM_Project_ProjectModel::PERIOD_DATES;
                $data['period_restriction_type'] = HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL;
                $data['base_id'] = $baseId;
                unset($data['projid']);
                $session = $this->insert($data);

                try {
                    $this->getService('Project')->copyElements($baseId, $session->projid);
                } catch (HM_Exception $e) {
                    // что-то не скопировалось..(
                }
                return $session;
            }
        }
        return false;
    }


    public function setDefaultUri($uri, $projectId)
    {
        $this->updateWhere(array('default_uri' => urldecode($uri)), array('projid = ?' => $projectId));
    }

    public function getDefaultUri($projectId)
    {
        $project = $this->find($projectId)->current();
        if ($project && !empty($project->default_uri) && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

                // dirty hack
                $uri = str_replace(array(
                    'lesson/list/index',
                ), array(
                    'lesson/list/my',
                ), $project->default_uri);

                return $uri;

        } else {
            $view = Zend_Registry::get('view');
            return $view->url(array('module' => 'project', 'controller' => 'index', 'action' => 'card', 'project_id' => $projectId));
        }
    }

    public function hasOnlyFreeLessons($projectId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(array(
            'CID = ?' => $projectId,
            'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN
        ));
        return !count($lessons);
    }

    public function getIcal(HM_Project_ProjectModel $project, $participantId = 0)
    {
        // create and set icalendar object
        $calendar = new HM_Ical_Calendar();
        $calendar->addTimezone(HM_Ical_Timezone::fromTimezoneId(Zend_Registry::get('config')->timezone->default));
        $calendar->properties()->add(new HM_Ical_Property('METHOD', HM_Ical_Property_Value_Text::fromString('REQUEST')));

        $event = new HM_Ical_Event();
        $event->properties()->add(new HM_Ical_Property('UID', HM_Ical_Property_Value_Text::fromString(md5('project_'.$project->projid.time()))));
        $event->properties()->add(new HM_Ical_Property('SUMMARY', HM_Ical_Property_Value_Text::fromString($project->name)));
        $event->properties()->add(new HM_Ical_Property('ORGANIZER', HM_Ical_Property_Value_Text::fromString('MAILTO:'.$this->getService('Option')->getOption('dekanEMail'))));

        //$event->properties()->add(new HM_Ical_Property('LOCATION', HM_Ical_Property_Value_Text::fromString('')));
        //$event->properties()->add(new HM_Ical_Property('SEQUENCE', HM_Ical_Property_Value_Text::fromString('0')));
        //$event->properties()->add(new HM_Ical_Property('TRANSP', HM_Ical_Property_Value_Text::fromString('OPAQUE')));
        //$event->properties()->add(new HM_Ical_Property('CLASS', HM_Ical_Property_Value_Text::fromString('PUBLIC')));

        if ($project->begin) {
            $start = new HM_Date($project->begin);
        } elseif ($project->begin) {
            $start = new HM_Date($project->begin);
        }

        if ($project->end) {
            $end = new HM_Date($project->end);
        } elseif ($project->end) {
            $end = new HM_Date($project->end);
        }

        if ($participantId) {
            $participant = $this->getOne(
                $this->getService('Participant')->fetchAll(
                    $this->quoteInto(
                        array('MID = ?', ' AND CID = ?'),
                        array($participantId, $project->projid)
                    )
                )
            );

            if ($participant) {
                if ($participant->time_registered) {
                    $start = new HM_Date($participant->time_registered);
                }

                if ($participant->end_personal) {
                    $end = new HM_Date($participant->end_personal);
                }
            }
        }

        $start->setHour(0)->setMinute(0)->setSecond(0);
        $end->setHour(23)->setMinute(59)->setSecond(59);

        $event->properties()->add(new HM_Ical_Property('DTSTART', HM_Ical_Property_Value_DateTime::fromString($start->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('DTEND', HM_Ical_Property_Value_DateTime::fromString($end->toString('YYYYMMddTHHmmss'))));

        $now = new HM_Date();
        $event->properties()->add(new HM_Ical_Property('DTSTAMP', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('CREATED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('LAST-MODIFIED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $description = $project->name;

        $collection = $this->getTeachers($project->projid);

        $teachers = array();
        if (count($collection)) {
            foreach($collection as $item) {
                $teachers[$item->MID] = $item->getName();
            }
        }

        if (count($teachers)) {
            $description .= ', '.sprintf(_('Тьюторы: %s'), join(', ', $teachers));
        }

        $event->properties()->add(new HM_Ical_Property('DESCRIPTION', HM_Ical_Property_Value_Text::fromString($description)));

        $calendar->addEvent($event);
        return $calendar;
    }

    public function getTeachers($projectId)
    {
        return $this->getService('User')->fetchAllJoinInner('Teacher', $this->quoteInto('Teacher.CID = ?', $projectId));
    }

    public function getModerators($projectId){
        if (!isset($this->_moderatorsProjectsCache[$projectId])){
            $result= $this->getService('Project')->fetchAllManyToMany('User','Moderator',array('projid = ?'=>$projectId));
            if ($result){
                foreach($result as $project){
                    $this->_moderatorsProjectsCache += array($project->projid=>$project->users);

                }
            }
        }
        return $this->_moderatorsProjectsCache[$projectId];
    }

    public function isGraduated($projectId, $userId)
    {
        return false;
    }

    public function assignParticipantRole($roleId, $projectId, $participantId)
    {
        $this->getService('Participant')->updateWhere(
            array(
                'project_role' => $roleId
            ),
            array(
                'MID = ?' => $participantId,
                'CID = ?' => $projectId
            )
        );
    }

    public function unassignParticipantRole($projectId, $participantId)
    {
        $this->getService('Participant')->updateWhere(
            array(
                'project_role' => HM_Role_ParticipantModel::ROLE_PARTICIPANT
            ),
            array(
                'MID = ?' => $participantId,
                'CID = ?' => $projectId
            )
        );
    }
}