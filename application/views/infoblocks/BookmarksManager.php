<?php

class HM_View_Infoblock_BookmarksManager extends HM_View_Infoblock_Abstract
{                                          
    
    protected $id = 'bookmarksManager';
    
    public function bookmarksManager($param = null)
    {
        $userService = $this->getService('User');
        $currentUserId = $userService->getCurrentUserId();

        /** @var HM_Course_Bookmark_BookmarkService $courseBookmarkService */
        $courseBookmarkService = $this->getService('CourseBookmark');

        $view = $this->view;
        /** @var HM_Frontend_HM $HM */
        $HM = $view->HM();

        $data = $courseBookmarkService->getHmBookmarksTree($currentUserId);
        $view->bookmarks = $HM->createComponent(
            'hm.core.ui.trainingModulesViewer.Bookmarks',
            array(
                'data' => $data
            )
        );

        $content = $view->render('bookmarksManager.tpl');
        return $this->render($content);
    }
}