<?php
abstract class HM_Controller_Action extends Zend_Controller_Action
{
    use HM_Controller_Action_Trait_Ajax;
    use HM_Controller_Action_Trait_Print;
    use HM_Controller_Action_Trait_Empty;

    // @todo: придумать место для этих констант (вынесены из trait'ов)
    const ACTION_INSERT    = 'insert';
    const ACTION_UPDATE    = 'update';
    const ACTION_DELETE    = 'delete';
    const ACTION_DELETE_BY = 'delete-by';
    const ACTION_ARCHIVE   = 'archive';

    // Errors
    const ERROR_NOT_FOUND      = 'not_found';
    const ERROR_COULD_NOT_CREATE = 'could_not_create';

    const EXPORT_FILENAME = 'Y-m-d_H-i-s'; // time format only!
    const NO_VALUE = -1;
    const NAMESPACE_MULTIPAGE = 'multipage';

    const SWITCHER_PARAM_DEFAULT = 'all';
    const FILTER_STRICT = 0;
    const FILTER_ALL = 1;

    protected $_user;
    protected $required_permission_level;
    protected $_gridAjaxRequest = null;
    protected $_ajaxRequest = null;

    protected $_positionFullCache = array();
    protected $courseCache = null;//#17462
    protected $projectCache = array();
    protected $departmentCache = array();
    protected $classifierCache = array();
    protected $lessonsCache = null;
    protected $meetingsCache = null;
    protected $testsCache = array();
    protected $subjectsCache = array();
    protected $_subjectsCache = array();
    protected $evaluationsCache = array();
    protected $_recruitersCache = null;
    protected $usersCache = array();
    protected $_usersCache = array();
    protected $_programEventCache = array();
    protected $_vacanciesCache = null;
    protected $_vacanciesStatusCache = null;

    protected $gridId = 'grid';
    protected $dataGrid = null;

    /**
     * MUST BE OVERRIDEN
     * @var HM_Service_Abstract
     */
    protected $_defaultService = null;

    /**
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger = null;

    /**
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

    protected $_serviceContainer;

    protected $_acl;

    /**
    * View object
    * @var HM_View
    */
    public $view;

    public function init()
    {
        $this->_acl = $this->getService('Acl');
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_redirector = $this->_helper->getHelper('ConditionalRedirector');

        //Получаем и запоминаем режим без меню. Флаг MOBILE это касается только запросов с визуализацией
        $default = new Zend_Session_Namespace('default');
        $isMobile = $this->_getParam('mobile', $default->isMobile);
        $default->isMobile = $isMobile ? $isMobile:0;
        if ($isMobile) {
            $this->_helper->getHelper('layout')->setLayout('naked');
        }

        $subjectId = $this->_getParam('subject_id');
        $this->gridId = $subjectId ? 'grid' . $subjectId ?: '' : 'grid';

        /**
         * TODO устранить неявность.
         *   Создать класс HM_Controller_Ajax_Action,
         *     вызывать initAjax() и
         *     @see HM_Controller_Action::postDispatch()
         *     только для унаследованных от него контроллеров.
         *   В остальных случаях возвращать json-ответ явно, через
         *     $this->>responseJson()
         */
        if ($this->isAjaxRequest()) {
            return $this->initAjax();
        } else {
            $this->view->isMobileApp = filter_var($isMobile, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->_getParam('print')) {
            return $this->initPrint();
        }
/*
        if($this->isMobile()) return;

        $client_security_token = $this->getService('User')->getSecurityToken();
        if($client_security_token && !$this->isMobile()) {
            return; //Экономим на инициализациях
        }
*/
        $this->view->setBlankPage($this->isEmpty());
        $this->view->initView();
        $this->initView();

        // Обработка форсированной смены пароля
        $user = $this->getService('User')->getCurrentUser();
        if (
            $user &&
            $user->force_password == 1 &&
            !($this->_getParam('module') == 'default' && $this->_getParam('controller') == 'index' && $this->_getParam('action') == 'force-password') &&
            !($this->_getParam('module') == 'default' && $this->_getParam('controller') == 'index' && $this->_getParam('action') == 'authorization') &&
            !($this->_getParam('module') == 'default' && $this->_getParam('controller') == 'index' && $this->_getParam('action') == 'logout')
        ){
            $this->_redirector->gotoSimple('force-password', 'index', 'default');
        }
    }

    public function initView()
    {
        $sidebars = $this->getDefaultSidebars();
        foreach ($sidebars as $i => $name) {
            $this->view->addSidebar(
                $name,
                [
                    'modal' => false,
                    'opened' => true,
                    'order' => -100 * ($i+1),
                ]
            );
        }

        $messages = $this->_flashMessenger->getMessages();
        if (is_array($messages) && count($messages)) {
            $this->_helper->Notificator($messages);
            $this->view->notifications = json_encode($messages);
        }

    }

    public function setCurrentNotifications()
    {
        if(!$this->_flashMessenger) return;

        $messages = $this->_flashMessenger->getCurrentMessages();
        if (is_array($messages) && count($messages)) {
            $this->_helper->Notificator($messages);
            $this->view->notifications = json_encode($messages);
        }
    }

    public function getDefaultSidebars()
    {
        if ($user = $this->getService('User')->getCurrentUser()) {
            return array(
                'user-events',
                'user-home',
            );
        }
        return array();
    }

    /*
     *  Этот метод переопределять в дочерних классах
     *  при необходимости модификации аккордеона
     */
    public function getContextNavigationModifiers()
    {
        return array();
    }

    /*
     *  Этот метод переопределять в дочерних классах
     *  при необходимости модификации аккордеона
     */
    public function getContextNavigationSubstitutions()
    {
        return array();
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        return $this->_helper->ServiceContainer($name);
    }

    public function isAjaxRequest() //[che 13.10.2014] если вызывалась до init, то _ajaxRequest был некорректный
    {
        if($this->_ajaxRequest===null)
            $this->_ajaxRequest = ($this->getRequest()->isXmlHttpRequest() || $this->_getParam('ajax', false)/*
            || ($this->_hasParam('gridmod') && ($this->_getParam('gridmod') == 'ajax'))*/);

        return $this->_ajaxRequest;
    }

    public function validateFormAction($form = null)
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->getHelper('layout')->disableLayout();
        $form->isValid($this->_getAllParams());
        $json = $form->getMessagesUtf8();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8', true);
        echo HM_Json::encodeErrorSkip($json);
        exit();
    }

