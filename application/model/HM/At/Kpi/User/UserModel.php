<?php
class HM_At_Kpi_User_UserModel extends HM_Model_Abstract
{   
    const TYPE_QUANTITATIVE = 1;
    const TYPE_QUALITATIVE  = 2;
    
    public static function getValueTypes(){
        return array(
            self::TYPE_QUALITATIVE  => _('Качественная'),
            self::TYPE_QUANTITATIVE => _('Количественная'),
        );
    }
    
    public static function getQualitiveValues(){
        return array(
            1  => _('Выполнено'),
            0  => _('Не выполнено'),
            -1 => '',
        );
    }
    
    
}