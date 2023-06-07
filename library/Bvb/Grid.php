<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @category  Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id: Grid.php 1203 2010-05-25 16:14:51Z bento.vilas.boas@gmail.com $
 * @link      http://zfdatagrid.com
 */

abstract class Bvb_Grid
{
    use HM_Grid_Trait_VueGetMarkup;

    const VERSION = '$Rev: 1203 $';
    const ROWS_PER_PAGE = 25;

    /**
     * Char encoding
     *
     * @var string
     */

    protected $_charEncoding = 'UTF-8';


    /**
     * DBRM server name
     * @var string
     */
    private $_server = null;

    /**
     * Fields order
     *
     * @var unknown_type
     */
    private $_fieldsOrder;

    /**
     * The path where we can find the library
     * Usually is lib or library
     *
     * @var unknown_type
     */
    protected $_libraryDir = 'library';


    /**
     * classes location
     *
     * @var array
     */
    //TODO set template classes from config file
    protected $_template = array();

    /**
     * templates type to be used
     *
     * @var unknown_type
     */
    protected $_templates;

    /**
     * dir and prefix list to be used when formatting fields
     *
     * @var unknown_type
     */
    protected $_formatter;

    /**
     * Number of results per page
     *
     * @var int
     */
    protected $_pagination = 15;

    /**
     * Number of results per page
     *
     * @var int
     */
    protected $_paginationOptions = array();

    /**
     * Type of export available
     *
     * @var array
     */
    protected $_export = array('pdf', 'word', 'wordx', 'excel', 'print', 'xml', 'csv', 'ods', 'odt', 'json');

    #protected $_export = array('pdf', 'word', 'wordx', 'excel', 'print', 'xml', 'csv', 'ods', 'odt', 'json','ofc');



    /**
     * All info that is not directly related to the database
     */
    protected $_info = array();


    /**
     * URL to prefix in case of routes
     * @var unknown_type
     */
    protected $_routeUrl = false;

    /**
     * Baseurl
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * Array containing the query result from table(s)
     *
     * @var array
     */
    protected $_result;

    /**
     * Total records from db query
     *
     * @var int
     */
    protected $_totalRecords;

    /**
     * Array containing field titles
     *
     * @var array
     */
    protected $_titles;

    /**
     * Array containing table(s) fields
     *
     * @var array
     */
    protected $_fields = array();


    /**
     * Filters list
     *
     * @var array
     */
    protected $_filters;

    /**
     * Filters Render
     * @var
     */
    protected $_filtersRenders;


    /**
     *
     * @var array
     */
    protected $_externalFilters = array();

    /**
     * Filters values inserted by the user
     *
     * @var array
     */
    protected $_filtersValues;

    /**
     * All information database related
     *
     * @var array
     */
    protected $_data = array();

    /**
     * URL params
     *
     * @var string
     */
    protected $_ctrlParams = array();

    /**
     * Extra fields array
     *
     * @var array
     */
    protected $_extraFields = array();

    /**
     * Final fields list (after all procedures).
     *
     *
     * @var unknown_type
     */
    protected $_finalFields;

    /**
     *Use cache or not.
     * @var bool
     */
    protected $_cache = false;

    /**
     * The field to set order by, if we have a horizontal row
     *
     * @var string
     */
    private $_fieldHorizontalRow;

    /**
     * Template instance
     *
     * @var unknown_type
     */
    protected $_temp;

    /**
     * Result untouched
     *
     * @var array
     */
    private $_resultRaw;

    /**
     * Check if all columns have been added by ->query()
     * @var bool
     */
    private $_allFieldsAdded = false;

    /**
     * If the user manually sets the query limit
     * @var int|bool
     */
    protected $_forceLimit = false;

    /**
     * Default filters to be applied
     * @var array
     * @return array
     */
    protected $_defaultFilters;

    /**
     * Instead throwing an exception,
     * we queue the field list and call this in
     * getFieldsFromQuery()
     * @var array
     */
    protected $_updateColumnQueue = array();

    /**
     * List of callback functions to apply
     * on grid deploy and ajax
     * @var $_configCallbacks
     */
    protected $_configCallbacks = array();

    /**
     * Treat hidden fields as 'remove'
     * @var bool
     */
    protected $_removeHiddenFields = false;

    /**
     * Functions to be applied on every fields before display
     * @var unknown_type
     */
    protected $_escapeFunction = 'htmlspecialchars';


    /**
     * Grid Options.
     * They can be
     * @var array
     */
    protected $_options = array();

    /**
     * Id used for multiples instances on the same page
     *
     * @var string
     */
    protected $_gridId;

    /**
     * Colspan for table
     * @var int
     */
    protected $_colspan;

    /**
     * User defined INFO for templates
     * @var array
     */
    protected $_templateParams = array();

    /**
     * To let a user know if the grid will be displayed or not
     * @var unknown_type
     */
    protected $_showsGrid = false;


    /**
     * Array of fields that should appear on detail view
     * @var unknown_type
     */
    protected $_gridColumns = null;


    /**
     * Array of columns that should appear on detail view
     * @var unknown_type
     */
    protected $_detailColumns = null;

    /**
     * If we are on detail or grid view
     * @var unknown_type
     */
    protected $_isDetail = false;


    /**
     * @var Zend_View_Interface
     */
    protected $_view;


    /**
     *
     * @var Bvb_Grid_Source_Interface
     */
    private $_source = null;

    /**
     * Last name from deploy class (table|pdf|csv|etc...)
     * @var unknown_type
     */
    private $_deployName = null;


    /**
     * What is being done with this request
     * @var unknown_type
     */
    protected $_willShow = array();


    /**
     * Print class based on conditions
     * @var array
     */
    protected $_classRowCondition = array();

    /**
     * Result to apply to every <tr> based on condition
     * @var $_classRowConditionResult array
     */
    protected $_classRowConditionResult = array();

    /**
     * Condition to apply a CSS class to a table cell <td>
     * @var unknown_type
     */
    protected $_classCellCondition = array();

    /**
     * Order setted by adapter
     * @var unknown_type
     */
    protected $_order;


    /**
     * custom translate instance
     * @var Zend_Translate
     */
    protected $_translator;

    protected $_actions = array();

    protected $_massActions = array();



    protected $_hasFixedRows = false;
    protected $_fixedRows='';

    protected $_subMassActionSelects = array();
    protected $_subMassActionFcbk = array();
    protected $_subMassActionSelectsAllowMultiple = true;

    protected $_action='';
    protected $_controller='';
    protected $_module='';

    protected $_actionsCallback = array();
    protected $_massActionsCallback = array();


    // ДЛя шоткат действия
    protected $_hasShotCut=false;
    protected $_urlShotCut='';
    protected $_shotCutField='';
    protected $_shotCutCondYes;
    protected $_shotCutCondNo;
    protected $pic_set='set';
    protected $pic_unset ='unset';

    protected $_gridSwitcher = null;

    /**
     * Backwards compatibility
     * @param $object
     * @return Bvb_Grid
     */
    public function query ($object)
    {

        if ( $object instanceof Zend_Db_Select ) {
            $this->setSource(new Bvb_Grid_Source_Zend_Select($object));
        } elseif ( $object instanceof Zend_Db_Table_Abstract ) {
            $this->setSource(new Bvb_Grid_Source_Zend_Table($object));
        } else {
            throw new Bvb_Grid_Exception('Please use the setSource() method instead');
        }

        return $this;

    }

    /**
     * Sets the source to be used
     *
     * Bvb_Grid_Source_*
     *
     * @param Bvb_Grid_Source_SourceInterface $source
     * @return Bvb_Grid
     */
    public function setSource (Bvb_Grid_Source_SourceInterface $source)
    {

        $this->_source = $source;

        $this->getSource()->setCache($this->getCache());

        $tables = $this->getSource()->getMainTable();

        $this->_data['table'] = $tables['table'];
        $this->_crudTable = $this->_data['table'];

        $fields = $this->getSource()->buildFields();

        foreach ( $fields as $key => $field ) {
            $this->updateColumn($key, $field);
        }

        $this->_allFieldsAdded = true;
        //Apply options to the fields
        $this->_applyOptionsToFields();

        return $this;
    }


    /**
     * The path where we can find the library
     * Usually is lib or library
     * @param $dir
     * @return Bvb_Grid
     */
    public function setLibraryDir ($dir)
    {
        $this->_libraryDir = $dir;
        return $this;
    }


    /**
     * Returns the actual library path
     */
    public function getLibraryDir ()
    {
        return $this->_libraryDir;
    }


    /**
     * Sets grid cache
     * @param bool|array $cache
     */
    public function setCache ($cache)
    {

        if ( $cache == false || (is_array($cache) && isset($cache['use']) && $cache['use'] == 0) ) {
            $this->_cache = array('use' => 0);
            return $this;
        }

        if ( is_array($cache) && isset($cache['use']) && isset($cache['instance']) && isset($cache['tag']) ) {
            $this->_cache = $cache;
            return $this;
        }

        return false;

    }


    /**
     * Returns actual cache params
     */
    public function getCache ()
    {
        return $this->_cache;
    }


    /**
     * Returns the actual source object
     */
    public function getSource ()
    {
        return $this->_source;
    }


    /**
     * Get db instance
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDb ()
    {
        return $this->_db;
    }

    protected function _isMssql()
    {
        $adapter = strtolower(get_class($this->getSelect()->getAdapter()));
        $adapter = str_replace("zend_db_adapter_", "", $adapter);
        $adapter = str_replace("hm_db_adapter_", "", $adapter);
        return in_array($adapter, array('mssql', 'sqlsrv', 'pdo_mssql'));
    }


    /**
     * Defines a custom Translator
     * @param Zend_Translate $translator
     */
    public function setTranslator (Zend_Translate $translator)
    {
        $this->_translator = $translator;
        return $this;
    }


    /**
     * The __construct function receives the db adapter. All information related to the
     * URL is also processed here
     *
     * @param $options
     * @param $gridId
     * @throws Bvb_Grid_Exception
     */
    public function __construct ($options, $gridId = null)
    {
        $this->_gridId = $gridId;

        if ( ! $this instanceof Bvb_Grid_Deploy_DeployInterface ) {
            throw new Bvb_Grid_Exception(get_class($this) . ' needs to implment the Bvb_Grid_Deploy_Interface');
        }

        if ( $options instanceof Zend_Config ) {
            $options = $options->toArray();
        } else if ( ! is_array($options) ) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = $options;

        //Get the controller params and baseurl to use with filters
        $this->setParams(Zend_Controller_Front::getInstance()->getRequest()->getParams());
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();


        /**
         * plugins loaders
         */
        $this->_formatter = new Zend_Loader_PluginLoader();

        //Templates loading
        if ( is_array($this->_export) ) {
            foreach ( $this->_export as $temp ) {
                $this->_templates[$temp] = new Zend_Loader_PluginLoader(array());
            }
        }

        // Add the formatter fir for fields content
        $this->addFormatterDir('Bvb/Grid/Formatter', 'Bvb_Grid_Formatter');


        $this->_filtersRenders = new Zend_Loader_PluginLoader();
        $this->addFiltersRenderDir('Bvb/Grid/Filters/Render', 'Bvb_Grid_Filters_Render');

        $deploy = explode('_', get_class($this));
        $this->_deployName = strtolower(end($deploy));
    }


    /**
     * Set view object
     *
     * @param Zend_View_Interface $view view object to use
     *
     * @return Bvb_Grid
     */
    public function setView (Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }


    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView ()
    {
        if ( null === $this->_view ) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }


    /**
     * Sets the functions to be used to apply to each value
     * before display
     * @param array $functions
     */
    public function setDefaultEscapeFunction ($functions)
    {
        $this->_escapeFunction = $functions;
        return $this;
    }


    /**
     * Returns the active escape functions
     */
    public function getDefaultEscapeFunction ()
    {
        return $this->_escapeFunction;
    }


    /**
     * Character encoding
     *
     * @param string $encoding
     * @return unknown
     */
    public function setcharEncoding ($encoding)
    {
        $this->_charEncoding = $encoding;
        return $this;
    }


    /**
     * Returns the actual encoding
     */
    public function getCharEncoding ()
    {
        return $this->_charEncoding;
    }


