<?php
class HM_Controller_Plugin_Offline extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $error = false;
		$serviceContainer = Zend_Registry::get('serviceContainer');
		$offlineSubjectId = Zend_Registry::get('config')->offline;
        
		// если в режиме offline и если такой курс есть
        if ($offlineSubjectId && count($collection = $serviceContainer->getService('Subject')->find($offlineSubjectId))) {

        	$subject = $serviceContainer->getService('Subject')->getOne($collection);
        	// отрубаем всё лишнее в интерфейсе
        	Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getController()->setView('DocumentOffline');
        	Zend_Registry::get('serviceContainer')->getService('Unmanaged')->setHeader($subject->name);
        	
        	// если уже авторизован
        	if ($serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
        	
        		// если еще не редиректили в курс
	        	if (!$request->getParam('subject_id', 0)) {
	        		
	                $collection = $serviceContainer->getService('Student')->fetchAll(
	                    $serviceContainer->getService('Student')->quoteInto(
	                        array('MID = ?', ' AND CID = ?'),
	                        array($serviceContainer->getService('User')->getCurrentUserId(), $offlineSubjectId)
	                    )
	                );
		            
		            $flashMessengerCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('FlashMessenger');
		            $redirectorCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('ConditionalRedirector');
		            
		            $flashMessenger = new $flashMessengerCls();
		            $redirector = new $redirectorCls();            
		            
		            if (!count($collection)) {
		                $flashMessenger->addMessage(_('У вас нет права на просмотр этого курса'));
		                $serviceContainer->getService('User')->logout();
		                $redirector->gotoUrl(Zend_Registry::get('baseUrl'));
		            } else {
		            	// принудительно редиректим в курс
		            	$redirector->gotoUrl($serviceContainer->getService('Subject')->getDefaultUri($offlineSubjectId));            	
		            }
	            }
            }
        }
    }
}
