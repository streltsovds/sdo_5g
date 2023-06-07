<?php
class HM_View_Helper_ReportList extends HM_View_Helper_Abstract
{
    const CLASS_BRIEF = 'brief';
    const CLASS_NORMAL = 'normal';
    const CLASS_WITHOUT_KEYS = 'without-keys';
    const CLASS_COLORED_QUESTION = 'colored-questions';

    public function reportList($data, $class = self::CLASS_NORMAL, $resume = false)
    {
        $this->view->data = $data;
        $this->view->class = $class;
        return ($resume) ? $this->view->render('resume-report-list.tpl') : $this->view->render('report-list.tpl');
    }
}