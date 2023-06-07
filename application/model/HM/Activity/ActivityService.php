<?php
class HM_Activity_ActivityService extends HM_Service_Abstract
{

    protected $_isIndexable = false;

    /**
     * @var HM_Search_Indexer_Activity
     */
    private $_indexer = null;

    /**
     * @var HM_Activity_Cabinet_CabinetModel
     */
    protected $_cabinet = null;

    public function __construct($mapperClass = null, $modelClass = null)
    {
        parent::__construct($mapperClass, $modelClass);
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->initializeActivityCabinet(get_class($this), $request->getParam('subject', ''), $request->getParam('subject_id', 0), $request->getParam('lesson_id', 0));
    }

    public function initializeActivityCabinet($activityName, $activitySubjectName, $activitySubjectId, $activityLessonId = 0)
    {
        if ($activityLessonId) {
            $activitySubjectName = 'subject';
        }

        $this->_cabinet = new HM_Activity_Cabinet_CabinetModel(
            array(
                'activity_name' => $activityName,
                'subject_name' => $activitySubjectName,
                'subject_id' => $activitySubjectId,
                'lesson_id' => $activityLessonId
            )
        );
    }

    public function isIndexable()
    {
        return $this->_isIndexable;
    }

    public function getCabinet(){
        return $this->_cabinet;
    }

    public function indexActivityItem(HM_Activity_Search_Document $doc)
    {
        if ($this->isIndexable()) {
            if (null == $this->_indexer) {
                $this->_indexer = new HM_Search_Indexer_Activity();
            }

            $doc->addField(Zend_Search_Lucene_Field::Keyword('document_activity_name', strtolower($this->_cabinet->getActivityName())));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('document_activity_subject_name', strtolower($this->_cabinet->getActivitySubjectName())));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('document_activity_subject_id', strtolower($this->_cabinet->getActivitySubjectId())));

