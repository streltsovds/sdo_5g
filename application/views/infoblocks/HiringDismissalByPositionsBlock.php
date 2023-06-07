<?php


class HM_View_Infoblock_HiringDismissalByPositionsBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'hiringDismissalByPositions';

    public function hiringDismissalByPositionsBlock()
    {
        $fromDate = Zend_Registry::get('session_namespace_default')->hrds_by_pos->fromDate;
        $toDate = Zend_Registry::get('session_namespace_default')->hrds_by_pos->toDate;
        $soid = Zend_Registry::get('session_namespace_default')->hrds_by_pos->soid;

        $fromDate = isset($fromDate) ? $fromDate : date('d.m.Y', strtotime('-30 DAY'));
        $toDate = isset($toDate) ? $toDate : date('d.m.Y');

        $services = Zend_Registry::get('serviceContainer');
        $orgstructureService = $services->getService('Orgstructure');
        $defaultParent = $orgstructureService->getDefaultParent();

        $userId = $this->getService('User')->getCurrentUserId();
        $openedParents = $this->getService('Responsibility')->fetchAll(array(
            'user_id = ?' => $userId,
            'item_type = ?' => HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE
        ))->getList('item_id');
        $orgstructureTree = $orgstructureService->getTreeContent($defaultParent->soid, true, $defaultParent->soid, null, $openedParents);

        $rootParent = array(
            'title' => $defaultParent->name,
            'count' => 0,
            'key' => $defaultParent->soid,
            'isLazy' => true,
            'isFolder' => true,
            'expand' => true
        );

        $orgstructureTree = array(
            0 => $rootParent,
            1 => $orgstructureTree
        );

        if ($soid > 0) {
            $unit = $orgstructureService->getOne($orgstructureService->fetchAll($orgstructureService->quoteInto('soid = ?', $soid)));
            $filterUnit = $orgstructureService->orgUnitToFrontendData($unit, $unit->owner_soid, true, $unit->soid);
        } else {
            $filterUnit = $rootParent;
        }

        $this->view->from = HM_Json::encodeErrorSkip($fromDate);
        $this->view->orgstructureTree = HM_Json::encodeErrorSkip($orgstructureTree);
        $this->view->to = HM_Json::encodeErrorSkip($toDate);
        $this->view->unit = HM_Json::encodeErrorSkip($filterUnit);

        $content = $this->view->render('hiringDismissalByPositionsBlock.tpl');

        return $this->render($content);
    }
}
