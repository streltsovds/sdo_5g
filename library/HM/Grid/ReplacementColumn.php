<?php

/**
 * Заготовка для столбцов, которые себя скрывают и размещают рядом
 * новый столбец, содержащий осмысленную информацию
 *
 * Class HM_Grid_ReplacementColumn
 */
abstract class HM_Grid_ReplacementColumn extends HM_Grid_SimpleColumn
{
    static $count = 0;

    protected $_tableAlias = '';
    protected $_table = '';
    protected $_columnAlias = '';

    /** @var Zend_Db_Select */
    protected $_select;

    protected static function _getDefaultOptions()
    {
        return array(
            'columnAlias' => '',
        );
    }

    abstract protected function _getTitleExpression();
    abstract protected function _getColumnCallback();

    public function __construct($options = array())
    {
        parent::__construct($options);

        if ($this->isHidden()) {
            return;
        }

        $field = $this->getFieldName();

        $this->_tableAlias = $tableAlias = 'entity_table_'.(self::$count++);

        $columnAlias = $this->getOption('columnAlias');

        $this->_columnAlias = $columnAlias ? $columnAlias : $tableAlias.'_'.$field;

        $source = $this->getGrid()->getSource();

        if (!($source instanceof Bvb_Grid_Source_Zend_Select)) {
            return;
        }

        $this->_select = $select = $source->getSelectObject();

        // находим месторасположение столбца в селекте
        $fieldColumnIndex = $this->_findColumnIndex($field);
        $fieldColumn = $this->_getColumn($fieldColumnIndex);

        if (!$fieldColumn) {
            return;
        }

        $tablePk = $this->_getTablePrimaryKey($source);

        $select->joinLeft(
            array($tableAlias => $this->_table),
            $tableAlias.'.'.$tablePk.' = '.$fieldColumn,
            array()
        );

        $this->_injectColumn($fieldColumnIndex, array(
            $tableAlias,
            $this->_getTitleExpression(),
            $this->_columnAlias
        ));

        $this->getOption('columns')->addColumn($this->_columnAlias, array(
            'title' => $this->getOption('title'),
            'filter' => $this->getOption('filter'),
            'order' => $this->getOption('order'),
            'hidden' => $this->getOption('hidden'),
            'callback' => $this->_getColumnCallback(),
        ));

        $this->_options['hidden'] = true;

    }

    protected function _getColumn($index)
    {
        $columns = $this->_select->getPart(Zend_Db_Select::COLUMNS);

        if (!isset($columns[$index])) {
            return false;
        }

        $column = $columns[$index];

        return $column[0].'.'.$column[1];

    }

    protected function _findColumnIndex($alias)
    {
        $columns = $this->_select->getPart(Zend_Db_Select::COLUMNS);

        foreach ($columns as $key => $column) {

            if (isset($column[2]) && $column[2] === $alias) {
                return $key;
            }

            if (isset($column[1]) && $column[1] === $alias) {
                return $key;
            }

        }

        return false;

    }

    protected function _injectColumn($position, $newColumn)
    {
        $select = $this->_select;

        $columns = $select->getPart(Zend_Db_Select::COLUMNS);

        $select->reset(Zend_Db_Select::COLUMNS);

        foreach ($columns as $key => &$existsColumn) {

            $tableCorrelationName = $existsColumn[0];

            if ($key === $position) {
                $select->columns(
                    array(
                        $newColumn[2] => $newColumn[1]
                    ),
                    $newColumn[0]
                );
            }

            if ($existsColumn[2] !== null) {
                $select->columns(
                    array(
                        $existsColumn[2] => $existsColumn[1]
                    ),
                    $tableCorrelationName
                );
            } else {
                $select->columns(
                    array(
                        $existsColumn[1]
                    ),
                    $tableCorrelationName
                );
            }
        }
    }

    protected function _getTablePrimaryKey(Bvb_Grid_Source_Zend_Select $source)
    {
        $columns = $source->getDescribeTable($this->_table);

        $keys = array();

        foreach ($columns as $column => $columnConfig) {
            if ($columnConfig['PRIMARY'] == 1) {
                $keys[] = $column;
            }
        }

        if (count($keys) !== 1) {
            return;
        }

        return $keys[0];
    }

}