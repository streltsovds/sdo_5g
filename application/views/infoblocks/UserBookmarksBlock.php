<?php


class HM_View_Infoblock_UserBookmarksBlock extends HM_View_Infoblock_Abstract
{
    const MAX_ITEMS = 10;

    protected $id = 'userBookmarks';

    public function userBookmarksBlock($param = null)
    {

        /** @var HM_Subject_SubjectModel $subject */
        $subject = $options['subject'];
        $services = Zend_Registry::get('serviceContainer');

        /** @var HM_Course_Bookmark_BookmarkService $courseBookmarkService */
        $courseBookmarkService = $this->getService('CourseBookmark');
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $services->getService('Lesson');
        /** @var HM_User_UserService $userService */
        $userService = $services->getService('User');

        $currentUserId = $userService->getCurrentUserId();

        $subjectLessons = $lessonService->fetchAll(array('CID = ?' => $subject->subid));
        $lessonsList = $subjectLessons->getList('SHEID', 'title');

        $bookmarks = array();
        if (count($lessonsList)) {
            $bookmarks = $courseBookmarkService->fetchAllDependence(array('CourseItem','Resource'), array(
                'user_id = ?' => $currentUserId,
                'lesson_id IN (?)' => array_keys($lessonsList)
            ));
        }

        $courses = array();
        /** @var HM_Course_Bookmark_BookmarkModel $bookmark */
        foreach($bookmarks as $bookmark) {
			if (!$bookmark->courseItems) continue;
            /** @var HM_Course_Item_ItemModel $course */
            $course = $bookmark->courseItems->current();
            //$resource = (count($bookmark->resources)) ? $bookmark->resources->current() : null;
            $itemTitle = ($bookmark->title) ? $bookmark->title : $course->title;
            $courses[] = array(
                'lesson_id'    => $bookmark->lesson_id,
                'lesson_title' => $lessonsList[$bookmark->lesson_id],
                'item_id'      => $bookmark->item_id,
                'item_title'   => $itemTitle,
                'course_id'    => $course->cid
            );
        }

        $this->view->bookmarks = $bookmarks;

        /** @var HM_View_Helper_HM $HM */
        $HM = $this->view->HM();
        $HM->create('hm.core.ui.trainingModulesViewer.BookmarksList', array(
            'renderTo' => '#hm-lesson-bookmarks-list',
            'subjectId' => $subject->subid,
            'bookmarks' => $courses
        ));

		$content = $this->view->render('userBookmarksBlock.tpl');

        
        return $this->render($content);
    }
}