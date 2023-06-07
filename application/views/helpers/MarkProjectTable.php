<?php
require_once APPLICATION_PATH .  '/views/helpers/Score.php';

class HM_View_Helper_MarkProjectTable extends HM_View_Helper_Abstract
{
    public function markProjectTable($persons, $schedules, $scores, $mode = 'page', $projectId = null)
    {
        $this->view->persons   = $persons;
        $this->view->schedules = $schedules;
        $this->view->scores    = $scores;
        $this->view->project = Zend_Registry::get('serviceContainer')->getService('Project')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Project')->find($projectId)
        );

        switch ($mode){
        	case 'page': return $this->view->render('markprojecttable.tpl'); break;
        	case 'print': return $this->view->render('markprojecttable-print.tpl'); break;
        	case 'export': return $this->view->render('markprojecttable-export.tpl'); break;
        }
    }
}