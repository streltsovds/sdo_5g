<?php
class HM_StudyGroup_StudyGroupService extends HM_Service_Abstract
{
    protected $_cache = [
        'getById' => []
    ];

    public function create($name, $type)
    {
        $group = $this->insert(array(
            'name' => $name,
            'type' => $type
        ));

        return $group;
    }

    public function delete($groupId)
    {
        /* Отменяем курсы которые были назначенны группе */
        $this->getService('StudyGroupCourse')->removeGroupCourses($groupId);

        /* Отменяем занятия которые были назначенны группе */
        $this->getService('StudyGroupCourse')->removeGroupLessons($groupId);

        /* Отменяем программы которые были назначенны группе */
        $this->getService('StudyGroupProgramm')->removeGroupProgramms($groupId);

        /* Удаляем участников из группы */
        $this->getService('StudyGroupUsers')->deleteBy($this->quoteInto('group_id = ?', $groupId));

        return parent::delete($groupId);
    }

    public function getUsers($groupId)
    {
        $users = [];
        $refs = $this->getService('StudyGroupUsers')->fetchAll(array(
            'group_id = ?' => $groupId
        ));

        foreach($refs as $ref) {
            $users []= $ref->user_id;
        }
        return $users;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('группа plural', '%s группа', $count), $count);
    }

    public function getById($id)
    {
        $cacheName = 'getById';

        if ($this->_cache[$cacheName][$id]) {
            $output = $this->_cache[$cacheName][$id];
        } else {

            $output = $this->getOne($this->fetchAll($this->quoteInto('group_id = ?', $id)));
            $this->_cache[$cacheName][$id] = $output;
        }

        return $output;
    }

    public function getByName($name)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('name = ?', $name)));
    }

    public function saveTags($groupId, $tags)
    {
        if (!$tags) {
            return;
        }
        $tagsIds = $this->getService('Tag')->updateTags($tags, $groupId, $this->getService('TagRef')->getStudyGroupType());

        $refUsers = $this->getService('TagRef')->fetchAll(
            $this->quoteInto(
                array('item_type = ?', ' AND tag_id in (?)'),
                array( $this->getService('TagRef')->getUserType(), $tagsIds)
            ))->getList('item_id');

        foreach ($refUsers as $userId) {
            if(!$this->getService('StudyGroupUsers')->isGroupUser($groupId, $userId)) {
                $this->getService('StudyGroupUsers')->addUser($groupId, $userId);
            }
        }
    }

    public function addUserByTags($userId, $tags, $bTagsById=true)
    {
        if (!$tags) {
            return;
        }

        if($bTagsById){
            $refGroups = $this->getService('TagRef')->fetchAll(
                $this->quoteInto(
                    array('item_type = ?', ' AND tag_id in (?)'),
                    array( $this->getService('TagRef')->getStudyGroupType(), $tags)
                ))->getList('item_id');
        } else {
            $refGroups = $this->getService('TagRef')->fetchAllJoinInner('Tag',
                $this->quoteInto(
                    array('item_type = ?', ' AND body in (?)'),
                    array( $this->getService('TagRef')->getStudyGroupType(), $tags)
                ))->getList('item_id');
        }

        foreach ($refGroups as $groupId) {
            if(!$this->getService('StudyGroupUsers')->isGroupUser($groupId, $userId)) {
                $this->getService('StudyGroupUsers')->addUser($groupId, $userId);
            }
        }
    }
}
