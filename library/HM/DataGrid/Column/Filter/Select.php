<?php

/**
 * Фильтр для поиска по цифровому значению, зачастую это ключи выпадающих списков
 */
class HM_DataGrid_Column_Filter_Select extends HM_DataGrid_Column_Filter
{
    public function __construct(HM_DataGrid $dataGrid, array $params)
    {
        $this->value = [
            'callback' => [
                'function' => [$this, 'callback'],
                'params' => [$dataGrid, $params]
            ]];
    }

    static public function create($dataGrid, array $params = [])
    {
        $self = new self($dataGrid, $params);
        $values = isset($params['values']) ? $params['values'] : [];
        $self->setValue([
//            'render' => 'select',
            'values' => $values
        ]);

        return $self->getValue();
    }

}