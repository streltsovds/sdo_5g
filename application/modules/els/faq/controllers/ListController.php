<?php

class Faq_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;


    const ACTION_PUBLISH = 'publish';
    const ACTION_UNPUBLISH = 'unpublish';

    public function init()
    {
        $this->view->setHeader(_('Часто задаваемые вопросы'));
        $this->view->setBackUrl('/');

        $this->_setForm(new HM_Form_Faq());
        parent::init();
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Вопрос успешно создан'),
            self::ACTION_UPDATE => _('Вопрос успешно обновлён'),
            self::ACTION_DELETE => _('Вопрос успешно удалён'),
            self::ACTION_DELETE_BY => _('Вопросы успешно удалены'),
            self::ACTION_PUBLISH => _('Вопросы успешно опубликованы'),
            self::ACTION_UNPUBLISH => _('Вопросы успешно сняты с публикации')
        );
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("order{$this->gridId}");
        if (($sorting == "")) $this->_request->setParam("order{$this->gridId}", $sorting);

        $defaultSession = new Zend_Session_Namespace('default');
        $defaultSession->viewType = $viewType = $this->_request->getParam('viewType', 'default');

        $select = $this->getService('Faq')->getSelect();
        $currentRole = $this->getService('User')->getCurrentUserRole();

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        if ($acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))
            $currentRole = HM_Role_Abstract_RoleModel::ROLE_ENDUSER;

        $select->from('faq', array('faq_id', 'question', 'answer', 'roles', 'published'));

        if ($acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_ADMIN])) {
            $isModerator = true;
        } else {
            $select->where('roles LIKE ?', '%' . $currentRole . '%');
            $select->where('published = ?', '1');
            $isModerator = false;
            $defaultSession->viewType = 'default';
        }
        if ($sorting) {
            list($field, $direction) = explode('_', $sorting);
            $select->order('faq.' . $field . ' ' . $direction);
        }

        if ($isModerator && $viewType == 'table') {
            $grid = $this->getFaqGrid($select);
            $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
            $this->view->grid = $grid;
        } else {
            $filter = array();
            if (isset($this->_request->filter)) {
                $filter[$this->_request->filter] = $this->_request->{$this->_request->filter};
            }
            $where = count($select->getPart('where')) ? join(" ", $select->getPart('where')) : 'faq_id > 0';
            $paginator = $this->getService('Faq')->getPaginator(
                $where,
                'faq_id DESC');
            $countPerPage = Zend_Registry::get('config')->dimensions->faq_per_page ?
                Zend_Registry::get('config')->dimensions->faq_per_page :
                Zend_Registry::get('config')->dimensions->news_per_page;
            $paginator->setItemCountPerPage((int)$countPerPage);
            $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
            $this->view->faq = $paginator;

            $items = $paginator->getCurrentItems()->asArrayOfArrays();
            foreach ($items as $key => $item) {
                $items[$key]['question'] = strip_tags($items[$key]['question']);
                $items[$key]['answer'] = strip_tags($items[$key]['answer']);
            }

            $this->view->items = $items;
            $this->view->currentPage = $paginator->getCurrentPageNumber();
            $this->view->pageCount = $paginator->count();
        }
        $this->view->viewType = $defaultSession->viewType;
        $this->view->isModerator = $isModerator;
    }

    protected function getFaqGrid($select)
    {
        $grid = $this->getGrid(
            $select,
            array(
                'faq_id' => array('hidden' => true),
                'question' => array('title' => _('Вопрос'), 'callback' => array('function' => array($this, 'updateAnswer'), 'params' => array('{{question}}'))),
                'answer' => array('title' => _('Ответ'), 'callback' => array('function' => array($this, 'updateAnswer'), 'params' => array('{{answer}}'))),
                'roles' => (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN)) ? array('title' => _('Роли')) : array('hidden' => true)),
                'published' => (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN)) ? array('title' => _('Опубликован')) : array('hidden' => true))
            ),
            array(
                'question' => null,
                'roles' => null,
                'published' => array('values' => HM_Faq_FaqModel::getStatuses())
            )
        );

        $grid->updateColumn('roles', array('callback' => array('function' => array($this, 'updateRoles'), 'params' => array('{{roles}}'))));
        $grid->updateColumn('published', array('callback' => array('function' => array($this, 'updatePublished'), 'params' => array('{{published}}'))));



       $grid->addAction(
            array('module' => 'faq', 'controller' => 'list', 'action' => 'edit'),
            array('faq_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(
            array('module' => 'faq', 'controller' => 'list', 'action' => 'delete'),
            array('faq_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array('module' => 'faq', 'controller' => 'list', 'action' => 'publish'),
            _('Опубликовать'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array('module' => 'faq', 'controller' => 'list', 'action' => 'unpublish'),
            _('Отменить публикацию'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array('module' => 'faq', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        return $grid;
    }

    public function updateAnswer($answer)
    {
        $answer = strip_tags($answer);
        if (strlen($answer) > Zend_Registry::get('config')->grid->cell->max_length) {
            return substr($answer, 0, Zend_Registry::get('config')->grid->cell->max_length) . '...';
        }
        return $answer;
    }

    public function updatePublished($published)
    {
        $statuses = HM_Faq_FaqModel::getStatuses();
        return $statuses[$published];
    }

    public function updateRoles($roles)
    {
        $all = HM_Role_Abstract_RoleModel::getBasicRoles();
        $ret = array();
        if (strlen($roles)) {
            foreach (explode('#', $roles) as $roleId) {
                $ret[$roleId] = $all[$roleId];
            }
        }
        return join('<br />', $ret);
    }

    public function create(Zend_Form $form)
    {
        $this->getService('Faq')->insert(
            array(
                'question' => $form->getValue('question'),
                'answer' => $form->getValue('answer'),
                'roles' => join('#', $form->getValue('roles')),
                'published' => $form->getValue('published')
            )
        );

        $this->getService('Faq')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);
    }

    public function update(Zend_Form $form)
    {
        $roles = $form->getValue('roles');
        $return = $this->getService('Faq')->update(
            array(
                'faq_id' => $form->getValue('faq_id'),
                'question' => $form->getValue('question'),
                'answer' => $form->getValue('answer'),
                'roles' => join('#', $roles),
                'published' => $form->getValue('published')
            )
        );

        $this->getService('Faq')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

        return $return;
    }

    public function delete($id)
    {
        $return = $this->getService('Faq')->delete($id);

        $this->getService('Faq')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

        return $return;
    }

    public function setDefaults(Zend_Form $form)
    {
        $faqId = (int)$this->_getParam('faq_id', 0);
        $faq = $this->getOne($this->getService('Faq')->find($faqId));
        if ($faq) {
            $values = $faq->getValues();
            if (strlen($faq->roles)) {
                $values['roles'] = explode('#', $faq->roles);
            }

            $form->setDefaults($values);
        }
    }

    public function publishAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $this->getService('Faq')->publish($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_PUBLISH));
            }
        }
        $this->_redirectToIndex();
    }

    public function unpublishAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $this->getService('Faq')->unpublish($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UNPUBLISH));
            }
        }
        $this->_redirectToIndex();
    }

}