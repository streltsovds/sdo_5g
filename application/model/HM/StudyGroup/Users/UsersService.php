<?php
class HM_StudyGroup_Users_UsersService extends HM_Service_Abstract
{
    protected $_cache = [
        'getUserGroups' => []
    ];

    public function isGroupUser($groupId, $userId)
    {
        return $this->getOne($this->fetchAll(array(
            'user_id = ?' => $userId,
            'group_id = ?' => $groupId
        )));
    }

    public function addUser($groupId, $userId)
    {
        $this->getService('StudyGroupUsers')->insert(array(
            'group_id' => $groupId,
            'user_id' => $userId
        ));

        /* Зачисление пользователя на курсы группы */
        $this->getService('StudyGroupCourse')->assignUser($groupId, $userId);
    }

    public function removeUser($groupId, $userId)
    {
        $this->deleteBy([
            'user_id = ?' => $userId,
            'group_id = ?' => $groupId
        ]);

        /* Отчисление пользователя с курсов группы */
        $this->getService('StudyGroupCourse')->unassignUser($groupId, $userId);
    }

    public function getUserGroups($userId)
    {
        $cacheName = 'getUserGroups';

        if (isset($this->_cache[$cacheName][$userId])) {
            $output = $this->_cache[$cacheName][$userId];
        } else {

            $select = $this->getSelect();
            $select->from(
                ['sg' => 'study_groups'],
                ['sg.*']
            )
                ->joinLeft(
                    ['g' => 'study_groups_users'],
                    'g.group_id = sg.group_id',
                    []
                )
                ->where('g.user_id = ?', $userId);

            $output = $select->query()->fetchAll();

            $this->_cache[$cacheName][$userId] = $output;
        }

        return $output;

    }

    public function getUsersOnCourse($groupId, $courseId)
    {
        $select = $this->getSelect();
        $select->from(
            ['sgu' => 'study_groups_users'],
            ['sgu.*']
        )
            ->joinLeft(
                ['sgc' => 'study_groups_courses'],
                'sgc.group_id = sgu.group_id',
                []
            )
            ->where('sgc.course_id = ?', $courseId);

        if ($groupId > 0) {
            $select->where('sgc.group_id = ?', $groupId);
        }

        return $select->query()->fetchAll();
    }
}