<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Table.php 1203 2010-05-25 16:14:51Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Table extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
    const OUTPUT = 'table';

    /**
     * Hold definitions from configurations
     * @var array
     */
    public $deploy = array();


    protected $_deployOptions = null;

    /**
     * Information about the template
     *
     * @var array|empty
     */

    public $templateInfo;

    /**
     * If the form has been submitted
     *
     * @var bool
     */
    protected $formPost = 0;

    /**
     * Deploy options
     *
     * @var string
     */
    protected $options = array();

    /**
     * The output type
     *
     * @var string
     */
    protected $output = 'table';

    /**
     * Permission to add records
     *
     * @var array
     */
    private $allowAdd = null;

    /**
     * Permission to edit records
     *
     * @var array
     */
    private $allowEdit = null;

    /**
     * Permission to delete records
     *
     * @var array
     */
    private $allowDelete = null;

    /**
     * Override the form presentation
     *
     * @var bool
     */
    protected $_editNoForm;

    /**
     * Images url for export
     *
     * @var string
     */
    protected $_imagesUrl;

    /**
     * If we are allowed to add records to the database if we
     * show two tables (the form and the grid) or just one
     *
     * @var bool
     */
    protected $double_tables = 0;

    /**
     * Set if form validation failed
     *
     * @var bool
     */
    protected $_failedValidation;


    /**
     * Callback to be called after crud operation update
     * @var unknown_type
     */
    protected $_callbackAfterUpdate = null;

    /**
     * Callback to be called after crud operation delete
     * @var unknown_type
     */
    protected $_callbackAfterDelete = null;

    /**
     * Callback to be called after crud operation insert
     * @var unknown_type
     */
    protected $_callbackAfterInsert = null;

    /**
     * Callback to be called Before crud operation update
     * @var unknown_type
     */
    protected $_callbackBeforeUpdate = null;

    /**
     * Callback to be called Before crud operation delete
     * @var unknown_type
     */
    protected $_callbackBeforeDelete = null;

    /**
     * Callback to be called Before crud operation insert
     * @var unknown_type
     */
    protected $_callbackBeforeInsert = null;

    /**
     * Contains result of deploy() function.
     *
     * @var string
     */
    protected $_deploymentContent = null;


    /**
     * String containg the inputs ids for fitlers
     * @var array
     */
    protected $_javaScriptHelper = array();

    /**
     * Url param with the information about removing records
     *
     * @var string
     */
    protected $_comm;

    /**
     * IF user has defined mass actions operations
     * @var bool
     */
    protected $_hasMassActions = false;

    /**
     *
     * @var Zend_Form
     */
    protected $_form;

    /**
     * The table where crud operations
     * should be performed.
     * by default the table is fetched from the quaery
     * but the user can set other manually
     * @var unknown_type
     */
    protected $_crudTable;


    /**
     * Options for CRUD operations
     * @var unknown_type
     */
    protected $_crudOptions = array();


    /**
     * If data should be saved or not into the source
     * @var unknown_type
     */
    protected $_crudTableOptions = array('add' => 1, 'edit' => 1, 'delete' => 1);

    /**
     *
     * @var Zend_Session_Abstract
     */
    protected $_gridSession = null;


    /**
     * Whether to use or not key events for filters
     * @var unknown_type
     */
    protected $_useKeyEventsOnFilters = false;

    /**
     * Extra Rows
     * @var unknown_typearray
     */
    protected $_extraRows = array();

    /**
     * An array with all the parts that can be rendered
     * even
     * @var unknown_type
     */
    protected $_render = array();

    /**
     * An array with all parts that will be rendered
     * @var array
     */
    protected $_renderDeploy = array();


    /**
     * CSS classes to be used
     * @var array
     */
    protected $_cssClasses = array('odd' => 'alt odd', 'even' => 'even');

    /**
     * Definitions from form
     * May contain data being edited, what operation is beiing performed
     * @var array
     */
    protected $_formSettings = array();

    /**
     * If the user should be redirected to a confirmation page
     * before a record being deleted or if there should be a popup
     * @var bool
     */
    protected $_deleteConfirmationPage = false;

    /**
     * Shows allways all arrows in all fields
     * or only when a fiel is sorted
     *
     * @var bool
     */
    protected $_alwaysShowOrderArrows = true;


    protected $_subMassActionSelects = array();

    protected $_subMassActionFcbk = array();

    protected $_subMassActionInput = array();

    /**
     * To edit, add, or delete records, a user must be authenticated, so we instantiate
     * it here.
     *
     * @param array $data
     */
    public function __construct ($options)
    {
        $this->_setRemoveHiddenFields(true);

        parent::__construct($options);

        if ( isset($this->_options['grid']['id']) ) {
            $this->setGridId($this->_options['grid']['id']);
        }

        $this->_gridSession = new Zend_Session_Namespace('Bvb_Grid_' . $this->getGridId());
        $this->addTemplateDir('Bvb/Grid/Template/Table', 'Bvb_Grid_Template_Table', 'table');
    }



    /**
     *
     * Process all information forms related
     * First we check for permissions to add, edit, delete
     * And then the request->isPost. If true we process the data
     *
     */

    protected function _processForm ()
    {

        if ( ! $this->getSource()->hasCrud() ) {
            return false;
        }

        if ( $this->getInfo("add,allow") == 1 ) {
            $this->allowAdd = 1;
        }

        if ( $this->getInfo("delete,allow") == 1 ) {
            $this->allowDelete = 1;
        }

        if ( $this->getInfo("edit,allow") == 1 ) {
            $this->allowEdit = 1;
        }

        if ( $this->allowEdit == 1 || $this->allowDelete == 1 ) {
            $dec = $this->getParam('comm');
            $this->_comm = $dec;
        }

        /**
         * Remove if there is something to remove
         */
        if ( $this->allowDelete == 1 ) {
            self::_deleteRecord($dec);
        }


        if ( $this->allowAdd == 1 || $this->allowEdit == 1 ) {
            $opComm = $this->getParam('comm');

            $mode = $this->getParam('edit') ? 'edit' : 'add';

            $queryUrl = $this->getPkFromUrl();


            if ( ! Zend_Controller_Front::getInstance()->getRequest()->isPost() ) {

                foreach ( array_keys($this->_form->getElements()) as $element ) {

                    if ( $this->_gridSession->noErrors !== true ) {
                        if ( isset($this->_gridSession->errors[$element]) ) {
                            $this->_form->getElement($element)->setErrors($this->_gridSession->errors[$element]);
                        }
                    }
                    if ( isset($this->_gridSession->post[$element]) ) {
                        $this->_form->getElement($element)->setValue($this->_gridSession->post[$element]);
                    }
                }

                if ( $this->getParam('add') == 1 ) {
                    $this->_willShow['form'] = true;
                    $this->_willShow['formAdd'] = true;
                }

                if ( $mode == 'edit' ) {

                    $this->_willShow['form'] = true;
                    $this->_willShow['formEdit'] = true;
                    $this->_willShow['formEditId'] = $this->getPkFromUrl();

                    $r = $this->getSource()->getRecord($this->_crudTable, $this->getPkFromUrl());

                    if ( $r === false ) {
                        $this->_gridSession->message = $this->__('Record Not Found');
                        $this->_gridSession->_noForm = 1;
                        $this->_gridSession->correct = 1;
                        $this->_redirect($this->getUrl(array('comm', 'gridRemove', 'gridDetail', 'edit')));
                    }


                    if ( is_array($r) ) {
                        foreach ( $r as $key => $value ) {
                            $isField = $this->_form->getElement($key);

                            if ( isset($isField) ) {


                                if ( isset($this->_data['fields'][$key]) ) {
                                    $fieldType = $this->getSource()->getFieldType($this->_data['fields'][$key]['field']);
                                } else {
                                    $fieldType = 'text';
                                }

                                if ( isset($this->_gridSession->post) && is_array($this->_gridSession->post) ) {
                                    if ( isset($this->_gridSession->post[$key]) ) {
                                        $this->getForm()->getElement($key)->setValue($this->_gridSession->post[$key]);
                                    }
                                } else {
                                    $this->getForm()->getElement($key)->setValue($value);
                                }

                            }
                        }
                    }
                }
            }
        }



        //Check if the request method is POST
        if ( Zend_Controller_Front::getInstance()->getRequest()->isPost() && Zend_Controller_Front::getInstance()->getRequest()->getPost('zfg_form_edit' . $this->getGridId()) == 1 ) {

            if ( $this->_form->isValid($_POST) ) {

                $post = array();

                foreach ( $this->_form->getElements() as $el ) {
                    $post[$el->getName()] = is_array($el->getValue()) ? implode(',', $el->getValue()) : $el->getValue();
                }

                $addNew = false;

                if ( isset($post['saveAndAdd' . $this->getGridId()]) ) {
                     $this->_gridSession->noErrors = true;
                    $addNew = true;
                }


                unset($post['form_submit' . $this->getGridId()]);
                unset($post['zfg_form_edit' . $this->getGridId()]);
                unset($post['form_reset' . $this->getGridId()]);
                unset($post['zfg_csrf' . $this->getGridId()]);
                unset($post['saveAndAdd' . $this->getGridId()]);

                $param = Zend_Controller_Front::getInstance()->getRequest();

                // Process data
                if ( $mode == 'add' ) {

                    try {

                        $sendCall = array(&$post, $this->getSource());

                        if ( null !== $this->_callbackBeforeInsert ) {
                            call_user_func_array($this->_callbackBeforeInsert, $sendCall);
                        }


                        if ( $this->_crudTableOptions['add'] == true ) {
                            $post = array_merge($post, $this->_crudOptions['addForce']);
                            $sendCall[] = $this->getSource()->insert($this->_crudTable, $post);
                        }


                        if ( null !== $this->_callbackAfterInsert ) {
                            call_user_func_array($this->_callbackAfterInsert, $sendCall);
                        }

                        $this->_gridSession->message = $this->__('Record saved');
                        $this->_gridSession->messageOk = true;

                        if ( isset($post['saveAndAdd' . $this->getGridId()]) ) {
                            $this->_gridSession->_noForm = 0;
                        } else {
                            $this->_gridSession->_noForm = 1;
                        }

                        $this->_gridSession->correct = 1;

                        unset($this->_gridSession->post);

                        $this->_removeFormParams($post, array('add' . $this->getGridId()));

                        if($addNew ===true)
                        {
                            $finalUrl = '/add'.$this->getGridId().'/1';
                        }else{
                            $finalUrl = '';
                        }

                        $this->_redirect($this->getUrl().$finalUrl);

                    }
                    catch (Zend_Exception $e) {
                        $this->_gridSession->messageOk = FALSE;
                        $this->_gridSession->message = $this->__('Error saving record: ') . $e->getMessage();
                        $this->_gridSession->formSuccess = 0;
                        $this->_gridSession->formPost = 1;
                        $this->_gridSession->_noForm = 0;
                        $this->_gridSession->correct = 0;

                        $this->_removeFormParams($post);
                        $this->_redirect($this->getUrl());
                    }

                }

                // Process data
                if ( $mode == 'edit' ) {

                    try {

                        $sendCall = array(&$post, $this->getSource());

                        if ( null !== $this->_callbackBeforeUpdate ) {
                            call_user_func_array($this->_callbackBeforeUpdate, $sendCall);
                        }

                        if ( $this->_crudTableOptions['edit'] == true ) {
                            $post = array_merge($post, $this->_crudOptions['editForce']);
                            $queryUrl = array_merge($queryUrl, $this->_crudOptions['editAddCondition']);
                            $this->getSource()->update($this->_crudTable, $post, $queryUrl);
                        }


                        if ( null !== $this->_callbackAfterUpdate ) {
                            call_user_func_array($this->_callbackAfterUpdate, $sendCall);
                        }

                        $this->_gridSession->message = $this->__('Record saved');
                        $this->_gridSession->messageOk = true;

                        $this->_gridSession->_noForm = 1;

                        $this->_gridSession->correct = 1;

                        unset($this->_gridSession->post);

                        $this->_removeFormParams($post, array('comm' . $this->getGridId(), 'edit' . $this->getGridId()));

                        $this->_redirect($this->getUrl());

                    }
                    catch (Zend_Exception $e) {
                        $this->_gridSession->messageOk = FALSE;
                        $this->_gridSession->message = $this->__('Error updating record: ') . $e->getMessage();
                        $this->_gridSession->formSuccess = 0;
                        $this->_gridSession->formPost = 1;
                        $this->_gridSession->_noForm = 0;
                        $this->_gridSession->correct = 0;

                        $this->_removeFormParams($post);
                        $this->_redirect($this->getUrl());
                    }
                }

            } else {

                $this->_gridSession->post = $_POST;
                $this->_gridSession->errors = $this->_form->getMessages();

                $this->_gridSession->message = $this->__('Validation failed');
                $this->_gridSession->messageOk = false;
                $this->_gridSession->formSuccess = 0;
                $this->_gridSession->formPost = 1;
                $this->_gridSession->_noForm = 0;
                $this->_gridSession->correct = 0;
                $this->_removeFormParams($_POST);

                $this->_redirect($this->getUrl());
            }

        }

    }


    /**
     * Remove unneeded form inputs
     * @param  $post
     * @param  $extra
     */
    protected function _removeFormParams ($post, $extra = array())
    {

        if ( count($extra) > 0 ) $post = array_merge($post, array_combine($extra, $extra));


        foreach ( $post as $key => $value ) {
            $this->removeParam($key);
        }

        $this->removeParam('saveAndAdd' . $this->getGridId());
        $this->removeParam('form_submit' . $this->getGridId());
        $this->removeParam('zfg_form_edit' . $this->getGridId());
        $this->removeParam('zfg_csrf' . $this->getGridId());


        return true;
    }


    /**
     * Remove the record from the table
     *
     * @param string $sql
     * @param string $user
     * @return string
     */
    protected function _deleteRecord ($sql)
    {

        if ( strpos($sql, ';') === false ) {
            return false;
        }

        $param = explode(";", $sql);

        foreach ( $param as $value ) {
            $dec = explode(":", $value);
            $final[$dec[0]] = $dec[1];
        }

        if ( $final['mode'] != 'delete' ) {
            return 0;
        }

        if ( is_array($this->getInfo("delete,where")) ) {
            $condition = array_merge($this->getInfo("delete,where"), $this->getPkFromUrl());
        } else {
            $condition = $this->getPkFromUrl();
        }

        try {

            $pkParentArray = $this->getSource()->getPrimaryKey($this->_data['table']);
            $pkParent = $pkParentArray[0];

            $sendCall = array(&$condition, $this->getSource());

            if ( null !== $this->_callbackBeforeDelete ) {
                call_user_func_array($this->_callbackBeforeDelete, $sendCall);
            }

            if ( $this->_crudTableOptions['delete'] == true ) {

                $condition = array_merge($condition, $this->_crudOptions['deleteAddCondition']);
                $resultDelete = $this->getSource()->delete($this->_crudTable, $condition);
            }

            if ( $resultDelete == 1 ) {
                if ( null !== $this->_callbackAfterDelete ) {
                    call_user_func_array($this->_callbackAfterDelete, $sendCall);
                }
            }

            $this->_gridSession->messageOk = true;
            $this->_gridSession->message = $this->__('Record deleted');
            $this->_gridSession->correct = 1;

            $this->_redirect($this->getUrl('comm'));

        }
        catch (Exception $e) {
            $this->_gridSession->correct = 1;
            $this->_gridSession->messageOk = FALSE;
            $this->_gridSession->message = $this->__('Error deleting record: ') . $e->getMessage();
        }

        $this->removeParam('comm' . $this->getGridId());

        return true;
    }


    /**
     * Build the first line of the table (Not the TH )
     *
     * @return string
     */
    protected function _buildHeader ()
    {

        $url = $this->getUrl(array('comm', 'edit', 'filters', 'order'));

        $final = '';
        $final1 = '';

        if ( $this->getSource()->hasCrud() ) {
            $this->_render['addButton'] = "<div class=\"addRecord\" ><a href=\"$url/add" . $this->getGridId() . "/1\">" . $this->__('Add Record') . "</a></div>";
            if ( ($this->getInfo('doubleTables') == 0 && $this->getParam('add') != 1 && $this->getParam('edit') != 1) && $this->getSource()->getPrimaryKey($this->_data['table']) && $this->getInfo('add,allow') == 1 && $this->getInfo('add,button') == 1 && $this->getInfo('add,noButton') != 1 ) {
                $this->_renderDeploy['addButton'] = $this->_render['addButton'];
            }
        }

        /**
         * We must check if there is a filter set or an order, to show the extra th on top
         */


        if ( count($this->_filters)>0 && ($this->getInfo('noOrder')!=1 && $this->getInfo('noFilters')!=1 ) ) {

            $url = $this->getUrl('filters', 'nofilters');
            $url2 = $this->getUrl(array('order', 'noOrder'));
            $url3 = $this->getUrl(array('filters', 'order', 'noFilters', 'noOrder'));

            if ( is_array($this->_defaultFilters) ) {
                $url .= '/nofilters'.$this->getGridId().'/1';
                $url3 .= '/nofilters'.$this->getGridId().'/1';
            }

            if ( is_array($this->getSource()->getSelectOrder()) ) {

                $url3 .= '/noOrder'.$this->getGridId().'/1';
                $url2 .= '/noOrder'.$this->getGridId().'/1';
            }

            $this->_temp['table']->hasExtraRow = 1;

            //Filters and order
            if ( $this->getParam('order') && ! $this->getParam('noOrder') && count($this->_filtersValues)>0) {
                if ( $this->getInfo("ajax") !== false ) {

                    $final1 = "<button href='gridAjax(\"{$this->getInfo("ajax")}\"," . json_encode($url) . ")'>" . $this->__('Remove Filters') . "</button><button onclick='gridAjax(\"{$this->getInfo("ajax")}\",".json_encode($url2).")'>" . $this->__('Remove Order') . "</button><button onclick='gridAjax(\"{$this->_info['ajax']}\"," . json_encode($url3) . ")'>" . $this->__('Remove Filters and Order') . "</button>";

                } else {
                    $final1 = "<button onclick='window.location=".json_encode($url)."'>" . $this->__('Remove Filters') . "</button><button onclick='window.location=".json_encode($url2)."'>" . $this->__('Remove Order') . "</button><button onclick='window.location=".json_encode($url3)."'>" . $this->__('Remove Filters and Order') . "</button>";
                }
                //Only filters
            } elseif (  (! $this->getParam('order') || $this->getParam('noOrder')) && count($this->_filtersValues)>0 ) {

                if ( $this->getInfo("ajax") !== false ) {

                    $final1 = "<button onclick='gridAjax(\"{$this->getInfo("ajax")}\"," . json_encode($url) . ")'>" . $this->__('Remove Filters') . "</button>";

                } else {
                    $final1 = "<button onclick='window.location=".json_encode($url)."'>" . $this->__('Remove Filters') . "</button>";
                }

            //Only order
            } elseif ( count($this->_filtersValues)==0 && ($this->getParam('order') && ! $this->getParam('noOrder') && $this->getInfo('noOrder') != 1) ) {

                if ( $this->getInfo("ajax") !== false ) {
                    $final1 = "<button onclick='gridAjax(\"{$this->getInfo("ajax")}\"," . json_encode($url2) . ")'>" . $this->__('Remove Order') . "</button>";
                } else {
                    $final1 = "<button onclick='window.location=".json_encode($url2)."'>" . $this->__('Remove Order') . "</button>";
                }
            }

            //Replace values
            if (  ( $this->getParam('noFilters') != 1 && $this->getInfo('noOrder') != 1) && ($this->getParam('add')!=1 && $this->getParam('edit')!=1) ) {


                if ( strlen($final1) > 5 || $this->getUseKeyEventsOnFilters() ==false ) {

                    if ( $this->getUseKeyEventsOnFilters() === false ) {
                        $final1 .= "<button onclick=\"" . $this->getGridId() . "gridChangeFilters(1)\">" . $this->__('Apply Filter') . "</button>";
                    }

                    $this->_render['extra'] = str_replace("{{value}}", $final1, $this->_temp['table']->extra());
                    $this->_renderDeploy['extra'] = str_replace("{{value}}", $final1, $this->_temp['table']->extra());

                }


            }


        //close cycle
        }

        return;
    }


    /**
     *
     * Build filters.
     *
     * We receive the information from an array
     * @param array $filters
     * @return unknown
     */
    protected function _buildFiltersTable ($filters)
    {
        //There are no filters.
        if ( ! is_array($filters) ) {
            $this->_temp['table']->hasFilters = 0;
            return '';
        }
        //Start the template
        $grid = $this->_temp['table']->filtersStart();

        $count = 1;
        foreach ( $filters as $filter ) {

            //Check extra fields
            if ( $filter['type'] == 'extraField' && $filter['position'] == 'left' ) {
                //Replace values
                $filterValue = isset($filter['value']) ? $filter['value'] : '';

                $grid .= str_replace('{{value}}', $filterValue . '&nbsp;', $this->_temp['table']->filtersLoop());
            }

            $hRowField = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

            //Check if we have an horizontal row
            if ( (isset($filter['field']) && $filter['field'] != $hRowField && $this->getInfo('hRow', 'title')) || ! $this->getInfo('hRow', 'title') ) {

                if ( $filter['type'] == 'field' ) {
                    //Replace values
                    $grid .= str_replace('{{value}}', $this->_formatField($filter['field']), $this->_temp['table']->filtersLoop());
                }
            }

            //Check extra fields from the right
            if ( $filter['type'] == 'extraField' && $filter['position'] == 'right' ) {
                 if ($count == count($filters)) {
                     $filter['value'] = isset($filter['value'])? $filter['value']:"<button onclick=\"" . $this->getGridId() . "gridChangeFilters(1)\">" . $this->__('Apply Filter') . "</button>";
                 }
                 $grid .= str_replace('{{value}}', $filter['value'], $this->_temp['table']->filtersLoop());
            }
            $count++;
        }

        //Close template
        $grid .= $this->_temp['table']->filtersEnd();

        return $grid;
    }


    /**
     * Build Table titles.
     *
     * @param array $titles
     * @return string
     */
    protected function _buildTitlesTable ($titles)
    {
        $orderField = null;
        if ( is_array($this->_order) ) {
            //We must now the field that is being ordered. So we can grab the image
            $order = array_keys($this->_order);
            $order2 = array_keys(array_flip($this->_order));

            //The field that is being ordered
            $orderField = $order[0];

            //The opposite order
            $order = strtolower($order2[0]);
        }

        //Lets get the images for defining the order
        $images = $this->_temp['table']->images($this->getImagesUrl());

        //Initiate titles template
        $grid = $this->_temp['table']->titlesStart();

        if ( $orderField === null ) {
            //Lets get the default order using in the query (Zend_Db)
            $queryOrder = $this->getSource()->getSelectOrder();

            if ( count($queryOrder) > 0 ) {
                $order = strtolower($queryOrder[1]) == 'asc' ? 'desc' : 'asc';
                $orderField = $queryOrder[0];
            }
        }

        if ( $this->getParam('noOrder') ) {
            $orderField = null;
        }

        foreach ( $titles as $titleName => $title ) {
            if (!$titleName) $titleName = ''; else $titleName = 'grid-'.$titleName;
            //deal with extra field and template
            if ( $title['type'] == 'extraField' && $title['position'] == 'left' ) {
                $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($title['value'], $titleName, $title['class']), $this->_temp['table']->titlesLoop());
            }

            $hRowTitle = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

            if ( (isset($title['field']) && $title['field'] != $hRowTitle && $this->getInfo("hRow,title")) || ! $this->getInfo("hRow,title") ) {

                if ( $title['type'] == 'field' ) {


                    $noOrder = $this->getInfo("noOrder") ? $this->getInfo("noOrder") : '';

                    if ( $noOrder == 1 ) {
                        //user set the noOrder(1) method
                        $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($this->__($title['value']), $titleName, $this->__($title['class'])), $this->_temp['table']->titlesLoop());

                    } else {

                        if ( ! isset($this->_data['fields'][$title['field']]['order']) ) {
                            $this->_data['fields'][$title['field']]['order'] = true;
                        }

                        if ( $this->getAlwaysShowOrderArrows() === false ) {
                            $imgF = explode('_', $this->getParam('order'));
                            $checkOrder = str_replace('_' . end($imgF), '', $this->getParam('order'));

                            if ( in_array(strtolower(end($imgF)), array('asc', 'desc')) && $checkOrder == $title['field'] ) {
                                $imgFinal = $images[strtolower(end($imgF))];
                            } else {
                                $imgFinal = '';
                            }
                        }

                        if ( $this->getInfo("ajax") !== false ) {

                            $sortable = !isset($this->_data['fields'][$title['field']]['sortable']) || $this->_data['fields'][$title['field']]['sortable'];
                            if($sortable) {

                            if ( $this->getAlwaysShowOrderArrows() === true ) {
                                $link1 = "<a  href='javascript:gridAjax(\"{$this->getInfo("ajax")}\",".json_encode($title['simpleUrl'].'/order'.$this->getGridId().'/'.$title['field'].'_DESC').")'>{$images['desc']}</a>";
                                $link2 = "<a  href='javascript:gridAjax(\"{$this->getInfo("ajax")}\",".json_encode($title['simpleUrl'].'/order'.$this->getGridId().'/'.$title['field'].'_ASC').")'>{$images['asc']}</a>";

                                if ( ($orderField == $title['field'] && $order == 'asc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                    $link1 = '';
                                }

                                if ( ($orderField == $title['field'] && $order == 'desc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                    $link2 = '';
                                }

                                $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($link2 . $title['value'] . $link1, $titleName, $title['class']), $this->_temp['table']->titlesLoop());
                            } else {
                                $grid .= !empty($title['url']) ?
                                    str_replace(array('{{value}}', '{{title}}', '{{class}}'), array("<a href='javascript:gridAjax(\"{$this->getInfo('ajax')}\"," . json_encode($title['url']) . ")'>" . $title['value'] . $imgFinal . "</a>", $titleName, $title['class']), $this->_temp['table']->titlesLoop()) :
                                    str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($title['value'], $titleName, $title['class']), $this->_temp['table']->titlesLoop());
                            }
                            }
                            else {
                                    $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($title['value'], $titleName, $title['class']), $this->_temp['table']->titlesLoop());
                            }
                        } else {
                            //Replace values in the template
                            if ( ! array_key_exists('url', $title) ) {
                                $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($title['value'], $titleName, $title['class']), $this->_temp['table']->titlesLoop());
                            } else {

                                if ( $this->getAlwaysShowOrderArrows() === true ) {

                                    $link1 = "<a  href='" . $title['simpleUrl'] . "/order{$this->getGridId()}/{$title['field']}_DESC'>{$images['desc']}</a>";
                                    $link2 = "<a  href='" . $title['simpleUrl'] . "/order{$this->getGridId()}/{$title['field']}_ASC'>{$images['asc']}</a>";

                                    if ( ($orderField == $title['field'] && $order == 'asc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                        $link1 = '';
                                    }

                                    if ( ($orderField == $title['field'] && $order == 'desc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                        $link2 = '';
                                    }

                                    $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($link2 . $title['value'] . $link1, $titleName, $title['class']), $this->_temp['table']->titlesLoop());

                                } else {

                                    $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array("<a href='" . $title['url'] . "'>" . $title['value'] . $imgFinal . "</a>", $titleName, $title['class']), $this->_temp['table']->titlesLoop());

                                }


                            }
                        }
                    }
                }
            }

            //Deal with extra fields
            if ( $title['type'] == 'extraField' && $title['position'] == 'right' ) {
                $grid .= str_replace(array('{{value}}', '{{title}}', '{{class}}'), array($title['value'],$titleName,$title['class']), $this->_temp['table']->titlesLoop());
            }

        }

        //End template
        $grid .= $this->_temp['table']->titlesEnd();

        return $grid;

    }

    public function getFieldsInfo()
    {
        $result = array();

        foreach ($this->_data['fields'] as $key => &$field) {

            if (!isset($field['field'])) {
                continue;
            }

            if (isset($field['width'])) {
                $result[] = array(
                    'code' => $key,
                    'width' => $field['width']
                );
            } elseif (empty($field['hidden'])) {
                $result[] = array(
                    'code' => $key,
                    'width' => 'auto'
                );
            }
        }

        return $result;
    }


    /**
     * Build the table
     *
     * @param array $grids | db results
     * @return unknown
     */
    protected function _buildGridTable ($grids)
    {
        $i = 0;
        $grid = '';

        //We have an extra td for the text to remove filters and order
        if ( $this->getParam('filters') || $this->getParam('order') ) {
            $i ++;
        }

        if ( $this->getInfo("hRow,title") && $this->_totalRecords > 0 ) {

            $bar = $grids;
            $hbar = trim($this->getInfo("hRow,field"));
            $p = 0;

            foreach ( $grids[0] as $value ) {
                if ( isset($value['field']) && $value['field'] == $hbar ) {
                    $hRowIndex = $p;
                }
                $p ++;
            }
            $aa = 0;
        }

        $aa = 0;
        $class = 0;
        $fi = array();
        foreach ( $grids as $value ) {

            unset($fi);
            // decorators
            $search = $this->_finalFields;
            foreach ( $search as $key => $final ) {
                if ( $final['type'] == 'extraField' ) {
                    unset($search[$key]);
                }
            }

            $search = array_keys($search);

            foreach ( $value as $tia ) {

                if ( isset($tia['field']) ) {
                    $fi[] = $tia['value'];
                }
            }


            if ( $this->getSource()->hasCrud() ) {

                if ( isset($search[0]) && ($search[0] === 'D' || $search[0] === 'E' || $search[0] === 'V') ) {
                    unset($search[0]);
                }

                if ( isset($search[1]) && ($search[1] === 'D' || $search[1] === 'E') ) {
                    unset($search[1]);
                }

                if ( isset($search[2]) && ($search[2] === 'D' || $search[2] === 'E') ) {
                    unset($search[2]);
                }
            } else {
                if ( isset($search[0]) && $search[0] === 'V' ) {
                    unset($search[0]);
                }
            }

            $search = $this->_resetKeys($search);


            if (count($search) == count($fi)) {
                $finalFields = array_combine($search, $fi);
            } else {
                $finalFields = false;
            }

            //horizontal row
            if ( $this->getInfo("hRow,title") ) {


                $col = $this->getInfo("hRow");
                $firstRow = false;

                if(! isset($bar[$aa - 1][$hRowIndex]))
                {
                     $bar[$aa - 1][$hRowIndex]['value'] = '';
                     $firstRow = true;
                }

                if ( $bar[$aa][$hRowIndex]['value'] != $bar[$aa - 1][$hRowIndex]['value'] ) {
                    $i ++;

                    if ( isset($bar[$aa - 1]) && $firstRow!==true ) {
                        $grid .= $this->_buildSqlexpTable($this->_buildSqlExp(array($col['field'] => $bar[$aa - 1][$hRowIndex]['value'])));
                    }

                    $grid .= str_replace(array("{{value}}", "{{class}}"), array($bar[$aa][$hRowIndex]['value'], isset($value['class'])?$value['class']:''), $this->_temp['table']->hRow($finalFields));
                }


            }

            $i ++;

            //loop tr
            $fixClass = '';
            if ($value[0]['fixType'] == 'fix') {
                $fixClass = ' grid-fix';
            }
            $grid .= $this->_temp['table']->loopStart(isset($this->_classRowConditionResult[$class]) ? $this->_classRowConditionResult[$class].$fixClass : $fixClass);
            $set = 0;
            foreach ( $value as $final ) {
                $finalField = isset($final['field']) ? $final['field'] : '';
                $finalHrow = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

                if ( ($finalField != $finalHrow && $this->getInfo("hRow,title")) || ! $this->getInfo("hRow,title") ) {

                    $set ++;

                    $grid .= str_replace(array("{{value}}", "{{class}}", "{{style}}"), array($final['value'], $final['class'], $final['style']), $this->_temp['table']->loopLoop($finalFields));

                }
            }

            if ( $this->getInfo("hRow,title") && $this->_totalRecords > 0 ) {
                if ( ($aa + 1) == $this->getTotalRecords() ) {
                    $grid .= $this->_buildSqlexpTable($this->_buildSqlExp(array($col['field'] => $bar[$aa][$hRowIndex]['value'])));
                }
            }

            $set = null;
            $grid .= $this->_temp['table']->loopEnd($finalFields);

            @$aa ++;
            $class ++;
        }

        if ( $this->_totalRecords == 0 ) {
            if (Zend_Controller_Front::getInstance()->getRequest()->getParam('gridmod') == 'ajax') {
                $grid = str_replace("{{value}}", $this->__('No records found'), $this->_temp['table']->noResults());
            } else {
                $grid = str_replace("{{value}}", $this->__('No records found'), $this->_temp['table']->noResults2());
            }
        }

        return $grid;

    }


    /**
     * Build the table that handles the query result from sql expressions
     *
     * @param array $sql
     * @return unknown
     */
    protected function _buildSqlexpTable ($sql)
    {

        $grid = '';
        if ( is_array($sql) ) {
            $grid .= $this->_temp['table']->sqlExpStart();

            foreach ( $sql as $exp ) {
                if ( ! $this->getInfo("hRow,field") || $exp['field'] != $this->getInfo("hRow,field") ) {
                    $grid .= str_replace(array("{{value}}", '{{class}}'), array($exp['value'], $exp['class']), $this->_temp['table']->sqlExpLoop());
                }
            }
            $grid .= $this->_temp['table']->sqlExpEnd();

        } else {
            return false;
        }

        return $grid;

    }


    /**
     * Build pagination
     *
     * @return string
     */
    protected function _pagination ()
    {

        $pageSelect = '';
        if ( count($this->_paginationOptions) > 0 && $this->getTotalRecords() > 0 ) {
            if ( ! array_key_exists($this->_pagination, $this->_paginationOptions) && ! $this->getParam('perPage') ) {
                $this->_paginationOptions[0] = $this->__('Select');
            }
            ksort($this->_paginationOptions);

            foreach ( $this->_paginationOptions as $key => $value ) {
                $this->_paginationOptions[$key] = $this->__($value);
            }

            $url = $this->getUrl('perPage');
            $menuPerPage = $this->__('Show') . ' ' . $this->getView()->formSelect('perPage', $this->getParam('perPage', $this->_pagination), array('onChange' => "window.location='$url/perPage" . $this->getGridId() . "/'+this.value;"), $this->_paginationOptions) . ' ' . $this->__('items');
        } else {
            $menuPerPage = '';
        }

        $url = $this->getUrl(array('start'));

        $actual = (int) $this->getParam('start');

        $ppagina = $this->getParam('perPage', $this->_pagination);
        $result2 = '';

        $pa = $actual == 0 ? 1 : ceil($actual / $ppagina) + 1;

        // Calculate the number of pages
        if ( $this->_pagination > 0 ) {
            $npaginas = ceil($this->_totalRecords / $ppagina);
            $actual = floor($actual / $ppagina) + 1;
        } else {
            $npaginas = 0;
            $actual = 0;
        }

        if ( $this->getInfo("ajax") !== false ) {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"javascript:void 0\">1</a>";
            //$pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/star{$this->getGridId()}t/0')\">1</a>";
        } else {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"$url/start{$this->getGridId()}/0\">1</a>";

        }

        $pag .= ($actual > 5) ? " ... " : "  ";

        if ( $npaginas > 5 ) {
            $in = min(max(1, $actual - 4), $npaginas - 5);
            $fin = max(min($npaginas, $actual + 4), 6);

            for ( $i = $in + 1; $i < $fin; $i ++ ) {
                if ( $this->getInfo("ajax") !== false ) {
                    $pag .= ($i == $actual) ? " <strong>$i</strong> " : " <a href=\"javascript:void 0\">$i</a>";
                    //$pag .= ($i == $actual) ? " <strong>$i</strong> " : " <a href=javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "')>$i</a>";
                } else {
                    $pag .= ($i == $actual) ? " <strong>$i</strong> " : " <a href='$url/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "'>$i</a>";
                }

            }

            $pag .= ($fin < $npaginas) ? " ... " : "  ";
        } else {

            for ( $i = 2; $i < $npaginas; $i ++ ) {
                if ( $this->getInfo("ajax") !== false ) {

                    $pag .= ($i == $actual) ? " <strong>$i</strong> " : " <a href=\"javascript:void 0\">$i</a> ";
                    //$pag .= ($i == $actual) ? " <strong>$i</strong> " : " <a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','" . $url . "/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "')\">$i</a> ";

                } else {

                    $pag .= ($i == $actual) ? " <strong>$i</strong> " : " <a href=\"" . $url . "/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "\">$i</a> ";

                }

            }
        }

        if ( $this->getInfo("ajax") !== false ) {
            $pag .= ($actual == $npaginas) ? " <strong>" . $npaginas . "</strong> " : " <a href=\"javascript:void 0\">$npaginas</a> ";
            //$pag .= ($actual == $npaginas) ? " <strong>" . $npaginas . "</strong> " : " <a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/" . (($npaginas - 1) * $ppagina) . "')\">$npaginas</a> ";

        } else {
            $pag .= ($actual == $npaginas) ? " <strong>" . $npaginas . "</strong> " : " <a href=\"$url/start{$this->getGridId()}/" . (($npaginas - 1) * $ppagina) . "\">$npaginas</a> ";

        }

        if ( $actual != 1 ) {

            if ( $this->getInfo("ajax") !== false ) {
                $pag = "<a href=\"javascript:void 0\">" . $this->__('Previous') . "</a> " . $pag;
                //$pag = "<a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start/" . (($actual - 2) * $ppagina) . "')\">" . $this->__('Previous') . "</a> " . $pag;

            } else {

                $pag = "<a href=\"$url/start{$this->getGridId()}/" . (($actual - 2) * $ppagina) . "\">" . $this->__('Previous') . "</a> " . $pag;
            }

        }

        if ( $actual != $npaginas ) {
            if ( $this->getInfo("ajax") !== false ) {

                $pag .= "<a href=\"javascript:void 0\">" . $this->__('Next') . "</a>";
                //$pag .= "<a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/" . ($actual * $ppagina) . "')\">" . $this->__('Next') . "</a>";
            } else {

                $pag .= "<a href=\"$url/start{$this->getGridId()}/" . ($actual * $ppagina) . "\">" . $this->__('Next') . "</a>";
            }

        }

        if ( $npaginas > 1 && $this->getInfo("limit") == 0 ) {

            if ( $npaginas <= 100 ) {

                $pageSelectOptions = array();
                for ( $i = 1; $i <= $npaginas; $i ++ ) {
                    $pageSelectOptions[(($i - 1) * $ppagina)] = $i;
                }

                // Buil the select form element
                if ( $this->getInfo("ajax") !== false ) {
                    $pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), array('onChange' => ""), $pageSelectOptions);
                    //$pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), array('onChange' => "javascript:gridAjax('{$this->getInfo("ajax")}','{$url}/start{$this->getGridId()}/'+this.value)"), $pageSelectOptions);
                    //$pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), $pageSelectOptions);
                } else {
                    //$pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), array('onChange' => "window.location='{$url}/start{$this->getGridId()}/'+this.value"), $pageSelectOptions);
                    $pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), array('onChange' => ""), $pageSelectOptions);
                    //$pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), $pageSelectOptions);
                }

            } else {
                //$pageSelect = $this->getView()->formText('idf', $pa, array('style' => 'width:30px !important; ', 'onChange' => "window.location='{$url}/start{$this->getGridId()}/'+(this.value - 1)*" . $this->getResultsPerPage()));
                $pageSelect = $this->getView()->formText('idf', $pa, array('style' => 'width:30px !important; '));
            }

            $pageSelect = $this->__('Page') . ':' . $pageSelect;

        }

        if ( $npaginas > 1 || count($this->_export) > 0 ) {

            //get actual record
            if ( $actual <= 1 ) {
                $registoActual = 1;
                $registoFinal = $this->_totalRecords > $ppagina ? $ppagina : $this->_totalRecords;
            } else {
                $registoActual = $actual * $ppagina - $ppagina;

                if ( $actual * $ppagina > $this->_totalRecords ) {
                    $registoFinal = $this->_totalRecords;
                } else {
                    $registoFinal = $actual * $ppagina;
                }

            }
            $jsUrl = $url;
            $images = $this->_temp['table']->images($this->getImagesUrl());

            $url1 = $url = $this->getUrl(array('start'), false);

            $this->_render['export'] = $this->_temp['table']->export($this->getExports(), $this->getImagesUrl(), $url1, $this->getGridId());


            if ( (int) $this->getInfo("limit") > 0 ) {
                $result2 = str_replace(array('{{has-pagination}}', '{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array('no-pagination', '', (int) $this->getInfo("limit"), $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

            } elseif ( $npaginas > 1 && count($this->_export) > 0 ) {

                if ( $this->_pagination == 0 ) {
                    $pag = '';
                    $pageSelect = '';
                }

                $result2 = str_replace(array('{{has-pagination}}', '{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array('has-pagination', $pag, $registoActual . ' ' . $this->__('to') . ' ' . $registoFinal . ' ' . $this->__('of') . '  ' . $this->_totalRecords, $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

            } elseif ( $npaginas < 2 && count($this->_export) > 0 ) {

                if ( $this->_pagination == 0 ) {
                    $pag = '';
                    $pageSelect = '';
                }
                $result2 .= str_replace(array('{{has-pagination}}', '{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array('no-pagination', '', $this->_totalRecords, $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

            } elseif ( count($this->_export) == 0 ) {

                if ( $this->_pagination == 0 ) {
                    $pag = '';
                    $pageSelect = '';
                }
                $result2 .= str_replace(array('{{has-pagination}}', '{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array('no-pagination', '' . $pag, $this->_totalRecords, $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

            }

        } else {
            return '';
        }

        Zend_registry::get('view')->inlineScript()->captureStart();
        ?>
        (function(){paginatorChange(<?php echo json_encode("$jsUrl/start{$this->getGridId()}/"); ?>,<?php echo $this->getResultsPerPage()?>,'<?php echo $this->getInfo("ajax")?>')})()
        <?php
        Zend_registry::get('view')->inlineScript()->captureEnd();

        return $result2;
    }


    /**
     * Here we go....
     *
     * @return string
     */
    public function deploy ()
    {

        if ( $this->getSource() === null ) {
            throw new Bvb_Grid_Exception('Please Specify your source');
        }


        if ( $this->allowDelete == 1 || $this->allowEdit == 1 || $this->allowAdd == 1 ) {
            $this->setAjax(false);
        }

        $this->_view = $this->getView();


        parent::deploy();


        $this->_applyConfigOptions(array(), true);

        if ( ! $this->_temp['table'] instanceof Bvb_Grid_Template_Table_Table ) {
            $this->setTemplate('table', 'table', $this->_templateParams);
        } else {
            $this->setTemplate($this->_temp['table']->options['name'], 'table', $this->_templateParams);
        }


        $images = $this->_temp['table']->images($this->getImagesUrl());


        if ( $this->allowDelete == 1 || $this->allowEdit == 1 || (is_array($this->_detailColumns) && $this->_isDetail == false) ) {

            $pkUrl = $this->getSource()->getPrimaryKey($this->_data['table']);
            $urlFinal = '';

            $failPk = false;
            $pkUrl2 = $pkUrl;
            foreach ( $pkUrl as $key => $value ) {
                foreach ( $this->getFields(true) as $field ) {
                    if ( $field['field'] == $value ) {
                        unset($pkUrl2[$key]);
                        break 2;
                    }
                }

                throw new Bvb_Grid_Exception("You don't have your primary key in your query.
                So it's not possible to perform CRUD operations. Change your select object to include your Primary Key: " . implode(';', $pkUrl2));
            }


            foreach ( $pkUrl as $value ) {
                if ( strpos($value, '.') !== false ) {
                    $urlFinal .= $value . ':{{' . substr($value, strpos($value, '.') + 1) . '}}-';
                } else {
                    $urlFinal .= $value . ':{{' . $value . '}}-';
                }
            }

            $urlFinal = trim($urlFinal, '-');

        }

        if ( $this->allowEdit == 1 ) {
            if ( ! is_array($this->_extraFields) ) {
                $this->_extraFields = array();
            }

            $removeParams = array('add', 'edit', 'comm');

            $url = $this->getUrl($removeParams);

            if ( $this->allowEdit == 1 && $this->getInfo("ajax") !== false ) {
                $urlEdit = $this->_baseUrl . '/' . str_replace("/gridmod" . $this->getGridId() . "/ajax", "", $url);
            } else {
                $urlEdit = $url;
            }

            array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'E', 'decorator' => "<a href=\"$urlEdit/edit" . $this->getGridId() . "/1/comm" . $this->getGridId() . "/" . "mode:edit;[" . $urlFinal . "]\" > " . $images['edit'] . "</a>", 'edit' => true));


        }


        if ( $this->allowDelete ) {
            if ( ! is_array($this->_extraFields) ) {
                $this->_extraFields = array();
            }


            if ( $this->_deleteConfirmationPage == true ) {
                array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"$url/comm" . $this->getGridId() . "/" . "mode:view;[" . $urlFinal . "]/gridDetail" . $this->getGridId() . "/1/gridRemove" . $this->getGridId() . "/1\" > " . $images['delete'] . "</a>", 'delete' => true));
            } else {
                array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"#\" onclick=\"_" . $this->getGridId() . "confirmDel('" . $this->__('Are you sure?') . "','$url/comm" . $this->getGridId() . "/" . "mode:delete;[" . $urlFinal . "]');\" > " . $images['delete'] . "</a>", 'delete' => true));
            }

        }


        if ( is_array($this->_detailColumns) && $this->_isDetail == false ) {
            if ( ! is_array($this->_extraFields) ) {
                $this->_extraFields = array();
            }

            $removeParams = array('add', 'edit', 'comm');
            $url = $this->getUrl($removeParams, false);

            array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'V', 'decorator' => "<a href=\"$url/gridDetail" . $this->getGridId() . "/1/comm" . $this->getGridId() . "/" . "mode:view;[" . $urlFinal . "]/\" >" . $images['detail'] . "</a>", 'detail' => true));
        }


        if ( $this->allowAdd == 0 && $this->allowDelete == 0 && $this->allowEdit == 0 ) {
            $this->_gridSession->unsetAll();
        }

        if ( ! in_array('add' . $this->getGridId(), array_keys($this->getAllParams())) && ! in_array('edit' . $this->getGridId(), array_keys($this->getAllParams())) ) {

            if ( $this->_gridSession->correct === NULL || $this->_gridSession->correct === 0 ) {
                $this->_gridSession->unsetAll();
            }
        }

        if ( strlen($this->_gridSession->message) > 0 ) {
            $this->_render['message'] = str_replace("{{value}}", $this->_gridSession->message, $this->_temp['table']->formMessage($this->_gridSession->messageOk));
            $this->_renderDeploy['message'] = $this->_render['message'];
        }


        #$this->_render['form'] = $this->_form->render();
        if ( (($this->getParam('edit') == 1) || ($this->getParam('add') == 1) || $this->getInfo("doubleTables") == 1) ) {

            if ( $this->allowAdd == 1 || $this->allowEdit == 1  ) {

                // Remove the unnecessary URL params
                $removeParams = array('filters', 'add');

                $url = $this->getUrl($removeParams);

                $this->_renderDeploy['form'] = $this->_form->render();
                $this->_render['form'] = $this->_form->render();

                $this->_showsForm = true;
            }
        }


        $showsForm = $this->willShow();

        $cols = count($this->_fields) + count($this->_extraFields) -1;

        if ( (isset($showsForm['form']) && $showsForm['form'] == 1 && $this->getInfo("doubleTables") == 1) || ! isset($showsForm['form']) ) {
            $this->_render['start'] = $this->_temp['table']->globalStart($cols);
            $this->_renderDeploy['start'] = $this->_render['start'];
        }

        if ( ((! $this->getParam('edit') || $this->getParam('edit') != 1) && (! $this->getParam('add') || $this->getParam('add') != 1))  || $this->getInfo("doubleTables") == 1 ) {

            if ( $this->_isDetail == true || ($this->_deleteConfirmationPage == true && $this->getParam('gridRemove') == 1) ) {

                $columns = parent::_buildGrid();

                $this->_willShow['detail'] = true;
                $this->_willShow['detailId'] = $this->getPkFromUrl();

                $this->_render['detail'] = $this->_temp['table']->globalStart($cols);

                foreach ( $columns[0] as $value ) {
                    if ( ! isset($value['field']) ) {
                        continue;
                    }

                    if ( isset($this->_data['fields'][$value['field']]['title']) ) {
                        $value['field'] = $this->__($this->_data['fields'][$value['field']]['title']);
                    } else {
                        $value['field'] = $this->__(ucwords(str_replace('_', ' ', $value['field'])));
                    }

                    $this->_render['detail'] .= str_replace(array('{{field}}', '{{value}}'), array($value['field'], $value['value']), $this->_temp['table']->detail());
                }

                if ( $this->getParam('gridRemove') == 1 ) {

                    $localCancel = $this->getUrl(array('comm', 'gridDetail', 'gridRemove'));

                    $localDelete = $this->getUrl(array('gridRemove', 'gridDetail', 'comm'));
                    $localDelete .= "/comm" . $this->getGridId() . "/" . str_replace("view", 'delete', $this->getParam('comm'));

                    $buttonRemove = $this->getView()->formButton('delRecordGrid', $this->__('Remove Record'), array('onclick' => "window.location='$localDelete'"));
                    $buttonCancel = $this->getView()->formButton('delRecordGrid', $this->__('Cancel'), array('onclick' => "window.location='$localCancel'"));

                    $this->_render['detail'] .= str_replace('{{button}}', $buttonRemove . ' ' . $buttonCancel, $this->_temp['table']->detailDelete());
                } else {
                    $this->_render['detail'] .= str_replace(array('{{url}}', '{{return}}'), array($this->getUrl(array('gridDetail', 'comm'), false), $this->__('Return')), $this->_temp['table']->detailEnd());
                }

                $this->_render['detail'] .= $this->_temp['table']->globalEnd();

                $this->_renderDeploy['detail'] = $this->_render['detail'];

            } else {
                $this->_willShow['grid'] = true;
                $this->_buildGridRender();
            }

            $this->_showsGrid = true;
        } else {
            $this->_render['start'] = $this->_temp['table']->globalStart($cols);
            $this->_buildGridRender(false);
            $this->_render['end'] = $this->_temp['table']->globalEnd();
        }


        if ( (isset($showsForm['form']) && $showsForm['form'] == 1 && $this->getInfo("doubleTables") == 1) || ! isset($showsForm['form']) ) {
            $this->_render['end'] = $this->_temp['table']->globalEnd();
            $this->_renderDeploy['end'] = $this->_render['end'];
        }


        //Build JS
        $this->_printScript();

        $gridId = $this->getGridId();

        if ( $this->getParam('gridmod') == 'ajax' && $this->getInfo("ajax") !== false ) {

            $layout = Zend_Layout::getMvcInstance();
            if ( $layout instanceof Zend_Layout ) {
                $layout->disableLayout();
            }

            $response = Zend_Controller_Front::getInstance()->getResponse();
            $response->clearBody();
            $response->setBody(implode($this->_renderDeploy));
            $response->sendResponse();
            die();
        }

        if ( $this->getInfo("ajax") !== false ) {
            $gridId = $this->getInfo("ajax");
        }

        $default = new Zend_Session_Namespace('default');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $gridSwitcher = '';
        //if ((($request->getParam('gridmod') != 'ajax') && (null !== $this->_gridSwitcher) && (count($this->_gridSwitcher)) > 1) || $request->getParam('treeajax')== "true") {
        if (((null !== $this->_gridSwitcher) && (count($this->_gridSwitcher)) > 1) && (($request->getParam('gridmod') != 'ajax') || ($request->getParam('treeajax')== "true"))) {

            $modes = array();
            $gridId =  $this->getGridId();
            $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
            foreach ($this->_gridSwitcher as $mode) {
                $params = array();
                $selected = true;
                if (is_array($mode['params'])) {
                    foreach ($mode['params'] as $key => $value) {
                        if ($key == 'all') {
                            $gridValue = $default->grid[$page][$gridId]['all'];
                        } else {
                            $gridValue = $default->grid[$page][$gridId]['filters'][$key];
                            $key .= $gridId;
                        }
                        if ($value === null) {
                            $selected = $selected && ($gridValue === null);
                            continue;
                        }
                        $params[] = "/{$key}/{$value}";
                        $selected = $selected && ($value == $gridValue);
                    }
                    $params = implode($params);
                    if (isset($mode['order'])) {

                        // грязный хак #5678 - странное поведение оракла при сортировке
                        if ((Zend_Registry::get('config')->resources->db->adapter == 'oracle') && isset($mode['order_dir']) && ($mode['order_dir'] == 'DESC')) {
                            unset($mode['order_dir']);
                        }

                        $params .= "/order{$gridId}/{$mode['order']}";
                        if (isset($mode['order_dir']) && ($mode['order_dir'] == 'DESC')) {
                            $params .= '_DESC';
                        } else {
                            $params .= '_ASC';
                        }
                    }
                }
                $modes[] = sprintf(
                    '<div class="%s %s" onClick="%s;%s;%s"><a>%s</a></div>',
                    $mode['name'],
                    $selected ? '_u_selected' : '',
                    "gridAjax('{$this->getInfo("ajax")}','" . $this->getUrl(array('filters', 'order'), true, array('all')) . $params . "')",
                    "$('._grid_gridswitcher div').removeClass('_u_selected')",
                    "$(this).addClass('_u_selected')",
                    $mode['title']
                );
            }

            $gridSwitcher = sprintf(
                '<div class="_grid_gridswitcher"><div class="_d_title">%s</div>%s</div>',
                _('Выводить в таблице:'),
                implode($modes)
            );
        }

        $grid = $gridSwitcher . "<div id='{$gridId}' class='els-grid'>" . implode($this->_renderDeploy) . "</div>";

        if ( $this->_gridSession->correct == 1 ) {
            $this->_gridSession->unsetAll();
        }

        $this->_deploymentContent = $grid;
        return $this;
    }


    /**
     * Combines all parts from the output
     * To deploy or to render()
     * @param mixed $deploy
     */
    private function _buildGridRender ($deploy = true)
    {
//        $bHeader  = $this->_buildExtraRows('beforeHeader');
//        $bHeader .= $this->_buildHeader();
//        $bHeader .= $this->_buildExtraRows('afterHeader');
//        $bTitles = $this->_buildExtraRows('beforeFilters');
//        $bTitles .= $this->_buildFiltersTable(parent::_buildFilters());
//        $bTitles .= $this->_buildExtraRows('afterFilters');
        $bTitles = $this->_buildExtraRows('beforeTitles');
//        $bTitles .= $this->_buildMassActions();

        if ($this->hasMassActions()) {
            $extraField = array_pop($this->_extraFields);
            $extraField['title'] = $this->getGmailCheckbox();
            $extraField['class'] .= 'checkboxes';
            array_push($this->_extraFields, $extraField);
        }

        $bTitles .= $this->_buildTitlesTable(parent::_buildTitles());
        $bTitles .= $this->_buildExtraRows('afterTitles');
        $bFilters = $this->_buildExtraRows('beforeFilters');
        $bFilters .= $this->_buildFiltersTable(parent::_buildFilters());
        $bFilters .= $this->_buildExtraRows('afterFilters');

        $data = parent::_buildGrid();

        if(count($data) && $this->_options['summaryOptions'] && is_array($this->_options['summaryOptions']) && count($this->_options['summaryOptions'])) {
            $data[] = $this->_getSummaryRow($data, $this->_options['summaryOptions']);
        }

        $bGrid = $this->_buildGridTable($data);

        if ( ! $this->getInfo("hRow,title") ) {
            $bSqlExp = $this->_buildExtraRows('beforeSqlExpTable');
            $bSqlExp .= $this->_buildSqlexpTable(parent::_buildSqlExp());
            $bSqlExp .= $this->_buildExtraRows('afterSqlExpTable');
        } else {
            $bSqlExp = '';
        }


        $bPagination  = $this->_buildExtraRows('beforePagination');
        $bMassActions = $this->_buildMassActions();
        $bPagination .= $this->_pagination();
        if (!$this->hasMassActions()) {
           $bMassActions = '';
        }
        $bPagination = str_replace('{{massActions}}', $bMassActions, $bPagination);
        $bPagination = str_replace('{{has-massActions}}', $this->hasMassActions() ? 'has-mass-actions' : 'no-mass-actions', $bPagination);
        $bPagination .= $this->_buildExtraRows('afterPagination');

        if ( $deploy == true ) {
            if ($this->_totalRecords == 0) {
                if ((Zend_Controller_Front::getInstance()->getRequest()->getParam('gridmod') == 'ajax') || count($this->_filters)) {
//                    $this->_renderDeploy['header'] = $bHeader;
                    $this->_renderDeploy['titles'] = $bTitles;
                    $this->_renderDeploy['filters'] = $bFilters;
                    $this->_renderDeploy['grid'] = $bGrid;
                } else {
                    $this->_renderDeploy['grid'] = $bGrid;
                }
            } else {
//            $this->_renderDeploy['header'] = $bHeader;
            $this->_renderDeploy['titles'] = $bTitles;
            $this->_renderDeploy['filters'] = $bFilters;
            $this->_renderDeploy['grid'] = $bGrid;
            $this->_renderDeploy['sqlExp'] = $bSqlExp;
            $this->_renderDeploy['pagination'] = $bPagination;
            }
        }

//        $this->_render['header'] = $bHeader;
        $this->_render['titles'] = $bTitles;
        $this->_render['filters'] = $bFilters;
        $this->_render['grid'] = $bGrid;
        $this->_render['sqlExp'] = $bSqlExp;
        $this->_render['pagination'] = $bPagination;

    }


    private function _getSummaryRow($data, $options)
    {
            $bNeedSummaryTitle = 1;//$data[0][0]['class']=='checkboxes';

            $rowDst = array();
            if($bNeedSummaryTitle) {
                $rowDst[] = array(
                    'value' => _('ИТОГО'),
                    'style' => 'font-weight:bold',
                );
            }
            foreach($data[0] as $i=>$field) {
                if($bNeedSummaryTitle && $i==0) continue;

                if(!isset($field['field']) || !isset($options[$field['field']])) {
                    $rowDst[] = array('value' => '');
                    continue;
                }

                $function = $options[$field['field']];
                switch($function){
                    case 'sum':
                    case 'avg':
                        $V = 0;
                    break;
                    case 'max':
                        $V = -99999999;
                    break;
                    case 'min':
                        $V = 99999999;
                    break;
                }

                $count = 0;
                foreach($data as $row) {
                    $d = false;
                    foreach($row as $fld) {
                        if($field['field']==$fld['field']) {
                            $d = $fld;
                            break;
                        }
                    }
                    if(!$d) continue;

                    switch($function){
                        case 'sum':
                        case 'avg':
                            $V += $d['value'];
                        break;
                        case 'max':
                            $V = max($V, $d['value']);
                        break;
                        case 'min':
                            $V = min($V, $d['value']);
                        break;
                    }
                    $count++;
                }

                switch($function){
                    case 'avg':
                        $V /= $count;
                    break;
                }

                $rowDst[] = array(
                    'value' => $V,
                    'field' => $field['field'],
                    'style' => 'font-weight:bold',
                );
            }
        return $rowDst;
    }
    /**
     * Render parts of the grid
     * @param $part
     * @param $appendGlobal
     */
    public function render ($part, $appendGlobal = false)
    {
        $result = '';
        if ( $appendGlobal === true ) {
            $result .= $this->_render['start'];
        }

        if ( isset($this->_render[$part]) ) {
            $result .= $this->_render[$part];
        }

        if ( $appendGlobal === true ) {
            $result .= $this->_render['end'];
        }

        return $result;
    }


    public function __toString ()
    {
        if ( is_null($this->_deploymentContent) ) {
            die('You must explicitly call the deploy() method before printing the object');
            # self::deploy();
        }
        return $this->_deploymentContent;
    }

    protected function _printScript ()
    {

        if ( $this->getInfo('ajax') !== false ) {
            $useAjax = 1;
        } else {
            $useAjax = 0;
        }

        $script = "";


        if($this->hasMassActions())
        {

 $script .=" var confirmMessages_".$this->getGridId()." = {};".PHP_EOL;

          foreach ($this->getMassActionsOptions() as $value)
          {
              if(isset($value['confirm']))
              {
                  $script .=" confirmMessages_".$this->getGridId()."['{$value['url']}'] = ".json_encode($value['confirm']).";".PHP_EOL;
              }
          }
$script .= "

var translations_".$this->getGridId()." = ".Zend_Json::encode(array(
    'no records selected' => $this->__('No records selected'),
    'no actions selected' => $this->__('No actions selected'),
    'are u shure'         => $this->__('Do you want to perform action')
)).";
".PHP_EOL;

        }

        if ( $this->allowDelete == 1 ) {

$script .= "function _" . $this->getGridId() . "confirmDel(msg, url)
        {
            if(confirm(msg))
            {
            ";
            if ( $useAjax == 1 ) {
                $script .= "    window.location = '" . $this->_baseUrl . "/'+url.replace('/gridmod" . $this->getGridId() . "/ajax','');";
            } else {
                $script .= "    window.location = url;";
            }

            $script .= "
            }else{
                return false;
            }
        }\n\n";

        }
