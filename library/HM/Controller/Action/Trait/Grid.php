<?php

trait HM_Controller_Action_Trait_Grid
{
    /* @var $_form HM_Form */
    protected $_form;

    public function initGrid()
    {
        $params = $this->_request->getParams();
        //Изменение параметров для даты в гриде
        foreach($params as $key => $value){
            if(strpos($key, '[from]') || strpos($key, '[to]')){
                $this->_request->setParam($key, str_replace('-','.',$value));
            }
        }
    }

    /**
     * @return Bvb_Grid
     */
    public function getGrid($select = null, $columnsOptions = null, $filters = null, $id = 'grid', $summaryOptions=null)
    {
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_GRID_COLUMNS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $columnsOptions);
        $summaryOptions = $event->getReturnValue();

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/grid.css');
        }

        // @todo: отключить неиспользуемое
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.collapsorz_1.1.min.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/content-modules/grid.js');

        $grid = Bvb_Grid::factory('vue', array(
            'deploy' => array(
                'excel' => array('download' => 1, 'dir' => Zend_Registry::get('config')->path->upload->tmp),
                'word' => array('download' => 1, 'dir' => Zend_Registry::get('config')->path->upload->tmp),
            ),
            'summaryOptions' => $summaryOptions
        ), $id);
        $grid->setAjax($id);
        $grid->setImagesUrl('/images/bvb/');
        $grid->setExport(array('print', 'excel', 'word'));
        $grid->setEscapeOutput(true);
        $grid->setAlwaysShowOrderArrows(false);

