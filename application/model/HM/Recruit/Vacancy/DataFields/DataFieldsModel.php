<?php 
class HM_Recruit_Vacancy_DataFields_DataFieldsModel extends HM_Model_Abstract
{
    const ITEM_TYPE_VACANCY     = 1;
    const ITEM_TYPE_APPLICATION = 2;
    
    static public function getTypes() {
        return array(
            self::ITEM_TYPE_VACANCY     => _('Сессия подбора'),
            self::ITEM_TYPE_APPLICATION => _('Заявка на сессию подбора'), 
        );
    }
    
}