    public function getOne($collection)
    {
        if (($collection instanceof HM_Collection_Abstract) && count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function quoteInto($where, $args)
    {
        return $this->getService('User')->quoteInto($where, $args);
    }

    public function getAcl()
    {
        return $this->_acl;
    }

    public function coursesCache($field, $select)
    {
        if ($this->courseCache === null || !count($this->courseCache)){
            $this->courseCache = array();
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['courses'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $tmp = array_filter($tmp);
            if (count($tmp)) {
                $this->courseCache = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $tmp), 'name');
            }
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result =  array();
        if (is_a($this->courseCache, 'HM_Collection')) {
            foreach($fields as $value){
                if ($tempModel = $this->courseCache->exists('subid', $value)) {
                    $marker = '';
                    if ($tempModel->base_id) {
                        $marker = HM_View_Helper_Footnote::marker(1);
                        $this->view->footnote(_('Учебная сессия'), 1);
                    }
                    $result[] = "<p>{$tempModel->name}{$marker}</p>";
                }
            }
        }

        if ($result) {
            if (count($result) > 1) {
                array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount(count($result)) . '</p>');
            }
            return implode('',$result);
        } else {
            return _('Нет');
        }
    }

    public function projectsCache($field, $select){

        if($this->projectCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['projects'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->projectCache = $this->getService('Project')->fetchAll(array('projid IN (?)' => $tmp), 'name');
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Project')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $this->projectCache->exists('projid', $value);
            $result[] = "<p>{$tempModel->name}</p>";
        }

        if($result)
            return implode('',$result);
        else
            return _('Нет');
    }

    public function positionFullCache($field, $select)
    {

        $fields = array_filter(array_unique(explode(',', $field)));
        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . count($fields) . 'должностей</p>') : array();
        $service = $this->getService('Orgstructure');

        foreach ($fields as $fld) {
            $soids = explode('/', $fld);
            $fldNames = array();
            foreach ($soids as $soid) {
                if (! isset($this->_positionFullCache[$soid])) {
                    $item = $service->getOne($service->find($soid));
                    if (false !== $item) $this->_positionFullCache[$soid] = $item->name;
                }
                $fldNames[] = $this->_positionFullCache[$soid];
            }
            $result[] = '<p>' . implode('/', $fldNames). '</p>';
        }

        if ($result) {
            return implode('', $result);
        } else {
            return _('Нет');
        }
    }

    public function departmentsCache($field, $select = null, $isPosition = false){

        $key = $isPosition ? 'positions' : 'departments';

        if(!isset($this->departmentCache[$key])/* || ($this->departmentCache[$key] === array())*/){
            // #8770 метод выборки по soid-ам пользователей из select не работает с фиксированными строками
            /*$smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                if ($val[$key]) {
                    $tmp[$key][] = $val[$key];
                }
            }
            if (!empty($tmp[$key])) {
                $tmp[$key] = implode(',', $tmp[$key]);
                $tmp[$key] = explode(',', $tmp[$key]);
                $tmp[$key] = array_unique($tmp[$key]);
                $extraCond = $isPosition ? 'type IN (1,2)' : 'type=0';
                $this->departmentCache[$key] = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $tmp[$key], $extraCond));
            } else {
                // если пусто, то не надо каждый раз долбаться
                $this->departmentCache[$key] = array();
            }*/
            $extraCond = $isPosition ? 'type IN (1,2)' : 'type=0';

            $select = $this->getService('Orgstructure')->getSelect();
            $select->from('structure_of_organ', array(
                'soid',
                'name',
                'is_manager'
            ));
            $select->where($extraCond);
            $deps = $select->query()->fetchAll();
            $index = array();
            foreach ($deps as $dep) {
                $index[$dep['soid']] = array('name' => $dep['name'], 'is_manager' => $dep['is_manager']);
            }

            $this->departmentCache[$key] = $index;
        }

        $fields = array_filter(array_unique(explode(',', $field)));
        $pluralForm = $isPosition ? 'pluralFormPositionsCount' : 'pluralFormCount';
        $cache = &$this->departmentCache[$key];


        if ($isPosition && is_array($fields) && (count($fields) == 1)) {
            // Если данные представляют собой одну-единственную должность
            $value = $fields[0];
            return $this->updatePositionName($cache[$value]['name'], $value, HM_Orgstructure_OrgstructureModel::TYPE_POSITION, $cache[$value]['is_manager']);

        } else {
            // Во всех остальных случаях (т.е. нет данных или несколько должностей или подразделений)
            // Делаем, как было раньше
            $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Orgstructure')->$pluralForm(count($fields)) . '</p>') : array();

            foreach($fields as $value){
                if (isset($cache[$value])) {
                    $result[] = "<p>{$cache[$value]['name']}</p>";
                }
            }

            if ($result) {
                return implode('', $result);
            } else {
                return _('Нет');
            }

        }


    }

    public function groupsCache($field, $select)
    {

	    if(!isset($this->departmentCache['groups'])) {
            $this->departmentCache['groups'] = $this->getService('StudyGroup')->fetchAll()->asArrayOfObjects();
        }

		$fields = array_filter(array_unique(explode(',', $field)));
		$result = (is_array($fields) && count($fields) > 1) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('StudyGroup')->pluralFormCount(count($fields)) . '</p>') : array();

		foreach ($fields as $value) {
			if (count($this->departmentCache['groups'])) {
				$tempModel = $this->departmentCache['groups'][$value];
				if ($tempModel) {
					$result[] = '<p><a href="' . $this->view->url(array('module' => 'study-groups', 'controller' => 'users', 'action' => 'index', 'group_id' => ''), null, true) . $tempModel->group_id . '">' . $tempModel->name . '</a></p>';
				}
			}
		}

		if ($result)
			return implode('', $result);
		else
			return _('Нет');
	}

    public function customDepartmentsFilter($params)
    {
        $params['select']->where('d.owner_soid = ?', $params['value']);

    }