//        if (property_exists($grid, 'controllerView2')) {
//            $grid->controllerView2 = $this->view;
//        }

        //Получаем колво строк с помощью старого метода
        $perPage = $this->getService('Option')->getOption('grid_rows_per_page');
        $perPage = $perPage > 0 ? $perPage : Bvb_Grid::ROWS_PER_PAGE;

        $grid->setNumberRecordsPerPage($perPage);
        $grid->setcharEncoding(Zend_Registry::get('config')->charset);
        if (null !== $select) {
            if (is_array($select)) {
                $grid->setSource(new Bvb_Grid_Source_Array($select, array_keys($columnsOptions)));
            } elseif ($select instanceof Zend_Db_Select) {
                $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
            }
        }
        if (null != $columnsOptions) {
            if (is_array($columnsOptions) && count($columnsOptions)) {
                foreach($columnsOptions as $column => $options) {
                    $grid->updateColumn($column, $options);
                }
            }
        }

        if (null != $filters) {
            if (is_array($filters) && count($filters)) {
                $gridFilters = new Bvb_Grid_Filters();
                foreach($filters as $field => $options) {
                    $gridFilters->addFilter($field, $options);
                }
                $grid->addFilters($gridFilters);
            }
        }

        $translator = new Zend_Translate('array', APPLICATION_PATH.'/system/errors.php');
        $grid->setTranslator($translator);

        return $grid;
    }

    /**
     * @return bool
     */
    public function isGridAjaxRequest()
    {
        if (null === $this->_gridAjaxRequest) {
            $this->_gridAjaxRequest = false;
            if ($this->_hasParam('gridmod') && ($this->_getParam('gridmod') == 'ajax')) {
                $this->_gridAjaxRequest = true;
            }
        }
        return $this->_gridAjaxRequest;
    }



    protected function _setForm( $form)
    {
        $this->_form = $form;
    }

    protected function _getForm()
    {
        $this->_form->setServiceContainer(Zend_Registry::get('serviceContainer')); //todo: не юзать singletone
        return $this->_form;
    }

    public function _getMessages()
    {
        return array(
            HM_Controller_Action::ACTION_INSERT     => _('Элемент успешно создан'),
            HM_Controller_Action::ACTION_UPDATE     => _('Элемент успешно обновлён'),
            HM_Controller_Action::ACTION_DELETE     => _('Элемент успешно удалён'),
            HM_Controller_Action::ACTION_DELETE_BY  => _('Элементы успешно удалены'),
            HM_Controller_Action::ACTION_ARCHIVE    => _('Элемент успешно архивирован'),
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            HM_Controller_Action::ERROR_COULD_NOT_CREATE => _('Элемент не был создан'),
            HM_Controller_Action::ERROR_NOT_FOUND        => _('Элемент не найден')
        );


    }

    private function _getErrorMessage($error)
    {
        $messages = $this->_getErrorMessages();
        if (isset($messages[$error])) {
            return $messages[$error];
        }else{
            return $error;
        }

        return _('Сообщение для данного события не установлено');
    }

    private function _getMessage($action)
    {
        $messages = $this->_getMessages();
        if (isset($messages[$action])) {
            return $messages[$action];
        }

        return _('Сообщение для данного события не установлено');
    }

    public function create(Zend_Form $form)
    {

    }

    public function update(Zend_Form $form)
    {

    }

    public function delete($id)
    {

    }

    public function setDefaults(Zend_Form $form)
    {

    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index');
    }

    public function newAction()
    {
        $form = $this->_getForm();

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirectToIndex();
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_INSERT));
                    $this->_redirectToIndex();
                }
            }
        }
        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    // нехороший метод
    // нельзя использовать другие параметры с _id
    public function deleteAction()
    {
        $params = $this->_getAllParams();
        foreach($params as $key => $value) {
            if (substr($key, -3) == '_id') {
                $this->_setParam('id', $value);
                break;
            }

            if (in_array($key, array('subid', 'projid'))) { // hack
                $this->_setParam('id', $value);
            }
        }

        $id = (int) $this->_getParam('id', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_DELETE));
        }

        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->delete($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function unsetAction(&$actions, $unsetAction, $reset = true)
    {
        // ВНИМАНИЕ!!!! Данная функция неверно работает при сортировке грида!!!!!
        $unsetUrl = $this->view->url($unsetAction, null, $reset);
        foreach ($actions as $actionKey => $actionVal) {
            $url = $actionVal['url'];
            if (false !== strpos($unsetUrl, $url) or false !== strpos($url, $unsetUrl)) {
                unset($actions[$actionKey]);
            }
        }

        $actions = array_values($actions);
    }

    public function expandResponsibility($subject)
    {
        $userId = $this->getService('User')->getCurrentUserId();
        $itemType = ($subject->is_labor_safety) ? HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT : HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT;

        // Добавление курсов в область ответственности
        $collection = $this->getService('Responsibility')->fetchAll(array(
            'user_id = ?' => $userId,
            'item_type = ?' => $itemType
        ));
        if (count($collection)) {
            $this->getService('Responsibility')->insert(array(
                'user_id' => $userId,
                'item_id' => $subject->subid,
                'item_type' => $itemType,
            ));
        }
    }

    public function orgFilter($data)
    {
        $field  = $data['field' ];
        $value  = $data['value' ];
        $select = $data['select'];

        if (strlen($value) > 0) {
            if ($field == 'department') {
                $select
                    ->joinLeft(array('so1' => 'structure_of_organ'), "so.owner_soid = so1.soid", array())
                    ->where("so1.name LIKE ?", '%' . $value . '%');

            } elseif ($field == 'position') {
                $select
                    ->where("so.name LIKE ?", '%' . $value . '%');
            }
        }
    }

    public function updateResponsibility($responsibilityType, $responsibilityCount)
    {
        if (!$responsibilityCount) {
            return _('Нет');
        }

        $service = null;

        switch ($responsibilityType) {
            case HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT:
                $service = $this->getService('Subject');
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT:
                $service = $this->getService('Subject');
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM:
                $service = $this->getService('Programm');
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_GROUP:
                $service = $this->getService('StudyGroup');
                break;
        }

        if ($service) {
            return $service->pluralFormCount($responsibilityCount);
        }
        return _('Нет');
    }

    public function updateResponsibilityOrg($responsibility, $select)
    {
        if ($this->_responsibilities === null) {
            $fetch = $select->query()->fetchAll();

            $tmp = array();
            foreach($fetch as $value) {
                $tmp = array_merge($tmp, explode(',', $value['orgStruct']));
            }

            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);

            $this->_responsibilities = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $tmp))->getList('soid', 'name');
        }

        if (empty($responsibility)) {
            return HM_Responsibility_ResponsibilityModel::getResponsibilityDefaultAccess(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR) ? _('Без ограничений') : _('Не задана');
        } else {
            $fields = array_unique(explode(",", $responsibility));
            $resp   = array();
            foreach($fields as $value) {
                if (!empty($this->_responsibilities[$value])) {
                    $resp[] = $this->_responsibilities[$value];
                }
            }

            $result = (is_array($resp) && (count($resp) > 1)) ? array('<p class="total">' . $this->getService('Orgstructure')->pluralFormCount(count($resp)) . '</p>') : array();
            foreach($resp as $value) {
                $result[] = "<p>{$value}</p>";
            }
            if ($result)
                return implode($result);
            else
                return _('Нет');
        }
    }

    /**
     * Аналог @see HM_User_DataGrid_Callback_UpdateStatus для старых гридов
     *
     * @param $field
     * @return string
     */
    public function updateStatus($field)
    {
        $active = ($field == 0);

        $colorName = $active ? 'themeColors.success' : 'themeColors.error';
        $caption = _($active ? 'Активный' : 'Заблокирован');

        return '<icon-diode :color="' . $colorName . '" style="margin-right: 6px"></icon-diode>' . $caption;
    }

    public function updateAssigned($assigned)
    {
        if ($assigned != "") {
            return _('Да');
        } else {
            return _('Нет');
        }
    }

    public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }


    public function updateDate($date)
    {
        if ($date == "") {
            return _('Нет');
        } else {
            $date = new Zend_Date($date);

            if ($date instanceof Zend_Date) {
                return $date->toString(HM_Locale_Format::getDateFormat());
            } else {
                return _('Нет');
            }
        }
    }
}