    /**
     * The translator
     *
     * @param string $message
     * @return string
     */
    protected function __ ($message)
    {
        if ( strlen($message) == 0 ) {
            return $message;
        }

        if ( $this->getTranslator() ) {
            return $this->getTranslator()->translate($message);
        }

        return $message;
    }


    function getTranslator ()
    {
        if ( $this->_translator instanceof Zend_Translate ) {
            return $this->_translator;
        } elseif ( Zend_Registry::isRegistered('Zend_Translate') ) {
            return Zend_Registry::get('Zend_Translate');
        }
        return false;
    }


    /**
     * Check if a string is available
     * @param unknown_type $message
     */
    protected function is__ ($message)
    {
        if ( strlen($message) == 0 ) {
            return false;
        }

        if ( $this->_translator instanceof Zend_Translate ) {
            return $this->_translator->isTranslated($message);
        } elseif ( Zend_Registry::isRegistered('Zend_Translate') ) {
            return Zend_Registry::get('Zend_Translate')->isTranslated($message);
        }

        return false;
    }


    /**
     * Use the overload function so we can return an object
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function __call ($name, $value)
    {

        if ( substr(strtolower($name), 0, 6) == 'source' ) {

            $meth = substr($name, 6);
            $meth[0] = strtolower($meth[0]);

            if ( is_object($this->getSource()) && method_exists($this->getSource(), $meth) ) {
                $this->getSource()->$meth();
                return $this;
            }
        }

        $class = $this->_deployName;


        if ( $name == 'set' . ucfirst($class) . 'GridColumns' ) {
            $this->setGridColumns($value[0]);
            return $this;
        }

        if ( $name == 'set' . ucfirst($class) . 'DetailColumns' ) {
            $this->setDetailColumns($value[0]);
            return $this;
        }

        if ( substr(strtolower($name), 0, strlen($class) + 3) == 'set' . $class ) {
            $name = substr($name, strlen($class) + 3);
            $name[0] = strtolower($name[0]);
            $this->deploy[$name] = $value[0];
            return $this;
        }

        if ( substr(strtolower($name), 0, 3) == 'set' ) {
            $name = substr($name, 3);

            if ( ! isset($value[0]) ) {
                $value[0] = null;
            }
            if ($name == 'ClassRowCondition') {
                $name = '_' . lcfirst($name);
                $this->$name = $value;
            }
            $this->__set($name, $value[0]);
        } else {
            throw new Bvb_Grid_Exception("call to unknown function $name");
        }

        return $this;
    }

    protected $tableWidth = 1200;
    protected $newHtmlTable = false;

    public function beHappy($width = 1900)
    {
        $this->newHtmlTable = true;
        $this->tableWidth = $width;
    }


    /**
     * @param string $var
     * @param string $value
     */
    public function __set ($var, $value)
    {
        $var[0] = strtolower($var[0]);
        $this->_info[$var] = $value;
        return $this;
    }


    /**
     * Update data from a column
     *
     * @param string $field
     * @param array $options
     * @param bool $force
     * @return self
     */

    public function updateColumn ($field, $options = array(), $force = false)
    {
        if ( null == $this->getSource() || ($this->_allFieldsAdded == true && ! array_key_exists($field, $this->_data['fields']) && $force == false) ) {
            /**
             * Add to the queue and call it from the getFieldsFromQuery() method
             * @var $_updateColumnQueue Bvb_Grid
             */
            if ( isset($this->_updateColumnQueue[$field]) ) {
                $this->_updateColumnQueue[$field] = array_merge($this->_updateColumnQueue[$field], $options);
            } else {
                $this->_updateColumnQueue[$field] = $options;
            }

            return $this;
        }

        if ( $this->_allFieldsAdded == false ) {

            $this->_data['fields'][$field] = $options;

        } elseif ( array_key_exists($field, $this->_data['fields']) ) {

            if ( isset($options['hRow']) && $options['hRow'] == 1 ) {
                $this->_fieldHorizontalRow = $field;
                $this->_info['hRow'] = array('field' => $field, 'title' => $options['title']);
            }

            $this->_data['fields'][$field] = array_merge($this->_data['fields'][$field], $options);

        } elseif ( $force == true ) {

            $this->_data['fields'][$field] = $options;

            if ( isset($options['hRow']) && $options['hRow'] == 1 ) {
                $this->_fieldHorizontalRow = $field;
                $this->_info['hRow'] = array('field' => $field, 'title' => $options['title']);
            }

            if ( isset($this->_updateColumnQueue[$field]) ) {
                $this->_data['fields'][$field] = array_merge($options, $this->_updateColumnQueue[$field]);
            }

        }

        return $this;
    }


    /**
     * Set option hidden=1 on several columns
     * @param $columns
     */
    public function setColumnsHidden (array $columns)
    {
        foreach ( $columns as $column ) {
            $this->updateColumn($column, array('hidden' => 1));
        }
        return $this;
    }


    /**
     * Add a new dir to look for when formating a field
     *
     * @param string $dir
     * @param string $prefix
     * @return $this
     */
    public function addFormatterDir ($dir, $prefix)
    {
        $this->_formatter->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/');
        return $this;
    }


    /**
     * Format a field
     *
     * @param  $value
     * @param  $formatter
     * @return formatted value
     */
    protected function _applyFormat ($value, $formatter)
    {
        if ( is_array($formatter) ) {
            $result = reset($formatter);
            if ( ! isset($formatter[1]) ) {
                $formatter[1] = array();
            }

            $options = (array) $formatter[1];
        } else {
            $result = $formatter;
            $options = array();
        }

        $class = $this->_formatter->load($result);


        $t = new $class($options);


        if ( ! $t instanceof Bvb_Grid_Formatter_FormatterInterface ) {
            throw new Bvb_Grid_Exception("$class must implement the Bvb_Grid_Formatter_FormatterInterface");
        }

        $return = $t->format($value);

        return $return;
    }


    /**
     * Number of records to show per page
     * @param $number
     */
    public function setPaginationInterval (array $pagination)
    {
        $this->_paginationOptions = $pagination;
        return $this;
    }


    /**
     * Number of records to show per page
     * @param $number
     */
    public function setNumberRecordsPerPage ($number = 15)
    {
        $this->_pagination = (int) $number;
        return $this;
    }


    /**
     * Default values for filters.
     * This will be applied before displaying. However the user can still remove them.
     * @param $filters
     */
    public function setDefaultFiltersValues (array $filters)
    {
        $this->_defaultFilters = array_flip($filters);
        return $this;
    }

    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Get filters values
     *
     * @return void
     */
    protected function _buildFiltersValues ()
    {

        //Build an array to know filters values
        $filtersValues = array();
        $fields  = $this->getFields();
        //print_r($fields);
        $filters = array();


        // Это добавляем, чтобы параметры из адреса не влияли на грид
        $arrayParam = array();
        $primary = $this->getSource()->getPrimaryKey($this->_data['table']);

        // Если точка есть в праймари кее
        foreach($primary as &$val){
            $explode = explode('.', $val);
            if(count($explode) > 1){
                $val = $explode[1];
            }
        }

        $primary = implode('-', $primary);

        foreach($this->_ctrlParams as $key => $val){
            if($key == $primary){
                unset($this->_ctrlParams[$key]);
            }
        }

        $default = new Zend_Session_Namespace('default');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());

         $sessionParams = $default->grid[$page][$this->_gridId]['params'];
         foreach ($sessionParams as $sessionKey => $sessionParam) {
             if (!in_array($sessionKey, $fields)) {
                 unset($sessionParams[$sessionKey]);
                 continue;
             }
             if (!isset($this->_ctrlParams[$sessionKey])) {
                 $this->_ctrlParams[$sessionKey] = $sessionParams[$sessionParam];
             }
         }

        foreach ( $this->_ctrlParams as $key => $value ) {
           // print($key."<br/>");
            if ( stripos($key, '[') ) {

                $name = explode('[', $key);
   // var_dump($name[0].$this->getGridId());
               // var_dump(in_array($name[0].$this->getGridId(), $fields));
                if ( in_array($name[0], $fields) ) {
                    $filters[$name[0]][substr($name[1], 0, - (strlen($this->getGridId())+1))] = $value;

                }


            } else {

                if ( in_array($key, $fields) ) {
                    $filters[$key] = $value;
                }elseif ( in_array(substr($key, 0, - strlen($this->getGridId())), $fields) ) {
                    if ( $this->getGridId() != '' && substr($key, - strlen($this->getGridId())) == $this->getGridId() ) {
                        $key = substr($key, 0, - strlen($this->getGridId()));
                    }
                        $filters[$key] = $value;
                }
            }
        }

        if ( count($filters)>0) {

            foreach ( $filters as $key => $value ) {
                if ( is_array($value) ) {
                    $this->setParam($key, $value);
                }
            }

            $fieldsRaw = $this->_data['fields'];
            foreach ( $filters as $key => $filter ) {

                $bHaveBuildQuery = $render = false;
                if(isset($this->_filters[$key]['render'])) {
                    $render = $this->loadFilterRender($this->_filters[$key]['render']);
                    $bHaveBuildQuery = is_callable(array($render, 'buildQuery'));
                }

//#17377 & #17823 - не ищет текст с кавычками и точками в базе
                if(!is_array($filter))
                    $filter = str_ireplace(array("%2E", "&quot;"), array(".", "\""), $filter); //html_entity_decode - опасно
                else
                    foreach($filter as $fkey=>$fvalue)
                        $filter[$fkey] = str_ireplace(array("%2E", "&quot;"), array(".", "\""), $filter[$fkey]); //html_entity_decode - опасно
//
                if ( ! is_array($filter) && (strlen($filter) == 0 || ! in_array($key, $this->_fields)) ) {
                    unset($filters[$key]);

                } elseif ( ! is_array($filter) /*&& !$bHaveBuildQuery*/) {
                    if ( isset($fieldsRaw[$key]['searchField']) ) {
                        $key = $fieldsRaw[$key]['searchField'];
                    }
                    $oldFilter = $filter;
                    if ( isset($this->_filters[$key]['transform']) && is_callable($this->_filters[$key]['transform']) ) {
                        $filter = call_user_func($this->_filters[$key]['transform'], $filter);
                    }

                    if ( isset($this->_filters[$key]['callback']) && is_array($this->_filters[$key]['callback']) ) {

                        if ( ! is_callable($this->_filters[$key]['callback']['function']) ) {
                            throw new Bvb_Grid_Exception($this->_filters[$key]['callback']['function'] . ' is not callable');
                        }

                        if ( ! isset($this->_filters[$key]['callback']['params']) || ! is_array($this->_filters[$key]['callback']['params']) ) {
                            $this->_filters[$key]['callback']['params'] = array();
                        }

                        $this->_filters[$key]['callback']['params'] = array_merge($this->_filters[$key]['callback']['params'], array('field' => $key, 'value' => $filter, 'select' => $this->getSource()->getSelectObject()));

                        $result = call_user_func($this->_filters[$key]['callback']['function'], $this->_filters[$key]['callback']['params']);
                    } elseif ( isset($this->_data['fields'][$key]['search']) && is_array($this->_data['fields'][$key]['search']) && $this->_data['fields'][$key]['search']['fulltext'] == true ) {
                        $this->getSource()->addFullTextSearch($filter, $key, $this->_data['fields'][$key]);

                    } elseif (!$bHaveBuildQuery) {
                        $op = $this->getFilterOp($key, $filter);
                        $this->getSource()->addCondition($op['filter'], $op['op'], $this->_data['fields'][$key]);
                    }

                    $filtersValues[$key] = $oldFilter;
                }

                if ($render) {
                    if ( is_array($filter)  || $bHaveBuildQuery) {
                        $cond = $render->getConditions();
                        $render->setSelect($this->getSource()->getSelectObject());

                        if (is_callable(array($render, 'hasConditions'))) {
                            if (!$render->hasConditions()) {
                                $render->setFieldName($fieldsRaw[$key]['field']);
                                $render->buildQuery($filter, $this);
                            }
                        }

                        if (is_array($filter)) {
                            foreach ( $filter as $nkey => $value ) {

                                if ( strlen($value) > 0 ) {
                                    $oldValue = $value;

                                    if(is_callable(array($render, 'transform'))){

                                        $value = $render->transform($value, $nkey);

                                    }else{
                                        $value = $render->normalize($value, $nkey);
                                    }
                                    if (!is_callable(array($render, 'hasConditions')) || $render->hasConditions()) {
                                        $this->getSource()->addCondition($value, $cond[$nkey], $this->_data['fields'][$key]);
                                    }
                                    $filtersValues[$key][$nkey] = $oldValue;

                                }

                            }
                        }
                    }
                }
            }
        }


