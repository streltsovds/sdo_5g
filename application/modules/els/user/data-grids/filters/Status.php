<?php

/**
 *
 */
class HM_User_DataGrid_Filter_Status extends HM_DataGrid_Column_Filter_Select
{
    /**
     * @param HM_DataGrid $dataGrid
     * @param array $params
     * @return mixed
     */
    static public function create($dataGrid, array $params = [])
    {
        $self = new self($dataGrid, $params);
        $self->setValue([
            'values'   => $params['values'],
            'callback' => [
                'function' => [$self, 'callback'],
                'params'   => [$dataGrid, $params]
            ]
        ]);

        return $self->getValue();
    }

    public function callback($data)
    {
        $value  = $data['value'];
        $select = $data['select'];
        if ($value) {
            $value = $value == 1 ? 0 : 1;
            $select->where('t1.blocked = ?', $value);
        }
    }
}