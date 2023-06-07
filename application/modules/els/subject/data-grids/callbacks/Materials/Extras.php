<?php

/**
 * Текстовый вывод поля "Доступ для слушателей"
 */
class HM_Subject_DataGrid_Callback_Materials_Extras extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $resourceIds) = func_get_args();
        $resourceIds = explode(',', $resourceIds);

        $extras = [];
        $resourceService = $this->getService('Resource');
        if (!empty($resourceIds)) {
            $collection = $resourceService->fetchAll(['resource_id IN (?)' => $resourceIds,], 'title');
            foreach ($collection as $resource) {
                $extras[] = sprintf('<a href="%s">%s</a> ', $this->getView()->url([
                    'module' => 'subject',
                    'controller' => 'material',
                    'action' => 'index',
                    'resource_id' => $resource->resource_id,
                ]), $resource->title);
            }
        }

        return implode($extras);
    }
}