<?php

class HM_View_Infoblock_CourseStatusBlock extends HM_View_Infoblock_Abstract
{

    protected $id = 'coursestatusblock';
    
    public function courseStatusBlock($param = null)
    {
        if (!isset($options['course']) && isset($options['courseId'])) {
            $options['course'] = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Course')->find($options['courseId'])
            );
        }
        $this->view->course = $options['course'];
        $content = $this->view->render('courseStatusBlock.tpl');

        
        return $this->render($content);
    }
}