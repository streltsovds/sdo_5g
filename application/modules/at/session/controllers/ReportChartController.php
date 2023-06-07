<?php
class Session_ReportChartController extends HM_Controller_Action_Chart
{
	public function getSettingsAction()
	{
	    $this->view->palette = HM_At_Evaluation_Method_CompetenceModel::getAnalyticsColors();
	}

	public function getDataAction()
	{
        if ($sessionUserId = $this->_getParam('session_user_id', 0)) {
            
            $analyticsTypes = $this->_getAllParams();
            $chartData = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getAnalyticsChartData($sessionUserId, $analyticsTypes);
            
            $this->view->legend = $chartData['legend'];
            $this->view->series = $chartData['series'];
            $this->view->graphs = $chartData['graphs'];
        }
        return true;
	}
}
