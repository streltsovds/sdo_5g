<?php

class IntegrationController extends HM_Controller_Action {

    public function indexAction()
    {
        $form = new HM_Form_Integration();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getParams();
            if ($form->isValid($params)) {
                $selectedTasks = array();
                $tasks = HM_Integration_Manager::getTasks();
                foreach ($params as $param => $value) {
                    if (false !== strpos($param, 'task')) {
                        list($task) = explode('_', $param);
                        if ($value) $selectedTasks[$task] = $tasks[$task];
                    }
                }

                $ad = $request->getParam('ad');
                $function = $request->getParam('type');
                $key = $request->getParam('source');
                $sourceNames = HM_Integration_Abstract_Model::getLdapNamesForSelect();
                $source = ($key != 0) ? $sourceNames[$key] : HM_Integration_Abstract_Model::getLdapNames();
                call_user_func(array(get_class($this), $function), $selectedTasks, $source);
                if ($ad) $this->ad($source);
            }
        }

        $this->view->form = $form;
    }

    public function ad($sources = false)
    {
        $this->prepend();
        if ($sources) $sources = is_array($sources) ? $sources : array($sources);

        foreach ($sources as $ldap) {
            $importManager = new HM_User_Import_AdManager();
            $userAdService = $this->getService('UserAd');
            $userAdService->getMapper()->getAdapter()->getLdap()->setLdapOptions($ldap);
            $importManager->init($userAdService->fetchAllByLdap($ldap));
            $importManager->import();
        }
    }

    public function import($tasks = false, $sources = false)
    {
        $this->prepend();
        if ($sources) $sources = is_array($sources) ? $sources : array($sources);

        $importManager = new HM_Integration_Manager($tasks, $sources);
        $importManager->importAll();
    }

    public function update($tasks = false, $sources = false)
    {
        $this->prepend();
        if ($sources) $sources = is_array($sources) ? $sources : array($sources);

        $importManager = new HM_Integration_Manager($tasks, $sources);
        $importManager->updateAll();
    }

    public function sync($tasks = false, $sources = false)
    {
        $this->prepend();
        if ($sources) $sources = is_array($sources) ? $sources : array($sources);

        if (count(array_intersect(array_keys($tasks), array(
            HM_Integration_Manager::TASK_ORGSTRUCTURE,
            HM_Integration_Manager::TASK_POSITIONS,
        )))) {
            exit(_('Это плохая идея'));
        }

        $importManager = new HM_Integration_Manager($tasks, $sources);
        $importManager->syncAll();
    }

    public function importAction()
    {
        $importManager = $this->getManager();
        $importManager->importAll();

        exit();
    }


    // положить файлы с иcторией в /data/integration/prod/history
    public function importHistoryAction()
    {
        $importManager = $this->getManager();
        $importManager->importHistory();

        exit();
    }

    public function updateAction()
    {
        $importManager = $this->getManager();
        $importManager->updateAll();

        exit();
    }

    public function syncAction()
    {
        $importManager = $this->getManager();
        $importManager->syncAll();

        exit();
    }

    public function exportAction()
    {
        $exportManager = $this->getManager();
        $exportManager->exportAll();

        $this->_flashMessenger->addMessage(_('Экспорт завершен'));
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }

    public function testAction()
    {
        $importManager = $this->getManager();
        $importManager->importAll();
        $importManager->updateAll();
        $importManager->syncAll();
        $importManager->exportAll();

        exit('done.');
    }

    protected function getManager()
    {
        $task = $this->_getParam('tasks', '');
        $source = $this->_getParam('sources', '');

        if (empty($task)) {
            $task = null;
        } else {
            $taskResults = array();
            $tasks = HM_Integration_Manager::getTasks();
            $paramTasks = explode(',' , $task);
            foreach ($paramTasks as $task) {
                if (isset($tasks[$task])) {
                    $taskResults[$task] = $tasks[$task];
                }
            }
        }
        if (empty($taskResults)) $taskResults = null;

        if (empty($source)) {
            $source = null;
        } else {
            $sourceResults = array();
            $sources = HM_Integration_Manager::getSources();
            $paramsources = explode(',' , $source);
            foreach ($paramsources as $source) {
                if (isset($sources[$source])) {
                    $sourceResults[] = $source;
                }
            }
        }
        if (empty($sourceResults)) $sourceResults = null;

        $this->prepend();

        return new HM_Integration_Manager($taskResults, $sourceResults);
    }

    protected function prepend()
    {
        if (!$this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_ADMIN)) {
            exit('Недостаточно прав. Для запуска интеграции необходимо обладать правами Администратора.');
        }
        setlocale(LC_ALL, 'ru_RU.UTF8'); // чтобы работал fgetcsv в процессе импорта из 1С
    }
}