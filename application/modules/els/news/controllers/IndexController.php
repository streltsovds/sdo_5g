<?php
class News_IndexController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $_subjectId;
    protected $_subject;

    public function init()
    {
        $this->_subjectId = $subjectId = $this->_getParam('subid', $this->_getParam('subject_id', 0));
        $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));

        if ($this->_subject) {

            $this->initContext($this->_subject);
            $this->setActiveContextMenu('mca:subject:index:description');

            $this->view->addSidebar('subject', [
                'model' => $this->_subject,
            ]);

            $this->view->addSidebar('subject-updates', [
                'model' => $this->_subject,
                'order' => 100, // после Subject
            ]);

            $this->view->setBackUrl($this->view->url([
                'module' => 'subject',
                'controller' => 'list',
                'action' => 'index',
                'base' => $this->_subject->base,
            ], null, true));

            $switcherData = $this->getService('Subject')->getContextSwitcherData($this->_subject);
            $this->view->setSwitchContextUrls($switcherData);
        }

        parent::init();
    }

    public function indexAction()
    {
        $this->view->getUrl = $this->view->url(array(
           'module' => 'news',
           'controller' => 'index',
           'action' => 'get'
        ));

        /*$subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $filter = array();
        if(isset($this->_request->filter)) {
            $filter[$this->_request->filter] = $this->_request->{$this->_request->filter};
        }
        $paginator = $this->getService('News')->getPaginator(
            $this->getService('News')->getNewsCondition($subjectId, $subjectName, $filter, true),
            'created DESC'
        );
        $paginator->setItemCountPerPage((int) Zend_Registry::get('config')->dimensions->news_per_page);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $this->view->news = $paginator;
        $this->view->isFullView = false;

        $this->view->subjectName = $subjectName;
        $this->view->subjectId = $subjectId;*/
    }

    /**
     * Copy from danone/develop2
     */
    public function getAction()
    {
        /** @var HM_News_NewsService $newsService */
        $newsService = $this->getService('News');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        $currentUserId = $userService->getCurrentUserId();
        $from = date('Y-m-d', strtotime("-1 year"));
        $to = date('Y-m-d', strtotime("+1 day"));
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $select = $newsService->getSelect();
        $select->distinct()->from(['n' => 'news'], [
            'n.id',
            'n.date',
            'n.author',
            'n.created',
            'n.created_by',
            'announce' => new Zend_Db_Expr('CAST(n.announce AS VARCHAR(MAX))'),
            'message' => new Zend_Db_Expr('CAST(n.message AS VARCHAR(MAX))'),
            'n.subject_name',
            'n.subject_id',
            'n.url',
            'n.name',
            'n.visible',
            'n.date_end',
            'n.icon_url',
            // 'like_value' => 'lu.value',
            // 'like_date' => 'lu.date',
            'like_count' => 'l.count_like',
            // 'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(c.name)'),
            'classifiers' => 'c.name',
        ]);
        $select->joinLeft(array('l' => 'likes'), "l.item_id = n.id AND l.item_type = " . HM_Like_LikeModel::ITEM_TYPE_NEWS, array());
        $select->joinLeft(array('lu' => 'like_user'), "lu.item_id = n.id AND l.item_type = " . HM_Like_LikeModel::ITEM_TYPE_NEWS . " AND lu.user_id = $currentUserId", array());
        $select->joinLeft(array('cl' => 'classifiers_links'), "cl.item_id = n.id AND cl.type = " . HM_Classifier_Link_LinkModel::TYPE_CLASSIFIER_NEWS, array());
        $select->joinLeft(array('c' => 'classifiers'), "c.classifier_id = cl.classifier_id", array());
        $select->where("created BETWEEN '$from' AND '$to'");
        $select->group([
            'n.id',
            'n.date',
            'n.author',
            'n.created',
            'n.created_by',
            new Zend_Db_Expr('CAST(n.announce AS VARCHAR(MAX))'),
//            'n.announce',
            new Zend_Db_Expr('CAST(n.message AS VARCHAR(MAX))'),
//            'n.message',
            'n.subject_name',
            'n.subject_id',
            'n.url',
            'n.name',
            'n.visible',
            'n.date_end',
            'n.icon_url',
            // 'like_value' => 'lu.value',
            // 'like_date' => 'lu.date',
            'like_count' => 'l.count_like',
	        'c.name'
        ]);

        if ($subjectName) {
            $select->where('subject_name = ?', $subjectName);
        } else {
            $select->where("subject_name IS NULL OR subject_name = ''");
        }

        if($subjectId) {
            $select->where('subject_id = ?', $subjectId);
        } else {
            $select->where('subject_id = 0 or subject_id is null');
        }

        //$select->where('n.id in (?)', $newsService->getAllowedNewsIds());
        $result['news'] = $select->query()->fetchAll();

        foreach ($result['news'] as &$news) {
            // убираем тэги из имени
            $news['name'] = strip_tags($news['name']);
            $news['classifiers'] = array_filter(explode(',', $news['classifiers']));

            if(!$news['icon_url']) {
                $news['icon_url'] = '';
            }
        }

        $result['our'] = []; // $htmlpageService->fetchAll(array("our = 1"))->asArray()
        $result['like_url'] = $this->view->url([
            'module' => 'like',
            'controller' => 'index',
            'action' => 'like',
        ]);

        $this->sendAsJsonViaAjax($result);
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
        );

        if($subjectId) {
            $select->where('subject_id = ?', $subjectId);
        } else {
            $select->where('subject_id = 0 or subject_id is null');
        }

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
        $this->view->setSubHeader(_('Создание новости'));
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {

            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex($subjectName, $subjectId);
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
                                
                $news = array(
                    'message' => $form->getValue('message'),
                    'announce' => $form->getValue('announce'),
                    'mobile' => $form->getValue('mobile2'),
                    'subject_name' => $form->getValue('subject_name'),
                    'subject_id' => $form->getValue('subject_id'),
                    'author' => $authorName,
                    'created' => date('Y-m-d H:i:s'),
                    'created_by' => $this->getService('User')->getCurrentUserId(),
                    'visible' => $form->getValue('visible'),
                    'icon_url' => $form->getValue('icon_url'),
                    'name' => $form->getValue('name'),
                    'url' => $form->getValue('url')
                );

                if($form->getValue('date_end')) {
                    $news['date_end'] = $this->_convertDate($form->getValue('date_end'));
                }

                $news = $this->getService('News')->insert($news);
               if(empty($news)) {
                   $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Новость с таким содержанием уже существует')));
                   $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));
               }

                $form->saveIcon($news->id);

                $updateArr = array(
                    'id' => $news->id,
                    'icon_url' => $form->getValue('icon_url')
                );

                $this->getService('News')->update( $updateArr );
                $this->getService('News')->linkClassifiers($news->id, $form->getClassifierValues());


                $this->getService('News')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

                $this->_flashMessenger->addMessage(_('Новость опубликована'));
                $this->_redirectToIndex($subjectName, $subjectId);
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
        $this->view->setSubHeader(_('Редактирование новости'));
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $newsId = (int) $this->_getParam('news_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }

        $form = new HM_Form_News();
        $form->setAction($this->view->url(array(
            'module' => 'news',
            'controller' => 'index',
            'action' => 'edit',
            'news_id' => $this->_request->getParam('news_id'),
        )));

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $form->saveIcon();
                $news = array(
                    'message' => $form->getValue('message'),
                    'announce' => $form->getValue('announce'),
                    'mobile' => $form->getValue('mobile2'),
                    'id' => $form->getValue('id'),
                    'visible' => $form->getValue('visible'),
                    'icon_url' => $form->getValue('icon_url'),
                    'name' => $form->getValue('name'),
                    'url' => $form->getValue('url'),
                );

                if($form->getValue('date_end')) {
                    $news['date_end'] = $this->_convertDate($form->getValue('date_end'));
                }

                $newsModel = $this->getService('News')->update($news);
                $this->getService('News')->linkClassifiers($newsId, $form->getClassifierValues());

                $this->getService('News')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

                $this->_flashMessenger->addMessage(_('Новость успешно изменена'));
                $this->_redirectToIndex($subjectName, $subjectId);

            }
        } else {
            if ($newsId) {
                $news = $this->getOne($this->getService('News')->find($newsId));
                $values = array();
                if ($news) {
                    $values = $news->getValues();
                }
                if ($values['date_end']) {
                    $values['date_end'] = date('d.m.Y', strtotime($values['date_end']));
                }
                $values['mobile2'] = $values['mobile'];
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
            $this->_redirectToIndex($subjectName, $subjectId);
        }
        
        $id = $this->_getParam('news_id', 0);
        if ($id) {
            $this->getService('News')->delete($id);
            $this->getService('News')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);
        }


        $this->_flashMessenger->addMessage(_('Новость успешно удалена'));
        $this->_redirectToIndex($subjectName, $subjectId);
    }


    public function deleteByAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex($subjectName, $subjectId);
        }
        
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        foreach ($ids as $value) {
            $this->getService('News')->delete($value);
        }
        $this->_flashMessenger->addMessage(_('Новости успешно удалены'));
        $this->_redirectToIndex($subjectName, $subjectId);
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

    public function _convertDate($date){
        $date = explode('.', $date);
        return "{$date[2]}-{$date[1]}-{$date[0]}";
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
	            'grid'
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

    protected function _redirectToIndex($subjectName, $subjectId)
    {
        $action = $this->getService('News')->isCurrentUserActivityModerator() ? 'grid' : 'index';
        $this->_redirector->gotoSimple($action, 'index', 'news', [
            'subject' => $subjectName,
            'subject_id' => $subjectId
        ]);
    }
}