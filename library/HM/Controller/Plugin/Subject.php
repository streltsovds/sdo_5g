<?php
class HM_Controller_Plugin_Subject extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $error = false;
		$serviceContainer = Zend_Registry::get('serviceContainer');
        $subjectId = $request->getParam('subject_id', 0);
        $subject = $request->getParam('subject', 0);
        /** @var HM_Acl $aclService */
        $aclService = $serviceContainer->getService('Acl');
        $currentUserId = $serviceContainer->getService('User')->getCurrentUserId();

        if ($subjectId && ($subject == 'subject')) {
            if ($aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
                $collection = $serviceContainer->getService('Teacher')->fetchAll(
                    $serviceContainer->getService('Teacher')->quoteInto(
                        array('MID = ?', ' AND CID = ?'),
                        array($currentUserId, $subjectId)
                    )
                );
                if (!count($collection)) {
                    $error = true;
                }
            } elseif ($aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

                $actionName = $this->getRequest()->getActionName();

                // карточку и описание могут видеть и не студенты - при подаче заявки
                if ($actionName !== "card" && $actionName !== 'description') {

                    $collection = $serviceContainer->getService('Student')->fetchAll(
                        $serviceContainer->getService('Student')->quoteInto(
                            array('MID = ?', ' AND CID = ?'),
                            array($currentUserId, $subjectId)
                        )
                    );

                    $collectionGraduated = $serviceContainer->getService('Graduated')->fetchAll(
                        $serviceContainer->getService('Graduated')->quoteInto(
                            array('MID = ?', ' AND CID = ?'),
                            array($currentUserId, $subjectId)
                        )
                    );

                    if (!count($collection)) {
                        $error = true;

                        $collectionGraduated = $serviceContainer->getService('Graduated')->fetchAll(
                            $serviceContainer->getService('Graduated')->quoteInto(
                                array('MID = ?', ' AND CID = ?'),
                                array($currentUserId, $subjectId)
                            )
                        );
                        $subjectModel = $serviceContainer->getService('Subject')->findOne($subjectId);
                        $needConfirmation = $serviceContainer
                            ->getService('SubjectMark')
                            ->isConfirmationNeeded(
                                $subjectId,
                                $currentUserId
                            );
                        $allowGraduated =
                            count($collectionGraduated) and
                            $subjectModel and
                            HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT == $subjectModel->period_restriction_type;

                        if ($allowGraduated or
                            ('happy-end' == $actionName and $needConfirmation)
                        ) {
                            $error = false;
                        }
                    }
                }
            }

            if ($error) {
                $serviceContainer->getService('Log')->log(
                    $currentUserId,
                    'Unauthorized access to subject pages',
                    'Fail',
                    Zend_Log::WARN,
                    get_class($this),
                    $subjectId
                );

                $flashMessengerCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('FlashMessenger');
                $redirectorCls = Zend_Controller_Action_HelperBroker::getPluginLoader()->load('ConditionalRedirector');
                
                $flashMessenger = new $flashMessengerCls();
                $redirector = new $redirectorCls();

                $flashMessenger->addMessage(_('У вас нет права на просмотр этого курса'));
                $redirector->gotoUrl(Zend_Registry::get('baseUrl'));
            }
        }
    }
}
