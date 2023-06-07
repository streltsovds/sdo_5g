<?php
class News_IndexController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction()
    {
        $this->view->setSubSubHeader(_('Новости'));

        $filter = array();
        if(isset($this->_request->filter)) {
            $filter[$this->_request->filter] = $this->_request->{$this->_request->filter};
        }
        $paginator = $this->getService('News')->getPaginator(
            $this->getService('News')->getNewsCondition($this->_subjectId, 'subject', $filter, true),
            'created DESC'
        );
        $paginator->setItemCountPerPage((int) Zend_Registry::get('config')->dimensions->news_per_page);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $this->view->news = $paginator;
        $this->view->isFullView = false;

        $this->view->subjectName = 'subject';
        $this->view->subjectId = $this->_subjectId;
    }

    public function gridAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->_hasParam('ordergrid_news')) $this->_setParam('ordergrid_news', 'created_DESC');
        $select = $this->getService('News')->getSelect();
        $select->from(
            'news',
            array('id', 'news_id' => 'id', 'created', 'author', 'announce')
        )
        ->where('subject_id = ?', $subjectId);

        if ($subjectName) {
            $select->where('subject_name = ?', $subjectName);
        } else {
            $select->where("subject_name IS NULL OR subject_name = ''");
        }

        $grid = $this->getNewsGrid($select, $subjectName, $subjectId);

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

        $this->view->subjectName = $subjectName;
        $this->view->subjectId = $subjectId;
    }

    public function newAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!($this->getService('News')->isCurrentUserActivityModerator() ||
            $this->currentUserRole(array(
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL
            )))) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
        }


        $form = new HM_Form_News();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $authorName = sprintf(_('Пользователь #%d'), $this->getService('User')->getCurrentUserId());
                $author = $this->getOne($this->getService('User')->find($this->getService('User')->getCurrentUserId()));
                if ($author) {
                    $authorName = $author->getName();
                }
                                
                $this->getService('News')->insert(array(
                    'message' => $form->getValue('message'),
                    'announce' => $form->getValue('announce'),
                    'url' => $form->getValue('url'),
                    'subject_name' => $form->getValue('subject_name'),
                    'subject_id' => $form->getValue('subject_id'),
                    'author' => $authorName,
                    'created_by' => $this->getService('User')->getCurrentUserId(),
                ));

                $this->getService('News')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

                $this->_flashMessenger->addMessage(_('Новость опубликована'));
                $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));

            }
        } else {
            $form->setDefaults(
                array(
                    'subject_name' => $subjectName,
                    'subject_id' => $subjectId
                )
            );
        }

        $this->view->form = $form;
        
    }

    public function editAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $news_id = (int) $this->_getParam('news_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
        }

        $form = new HM_Form_News();
        $form->setAction($this->view->url(array('module' => 'news', 'controller' => 'index', 'action' => 'edit', self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId)));

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $this->getService('News')->update(array(
                    'message' => $form->getValue('message'),
                    'announce' => $form->getValue('announce'),
                    'url' => $form->getValue('url'),
                    'id' => $form->getValue('id')
                ));

                $this->getService('News')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

                $this->_flashMessenger->addMessage(_('Новость успешно изменена'));
                $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));

            }
        } else {
            if ($news_id) {
                $news = $this->getOne($this->getService('News')->find($news_id));
                $values = array();
                if ($news) {
                    $values = $news->getValues();
                }
                $form->setDefaults($values);
            }
        }

        $this->view->form = $form;

    }

    public function deleteAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
        }
        
        $id = $this->_getParam('news_id', 0);
        if ($id) {
            $this->getService('News')->delete($id);
            $this->getService('News')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);
        }


        $this->_flashMessenger->addMessage(_('Новость успешно удалена'));
        $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
    }


    public function deleteByAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
        }
        
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_news'));
        foreach ($ids as $value) {
            $this->getService('News')->delete($value);
        }
        $this->_flashMessenger->addMessage(_('Новости успешно удалены'));
        $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
    }

    public function viewAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $newsId = (int) $this->_getParam('news_id', 0); // точка отсчета
        $step = (int) $this->_getParam('step', 0); // куда листнули от точки отсчета
        $isModerator = $this->getService('News')->isCurrentUserActivityModerator();

        /** @var HM_News_NewsService $newsService */
        $newsService = $this->getService('News');

        $news = $newsService->getNews($newsId, $subjectName, $subjectId, $step);
        $isNextExists = $newsService->isNewsExists(
            $news->id,
            $news->subject_name,
            $news->subject_id,
            $newsService::EXIST_NEXT);
        $isPreviousExists = $newsService->isNewsExists(
            $news->id,
            $news->subject_name,
            $news->subject_id,
            $newsService::EXIST_PREVIOUS);

 		$view = $this->view;

        $view->isModerator = $isModerator;
        $view->news = $news;
        $view->isNextExists = $isNextExists;
        $view->isPreviousExists = $isPreviousExists;
    }
    
    protected function getNewsGrid($select, $subjectName, $subjectId){
    	
	        $grid = $this->getGrid(
	            $select,
	            array(
	                'id' => array('hidden' => true),
	                'news_id' => array('hidden' => true),
	                'created' => array('title' => _('Дата')),
	                'author' => array('title' => _('Автор')),
	                'announce' => array('title' => _('Анонс новости'), 'escape' => false)
	            ),
	            array(
	                'news_id' => null,
	                'created'   => array('render' => 'Date'),
	                'author' => null,
	                'announce' => null
	            ),
	            'grid_news'
	        );
	
            $grid->addAction(array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'edit'
            ),
                array('news_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'delete'
            ),
                array('news_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );
            
            $grid->addMassAction(
                array('module' => 'news', 'controller' => 'index', 'action' => 'delete-by', 'subject' => $subjectName, 'subject_id' => $subjectId),            _('Удалить'),
                _('Вы подтверждаете удаление отмеченных новостей?')
            );

	
	        $grid->updateColumn('created', array(
	            'format' => array(
	                'date',
	                array('date_format' => HM_Locale_Format::getDateFormat())
	            )
	        ));
	        
	        return $grid;
    	
    }

    public function preDispatch()
    {
        $activitySubjectName = $this->_getParam('subject', '');
        $activitySubjectId = $this->_getParam('subject_id', 0);
        if ($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_GUEST
            && !$activitySubjectName
            && !$activitySubjectId) {
            return true;
        }

        parent::preDispatch();
    }
    
}