<?php
/**
 * Description of ArrayDecorator
 *
 * @author slava
 */
class HM_Recruit_RecruitingServices_Entity_Decorator_Hh_DepartmentList_ArrayDecorator
    extends HM_Recruit_RecruitingServices_Entity_Decorator_OutDecorator
{
    public function out()
    {
        $result = array();
        if ($this->getDecorableCollection()->count() > 0) {
            foreach ($this->getDecorableCollection() as $decorableItem) {
                $result[$decorableItem->getValue()] = $decorableItem->getName();
            }
        }
        return $result;
    }
}

?>