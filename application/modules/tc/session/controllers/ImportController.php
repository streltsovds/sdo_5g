<?php
class Session_ImportController extends HM_Controller_Action_Import
{
    protected $_importManagerClass = 'HM_Tc_Application_Import_Manager';

    public function indexAction()
    {
        $sessionId = $this->getRequest()->getParam('session_id');

        if (!$sessionId) {
            $session = $this->_autoCreateSession();
            $this->_redirector->gotoSimple('index', 'import', 'session', array(
                'source' => 'template',
                'session_id' => $session->session_id,
            ));
        }

        $url = array('module' => 'session', 'controller' => 'list', 'action' => 'index');
        $returnUrl = $this->view->url($url, null, true);
        Zend_Registry::get('session_namespace_default')->tsSessions['import'] = array(
            'returnUrl' => $returnUrl,
            'sessionId' => $sessionId,
        );

        parent::indexAction(); // required

        if ($this->_valid && !$this->_importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые записи не найдены'));
            $this->_redirector->gotoUrl($returnUrl, array('prependBase' => false));
        }

        $this->view->returnUrl = $returnUrl;
        $this->view->sessionId = $sessionId;

        if ($this->_request->isPost()) {
            $this->processAction();
        }

    }

    public function template()
    {
        $this->view->setHeader(_('Импорт годового плана обучения'));
        $this->_importService = $this->getService('TcApplicationImportTemplate');
    }

    public function processAction()
    {
        $storage = Zend_Registry::get('session_namespace_default')->tsSessions['import'];
        $importManager = $this->_importManager; // new $this->_importManagerClass();

//        if ($importManager->restoreFromCache()) {
//            $importManager->init(array());
//        } else {
//            $importManager->init($this->_importService->fetchAll());
//        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые записи не найдены'));
            $this->_redirector->gotoUrl(
                Zend_Registry::get('session_namespace_default')->tsSessions['import']['returnUrl'],
                array('prependBase' => false)
            );
        }

        $importManager->import();

        $session = $this->getService('TcSession')->getOne(
            $this->getService('TcSession')->find($storage['sessionId'])
        );

        if ($session) {
            $i = 0;
            $state = $this->getService('Process')->getCurrentState($session);
            while ($state && !is_a($state, 'HM_Tc_Session_State_Agreement') && ($i++ < 10)) { // на всяк.случай
                $this->getService('Process')->goToNextState($session);
                $state = $this->getService('Process')->getCurrentState($session);
            }
        }

        $skipped = $importManager->getSkipped();
        $this->_flashMessenger->addMessage(sprintf(_('Импортировано строк из исходного файла: %d'), $importManager->getSourceCount()));
        $this->_flashMessenger->addMessage(sprintf(_('Успешно импортировано заявок: %d'), $importManager->getInsertsCount()));
        if (($importManager->getNoNameCount() > 0) && ($skipped['noDepartment'] == 0) && ($skipped['tooHighDep'] == 0)) $this->_flashMessenger->addMessage(sprintf(_('Из них обезличенных заявок: %d'), $importManager->getNoNameCount()));
        if (count($skipped['noUser']) > 0) $this->_flashMessenger->addMessage(sprintf(_('Не найдено пользователей: %d'), count($skipped['noUser'])));
        if (count($skipped['noDepartment']) > 0) $this->_flashMessenger->addMessage(sprintf(_('Не найдено подразделений для обезличенных заявок: %d'), count($skipped['noDepartment'])));
        if (count($skipped['tooHighDep']) > 0) $this->_flashMessenger->addMessage(sprintf(_('Найдено подразделений выше третьего уровня, для которых нельзя создавать обезличенные заявки: %d'), count($skipped['tooHighDep'])));
        if (count($skipped['noFio'])  > 0) $this->_flashMessenger->addMessage(sprintf(_('Некорректно заполнено ФИО у %d записей в исходном файле.'), count($skipped['noFio'])));
        $this->_redirector->gotoUrl(
            $storage['returnUrl'],
            array('prependBase' => false)
        );
    }

    protected function _autoCreateSession()
    {
        $values = array(
            'name' => sprintf(_('[Импортированная сессия %s]'), date('Y-m-d H:i:s')),
            'responsible_id' => $this->getService('User')->getCurrentUserId(),
        );

        $begin = new HM_Date();
        $values['date_begin'] = $begin->toString(HM_Date::SQL_DATE);

        $end = clone $begin;
        $end->add(7, Zend_Date::DAY);
        $values['date_end'] = $end->toString(HM_Date::SQL_DATE);

        $year = (int) date('Y') + 1;
        $cycle = $this->getService('Cycle')->fetchOne(
            array(
                'year = ?' => $year,
                'quarter = ?' => 0,
                'type = ?' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING
            )
        );

        $begin = $year.'-01-01';
        $end = $year.'-12-31';

        if (!$cycle) {
            $cycle = $this->getService('Cycle')->insert(
                array(
                    'name' => $year. _(' год'),
                    'begin_date' => $begin,
                    'end_date' => $end,
                    'type' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING,
                    'year' => $year,
                    'quarter' => 0
                )
            );
        }

        $values['cycle_id'] = $cycle->cycle_id;

        return $this->getService('TcSession')->insert($values, false);
    }
}