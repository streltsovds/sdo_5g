<?php


class HM_View_Infoblock_LearningSessionsCalendarBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'learningSessionsCalendar';

    public function learningSessionsCalendarBlock($param = null)
    {
        $datesWithEvents = [];
        $sessions = $this->getService('Subject')->fetchAll(array(
            'base = ?' => HM_Subject_SubjectModel::BASETYPE_SESSION
        ));
        foreach ($sessions as $key => $session) {
            $datesWithEvents[date('Y-m-d', strtotime($session->begin))][] = [
                'subject_id' => $session->subid,
                'name' => $session->name,
                'begin_date' => date('Y-m-d', strtotime($session->begin)),
                'type' => 'subject',
                'view_url' => $this->view->url(array(
                    'module' => 'subject',
                    'controller' => 'index',
                    'action' => 'description',
                    'subject_id' => $session->subid,
                )), 
                'is_session' => 1,
                'color' => $session->base_color
            ];
        }

        $this->view->data = HM_Json::encodeErrorSkip($datesWithEvents);

        $content = $this->view->render('learningSessionsCalendarBlock.tpl');
        return $this->render($content);
    }
}