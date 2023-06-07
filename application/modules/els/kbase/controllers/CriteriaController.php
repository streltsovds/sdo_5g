<?php
class Kbase_CriteriaController extends HM_Controller_Action
{
    public function corporateAction()
    {
        $criteria = $this->getService('AtCriterion')->fetchAll();

        $materialId = $this->_getParam('material_id');
        $materialType = $this->_getParam('material_type');

        $selectedCriteria = false;
        if ($materialId && $materialType) {
            $selectedCriteria = $this->getService('MaterialCriteria')->fetchAllManyToMany('Criterion', 'MaterialCriteria', [
                'material_id = ?' => $materialId,
                'material_type = ?' => $materialType,
                'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
            ]);
        }

        $list = [];
        $position = 0;
        foreach($criteria as $criterion) {
            $list[] = [
                'criterion_id' => $criterion->criterion_id,
                'name' => $criterion->name,
                'selected' => (bool)($selectedCriteria && $selectedCriteria->exists('criterion_id', $criterion->criterion_id)),
                'level' => 1,
                'lft' => ++$position,
                'rgt' => ++$position,
            ];

        }

        $this->view->list = $list;
    }

    public function criteriaTestAction()
    {
		$selectedCriteria = array();
        if ($evaluationId = $this->_getParam('evaluation_id')) {
            $selectedCriteria = $this->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluationId))->getList('criterion_id');
        }		

        $tree = $this->getService('AtCriterionTest')->getTreeContentForMultiselect($selectedCriteria);
    }
}