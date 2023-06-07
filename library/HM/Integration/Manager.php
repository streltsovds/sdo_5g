<?php

class HM_Integration_Manager
{
    const TRANSPORT_SOAP = 'soap';
    const TRANSPORT_FILE = 'file';
    const TRANSPORT_FTP = 'ftp';

    const TASK_ORGSTRUCTURE = 'orgstructure';
    const TASK_PROFILES = 'profiles';
    const TASK_USERS = 'users';
    const TASK_STAFF_UNITS = 'staffUnits';
    const TASK_POSITIONS = 'positions';
    const TASK_ABSENCE = 'absence';

    // потоки данных
    protected $_tasks = array();

    // источники (ДЗО)
    protected $_sources = array();

    // выполненные задачи
    protected $_processed = array();

    protected $_deferredActions = array();

    protected $_cache = array();

    protected $_method;

    public function __construct($tasks = false, $sources = false)
    {
        $this->_tasks = is_array($tasks) ? $tasks : self::getTasks();
        $this->_sources = self::getSources($sources);
        Zend_Registry::set('integrationManager', $this);
    }

    static public function getTasks()
    {
        return array(
            self::TASK_ORGSTRUCTURE => _('Организационная структура'),
            self::TASK_PROFILES => _('Профили должностей'),
            self::TASK_USERS => _('Учётные записи пользователей'),
            self::TASK_STAFF_UNITS => _('Свободные штатные единицы'),
            self::TASK_POSITIONS => _('Должности'),
            self::TASK_ABSENCE => _('Периоды отсутствия на рабочем месте (отпуск и вахта)'),
        );
    }

    static public function isExport($taskId)
    {
        return in_array($taskId, array());
    }

    static public function getSources($source = null)
    {
        $config = Zend_Registry::get('config');
        $sources = array();
        if (empty($source)) $source = HM_Integration_Abstract_Model::getLdapNames();
        foreach ($source as $key) {
            $value = $config->$key->integration->sources->toArray();
            $sources[$key] = $value;
        }

        return $sources;
    }

    public function importAll()
    {
        return $this->_processAll('import');
    }

    public function importHistory()
    {
        $config = Zend_Registry::get('config');
        if (!$config->integration->enabled) return '';

        $transport = $config->integration->transport ? : self::TRANSPORT_SOAP;
        foreach ($this->_sources as $source) {
            $taskService = HM_Integration_Abstract_Service::factory(self::TASK_USERS)
                ->setSource($source)
                ->initClient($transport);

            $taskService->getClient()->addSubDir('history');

            try {
                HM_Integration_Abstract_Manager::factory(self::TASK_USERS, $this)
                    ->setService($taskService)
                    ->log(sprintf('importHisory стартовал'))
                    ->importHistory()
                    ->log(sprintf('importHisory отработал'));

            } catch (HM_Integration_Exception $e) {
                return true;
            }
        }
    }

    public function updateAll()
    {
        return $this->_processAll('update');
    }

    public function syncAll()
    {
        return $this->_processAll('sync');
    }

    public function exportAll()
    {
        return $this->_processAll('export');
    }

    protected function _processAll($method)
    {
        $config = Zend_Registry::get('config');

        if (!$config->integration->enabled) return '';

        $this->setMethod($method);

        $transport = $config->integration->transport ? : self::TRANSPORT_SOAP;

        foreach ($this->_sources as $source) {
            foreach ($this->_tasks as $task => $taskTitle) {

                if (($method == 'export') && !self::isExport($task)) continue;
                if (($method != 'export') && self::isExport($task)) continue;

                $taskService = HM_Integration_Abstract_Service::factory($task)
                    ->setSource($source)
                    ->initClient($transport);

                try {
                    HM_Integration_Abstract_Manager::factory($task, $this)
                        ->setService($taskService)
                        ->log(sprintf('%s стартовал', ucfirst($method)))
                        ->$method($source)
                        ->log(sprintf('%s отработал', ucfirst($method)));

                    $this->_processed[$source['id']][] = $task;

                } catch (HM_Integration_Exception $e) {
                    // если одно ДЗО недоступно - продолжаем
                    // return true;
                }
            }
        }
        $this->executeDeferredActions();
    }

