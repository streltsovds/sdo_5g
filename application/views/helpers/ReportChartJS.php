<?php
/**
 * Абстрактный helper 
 * для рисования диаграмм расшифровкой в виде таблицы
 * 
 * @param array $data: как в HM_View_Helper_ChartJS
 * @param array $graphs: как в HM_View_Helper_ChartJS
 * @param array $options: dataTitle|showTable|width|height|title|id
 * @return string
 * 
 */
class HM_View_Helper_ReportChartJS extends HM_View_Helper_Abstract
{
    const TABLE_DISPLAY_NONE = 0;
    const TABLE_DISPLAY_INLINE = 1;
    const TABLE_DISPLAY_BLOCK = 2;

    public function reportChartJS($data = [], $graphs = [], $chartOptions = [], $tableOptions = [])
    {
        if (!count($data)) return '';
        
        $this->view->data = $data;
        $this->view->graphs = $graphs;
        $this->view->chartOptions = $chartOptions;
        $this->view->tableOptions = $tableOptions;
        
        $tpl = (Zend_Registry::get('view')->getRequest()->getActionName() != 'competence') ? 'report-chart-js.tpl' : 'report-chart-js-competence.tpl';
        return $this->view->render($tpl);
    }
}