            return $this->_indexer->insert($doc);
        }
        return true;
    }

    public function isActivityUser($userId, $userRole)
    {

        if ($this->getService('Acl')->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $userRole = HM_Role_Abstract_RoleModel::ROLE_ENDUSER;
        }

        if ($userId) {
            $select = $this->getActivityUsersSelect(false, false);
            $select->where('t1.MID = ?', $userId);
            $select->where('r.role LIKE ?', '%' . $userRole . '%');
            $select->order('fio');
            $stmt = $select->query();
            return count($stmt->fetchAll());
        }

        return 0;
//        return $stmt->rowCount();
    }

    /**
     * @param  string $activitySubjectName
     * @param  int $activitySubjectId
     * @return HM_Collection
     */
    public function getActivityUsers($onlyModerator = false, $onlyCurrentUser = false)
    {
        $collection = new HM_Collection(array(), 'HM_User_UserModel');
        $subSelect = $this->getActivityUsersSelect($onlyModerator, false);
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array('p.*'))
                ->joinInner(array('s' => $subSelect), 'p.MID = s.MID', array());
        
        if ($onlyCurrentUser) {
            $select->where($this->quoteInto('p.MID = ?', $this->getService('User')->getCurrentUserId()));
            $select->order('p.LastName');
        }
              
        $stmt = $select->query();
        $result = $stmt->fetchAll();
        if (count($result)) {
            foreach($result as $data) {

// цикл по _всем_ юзерам с случае глобального кабинета => проблема с быстродействием
// решил на уровне БД в getActivityUsersSelect(false, false)
               
//                 $isModerator = false;
//                 if (isset($data['role'])) {
//                     $roles = explode(',', $data['role']);
//                     if (count($roles)) {
//                         foreach($roles as $role) {
//                             $isModerator = $this->isUserActivityModerator($data['MID'], $role);
//                             if ($isModerator) break;
//                         }
//                     }
//                 }
//                 unset($data['role']);
//                 $data['isPotentialModerator'] = $isModerator;
//                 /**
//                  * Чтобы пользователь мог видеть свои папки
//                  * при постоянном фильтре (только модераторы)
//                  */
//                 if(($isModerator == false && $onlyModerator == true) && ($this->getService('User')->getCurrentUserId() != $data['MID'])) continue;

                $collection[count($collection)] = new HM_User_UserModel($data);
            }
        }

        return $collection;

    }

    public function getActivityProjectUsersSelect($projectId)
    {
        $select = $this->getService('Activity')->getSelect();
        $select->from(array('r'=>'activities'),array(
                'r.MID',
                'fio'    => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                't1.Phone',
                't1.EMail',
                'role' =>new Zend_Db_Expr("GROUP_CONCAT(r.role)"),))
            ->joinInner(array('t1' => 'People'),'r.mid = t1.MID',array());
        $select->where('(subject_id = ? OR subject_id = 0)AND subject_name = \'project\'',$projectId);
        $select->group(array('r.MID','t1.LastName','t1.FirstName','t1.Patronymic','t1.Phone','t1.Email'));
        return $select;
    }

    /**
     * @param  $onlyModerator
     * @param  $customModerators
     *
     * @return Zend_Db_Table_Select
     * @throws Zend_Db_Select_Exception
     */
    public function getActivityUsersSelect($onlyModerator = false)
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $select = $this->getService('User')->getSelect();
        if ($this->_cabinet->getActivityLessonId() > 0) {

            $select1 = clone $select;
            $select2 = clone $select;

            $subSelect = clone $select;

            $select1
                ->from(
                    array('t1' => 'People'),
                    array(
                        't1.MID',
                        'fio'    => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                        't1.Phone',
                        't1.Fax',
                        't1.Gender',
                        't1.EMail'))
                ->joinInner(
                    array('si' => 'scheduleID'),
                    't1.MID = si.MID',
                    array()
                )->where('si.SHEID = ?', $this->_cabinet->getActivityLessonId());

            $select2
                ->from(
                    'People',
                    array(
                        'People.MID',
                        'fio'    => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(People.LastName, ' ') , People.FirstName), ' '), People.Patronymic)"),
                        'People.Phone',
                        'People.Fax',
                        'People.Gender',
                        'People.EMail'))
                ->joinInner(
                    'Teachers',
                    'Teachers.MID = People.MID',
                    array()
                )->where('Teachers.CID = ?', $this->_cabinet->getActivitySubjectId());

            $subSelect->union(array($select1, $select2), Zend_Db_Select::SQL_UNION);

            $select->from(array('t1' => $subSelect),
                array(
                    't1.MID',
                    't1.fio',
                    't1.Phone',
                    't1.Fax',
                    't1.Gender',
                    't1.EMail')
                )->joinLeft(
                    array( 'r' =>'roles'),
                    'r.mid = t1.MID',
                    array(
                      'role' => 'r.role'
                    )
                );
