<?php

class HM_Grid_ColumnCallback_AbstractList extends HM_Grid_ColumnCallback_Abstract
{
    protected $_columnName = '';

    protected $_indexFieldName = '';
    protected $_titleFieldName = 'name';

    protected $_cache = null;

    protected static $_filterCounter = 0;

    /**
     * @return HM_Db_Table
     */
    protected function _getTable()
    {
        return $this->getService()->getMapper()->getAdapter();
    }

    protected function _getTablePrimaryKey()
    {
        $primaryKey = $this->_getTable()->getPrimaryKey();

        if (is_array($primaryKey)) {

            if (count($primaryKey) > 1) {
                throw new Exception('В классе HM_Grid_ColumnCallback_Abstract нельзя использовать таблицы с составными ключами');
            }

            $primaryKey = array_pop($primaryKey);
        }

        return $primaryKey;


    }

    /**
     * @param HM_Grid_AbstractGrid|Bvb_Grid $hmGrid
     * @param string $columnName
     */
    public function __construct($hmGrid = null, $columnName = '')
    {
        parent::__construct($hmGrid);

        $this->_columnName = $columnName;

        if ($this->_indexFieldName === '') {
            $this->_indexFieldName = $this->_getTablePrimaryKey();
        }
    }

    /**
     * @param $ids
     * @return string
     */
    public function __invoke($ids)
    {
        $cache = $this->_getCachedItems();

        $items = array();

        $ids = array_unique(explode(',', $ids));

        foreach ($ids as $id) {
            if (isset($cache[$id])) {
                $items[] = $this->_renderItem($id, $cache[$id]);
            }
        }

        return $this->_toUnfoldingList($items, false);

    }

    protected function _getBvbGrid()
    {
        $grid = $this->_hmGrid;

        if ($grid instanceof Bvb_Grid) {
            return $grid;
        }

        if ($grid instanceof HM_Grid) {
            return $grid->getBvbGrid();
        }
    }

    /**
     * @return HM_Collection
     */
    protected function _getCachedItems()
    {
        $cache = &$this->_cache;

        if ($cache === null) {

            $rows = $this->_getBvbGrid()->getResult();

            $ids = array();

            foreach ($rows as $row) {
                $ids = array_merge($ids, explode(',', $row[$this->_columnName]));
            }

            $ids = array_unique($ids);

            $cache = $this->_loadCache($ids);

        }

        return $cache;

    }

    protected function _loadCache($ids)
    {
        $collection = $this->getService()->find($ids);

        $result = array();

        /** @var HM_Model_Abstract $model */
        foreach ($collection as $model) {

            $key = $model->getValue($this->_indexFieldName);

            $result[$key] = $model;
        }

        return $result;
    }

    /**
     * @param HM_Model_Abstract $item
     * @return mixed
     */
    protected function _getTitle($item)
    {
        return $this->_escape($item->getValue($this->_titleFieldName));
    }

    protected function _renderItem($id, $item)
    {
        return $this->_getTitle($item);
    }

    protected function _toUnfoldingList($source, $escapeItems = true)
    {
        $total = false;

        if (is_array($source) && (count($source) > 1)) {
            $total = $this->_pluralCount(count($source));
        }

        $list = (string) HM_Grid_UnfoldingList::create(array(
            'title' => $total,
            'items' => $source,
            'escapeItems' => $escapeItems,
        ));

        if (!$list) {
            return $this->_getEmptyCellContent();
        }

        return $list;

    }

    protected function _pluralCount($count)
    {
        $service = $this->getService();

        if (method_exists($service, 'pluralFormCount')) {
            return $service->pluralFormCount($count);
        }

        return $count;
    }

    protected function _getEmptyCellContent()
    {
        return _('Нет');
    }

    public function filterCallback($data)
    {
        /** @var Zend_Db_Select $select */
        $select = $data['select'];
        $search = strtolower(trim($data['value']));

        $columns = $select->getPart(Zend_Db_Select::COLUMNS);

        foreach ($columns as $column) {

            if ($column[2] !== $this->_columnName) {
                continue;
            }

            if (!($column[1] instanceof Zend_Db_Expr)) {
                throw new Exception('Столбец "'.$this->_columnName.'" должен быть экземпляром класса Zend_Db_Expr');
            }

            $matches = array();

            if (!preg_match('/GROUP_CONCAT\((?:\s*DISTINCT\s+)?([^\)]+)\)/i', (string) $column[1], $matches)) {
                throw new Exception('Выражение "'.$column[1].'" должно содержать GROUP_CONCAT');
            }

            $column = $matches[1];

            $tableAlias = 'filter_join_'.self::$_filterCounter++;

            $where  = $column.' = '.$tableAlias.'.'.$this->_getTablePrimaryKey();
            $where .= ' AND '.$this->_getFilterExpression(array(
                    'select'     => $select,
                    'tableAlias' => $tableAlias,
                    'search'     => $search
                ));

            $select->join(array($tableAlias => $this->_getTable()->getTableName()), $where, array());

            break;

        }
    }

    /**
     * В некоторых случаях потребуется переопределить
     *
     * @param $options
     *
     * @return string
     */
    protected function _getFilterExpression($options)
    {
        $tableAlias = $options['tableAlias'];
        $search     = $options['search'];
        $service    = $this->getService();

        return $service->quoteInto('LOWER('.$tableAlias.'.'.$this->_titleFieldName.') LIKE ?', '%'.$search.'%');
    }

}