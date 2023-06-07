<?php

class Subject_ContactsController extends HM_Controller_Action_Subject
{
    const ITEMS_PER_PAGE = 40;

    public function indexAction()
    {
        $this->view->replaceSidebar('subject', 'subject-contacts', [
            'model' => $this->_subject,
        ]);

        $userId = $this->getService('User')->getCurrentUserId();

        /** @var HM_Activity_ActivityService $activityService */
        $activityService = $this->getService('Activity');

        $isModerator = $activityService->isUserActivityPotentialModerator($userId)
            // В модератор - тьютор конкретного курса, тут - глобальный тьютор
            || $this->getService('User')->isRoleExists($userId, HM_Role_Abstract_RoleModel::ROLE_TEACHER);

        // Передаём как параметр в компонент VUE, потому что данные в компоненте кэшируются и при смене настроек могут залипнуть
        $this->view->enablePersonalInfo = ($isModerator || !$this->getService('Option')->getOption('disable_personal_info'));
        $this->view->disableMessages = !$isModerator && $this->getService('Option')->getOption('disable_messages');
    }

    // то же самое, только для для navigation
    public function indexManagerAction()
    {
        $this->_forward('index');
    }

    public function searchAction()
    {
        $query = $this->getParam('query');
        $searchParams = new HM_DataType_Contacts_SearchParams();
        $searchParams->query = $query;

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        $usersList = $userService->searchContacts($this->_subjectId, $searchParams);

        $itemsPerPage = $this->_getParam('itemsPerPage', self::ITEMS_PER_PAGE);
        $page = $this->_getParam('page', 0);
        $paginator = Zend_Paginator::factory($usersList);
        $paginator->setCurrentPageNumber((int) $page);
        $paginator->setItemCountPerPage($page === 'all' ? $paginator->getTotalItemCount() : $itemsPerPage);
        $currentItems = $paginator->getCurrentItems();
        $result = $userService->formatContacts($currentItems);
        $result['pageCurrent'] = $paginator->getCurrentPageNumber();
        $result['pageCount'] = $paginator->count();

        return $this->_helper->json($result);
    }
}
