<?php


class HM_View_Infoblock_DistanceSubjectsProgressBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'distanceSubjectsProgressBlock';

    public function distanceSubjectsProgressBlock()
    {
        $soid = Zend_Registry::get('session_namespace_default')->dstnc_subj_prgrs->soid;
        $profileId = Zend_Registry::get('session_namespace_default')->dstnc_subj_prgrs->profileId;

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

        $jobProfiles = HM_At_Profile_ProfileModel::getProfilesToFrontend();

        $this->view->orgstructureTree = HM_Json::encodeErrorSkip($orgstructureTree);
        $this->view->jobProfiles = HM_Json::encodeErrorSkip($jobProfiles);
        $this->view->unit = HM_Json::encodeErrorSkip($filterUnit);
        $this->view->profileId = HM_Json::encodeErrorSkip($profileId);

        $content = $this->view->render('distanceSubjectsProgressBlock.tpl');

        return $this->render($content);
    }
}
