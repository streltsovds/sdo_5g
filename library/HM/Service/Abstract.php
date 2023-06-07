<?php

/*abstract*/ class HM_Service_Abstract
{
    protected $_mapperClass = null;
    protected $_modelClass = null;
    protected $_adapterClass = null;

    protected $_mapper = null;
    protected $_serviceContainer = null;
    protected $_acl = null;

    /**
     * Кэш произвольного назначения
     * Чтобы не обращаться к БД в циклах
     *
     * По аналогии с @see HM_Collection_Abstract::$_cache
     * @var array
     */
    protected $_cache = [];

    public function __construct($mapperClass = null, $modelClass = null, $adapterClass = null)
    {
        if (null !== $mapperClass) {
            $this->_mapperClass = $mapperClass;
        }

        if (null !== $modelClass) {
            $this->_modelClass = $modelClass;
        }

        if (null !== $adapterClass) {
            $this->_adapterClass = $adapterClass;
        }

        $className = substr(get_class($this), 0, -7); // trim Service
        if (null === $this->_mapperClass) {
            $this->_mapperClass = $className . 'Mapper';
        }

        if (null === $this->_modelClass) {
            $this->_modelClass = $className . 'Model';
        }

        if (null === $this->_adapterClass) {
            $this->_adapterClass = $className . 'Table';
        }

        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->suppressNotFoundWarnings(true);

        if ($loader->autoload($this->_mapperClass)
            && $loader->autoload($this->_modelClass)) {

                $this->setMapper(new $this->_mapperClass($this->_adapterClass, $this->_modelClass));
                //$this->getMapper()->setModelClass($this->_modelClass);
        }

    }

    public function setAcl($acl)
    {
        $this->_acl = $acl;
    }

    public function getAcl()
    {
        return $this->_acl;
    }

    public function setServiceContainer($serviceContainer)
    {
        $this->_serviceContainer = $serviceContainer;
    }

    public function getServiceContainer()
    {
        return $this->_serviceContainer;
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        $service = $this->getServiceContainer()->getService($name);
        if (method_exists($service, 'getServiceContainer')) {
            if (null == $service->getServiceContainer()) {
                $service->setServiceContainer($this->getServiceContainer());
            }
        }
        return $service;
    }
    public function setMapper(HM_Mapper_Abstract $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     *
     * @return HM_Mapper_Abstract
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * TODO: Избавиться от $unsetNull, это в корне не правильно - удалять все NULL
     *
     * @param $data
     * @param bool $unsetNull
     * @return HM_Model_Abstract
     */
    public function insert($data, $unsetNull = true)
    {
        if (is_array($data)) {

            if ($unsetNull) {
                // insert NULL в поле NOT NULL даёт ошибку БД в оракле и mssql
                foreach ($data as $key => $value) {
                    if ($value === null) {
                        unset($data[$key]);
                    }
                }
            }

            //Вообще тут это не нужно нафик
            try{
                $pk = $this->getMapper()->getTable()->getPrimaryKey();
                $pKey = 0;
                if(!is_array($pk)){
                    if (isset($data[$pk])) {
                        $pKey = $data[$pk];
                    } else {
//                        index_php_log(__METHOD__ . ': pk "' . $pk . '" from mapper is not set in data');
                    }
                }

                $this->getService('Log')->log(
                    $this->getService('User')->getCurrentUserId(),
                    'INSERT',
                    'Success',
                    Zend_Log::NOTICE,
                    get_class($this),
                    $pKey
                );
            }
            catch(Exception $e){

            }
            return $this->getMapper()->insert(call_user_func_array(array($this->_modelClass, 'factory'), array($data, $this->_modelClass)));
        }
    }

    public function update($data, $unsetNull = true)
    {
        if (is_subclass_of($data, 'HM_Model_Abstract')) {
            $data = $data->getData();
        }

        if (is_array($data)) {

            if ($unsetNull) {
                // insert NULL в поле NOT NULL даёт ошибку БД в оракле и mssql
                foreach ($data as $key => $value) {
                    if ($value === null) {
                        unset($data[$key]);
                    }
                }
            }

            //Вообще тут это не нужно нафик
            try{
                $pk = $this->getMapper()->getTable()->getPrimaryKey();
                $pKey = 0;
                if(!is_array($pk)){
                    $pKey = $data[$pk];
                }

                $model = $this->getMapper()->update(call_user_func_array(array($this->_modelClass, 'factory'), array($data, $this->_modelClass)));

                if(!$pKey)
                	$pKey = $model->getPrimaryKey();

                $this->getService('Log')->log(
                    $this->getService('User')->getCurrentUserId(),
                    'UPDATE',
                    'Success',
                    Zend_Log::NOTICE,
                    get_class($this),
                    $pKey
                );
            }
            catch(Exception $e){

            }
            return $model;
        }
    }

    public function updateWhere($data, $where){
        if (is_array($data)) {
            return $this->getMapper()->updateWhere($data, $where);
        }else{
            return false;
        }

    }

    public function delete($id)
    {
        //Вообще тут это не нужно нафик
        try{
            $this->getService('Log')->log(
                $this->getService('User')->getCurrentUserId(),
                'DELETE',
                'Success',
                Zend_Log::NOTICE,
                get_class($this),
                $id
            );
        }
        catch(Exception $e){

        }

        return $this->getMapper()->delete($id);
    }

    public function deleteBy($where)
    {
        try{
            $this->getService('Log')->log(
                $this->getService('User')->getCurrentUserId(),
                'DELETE',
                'Success',
                Zend_Log::NOTICE,
                get_class($this),
                str_replace(array("\r", "\n"), ' ', print_r($where, true))
            );
        }
        catch(Exception $e){

        }

        return $this->getMapper()->deleteBy($where);
    }

    /**
     * @return HM_Collection
     */
    public function find()
    {
        $args = func_get_args();

        return call_user_func_array(array($this->getMapper(), 'find'), $args);
    }

    public function findOne()
    {
        $args = func_get_args();
        $return = call_user_func_array(array($this->getMapper(), 'find'), $args);

        return $this->getOne($return);
    }

    public function fetchRow($where = null, $order = null)
    {
        return $this->getMapper()->fetchRow($where, $order);
    }

    /**
     * @param  $where
     * @param  $order
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }

    public function fetchOne($where = null)
    {
        return $this->getOne($this->getMapper()->fetchAll($where, null, 1, 0));
    }

    public function countAll($where = null)
    {
        return $this->getMapper()->countAll($where);
    }

    public function findDependence()
    {
        $args = func_get_args();
        return call_user_func_array(array($this->getMapper(), 'findDependence'), $args);
    }

    /**
     *  Метод для получения нескольких вложенных зависимостей
     *  Возвращает коллекцию из одного элемента (так же как find())
     *  Если вложенные зависимости найдены - они цепляются к соответствующим property в виде массивов объектов (не коллекции!)
     *  Количество уровней вложенности теоретически не ограничено
     *
     *  ВАЖНО! Все участвующие модели должны иметь primaryName и serviceName
     *
     *   Пример использования:
     *   $session = $this->getService('AtSession')->findMultiDependence(array(
     *       'vacancy'            => 'Vacancy',
     *       'evaluations'        => 'Evaluation',
     *       'criteriaPersonal'   => array('CriterionPersonal', 'EvaluationCriterion'),
     *       'quest'              => 'Quest',
     *   ), $session_id);
     *
     * @param array $dependences
     * @param int $id
     * @return HM_Collection
     */
    public function findMultiDependence($dependences, $id)
    {
        $levels = array();
        $dependenceKeys = array_keys($dependences);
        $dependences = array_values($dependences);

        if (is_array($refName = $dependences[0])) {
            $collection = $this->findManyToMany($refName[0], $refName[1], $id);
        } else {
            $collection = $this->findDependence($refName, $id);
        }
        $cloneCollection = clone $collection;

        for ($i = 0; $i < count($dependences); $i++) {

            $refName = $dependences[$i];
            $childRefName = $dependences[$i+1];
            $itemName = $dependenceKeys[$i];
            $childItemName = $dependenceKeys[$i+1];

            if (count($collection)) {

                $serviceName = $primaryName = false;
                $itemIds = $items = array();

                foreach ($collection as $item) {
                    if (count($item->$itemName)) {

                        $item->$itemName->rewind();
                        if (empty($serviceName)) $serviceName = $item->$itemName->current()->getServiceName();
                        if (empty($primaryName)) $primaryName = $item->$itemName->current()->getPrimaryName();

                        $itemIds = $itemIds + $item->$itemName->getList($primaryName);
                        $items = $items + $item->$itemName->asArrayOfObjects();
                    }
                }

                if ($serviceName && $primaryName) {
                    if (is_array($childRefName)) {
                        $childCollection = $this->getService($serviceName)->fetchAllManyToMany($childRefName[0], $childRefName[1], array("{$primaryName} IN (?)" => $itemIds));
                    } else {
                        $childCollection = $this->getService($serviceName)->fetchAllDependence($childRefName, array("{$primaryName} IN (?)" => $itemIds));
                    }

                    if (count($childCollection)) {
                        //$cloneCollection = clone $childCollection;
                        foreach ($childCollection as $childItem) {
                            if (count($childItem->$childItemName)) {
                                $items[$childItem->$primaryName]->$childItemName = $childItem->$childItemName->asArrayOfObjects();
                            }
                        }
                    }
                    $levels[] = $items;
                    $collection = $childCollection;

                } else {
                    return $cloneCollection;
                }
            }
        }

        for ($i = count($levels)-1; $i > 0; $i--) {
            foreach ($levels[$i-1] as &$item) {
                $key = $dependenceKeys[$i];
                if (count($item->$key)) {
                    $replacement = array();
                    foreach ($item->$key as $id => $value) {
                        $replacement[$id] = $levels[$i][$id];
                    }
                    $item->$key = $replacement;
                }
            }
        }

        if (count($levels[0])) {
            $cloneCollection->current()->$dependenceKeys[0] = $levels[0];
        }
        return $cloneCollection;
    }

    /**
     * @param  $joinDependence
     * @param  $where
     * @param  $order
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAllDependence($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllDependence($dependence, $where, $order, $count, $offset);
    }

    public function fetchOneDependence($dependence = null, $where = null)
    {
        return $this->getOne($this->getMapper()->fetchAllDependence($dependence, $where, null, 1, 0));
    }

    /**
     * @param  $joinDependence
     * @param  $where
     * @param  $order
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAllDependenceJoinInner($joinDependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllDependenceJoinInner($joinDependence, $where, $order, $count, $offset);
    }

    public function fetchAllJoinInner($joinDependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllJoinInner($joinDependence, $where, $order, $count, $offset);
    }


    public function countAllDependenceJoinInner($joinDependence = null, $where = null)
    {
        return $this->getMapper()->countAllDependenceJoinInner($joinDependence, $where);
    }

    public function findManyToMany()
    {
        $args = func_get_args();

        return call_user_func_array(array($this->getMapper(), 'findManyToMany'), $args);
    }

    public function fetchAllManyToMany($dependence = null, $intersection = null, $where = null, $order = null, $count= null, $offset =null)
    {
        return $this->getMapper()->fetchAllManyToMany($dependence, $intersection, $where, $order, $count, $offset);
    }

    public function fetchAllHybrid($dependence = null, $ManyToManyDependence = null, $ManyToManyIntersection = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllHybrid($dependence, $ManyToManyDependence, $ManyToManyIntersection, $where, $order, $count, $offset);
    }

    public function quoteInto($where, $args)
    {
        if (is_array($where)) {
            // Стараться не использовать $where в виде массива: полезно только:
            // когда в аргументах $where нет скобочек (все на одном уровне) и
            // 1) либо $args в конце могут быть не заданы и тогда соответствующие им условия
            // в конце $where отбросятся.
            //
            // 2) либо $where и $args собираются синхронно в цикле в массиве
            $quotedWhere = '';
            reset($where);
            $firstKey = key($where);
            foreach($where as $key => $w) {
                if (isset($args[$key])) {
                    $quotedWhere .= $this->getMapper()->getTable()->getAdapter()->quoteInto($w, $args[$key]);
                } elseif (!is_int($key)) {
                    $conditionPrefix = ($key === $firstKey) ? '' : ' AND ';
                    $quotedWhere .= $this->getMapper()->getTable()->getAdapter()->quoteInto($conditionPrefix . $key, $w);
                }
            }
            return $quotedWhere;
        } else {
            return $this->getMapper()->getTable()->getAdapter()->quoteInto($where, $args);
        }
    }

    public function quoteIdentifier($ident)
    {
        return $this->getMapper()->getTable()->getAdapter()->quoteIdentifier($ident, true);
    }

    public function getDateTime($time = null, $onlyDate = false)
    {
        if (null == $time) {
            $time = time();
        }
        return ($onlyDate)? date('Y-m-d', $time) : date('Y-m-d H:i:s', $time);
    }

    /**
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        return $this->getMapper()->getTable()->getAdapter()->select();
    }

    /**
     * @param  $collection
     * @return bool | HM_Model_Abstract
     */
    public function getOne($collection)
    {
        if (count($collection)) {
            return $collection->current();
        }
        return false;
    }

    /**
     * @param  $where
     * @param  $order
     * @param  $dependence
     * @param  $intersection
     * @param  $ManyToManyDependence
     * @return Zend_Paginator
     */
    public function getPaginator($where = null, $order = null, $dependence = null, $intersection = null, $ManyToManyDependence = null)
    {
        $this->getMapper()->setPaginatorOptions(array(
            'where' => $where,
            'order' => $order,
            'dependence' => $dependence,
            'intersection' => $intersection,
            'mtm_dependence' => $ManyToManyDependence
        ));

        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(
          'pager.tpl'
        );
        //$paginator->setView($view);

        $paginator = new Zend_Paginator($this->getMapper());
        $paginator->setCurrentPageNumber(Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1));
        //$paginator->setItemCountPerPage(5);

        return $paginator;
    }

    public function getResults($lessonId, $userId)
    {
        $lesson = $this->getOne($this->find($lessonId));
        if ($lesson) {
            switch($lesson->typeID) {
                // todo: Another lesson types
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                case HM_Event_EventModel::TYPE_POLL:
                case HM_Event_EventModel::TYPE_EXERCISE:
                case HM_Event_EventModel::TYPE_TASK:
                case HM_Event_EventModel::TYPE_TEST:
                    return $this->getService('TestResult')->fetchAll(
                        $this->quoteInto(
                            array('mid = ?', ' AND sheid = ?'),
                            array($userId, $lessonId)
                        ),
                        'stid DESC'
                    );
                    break;
            }
        }

        return new HM_Collection(array());
    }

    public function isDeletable($itemId)
    {
        return true;
    }

    public function idEditable($itemId)
    {
        return true;
    }

    /*
     * Получаем шаги статических процессов, определённых в processes.xml
     *
     * @param string $processName - имя элемента в xml, содержащего соотв. набор настроек
     *
     * @return array
     */
    public function getStepsFromProcessesXml($processName = null)
    {
        $steps = array();
        if (is_null($processName) || empty($processName)) return array();

        $processXml = APPLICATION_PATH . '/settings/processes.xml';
        $xml = simplexml_load_file($processXml);
        foreach ($xml->{$processName}->states->state as $state) {
            $steps[] = (string) $state->class;
        }
        return $steps;
    }

    public function cleanUpCache($infoblocks, $userIds)
    {
        if (!is_array($infoblocks) && ($infoblocks != Zend_Cache::CLEANING_MODE_ALL))
            $infoblocks = array($infoblocks);
        if (!is_array($userIds) && ($userIds != Zend_Cache::CLEANING_MODE_ALL))
            $userIds = array($userIds);

        $this->removeFromCache($infoblocks, $userIds);
    }

    public function connect($name, $listener)
    {
    }

    private function removeFromCache($infoblocks, $userIds)
    {
        $cache = Zend_Registry::get('cache');
        if (($infoblocks == Zend_Cache::CLEANING_MODE_ALL) || ($userIds == Zend_Cache::CLEANING_MODE_ALL)) {
            $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
            return;
        }

        foreach ($infoblocks as $infoblock) {
            foreach ($userIds as $userId) {
                $key = sprintf('widget_%s_%s', $infoblock, $userId);
                // memcache не поддерживает тэги
                $cache->remove($key);
            }
        }

    }

    /**
     * @return array
     */
    public function getCache()
    {
        return $this->_cache;
    }

    public function getCachedValue($cacheType, $key)
    {
        if (isset($this->_cache[$cacheType][$key])) {
            return $this->_cache[$cacheType][$key];
        }
        return false;
    }

    /**
     * @param array $cache
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    public function addCache($cacheType, $data)
    {
        $this->_cache[$cacheType] = $data;
        return $this;
    }

    public function add2Cache($cacheType, $data, $key = null)
    {
        if(is_null($key))
            $this->_cache[$cacheType][] = $data;
        else
            $this->_cache[$cacheType][$key] = $data;

        return $this;
    }

    public function getTableName()
    {
        return $this->getMapper()->getAdapter()->getTableName();
    }
}