//                ->order('t1.LastName');

            return $select;
        }

        $activitySubjectName = $this->_cabinet->getActivitySubjectName();

        switch(strtolower($activitySubjectName)) {
            case 'course':
            case 'resource':
            case 'subject':
            case 'project':

                $fields = array(
                        't1.MID',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    );

                $isModerator = $this->isUserActivityPotentialModerator($currentUserId);

                if ($isModerator || !$this->getService('Option')->getOption('disable_contacts')) {
                     $fields[] = 't1.Phone';
                     $fields[] = 't1.EMail';
                }

                $select->from(array('t1' => 'People'), $fields)
                    ->join(array('r' =>'activities'),
                            'r.mid = t1.MID',
                            array('role' => new Zend_Db_Expr('GROUP_CONCAT(r.role)')
                        ));

                $activitySubjectId = $this->_cabinet->getActivitySubjectId();
                if ($activitySubjectId > 0) {
                    $whereSubject = $this->quoteInto(array(
                            'r.subject_name = ? AND ',
                            '(r.subject_id = ? OR r.subject_id = 0)',
                        ), array(
                            $activitySubjectName,
                            $activitySubjectId,
                        ));
                    $select->where($whereSubject);
                } else {
                    $onlyModerator = true;
                }

                $select->group(array(
                        't1.MID',
                        't1.LastName',
                        't1.FirstName',
                        't1.Patronymic',
                        't1.Phone',
                        't1.Fax',
                        't1.Gender',
                        't1.EMail'
                ));
//                    ->order('t1.LastName');
                break;

            default:
                $isPotentialModerator = $this->quoteInto(
                    array(
                        'CASE WHEN (r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ? OR ',
                        'r.role LIKE ?) THEN 1 ELSE 0 END',
                    ),
                    array(
                        '%' . HM_Role_Abstract_RoleModel::ROLE_MANAGER . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_CURATOR . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_DEAN . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_ATMANAGER . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_HR . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN . '%',
                        '%' . HM_Role_Abstract_RoleModel::ROLE_ADMIN . '%',
                    )
                );

                $select->from(array('t1' => 'People'),
                        array(
                            't1.MID',
                            'fio'    => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                            't1.Phone',
                            't1.Fax',
                            't1.Gender',
                            't1.EMail',
                            'isPotentialModerator' => new Zend_Db_Expr($isPotentialModerator)
                        ))
                    ->joinLeft(array( 'r' =>'roles'),
                            'r.mid = t1.MID',
                            array(
                                'role' => 'r.role'
                            )
                        );
//                    ->order('t1.LastName');
        }

        if ($onlyModerator) {
            $condition = $this->quoteInto(array(
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ? OR ',
                'r.role LIKE ?',
            ), array(
                '%' . HM_Role_Abstract_RoleModel::ROLE_MANAGER . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_CURATOR . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_DEAN . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_ATMANAGER . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_HR . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN . '%',
                '%' . HM_Role_Abstract_RoleModel::ROLE_ADMIN . '%',
            ));
            $select->where($condition);
        }

        return $select;
    }

    public function isUserActivityPotentialModerator($userId)
    {
        if (($this->_cabinet->getActivityLessonId() > 0) &&($this->_cabinet->getActivitySubjectName()!='project')) {
            return $this->getService('Lesson')->isTeacher($this->_cabinet->getActivityLessonId(), $userId);
        }

        switch(strtolower($this->_cabinet->getActivitySubjectName())) {
            case 'subject':
                return $this->getService('Subject')->isTeacher($this->_cabinet->getActivitySubjectId(), $userId);
                break;
            case 'project':
                return $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_CURATOR) ||
                	$this->getService('Project')->isModerator($this->_cabinet->getActivitySubjectId(), $userId);
                break;
            case 'course':
                return $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_DEAN);
                break;
            case 'resource':
                return $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_MANAGER);
                break;
            default:
                return ($this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_ADMIN)
                        || $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_DEAN)
                        || $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)
                        ||$this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_CURATOR)
                        || $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_HR));
        }
    }

    // радикально упростил относительно 4.x
    public function isUserActivityModerator($userId, $userRole)
    {
        return in_array($userRole, $this->getActivityModeratorRoles());
    }

    public function isCurrentUserActivityModerator()
    {
        return $this->isUserActivityModerator($this->getService('User')->getCurrentUserId(), $this->getService('User')->getCurrentUserRole());
    }

    /**
     * Добавить комментарий
     *
     * @return HM_Comment_CommentModel
     */
    public function insertActivityComment(HM_Comment_CommentModel $comment)
    {
        return $this->getService('Comment')->insert(
            array(
                'activity_name' => $this->_cabinet->getActivityName(),
                'subject_name' => $this->_cabinet->getActivitySubjectName(),
                'subject_id' => $this->_cabinet->getActivitySubjectId(),
                'user_id' => $comment->user_id,
                'item_id' => $comment->item_id,
                'message' => $comment->message,
                'created' => $this->getDateTime(),
                'updated' => $this->getDateTime()
            )
        );
    }

    public function updateActivityComment($id, $message) {
        return $this->getService('Comment')->updateWhere(
            array(
                'message' => $message,
                'updated' => $this->getDateTime()
            ),
            $this->quoteInto('id = ? ', $id)
        );
    }

    /**
     * Удалить комментарий $commentId
     * @param  $activityName
     * @param  $activitySubjectName
     * @param  $activitySubjectId
     * @param  $commentId
     * @return
     */
    public function deleteActivityComment($commentId)
    {
        return $this->getService('Comment')->deleteBy(
            $this->quoteInto(
                array('activity_name = ?', ' AND subject_name = ?', ' AND subject_id = ?', ' AND id = ?'),
                array($this->_cabinet->getActivityName(), $this->_cabinet->getActivitySubjectName(), $this->_cabinet->getActivitySubjectId(), $commentId)
            )
        );
    }

    /**
     * Получить список комментариев текущего виртуального кабинета
     * @param  $itemId
     * @param  $userId
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAllActivityComments($itemId = null, $userId = null, $count = null, $offset = null)
    {
        $subjectName = $this->_cabinet->getActivitySubjectName();
        return $this->getService('Comment')->fetchAll(
            $this->quoteInto(
                array('activity_name = ?', ' AND subject_id = ?'),
                array($this->_cabinet->getActivityName(), $this->_cabinet->getActivitySubjectId())
            )
            .(!empty($subjectName) ? $this->quoteInto(' AND subject_name = ?', $subjectName) : ' AND (subject_name IS NULL OR subject_name = \'\')')
            .(null !== $itemId ? $this->quoteInto(' AND item_id = ?', $itemId) : '')
            .(null !== $userId ? $this->quoteInto(' AND user_id = ?', $userId) : ''),
            'created DESC',
            $count,
            $offset
        );
    }

    /**
     * @param HM_Subscription_Channel_ChannelModel $channel
     * @return  HM_Subscription_Channel_ChannelModel
     */
    public function registerActivityChannel(HM_Subscription_Channel_ChannelModel $channel)
    {
        if (!$channel->activity_name) $channel->activity_name = $this->_cabinet->getActivityName();
        if (!$channel->subject_name)  $channel->subject_name  = $this->_cabinet->getActivitySubjectName();
        if (!$channel->subject_id)    $channel->subject_id    = $this->_cabinet->getActivitySubjectId();
        if (!$channel->lesson_id)     $channel->lesson_id     = $this->_cabinet->getActivityLessonId();

        return $this->getService('Subscription')->insertChannel($channel->getValues());
    }

    /**
     * @param HM_Subscription_Channel_ChannelModel $channel
     * @return bool
     */
    public function isActivityChannel(HM_Subscription_Channel_ChannelModel $channel)
    {
        if ($channel->activity_name) {
            $query['activity_name = ?'] = $channel->activity_name;
        } else {
            return false;
        }

        if ($channel->subject_name) {
            $query['subject_name = ?'] = $channel->subject_name;
        } else {
            $query['subject_name IS NULL'];
        }

        $query['subject_id = ?'] = (int) $channel->subject_id;
        $query['lesson_id = ?']  = (int) $channel->lesson_id;

        $result = $this->getService('SubscriptionChannel')->getOne($this->getService('SubscriptionChannel')->fetchAll($query));
        return ($result)? true : false;
    }

    public function unregisterActivityChannel(HM_Subscription_Channel_ChannelModel $channel)
    {
        return $this->getService('Subscription')->deleteChannel($channel->id);
    }

    public function subscribeUser($userId, $channelId)
    {
        return $this->getService('Subscription')->insert(array('user_id' => $userId, 'channel_id' => $channelId));
    }

    public function unsubscribeUser($userId, $channelId)
    {
        return $this->getService('Subscription')->deleteBy($this->quoteInto(array('user_id = ?', ' AND channel_id = ?'), array($userId, $channelId)));
    }

    /**
     * @param  $channelId
     * @param HM_Subscription_Entry_EntryModel $entry
     * @return HM_Subscription_Entry_EntryModel
     */
    public function insertActivityEntry($channelId, HM_Subscription_Entry_EntryModel $entry)
    {
        return $this->getService('Subscription')->insertEntry($entry->getValues());
    }

    public function updateActivityEntry(HM_Subscription_Entry_EntryModel $entry)
    {
        return $this->getService('Subscription')->updateEntry($entry->getValues());
    }

    public function deleteActivityEntry($entryId)
    {
        return $this->getService('Subscription')->deleteEntry($entryId);
    }

    /**
     * @param  $userId
     * @param  $activityName
     * @param  $activitySubjectName
     * @param  $activitySubjectId
     * @return HM_Collection
     */
    public function getUserActivityChannels($userId)
    {
        return $this->getService('SubscriptionChannel')->fetchAll(
            $this->quoteInto(
                array('user_id = ?', ' AND activity_name = ?', ' AND subject_name = ?', ' AND subject_id = ?'),
                array($userId, $this->_cabinet->getActivityName(), $this->_cabinet->getActivitySubjectName(), $this->_cabinet->getActivitySubjectId())
            )
        );
    }

    public function getActivityFileBrowserUrl()
    {
        return '';
    }

    /**
     * @param  $fileId
     * @return HM_Activity_File_FileModel
     */
    public function findActivityFile($fileId)
    {

    }

    public function getSubjectTitle($subjectName, $subjectId)
    {
        switch(strtolower($subjectName)) {
            case 'subject':
                return  $this->getOne($this->getService('Subject')->find($subjectId))->name;
            case 'project':
                return  $this->getOne($this->getService('Project')->find($subjectId))->name;
            case 'course':
                return $this->getOne($this->getService('Course')->find($subjectId))->Title;
            case 'resource':
                return $this->getOne($this->getService('Resource')->find($subjectId))->title;
            default:
                return '';
        }
    }

    /**
     * Создание канала для занятия
     * @param $lesson
     * @return bool|HM_Subscription_Channel_ChannelModel
     */
    public function createLessonSubscriptionChannel(HM_Lesson_LessonModel $lesson)
    {
        $activityService = HM_Activity_ActivityModel::getActivityService($lesson->typeID);
        if (strlen($activityService)) {
            $service = $this->getService($activityService);
        }

        if ($service instanceof HM_Service_Schedulable_Interface) {
            $channel = new HM_Subscription_Channel_ChannelModel(array(
                'activity_name' => $service->_cabinet->getActivityName(),
                'subject_name'  => $service->_cabinet->getActivitySubjectName(),
                'subject_id'    => $service->_cabinet->getActivitySubjectId(),
                'lesson_id'     => $lesson->SHEID,
                'title'         => $lesson->title
            ));

            if (!$this->isActivityChannel($channel)) {
                return $this->registerActivityChannel($channel);
            }
        }

        return false;
    }

    public function createMeetingSubscriptionChannel(HM_Meeting_MeetingModel $meeting)
    {
        $activityService = HM_Activity_ActivityModel::getActivityService($meeting->typeID);
        if (strlen($activityService)) {
            $service = $this->getService($activityService);
        }

        if ($service instanceof HM_Service_Schedulable_Interface) {
            $channel = new HM_Subscription_Channel_ChannelModel(array(
                'activity_name' => $service->_cabinet->getActivityName(),
                'subject_name'  => $service->_cabinet->getActivitySubjectName(),
                'subject_id'    => $service->_cabinet->getActivitySubjectId(),
                'subject'       => 'project',
                'lesson_id'     => $meeting->meeting_id,
                'title'         => $meeting->title
            ));

            if (!$this->isActivityChannel($channel)) {
                return $this->registerActivityChannel($channel);
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getActivityModeratorRoles()
    {
        return array(
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_CURATOR
        );
    }
}