<?php
class HM_At_Profile_EducationRequirement_EducationRequirementModel_ extends HM_Model_Abstract
{
    const TYPE_MAIN         = 0; // основное образование
    const TYPE_SPECIALITY   = 1; // специальности  НЕ ИСПОЛЬЗУЕТСЯ, ВЫНЕСЕН В КЛАССИФИКАТОРЫ
    const TYPE_UNIVERSITIES = 2; // ВУЗы НЕ ИСПОЛЬЗУЕТСЯ, ВЫНЕСЕН В КЛАССИФИКАТОРЫ


    static public function getMainEducationVariant($variantId)
    {
        $variants = self::getMainEducationVariants();
        if (isset($variants[$variantId])) {
            return $variantId;
        }
        return false;
    }
    
    static public function getMainEducationVariants()
    {
        return array(
            1 => _('Основное'),
            2 => _('Среднее'),
            3 => _('Профессиональное'),
            4 => _('Неоконченное высшее'),
            5 => _('Высшее'),
            6 => _('Два и более высших')
        );
    }
}