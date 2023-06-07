<?php
class HM_Form_Modifier_FulltimeNew extends HM_Form_Modifier_Fulltime
{
    protected $hideFields    = array();
    protected $displayGroups = array('Fulltime1', 'Fulltime2', 'Fulltime3', 'classifiers');

    protected function _getActions()
    {
        $categories = HM_Tc_Subject_SubjectModel::getVariants('FulltimeCategories');
        unset($categories[HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_CORPORATE]);
        $form = $this->getForm();
        $form->getElement('category')->setOptions(array('multiOptions' => $categories));


        return parent::_getActions();
    }
}