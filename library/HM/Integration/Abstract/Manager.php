<?php

abstract class HM_Integration_Abstract_Manager implements HM_Integration_Interface_Manager
{
    const LOG_LEVEL_REGULAR = 0;
    const LOG_LEVEL_PARANOID = 1;

    const TASK_STATUS_SUCCESS = 1;
    const TASK_STATUS_FAILURE = 0;

    protected $_logger;

    protected $_service;

    protected $_targetService;
    protected $_targetServiceName;
    protected $_targetServiceTable;
    protected $_targetServiceAdapter;
    protected $_targetServiceMapper;

    protected $_adapter;
    protected $_adapterName;

    static public function factory($task)
    {
        $class = sprintf('HM_Integration_Task_%s_Manager', ucfirst($task));
        if (class_exists($class)) {
            $object = new $class;
            $object->initTargetService();
            return $object;
        }
        return null;
    }


    public function import($integrationSource)
    {
        try {
            $this->getTargetService()->deleteBy('');
            $items = $this->getService()->fetchAll();

            foreach($items as $item) {

                $model = $this->getAdapter()
                    ->init(new HM_Integration_Abstract_Model())
                    ->convert($item)
                    ->getModel();

                $this->_insert($model);
            }
        } catch (Exception $e) {
            $this->breakAll($e->getMessage());
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);
        return $this;
    }


