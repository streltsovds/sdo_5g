<?php
class Report_IndexController extends HM_Controller_Action_Report
{
    use HM_Controller_Action_Trait_Grid;

    public function listAction()
    {
        $this->view->domains = $this->getService('Report')->getListContent(new HM_Report_Config());
    }

    static public function listPlainify($data = array(), $view = null)
    {
        foreach($data['domains'] as &$domain) {
            foreach($domain['reports'] as &$report) {
                $report['url'] = $view->url([
                    'module' => 'report',
                    'controller' => 'index',
                    'action' => 'index',
                    'report_id' => $report['id'],
                ]);
            }
        }

        return $data;
    }

    public function indexAction()
    {
        $reportId = (int) $this->_getParam('report_id', 0);

        $reportItem = $this->getOne($this->getService('Report')->find($reportId));

        if ($reportItem) {
            // Чистим state грида
            $page = sprintf('%s-%s-%s', $this->_request->getModuleName(), $this->_request->getControllerName(), 'view');
            unset(Zend_Registry::get('session_namespace_default')->grid[$page]['grid']);
            
            $reportItem->fields = (strlen($reportItem->fields))? unserialize($reportItem->fields) : array();

            $config = new HM_Report_Config();
            $report = new HM_Report();
            $report->setConfig($config);
            $report->setFields($reportItem->fields);
            $fields = $report->getInputFields($this);

            if (!$fields) {
                $this->_redirector->gotoSimple('view', 'index', 'report', array('report_id' => $reportId));
            }

            $form = new HM_Form_InputParams($fields, $reportId);

            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                Zend_Registry::get('session_namespace_default')->report['values'][$reportId] = $form->getValues();
                $this->_redirector->gotoSimple('view', 'index', 'report', array('report_id' => $reportId));
            }

            $this->view->form = $form;
            $this->view->reportId = $reportId;
        }
    }

    public function viewAction()
    {
        $reportId = (int) $this->_getParam('report_id', 0);

        /** @var HM_Report_ReportModel $reportItem */
        $reportItem = $this->getOne($this->getService('Report')->find($reportId));

        $this->view->grid = false;

        if ($reportItem) {
            $this->view->setSubHeader($reportItem->name);

            if ($reportItem->isValid()) {

                $reportItem->fields = (strlen($reportItem->fields)) ? unserialize($reportItem->fields) : [];

                $config = new HM_Report_Config();
                $report = new HM_Report();
                $report->setConfig($config);

                if (isset(Zend_Registry::get('session_namespace_default')->report['values'][$reportId])) {
                    $report->setValues(Zend_Registry::get('session_namespace_default')->report['values'][$reportId]);
                }

                $report->setFields($reportItem->fields);

                // сортировка по умолчанию нужна в MSSQL, иначе не работает пагинатор грида
                $sorting = $this->_request->getParam("ordergrid");
                if ($sorting == "") {
                    $arr = explode('.', $reportItem->fields[0]['field']);
                    $field = array_pop($arr);

                    if ($field)
                        $this->_request->setParam("ordergrid", $field . '_ASC');
                }

                $grid = $report->getGrid($this);

                $this->view->grid = $grid;
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => $reportItem->getError()
                ));
            }
        }
    }
}