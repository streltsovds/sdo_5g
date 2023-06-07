<?php
class HM_Course_Bookmark_BookmarkService extends HM_Service_Abstract
{

    /**
     * @param null|int|array $lessonId
     * @param null|int|array $userId
     * @return HM_Collection
     */
    public function getBookmarks($lessonId = null, $userId = null)
    {
        $where = array();

        if (!is_null($lessonId)) {
            if (!is_array($lessonId)) {
                $lessonId = array($lessonId);
            }

            $where['lesson_id IN (?)'] = $lessonId;
        }

        if (!is_null($userId)) {
            if (!is_array($userId)) {
                $userId = array($userId);
            }

            $where['user_id IN (?)'] = $userId;
        }

        $bookmarks = $this->fetchAll($where);

        return $bookmarks;
    }

    /**
     * Возвращает древовидную структуру для hm.core.ui.trainingModulesViewer.Bookmarks
     * @param $userId
     * @return array
     */
    public function getHmBookmarksTree($userId)
    {
        $userBookmarks = $this->getBookmarks(null, $userId);

        //сортируем элементы по prev_id
        $userBookmarksSorted = array();
        foreach ($userBookmarks as $bookmark) {
            if ($bookmark->prev_id == 0) {
                //заполняем элементами, перед которыми ничего нет
                $userBookmarksSorted[] = $bookmark;
            }
        }

        //вспомогательный массив по prev_id
        $userBookmarksByPrevId = array();
        foreach ($userBookmarks as $bookmark) {
            $userBookmarksByPrevId[$bookmark->prev_id] = $bookmark;
        }

        //упорядочиваем данные
        $index = 0;
        while (isset($userBookmarksSorted[$index])) {
            $bookmarkId = $userBookmarksSorted[$index]->bookmark_id;

            //дополняем массив для дальнейшего перебора
            if (isset($userBookmarksByPrevId[$bookmarkId])) {
                array_splice(
                    $userBookmarksSorted,
                    $index + 1,
                    0,
                    array($userBookmarksByPrevId[$bookmarkId])
                );
            }
            $index++;
        }

        //title
        $itemIds = array_unique($userBookmarks->getList('item_id'));
        $courseItems = $this->getService('CourseItem')->find($itemIds);
        $resourceIds = $courseItems->getList('oid', 'vol2');
        $resources = $this->getService('Resource')->find($resourceIds);
        $resourceTitles = $resources->getList('resource_id', 'title');

        $resourceTitlesByItemId = $resourceIds;
        foreach ($resourceTitlesByItemId as $key => $value) {
            $resourceTitlesByItemId[$key] = $resourceTitles[$value];
        }

        //готовим данные для дерева
        $userBookmarksById = array();
        /** @var HM_Course_Bookmark_BookmarkModel $bookmark */
        foreach ($userBookmarksSorted as $bookmark) {
            unset($bookmark->prev_item);
            $bookmarkId = $bookmark->bookmark_id;
            $bookmarkTitle = $bookmark->title;
            $itemId = $bookmark->item_id;

            $isFolder = !$itemId;

            $userBookmarksById[$bookmarkId] = array(
                'title' => $isFolder ? $bookmarkTitle : ($bookmarkTitle) ? $bookmarkTitle : $resourceTitlesByItemId[$itemId],
                'isFolder' => $isFolder,
                'iconClass' => $isFolder ? null : 'hm-lesson-bookmarks-icon',
                'data' => $bookmark->getData(),
                'children' => array(),
            );
        }

        //строим дерево
        $data = array();

        foreach ($userBookmarksById as $bookmarkId => $bookmark) {
            $parentId = $bookmark['data']['parent_id'];
            if ($parentId == 0) {
                $data[] = &$userBookmarksById[$bookmarkId];
            } else {
                $userBookmarksById[$parentId]['children'][] = &$userBookmarksById[$bookmarkId];
            }
        }

        return $data;
    }

    public function getBookmarksForWidget($userId)
    {
        $select = $this->getSelect();

        $select->from(
            array('ob' => 'organizations_bookmarks'),
            array(
                'bookmark_name' => 'o.title',
                'resource_id' => 'r.resource_id',
                'subject_id' => 's.subid',
                'subject_name' => 's.name',
                'lesson_name' => 'sch.title'
            )
        );
        $select->joinLeft(array('sch' => 'schedule'), 'ob.lesson_id=sch.SHEID', array());
        $select->joinLeft(array('s' => 'subjects'), 'sch.CID = s.subid', array());
        $select->joinInner(array('o' => 'organizations'), 'ob.item_id = o.oid', array());
        $select->joinInner(array('r' => 'resources'), 'o.module = r.resource_id', array());
        $select->joinInner(array('c' => 'Courses'), 'o.cid = c.CID', array());
        $select->where($this->quoteInto(
            array('ob.user_id = ?', ' AND c.format'),
            array($userId, HM_Course_CourseModel::FORMAT_FREE)
        ));

        return $select->query()->fetchAll();
    }

    public function deleteBy($where)
    {
        $updateResult = $this->updatePrevIdsOnDelete($where);
        return parent::deleteBy($where);
    }
    
    public function updatePrevIdsOnDelete($where)
    {
        $bookmark = $this->getOne($this->fetchAll($where));
        $nextBookmarks = $this->fetchAll($this->quoteInto(
            array('prev_id = ?'),
            array($bookmark->bookmark_id)
        ));
        $updateResult = 0;
        if(count($nextBookmarks)) {
            $updateResult = $this->updateWhere(
                array('prev_id' => $bookmark->prev_id),
                $this->quoteInto('bookmark_id in (?)', $nextBookmarks->getList('bookmark_id'))
            );
        }
        return $updateResult;
    }
}