if ( $useAjax == 1 ) {
    $script .= "
    var postFixedIds_".$this->getGridId()." = [];
    var listType='current';
    function gridAjax(ponto,url) {
        url = url.replace('subject//', 'subject/ /');
        url = url.replace('//', '/');
        url = url.replace('subject/ /', 'subject//');
        jQuery.ajax({
            url: '" . $this->_baseUrl . "/'+encodeURI(url),
            context: document.getElementById(ponto),
            dataType: 'html',
            global: false,
            beforeSend: function () { gridAjaxRequestStarted($('#{$this->getGridId()}')); },
            complete: function () { gridAjaxRequestCompleted($('#{$this->getGridId()}')); },
            success: function (data) {
                jQuery(this).replaceWith(data);
                ";
                // Немного жестко
                if ($this->_filters) {
                    foreach($this->_filters as $key =>$value) {
                        if (isset($value['render']) && strtolower($value['render']) == 'date') {
                            $script.="jQuery('#filter_grid" .$key ."_from, #filter_grid" .$key ."_to')
                                .datepicker({
                                    showOn: 'button',
                                    buttonImage: '".$this->getView()->serverUrl().(isset($value['img']) ? $value['img'] : '/images/icons/calendar.png')."',
                                    buttonImageOnly: true
                                });";
                        }
                    }
                }
                $script.="
                document.dispatchEvent(new Event('gridAjaxUpdate'));
            },
            error: function () {}
        });
    }".PHP_EOL;
}

