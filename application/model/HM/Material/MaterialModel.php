<?php
class HM_Material_MaterialModel extends HM_Model_Abstract
{
    const MATERIAL_NEW = -1;

    static public function getMaterialTypes()
    {
        return [
            HM_Event_EventModel::TYPE_RESOURCE => _('Информационный ресурс'),
            HM_Event_EventModel::TYPE_COURSE => _('Учебный модуль'),
            HM_Event_EventModel::TYPE_TEST => _('Тест'),
            HM_Event_EventModel::TYPE_TASK => _('Задание'),
            HM_Event_EventModel::TYPE_POLL => _('Опрос'),
            HM_Event_EventModel::TYPE_FORUM => _('Форум'),
            HM_Event_EventModel::TYPE_ECLASS => _('Вебинар'),
        ];
    }
}