        $this->_filtersValues = $filtersValues;

        $this->_applyExternalFilters();


        return $this;
    }

    protected function _applyExternalFilters()
    {
        if(count($this->_externalFilters)==0)
        return false;


        foreach ($this->_externalFilters as $id=>$callback)
        {
            if($this->getParam($id))
            call_user_func_array($callback,array($id,$this->getParam($id),$this->getSelect()));

            if($this->getParam($id))
            $this->_filtersValues[$id] = $this->getParam($id);
        }

    }


    /**
     * Returns the operand to be used in filters
     * This value comes from the user input
     * but can be override
     * @param $field
     * @param $filter
     */
    public function getFilterOp ($field, $filter)
    {
        $filter = urldecode($filter);
        if ( ! isset($this->_data['fields'][$field]['searchType']) ) {
            $this->_data['fields'][$field]['searchType'] = 'like';
        }

        $op = strtolower($this->_data['fields'][$field]['searchType']);

        if($filter=='ISNULL'){
               $op = 'IS NULL';
         }
        elseif ( substr(strtoupper($filter), 0, 2) == 'R:' ) {
            $op = 'REGEX';
            $filter = substr($filter, 2);
        } elseif ( strpos($filter, '<>') !== false && substr($filter, 0, 2) != '<>' ) {
            $op = 'range';
        } elseif ( substr($filter, 0, 1) == '=' ) {
            $op = '=';
            $filter = substr($filter, 1);
        } elseif ( substr($filter, 0, 2) == '>=' ) {
            $op = '>=';
            $filter = substr($filter, 2);
        } elseif ( $filter[0] == '>' ) {
            $op = '>';
            $filter = substr($filter, 1);
        } elseif ( substr($filter, 0, 2) == '<=' ) {
            $op = '<=';
            $filter = substr($filter, 2);
        } elseif ( substr($filter, 0, 2) == '<>' || substr($filter, 0, 2) == '!=' ) {
            $op = '<>';
            $filter = substr($filter, 2);
        } elseif ( $filter[0] == '<' ) {
            $op = '<';
            $filter = substr($filter, 1);
        } elseif ( $filter[0] == '*' and substr($filter, - 1) == '*' ) {
            $op = 'like';
            $filter = substr($filter, 1, - 1);
        } elseif ( $filter[0] == '*' and substr($filter, - 1) != '*' ) {
            $op = 'llike';
            $filter = substr($filter, 1);
        } elseif ( $filter[0] != '*' and substr($filter, - 1) == '*' ) {
            $op = 'rlike';
            $filter = substr($filter, 0, - 1);
        } elseif ( stripos($filter, ',') !== false ) {
            $op = 'IN';
        }

        if ( in_array($op, array('like', 'llike', 'rlike', 'REGEX')) ) {
            if ( stripos($filter, '_') !== false ) {
                if ($this->_isMssql()) {
                    $filter = str_replace('_', '[_]', $filter);
                } else {
                    $filter = str_replace('_', '\_', $filter);
                }
            }
            if ( stripos($filter, '?') !== false ) {
                $filter = str_replace('?', '_', $filter);
            }
        }

        //pr($filter);die();
        if ( isset($this->_data['fields']['searchTypeFixed']) && $this->_data['fields']['searchTypeFixed'] === true && $op != $this->_data['fields']['searchType'] ) {
            $op = $this->_data['fields']['searchType'];
        }
        return array('op' => $op, 'filter' => $filter);
    }


    /**
     * Build query.
     *
     * @return string
     */
    protected function _buildQueryOrderAndLimit ()
    {

        $start = (int) $this->getParam('start');
        $order = $this->getParam('order');
        $order1 = explode("_", $order);
        $orderf = strtoupper(end($order1));

        if ( $orderf == 'DESC' || $orderf == 'ASC' ) {
            array_pop($order1);
            $order_field = implode("_", $order1);

            $this->getSource()->buildQueryOrder($order_field, $orderf);

            if ( in_array($order_field, $this->_fieldsOrder) ) {
                $this->getSource()->buildQueryOrder($order_field, $orderf, true);
            }
        }

        if ( strlen($this->_fieldHorizontalRow) > 0 ) {
            $this->getSource()->buildQueryOrder($this->_fieldHorizontalRow, 'ASC', true);
        }

        if ( false === $this->_forceLimit ) {
            $this->getSource()->buildQueryLimit($this->getResultsPerPage(), $start);
        }
        return true;
    }


    /**
     * Returns the number of records to show per page
     */
    public function getResultsPerPage ()
    {

        $perPage = (int) $this->getParam('perPage', 0);

        if ( $perPage > 0 && array_key_exists($perPage, $this->_paginationOptions) ) {
            return $perPage;
        } else {
            return $this->_pagination;
        }

    }

    /**
     * Returns the url, without the param(s) specified
     *
     * @param array|string $situation
     * @return string
     */
    public function getUrl ($situation = '', $allowAjax = true, $unsetParams = array())
    {
        $situation = (array) $situation;

        //this array the a list of params that name changes
        //based on grid id. The id is prepended to the name
        $paramsGet = array('perPage', 'order', 'start', 'filters', 'noFilters', '_exportTo', 'add', 'edit', 'noOrder', 'comm', 'gridDetail', 'gridRemove');

        $params = $this->getAllParams();

        unset($params['treeajax']);
        if ( in_array('filters', $situation) ) {

            $fields = array_merge($this->getFields(),array_keys($this->_externalFilters));

            foreach ( $fields as $field ) {
                if ( isset($params[$field.$this->getGridId()]) ) {
                    unset($params[$field.$this->getGridId()]);
                }
            }

            foreach ( $params as $key => $value ) {
                if ( stripos($key, '[') ) {
                    $fl = explode('[', $key);
                    unset($params[$key]);

                    /*if ( in_array(rtrim($fl[0],$this->getGridId()), $fields) ) {
                        unset($params[rtrim($fl[0],$this->getGridId()).'['.$fl[1]]);
                    }*/
                }
            }

        }

        foreach ( $situation as $value ) {
            if ( in_array($value, $paramsGet) ) {
                $value = $value . $this->getGridId();
            }
            unset($params[$value]);
        }


        //print_r($params);
        $params_clean = $params;
        unset($params_clean['controller']);
        unset($params_clean['module']);
        unset($params_clean['action']);
        unset($params_clean['gridmod']);

        foreach ($unsetParams as $unsetParam) {
            unset($params_clean[$unsetParam]);
        }




        if ( is_array($this->_filters) ) {
            foreach ( $this->_filters as $key => $value ) {
                if ( isset($key['render']) ) {
                    unset($params_clean[$key]);
                }
            }
        }

        $url = '';
        foreach ( $params_clean as $key => $param ) {
//#17377 - чем дальше движемся по страницам грида, тем больше накапливается '&' в запросе к базе - уже на 2-м шаге ничегно не находим,
// несмотря на патч выше (ему на N-шаге навигации придется выполнять N-замен)
            $param = str_replace("&quot;", "\"", $param);
//
            // Apply the urldecode function to the filtros param
            if ( $key == 'filters' . $this->getGridId() ) {
                $url .= "/" . trim(htmlspecialchars($key, ENT_QUOTES)) . "/" . trim(htmlspecialchars(str_replace('/', '%2F' ,urlencode($param)), ENT_QUOTES));
            } else {
                if(!is_array($param) && $key!== 'masterOrder' . $this->getGridId() && $key!== 'slaveOrder' . $this->getGridId()){
                    $url .= "/" . $this->getView()->escape($key) . "/" . str_replace('/', '%2F' ,$this->getView()->escape($param));
                }
            }
        }

        $action = '';
        if ( isset($params['action']) ) {
            $action = "/" . $params['action'];
        }


        if ( $this->getRouteUrl() !== false ) {
            $finalUrl = $this->getRouteUrl();
        } else {

            if ( Zend_Controller_Front::getInstance()->getDefaultModule() != $params['module'] ) {
                $urlPrefix = $params['module'] . "/";
            } else {
                $urlPrefix = '';
            }

            $finalUrl = $urlPrefix . $params['controller'] . $action;
        }
        // Remove the action e controller keys, they are not necessary (in fact they aren't part of url)
        if ( array_key_exists('ajax', $this->_info) && $this->getInfo('ajax') !== false && $allowAjax == true ) {
            return $finalUrl . $url . "/gridmod/ajax";
        } else {
            return $this->_baseUrl . "/" . $finalUrl . $url;
        }
    }


    /**
     * Return variable stored in info. Return default if value is not stored.
     *
     * @param string $param
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getInfo ($param, $default = false)
    {
        if ( isset($this->_info[$param]) ) {
            return $this->_info[$param];
        } elseif ( strpos($param, ',') ) {

            $params = explode(',', $param);
            $param = array_map('trim', $params);

            $final = $this->_info;

            foreach ( $params as $check ) {
                if ( ! isset($final[$check]) ) {
                    return $default;
                }
                $final = $final[$check];
            }

            return $final;
        }

        return $default;
    }


    /**
     *
     * Build Filters. If defined put the values
     * Also check if the user wants to hide a field
     *
     *
     * @return string
     */
    protected function _buildFilters ()
    {
        $return = array();
        if ( $this->getInfo('noFilters') == 1 ) {
            return false;
        }

        $data = $this->_fields;

        $tcampos = count($data);

        foreach ( $this->_extraFields as $key => $value ) {
            if ( $value['position'] == 'left' ) {
                $return[$key] = array('type' => 'extraField', 'class' => isset($this->_template['classes']['filter']) ? $this->_template['classes']['filter'] : '', 'position' => 'left');
            }
        }

        for ( $i = 0; $i < $tcampos; $i ++ ) {

            $nf = $this->_fields[$i];

            if ( ! isset($this->_data['fields'][$nf]['search']) ) {
                $this->_data['fields'][$nf]['search'] = true;
            }

            if ( $this->_displayField($nf) ) {

                if ( is_array($this->_filters) && array_key_exists($data[$i], $this->_filters) && $this->_data['fields'][$nf]['search'] != false ) {
                    $return[] = array('type' => 'field', 'class' => isset($this->_template['classes']['filter']) ? $this->_template['classes']['filter'] : '', 'value' => isset($this->_filtersValues[$data[$i]]) ? $this->_filtersValues[$data[$i]] : '', 'field' => $data[$i]);
                } else {
                    $return[] = array('type' => 'field', 'class' => isset($this->_template['classes']['filter']) ? $this->_template['classes']['filter'] : '', 'field' => $data[$i]);
                }
            }
        }


        foreach ( $this->_extraFields as $key => $value ) {
            if ( $value['position'] == 'right' ) {
                $return[$key] = array('type' => 'extraField', 'class' => isset($this->_template['classes']['filter']) ? $this->_template['classes']['filter'] : '', 'position' => 'right');
            }
        }

        return $return;

    }


    /**
     * Checks if a field should be displayed or is setted as 'remove'
     * @param string $field
     * @return bool
     */
    protected function _displayField ($field)
    {

        if ( ! isset($this->_data['fields'][$field]['remove']) ) {
            $this->_data['fields'][$field]['remove'] = false;
        }
        if ( ! isset($this->_data['fields'][$field]['hidden']) ) {
            $this->_data['fields'][$field]['hidden'] = false;
        }

        if ( $this->_data['fields'][$field]['remove'] == 0 && (($this->_data['fields'][$field]['hidden'] == 0) || ($this->_data['fields'][$field]['hidden'] == 1 && $this->_removeHiddenFields !== true)) ) {

            return true;
        }

        return false;

    }


    /**
     *
     * @param array $fields
     * @return array
     */
    protected function _prepareReplace ($fields)
    {
        return array_map(function ($value) {return "{{{$value}}}";}, $fields);
    }


    /**
     * Build the titles with the order links (if wanted)
     *
     * @return string
     */
    protected function _buildTitles ()
    {

        $return = array();
        $url = $this->getUrl(array('order', 'start', 'comm', 'noOrder'));

        $tcampos = count($this->_fields);

        foreach ( $this->_extraFields as $key => $value ) {
            if ( $value['position'] == 'left' ) {
                $return[$key] = array('class' => $this->__(isset($value['class']) ? $value['class'] : ''),'type' => 'extraField', 'value' => $this->__(isset($value['title']) ? $value['title'] : $value['name']), 'position' => 'left');
            }
        }

        $titles = $this->_fields;

        if ( ! $this->getParam('noOrder') ) {
            $selectOrder = $this->getSource()->getSelectOrder();

            if ( count($selectOrder) == 1 ) {
                $this->setParam('order' . $this->getGridId(), $selectOrder[0] . '_' . strtoupper($selectOrder[1]));
            }
        }

        for ( $i = 0; $i < $tcampos; $i ++ ) {
            if ( $this->getParam('order') ) {
                $explode = explode('_', $this->getParam('order'));
                $name = str_replace('_' . end($explode), '', $this->getParam('order'));
                $this->_order[$name] = strtoupper(end($explode)) == 'ASC' ? 'DESC' : 'ASC';
            }

            $fieldsToOrder = $this->_resetKeys($this->_data['fields']);

            if ( isset($fieldsToOrder[$i]['orderField']) && strlen($fieldsToOrder[$i]['orderField']) > 0 ) {
                $orderFinal = $fieldsToOrder[$i]['orderField'];
            } else {
                $orderFinal = $titles[$i];
            }

            if ( is_array($this->_order) ) {
                $order = $orderFinal == key($this->_order) ? $this->_order[$orderFinal] : 'ASC';
            } else {
                $order = 'ASC';
            }

            if ( $this->_displayField($titles[$i]) ) {

                $noOrder = $this->getInfo('noOrder') ? $this->getInfo('noOrder') : '';

                if ( ($noOrder == 1) || ($this->_data['fields'][$titles[$i]]['order'] === false) ) {
//                if ( $noOrder == 1 ) {
                    $return[$titles[$i]] = array('type' => 'field', 'name' => $titles[$i], 'field' => $titles[$i], 'value' => ($this->is__($titles[$i])) ? $this->__($titles[$i]) : $this->__($this->_titles[$titles[$i]]));
                } else {
                    $return[$titles[$i]] = array('type' => 'field', 'name' => $titles[$i], 'field' => $orderFinal, 'simpleUrl' => $url, 'url' => "$url/order{$this->getGridId()}/{$orderFinal}_$order", 'value' => ($this->is__($titles[$i])) === true ? $this->__($titles[$i]) : $this->__($this->_titles[$titles[$i]]));
                }
            }
        }

        foreach ( $this->_extraFields as $key => $value ) {
            if ( $value['position'] == 'right' ) {
                $return[$key] = array('type' => 'extraField', 'value' => $this->__(isset($value['title']) ? $value['title'] : $value['name']), 'position' => 'right');
            }
        }

        $this->_finalFields = $return;


        return $return;
    }


    /**
     * Replaces {{field}} for the actual field value
     * @param  $item
     * @param  $key
     * @param  $text
     */
    protected function _replaceSpecialTags (&$item, $key, $text)
    {
        $item = str_replace($text['find'], $text['replace'], $item);
    }


    /**
     * Applies the format option to a field
     * @param $new_value
     * @param $value
     * @param $search
     * @param $replace
     */
    protected function _applyFieldFormat ($new_value, $value, $search, $replace)
    {
        if ( is_array($value) ) {
            array_walk_recursive($value, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        return $this->_applyFormat($new_value, $value);
    }


    /**
     * Applies the callback option to a field
     * @param unknown_type $new_value
     * @param unknown_type $value
     * @param unknown_type $search
     * @param unknown_type $replace
     * @param $row
     * @return mixed
     * @throws Bvb_Grid_Exception
     */
    protected function _applyFieldCallback ($new_value, $value, $search, $replace, $row)
    {

        if ( ! is_callable($value['function']) ) {
            throw new Bvb_Grid_Exception($value['function'] . ' not callable');
        }

        $appendRowToParams = !empty($value['appendRowToParams']);

        if ( isset($value['params']) && is_array($value['params']) ) {
            $toReplace = $value['params'];
            $toReplaceArray = array();
            $toReplaceObj = array();

            foreach ( $toReplace as $key => $rep ) {
                if ( is_scalar($rep) || is_array($rep) ) {
                    $toReplaceArray[$key] = $rep;
                } else {
                    $toReplaceObj[$key] = $rep;
                }
            }

        } else {

            if ($appendRowToParams) {
                return call_user_func($value['function'], $row);
            } else {
            return call_user_func($value['function']);
            }
        }

        if ( is_array($toReplace) ) {
            array_walk_recursive($toReplaceArray, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        for ( $i = 0; $i <= count($toReplace); $i ++ ) {
            if ( isset($toReplaceArray[$i]) ) {
                $toReplace[$i] = $toReplaceArray[$i];
            } elseif ( isset($toReplaceObj[$i]) ) {
                $toReplace[$i] = $toReplaceObj[$i];
            }
        }

        if ($appendRowToParams) {
            $toReplace[] = $row;
        }

        return call_user_func_array($value['function'], $toReplace);

    }


    /**
     * Applies the decorator to a fields
     * @param unknown_type $find
     * @param unknown_type $replace
     * @param unknown_type $value
     * @return mixed
     */
    protected function _applyFieldDecorator ($find, $replace, $value)
    {
        return str_replace($find, $replace, $value);
    }


    /**
     * Applies escape functions to a field
     * @param  $value
     * @return mixed
     * @throws Bvb_Grid_Exception
     */
    protected function _applyFieldEscape ($value)
    {
        if ( $this->_escapeFunction === false ) {
            return $value;
        }

        if ( ! is_callable($this->_escapeFunction) ) {
            throw new Bvb_Grid_Exception($this->_escapeFunction . ' not callable');
        }

        $value = call_user_func($this->_escapeFunction, $value);
        return $value;

    }


    /**
     * Apply escape functions to column
     * @param string $field
     * @param string $new_value
     * @return mixed
     * @throws Bvb_Grid_Exception
     */
    private function _escapeField ($field, $new_value)
    {

        if ( ! isset($this->_data['fields'][$field]['escape']) ) {
            $this->_data['fields'][$field]['escape'] = 1;
        }

        if ( ($this->_data['fields'][$field]['escape'] ? 1 : 0) == 0 ) {
            return $new_value;
        }

        if ( $this->_data['fields'][$field]['escape'] == 1 ) {
            return $this->_applyFieldEscape($new_value);
        }

        if ( ! is_callable($this->_data['fields'][$field]['escape']) ) {
            throw new Bvb_Grid_Exception($this->_data['fields'][$field]['escape'] . ' not callable');
        }

        return call_user_func($this->_data['fields'][$field]['escape'], $new_value);

    }


    /**
     * Applies the view helper to the field
     * @param  $new_value
     * @param  $value
     * @param  $search
     * @param  $replace
     * @return mixed
     * @throws ReflectionException
     */
    protected function _applyFieldHelper ($new_value, $value, $search, $replace)
    {

        if ( is_array($value) ) {
            array_walk_recursive($value, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        $name = $value['name'];
        $t = $this->getView()->getHelper($name);
        $re = new ReflectionMethod($t, $name);

        if ( isset($value['params']) && is_array($value['params']) ) {
            $new_value = $re->invokeArgs($t, $value['params']);
        } else {
            $new_value = $re->invoke($t);
        }

        return $new_value;
    }

    protected $_classRowCallBack = null;

    public function setClassRowCallback($callBack)
    {
        if (!is_callable($callBack)) {
            throw new Exception('Передан некорректный callback для генерации имени класса');
        }

        $this->_classRowCallBack = $callBack;
    }

    /**
     * The loop for the results.
     * Check the extra-fields,
     *
     * @return array
     * @throws Bvb_Grid_Exception
     * @throws ReflectionException
     */
    protected function _buildGrid ()
    {
        $return = array();

        $search = $this->_prepareReplace($this->_fields);

        $fields = $this->_fields;

        $decimalPlaces = $this->getRequest()->getParam('decimalPlaces');

        $i = 0;

        $classRowCallBack = $this->_classRowCallBack;

        $classConditional = array();
        foreach ( $this->_result as $dados ) {
            $outputToReplace = array();
            foreach ( array_combine($fields, $fields) as $key => $value ) {
                $outputToReplace[$key] = $dados[$value];
            }

            if ( in_array($this->_deployName, array('table', 'vue')) ) {

                if ( isset($this->_classRowCondition[0]) && is_array($this->_classRowCondition[0]) ) {
                    $this->_classRowConditionResult[$i] = '';

                    foreach ( $this->_classRowCondition as $key => $value ) {
                        $cond = str_replace($search, $outputToReplace, $value['condition']);
                        $final = call_user_func(
                            function() use ($cond) {
                                if ($cond) {return true;} else {return false;}
                            });
                        $this->_classRowConditionResult[$i] .= $final == true ? $value['class'] . ' ' : $value['else'] . ' ';
                    }

                } else {
                    $this->_classRowConditionResult[$i] = '';
                }

                $this->_classRowConditionResult[$i] .= ($i % 2) ? $this->_cssClasses['even'] : $this->_cssClasses['odd'];

                if ($classRowCallBack && $classRow = call_user_func($classRowCallBack, $dados)) {
                    $this->_classRowConditionResult[$i] .= ' '.$classRow;
                }

                if ( count($this->_classCellCondition) > 0 ) {
                    foreach ( $this->_classCellCondition as $key => $value ) {
                        $classConditional[$key] = '';
                        foreach ( $value as $condFinal ) {

                            $cond = str_replace($search, $outputToReplace, $condFinal['condition']);

                            $function = function () use ($cond) {
                                return eval('if (' . $cond . ') {return true;} else {return false;}');
                            };

                            $classConditional[$key] .= $function() ? $condFinal['class'] . ' ' : $condFinal['else'] . ' ';
                        }
                    }
                }

            }
            /**
             *Deal with extrafield from the left
             */

            foreach ( $this->_getExtraFields('left') as $value ) {

                $value['class'] = ! isset($value['class']) ? '' : $value['class'];

                $value['style'] = ! isset($value['style']) ? '' : $value['style'];

                if($this->_hasShotCut){
                    $search[]= '{{' . $this->_shotCutField . '}}';
                }

                $res = $this->checkShotCutCond($outputToReplace[$this->_shotCutField]);

                if ($res == true)
                {
                    $outputToReplace[$this->_shotCutField] = $this->pic_set;
                } else
                {
                    $outputToReplace[$this->_shotCutField] = $this->pic_unset;
                }

                $new_value = '';

                if ( isset($value['format']) ) {
                    $new_value = $this->_applyFieldFormat($new_value, $value['format'], $search, $outputToReplace);
                }

                $updateDecorator = null;
                if ( isset($value['callback']['function']) ) {
                    $new_value = $this->_applyFieldCallback($new_value, $value['callback'], $search, $outputToReplace, $dados);
                    if (is_array($new_value)) {
                        $updateDecorator = $new_value['decorator'];
                        $new_value       = $new_value['title'];
                    }
                }

                if ( isset($value['helper']) ) {
                    $new_value = $this->_applyFieldHelper($new_value, $value['helper'], $search, $outputToReplace);
                }

                if ( isset($value['decorator']) ) {
                    $new_value = $this->_applyFieldDecorator($search, $outputToReplace, $updateDecorator?: $value['decorator']);
                }


                $return[$i][] = array('fixType' => isset($dados['fixType']) ? $dados['fixType'] : '', 'class' => $value['class'], 'value' => $new_value, 'style' => $value['style']);

            }

            /**
             * Deal with the grid itself
             */
            $is = 0;
            foreach ( $fields as $campos ) {

                $raw_value = $new_value = $dados[$fields[$is]];

                $new_value = $this->_escapeField($fields[$is], $new_value);

                if ( isset($this->_data['fields'][$fields[$is]]['format']) ) {
                    $new_value = $this->_applyFieldFormat($new_value, $this->_data['fields'][$fields[$is]]['format'], $search, $outputToReplace);
                    $outputToReplace[$fields[$is]] = $new_value;
                }

                $updateDecorator = null;
                if ( isset($this->_data['fields'][$fields[$is]]['callback']['function'])&& !$this->_data['fields'][$fields[$is]]['hidden'] ) {
                    $new_value = $this->_applyFieldCallback($new_value, $this->_data['fields'][$fields[$is]]['callback'], $search, $outputToReplace, $dados);
                    if (is_array($new_value)) {
                        $updateDecorator = $new_value['decorator'];
                        $new_value       = $new_value['title'];
                    }
                    $outputToReplace[$fields[$is]] = $new_value;
                }


                if ( isset($this->_data['fields'][$fields[$is]]['helper']) ) {
                    $new_value = $this->_applyFieldHelper($new_value, $this->_data['fields'][$fields[$is]]['helper'], $search, $outputToReplace);
                    $outputToReplace[$fields[$is]] = $new_value;
                }


                if ( isset($this->_data['fields'][$fields[$is]]['decorator']) ) {
                    $new_value = $this->_applyFieldDecorator($search, $outputToReplace, $updateDecorator ?: $this->_data['fields'][$fields[$is]]['decorator']);
                }


                if ( $this->_displayField($fields[$is]) ) {

                    if ( isset($this->_data['fields'][$fields[$is]]['translate']) && $this->_data['fields'][$fields[$is]]['translate'] == true ) {
                        $new_value = $this->__($new_value);
                    }

                    $style = ! isset($this->_data['fields'][$fields[$is]]['style']) ? '' : $this->_data['fields'][$fields[$is]]['style'];
                    $fieldClass = isset($this->_data['fields'][$fields[$is]]['class']) ? $this->_data['fields'][$fields[$is]]['class'] : '';
                    $finalClassConditional = isset($classConditional[$fields[$is]]) ? $classConditional[$fields[$is]] : '';

                    if (isset($decimalPlaces) && is_float($new_value + 0)) { // cast to float
                        $new_value = round($new_value, $decimalPlaces);
                    }

                    $return[$i][] = array('fixType' => isset($dados['fixType']) ? $dados['fixType'] : '', 'class' => $fieldClass . ' ' . $finalClassConditional, 'value' => $new_value, 'raw_value' => $raw_value, 'field' => $this->_fields[$is], 'style' => $style);
                }

                $is ++;

            }

            /**
             * Deal with extra fields from the right
             */

            //Reset the value. This is an extra field.
            $new_value = null;
            foreach ( $this->_getExtraFields('right') as $value ) {

                $value['class'] = ! isset($value['class']) ? '' : $value['class'];
                $value['style'] = ! isset($value['style']) ? '' : $value['style'];

                $updateDecorator = null;
                if ( isset($value['callback']['function']) ) {
                    $new_value = $this->_applyFieldCallback($new_value, $value['callback'], $search, $outputToReplace, $dados);
                    if (is_array($new_value)) {
                        $updateDecorator = $new_value['decorator'];
                        $new_value       = $new_value['title'];
                    }
                }

                if ( isset($value['format']) ) {
                    $new_value = $this->_applyFieldFormat($new_value, $value['format'], $search, $outputToReplace);
                }

                if ( isset($value['helper']) ) {
                    $new_value = $this->_applyFieldHelper($new_value, $value['helper'], $search, $outputToReplace);
                }

                if ( isset($value['decorator']) ) {
                    $new_value = $this->_applyFieldDecorator($search, $outputToReplace, $updateDecorator ?: $value['decorator']);
                }

                $return[$i][] = array('fixType' => isset($dados['fixType']) ? $dados['fixType'] : '', 'class' => $value['class'], 'value' => $new_value, 'style' => $value['style']);

            }
            $i ++;
        }

        return $return;
    }


    /**
     * Get the extra fields for a give position
     *
     * @param string $position
     * @return array
     */
    protected function _getExtraFields ($position = 'left')
    {

        if ( ! is_array($this->_extraFields) ) {
            return array();
        }

        $final = array();

        foreach ( $this->_extraFields as $value ) {
            if ( $value['position'] == $position ) {
                $final[] = $value;
            }
        }

        return $final;

    }


    /**
     * Reset keys indexes
     * @param unknown_type $array
     * @return unknown
     */
    protected function _resetKeys (array $array)
    {

        $novo_array = array();
        $i = 0;
        foreach ( $array as $value ) {
            $novo_array[$i] = $value;
            $i ++;
        }
        return $novo_array;
    }


    /**
     * Apply SQL Functions
     *
     */
    protected function _buildSqlExp ($where = array())
    {


        $return = false;

        $final = $this->getInfo('sqlexp') ? $this->getInfo('sqlexp') : '';

        if ( ! is_array($final) ) {
            return false;
        }

        foreach ( $final as $key => $value ) {

            if ( ! array_key_exists($key, $this->_data['fields']) ) continue;


            if ( ! isset($value['value']) ) {
                $value['value'] = $key;
            }

            $resultExp = $this->getSource()->getSqlExp($value, $where);

            if ( ! isset($value['format']) && isset($this->_data['fields'][$key]['format']) ) {
                $resultExp = $this->_applyFormat($resultExp, $this->_data['fields'][$key]['format']);
            } elseif ( isset($value['format']) && strlen(isset($value['format'])) > 2 && false !== $value['format'] ) {
                $resultExp = $this->_applyFormat($resultExp, $value['format']);
            }

            $result[$key] = $resultExp;

        }

        if ( isset($result) && is_array($result) ) {
            $return = array();
            foreach ( $this->_finalFields as $key => $value ) {
                if ( array_key_exists($key, $result) ) {
                    $class = $this->getInfo("sqlexp,$key,class") ? ' ' . $this->getInfo("sqlexp,$key,class") : '';
                    $return[] = array('class' => $class, 'value' => $result[$key], 'field' => $key);
                } else {
                    $class = $this->getInfo("sqlexp,$key,class") ? ' ' . $this->getInfo("sqlexp,$key,class") : '';
                    $return[] = array('class' => $class, 'value' => '', 'field' => $key);
                }
            }
        }
        return $return;
    }


    /**
     * Make sure the fields exists on the database, if not remove them from the array
     *
     * @param array $fields
     */
    protected function _validateFields (array $fields)
    {

        $hidden = array();
        $show = array();
        foreach ( $fields as $key => $value ) {

            if ( ! isset($value['order']) || $value['order'] == 1 ) {
                if ( isset($value['orderField']) ) {
                    $orderFields[$key] = $value['orderField'];
                } else {
                    $orderFields[$key] = $key;
                }
            }

            if ( isset($value['title']) ) {
                $titulos[$key] = $value['title'];
            } else {
                $titulos[$key] = ucwords(str_replace('_', ' ', $key));
            }

            if ( isset($this->_data['fields'][$key]['hidden']) && $this->_data['fields'][$key]['hidden'] == 1 ) {
                $hidden[$key] = $key;
            } else {
                $show[$key] = $key;
            }

        }

        $fields_final = array();
        $lastIndex = 1;
        $norder = 0;
        foreach ( $show as $key => $value ) {

            $value = $this->_data['fields'][$value];

            if ( isset($value['position']) && (! isset($value['hidden']) || $value['hidden'] == 0) ) {

                if ( $value['position'] == 'last' ) {
                    $fields_final[($lastIndex + 100)] = $key;
                } elseif ( $value['position'] == 'first' ) {
                    $fields_final[($lastIndex - 100)] = $key;
                } else {

                    if ( $value['position'] == 'next' ) {
                        $norder = $lastIndex + 1;
                    } else {
                        $norder = (int) $value['position'];
                    }

                    if ( array_key_exists($norder, $fields_final) ) {
                        for ( $i = count($fields_final); $i >= $norder; $i -- ) {
                            $fields_final[($i + 1)] = $fields_final[$i];
                        }
                        $fields_final[$norder] = $key;
                    }

                    $fields_final[$norder] = $key;
                }

            } elseif ( ! isset($value['hidden']) || $value['hidden'] == 0 ) {

                while (true) {
                    if ( array_key_exists($lastIndex, $fields_final) ) {
                        $lastIndex ++;
                    } else {
                        break;
                    }
                }
                $fields_final[$lastIndex] = $key;
            }
        }

        ksort($fields_final);

        $fields_final = $this->_resetKeys($fields_final);

        //Put the hidden fields on the end of the array
        foreach ( $hidden as $value ) {
            $fields_final[] = $value;
        }

        $this->_fields      = $fields_final ;
        $this->_titles      = $titulos      ;
        $this->_fieldsOrder = $orderFields  ;
    }


    /**
     * Make sure the filters exists, they are the name from the table field.
     * If not, remove them from the array
     * If we get an empty array, we then create a new one with all the fields specified
     * in $this->_fields method
     *
     * @param string $filters
     */
    protected function _validateFilters ()
    {

        if ( $this->getInfo("noFilters") == 1 ) {
            return false;
        }

        $filters = null;

        if ( is_array($this->_filters) ) {
            return $this->_filters;
        } else {
            $filters = array_combine($this->_fields, $this->_fields);
        }

        return $filters;
    }


    /**
     * Build user defined filters
     */
    protected function _buildDefaultFilters ()
    {

        if ( is_array($this->_defaultFilters) && ! $this->getParam('filters') && ! $this->getParam('noFilters') ) {
            $df = array();
            foreach ( $this->_data['fields'] as $key => $value ) {

                if ( ! $this->_displayField($key) ) {
                    continue;
                }

                if ( array_key_exists($key, array_flip($this->_defaultFilters)) ) {
                    $df['filter_' . $key] = array_search($key, $this->_defaultFilters);
                } else {
                    $df['filter_' . $key] = '';
                }
            }

            $defaultFilters = $df;

            $this->setParam('filters' . $this->getGridId(), Zend_Json_Encoder::encode($defaultFilters));
        }

        return $this;
    }

    protected $_oldActionsIsDisabled = false;

    public function disableOldActions()
    {
        $this->_oldActionsIsDisabled = true;
    }

    /**
     * Done. Send the grid to the user
     *
     * @return string
     * @throws Bvb_Grid_Exception
     * @throws Zend_Exception
     */
    public function deploy ()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        if ($request->getParam('gridmod') != 'ajax' && !$this->_notUseRestoreState()) {
            $this->restoreState();
        } elseif ($request->getParam('gridmod') == 'ajax') {
        	// Сброс данных о фильтрах из сесиии при ajax-запросах, то есть "Искать", пагинация и сортировка
        	$this->flushState();
        }

        if ($this instanceof Bvb_Grid_Deploy_Table && !$this->_oldActionsIsDisabled) {

            $actions = new Bvb_Grid_Extra_Column();
            $actions->position('right')->name('actions')->title(_('Действия'))->helper()->decorator('');

            if (count($this->_actions)) {

                $actionsDecorator = '<menu class="grid-row-actions"><ul class="dropdown">';
                foreach ( $this->_actions as $action )
                {
                  // echo $test = substr($action['url'], 33, 7);
                    //if($test=='{{MID}}')
                   //print_r($this->_prepareReplace($this->_actions));
                    $actionsDecorator .= "" . "<li><a href =\"" . $action['url'] . "\">" . $action['icon'] . "</a></li>";
                    //$actionsDecorator .= "" . "<li><a href =\"" . $action['url'] . "\">" . $action['icon'] . "</a></li>";
                }
                $actionsDecorator .= '</ul></menu>';
                $actions->decorator($actionsDecorator);
            }

            if(!empty($this->_actionsCallback)){

                $actions->decorator();
                $this->_actionsCallback['params'][] = $actionsDecorator;
                $actions->callback($this->_actionsCallback);

            }

            $this->addExtraColumns($actions);
        }


        // count(false) == 1?
        if (count($this->_actions)) {
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_ACTIONS);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $this->_actions);
            $this->_actions = $event->getReturnValue();
        }

        if (! empty($this->_massActions)) {

            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_MASS_ACTIONS);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $this->_massActions);
            $this->_massActions = $event->getReturnValue();

            $this->setMassAction($this->_massActions);

            if (is_array($this->_subMassActionSelects) && count($this->_subMassActionSelects)) {
                $this->setSubMassActionSelects($this->_subMassActionSelects);
            }
        }

        if ( $this->getSource() === null ) {
            throw new Bvb_Grid_Exception('Please Specify your source');
        }

        //We need to get fields again because user may have added a few more after
        //Setting the source using $select->columns();
        $fields = $this->getSource()->buildFields();
        foreach ( $fields as $key => $field ) {
            if ( ! array_key_exists($key, $this->_data['fields']) ) $this->updateColumn($key, $field, true);
        }

        // apply additional configuration
        $this->_runConfigCallbacks();

        if ( $this->getParam('gridDetail') == 1 && $this->_deployName == 'table' && (is_array($this->_detailColumns) || $this->getParam('gridRemove')) ) {
            $this->_isDetail = true;
        }

        if ( $this->_isDetail === true && is_array($this->_detailColumns) ) {
            if ( count($this->_detailColumns) > 0 ) {

                $finalColumns = array_intersect($this->_detailColumns, array_keys($this->_data['fields']));

                foreach ( $this->_data['fields'] as $key => $value ) {
                    if ( ! in_array($key, $finalColumns) ) {
                        $this->updateColumn($key, array('remove' => 1));

                    }
                }
            }

        }


        if ( $this->_isDetail === false && is_array($this->_gridColumns) ) {
            $finalColumns = array_intersect($this->_gridColumns, array_keys($this->_data['fields']));
            foreach ( $this->_data['fields'] as $key => $value ) {
                if ( ! in_array($key, $finalColumns) ) {
                    $this->updateColumn($key, array('remove' => 1));
                }
            }

        }

        if ( $this->_isDetail == true ) {
            $result = $this->getSource()->fetchDetail($this->getPkFromUrl());
            if ( $result == false ) {
                $this->_gridSession->message = $this->__('Record Not Found');
                $this->_gridSession->_noForm = 1;
                $this->_gridSession->correct = 1;
                $this->_redirect($this->getUrl(array('comm', 'gridDetail', 'gridRemove')));
            }
        }


        if ( count($this->getSource()->getSelectOrder()) > 0 && ! $this->getParam('order') ) {
            $norder = $this->getSource()->getSelectOrder();

            if ( ! $norder instanceof Zend_Db_Expr ) {
                $this->setParam('order' . $this->getGridId(), $norder[0] . '_' . strtoupper($norder[1]));
            }
        }

        $this->_buildDefaultFilters();

        // Validate table fields, make sure they exist...
        //print_r($this->_data['fields']); exit;
        $this->_validateFields($this->_data['fields']);
        //print_r($this->_data['fields']);
        // Filters. Not required that every field as filter.
        $this->_filters = $this->_validateFilters($this->_filters);

        $this->_buildFiltersValues();
        $this->getTotalResultsCountBeforePagination();

        do {
            $reload = false;

            if ( $this->_isDetail == false ) {
                $this->_buildQueryOrderAndLimit();
            }

            if ( $this->getParam('noOrder') == 1 ) {
                $this->getSource()->resetOrder();
            }

            if ( $masterOrder = $this->getParam('masterOrder')) {
                $this->getSource()->addMasterOrder($masterOrder);
            }

            if ( $slaveOrder = $this->getParam('slaveOrder')) {
                $this->getSource()->addSlaveOrder($slaveOrder);
            }

//die($this->getSource()->getSelectObject());

            $result = $this->getSource()->execute();
//            print_r($result);

            $request = Zend_Controller_Front::getInstance()->getRequest();
            $perPage = $request->getParam('perPage');
            if ($perPage) $this->_forceLimit = $perPage;

            if ( $this->_forceLimit === false ) {
                $resultCount = $this->getSource()->getTotalRecords();
            } else {
                $resultCount = $this->_forceLimit;
                if ( count($result) < $resultCount ) {
                    $resultCount = count($result);
                }
            }

            // Total records found
            $this->_totalRecords = $resultCount;

            // Check pagination
            $start = (int) $this->getParam('start');
            if ($start && $start >= $resultCount) {
                $lastRecord = $resultCount ? $resultCount - 1 : 0;
                $start = floor($lastRecord / $this->_pagination) * $this->_pagination;
                $this->setParam('start' . $this->getGridId(), $start);
                $reload = true;
            }
        } while ($reload);

        //The result
        $this->_result = $result;

        $this->_colspan();

        if (!$this->_notUseRestoreState()) {
//            $this->saveState();
        }

        return $this;
    }

    protected function getTotalResultsCountBeforePagination()
    {
        $result = $this->getSource()->execute();
        $this->totalRecordsBeforePagination = count($result);
    }

    protected function _notUseRestoreState()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        return ($request->getParam('no-restore-state') === 'true');

    }

    protected function flushState()
    {
// Сбрасывался переключатель типа switcher, так как занулялась сессия грида на аяксе. Сброс сессия объяснялся "жуткими багами в 4.х", поэтому решил его не блокировать, а пробросить переключатель
        $default = Zend_Registry::get('session_namespace_default');
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        $switcher = $default->grid[$page][$this->getGridId()]['all'];
        $default->grid[$page][$this->getGridId()] = array ('all' => $switcher); //null
    }

    protected function restoreState()
    {
        $default = Zend_Registry::get('session_namespace_default');
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        if (isset($default->grid[$page][$this->getGridId()])) {
            if (isset($default->grid[$page][$this->getGridId()]['filters'])) {
                if (is_array($default->grid[$page][$this->getGridId()]['filters']) && count($default->grid[$page][$this->getGridId()]['filters'])) {

                    foreach($default->grid[$page][$this->getGridId()]['filters'] as $key => $value) {
                        $existingValue = $this->getParam($key);
                        if (empty($existingValue)) {
                            $this->setParam($key.$this->getGridId(), $value);
                        }
                    }
                }
            }
            if (isset($default->grid[$page][$this->getGridId()]['order'])) {
                $this->setParam('order'.$this->getGridId(), $default->grid[$page][$this->getGridId()]['order']);
            }
            if (isset($default->grid[$page][$this->getGridId()]['start'])) {
                $this->setParam('start'.$this->getGridId(), $default->grid[$page][$this->getGridId()]['start']);
            }
            if (isset($default->grid[$page][$this->getGridId()]['all'])) {
                Zend_Controller_Front::getInstance()->getRequest()->setParam('all', $default->grid[$page][$this->getGridId()]['all']);
            }
        }
    }

    public function getGridSwitcherParam()
    {
        return self::getGridSwitcherParamById($this->getGridId());
    }

    public static function &getGridSessionData($gridId)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $default = Zend_Registry::get('session_namespace_default');
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());

        if (!isset($default->grid[$page])) {
            $default->grid[$page] = array();
        }

        if (!isset($default->grid[$page][$gridId])) {
            $default->grid[$page][$gridId] = array();
        }

        return $default->grid[$page][$gridId];

    }

    public static function getGridSwitcherParamById($gridId)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $all = $request->getParam('all', NULL);

        if ($all !== NULL) {
            return $all;
        }

        $gridData = self::getGridSessionData($gridId);

        if (isset($gridData['all'])) {
            return $gridData['all'];
            }
        }

    protected function saveState()
    {
        $default = Zend_Registry::get('session_namespace_default');
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
//pr($this->_filtersValues);
//die();
        $default->grid[$page][$this->getGridId()] = array(
            'filters' => $this->_filtersValues,
            'order' => $this->getParam('order', ''),
            'start' => $this->getParam('start', '')
        );

        if (isset($default->grid[$page][$this->getGridId()]['all'])) {
            $allStateBeforeUpdate = $default->grid[$page][$this->getGridId()]['all'];

            if (null !== $this->_gridSwitcher) {
                $allStateParam = $request->getParam('all');

                if(!is_null($allStateParam)) {
                    $default->grid[$page][$this->getGridId()]['all'] = $allStateParam;
                }
                else {
                    $default->grid[$page][$this->getGridId()]['all'] = $allStateBeforeUpdate;
                }
            }
        }
    }


    /**
     * Get details about a column
     *
     * @param string $column
     * @return null|array
     */
    protected function _getColumn ($column)
    {

        return isset($this->_data['fields'][$column]) ? $this->_data['fields'][$column] : null;

    }


    /**
     *Convert Object to Array
     * @param object $object
     * @return array
     */
    protected function _object2array ($data)
    {

        if ( ! is_object($data) && ! is_array($data) ) return $data;

        if ( is_object($data) ) $data = get_object_vars($data);

        return array_map(array($this, '_object2array'), $data);

    }


    /**
     * set template locations
     *
     * @param string $path
     * @param string $prefix
     * @return unknown
     */
    public function addTemplateDir ($dir, $prefix, $type)
    {

        if ( ! isset($this->_templates[$type]) ) {
            $this->_templates[$type] = new Zend_Loader_PluginLoader();
        }

        $this->_templates[$type]->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/', $type);
        return $this;
    }


    /**
     * Define the template to be used
     *
     * @param string $template
     * @return unknown
     */
    public function setTemplate ($template, $output = 'table', $options = array())
    {

        $tmp = $options;
        $options['userDefined'] = $tmp;


        $class = $this->_templates[$output]->load($template, $output);

        if ( isset($this->_options['template'][$output][$template]) ) {
            $tpOptions = array_merge($this->_options['template'][$output][$template], $options);
        } else {
            $tpOptions = $options;
        }


        $tpInfo = array('colspan' => $this->_colspan, 'charEncoding' => $this->getCharEncoding(), 'name' => $template, 'dir' => $this->_templates[$output]->getClassPath($template, $output), 'class' => $this->_templates[$output]->getClassName($template, $output));

        $this->_temp[$output] = new $class();

        $this->_temp[$output]->options = array_merge($tpInfo, $tpOptions);

        return $this->_temp[$output];

    }


    /**
     * Add multiple columns at once
     *
     */
    public function updateColumns ()
    {

        $fields = func_get_args();

        foreach ( $fields as $value ) {

            if ( $value instanceof Bvb_Grid_Column ) {

                $value = $this->_object2array($value);
                foreach ( $value as $field ) {

                    $finalField = $field['field'];
                    unset($field['field']);
                    $this->updateColumn($finalField, $field);

                }
            }
        }

        return;
    }


    /**
     * Calculate colspan for pagination and top
     *
     * @return int
     */
    protected function _colspan ()
    {

        $totalFields = count($this->_fields);

        foreach ( $this->_data['fields'] as $value ) {
            if ( isset($value['remove']) && $value['remove'] == 1 ) {
                $totalFields --;
            } elseif ( isset($value['hidden']) && $value['hidden'] == 1 && $this->_removeHiddenFields === true ) {
                $totalFields --;
            }

            if ( isset($value['hRow']) && $value['hRow'] == 1 ) {
                $totalFields --;
            }
        }

        if ( $this->getInfo("delete,allow") == 1 ) {
            $totalFields ++;
        }

        if ( $this->getInfo("edit,allow") == 1 ) {
            $totalFields ++;
        }

        if ( is_array($this->_detailColumns) && $this->_isDetail == false ) {
            $totalFields ++;
        }

        $colspan = $totalFields + count($this->_extraFields);

        $this->_colspan = $colspan;

        return $colspan;
    }


    /**
     * Returns a field and is options
     * @param $field
     */
    public function getField ($field)
    {
        return $this->_data['fields'][$field];
    }


    /**
     *Return fields list.
     *Optional param returns also fields options
     * @param $returnOptions
     */
    public function getFields ($returnOptions = false)
    {

        if ( false !== $returnOptions ) {
            return $this->_data['fields'];
        }

        return array_keys($this->_data['fields']);

    }


    /**
     * Add filters
     *
     */
    public function addFilters ($filters)
    {

        $filtersObj = $filters;

        $filters = $this->_object2array($filters);
        $filters = $filters['_filters'];

        foreach ( $filtersObj->_filters as $key => $value ) {
            if ( isset($filters[$key]['callback']) ) {
                $filters[$key]['callback'] = $value['callback'];
            }
            if ( isset($filters[$key]['transform']) ) {
                $filters[$key]['transform'] = $value['transform'];
            }
        }

        $this->_filters = $filters;

        foreach ( $filters as $key => $filter ) {
            if ( isset($filter['searchType']) ) {
                $this->updateColumn($key, array('searchType' => $filter['searchType']));
            }
        }

        $unspecifiedFields = array_diff($this->getFields(), array_keys($this->_filters));

        foreach ( $unspecifiedFields as $value ) {
            $this->updateColumn($value, array('search' => false));
        }

        return $this;
    }


    /**
     * Add extra columns
     *
     * @return unknown
     */
    public function addExtraColumns ()
    {

        $extra_fields = func_get_args();

        if ( is_array($this->_extraFields) ) {
            $final = $this->_extraFields;
        } else {
            $final = array();
        }

        foreach ( $extra_fields as $value ) {

            if ( ! $value instanceof Bvb_Grid_Extra_Column ) {
                throw new Bvb_Grid_Exception($value . ' must be na instance of Bvb_Grid_Extra_Column');
            }

            if ( ! isset($value->_field['name']) || ! is_string($value->_field['name']) ) {
                throw new Bvb_Grid_Exception('You need to define the column name');
            }

            if ( isset($value->_field['title']) && ! is_string($value->_field['title']) ) {
                throw new Bvb_Grid_Exception('title option must be a string');
            }

            $final[$value->_field['name']] = $value->_field;

        }

        $this->_extraFields = $final;
        return $this;
    }


    /**
     * Returns the grid version
     * @return string
     */
    public static function getVersion ()
    {
        return self::VERSION;
    }


    /**
     * Return number records found
     */
    public function getTotalRecords ()
    {
        return (int) $this->_totalRecords;
    }


    /**
     * Automates export functionality
     *
     * @param $defaultClass
     * @param array $options
     * @param string $id
     * @param array $classCallbacks
     * @param array|boolean $requestData request parameters will bu used if FALSE
     * @return mixed
     */
    public static function factory ($defaultClass, $options = array(), $id = '', $classCallbacks = array(), $requestData = false)
    {
        if ( ! is_string($id) ) {
            $id = "";
        }

        if ( strpos($defaultClass, '_') === false ) {
            $defaultClass = 'Bvb_Grid_Deploy_' . ucfirst(strtolower($defaultClass));
        }

        if ( false === $requestData ) {
            $requestData = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        }

        // Защита от передачи массива вместо строки. Бывает в зенде при передаче более 1-го параметра с одинаковыми именами через url
        if (isset($requestData['_exportTo' . $id]) && is_array($requestData['_exportTo' . $id])) {
            $requestData['_exportTo' . $id] = end($requestData['_exportTo' . $id]);
        }

        if ( ! isset($requestData['_exportTo' . $id]) ) {

            // return instance of the main Bvb object, because this is not and export request
            $grid = new $defaultClass($options, $id);
            $lClass = $defaultClass;
        } else {
            $lClass = strtolower($requestData['_exportTo' . $id]);
            // support translating of parameters specifig for the export initiator class
            if ( isset($requestData['_exportFrom']) ) {
                // TODO support translating of parameters specifig for the export initiator class
                $requestData = $requestData;
            }

            // now we need to find and load the right Bvb deploy class
            $className = "Bvb_Grid_Deploy_" . ucfirst($requestData['_exportTo' . $id]); // TODO support user defined classes



            if ( Zend_Version::compareVersion('1.8.0') == 1 ) {
                if ( Zend_Loader::autoload($className) ) {
                    $grid = new $className($options);

                } else {
                    $grid = new $defaultClass($options);
                    $lClass = $defaultClass;
                }
            } else {

                if ( Zend_Loader_Autoloader::autoload($className) ) {
                    $grid = new $className($options);
                } else {
                    $grid = new $defaultClass($options);
                    $lClass = $defaultClass;
                }
            }
        }

        // add the powerfull configuration callback function
        if ( isset($classCallbacks[$lClass]) ) {
            $grid->_configCallbacks = $classCallbacks[$lClass];
        }

        if ( is_string($id) ) {
            $grid->setGridId($id);
        }
        //echo "!!!".$defaultClass; exit;
        return $grid;
    }


    /**
     * Runs callbacks
     * @return
     */
    protected function _runConfigCallbacks ()
    {
        if ( ! is_array($this->_configCallbacks) ) {
            call_user_func($this->_configCallbacks, $this);
        } elseif ( count($this->_configCallbacks) == 0 ) {
            // no callback
            return;
        } elseif ( count($this->_configCallbacks) > 1 && is_array($this->_configCallbacks[0]) ) {
            die("multi");
            // TODO maybe fix
            // ordered list of callback functions defined
            foreach ( $this->_configCallbacks as $func ) {

            }
        } else {
            // only one callback function defined
            call_user_func($this->_configCallbacks, $this);
        }
        // run it only once
        $this->_configCallbacks = array();
    }


    /**
     * Build list of exports with options
     *
     * Options:
     * caption   - mandatory
     * img       - (default null)
     * cssClass   - (default ui-icon-extlink)
     * newWindow - (default true)
     * url       - (default actual url)
     * onClick   - (default null)
     * _class    - (reserved, used internaly)
     */
    public function getExports ()
    {
        $res = array();
        foreach ( $this->_export as $name => $defs ) {
            if ( ! is_array($defs) ) {
                // only export name is passed, we need to get default option
                $name = $defs;
                $className = "Bvb_Grid_Deploy_" . ucfirst($name); // TODO support user defined classes


                if ( Zend_Loader_Autoloader::autoload($className) && method_exists($className, 'getExportDefaults') ) {
                    // learn the defualt values
                    $defs = call_user_func(array($className, "getExportDefaults"));
                } else {
                    // there are no defaults, we need at least some caption
                    $defs = array('caption' => $name);
                }

                $defs['_class'] = $className;

            }
            $res[$name] = $defs;
        }

        return $res;
    }


    /**
     * This is useful if the deploy class has no intention of using hidden fields
     * @param bool $value
     * @return $this
     */
    protected function _setRemoveHiddenFields ($value)
    {

        $this->_removeHiddenFields = (bool) $value;
        return $this;

    }


    /**
     * Adds more options to the grid
     * @param $options
     */
    public function updateOptions ($options)
    {
        if ( $options instanceof Zend_Config ) {
            $options = $options->toArray();
        } else if ( ! is_array($options) ) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = array_merge($this->options, $options);
        return $this;
    }


    /**
     * Defines options to the grid
     * @param $options
     */
    public function setOptions ($options)
    {
        $this->_options = array_merge($options, $this->_options);
        return $this;
    }


    /**
     * Apply the options to the fields
     */
    protected function _applyOptionsToFields ()
    {
        if ( isset($this->_options['fields']) && is_array($this->_options['fields']) ) {
            foreach ( $this->_options['fields'] as $field => $options ) {

                if ( isset($options['format']['function']) ) {
                    if ( ! isset($options['format']['params']) ) {
                        $options['format']['params'] = array();
                    }
                    $options['format'] = array($options['format']['function'], $options['format']['params']);
                }

                if ( isset($options['callback']) ) {

                    if ( ! isset($options['callback']['params']) ) {
                        $options['callback']['params'] = array();
                    }

                    if ( isset($options['callback']['function']) && isset($options['callback']['class']) ) {
                        $options['callback'] = array('function' => array($options['callback']['class'], $options['callback']['function']), 'params' => $options['callback']['params']);
                    } else {
                        $options['callback'] = array('function' => $options['callback']['function'], 'params' => $options['callback']['params']);
                    }

                }

                $this->updateColumn($field, $options);

            }
        }

        $deploy = explode('_', get_class($this));
        $name = strtolower(end($deploy));

        if ( isset($this->_options['deploy'][$name]) && is_array($this->_options['deploy'][$name]) ) {
            if ( method_exists($this, '_applyConfigOptions') ) {
                $this->_applyConfigOptions($this->_options['deploy'][$name]);
            } else {
                $this->deploy = $this->_options['deploy'][$name];
            }
        }

        if ( isset($this->_options['template'][$name]) && is_array($this->_options['template'][$name]) ) {
            $this->addTemplateParams($this->_options['template'][$name]);
        }

        if ( isset($this->_options['grid']['formatter']) ) {
            $this->_options['grid']['formatter'] = (array) $this->_options['grid']['formatter'];

            foreach ( $this->_options['grid']['formatter'] as $formatter ) {
                $temp = $formatter;
                $temp = str_replace('_', '/', $temp);
                $this->addFormatterDir($temp, $formatter);
            }

        }

    }


    /**
     * Sets the grid id, to allow multiples instances per page
     * @param $id
     */
    function setGridId ($id)
    {
        $this->_gridId = trim(preg_replace("/[^a-zA-Z0-9_]/",'_',$id),'_');
        return $this;
    }


    /**
     * Returns the current id.
     * ""=>emty string is a valid value
     */
    public function getGridId ()
    {
        return $this->_gridId;
    }


    /**
     *Set user defined params for templates.
     * @param array $options
     * @return unknown
     */
    public function setTemplateParams (array $options)
    {
        $this->_templateParams = $options;
        return $this;
    }


    /**
     * Set user defined params for templates.
     * @param $name
     * @param $value
     */

    public function addTemplateParam ($name, $value)
    {
        $this->_templateParams[$name] = $value;
        return $this;
    }


    /**
     * Adds user defined params for templates.
     * @param array $options
     * @return $this
     */
    public function addTemplateParams (array $options)
    {

        $this->_templateParams = array_merge($this->_templateParams, $options);
        return $this;

    }


    /**
     * Returns template info defined by the user
     */
    public function getTemplateParams ()
    {
        return $this->_templateParams;
    }


    /**
     * Reset options for column
     * @param string $column
     * @return self
     */
    public function resetColumn ($column)
    {
        $support = array();
        $support[] = $this->_data['fields']['title'];
        $support[] = $this->_data['fields']['field'];
        $this->updateColumn($column, $support);
        return $this;
    }


    /**
     * Reset options for several columns
     * @param $columns
     */
    public function resetColumns (array $columns)
    {
        foreach ( $columns as $column ) {
            $support = array();
            $support[] = $this->_data['fields']['title'];
            $support[] = $this->_data['fields']['field'];
            $this->updateColumn($column, $support);
        }

        return $this;
    }


    /**
     * Some debug info
     */
    public function debug ($returnSerialized = false)
    {
        $result = array();
        $result['fields'] = $this->getFields(true);
        $result['colspan'] = $this->_colspan();
        $result['filters'] = $this->_filters;
        $result['filtersValues'] = $this->_filtersValues;
        $result['mainSelect'] = $this->getSource()->getSelectObject()->__toString();
        $result['form'] = isset($this->_form) ? $this->_form : null;

        if ( $returnSerialized === true ) {
            return serialize($result);
        }

        return $result;
    }


    /**
     * Defines which columns will be available to user
     * @param $columns
     */
    public function setGridColumns (array $columns)
    {
        $this->_gridColumns = $columns;
        return $this;
    }


    /**
     * Adds more columns to be showed
     * @param $columns
     */
    public function addGridColumns (array $columns)
    {
        $this->_gridColumns = array_merge($this->_gridColumns, $columns);
        return $this;
    }


    /**
     * Defines which columns will be available on detail view
     * @param $columns
     */
    public function setDetailColumns ($columns = array())
    {
        $this->_detailColumns = $columns;
        return $this;
    }


    /**
     * Adds more columns that will be available on detail view
     * @param $columns
     */
    public function addDetailColumns (array $columns)
    {
        $this->_detailColumns = array_merge($this->_detailColumns, $columns);
        return $this;
    }


    /**
     * Get the list of primary keys from the URL
     *
     * @return string
     */
    public function getPkFromUrl ()
    {
        if ( ! $this->getParam('comm') ) {
            return array();
        }

        $param = $this->getParam('comm');
        $explode = explode(';', $param);
        $param = end($explode);
        $param = substr($param, 1, - 1);

        $paramF = explode('-', $param);
        $param = '';

        $returnArray = array();
        foreach ( $paramF as $value ) {
            $f = explode(':', $value);
            $returnArray[$f[0]] = $f[1];
        }
        return $returnArray;
    }


    /**
     * Let the user know what will be displayed.
     * @param $option (grid|form)
     * @return array|bool
     */
    public function willShow ()
    {
        return $this->_willShow;
    }


    /**
     * Get a param from the $this->_ctrlParams appending the grid id
     * @param $param
     * @param $default
     */
    public function getParam ($param, $default = false)
    {
        return isset($this->_ctrlParams[$param . $this->getGridId()]) ? $this->_ctrlParams[$param . $this->getGridId()] : $default;
    }


    /**
     * Returns all params received from Zend_Controller
     */
    public function getAllParams ()
    {
        return $this->_ctrlParams;
    }


    /**
     * Redirects a user to a give URL and exits
     * @param string $url
     * @param int $code
     */
    protected function _redirect ($url, $code = 302)
    {
        $response = Zend_Controller_Front::getInstance()->getResponse();
        $response->setRedirect($url, $code);
        $response->sendResponse();
        die();
    }


    /**
     * Set a param to be used by controller.
     *
     * @param $param
     * @param $value
     */
    public function setParam ($param, $value)
    {
        $this->_ctrlParams[$param] = $value;
        return $this;
    }


    /**
     * Remove a param
     * @param $param
     */
    public function removeParam ($param)
    {
        unset($this->_ctrlParams[$param]);
        return $this;
    }


    /**
     * Unsets all params received from controller
     */
    public function removeAllParams ()
    {
        $this->_ctrlParams = array();
        return $this;
    }


    /**
     * Defines a new set of params
     * @param array $params
     */
    public function setParams (array $params)
    {
        $this->_ctrlParams = $params;

        $default = new Zend_Session_Namespace('default');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());

        $default->grid[$page][$this->_gridId]['params'] = $params;

        return $this;
    }


    /**
     * Defines which export options are available
     * Ex: array('word','pdf');
     * @param array $export
     * @return Bvb_Grid
     */
    public function setExport (array $export)
    {
        $this->_export = $export;
        return $this;
    }


    /**
     * Returns the currently setted export methods
     * @return array
     */
    public function getExport ()
    {
        return $this->_export;
    }


    /**
     * Defines SQL expressions
     * @param array $exp
     * @return Bvb_Grid
     */
    public function setSqlExp (array $exp)
    {
        $this->_info['sqlexp'] = $exp;
        return $this;
    }


    function setRouteUrl ($url)
    {
        $this->_routeUrl = (string) $url;
        return $this;
    }


    function getRouteUrl ()
    {
        return $this->_routeUrl;
    }


    /**
     *
     * @param $render
     */
    function loadFilterRender ($render)
    {

        if ( is_array($render) ) {
            $toRender = key($render);
        } else {
            $toRender = $render;
        }
        $class = $this->_filtersRenders->load(ucfirst($toRender));
        $class = new $class();

        if ( is_array($render) ) {
            $re = new ReflectionMethod($class, '__construct');
            $new_value = $re->invokeArgs($class, $render[$toRender]);
        }

        return $class;
    }


    public function addFiltersRenderDir ($dir, $prefix)
    {
        $this->_filtersRenders->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/');
        return $this;
    }


    public function isExport ()
    {
        return in_array($this->getParam('_exportTo'), $this->_export);
    }


    /**
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function getSelect ()
    {
        return $this->getSource()->getSelectObject();
    }


    function addExternalFilter ($fieldId, $callback)
    {
        if ( ! is_callable($callback) ) {
            throw new Bvb_Grid_Exception($callback . ' not callable');
        }

        $this->_externalFilters[$fieldId] = $callback;

        return $this;
    }


    function removeAllExternalFilters ()
    {
        $this->_externalFilters = array();
        return $this;
    }


    function removeExternalFilter ($fieldId)
    {
        if ( isset($this->_externalFilters[$fieldId]) ) {
            unset($this->_externalFilters[$fieldId]);
        }

        return $this;
    }

    public function addAction($url, $params, $icon, $confirm = null, $target = '_self')
    {
        $confirm = (is_null($confirm) && $url['action'] == 'delete')
            ? _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            : $confirm;

        $_url = $url;
        if (is_array($url)) {

            $url['gridmod'] = '';
            $resource = sprintf('mca:%s:%s:%s', $url['module'], $url['controller'], $url['action']);
            if (!Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed($resource)) {
                return false;
            }
            $_url = $this->getView()->url($url);
            if (is_array($params) && count($params)) {
                foreach($params as $custUrl => $param) {
                    if(!is_numeric($custUrl)) {
                       $_url.= "/$param/{{{$custUrl}}}" ;
                    } else {
                      $_url.= "/$param/{{{$param}}}";
                    }
                }
            }
        }

        $this->_actions[] = array('url' => $_url, 'icon' => $icon, 'confirm' => $confirm, 'target' => $target);

        return true;

        //exit;
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function addMassAction($url, $caption, $confirm = null, $options = null)
    {
        $_url = $url;

        $request = $this->getRequest();
        $defaultModuleName = $request->getModuleName();
        $defaultControllerName = $request->getControllerName();

        if (is_array($url)) {
            unset($url['gridmod']); // = '';
            if (!isset($url['module'])) $url['module'] = $defaultModuleName;
            if (!isset($url['controller'])) $url['controller'] = $defaultControllerName;
            if (!isset($url['action'])) $url['action'] = '';
            $resource = sprintf('mca:%s:%s:%s', $url['module'], $url['controller'], $url['action']);
            if (substr($url['action'], -3) == '-by') {
                $resource = substr($resource, 0, -3);
            }

            if (!Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed($resource)) {
                   return false;
            }
            $_url = $this->getView()->url($url);
        }

        if (!count($this->_massActions)) {
            $this->_massActions[] =  array(
                'url'=> $this->getView()->url(),
                'caption'=> _('Выберите действие')
            );
        }

        $this->_massActions[] =  array(
            'url'=> $_url,
            'caption'=> $caption,
            'confirm' => $confirm,
            'options' => $options
        );
    }

    public function addSubMassActionInput($massActionUrl, $InputName, $options = array())
    {

        if (is_string($massActionUrl)) {
            $massActionUrl = array($massActionUrl);
        } else {
            $massActionUrl['gridmod'] = '';
        }

        foreach($massActionUrl as $url) {
            $this->_subMassActionInput[$url] = array(
                'name'    => $InputName,
                'options' => $options
            );
        }
    }

    public function addSubMassActionFcbk($massActionUrl, $fcbkName, $options = array())
    {
        $defaultOptions = array(
            // для совместимости со старым поведением
            'DataUrl' => $this->getView()->url(array(
                'module'     => 'user',
                'controller' => 'index',
                'action'     => 'tags'
            )),
            'MaxItems' => 10,
            'AllowNewItems' => false
        );

        if (is_string($massActionUrl)) {
            $massActionUrl = array($massActionUrl);
        } else {
            $massActionUrl['gridmod'] = '';
        }

        foreach($massActionUrl as $url) {
            $this->_subMassActionFcbk[$url] = array(
                'name'    => $fcbkName,
                'options' => array_merge($defaultOptions, $options)
            );
        }
    }

    public function addSubMassActionSelect($massActionUrl, $selectName, $selectOptions, $allowMultiple = true)
    {
        if (is_string($massActionUrl)) {
            $massActionUrl = array($massActionUrl);
        } else {
            $massActionUrl['gridmod'] = '';
        }

        foreach($massActionUrl as $url) {
            $this->_subMassActionSelects[$url] = array(
                'name'    => $selectName,
                'options' => $selectOptions
            );
        }

        $this->_subMassActionSelectsAllowMultiple = $allowMultiple;
    }

    /**
     *
     * Добавляем возможность работы с фиксированными строками
     *
     * @param unknown_type $module
     * @param unknown_type $controller
     * @param unknown_type $action
     * @param unknown_type $primary
     */
    public function addFixedRows($module, $controller, $action, $primary='')
    {

        $this->_module=$module;
        $this->_controller=$controller;
        $this->_action=$action;

        $fixedNamespace = new Zend_Session_Namespace('gridFixedRows');
        $string = $module . ":" . $controller . ":" . $action;

        if( $this->_pagination > $this->getSource()->getTotalRecords() && (!is_array($fixedNamespace->{$string}) || count($fixedNamespace->{$string}) == 0) ){
            $this->_hasFixedRows = false;
            return false;
        }

        $this->_hasFixedRows = true;

        // придется отойти от общего и перейти к частному
        $source = $this->getSource();

        $in = array();
        if(is_array($fixedNamespace->{$string}))
        {
            foreach ( $fixedNamespace->{$string} as $val )
            {
               // $this->_fixedRows .= $val['html'];
                $in[] = intval($val['id']);
            }
        }
        // Пока заглушка с левым заполнением
        if(empty($in)){
            $in=array(-9999999);
        }


        if(!empty($in) && !empty($primary)){

            $source->setFixedRows($in);
            $source->setFixedPk($primary);
        }
    }

    /**
     *
     * Очищаем строки
     * @param unknown_type $module
     * @param unknown_type $controller
     * @param unknown_type $action
     */
    public function clearFixedRows($module, $controller, $action)
    {

        $fixedNamespace = new Zend_Session_Namespace('gridFixedRows');
        $string = $module . ":" . $controller . ":" . $action;

        unset($fixedNamespace->{$string});

    }


    /**
     * Добавляем шоткат справа от галочки
     * @param unknown_type $url  Адрес по которому идем
     * @param unknown_type $field Поле, которое обрабатываем. Нужно создать новое поле в выборке $select
     * @param unknown_type $condYes Условия при которых будет set
     * @param unknown_type $condNo Условия при которых будет unset, необязательно ставить оба условия
     * @param unknown_type $pic1  Название картинки set(только имя, без расширения.(gif)),
     * @param unknown_type $pic2
     */
    public function addShotCutAction($url, $field, $condYes=array(), $condNo=array(), $pic1='', $pic2='')
    {
        $this->_hasShotCut=true;
        $this->_urlShotCut=$url;
        $this->_shotCutField=$field;
        $this->_shotCutCondYes =$condYes;
        $this->_shotCutCondNo =$condNo;
        if(!empty($pic1)){
            $this->pic_set = $pic1;
        }
     if(!empty($pic2)){
            $this->pic_unset = $pic2;
        }

        //$source = $this->getSource();
        //$source->setShotCut(true);


   }

    // Пока только ==
    // Потом сделать еще и другие типы
    protected function checkShotCutCond($value)
    {

        if (is_array($this->_shotCutCondYes))
        {
            foreach ( $this->_shotCutCondYes as $val )
            {

                if (is_array($val))
                {
                    if ($val[0] == $value)
                    {
                        return true;
                    } else
                    {
                        return false;
                    }

                } else
                {
                    if ($val == $value)
                    {
                        return true;
                    } else
                    {
                        return false;
                    }
                }

            }
        }
        if (is_array($this->_shotCutCondNo))
        {
            foreach ( $this->_shotCutCondNo as $val )
            {

                if (is_array($val))
                {
                    if ($val[0] != $value)
                    {
                        return true;
                    } else
                    {
                        return false;
                    }

                } else
                {
                    if ($val != $value)
                    {
                        return true;
                    } else
                    {
                        return false;
                    }
                }

            }
        }

        return false;

    }

    public function setGridSwitcher($gridSwitcher)
    {
        $this->_gridSwitcher = $gridSwitcher;
    }

    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Передаем массив с калбэк функцией
     *
     * @param array $callback
     */
    public function setActionsCallback($callback)
    {
        $this->_actionsCallback = $callback;
    }

    public function setMassActionsCallback($callback)
    {
        $this->_massActionsCallback = $callback;
    }

    public function setPrimaryKey($key)
    {
        $this->getSource()->setPrimaryKey($key);
    }


    public function setPrimaryKeyField($field)
    {
        $this->getSource()->setPrimaryKeyField($field);
    }

}