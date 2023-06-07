<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 6/20/19
 * Time: 5:08 PM
 */

class HM_View_Sidebar_Blog extends HM_View_Sidebar_Abstract
{
    function getIcon()
    {
        return 'blog'; // @todo
    }

    function getContent()
    {
        // @todo: почему только внутри курса??

        if ($subject = $this->getModel()) {
            $subjectId = $subject->subid;
            $subjectName = $subject->name;

            $tags = $this->getService('Tag')->getTagsRating(HM_Tag_Ref_RefModel::TYPE_BLOG, $subjectId, $subjectName);
            $archiveDates = $this->getService('Blog')->getArchiveDates($subjectId, $subjectName);
            $authors = $this->getService('Blog')->getAuthors($subjectId, $subjectName);

            $data = [
                'cloudTags' => $tags,
                'archiveDates' => $archiveDates,
                'authors' => $authors,
                'model' => $this->getModel(),
            ];

            return $this->view->partial('blog.tpl', $data);
        }
    }
}