<?php
/**
 * Для внешних кандидатов на вакансии не показываем в интерфейсе ничего лишнего
 *
 */
class HM_Controller_Plugin_Candidate extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        return;
        $vacancyId = false;
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        
        $flashMessengerCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('FlashMessenger');
        $flashMessenger = new $flashMessengerCls();
        $redirectorCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('ConditionalRedirector');
        $redirector = new $redirectorCls();
        
        if ($userId = $serviceContainer->getService('User')->getCurrentUserId()) {       
            if (count($collection = $serviceContainer->getService('User')->fetchAllHybrid(array('Position', 'Candidate'), 'Vacancy', 'VacancyCandidate', array('MID = ?' => $userId)))) {
                $user = $collection->current();
                if (count($user->candidate)) {
                    $candidate = $user->candidate->current();
                    if ($candidate->source != HM_Recruit_Provider_ProviderModel::ID_PERSONAL) {
                        if (count($user->vacancies)) {
                            $vacancyStatuses = $user->vacancies->getList('vacancy_id', 'status'); 
                            if ($vacancyId = array_search(HM_Recruit_Vacancy_VacancyModel::STATE_ACTUAL, $vacancyStatuses)) {
                                if ($this->_needRedirect($page)) {
                                    // редиректим на первую попавшуюся активную вакансию
                                    // при назначении на вакансию работает проверка чтоб не было несколько одновременно вакансий у кандидата
                                    
                                    $vacancies = $user->vacancies->asArrayOfObjects();
                                    
                                    $baseUrl = (APPLICATION_MODULE == 'AT') ? '' : 'at';
                                    $redirector->gotoUrl(Zend_Registry::get('view')->url(array(
                                        'module' => 'session', 
                                        'controller' => 'event', 
                                        'action' => 'my', 
                                        'baseUrl' => $baseUrl, 
                                        'session_id' => $vacancies[$vacancyId]->session_id,
                                        'session_event_id' => null,
                                    )));                                
                                }
                            }
                        }
                        if ($vacancyId) {
                            $serviceContainer->getService('Unmanaged')->getController()->setView('DocumentRestricted');
                        } else {
                            // ничего не делаем; между сессиями подбора все внешние кандидаты пребывают в заблокированном состоянии и авторизоваться не могут  
                        }
                    }
                }
            }
        }
    }
    
    protected function _needRedirect($page)
    {
        return in_array($page, array(
            'default-index-index',
            'session-list-my',
        ));
    }
}
