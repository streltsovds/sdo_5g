<?php
require_once APPLICATION_PATH . '/views/infoblocks/ResourcesRatingBlock.php';

class HM_View_Infoblock_CoursesRatingBlock extends HM_View_Infoblock_ResourcesRatingBlock
{
    protected $id = 'courses_rating';
    protected $_type = HM_Kbase_KbaseModel::TYPE_COURSE;
	protected $_template = 'resourcesRatingBlock.tpl';
    protected $_itemService = 'Course';


    protected $_count = 10;

    public function coursesRatingBlock($param = null)
    {
		return $this->resourcesRatingBlock($title, $attribs, $options);
    }

    public function getItemTitleLink($id, $title)
    {
        return '<a href="'.$this->view->url(array(
            'module' => 'course',
            'controller' => 'index',
            'action' => 'index',
            'course_id' => $id
        ), null, false, false).'">' . $title . '</a>';

    }

}