if ( ! $this->getInfo("noFilters") || $this->getInfo("noFilters") != 1 ) {
$script .= "
function urlencode(str) {
    return str.replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40').replace(/\?/g, '%3F').replace(/\./g,'%2E');;
    //return escape(str).replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
}

function " . $this->getGridId() . "gridChangeFilters(event)
    {

    if(typeof(event)=='undefined')
    {
        event = 1;
    }

      if (event!= 1 
          && event.keyCode != 13
      && event.keyCode !== undefined)//проверка на trigger в grid.js [508||516]
      {
        return false;
      }

        var fields = '{$this->_javaScriptHelper['js']}';
        var url = ".json_encode($this->_javaScriptHelper['url']).";

        var fieldsArray = fields.split(\",\");
        var filtro = [];
        var values = [];
        var value = '';

        for (var i = 0; i < fieldsArray.length -1; i++)
        {

        if(document.getElementById(fieldsArray[i]) && document.getElementById(fieldsArray[i]).type=='checkbox' && document.getElementById(fieldsArray[i]).checked ==false)
        {
            value = '';
        }else if(document.getElementById(fieldsArray[i])){
            value = document.getElementById(fieldsArray[i]).value;
        }
         ".PHP_EOL;

$script .= "
        if(value.length>0 && document.getElementById(fieldsArray[i])!=null)
            {";

                $script .= "         value = value.replace(/^\s+|\s+$/g,'');".PHP_EOL;
                //$script .= "         value = value.replace(/\//,'');".PHP_EOL;
                $script .= "         filtro += urlencode(document.getElementById(fieldsArray[i]).name)+'".$this->getGridId()."/'+urlencode(value)+'/';

                values.push(value);
            }

            if(document.getElementById(fieldsArray[i]) && document.getElementById(fieldsArray[i]).type == 'select-one')
            {
                values.push(value);
            }
        }

        if(values.length==0)
        {
            //alert('".$this->__('No Filters to Apply')."');
            //return false;
        }

    ".PHP_EOL;

            if ( $useAjax == 1 ) {
                $script .= "        gridAjax('{$this->getInfo("ajax")}',url+'/'+filtro + getUrlPart());";
            } else {
                $script .= "        window.location=url+'/'+filtro + getUrlPart();".PHP_EOL;
            }
        }
        $script .= "
    }
        ";


        if($this->_hasFixedRows){
            $script.='
                var setFilter="true";
            ';
        }

        $controller = new Zend_Controller_Request_Http();

        // Картинки определены в функции
        $script.='

            function toggleFix(setup, element){


                var load_url="'.$this->getView()->serverUrl().'/images/icons/load.gif";
                var fix_url="'.$this->getView()->serverUrl().'/images/icons/fix.gif";
                var unfix_url="'.$this->getView()->serverUrl().'/images/icons/unfix.gif";


                var idrow = setup;

                var src=element.src;

                if(src.indexOf(fix_url, 0)!=-1){
                    element.src = unfix_url;
                    element.parentNode.firstChild.checked=false;
                }else{
                    element.src = fix_url;
                    element.parentNode.firstChild.checked=true;
                }

                var htmlrow =  element.parentNode.parentNode.innerHTML;

                htmlrow= \'<tr class="\'+element.parentNode.parentNode.className +\'">\' + htmlrow + \'</tr>\';

                element.src=load_url;
                element.disabled=true;

                $.post("'.$this->getView()->serverUrl($this->getView()->url(array('action' => 'toggle-fixed-row'))).'", { idrow: setup, html: htmlrow, getaction: "'.$this->_action.'" },
                       function(data){
                            if(data == "set"){
                                element.src=fix_url;
                            }else{
                                element.src=unfix_url;
                            }
                });
                element.disabled=false;

            }

            function toggleShotCut(setup, element){


                var loadshotcut_url="'.$this->getView()->serverUrl().'/images/icons/load.gif";
                var set_url="'.$this->getView()->serverUrl().'/images/icons/'.$this->pic_set.'.gif";
                var unset_url="'.$this->getView()->serverUrl().'/images/icons/'.$this->pic_unset.'.gif";


                var idrow = setup;

                element.src=loadshotcut_url;


                $.post("'.$this->getView()->serverUrl().$this->_urlShotCut.'", { idrow: setup },
                       function(data){

                            if(data == "set"){
                                element.src=set_url;
                            }else{
                                element.src=unset_url;
                            }
                });


            }




            // Если setFilter определен, то возвращаем нужные вещи, иначе нет
            // Так же с type
            function getUrlPart(type){

                if(window.setFilter != undefined){

                    if(type == undefined){
                        if(postFixedIds_'. $this->getGridId() . '.join("")!=""){
                            return "listType/"+ listType+"/postFixIds/"+postFixedIds_'. $this->getGridId() . '.join(",");
                        }else{
                            return "listType/" +listType;
                        }
                    }else{
                        if(postFixedIds_'. $this->getGridId() . '.join("")!=""){
                            return "/postFixIds/"+postFixedIds_'. $this->getGridId() . '.join(",");
                        }else{
                            return "";
                        }
                    }

                }else{
                    return "";
                }

            }

        ';

        //инициализация грида с прилипающими элементами
        $script .= '$(function() {
                if (HM.moduleExists("grid")) {
                    HM.create("hm.core.ui.grid.Grid");
                }
            });';

        $this->getView()->headScript()->appendScript($script);

        return;
    }


    /**
     *
     * @var Bvb_Grid_Form
     * @return unknown
     */
    public function setForm ($crud)
    {
        //Disable ajax for CRUD operations
        $this->setAjax(false);


        $oldElements = $crud->getElements();

        $form = $this->getSource()->buildForm($this->_data['fields']);

        $crud->getForm()->setOptions($form);

        foreach ( $oldElements as $key => $value ) {
            $crud->getForm()->addElement($value);
        }

        if ( count($crud->getForm()->getElements()) > 0 ) {
            foreach ( $crud->getForm()->getElements() as $key => $value ) {
                $value->setDecorators($crud->getElementDecorator());
            }
        }

        if ( $crud->getFieldsBasedOnQuery() == 1 ) {

            $finalFieldsForm = array();
            $fieldsToForm = $this->getFields(true);

            foreach ( $fieldsToForm as $key => $value ) {
                $field = substr($value['field'], strpos($value['field'], '.') + 1);
                $finalFieldsForm[] = $field;
            }
            foreach ( $crud->getForm()->getElements() as $key => $value ) {

                if ( ! in_array($key, $finalFieldsForm) ) {
                    $crud->getForm()->removeElement($key);
                }
            }

        }

        if ( count($crud->getAllowedFields()) > 0 ) {

            foreach ( $crud->getForm()->getElements() as $key => $value ) {
                if ( ! in_array($key, $crud->getAllowedFields()) ) {
                    $crud->getForm()->removeElement($key);
                }
            }

        }

        if ( count($crud->getDisallowedFields()) > 0 ) {

            foreach ( $crud->getForm()->getElements() as $key => $value ) {
                if ( in_array($key, $crud->getDisallowedFields()) ) {
                    $crud->getForm()->removeElement($key);
                }
            }

        }

         foreach ($this->_data['fields'] as $key=>$title)
         {

             if($crud->getForm()->getElement($key))
             {
                 $crud->getForm()->getElement($key)->setLabel($title['title']);
             }

         }

        if ( count($crud->getForm()->getElements()) == 0 ) {
            throw new Bvb_Grid_Exception($this->__("Your form does not have any fields"));
        }


        foreach ($crud->getElements() as $element)
        {
            if($element->helper=='formFile')
            {
                $element->setDecorators($crud->getFileDecorator());
            }
        }



        $crud->getForm()->setDecorators($crud->getFormDecorator());

        if(isset($crud->options['saveAndAddButton']) && $crud->options['saveAndAddButton']==true && $this->getParam('edit')!=1)
        {
          $crud->getForm()->addElement('submit', 'saveAndAdd' . $this->getGridId(), array('label' => $this->__('Save And New'), 'class' => 'submit', 'decorators' => $crud->getButtonHiddenDecorator()));
        }

        $crud->getForm()->addElement('submit', 'form_submit' . $this->getGridId(), array('label' => $this->__('Save'), 'class' => 'submit', 'decorators' => $crud->getButtonHiddenDecorator()));
        $crud->getForm()->addElement('hidden', 'zfg_form_edit' . $this->getGridId(), array('value' => 1, 'decorators' => $crud->getButtonHiddenDecorator()));

        $crud->addElement('hash', 'zfg_csrf' . $this->getGridId(), array('salt' => 'unique', 'decorators' => $crud->getButtonHiddenDecorator()));

        $url = $this->getUrl(array_merge(array('add', 'edit', 'comm', 'form_reset'), array_keys($crud->getForm()->getElements())));

        $crud->getForm()->addElement('button', 'form_reset' . $this->getGridId(), array('onclick' => "window.location='$url'", 'label' => $this->__('Cancel'), 'class' => 'reset', 'decorators' => $crud->getButtonHiddenDecorator()));
        $crud->getForm()->addDisplayGroup(array('zfg_csrf' . $this->getGridId(), 'zfg_form_edit' . $this->getGridId(), 'form_submit' . $this->getGridId(),'saveAndAdd' . $this->getGridId(), 'form_reset' . $this->getGridId()), 'buttons', array('decorators' => $crud->getGroupDecorator()));

        $crud->setAction($this->getUrl(array_keys($crud->getForm()->getElements())));

        $this->_crudOptions['addForce'] = $crud->getOnAddForce();
        $this->_crudOptions['editForce'] = $crud->getOnEditForce();
        $this->_crudOptions['editAddCondition'] = $crud->getOnEditAddCondition();
        $this->_crudOptions['deleteAddCondition'] = $crud->getOnDeleteAddCondition();

        $this->_form = $crud->getForm();

        if ( isset($crud->options['callbackBeforeDelete']) ) {
            $this->_callbackBeforeDelete = $crud->options['callbackBeforeDelete'];
        }

        if ( isset($crud->options['callbackBeforeInsert']) ) {
            $this->_callbackBeforeInsert = $crud->options['callbackBeforeInsert'];
        }

        if ( isset($crud->options['callbackBeforeUpdate']) ) {
            $this->_callbackBeforeUpdate = $crud->options['callbackBeforeUpdate'];
        }

        if ( isset($crud->options['callbackAfterDelete']) ) {
            $this->_callbackAfterDelete = $crud->options['callbackAfterDelete'];
        }

        if ( isset($crud->options['callbackAfterInsert']) ) {
            $this->_callbackAfterInsert = $crud->options['callbackAfterInsert'];
        }

        if ( isset($crud->options['callbackAfterUpdate']) ) {
            $this->_callbackAfterUpdate = $crud->options['callbackAfterUpdate'];
        }

        $crud = $this->_object2array($crud);


        $options = $crud['options'];


        if ( isset($options['table']) && is_string($options['table']) ) {
            $this->_crudTable = $options['table'];
        }

        if ( isset($options['isPerformCrudAllowed']) && $options['isPerformCrudAllowed'] == 0 ) {
            $this->_crudTableOptions['add'] = 0;
            $this->_crudTableOptions['edit'] = 0;
            $this->_crudTableOptions['delete'] = 0;
        } else {
            $this->_crudTableOptions['add'] = 1;
            $this->_crudTableOptions['edit'] = 1;
            $this->_crudTableOptions['delete'] = 1;
        }

        if ( isset($options['isPerformCrudAllowedForAddition']) && $options['isPerformCrudAllowedForAddition'] == 1 ) {
            $this->_crudTableOptions['add'] = 1;
        } elseif ( isset($options['isPerformCrudAllowedForAddition']) && $options['isPerformCrudAllowedForAddition'] == 0 ) {
            $this->_crudTableOptions['add'] = 0;
        }

        if ( isset($options['isPerformCrudAllowedForEdition']) && $options['isPerformCrudAllowedForEdition'] == 1 ) {
            $this->_crudTableOptions['edit'] = 1;
        } elseif ( isset($options['isPerformCrudAllowedForEdition']) && $options['isPerformCrudAllowedForEdition'] == 0 ) {
            $this->_crudTableOptions['edit'] = 0;
        }

        if ( isset($options['isPerformCrudAllowedForDeletion']) && $options['isPerformCrudAllowedForDeletion'] == 1 ) {
            $this->_crudTableOptions['delete'] = 1;
        } elseif ( isset($options['isPerformCrudAllowedForDeletion']) && $options['isPerformCrudAllowedForDeletion'] == 0 ) {
            $this->_crudTableOptions['delete'] = 0;
        }


        $this->_info['doubleTables'] = $this->getInfo("doubleTables");

        if ( isset($options['delete']) ) {
            if ( $options['delete'] == 1 ) {
                $this->delete = array('allow' => 1);
                if ( isset($options['onDeleteAddWhere']) ) {
                    $this->_info['delete']['where'] = $options['onDeleteAddWhere'];
                }
            }
        }

        if ( isset($options['add']) && $options['add'] == 1 ) {
            if ( ! isset($options['addButton']) ) {
                $options['addButton'] = 0;
            }
            $this->add = array('allow' => 1, 'button' => $options['addButton']);
        }

        if ( isset($options['edit']) && $options['edit'] == 1 ) {
            $this->edit = array('allow' => 1);
        }

        $this->_processForm();
        return $this;
    }


    /**
     * Field type on the filters area. If the field type is enum, build the options
     * Also, we first need to check if the user has defined values to present.
     * If set, this values override the others
     *
     * @param string $campo
     * @param string $valor
     * @return string
     */
    protected function _formatField ($campo)
    {
        $renderLoaded = false;
        $allFieldsIds = $this->getAllFieldsIds();

        if (isset($this->_filters[$campo]) && is_array($this->_filters[$campo]) && isset($this->_filters[$campo]['render']) ) {

            $render = $this->loadFilterRender($this->_filters[$campo]['render']);
            $render->setView($this->getView());
            $renderLoaded = true;
        }


        $valor = $campo;

        if ( isset($this->_data['fields'][$valor]['search']) && $this->_data['fields'][$valor]['search'] == false ) {
            return '';
        }

        //check if we need to load  fields for filters
        if ( isset($this->_filters[$valor]['distinct']) && is_array($this->_filters[$valor]['distinct']) && isset($this->_filters[$valor]['distinct']['field']) ) {

            $distinctField = $this->_filters[$valor]['distinct']['field'];
            $distinctValue = $this->_filters[$valor]['distinct']['name'];
            $distinctOrder = isset($this->_filters[$valor]['distinct']['order']) ? $this->_filters[$valor]['distinct']['order'] : 'name ASC';


            $dir = stripos($distinctOrder, ' desc') !== false ? 'DESC' : 'ASC';
            $sort = stripos($distinctOrder, 'name') !== false ? 'value' : 'field';

            if ( isset($this->_data['fields'][$distinctField]['field']) ) {
                $distinctField = $this->_data['fields'][$distinctField]['field'];
            }
            if ( isset($this->_data['fields'][$distinctValue]['field']) ) {
                $distinctValue = $this->_data['fields'][$distinctValue]['field'];
            }

            $final = $this->getSource()->getDistinctValuesForFilters($distinctField, $distinctValue, $sort . ' ' . $dir);


            $this->_filters[$valor]['values'] = $final;
        }

        //Remove unwanted url params
        $url = $this->getUrl(array('filters', 'start', 'comm', '_exportTo'));

        $fieldsSemAsFinal = $this->_data['fields'];

        if ( isset($fieldsSemAsFinal[$campo]['searchField']) ) {
            $nkey = $fieldsSemAsFinal[$campo]['searchField'];
            @$this->_filtersValues[$campo] = $this->_filtersValues[$nkey];
        }


        $help_javascript = '';

        $i = 0;

        foreach ( array_keys($this->_filters) as $value ) {

            if ( ! isset($this->_data['fields'][$value]['search']) ) {
                $this->_data['fields'][$value]['search'] = true;
            }

            $hRow = isset($this->_data['fields'][$value]['hRow']) ? $this->_data['fields'][$value]['hRow'] : '';

            if ( $this->_displayField($value) && $hRow != 1 && $this->_data['fields'][$value]['search'] != false ) {

                if ( is_array($allFieldsIds[$value]) ) {
                    foreach ( $allFieldsIds[$value] as $newId ) {
                        $help_javascript .= "filter_" . $this->getGridId() . $value . "_" . $newId . ',';
                    }
                } else {
                    $help_javascript .= "filter_" . $this->getGridId() . $value . ",";
                }
            }
        }

        if(count($this->_externalFilters)>0)
        {
            foreach (array_keys($this->_externalFilters) as $fil)
            {

                $help_javascript .= $fil.',';
            }
        }

        $this->_javaScriptHelper = array('js'=>$help_javascript,'url'=>$url);

        if ( $this->getUseKeyEventsOnFilters() === true ) {
            $attr['onChange'] =  $this->getGridId() . "gridChangeFilters(1);";
        }
            $attr['onKeyUp'] =  $this->getGridId() . "gridChangeFilters(event);";

        $opcoes = array();
        if ( isset($this->_filters[$campo]) ) {
            $opcoes = $this->_filters[$campo];
        }

        if ( isset($opcoes['style']) ) {
            $attr['style'] = $opcoes['style'];
        } else {
            $attr['style'] = " ";
        }

        if ( isset($opcoes['class']) ) {
            $attr['class'] = $opcoes['class'];
        }

        $attr['id'] = "filter_" . $this->getGridId() . $campo;

        $selected = null;

        if ( isset($this->_filters[$valor]['values']) && is_array($this->_filters[$valor]['values']) ) {
            $hasValues = false;
        } else {
            $hasValues = $this->getSource()->getFilterValuesBasedOnFieldDefinition($this->_data['fields'][$campo]['field']);
        }
        if ( is_array($hasValues) ) {
            $opcoes = array();
            $tipo = 'text';
            $opcoes['values'] = $hasValues;
        } else {
            $tipo = 'text';
        }

        if ( isset($opcoes['values']) && is_array($opcoes['values']) ) {

            $tipo = 'invalid';
            $values = array();
            if(!$opcoes['removeShowAll']){
                $values[''] = '--' . $this->__('Все') . '--';
            }

            $avalor = $opcoes['values'];

            if ( isset($this->_data['fields'][$valor]['translate']) && $this->_data['fields'][$valor]['translate'] == 1 ) {
                $avalor = array_map(array($this, '__'), $avalor);
            }

            foreach ( $avalor as $key => $value ) {
                if ( isset($this->_filtersValues[$campo]) && $this->_filtersValues[$campo] == $key ) {
                    $selected = $key;
                }

                $values[$key] = $value;
            }
            if($renderLoaded===false)
            {
                $render = $this->loadFilterRender('Select');
                $render->setView($this->getView());
                $renderLoaded = true;
            }

            $render->setValues($values);
            $render->setDefaultValue(isset($this->_filtersValues[$campo]) ? $this->_filtersValues[$campo] : '');
        }

        if ( $tipo != 'invalid' ) {

            if ( $renderLoaded === false ) {
                $render = $this->loadFilterRender('Text');
                $render->setView($this->getView());
                $renderLoaded = true;
            }

            $render->setDefaultValue(isset($this->_filtersValues[$campo]) ? $this->_filtersValues[$campo] : '');

        }

        if (isset($this->_filtersValues[$campo]) && is_array($this->_filtersValues[$campo]) ) {

            foreach ( $this->_filtersValues[$campo] as $key => $value ) {
                $render->setDefaultValue($value, $key);
            }
        }

        $render->setFieldName($valor);
        $render->setAttributes($attr);
        $render->setTranslator($this->getTranslator());
        $render->setGridId($this->getGridId());

        return $render->render();
    }


    function getAllFieldsIds ()
    {
        $fields = array();
        foreach ( $this->_filters as $key => $filter ) {

            if (is_array($filter) && isset($filter['render']) ) {

                $render = $this->loadFilterRender($filter['render']);
                $fields[$key] = $render->getFields();

            } else {
                $fields[$key] = $key;
                $render = false;
            }

        }

        return $fields;

    }

    /**
     * Apply config options
     * @param $options
     */
    protected function _applyConfigOptions ($options)
    {

        $this->_deployOptions = $options;

        if ( isset($this->_deployOptions['templateDir']) ) {

            $this->_deployOptions['templateDir'] = (array) $this->_deployOptions['templateDir'];

            foreach ( $this->_deployOptions['templateDir'] as $templates ) {
                $temp = $templates;
                $temp = str_replace('_', '/', $temp);
                $this->addTemplateDir($temp, $templates, 'table');
            }
        }


        if ( isset($this->_deployOptions['imagesUrl']) ) {
            $this->setImagesUrl($this->_deployOptions['imagesUrl']);
        }

        if ( isset($this->_deployOptions['template']) ) {
            $this->setTemplate($this->_deployOptions['template'], 'table');
        }

        return true;
    }


    /**
     * Returns form instance
     */
    public function getForm ()
    {
        return $this->_form;
    }


    /**
     * Adds a row class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     */
    public function addClassRowCondition ($column, $condition, $class)
    {
        $this->_classRowCondition[$column][] = array('condition' => $condition, 'class' => $class);
        return $this;
    }


    /**
     * Adds a cell class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     */
    public function addClassCellCondition ($column, $condition, $class, $else = '')
    {
        $this->_classCellCondition[$column][] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }


    /**
     * Sets a row class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     * @return Bvb_Grid_Deploy_Table
     */
    public function setClassRowCondition ($condition, $class, $else = '')
    {
        if (empty($this->_classRowCondition)) {
            $this->_classRowCondition = array();
        }
        $this->_classRowCondition[] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }


    /**
     * Set a cell class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     */
    public function setClassCellCondition ($column, $condition, $class, $else)
    {
        $this->_classCellCondition = array();
        $this->_classCellCondition[$column][] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }


    /**
     * Adds extra rows to the grid.
     * @param Bvb_Grid_Extra_Rows $rows
     */
    public function addExtraRows (Bvb_Grid_Extra_Rows $rows)
    {
        $rows = $this->_object2array($rows);
        $this->_extraRows = $rows['_rows'];

        return $this;
    }


    /**
     * Build extra rows
     * @param $position
     */
