<?php

/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 27.05.2016
 * Time: 19:46
 */


class HM_View_Infoblock_KbaseItemRatingBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'kbaseItemRatingBlock';

    public function kbaseItemRatingBlock($param = null)
    {
        $subject = $options['subject'];
        $type = $subject->getKbaseType();
        $paramName = $options['paramName'];
        $this->view->kbase_assessment = array(
            'show' => true,
            'resource_id' => $subject->$paramName,
            'value'=> $this->getService('KbaseAssessment')
                ->getAverage($subject->$paramName, $type),
            'type' => HM_Kbase_KbaseModel::TYPE_RESOURCE,
        );
        $content = $this->view->render('kbaseItemRatingBlock.tpl');

        
        return $this->render($content);
    }
}