    public function classifiersCache($field, $select){

        if($this->classifierCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['classifiers'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->classifierCache = $this->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $tmp));
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Classifier')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $this->classifierCache->exists('classifier_id', $value);
            $result[] = "<p>{$tempModel->name}</p>";
        }
        if($result)
            return implode('',$result);
        else
            return _('Нет');
    }

    public function usersCache($userIds)
    {
        if ($userIds == '') return false;
        $userIds = array_unique(explode(',', $userIds));
        $result = array();
        if (count($userIds) > 1) $result[] = '<p class="total">' . _('Всего специалистов: ') .  count($userIds) . '</p>';
        foreach ($userIds as $userId) {
            if (! isset($this->_usersCache[$userId]) ) {
                $user = $this->getService('User')->getOne(
                    $this->getService('User')->find($userId)
                );
                if (false !== $user ) $this->_usersCache[$userId] = $user->getName();
            }
            $result[] = '<p>' . $this->_usersCache[$userId] . '</p>';
        }
        if($result)
            return implode('',$result);
        else
            return _('Нет');

    }

    public function recruitersCache($recruiterIds)
    {
        if ($recruiterIds == '') return false;
        $recruiterIds = explode(',', $recruiterIds);
        $recruiterIds = array_unique($recruiterIds);

        if ($this->_recruitersCache === null){
            $recruiters = $this->getService('Recruiter')->fetchAllDependence('User', array());
            foreach ($recruiters as $recruiter) {
                if (count($recruiter->user)) {
                    $user = $recruiter->user->current();
                    $this->_recruitersCache[$recruiter->recruiter_id] = sprintf(
                        '%s <a href="%s">%s</a>',
                        $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => $user->MID), null, true),null, true),
                        $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => $user->MID), null, true),
                        $user->getName()
                    );
                }
            }

        }

        $result = (is_array($recruiterIds) && (count($recruiterIds) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Recruiter')->pluralFormCount(count($recruiterIds)) . '</p>') : array();
        foreach($recruiterIds as $recruiterId){
            if (isset($this->_recruitersCache[$recruiterId])) {
                $result[] = "<p>{$this->_recruitersCache[$recruiterId]}</p>";
            }
        }

        if($result)
            return implode('',$result);
        else
            return _('Нет');

    }

    public function tagsAction()
    {
        $json = $this->getJsonParams();
        if (!is_a($json, 'Zend_Json_Exception')) {
            $tagName = $json['tag'];
        }
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }
        $tagService = $this->getService('Tag');
        $tags = $tagService->fetchAll($tagService->getTagCondition(null, $tagName));
        $result = [];

        foreach($tags as $tag) {
            $result [] = $tag->body;
        }

        $this->_helper->json($result);
    }

    public function displayTags($itemId, $itemType, $forGrid = true)
    {
        if ( $tags = $this->getService('Tag')->getStrTagsByIds($itemId, $itemType, $forGrid) ) {
            return $tags;
        }
        return '';
    }

    public function filterTags($data)
    {
        $module = strtolower( $this->_request->getModuleName() );
        $data['value'] = trim($data['value']);
        $service = false;

        switch ( $module ) {
            case 'blog':
                $service = $this->getService('TagRefBlog');
                break;
            case 'resource':
            case 'activity':
                $service = $this->getService('TagRefResource');
                break;
            case 'course':
            case 'subject':
                $service = $this->getService('TagRefCourse');
                break;
            case 'subject':
                $service = $this->getService('TagRefCourse');
                break;
            case 'quest':
                $service = $this->getService('TagRefTest');
                break;
            case 'exercises':
                $service = $this->getService('TagRefExercises');
                break;
            case 'poll':
                $service = $this->getService('TagRefPoll');
                break;
            case 'task':
                $service = $this->getService('TagRefTask');
                break;
            case 'study-groups':
            case 'user':
            case 'assign':
                $service = $this->getService('TagRefUser');
                break;
            case 'session':
                $service = $this->getService('TagRefAtSession');
                break;
        }

        if ( $service ) {
            $data['select'] = $service->getFilterSelect( $data['value'], $data['select'] );
        }
    }

    public function groupsFilter($data)
    {
        $value = $data['value'];
        $select = $data['select'];

        $tableName = 'sgc';
        if($data['tableName'] != ''){
            $tableName = $data['tableName'];
        }

        if(strlen($value) > 0){
            $fetch = $this->getService('StudyGroup')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));

            $data = $fetch->getList('group_id', 'name');

            if ($data) {
                $select->where($tableName.'.group_id IN (?)', array_keys($data));
            } else {
                $select->where($tableName.'.group_id IN (?)',0);
            }
        }

    }

    public function postDispatch()
    {
        if ($this->isAjaxRequest()) {
            $this->postDispatchAjax();
        } else {

            $gridMarkup = '';

            if (isset($this->view->grid) && $this->view->grid) {

                if(is_object($this->view->grid))
                    $gridMarkup = $this->view->grid->getMarkup();
                else
                    $gridMarkup = $this->view->grid;

            }
            else if ($this->dataGrid) {
                $gridMarkup = $this->dataGrid->getMarkup();
            }
            // TODO закомментировал, т. к. grid стал появляться на страницах, где его никогда не было
            // else {
            //     echo $this->getDataGridMarkup();
            // }

            if ($this->view->noTpl($this->getViewScript())) {
                $this->getHelper('viewRenderer')->setNoRender();
                if ($gridMarkup) {
                    echo $gridMarkup;
                }
            } else {
                $this->view->grid = $gridMarkup;
            }
        }

        $this->setCurrentNotifications();
    }

    /**
     * TODO убрать, т.к. не подгружает actions как это делает
     * @see HM_DataGrid::getMarkup()
     *
     * Получить разметку грида для вставки
     * @todo реализовать передачу ссылки
     * @return string
     */
    public function getDataGridMarkup()
    {
        $start = '<hm-grid';
        if (APPLICATION_ENV === 'development') {
            $start.=' debug';
        }
        $end = '></hm-grid>';
        return $start.$end;
    }

    protected function setDefaultService(HM_Service_Abstract $service)
    {
        $this->_defaultService = $service;
    }

    public function getDateForGrid($date,$onlyDate = false)
    {
        if (!$date) return '';
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
    }

    static public function formatDate($date)
    {
        if (!$date) return '';
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
    }

    public function updateStateAction()
    {
        if ($this->isAjaxRequest()) {
            $stateId = $this->_getParam('state_id', 0 );
            $names = $this->_getParam('names', array());
            $forState = $this->_getParam('forState', '');
            $state = $this->getOne($this->getService('State')->find($stateId));

            $params = unserialize($state->params);

            foreach($names as $name){
                $params[$forState][$name] = $this->_getParam($name, '');
            }

            $processStates = $this->getService('State')->update(
                array(
                    'state_of_process_id' => $stateId,
                    'params'           => serialize($params)
                )
            );

            $serviceName = '';
            switch($processStates->process_type){
                case HM_Process_ProcessModel::PROCESS_ORDER:
                    $serviceName = 'Claimant';
                    break;
                case HM_Process_ProcessModel::PROCESS_VACANCY:
                    $serviceName = 'Vacancy';
                    break;
                case HM_Process_ProcessModel::PROCESS_VACANCY_ASSIGN:
                    $serviceName = 'VacancyAssign';
                    break;
                case HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING:
                    $serviceName = 'RecruitNewcomer';
                    break;
                case HM_Process_ProcessModel::PROCESS_PROGRAMM_RESERVE:
                    $serviceName = 'HrReserve';
                    break;
                default:
                    exit;
            }

            /** @var $model HM_Model_Abstract */
            $model = $this->getOne($this->getService($serviceName)->find($processStates->item_id));

            $this->getService('Process')->initProcess($model);

            // Если нужные параметры набрались то в state происходит переходит на следующий стейт или переход на фейл.
            $model->getProcess()->checkAutomateTransition($names);

            $model->getProcess()->getCurrentState()->setStatus(HM_State_Abstract::STATE_STATUS_CONTINUING);
            $model->getProcess()->updateData();

        }else{
            $stateId = $this->_getParam('state_id', 0 );

            $inParams = $this->_getAllParams();
            unset($inParams['state_id'], $inParams['controller'], $inParams['module']);

            $processStates = $this->getOne($this->getService('State')->find($stateId));

            switch($processStates->process_type){
                case HM_Process_ProcessModel::PROCESS_ORDER:
                    $serviceName = 'Claimant';
                    break;
                case HM_Process_ProcessModel::PROCESS_VACANCY:
                    $serviceName = 'Vacancy';
                    break;
                case HM_Process_ProcessModel::PROCESS_VACANCY_ASSIGN:
                    $serviceName = 'VacancyAssign';
                    break;
                case HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING:
                    $serviceName = 'RecruitNewcomer';
                    break;
                case HM_Process_ProcessModel::PROCESS_PROGRAMM_RESERVE:
                    $serviceName = 'HrReserve';
                    break;
                default:
                    exit;
            }

            /** @var $model HM_Model_Abstract */
            $model = $this->getOne($this->getService($serviceName)->find($processStates->item_id));

            $this->getService('Process')->initProcess($model);


            $params = unserialize($processStates->params);

            foreach($inParams as $name => $val){
                $params[get_class($model->getProcess()->getCurrentState())][$name] = $val;
            }

            $processStates = $this->getService('State')->update(
                array(
                    'state_of_process_id' => $stateId,
                    'params'           => serialize($params)
                )
            );

            // reUpdate model and process. dirty)
            $model = $this->getOne($this->getService($serviceName)->find($processStates->item_id));
            $this->getService('Process')->initProcess($model);


            // Если нужные параметры набрались то в state происходит переход на следующий стейт или переход на фейл.
            $model->getProcess()->checkAutomateTransition(array_keys($inParams));

            if($model->getProcess()->getCurrentState()){
                $model->getProcess()->getCurrentState()->setStatus(HM_State_Abstract::STATE_STATUS_CONTINUING);
                $model->getProcess()->updateData();
            }
            $url = $model->getProcess()->getRedirectionUrl();

            //dirty hack
            if(substr($url, 0,3) == '/at'){
                $url = substr($url, 3);
            }

            $this->_redirector->gotoUrlAndExit($url);
        }
        exit();
    }

    public function updateSubjectColumn($typeId, $moduleId, $gridSubjectId, $subjectId,$subjectType='subject')
    {

        switch ($subjectType) {
            case 'project':
                return $this->_updateProjectColumn($typeId, $moduleId, $gridSubjectId, $subjectId);
                break;
            default:
                return $this->_updateSubjectColumn($typeId, $moduleId, $gridSubjectId, $subjectId);
        }
    }

    protected function _updateSubjectColumn($typeId, $moduleId, $gridSubjectId, $subjectId)
    {
        if (in_array($typeId, HM_Lesson_LessonModel::getTypesFreeModeEnabled())) {
            $isFreeCondition = new Zend_Db_Expr(implode(',', array(HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_FREE_BLOCKED)));
        } else {
            $isFreeCondition = HM_Lesson_LessonModel::MODE_PLAN;
        }

        if($this->lessonsCache === null){
            $this->lessonsCache = array();
            $lessons = $this->getService('Lesson')->fetchAll(array(
                    'CID = ?' => $subjectId,
                    'typeID = ?' => $typeId,
                    'isfree IN (?)' => $isFreeCondition,
            ));
            foreach ($lessons as $lesson) {
                $params = $lesson->getParams();
                if ($params['module_id']) {
                    $this->lessonsCache[$params['module_id']] = $lesson;
                }
            }
        }

        if ($gridSubjectId == $subjectId && isset($this->lessonsCache[$moduleId])) {
            if (in_array($this->lessonsCache[$moduleId]->isfree, array(HM_Lesson_LessonModel::MODE_FREE_BLOCKED, HM_Lesson_LessonModel::MODE_PLAN))) {
                return _('Доступ через план занятий');
            } else {
                return _('Свободный доступ');
            }
        }
        return _('Нет доступа');
    }

    public function updateSubjectColumnTasks($typeId, $moduleId, $gridSubjectId, $subjectId)
    {
        $testTypeId = HM_Test_TestModel::mapEvent2TestType($typeId);

        if($this->testsCache === array()){
            $tests = $this->getService('Test')->fetchAll(array(
                'cid = ?' => $subjectId,
                'type = ?' => $testTypeId,
            ));
            foreach ($tests as $test) {
                if ($test->test_id && $test->tid) {
                    $this->testsCache[$test->tid] = $test->test_id;
                }
            }
        }
        if($this->lessonsCache === null){
            $this->lessonsCache = array();
            $lessons = $this->getService('Lesson')->fetchAll(array(
                    'CID = ?' => $subjectId,
                    'typeID = ?' => $typeId,
                    'isfree =? ' => HM_Lesson_LessonModel::MODE_PLAN,
            ));
            foreach ($lessons as $lesson) {
                $params = $lesson->getParams();
                if ($params['module_id']) {
                    $this->lessonsCache[$this->testsCache[$params['module_id']]] = $lesson;
                }
            }
        }

        if ($gridSubjectId == $subjectId && isset($this->lessonsCache[$moduleId])) {
            if ($this->lessonsCache[$moduleId]->isfree == HM_Lesson_LessonModel::MODE_PLAN) {
                return _('Доступ через план занятий');
            }
        }
        return _('Нет доступа');
    }

    protected function _updateProjectColumn($typeId, $moduleId, $gridSubjectId, $subjectId)
    {
        if (in_array($typeId, HM_Meeting_MeetingModel::getTypesFreeModeEnabled())) {
            $isFreeCondition = new Zend_Db_Expr(implode(',', array(HM_Meeting_MeetingModel::MODE_FREE, HM_Meeting_MeetingModel::MODE_FREE_BLOCKED)));
        } else {
            $isFreeCondition = HM_Meeting_MeetingModel::MODE_PLAN;
        }

        if($this->meetingsCache === null){
            $this->meetingsCache = array();
            $meetings = $this->getService('Meeting')->fetchAll(array(
                    'project_id = ?' => $subjectId,
                    'typeID = ?' => $typeId,
                    'isfree IN (?)' => $isFreeCondition,
                ));
            foreach ($meetings as $meeting) {
                $params = $meeting->getParams();
                if ($params['module_id']) {
                    $this->meetingsCache[$params['module_id']] = $meeting;
                }
            }
        }

        if ($gridSubjectId == $subjectId && isset($this->meetingsCache[$moduleId])) {
            if (in_array($this->meetingsCache[$moduleId]->isfree, array(HM_Meeting_MeetingModel::MODE_FREE_BLOCKED, HM_Meeting_MeetingModel::MODE_PLAN))) {
                return _('Доступ через план занятий');
            } else {
                return _('Свободный доступ');
            }
        }
        return _('Нет доступа');
    }

    public function updateSubjectColumnQuests($typeId, $moduleId, $gridSubjectId, $subjectId)
    {
        if($this->lessonsCache === null){
            $this->lessonsCache = array();
            $lessons = $this->getService('Lesson')->fetchAll(array(
                    'CID = ?' => $subjectId,
                    'typeID = ?' => $typeId,
                    'isfree =? ' => HM_Lesson_LessonModel::MODE_PLAN,
            ));
            foreach ($lessons as $lesson) {
                $params = $lesson->getParams();
                if ($params['module_id']) {
                    $this->lessonsCache[$params['module_id']] = $lesson;
                }
            }
        }

        if ($gridSubjectId == $subjectId && isset($this->lessonsCache[$moduleId])) {
            if ($this->lessonsCache[$moduleId]->isfree == HM_Lesson_LessonModel::MODE_PLAN) {
                return _('Доступ через план занятий');
            }
        }
        return _('Нет доступа');
    }

    public function updateEmail($email, $emailConfirmed, $validateEmailEnabled)
    {
        return ($emailConfirmed || !$validateEmailEnabled) ? $email : '<span class="unconfirmed" title="' . _('Email не подтверждён пользователем') . '">' . $email . '</span>';
    }

    /**
     * Форматирование номера сертификата перед выводом
     * @param int|string $cId
     * @return string
     */
    public function updateCertificateNumber($cId = 0, $cFile = false)
    {
        if ( !$cId ){
            return _('Нет');
        }
        $formatingNumber = $this->getService('Certificates')->getFormatNubmer($cId);

        $certificateUrl = $this->view->url(array(
            'baseUrl' => '',
            'module' =>'file',
            'action'=>'certificate',
            'controller' => 'get',
            'certificate_id' => $cId,
        ));

        $certificateSrc = Zend_Registry::get('config')->path->upload->certificates .  $cId . ".pdf";
        $hasGeneratedCertificate = is_file($certificateSrc);

        return ($cFile or $hasGeneratedCertificate) ?
            '<a href="' . $certificateUrl . '" target="_blank">' . $formatingNumber . '</a>' :
            $formatingNumber;
    }

    /**
     * Форматирование итоговой оценки
     * @param int|string $mark
     * @param int|string $status
     * @return string
     */
    public function updateMark($mark, $scaleId)
    {
        return HM_Scale_Value_ValueModel::getTextStatus($scaleId, $mark);
    }

     /**
     * Убирает из меню действий грида определеное действие по его url
     * @param string $actionMenu      - строка html кода меню
     * @param array|string $actionUrl - урл, пункт меню которого необходимо удалить
     * @return string
     */
    public function removeActionFromMenu($actionMenu, $actionUrl)
    {
        if (is_array($actionUrl)) {
            $actionUrl = $this->view->url($actionUrl);
        } else {
            $actionUrl = (string) $actionUrl;
        }

        $urlPos    = strpos($actionMenu, $actionUrl);
        $startPos  = strrpos(substr($actionMenu, 0, $urlPos), '<li>');
        $endPos    = strpos($actionMenu, '</li>', $urlPos);
        return ($urlPos !== false && $startPos !== false && $endPos !== false)? substr($actionMenu,0,$startPos) . substr($actionMenu, $endPos+5) : $actionMenu;
    }

    public function loginAsAction()
    {
        $userId = (int) $this->_getParam('MID', $this->_getParam('user_id', 0));
        $user = $this->getService('User')->getOne($this->getService('User')->findDependence('Position', $userId));

        $hasPermission = false;
        $currentRole = $this->getService('User')->getCurrentUserRole();
        if ($user) {
            if ($this->getService('Acl')->inheritsRole($currentRole, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)){
                $hasPermission = $this->getService('Supervisor')->isResponsibleFor($userId);
            } elseif ($this->getService('Acl')->inheritsRole($currentRole,
                array(
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL))
                ) {
                // всех по области ответственности и всех вне структуры
                $positionId = count($user->positions) ? $user->positions->current()->soid : 0;
                $hasPermission = !$positionId || $this->getService('Responsibility')->isResponsibleFor(null, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, $positionId);
            } elseif ($this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_ADMIN, HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY))){
                $hasPermission = true;
                $isAdmin = true;
            }
        }

        $isEnduser = true;