    public function isFirstRun($task)
    {
        foreach ($this->_processed as $tasks) {
            if (in_array($task, $tasks)) return false;
        }
        return true;
    }

    public function isAllSources()
    {
        return count($this->_sources) == count(HM_Integration_Abstract_Model::getLdapNames());
    }



    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    public function registerDeferredAction($class, $action, $order = 0)
    {
        $key = sprintf('%s::%s', $class, $action);

        if (!isset($this->_deferredActions[$key])) {
            $this->_deferredActions[$key] = $order;
        }
        return true;
    }

    public function executeDeferredAction($class, $action)
    {
        $taskManager = HM_Integration_Abstract_Manager::factory($class);
        if (method_exists($taskManager, $action)) {
            $taskManager->$action();
        }
    }

    public function executeDeferredActions()
    {
        asort($this->_deferredActions);

        if ($keys = array_keys($this->_deferredActions)) {
            $logger = Zend_Registry::get('log_integration');
            $logger->log('[DEFERRED PLAN]:' . implode(', ', $keys), Zend_Log::INFO);
        }

        foreach ($this->_deferredActions as $key => $order) {
            $method = explode('::', $key);
            $taskManager = HM_Integration_Abstract_Manager::factory($method[0]);
            if (method_exists($taskManager, $method[1])) {
                $taskManager->$method[1]();
            }
        }
        $this->_deferredActions = array();
    }

    public function cacheExists($key)
    {
        return isset($this->_cache[$key]);
    }

    public function initCache($key)
    {
        $method = sprintf('_initCache%s', ucfirst($key));
        if (!isset($this->_cache[$key]) && method_exists($this, $method)) {
            $this->_cache[$key] = $this->$method();
        }
        return true;
    }

    public function destroyCache($key)
    {
        if (isset($this->_cache[$key])) {
            unset($this->_cache[$key]);
        }
        return true;
    }

