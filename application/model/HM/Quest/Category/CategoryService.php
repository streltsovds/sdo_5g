<?php
class HM_Quest_Category_CategoryService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        $data['formula'] = $this->_prepareFormula($data['formula']); 
        return parent::insert($data, $unsetNull);
    }
    
    public function update($data, $unsetNull = true)
    {
        $data['formula'] = $this->_prepareFormula($data['formula']); 
        return parent::update($data, $unsetNull);
    }
    
    protected function _prepareFormula($formulaArr)
    {
        $formula = array();
        if (!empty($formulaArr)) {
            foreach ($formulaArr as $itemId => $item) {
                if ($itemId !== HM_Form_Element_MultiSet::ITEMS_NEW) {
                    $formula[] = $item;
                } else {
                    foreach ($item['description'] as $i => $value) {
                        if (!strlen(trim($value))) continue;
                        $formula[] = array(
                            'from' => $item['from'][$i],        
                            'to' => $item['to'][$i],        
                            'description' => $item['description'][$i],        
                        ); 
                    }                    
                }
            }     
        }
        return (count($formula)) ? serialize($formula) : '';
    }

    public function getByQuest($questId) {
        return $this->fetchAll(['quest_id = ?' => $questId]);
    }
}