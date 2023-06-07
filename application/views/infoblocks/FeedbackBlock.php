<?php

class HM_View_Infoblock_FeedbackBlock extends HM_View_Infoblock_Abstract
{

    protected $id = 'feedbackblock';

    public function feedbackBlock($param = null)
    {
/*
        $begin = time();
        $end = $begin + 28 * 60*60*24;

        if (isset($options['begin'])) {
            $begin = $options['begin'];
        }

        if (isset($options['end'])) {
            $end = $options['end'];
        }
*/
        $ajax = isset($options['ajax']);

        $currentUserId = $this->getService('User')->getCurrentUserId();
        $feedbackGroupByCourse = $this->getService('Feedback')->getUserFeedback($currentUserId);

        foreach ($feedbackGroupByCourse as $courseId => $feedbackCourse) {
            foreach ($feedbackCourse['feedbacks'] as $keyFeedback => $feedback) {
                $url = $this->view->url(array('module' => 'quest', 'controller' => 'feedback', 'action' => 'start', 'quest_id' => $feedback['quest_id'], 'feedback_user_id' => $feedback['feedback_user_id']), null, true);
                $feedbackGroupByCourse[$courseId]['feedbacks'][$keyFeedback]['url'] = $url;
            }
        }

        $this->view->ajax = $ajax;
        $this->view->feedback = $feedbackGroupByCourse;

        $content = '';
        if (count($feedbackGroupByCourse)) {
            $content = $this->view->render('feedbackBlock.tpl');
        }

        return $this->render($content);
    }
}