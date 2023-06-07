<?php
class HM_View_Helper_ReportBlank extends HM_View_Helper_Abstract
{
    public function reportBlank($title = '', $rows = false)
    {
        $this->view->title = trim($title);
        $this->view->rows = $rows ? $rows : 6;
        return $this->view->render('report-blank.tpl');
    }
}