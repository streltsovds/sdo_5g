<?php

class HM_Grid_FiltersList extends HM_Grid_AbstractClass
{
    protected $_filters = array();

    /**
     * @var HM_Grid_AbstractGrid
     */
    protected $_grid;

    public function __construct(HM_Grid_AbstractGrid $grid)
    {
        $this->_grid = $grid;
    }

    public function add($filters)
    {
        $this->_filters = array_merge($this->_filters, $filters);
    }

    /**
     * Добавляет автоматические коллбэки для фильтрации к столбцам с раскрывающимися списками
     *
     * @param $columns
     */
    public function addFromColumnCallbacks($columns)
    {
        $gridColumns = $this->_grid->getColumns();

        foreach ($columns as $column) {

            if (isset($gridColumns[$column]['callback'])) {

                $columnCallback = $gridColumns[$column]['callback']['function'];

                if ($columnCallback instanceof HM_Grid_ColumnCallback_AbstractList) {
                    $this->_filters[$column] = array(
                        'callback' => array(
                            'function' => array($columnCallback, 'filterCallback')
                        )
                    );
                }
            }
        }
    }

    /**
     * @return Bvb_Grid_Filters
     */
    public function getFilters()
    {
        if (empty($this->_filters)) {
            return null;
        }

        $gridFilters = new Bvb_Grid_Filters();

        foreach($this->_filters as $field => $options) {
            $gridFilters->addFilter($field, $options);
        }

        return $gridFilters;
    }
}