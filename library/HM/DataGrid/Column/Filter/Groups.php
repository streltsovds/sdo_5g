<?php

/**
 *
 */
class HM_DataGrid_Column_Filter_Groups
{
    private $value;

    public function __construct(HM_DataGrid $dataGrid, array $params)
    {
        $this->value = array(
            'callback' => array(
                'function' => array($this, 'callback'),
                'params' => array($dataGrid, $params)
            )
        );
    }

    static public function create($dataGrid, $params)
    {
        $self = new self($dataGrid, $params);
        return $self->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function callback($data)
    {
        $serviceContainer = $data[0]->getServiceContainer();

        $tableName = ($data[1]['tableName'] != '') ? $data[1]['tableName'] : 'sgc';

        //Только больше 4 символов чтобы много не лезло в in
        if(strlen($data['value']) > 0){
            $result = $serviceContainer->getService('StudyGroup')->fetchAll(
                array('name LIKE LOWER(?)' => "%" . $data['value'] . "%")
            )->getList('group_id', 'name');

            if ($result) {
                $data['select']->where($tableName.'.group_id IN (?)', array_keys($result));
            } else {
                $data['select']->where($tableName.'.group_id IN (?)',0);
            }
        }
    }
}