//        if (!$isAdmin && count($userRoles = $this->getService('User')->getUserRoles($userId))) {
//            foreach ($userRoles as $userRole) {
//                if (!$this->getService('Acl')->inheritsRole($userRole, array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
//                    $message =  _('Данную функцию нельзя использовать применительно к пользователям, имеющим административные роли или роль тьютора.');
//                    $isEnduser = false;
//                    break;
//                }
//            }
//        }



        $whoseDeputyIam = $this->getService('Deputy')->whoseDeputyIam();
        if ($whoseDeputyIam !== null && $userId == $whoseDeputyIam['user']->MID) {
            $hasPermission = true;
        }

        if ($userId && $hasPermission && $isEnduser) {
            $this->getService('User')->authorizeOnBehalf($userId);
        } else {
            if (empty($message)) $message = _('У Вас нет прав на авторизацию от имени данного пользователя');
            $this->_flashMessenger->addMessage(array(
                'message' => $message,
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            ));
        }
        if (in_array($this->getRequest()->getModuleName(), array('reserve', 'newcomer'))) {
            $url = $this->view->url(array(
                'module' => 'default',
                'controller' => 'index',
                'action' => 'index',
                'baseUrl' => '',
            ));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        }
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function updateResourceName($resourceId, $title, $type, $filetype, $filename, $activity_type)
    {
        return $this->view->cardLink(
                $this->view->url(array(
                    'module' => 'kbase',
                    'controller' => 'resource',
                    'action' => 'card',
                    'resource_id' => '')
                ) . $resourceId,
                _('Карточка информационного ресурса'),
                'icon-custom',
                'pcard',
                'pcard',
                'material-icon-small ' . HM_Resource_ResourceService::getIconClass($type, $filetype, $filename, $activity_type)
            ) .
            '<a href="'.$this->view->url(array(
                'module' => 'kbase',
                'controller' => 'resource',
                'action' => 'index',
                'resource_id' => $resourceId
            ), null, false, false).'">' . $title . '</a>';
    }

    public function updatePositionName($name, $soid, $type, $isManager)
    {
        if (empty($name)) return '';

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) return $name;

        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            $name = '<a href="'.$this->view->url(array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $soid), null, true).'">'.$name.'</a>';
        }


        $name = '
            <hm-long-text-tooltip 
                class="color-text-contrast" 
                text="'. $name . '"
                max-width="220px"
                :min-chars-enable="25"
            >
            </hm-long-text-tooltip>';

        return $this->view->cardLink(
                $this->view->url(array(
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'card',
                    'org_id' => '',
            'baseUrl' => '')
                ) . $soid,
                HM_Orgstructure_OrgstructureService::getIconTitle($type, $isManager),
                'icon-svg',
                'pcard', // className
                'pcard', // relName
//                'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass($type, $isManager)
                'address-book', // iconType
                [ // additional params
                    'iconVueColor' => $isManager ? 'colors.iconBlue' : 'colors.iconGray',
                    'class' => 'hm-card-link-orgstructure',
                ]
            ) . $name;
    }

    public function evaluationCache($evaluationIds, $profileOrCategory = HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE){

        $condition = ($profileOrCategory == HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE) ? 'profile_id IS NOT NULL' : 'category_id IS NOT NULL';
        $evaluationIds = array_unique(explode(',', $evaluationIds));
        if($this->evaluationsCache === array()){
            $this->evaluationsCache = $this->getService('AtEvaluation')->fetchAll($condition); // их немного
        }

        $result = (is_array($evaluationIds) && (($count = count($evaluationIds)) > 1)) ? array('<p class="total">' . sprintf(_n('мероприятия во множественном числе', '%s мероприятие', $count), $count) . '</p>') : array();
        foreach($evaluationIds as $evaluationId){
            $tempModel = $this->evaluationsCache->exists('evaluation_type_id', $evaluationId);
            $result[] = "<p>{$tempModel->name}</p>";
        }
        if($result)
            return implode(' ', $result);
        else
            return '';
    }

    protected function _getParamWithFreeVariant($paramName)
    {
        $return = array();
        $params = $this->_getAllParams();
        if (isset($params[$paramName])) {
            $return += $params[$paramName];
        }
        foreach ($params as $key => $value) {
            if (strpos($key, $paramName) !== false) {
                $keyParts = explode('_', $key);

                $id = isset($keyParts[1]) ? $keyParts[1] : null;

                if ($id) {
                    if (is_array($return[$id])) {
//                         $variant = array_search(HM_Quest_Question_Variant_VariantModel::FREE_VARIANT, $return[$id]);
//                         $return[$id][$variant] = $value;
// @todo: разобраться с этими свободными варантами!
                        $return[$id][HM_Quest_Question_Variant_VariantModel::FREE_VARIANT] = $value;
                    } elseif ($return[$id] == HM_Quest_Question_Variant_VariantModel::FREE_VARIANT) {
                        $return[$id] = $value;
                    }
                }
            }
        }
        return $return;
    }

    public function updateResponsibilityRole($itemIds, $enabled)
    {
        if ($enabled) {
            return empty($itemIds) ? _('Менеджер') : _('Специалист');
    }
        return '';
    }

    protected function export2Excel($data, $mode, $title='Sheet', $bFirstIsHeader=false) //mode={filename|download|return}
    {
        $xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
        <Workbook 
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= '<Worksheet ss:Name="' .  $title  . '" ss:Description="' .  $title  . '"><ss:Table>';
        if($bFirstIsHeader) {
            $xml .= '<ss:Row>';
            foreach ($data[0] as $value)
               $xml .= '<ss:Cell><Data ss:Type="String">' . $value . '</Data></ss:Cell>';
            $xml .= '</ss:Row>';
            unset($data[0]);
        }
        foreach ($data as $item ) {
            $xml .= '<ss:Row>';
            foreach ($item as $value)
                $xml .= '<ss:Cell><Data ss:Type="String">' . $value . '</Data></ss:Cell>';
            $xml .= '</ss:Row>';
        }
        $xml .= '</ss:Table></Worksheet>';
        $xml .= '</Workbook>';


        switch($mode)
        {
            case 'return':
                return $xml;

            case 'download':
                $request = Zend_Controller_Front::getInstance()->getRequest();
		        $contentType = strpos($request->getHeader('user_agent'), 'opera') ? 'application/x-download' : 'application/excel';
		        $fileName = date(HM_Controller_Action::EXPORT_FILENAME);
//    	ob_end_clean();
		        header('Content-type: '.$contentType);
                header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
                header("Pragma: public");
                header("Content-Transfer-Encoding: binary");
                echo $xml;
                exit();

            default:
                if(strpos($mode, '.xls')===false)
                    $mode .= '.xlsx';
                $fd = fopen($mode, 'w');
                fwrite($fd, $xml);
                fclose($fd);
        }
    }

    public function updateRole($mid, $grid)
    {
        static $rolesCache = null;
        static $basicRolesCache = null;

        if ($rolesCache === null) {

            $basicRolesCache = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);

            // оптимизация получения ролей
            $gridResult = $grid->getResult();
            $mids = array();

            foreach ($gridResult as $raw) {
                $mids[$raw['MID']] = $raw['MID'];
            }

            $rolesCache = array();

            if (count($mids)) {
                $select = $this->getService('User')->getSelect();
                $select->from('roles', array('mid', 'role'));
                $select->where('mid IN (?)', $mids);
                $allUsersRoles = $select->query()->fetchAll();
                foreach ($allUsersRoles as $userRole) {
                    $rolesCache[$userRole['mid']] = explode(',', $userRole['role']);
                }
            }
        }

        $roles = $basicRolesCache;
        $userRoles = !empty($rolesCache[$mid]) ? $rolesCache[$mid] : array();
        $userRolesIndex = array(
                HM_Role_Abstract_RoleModel::ROLE_ENDUSER => $roles[HM_Role_Abstract_RoleModel::ROLE_ENDUSER]
        );
        foreach ($userRoles as $userRole) {
            if (!isset($roles[$userRole])) {
                continue;
            }
            $userRolesIndex[$userRole] = $roles[$userRole];
        }

        // #5337 - сворачивание высоких ячеек
        //$result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount(count($fields)) . '</p>') : array();
        $result[$roles[HM_Role_Abstract_RoleModel::ROLE_ENDUSER]] = "<p>" . $roles[HM_Role_Abstract_RoleModel::ROLE_ENDUSER] . "</p>";

        foreach($userRolesIndex as $value){
            $result[$value] = "<p>{$value}</p>";
        }
        $result = array_reverse($result);
        $roleCount = count($result);

