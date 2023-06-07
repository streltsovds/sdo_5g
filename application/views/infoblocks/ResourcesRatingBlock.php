<?php


class HM_View_Infoblock_ResourcesRatingBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'resources_rating';
    protected $_type = HM_Kbase_KbaseModel::TYPE_RESOURCE;
    protected $_template = 'resourcesRatingBlock.tpl';
    protected $_itemService = 'Resource';


    protected $_count = 10;

    public function resourcesRatingBlock($param = null)
    {
        /** @var HM_Kbase_Assessment_AssessmentService $kbaseAssessmentService */
        $kbaseAssessmentService = $this->getService('KbaseAssessment');

        $items = $kbaseAssessmentService->getTopByType($this->_count, $this->_type);

        $report = array();
        foreach ($items as $item) {
            $item['average'] = round($item['average'], 1);

            $reportItemValue = array(
                'url' => $this->view->url(array(
                    'module' => 'kbase',
                    'controller' => 'resource',
                    'action' => 'index',
                    'resource_id' => $item['resource_id']
                ), null, false, false),
                'title' => $item['title'],
                'average' => $item['average'],
                'resource_id' => $item['resource_id'],
                'value' => $item['value'],
                'count' => $item['count'],
                'type' => $item['type']
            );

            $report[] = $reportItemValue;
        }
        $this->view->report = $report;

        $content = $this->view->render($this->_template);

        return $this->render($content);
    }
}