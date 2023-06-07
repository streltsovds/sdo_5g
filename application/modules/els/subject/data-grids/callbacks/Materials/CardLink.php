<?php

/**
 * Разные ссылки с названия материала в зависимости от его типа
 */
class HM_Subject_DataGrid_Callback_Materials_CardLink extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $title, $type, $id) = func_get_args();
        $titleColumn = $dataGrid->getColumns()['title'];

        $search = implode('/', ['module', 'controller', 'action']);
        $replace = ['subject', 'material', 'index'];

        $params = ['id' => $id, 'type' => $type];
        foreach ($params as $param => $value) {
            $replace[] = $param;
            $replace[] = $value;
        }
        $replace   = implode('/', $replace);

        $decorator = $titleColumn->getDecorator();
        $decorator = str_replace($search, $replace, $decorator);

        /*
         *  Так как преобразования в данном коллбэке изменяют декоратор,
         *  значения которого будут обрабатываться позже в _buildGrid(),
         *  отдаю нужные данные в массиве.
         */
        return ['title' => $title, 'decorator' => $decorator];
    }
}