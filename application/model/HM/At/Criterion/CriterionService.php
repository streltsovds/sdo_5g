<?php
class HM_At_Criterion_CriterionService extends HM_Service_Abstract
{
    public function getNonClustered()
    {
        return $this->fetchAll('cluster_id IS NULL OR cluster_id = 0', 'name');
    }

    public function getClustersCriteriaIndicators($criterionIds)
    {
        $criteria = $this->fetchAllDependence(array('CriterionCluster', 'CriterionIndicator', 'CriterionScaleValue'), $this->quoteInto(array(
                'criterion_id IN (?)',
            ), array (
                $criterionIds
            )),
            array('order')
        );
        return $criteria;
    }
    
    
    /*
     *  DEPRECATED!!!
     *  старый код из pmа
     */
    public function getTreeContent($parent = 0, $criterionId = 0, $selectedCriteria = array())
    {
        $res = array();
        $criteria = $this->getChildren($parent);

        if (count($criteria)) {
            foreach($criteria as $criterion) {
            	$subcriteria = $this->getChildren($criterion->criterion_id);
            	$isFolder = (count($subcriteria)) ? true : false;
                $item = array(
                    'title' => (($parent > 0) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $criterion->name) : $criterion->name),
                	'count' => (int) count($subcriteria),
                    'key' => $criterion->criterion_id,
                    'isLazy' => ($isFolder ? true  : false),
                    'isFolder' => $isFolder,
                    'select' => in_array($criterion->criterion_id, $selectedCriteria),
                );

            	$sub = $this->getTreeContent($criterion->criterion_id, $criterionId, $selectedCriteria);
                if(count($sub)) {
	                //$item['expand'] = true;
                	$res[] = $item;
                	$res[] = $sub;
                } else {
                    $res[] = $item;
                }
            }
        }
        return $res;
    }
    
    public function getCriteriaByMethod($method, $criterionIds)
    {
        $criteria = array();
        switch ($method) {
            case HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO:
                $service = 'AtCriterionPersonal';
                break;
            case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
                $service = 'AtCriterionTest';
                break;
            case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                $service = 'AtCriterion';
                break;
        }
        
        if (count($collection = $this->getService($service)->fetchAllDependence('CriterionCluster', array('criterion_id IN (?)' => $criterionIds)))) {
            $criteria = $collection;
        }
        return $criteria;
    }

    // @todo: понять, в каких случаях нам нужен softDelete
    public function softDelete($id)
    {
        parent::update(array(
            'criterion_id' => $id,
            'status'       => HM_At_Criterion_CriterionModel::STATUS_DELETED
        ));

        $this->getService('Subject')->onCriterionDelete(
            $id,
            HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION
        );
    }

    public function unlinkCategory($categoryIds)
    {
        if (!is_array($categoryIds)) {
            $categoryIds = (array) $categoryIds;
        }

        if (count($categoryIds)) {
            $this->updateWhere(
                [
                    'category_id' => 0,
                ],
                $this->quoteInto('category_id IN (?)', $categoryIds)
            );
        }
    }
}