protected function _buildExtraRows ($position)
    {

        if ( count($this->_extraRows) == 0 ) {

            return false;
        }

        $start = '<tr>';
        $start2 = '<tr>';
        $middle = '';
        $end = '';
        $hasReturn = false;
       if (count($this->_getExtraFields('left')) > 0)
        {
            if(count($this->_getExtraFields('left')) == 1 ){
              $start2 .= "";

            }else{
                $start2 .= " <td colspan='" .( count($this->_getExtraFields('left'))-1 ). "'></td>";

            }

            $start .= " <td colspan='" . count($this->_getExtraFields('left')) . "'></td>";
        }


        if ( count($this->_getExtraFields('right')) > 0 ) {
            $end .= " <td colspan!!!='" . count($this->_getExtraFields('right')) . "'></td>";
        }
        $end .= '</tr>';
       // print_r(htmlspecialchars($end));
           $extracolumns=false;

        foreach ( $this->_extraRows as $key => $value ) {

            if ( $value['position'] != $position ) continue;
                $temp=false;
                $middle1='';
            foreach ( $value['values'] as $key => $final ) {
                $colspan = isset($final['colspan']) ? "colspan='" . $final['colspan'] . "'" : '';
                $class = isset($final['class']) ? "class='" . $final['class'] . "'" : '';
                if ( ! isset($final['content']) ) {
                    $final['content'] = '';
                }

                if ($key === 'ids')
                {
                    if($this->_hasFixedRows){
                        $middle1 .= "<td $colspan $class ><label><input type='checkbox' name='gridMassActions_" . $this->_gridId . "' id='massCheckBox_" . $this->_gridId . "' class='mass-checkbox' value='" . $final['value'] . "' checked='checked'><span class='grid__checkbox'></label></span><span class='mass-fixer'><img src='/images/icons/attention.gif' onclick=\"toggleFix(".$final['value'].", this)\" id=\"toggle_".$final['value']."\"></span></td>".PHP_EOL;
                    }else{
                        $middle1 .= "<td $colspan $class ><label><input type='checkbox' name='gridMassActions_" . $this->_gridId . "' id='massCheckBox_" . $this->_gridId . "' class='mass-checkbox' value='" . $final['value'] . "' checked='checked'><span class='grid__checkbox'></span></label></td>".PHP_EOL;
                    }

                    $temp=true;
                } else
                {
                    $middle1 .= "<td $colspan $class >{$final['content']}</td>";

                }
                $hasReturn = true;
            }

            if($temp==true){
                $middle .= $start2.$middle1.$end.PHP_EOL;
            }else{
                 $middle .= $start.$middle1.$end.PHP_EOL;
            }



        }

        if ( $hasReturn === false ) {
            return false;
        }








        return $middle ;

    }


    /**
     * Defines the default classes to be used on odd and even td
     * @param string $odd
     * @param string $even
     */
    public function setRowAltClasses ($odd, $even = '')
    {
        $this->_cssClasses = array('odd' => $odd, 'even' => $even);
        return $this;
    }

    /**
     * So user can know what is going to be done
     */
    public function buildFormDefinitions ()
    {

        if ( $this->getParam('add') == 1 ) {
            $this->_formSettings['mode'] = 'add';
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

        if ( $this->getParam('edit') == 1 ) {
            $this->_formSettings['mode'] = 'edit';
            $this->_formSettings['id'] = $this->getPkFromUrl();
            $this->_formSettings['row'] = $this->getSource()->fetchDetail($this->getPkFromUrl());
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

        if ( $this->getParam('delete') == 1 ) {
            $this->_formSettings['mode'] = 'delete';
            $this->_formSettings['id'] = $this->getPkFromUrl();
            $this->_formSettings['row'] = $this->getSource()->fetchDetail($this->getPkFromUrl());
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

    }


    /**
     * Return actions from the form
     */
    public function getFormSettings ()
    {

        $this->buildFormDefinitions();
        return $this->_formSettings;
    }


    /**
     * Show a confirmation page instead a alert window
     * @param $status
     */
    public function setDeleteConfirmationPage ($status)
    {
        $this->_deleteConfirmationPage = (bool) $status;
        return $this;
    }


    /**
     * Defines Images location
     * @param $url
     */
    public function setImagesUrl ($url)
    {
        if ( ! is_string($url) ) {
            throw new Bvb_Grid_Exception('String expected, ' . gettype($url) . ' provided');
        }
        $this->_imagesUrl = $url;
        return $this;
    }


    /**
     * Returns the actual URL images location
     */
    public function getImagesUrl ()
    {
        return $this->_imagesUrl;
    }


    /**
     *
     * Always show arrows on all fields or show only when a field
     * is sorted
     *
     * @param bool $status
     * @return Bvb_Grid_Deploy_Table
     */
    public function setAlwaysShowOrderArrows ($status)
    {
        $this->_alwaysShowOrderArrows = (bool) $status;
        return $this;
    }


    public function getAlwaysShowOrderArrows ()
    {
        return $this->_alwaysShowOrderArrows;
    }


    public function hasMassActions ()
    {
        return $this->_hasMassActions;
    }


    public function getMassActionsOptions ()
    {
        if ( ! $this->_hasMassActions ) {
            return array();
        }

        return (array) $this->_massActions;
    }


    protected function _buildMassActions ($allowMultiple = true)
    {
        if ( ! $this->hasMassActions() ) return false;


        $select = array();
        foreach ( $this->getMassActionsOptions() as $value ) {
            $select[$value['url']] = $value['caption'];
        }

        $attribs = array();
        $onChange = $formSelect = '';
        $subSelects = $subInputs = array();

        if (count($this->_subMassActionSelects)) {
            $onChange .= "jQuery('div.multiple_toggle').hide();";
            foreach($this->_subMassActionSelects as $massAction => $subSelect) {
                if (!isset($subSelects[$subSelect['name']])) {
                    $onChange .= "jQuery('#".preg_replace("/\[|\]/","",$subSelect['name'])."').hide();";
                }
                $onChange .= "if (this.value == '".$massAction."') {jQuery('#".preg_replace("/\[|\]/","",$subSelect['name'])."').show();jQuery('div.multiple_toggle').show();}";
                $attribs['OnChange'] = $onChange;
                if (!isset($subSelects[$subSelect['name']])) {
                    $formSelect .= $this->getView()->formSelect($subSelect['name'], null, array('style' => 'display: none'), $subSelect['options']);
                    $subSelects[$subSelect['name']] = true;
                }
            }
        }

        if (count($this->_subMassActionInput)) {

            $inputMassActionCounter = 0;

            foreach($this->_subMassActionInput as $massAction => $subInput) {
                if($massAction == ''){
                    continue;
                }

                $title = null;
                $inputContainerSelector = json_encode('#_fdiv .container-input-'.$inputMassActionCounter.' input');

                if (isset($subInput['options']['title'])) {
                    $title = $subInput['options']['title'];
                    $inputContainerSelector = json_encode('#_fdiv .container-input-'.$inputMassActionCounter.' label');
                    unset($subInput['options']['title']);
                }

                $dataType = 'text';
                if (isset($subInput['options']['dataType'])) {
                    $dataType = $subInput['options']['dataType'];
                    unset($subInput['options']['dataType']);
                }


                if ($dataType == 'integer') {
//                    $subInput['options']
                }


                if (!isset($subInputs[$subInput['name']])) {
                    $onChange .= "jQuery($inputContainerSelector).hide();";
                }
                $onChange .= "if (this.value == '".$massAction."') {jQuery($inputContainerSelector).show();}";
                $attribs['OnChange'] = $onChange;
                if (!isset($subInputs[$subInput['name']])) {
                    if (empty($title) ) {
                        $formSelect .= '<div class="container-input container-input-'.$inputMassActionCounter.'">' .
                            $this->getView()->formText($subInput['name'], null, array('style' => 'display: none'), $subInput['options']) . '</div>';

                    } else {
                        $formSelect .= '<div class="container-input container-input-'.$inputMassActionCounter.'"><label style="display: none">' .
                            $this->getView()->formText($subInput['name'], null, array('style' => 'width: 100%'), $subInput['options']) . '<br/>' . $title . '</label></div>';

                    }
                    $subInputs[$subInput['name']] = true;
                }

                $inputMassActionCounter++;
            }
        }

        if (count($this->_subMassActionFcbk)) {

            $fcbkMassActionCounter = 0;

            foreach($this->_subMassActionFcbk as $massAction => $subInput) {
                if($massAction == ''){
                    continue;
                }
                $fcbkContainerSelector = json_encode('#_fdiv .container-fcbk-'.$fcbkMassActionCounter.' ul.holder');

                if (!isset($subInputs[$subInput['name']])) {
                    $onChange .= "jQuery($fcbkContainerSelector).hide();";
                }
                $onChange .= "if (this.value == '".$massAction."') {jQuery($fcbkContainerSelector).show();}";
                $attribs['OnChange'] = $onChange;
                if (!isset($subInputs[$subInput['name']])) {
                    $formSelect .= '<div class="container-fcbk container-fcbk-'.$fcbkMassActionCounter.'">' . $this->getView()->fcbkComplete($subInput['name'], null, array(
                        'width' => 240,
                        'json_url' => $subInput['options']['DataUrl'],
                        'maxitems' => $subInput['options']['MaxItems'],
                        'newel'    => $subInput['options']['AllowNewItems'],
                    )) . '</div>';
                    $subInputs[$subInput['name']] = true;
                }

                $fcbkMassActionCounter++;
            }
        }

        $formSelect = $this->getView()->formSelect("gridAction_".$this->getGridId(), null, $attribs, $select).$formSelect;
        $formSubmit = $this->getView()->formSubmit("send_".$this->getGridId(),$this->__('Submit'));

        if($this->getResultsPerPage()<$this->getTotalRecords())
        {
            $currentRecords = $this->getResultsPerPage();
        }else{
            $currentRecords = $this->getTotalRecords();
        }

        $ids = $this->getSource()->getMassActionsIds($this->_data['table']);

        //$return = "<tr><td class='massActions' colspan=" . $this->_colspan . ">";
        //$return = "<div class='massActions'>";
        $return = "";
        $return .= '<form method="post" action="" id="massActions_' . $this->getGridId() . '" name="massActions_' . $this->getGridId() . '"><div id="_fdiv">';
        $return .= $this->getView()->formHidden('massActionsAll_' . $this->getGridId(), $ids);
        $return .= $this->getView()->formHidden('postMassIds_'.$this->getGridId(), '');

        //$return .= "<span class='massSelect'><a href='#' title=\""._('Выделить все')."\" onclick='checkAll_" . $this->getGridId() . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",{$this->getTotalRecords()},1);return false;'><img src = \"{$GLOBALS['sitepath']}images/icons/select_all.gif\"  alt=\""._('Выделить все')."\"  align=\"absmiddle\" border=0></a> <a href='#' title=\""._('Выделить видимое')."\"  onclick='checkAll_" . $this->getGridId() . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",{$currentRecords},0);return false;'><img src = \"{$GLOBALS['sitepath']}images/icons/select_visible.gif\"  alt=\""._('Выделить видимое')."\"  align=\"absmiddle\" border=0></a> <a href='#' title=\""._('Инвертировать выделение')."\" onclick='checkInverseAll_" . $this->getGridId() . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",{$this->getTotalRecords()},1);return false;'><img src = \"{$GLOBALS['sitepath']}images/icons/select_inverse.gif\"  alt=\""._('Инвертировать выделение')."\"  align=\"absmiddle\" border=0></a> <a href='#' title=\""._('Снять выбор')."\" onclick='uncheckAll_" . $this->getGridId() . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",0); return false;'><img src = \"{$GLOBALS['sitepath']}images/icons/select_unselect.gif\" alt=\""._('Снять выбор')."\" align=\"absmiddle\" border=0></a> | <strong><span id='massSelected_" . $this->getGridId() . "'>0</span></strong><strong> " . $this->__('items selected') . "</strong></span> " . /*$this->__('Actions') . */" $formSelect $formSubmit</form>";
        $toggleMultiple = $this->_subMassActionSelectsAllowMultiple ? "<div class='multiple_toggle'> <img src='/images/multiple-shots.svg'/></div> " : '&nbsp;';
        $return .= "<span class='massSelect'>
        <strong>" . $this->__('Для') . " <span id='massSelected_" . $this->getGridId() . "' class='selected-count'>0</span> " . $this->__('элементов:') . "</strong></span> " .$formSelect. $toggleMultiple . $formSubmit .
        "</div></form>";
    //     $this->getView()->inlineScript()->appendScript("
    //         var elems = document.querySelectorAll('select');
    //         var instances = M.FormSelect.init(elems, options);
    //    ");
        //$return .= "</div>";
        //$return .= "</td></tr>";

        return $return;
    }

    function setSubMassActionSelects($selects)
    {
        $this->_subMassActionSelects = $selects;
    }

    function setSubMassActionFcbk($fcbk)
    {
        $this->_subMassActionFcbk = $fcbk;
    }

    function setMassAction(array $options)
    {

        $this->_hasMassActions = true;
        $this->_massActions = $options;

        foreach ($options as $value)
        {
            if(!isset($value['url']) || !isset($value['caption']))
            {
                throw new Bvb_Grid_Exception('Options url and caption are required for each action');
            }
        }

        if(count($this->getSource()->getPrimaryKey($this->_data['table']))==0)
        {
            throw new Bvb_Grid_Exception('No primary key defined in table. Mass actions not available');
        }

        $pk = '';
        foreach ($this->getSource()->getPrimaryKey($this->_data['table']) as $value)
        {
            $aux = explode('.',$value);
            $pk .= end($aux).'-';
        }

        $pk = rtrim($pk,'-');


        $left = new Bvb_Grid_Extra_Column();
        if($this->_hasFixedRows) {
            $view = $this->getView();
            $decorator = "<label><input type='checkbox' name='gridMassActions_".$this->getGridId()."' id='massCheckBox_".$this->getGridId()."' class='mass-checkbox' value='{{{$pk}}}' ><span class='grid__checkbox'></span><span class='mass-fixer'>".$view->icon('{{fixType}}', _('Фиксирование строки'),"toggleFix({{{$pk}}}, this)", "toggle_{{{$pk}}}")."</span><label>";
        } elseif ($this->_hasShotCut) {
            $view = $this->getView();
            $decorator = "<label><input type='checkbox' name='gridMassActions_".$this->getGridId()."' id='massCheckBox_".$this->getGridId()."' class='mass-checkbox' value='{{{$pk}}}' ><span class='grid__checkbox'></span></label><span class='mass-fixer'>".$view->icon('{{'.$this->_shotCutField.'}}', _('Фиксирование строки'),"toggleShotCut({{{$pk}}}, this)", "toggle_{{{$pk}}}")."</span>";
        } else {
            $decorator = "<label><input type='checkbox' name='gridMassActions_".$this->getGridId()."' id='massCheckBox_".$this->getGridId()."' class='mass-checkbox' value='{{{$pk}}}' ><span class='grid__checkbox'></span></label>";
        }

        $left->position('left')->name('')->title('')->decorator($decorator);

        if (!empty($this->_massActionsCallback)) {
            $left->decorator();
            $this->_massActionsCallback['params'][] = $decorator;
            $left->callback($this->_massActionsCallback);
        }

        $this->addExtraColumns( $left);

    }

    public function getGmailCheckbox()
    {
        if($this->getResultsPerPage()<$this->getTotalRecords())
        {
            $currentRecords = $this->getResultsPerPage();
        }else{
            $currentRecords = $this->getTotalRecords();
        }
        $gridId = $this->getGridId();
        $content = $this->getView()->GmailCheckbox(
            "gmailCheck_$gridId",
            "gmailCheck_$gridId",
            array(
                array(
                    'title' => _('Выделить видимое'),
                    'onClick' => "GridHelpers.check('$gridId');"
                ),
                array(
                    'title' => _('Снять выделение'),
                    'onClick' => "GridHelpers.uncheck('$gridId');"
                ),
                array(
                    'title' => _('Выделить всё'),
                    'onClick' => "GridHelpers.checkAll('$gridId');"
                ),
                array(
                    'title' => _('Инвертировать выделение'),
                    'onClick' => "GridHelpers.inverse('$gridId');"
                )
            )
        );
        return $content;
    }



    /**
     * Returns any erros from form validation
     */
    public function getFormErrorMessages()
    {
        return isset($this->_gridSession->errors)?$this->_gridSession->errors:false;
    }

    /**
     * If we should use onclick, and onkeyup instead a button over the filters
     * @param $flag
     */
    public function setUseKeyEventsOnFilters( $flag)
    {
        $this->_useKeyEventsOnFilters = (bool) $flag;
        return $this;
    }

    public function getUseKeyEventsOnFilters()
    {
        return $this->_useKeyEventsOnFilters;
    }

}
