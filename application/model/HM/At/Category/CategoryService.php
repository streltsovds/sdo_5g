<?php
class HM_At_Category_CategoryService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        $category = parent::insert($data, $unsetNull);

        foreach (HM_Programm_ProgrammModel::getTypes(false) as $programmType => $title) {

            $this->getService('Programm')->insert(array(
                'programm_type' => $programmType,
                'name' => HM_Programm_ProgrammModel::getProgrammTitle($programmType, HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY, $category->name),
                'item_id' => $category->category_id,
                'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY,
            ));
        }

        return $category;
    }
    
    public function delete($categoryId)
    {
        $this->getService('Programm')->deleteBy(array(
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY,       
            'item_id = ?' => $categoryId,       
        ));
         $this->getService('AtEvaluation')->deleteBy(array('category_id = ?' => $categoryId));
        return parent::delete($categoryId);
    }
}