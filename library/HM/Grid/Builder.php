<?php

class HM_Grid_Builder extends HM_Grid_AbstractClass
{
    /**
     * @var Bvb_Grid
     */
    protected $_grid;

    public function __construct($grid)
    {
        $this->_grid = $grid;
    }

    public function createGrid($options)
    {
        $this->_initFrontend();

        $columns  = $options['columns'];
        $filters  = $options['filters'];
        $switcher = $options['switcher'];
        /** @var HM_Grid_ActionsList $actions */
        $actions  = $options['actions'];
        $classRowCondition = $options['classRowCondition'];

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_COLUMNS);
        $this->getService('EventDispatcher')->filter($event, $columns);
        $columns = $event->getReturnValue();

        $config = Zend_Registry::get('config');

        /** @var Bvb_Grid $grid */
        $grid = $this->_grid;

        $grid->setAjax($grid->getGridId());
        $grid->setImagesUrl('/images/bvb/');
        $grid->setExport(array(
            'print',
            'excel',
            'word'
        ));
        $grid->setEscapeOutput(true);
        $grid->setAlwaysShowOrderArrows(false);

        /** @var HM_Option_OptionService $optionService */
        $optionService = $this->getService('Option');

	    $perPage = $optionService->getOption('grid_rows_per_page');
        $perPage = $perPage > 0 ? $perPage : Bvb_Grid::ROWS_PER_PAGE;

        $grid->setNumberRecordsPerPage($perPage);
        $grid->setcharEncoding($config->charset);

        if (null != $columns) {

            if (is_array($columns) && count($columns)) {

                foreach($columns as $column => $options) {
                    $grid->updateColumn($column, $options);
                }
            }
        }

        if (!empty($filters)) {
            $grid->addFilters($filters);
        } else {
            $grid->noFilters = true;
        }

        $translator = new Zend_Translate('array', APPLICATION_PATH.'/system/errors.php');
        $grid->setTranslator($translator);

        if ($grid instanceof Bvb_Grid_Deploy_Table) {

            if ($switcher) {
                $grid->setGridSwitcher($switcher);
            }
            
            $grid->disableOldActions();

            if (!$actions->isEmpty()) {
                $grid->addExtraColumns(new HM_Grid_ActionsColumn($actions));
            }

            if(count($classRowCondition)) {
                foreach($classRowCondition as $condition) {
                    $grid->setClassRowCondition(
                        $condition['condition'],
                        $condition['class'],
                        $condition['else']
                    );
                }
            }

        }

        return $grid;
    }

    protected function _initFrontend()
    {
        $view = $this->getView();

        $headScript = $view->headScript();
        $headLink   = $view->headLink();

        if (!$this->isAjaxRequest()) {

            $headLink
                ->appendStylesheet($view->serverUrl('/css/content-modules/grid.css'));

            $headScript
                ->appendFile($view->serverUrl('/js/lib/jquery/jquery.collapsorz_1.1.min.js'));

        }

        $headScript
            ->appendFile($view->serverUrl('/js/content-modules/grid.js'));

    }
}