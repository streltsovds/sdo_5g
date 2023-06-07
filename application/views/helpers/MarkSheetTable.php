<?php
require_once APPLICATION_PATH .  '/views/helpers/Score.php';

class HM_View_Helper_MarkSheetTable extends HM_View_Helper_Abstract
{
    public function markSheetTable($mode = 'page')
    {
        switch ($mode) {
            case 'page': return $this->view->render('marksheettable.tpl'); break;
            case 'print': return $this->view->render('marksheettable-print.tpl'); break;
            case 'export': return $this->view->render('marksheettable-export.tpl'); break;
        }
    }
}