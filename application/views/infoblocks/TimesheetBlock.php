<?php

class HM_View_Infoblock_TimesheetBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'timesheet';

    public function timesheetBlock($param = null)
    {

        $content = $this->view->render('timesheetBlock.tpl');
        return $this->render($content);
    }
}