    public function getCache($cacheKey)
    {
        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }
        return false;
    }

    public function getCachedValue($key, $cacheKey)
    {
        if (isset($this->_cache[$cacheKey]) && isset($this->_cache[$cacheKey][$key])) {
            return $this->_cache[$cacheKey][$key];
        }
        return false;
    }

    public function getCachedKey($value, $cacheKey)
    {
        if (isset($this->_cache[$cacheKey])) {
            $key = array_search($value, $this->_cache[$cacheKey]);
            return $key;
        }
        return false;
    }

    public function setCachedValue($key, $value, $cacheKey)
    {
        if (isset($this->_cache[$cacheKey])) {
            $this->_cache[$cacheKey][$key] = $value;
        }
        return $this;
    }

    public function unsetCachedKey($key, $cacheKey)
    {
        if (isset($this->_cache[$cacheKey]) && isset($this->_cache[$cacheKey][$key])) {
            unset($this->_cache[$cacheKey][$key]);
        }
        return $this;
    }

    public function unsetCachedValues(Array $values, $cacheKey)
    {
        foreach ($values as $value) {
            $this->unsetCachedValue($value, $cacheKey);
        }
        return $this;
    }

    public function unsetCachedValue($value, $cacheKey)
    {
        if (isset($this->_cache[$cacheKey])) {
            if ($key = array_search($value, $this->_cache[$cacheKey])) {
                unset($this->_cache[$cacheKey][$key]);
            }
        }
        return $this;
    }

    /****************** CACHES *******************/


    protected function _initCacheMidExternal2mid()
    {
        $midExternal2mid = array();

        $select = Zend_Registry::get('serviceContainer')->getService('User')->getSelect();
        $select->from('People', array('MID', 'mid_external'))
//            ->where('blocked != 1') // разблокируем вместо создания дубликата; HM_Integration_Abstract_Manager:83
            ->where("mid_external !=''");

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                if (!empty($row['mid_external'])) {
                    $midExternal2mid[$row['mid_external']] = $row['MID'];
                }
            }
        }
        return $midExternal2mid;
    }

    // новые юзеры, созданные в ходе данной синхронизации
    protected function _initCacheNewMid2midExternal()
    {
        $newMid2midExternal = array();
        return $newMid2midExternal;
    }

    // новые юзеры, созданные в ходе данной синхронизации
    protected function _initCacheNewMid2soid()
    {
        $newMid2soid = array();
        return $newMid2soid;
    }

    protected function _initCacheSoidExternal2mid()
    {
        $usersSoidExternal = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getSelect();
        $select->from('structure_of_organ', array('mid', 'soid_external'))
            ->where('blocked != 1')
            ->where("mid !=''");

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                if (!empty($row['soid_external'])) {
                    $usersSoidExternal[$row['soid_external']] = $row['mid'];
                }
            }
        }
        return $usersSoidExternal;
    }

    protected function _initCacheSnils2mid()
    {
        $snils2mid = array();

        $select = Zend_Registry::get('serviceContainer')->getService('User')->getSelect();
        $select->from('People', array('MID', 'snils'))
            ->where('blocked != 1')
            ->where("snils != ''");

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                if (!empty($row['snils'])) {
                    $snils2mid[$row['snils']] = $row['MID'];
                }
            }
        }
        return $snils2mid;
    }

    protected function _initCacheSoidExternal2soid()
    {
        $orgstructure = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getSelect();
        $select->from('structure_of_organ', array('soid', 'soid_external'));
//            ->where('blocked != 1');

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                if (!empty($row['soid_external'])) {
                    $orgstructure[$row['soid_external']] = $row['soid'];
                }
            }
        }
        return $orgstructure;
    }

    protected function _initCacheSoidExternal2orgstructurePath()
    {
        $orgstructure = array();

        $collection = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll('type = 0');
        if (count($collection)) {
            foreach ($collection as $item) {
                if (!empty($item->soid_external)) {
                    $orgstructure[$item->soid_external] = $item->getOrgPath(true);
                }
            }
        }
        return $orgstructure;
    }

    protected function _initCacheSessionId2array()
    {
        $sessions = array();

        $select = Zend_Registry::get('serviceContainer')->getService('AtSession')->getSelect();
        $select->from('at_sessions', array('session_id', 'begin_date', 'end_date'))
            ->where('session_type = ' . HM_At_Session_SessionModel::TYPE_REGULAR);

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $sessions[$row['session_id']] = $row;
            }
        }
        return $sessions;
    }

    protected function _initCacheProfileIdExternal2profileId()
    {
        $profiles = array();

        $select = Zend_Registry::get('serviceContainer')->getService('AtProfile')->getSelect();
        $select->from('at_profiles', array('profile_id', 'profile_id_external'))
            ->where('profile_id_external IS NOT NULL');

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $profiles[$row['profile_id_external']] = $row['profile_id'];
            }
        }
        return $profiles;
    }

    protected function _initCachePositionDepartmentIdsExternal2profileId()
    {
        $profiles = array();

        $select = Zend_Registry::get('serviceContainer')->getService('AtProfile')->getSelect();
        $select->from('at_profiles', array('profile_id', 'position_id_external', 'department_id_external'))
            ->where('position_id_external IS NOT NULL')
            ->where('department_id_external IS NOT NULL')
        ;

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $profiles[implode('_', array($row['position_id_external'], $row['department_id_external']))] = $row['profile_id'];
            }
        }
        return $profiles;
    }

    protected function _initCacheProfileIdExternal2isManager()
    {
        $profiles = array();

        $select = Zend_Registry::get('serviceContainer')->getService('AtProfile')->getSelect();
        $select->from(array('ap' => 'at_profiles'), array('ap.profile_id_external', 'ap.name', 'ac.category_id_external'))
            ->joinInner(array('ac' => 'at_categories'), 'ap.category_id = ac.category_id', array())
            ->where('ac.category_id_external IS NOT NULL');

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $profiles[$row['profile_id_external']] = 0;
                $managerCategoryIds = array(
                    md5(mb_strtolower('Менеджеры')),
                    md5(mb_strtolower('Ведущие менеджеры')),
                    md5(mb_strtolower('Высшие менеджеры')),
                );
                foreach ($managerCategoryIds  as $managerCategoryId) {
                    if (strpos($row['category_id_external'], $managerCategoryId) !== false) {
                        if (count(HM_Integration_Task_Positions_Adapter::$managerExcludeNames)) {
                            foreach (HM_Integration_Task_Positions_Adapter::$managerExcludeNames  as $excludeName) {
                                if (strpos($row['name'], $excludeName) === false) {
                                    $profiles[$row['profile_id_external']] = 1;
                                }
                            }
                        } else {
                            $profiles[$row['profile_id_external']] = 1;
                        }
                        break;
                    }
                }
            }
        }
        return $profiles;
    }

    protected function _initCacheProfileId2baseProfileId()
    {
        $profiles = array();

        $select = Zend_Registry::get('serviceContainer')->getService('AtProfile')->getSelect();
        $select->from('at_profiles', array('profile_id', 'base_id'))
            ->where('base_id IS NOT NULL AND base_id != 0');

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $profiles[$row['profile_id']] = $row['base_id'];
            }
        }
        return $profiles;
    }

    protected function _initCacheSoid2customProfileId()
    {
        $soids = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getSelect();
        $select->from(array('soo' => 'structure_of_organ'), array('soo.soid', 'soo.profile_id'))
            ->join(array('ap' => 'at_profiles'), 'soo.profile_id = ap.profile_id', array())
            ->where('ap.profile_id_external IS NULL');

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $soids[$row['soid']] = $row['profile_id'];
            }
        }
        return $soids;
    }

    protected function _initCacheCategoryIdExternal2categoryId()
    {
        $profiles = array();

        $select = Zend_Registry::get('serviceContainer')->getService('AtCategory')->getSelect();
        $select->from('at_categories', array('category_id', 'category_id_external'))
            ->where('category_id_external IS NOT NULL');

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $profiles[$row['category_id_external']] = $row['category_id'];
            }
        }
        return $profiles;
    }

    protected function _initCacheStaffUnitIdExternal2staffUnitId()
    {
        $staffUnits = array();

        $select = Zend_Registry::get('serviceContainer')->getService('StaffUnit')->getSelect();
        $select->from('staff_units', array('staff_unit_id', 'staff_unit_id_external'));

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $staffUnits[$row['staff_unit_id_external']] = $row['staff_unit_id'];
            }
        }
        return $staffUnits;
    }

    protected function _initCacheAbsenceIdExternal2absenceId()
    {
        $absence = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Absence')->getSelect();
        $select->from('absence', array('absence_id', 'user_id', 'user_external_id', 'absence_begin', 'absence_end'));

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $begin = explode(' ', $row['absence_begin']);
                $end   = explode(' ', $row['absence_end']);
                $key   = implode('-', array($row['user_external_id'], $begin[0], $end[0]));
                $absence[$key] = $row['absence_id'];
            }
        }
        return $absence;
    }

    protected function _initCacheUniversity2classifierId()
    {
        $classifiers = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Classifier')->getSelect();
        $select->from('classifiers', array('classifier_id', 'name'))
            ->where('type = ?', 2); //HM_Classifier_Type_TypeModel::BUILTIN_TYPE_UNIVERSITIES

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $classifiers[$row['name']] = $row['classifier_id'];
            }
        }
        return $classifiers;
    }

    protected function _initCacheSpeciality2classifierId()
    {
        $classifiers = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Classifier')->getSelect();
        $select->from('classifiers', array('classifier_id', 'name'))
            ->where('type = ?', 3); //HM_Classifier_Type_TypeModel::BUILTIN_TYPE_SPECIALITIES

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $classifiers[$row['name']] = $row['classifier_id'];
            }
        }
        return $classifiers;
    }

    protected function _initCacheCity2classifierId()
    {
        $classifiers = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Classifier')->getSelect();
        $select->from('classifiers', array('classifier_id', 'name'))
            ->where('type = ?', 1); //HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $classifiers[$row['name']] = $row['classifier_id'];
            }
        }
        return $classifiers;
    }
}