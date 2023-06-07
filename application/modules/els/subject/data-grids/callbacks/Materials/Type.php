<?php

/**
 * Текстовый вывод поля "Тип материала"
 */
class HM_Subject_DataGrid_Callback_Materials_Type extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $type) = func_get_args();
        $types = HM_Material_MaterialModel::getMaterialTypes();
        return $types[$type];
    }
}