//        $result[] = ($roleCount > 1) ? '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount($roleCount) . '</p>' : '';
        $result[] = ($roleCount > 1) ? '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount($roleCount) . '</p>' : '';

        $result = array_reverse($result);

        if($result) {
            return implode('',$result);
        } else {
            return _('Нет');
        }
    }

    public function updateBoolColumn($value) {
        return $value ? _('Да') : _('Нет');
    }

    public function getBoolColumnFilter()
    {
        return array(
            1 => _('Да'),
            0 =>_('Нет')
        );
    }




    public function changeStudentAction()
    {
        $form = new HM_Form_ChangeStudent();
        $currentStudent = $this->_getParam('MID');
        $subjectId = $this->_getParam('subject_id') ? $this->_getParam('subject_id') : $this->_getParam('CID');
        if (!$subjectId) $subjectId = $this->_getParam('subjectId');
        $tcApplicationId = $this->_getParam('application_id');
        $model = $this->_getParam('model');
        $sessionQuarterId = $this->_getParam('session_quarter_id');

        if ( $this->_request->isPost() ) {

            $params = $this->_request->getParams();

            if ( $form->isValid($params) ) {
                $student = $this->getService('Student')->fetchAll(array(
                    'CID = ?' => $subjectId,
                    'MID = ?' => reset($params['new'])
                ));

                if (count($student)) {
                    $this->_flashMessenger->addMessage(_('Пользователь уже назначен на курс'));
                } else {
                    $this->_changeStudent($subjectId, $currentStudent, $params['new'], $model, $tcApplicationId);

                    $this->_flashMessenger->addMessage(_('Сотрудник успешно изменён.'));
                }

                if (!$sessionQuarterId) {
                    // Потому что subject_id это курс, на котором мы меняем юзера, а вернуться можем, например, на общую страницу /graduated
                    // Для session-quarter пока этот параметр не передавал
                    $this->_redirector->gotoSimple('index', strtolower($model), 'assign', array('subject_id' => $this->_getParam('return_subid', $subjectId)));
                } else {
                    $url = $this->view->url(array('module' => 'session-quarter', 'controller' => 'list', 'action' => 'view', 'baseUrl' => 'tc', 'session_quarter_id' => $sessionQuarterId));
                    $this->_redirector->gotoUrl($url, array('prependBase' => false));
                }

            } else {
                $form->populate($this->_request->getParams());
            }
        }

        $this->view->form = $form;
    }

    protected function _changeStudent($subjectId, $fromMid, $toMid, $model, $tcApplicationId = null)
    {
        $toMid = reset($toMid);
        $assignMethod = 'assign' . $model;
        $unassignMethod = 'unassign' . $model;

        $oldMark = $this->getService('SubjectMark')->fetchAll(
            $this->getService('SubjectMark')->quoteInto(
                array('cid=? AND ', 'mid=?'),
                array($subjectId, $fromMid)
            )
        )->current();

        $oldGraduated = $this->getService('Graduated')->fetchAll(
            $this->getService('Graduated')->quoteInto(
                array('CID=? AND ', 'MID=?'),
                array($subjectId, $fromMid)
            )
        )->current();

        $oldCertificate = $this->getService('Certificates')->fetchAll(
            $this->getService('Certificates')->quoteInto(
                array('subject_id=? AND ', 'user_id=?'),
                array($subjectId, $fromMid)
            )
        )->current();

        $this->getService('Subject')->$unassignMethod($subjectId, $fromMid);
        if ($model == 'Graduated') {
            $this->getService('Subject')->assignStudent($subjectId, $toMid);
        }
        $this->getService('Subject')->$assignMethod($subjectId, $toMid);

        $data = array(
            'mark' => $oldMark->mark ? $oldMark->mark : '1',
            'alias' => $oldMark->alias ? $oldMark->alias : '',
            'confirmed' => $oldMark->confirmed ? $oldMark->confirmed : 0,
            'comments' => $oldMark->comments ? $oldMark->comments : '',
            'certificate_validity_period' => $oldMark->certificate_validity_period
        );
        $this->getService('SubjectMark')->updateWhere(
            $data,
            $this->getService('SubjectMark')->quoteInto(
                array('cid=? AND ', 'mid=?'),
                array($subjectId, $toMid)
            )
        );

        $updatedCertificate = null;
        if ($oldCertificate) {
            $updatedCertificate = $this->getService('Certificates')->updateWhere(
                array(
                    'created' => $oldCertificate->created,
                    'name' => $oldCertificate->name,
                    'description' => $oldCertificate->description,
                    'organization' => $oldCertificate->organization,
                    'startdate' => $oldCertificate->startdate,
                    'enddate' => $oldCertificate->enddate,
                    'filename' => $oldCertificate->filename,
                    'type' => $oldCertificate->type,
                    'number' => $oldCertificate->number,
                ),
                $this->getService('Certificates')->quoteInto(
                    array('subject_id=? AND ', 'user_id=?'),
                    array($subjectId, $toMid)
                )
            );
        }

        $this->getService('Graduated')->updateWhere(
            array(
                'begin' => $oldGraduated->begin,
                'end' => $oldGraduated->end,
                'certificate_id' => $updatedCertificate->certificate_id ? $updatedCertificate->certificate_id : $oldGraduated->certificate_id,
                'created' => $oldGraduated->created,
                'status' => $oldGraduated->status,
                'score' => $oldGraduated->score,
                'progress' => $oldGraduated->progress,
                'is_lookable' => $oldGraduated->is_lookable,
                'effectivity' => $oldGraduated->effectivity,
                'application_id' => $oldGraduated->application_id
            ),
            $this->getService('Graduated')->quoteInto(
                array('CID=? AND ', 'MID=?'),
                array($subjectId, $toMid)
            )
        );

        if ($tcApplicationId) {
            $this->getService('Student')->updateWhere(
                array('application_id' => $tcApplicationId),
                array(
                    'CID = ? ' => $subjectId,
                    'MID = ? ' => $toMid
                )
            );

            $position = $this->getService('Orgstructure')->getOne(
                $this->getService('Orgstructure')->fetchAll(
                    array('mid = ? ' => $toMid)
                )
            );

            $department = $this->getService('Orgstructure')->getOne(
                $this->getService('Orgstructure')->find($position->owner_soid)
            );

            $this->getService('TcApplication')->updateWhere(
                array(
                    'user_id' => $toMid,
                    'position_id' => $position->soid,
                    'department_id' => $department->soid
                ),
                array('application_id = ? ' => $tcApplicationId)
            );
        }
    }

    public function programEventCache($programEventIds)
    {
        $keys = array_unique(explode(',', $programEventIds));

        $programEventIds = array();
        foreach ($keys as $key) $programEventIds[$key] = $key;


        $result = array();
        if (is_array($programEventIds) && (($count = count($programEventIds)) > 1)) {
            $name = _('методик');
            if ($count < 5 ) $name = _('методики');
            $result[] = sprintf("<p class=\"total\">%d %s</p>", $count, $name);
        }

        if ( count($diff = array_diff_key($programEventIds, $this->_programEventCache)) > 0  ) {
            $r = $this->getService('ProgrammEvent')->fetchAll(
                $this->getService('ProgrammEvent')->quoteInto(
                    "programm_event_id IN (?)",
                    $diff
                )
            );
            foreach ($r as $rr) $this->_programEventCache[$rr->programm_event_id] = $rr->name;

        }

        foreach($programEventIds as $programEventId) {
            $result[] = sprintf("<p>%s</p>", $this->_programEventCache[$programEventId]);
        }

        if($result)
            return implode(' ',$result);
        else
            return '';
    }

    public function subjectsCache($subjectIds)
    {
        $keys = array_unique(explode(',', $subjectIds));

        $subjectIds = array();
        foreach ($keys as $key) $subjectIds[$key] = $key;


        $result = array();
        if (is_array($subjectIds) && (($count = count($subjectIds)) > 1)) {
            $name = _('курсов');
            if ($count < 5 ) $name = _('курса');
            $result[] = sprintf("<p class=\"total\">%d %s</p>", $count, $name);
        }

        if ( count($diff = array_diff_key($subjectIds, $this->_subjectsCache)) > 0  ) {
            $r = $this->getService('Subject')->fetchAll(
                $this->getService('Subject')->quoteInto(
                    "subid IN (?)",
                    $diff
                )
            );
            foreach ($r as $rr) $this->_subjectsCache[$rr->subid] = $rr->name;

        }

        foreach($subjectIds as $subjectId) {
            $result[] = sprintf("<p>%s</p>", $this->_subjectsCache[$subjectId]);
        }

        if($result)
            return implode(' ',$result);
        else
            return '';
    }

    public function vacanciesCache($vacancyIds, $excludeVacancyId)
    {
        // если ещё не подгружали данные о вакансиях, подгружаем
        if ($this->_vacanciesCache === null) {

            $this->_vacanciesCache = array();
            $collection = $this->getService('RecruitVacancy')->fetchAll();
            $this->_vacanciesCache = $collection->getList('vacancy_id', 'name');
            $this->_vacanciesStatusCache = $collection->getList('vacancy_id', 'status');
        }

        $vacancyIds = explode(',', $vacancyIds);

        if(!count($vacancyIds) || !trim($vacancyIds[0]))
            return _('Нет');

        $result =  array();
        foreach ($vacancyIds as $key => $vacancyId) {

            if ($vacancyId == $excludeVacancyId) continue;

            $url = $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $vacancyId));
            if ($this->_vacanciesStatusCache[$vacancyId] && ($this->_vacanciesStatusCache[$vacancyId] == HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL)) {
                $result[] = "<p class='smaller'>".($this->_vacanciesCache[$vacancyId])."</p>";
            } else {
                $result[] = "<p class='smaller'><a href='{$url}'>".($this->_vacanciesCache[$vacancyId])."</a></p>";
            }
        }
        if (count($result) > 1) {
            array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('AtSession')->pluralFormCount(count($result)) . '</p>');
        }

        return implode('',$result);
    }

    public function updateCertificateType($type)
    {
        $types = HM_Certificates_CertificatesModel::getCertificateTypes();
        return $types[$type ?: HM_Certificates_CertificatesModel::TYPE_CERTIFICATE_ELS];
    }

    public function updateDepartmentPath($path)
    {
        if ($path) {
            return "<p class='smaller'>" . html_entity_decode($path) . "</p>";
        }
        return '';
    }

    public function setCostItemByAction()
    {
        /** @var HM_Controller_Request_Http $request */
        $request = $this->getRequest();

        $params = $request->getParams();

        foreach ($params as $paramName => $param) {
            if (substr($paramName, 0, 11) === 'postMassIds') {
                $request->setParam('postMassIds_grid', $param);
                break;
            }
        }

        $applicationsIds = $request->getParam('postMassIds_grid');
        $applicationsIds = explode(',', $applicationsIds);
        $costItem = $this->_getParam('costItem', null);

        if (is_array($applicationsIds) && count($applicationsIds) && $costItem) {
            $idField = ($this->_getParam('controller', '') == 'impersonal') ? 'application_impersonal_id' : 'application_id';
            $result = $this->_defaultService->updateWhere(
                array('cost_item' => $costItem),
                $this->quoteInto($idField . ' IN (?)', $applicationsIds)
            );
        }
        if ($result) {
            $this->_flashMessenger->addMessage(_('Статьи расходов успешно назначены'));
        } else {
            $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('При назначении статей расходов произошли ошибки'))
            );
        }

        $this->_redirectToIndex();
    }

    public function questRestrict()
    {
        $questId = (int) $this->_getParam('quest_id', 0);
        $quest = $this->getOne($this->getService('Quest')->find($questId));
/*
        if ($quest) {
            $isDeny = $this->getService('Quest')->isDenyByCreatorRole($quest->creator_role);

            if($isDeny) {
                $flashMessenger = $this->_helper->getHelper('FlashMessenger');
                $redirector = $this->_helper->getHelper('ConditionalRedirector');

                $flashMessenger->addMessage(_("Доступ запрещен"));
                $redirector->gotoSimpleAndExit('index', 'index', 'index');
            }
        }
*/
    }

    protected function currentUserRole($roles)
    {
        return $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), $roles);
    }

    /**
     * Получить все поля из закодированного в JSON запроса по AJAX
     *
     * Используется когда с фронтенда передаются данные, закодированные
     * в JSON в теле запроса.
     * Обязательно при запросе в заголовках должен быть
     * "X_REQUESTED_WITH" "XMLHttpRequest"
     *
     * @return array|StdClass|string|null массив данных, переданных в запросе либо null
     */
    public function getJsonParams()
    {
        /** @var HM_Controller_Request_Http $request */
        $request = $this->getRequest();
        return $request->getJsonParams();
    }

    public function getSwitcherSetOrder($index = null, $order = null, $masterOrder = null)
    {
        $default = new Zend_Session_Namespace('default');
        $page    = sprintf('%s-%s-%s',
            $this->getRequest()->getModuleName(),
            $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName()
        );
//        $this->gridId .= $index ?: ''; // обычно gridId уже содержит index (id аккордеона), если он вообще нужен
        $switcher      = $this->_getParam(
            HM_DataGrid::SWITCHER_PARAM_DEFAULT,
           isset($default->grid[$page][$this->gridId]['all']) ? $default->grid[$page][$this->gridId]['all'] : 0);
        $switcher = filter_var($switcher, FILTER_VALIDATE_INT);

        $default->grid[$page][$this->gridId]['all'] = $switcher;

        $sorting = $this->_request->getParam("order{$this->gridId}");
        if ($order && ($sorting == "")) $this->_request->setParam("order{$this->gridId}", $sorting = $order);
        if ($masterOrder) $this->_request->setParam("masterOrder{$this->gridId}", $masterOrder);

        return $switcher;
    }

    protected function getParam($paramName, $default = null)
    {
        $value = $this->_getParam($paramName, $default);
        return $value;
    }

    /**
     * Мобильный интерфейс, не ajax-запрос
     */
    public function isMobile()
    {
        $default = new Zend_Session_Namespace('default');
        return $this->_getParam('mobile', $default->isMobile);
    }

    public function exitMobile($die=true)
    {
        $script = "
<script>
    window.parent.COMMON_DATA = {
	    event_id: 'close_window'
    }
</script>";

        if($die) die($script);

        return $script;
    }


    /**
     * @param $data mixed - данные для сериализации
     * @param $params int - параметры `json_encode`
     * @return false|string
     * @throws Exception
     */
    public function jsonEncodeErrorThrow($data, $params = HM_Json::JSON_ENCODE_OPTS_DEFAULT) {
        return HM_Json::encodeErrorThrow($data, $params);
    }

    /**
     * @param $data mixed - данные для сериализации
     * @param $params int - параметры `json_encode`
     * @return false|string
     */
    public function jsonEncodeErrorReturn($data, $params = HM_Json::JSON_ENCODE_OPTS_DEFAULT)
    {
        return HM_Json::encodeErrorReturn($data, $params);
    }

    /**
     * Кодирует в json и добавляет соответствующий http-заголовок
     * На данный момент используется
     * @see HM_Json::encodeErrorThrow()
     *
     * @param $data
     * @return mixed
     */
    public function responseJson($data) {
        /**
         * @see \Zend_Controller_Action_Helper_Json::direct()
         * вызывает
         * @see \HM_Controller_Action_Helper_Json::sendJson()
         * вызывает
         * @see HM_Json::encodeErrorThrow()
         * либо
         * @see HM_Json::encodeErrorSkip()
         */
        return $this->_helper->json($data);
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    // Это попытка заюзать скомпилированные Vue.js-ом файлы в html, приходяшем в iframe модального окна.
    protected function compiledVueData($type)
    {
        $html = '';

        $localPath = '/frontend/app/' . $type . '/';
        $path  = realpath(APPLICATION_PATH . '/../public' . $localPath);
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            $url = Zend_Registry::get('view')->baseUrl() . $localPath . $file;
            switch ($type) {
                case 'css':
                    $html .= '<link href="' . $url . '" rel="stylesheet" type="text/css" />';
                    break;
                case 'js':
                    $html .= '<script src="' . $url . '"></script>';
                    break;
            }
        }

        return $html;
    }

}
