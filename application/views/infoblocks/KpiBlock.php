<?php

require_once APPLICATION_PATH . '/views/helpers/Score.php';

class HM_View_Infoblock_KpiBlock extends HM_View_Infoblock_Abstract
{
    public function kpiBlock($param = null)
    {
        $disabled = false;
        $currentUser = $this->getService('User')->getCurrentUserId();
        try {
            $clusters = $this->getService('AtKpiUser')->getUserKpis($this->getService('User')->getCurrentUserId());
        } catch (HM_Exception $e) {
            $this->view->message = $e->getMessage();
        }
        
        if (!count($clusters)) {
            $this->view->message = _('Отсутствуют данные для отображения');
        }

        $cycles = array();
        foreach ($clusters as $cluster) {
            if (is_array($cluster)) {
                foreach ($cluster as $item) {
                    if (!in_array($item['cycle_id'], $cycles)) $cycles[] = $item['cycle_id'];
                }
            }
        }

        $atSessionEvents = $this->getService('AtSessionEvent')->fetchAllDependenceJoinInner('Session',
            $this->getService('AtSessionEvent')->quoteInto(
                array(
                    ' self.user_id=? AND ',
                    ' self.method=? AND ',
                    ' self.status=? AND ',
                    ' Session.cycle_id IN (?) '),
                array(
                    $currentUser,
                    HM_At_Evaluation_EvaluationModel::TYPE_KPI,
                    HM_At_Session_Event_EventModel::STATUS_COMPLETED,
                    count($cycles) ? $cycles : array(0))
            ));

        if(count($atSessionEvents)) {
            $disabled = true;
            $clusters = $this->getService('AtKpiUser')->getUserKpis(
                $this->getService('User')->getCurrentUserId(),
                null,
                HM_At_Evaluation_EvaluationModel::RELATION_TYPE_PARENT
            );
        }

        $progress = $this->getService('AtKpiUser')->getUserProgress($this->getService('User')->getCurrentUserId());

        $this->view->disabled = $disabled;
        $this->view->clusters = $clusters;
        $this->view->progress = $progress;
        $this->view->footnote(_('Отметки о выполнении задач могут вводиться пользователем в течение всего оценочного периода; они доступны для просмотра руководителю и носят информационный характер. Непосредственно подведение итогов выполнения задач осуществляется в оценочной форме "Оценка выполнения задач".'), 1);

        $content = $this->view->render('kpiBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/kpi/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/kpi/script.js');
        
        return $this->render($content);
    }
}