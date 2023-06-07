<?php
class HM_At_Profile_Skill_SkillModel extends HM_Model_Abstract
{
    const TYPE_OUTER = 0; // для кандидатов 
    const TYPE_INNER = 1; // для пользователей
    
    static public function getTypes()
    {
        return array(
            self::TYPE_OUTER => _('Требования ФЗ РФ'),
            self::TYPE_INNER => _('Внутренние требования СГК')
        );
    }
}