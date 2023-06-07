<?php

class HM_Grid_Columns extends HM_Grid_AbstractClass
{
    protected $_columns = array();

    /** @var HM_Grid */
    protected $_grid = null;

    protected $_isEmpty = true;

    public function __construct(HM_Grid_AbstractGrid $grid)
    {
        $this->_grid = $grid;
    }

    public function addColumn($field, $options)
    {
        $type = isset($options['type']) ? $options['type'] : 'simple';

        unset($options['type']);

        $options['grid'] = $this->_grid;
        $options['field'] = $field;
        $options['columns'] = $this;

        $this->_columns[$field] = HM_Grid_SimpleColumn::factory($type, $options);

    }

    public function add($columns)
    {
        $this->_isEmpty = false;

        foreach ($columns as $field => $column) {
            $this->addColumn($field, $column);
        }
    }

    public function getFilterConfig()
    {
        $result = array();

        // дополняем конфигурацию
        /** @var HM_Grid_SimpleColumn $column */
        foreach ($this->_columns as $field => $column) {

            if (!$column->hasFilter()) {
                continue;
            }

            $result[$field] = $column->getFilter();
        }

        return $result;
    }

    public function getColumnsConfig()
    {
        $source = $this->_grid->getSource();

        $hiddenField = array(
            'hidden' => true,
        );

        $result = array();

        if ($source instanceof Bvb_Grid_Source_Zend_Select) {

            $sourceFields = $source->getColumns();

            // по умолчанию все столбцы скрыты
            foreach ($sourceFields as $field) {
                if ($field[2] === null) {
                    $result[$field[1]] = $hiddenField;
                } else {
                    $result[$field[2]] = $hiddenField;
                }
            }
        }

        if ($source instanceof HM_Grid_Source_Array) {

            $fields = $source->getFields();

            // по умолчанию все столбцы скрыты
            foreach ($fields as $field) {
                $result[$field] = $hiddenField;
            }
        }

        // дополняем конфигурацию
        /** @var HM_Grid_SimpleColumn $column */
        foreach ($this->_columns as $field => $column) {
            $result[$field] = $column->getColumnConfig();
        }

        return $result;

    }

    public function isEmpty()
    {
        return $this->_isEmpty;
    }
}