    public function update($integrationSource)
    {
        try {
            $items = $this->getService()->fetchChanged();

            $cntDeleted = $cntUpdated = $cntInserted = 0;
            foreach($items as $item) {

                try {
                    $model = $this->getAdapter()
                        ->init(new HM_Integration_Abstract_Model())
                        ->getModel();

                    $this->getAdapter()->convert($item);

                    $match = $this->match($model);
                    if ($this->todel($model)) {
                        if ($match) {
                            $this->_delete($match);
                            $cntDeleted++;
                        }
                    } else {
                        if ($match) {
                            $this->_unblock($model) // разблокируем вместо создания дубликата, если применимо
                                ->_update($model, $match);
                            $cntUpdated++;
                        } else {
                            $this->_insert($model);
                            $cntInserted++;
                        }
                    }
                } catch (Exception $e) {
                    $this->log(sprintf('Ошибка синхронизации записи %s: %s', $model->getExternalId(), $e->getMessage()));
                }
            }

            if (get_class($this) == 'HM_Integration_Task_Absence_Manager') {
                $this->log(sprintf('Добавлено: %s, удалено: %s', $cntInserted, $cntDeleted));
            } else {
                $this->log(sprintf('Добавлено: %s, изменено: %s, удалено: %s', $cntInserted, $cntUpdated, $cntDeleted));
            }

        } catch (Exception $e) {
            $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_FAILURE);
            $this->log('Общая ошибка, синхронизация остановлена. ' . $e->getMessage());
            return $this;
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);
        return $this;
    }

    public function sync($integrationSource)
    {
        try {

            $service = $this->getService()->setSource($integrationSource);
            $items = $service->fetchAll();
            $existingItems = $this->_fetchAll($integrationSource);

            $cntDeleted = $cntUpdated = $cntInserted = 0;
            foreach($items as $item) {

                try {
                    $nestedModels = array(
                        'HM_Integration_Task_Orgstructure_Manager',
                        'HM_Integration_Task_Positions_Manager',
                    );
                    $abstractModel = (in_array(get_class($this), $nestedModels)) ? new HM_Integration_Abstract_Model_Nested() : new HM_Integration_Abstract_Model();
                    $model = $this->getAdapter()
                        ->init($abstractModel)
                        ->getModel();

                    $this->getAdapter()->convert($item);

                    $match = $this->match($model);
                    if ($this->todel($model)) {
                        if ($match) {
                            $this->_delete($match);
                            $cntDeleted++;
                        }
                    } else {
                        if ($match) {
                            $updates = $this->needUpdate($model, $match);
                            if (count($updates)) {
                                $model->setAttributes($updates);
                                $cntUpdated++;
                            }
                            $this->_unblock($model)
                                ->_update($model, $match);

                        } else {
                            $this->_insert($model);
                            $cntInserted++;
                        }
                    }

                    if (isset($existingItems[$match])) {
                        unset($existingItems[$match]);
                    }
                } catch (Exception $e) {
                    $this->log(sprintf('Ошибка синхронизации записи %s: %s', $model->getExternalId(), $e->getMessage()));
                }
            }
            
            // остались те, кого вообще нет в новых данных
            // их удалить
            foreach ($existingItems as $existingItem) {
                $this->_delete($existingItem);
            }

            if (get_class($this) == 'HM_Integration_Task_Absence_Manager') {
                $this->log(sprintf('Добавлено: %s, удалено: %s', $cntInserted, $cntDeleted));
            } else {
                $this->log(sprintf('Добавлено: %s, изменено: %s, удалено: %s', $cntInserted, $cntUpdated, $cntDeleted + count($existingItems)));
            }

        } catch (Exception $e) {
            $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_FAILURE);
            $this->log('Общая ошибка, синхронизация остановлена. ' . $e->getMessage());
            return $this;
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);
        return $this;
    }

    public function match(HM_Integration_Abstract_Model $model)
    {
        return false;
    }

    public function todel(HM_Integration_Abstract_Model $model)
    {
        $source = $model->getSource();
        return $source['isDeleted'];
    }

    protected function breakAll($msg = '')
    {
        $this->log('Критичная ошибка синхронизации, дальнейшее выполнение невозможно. ' . $msg);
        throw new HM_Integration_Exception();
    }

    /**
     * @return mixed
     */
    public function getMasterManager()
    {
        return $this->_masterManager;
    }

    /**
     * @param mixed $masterManager
     */
    public function setMasterManager($masterManager)
    {
        $this->_masterManager = $masterManager;
        return $this;
    }

    public function initTargetService()
    {
        $this->_targetServiceMapper = $mapper = $this->getTargetService()->getMapper();
        $this->_targetServiceTable = $table = $mapper->getTable();
        $this->_targetServiceAdapter = $table->getAdapter();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        try {
            $logger = Zend_Registry::get('log_integration');
        } catch (Exception $e) {
            $logger = Zend_Registry::get('log_system');
        }
        return $logger;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTargetService()
    {
        if (empty($this->_targetService)) {
            $this->_targetService = Zend_Registry::get('serviceContainer')->getService($this->_targetServiceName);
        }
        return $this->_targetService;
    }

    /**
     * @param mixed $targetService
     */
    public function setTargetService($targetService)
    {
        $this->_targetService = $targetService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdapter()
    {
        if (empty($this->_adapter)) {
            $task = ucfirst($this->getTaskId());
            $class = sprintf("HM_Integration_Task_%s_Adapter", ucfirst($task));
            $this->_adapter = new $class($this->_masterManager);
        }
        return $this->_adapter;
    }

    /**
     * @param mixed $adapter
     */
    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    protected function _insert(HM_Integration_Abstract_Model $model)
    {
        $this->_targetServiceAdapter->insert($this->_targetServiceTable->getTableName(), $model->getAttributes());
        $model->setId($this->_targetServiceAdapter->lastInsertId());

        return $model;
    }

    protected function _update(HM_Integration_Abstract_Model $model, $id)
    {
        $primaryKey = $this->_targetServiceTable->getPrimaryKey();
        if (is_array($primaryKey)) $primaryKey = array_shift($primaryKey); // почему array?

        $this->_targetServiceAdapter->update(
            $this->_targetServiceTable->getTableName(),
            $model->getAttributes(),
            $this->_targetServiceAdapter->quoteInto($primaryKey . ' = ?', $id)
        );

        return true;
    }

    // переопределить там, где применимо
    protected function _unblock($model)
    {
        return $this;
    }

    protected function _delete($match)
    {
        $primaryKey = $this->_targetServiceTable->getPrimaryKey();
        if (is_array($primaryKey)) $primaryKey = array_shift($primaryKey); // почему array?

        $this->_targetServiceAdapter->delete(
            $this->_targetServiceTable->getTableName(),
            $this->_targetServiceAdapter->quoteInto($primaryKey . ' = ?', $match)
        );

        return true;
    }

    protected function _softDelete($match)
    {
        $primaryKey = $this->_targetServiceTable->getPrimaryKey();
        if (is_array($primaryKey)) $primaryKey = array_shift($primaryKey); // почему array?

        $this->_targetServiceAdapter->update(
            $this->_targetServiceTable->getTableName(),
            array('blocked' => 1),
            $this->_targetServiceAdapter->quoteInto($primaryKey . ' = ?', $match)
        );

        return true;
    }

    protected function _fetchAll($integrationSource)
    {
        $primaryKey = $this->_targetServiceTable->getPrimaryKey();

        if ($keyExternal = $this->getAdapter()->getKeyExternal()) {
            $items = $this->_targetService->fetchAll(
                $this->_targetService->quoteInto("{$keyExternal} LIKE '{$integrationSource['key']}-%'")
            );
        }

        return $items ? $items->getList($primaryKey) : array();
    }

    public function log($message, $level = self::LOG_LEVEL_REGULAR)
    {
        $config = Zend_Registry::get('config');
        $configLevel = $config->integration->log_level;

        if ($level <= $configLevel) {
            $source = $this->getService() ? $this->getService()->getSource() : false;
            $messages = array(
                $source ? $source['title'] : '[DEFERRED]',
                $this->getTaskId(),
                $message
            );
            $this->getLogger()->log(implode(' :: ', $messages), Zend_Log::INFO);
            echo sprintf('<div style="%s">%s</div>', strpos(strtolower($message), 'ошибка') !== false ? 'color: red' : '',  implode(' :: ', $messages));
        }
        return $this;
    }

    public function answer($status)
    {
        try {
            $client = $this->_service->getClient();
            $client->answer($status);
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }
}