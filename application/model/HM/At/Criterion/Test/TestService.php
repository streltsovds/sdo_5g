<?php
// скопировано с классификаторов
class HM_At_Criterion_Test_TestService extends HM_Service_Nested
{
    public function getTreeContent($parent = 0, $notEncode = false, $criterionId = 0)
    {
        $res = array();
        $criteria = $this->getChildren($parent);
        $userId = $this->getService('User')->getCurrentUserId();
        if (count($criteria)) {
            foreach ($criteria as $criterion) {
                $subCriteria = $this->getChildren($criterion->criterion_id);
                $isFolder = (count($subCriteria)) ? true : false;
                $item = array(
                    'title' => (($parent > 0 && $notEncode === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $criterion->name) : $criterion->name),
                    'count' => 0,
                    'key' => $criterion->criterion_id,
                    'isLazy' => ($isFolder ? true  : false),
                    'isFolder' => $isFolder
                );
                if ($criterionId && /*$criterion->lft == 14 && $criterion->rgt > 16 &&*/ $parent) { // @todo: что за цифры??
                    $sub = $this->getTreeContent($criterion->criterion_id, false, $criterionId);
                    if(count($sub)) {
                        $item['expand'] = true;
                        $res[] = $item; $res[] = $sub;
                    }
                }
                else $res[] = $item;
            }
        }

        if ($parent === 0){
            if (count($res)) {
                $result = array();
                foreach($res as $r) {
                    $r['expand'] = true;
                    $result[] = $r;
                    $temp = $this->getTreeContent($r['key'], true, $criterionId);
                    $result[] = $temp;
                }
                $res = $result;
            }
            /*
            $temp = $this->getTreeContent($itemType, $res[0]['key'], $type, true, $criterionId);
            $res[1] = $temp;
            $res[0]['expand'] = true;

             */
        }
        return $res;
    }
    
    public function getTreeContentForMultiselect($selectedCriteria)
    {
        $return = [];
        return $this->_iterateTreeForMultiselect($return, 0, $selectedCriteria);
    }
    
    private function _iterateTreeForMultiselect(&$return, $parent, $selectedCriteria)
    {
        $count = 0;
        $criteria = $this->getChildren($parent);
        foreach($criteria as $criterion) {
            if (($parent > 0) || ($count++ > 0)) $return .= "\n";
            $criterionId = $criterion->criterion_id;
            if (in_array($criterionId, $selectedCriteria)) {
                $criterionId .= '+';
            } 
            $return .= sprintf("%s= %s%s", $criterionId, str_pad('', $criterion->level, '-'), $criterion->name);
            $this->_iterateTreeForMultiselect($return, $criterionId, $selectedCriteria);
        }
        return $return;
    }

    public function getLeaves($criterionIds)
    {
        return $this->fetchAll(array(
            'criterion_id IN (?)' => $criterionIds,        
            new Zend_Db_Expr('lft = rgt - 1'),        
        ));        
    }
    public function delete($id)
    {
        parent::update(array(
            'criterion_id' => $id,
            'status'       => HM_At_Criterion_Test_TestModel::STATUS_DELETED
        ));

        $this->getService('Subject')->onCriterionDelete(
            $id,
            HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST
        );
    }

}