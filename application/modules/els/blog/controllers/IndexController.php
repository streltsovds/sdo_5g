<?php
class Blog_IndexController extends HM_Controller_Action_Activity implements Es_Entity_EventViewer
{
    protected $_subjectName;
    protected $_subjectId;
    protected $_isModerator;
    protected $_allowedActions = array('index', 'view', 'tags', 'comment-edit', 'comment-delete', 'new');

    public function init()
    {
        parent::init();

        $this->view->addSidebar('blog', [
            'model' => $this->getActivitySubject(),
        ]);

    }

    /**
     * true, если пользователь является участником конкурса
     * @var bool
     */
    protected $_isParticipant = false;

    public function preDispatch()
    {
        if($this->getService('User')->getCurrentUserRole() == 'guest') {
            $this->_redirector->gotoSimple('index', 'index', 'index');
        }

        parent::preDispatch();

        $this->_subjectName = $this->_getParam('subject', '');
        if(empty($this->_subjectName)) {
            $this->_subjectName = null;
        }
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);

        if(!in_array($this->_request->getActionName(), $this->_allowedActions)) {
            $this->_checkPermissions();
        }
        $this->view->subjectName = $this->_subjectName;
        $this->view->subjectId = $this->_subjectId;
        $this->view->currentUserId = $this->getService('User')->getCurrentUserId();
        $this->view->isModerator = $this->_isModerator = $this->getService('Blog')->isCurrentUserActivityModerator();
        
        // если блог в контекcте поекта, то проверяем, является ли пользователь его участником
        if ($this->_subjectName === 'project') {
            $this->_isParticipant = $this->getService('Project')->isParticipant($this->_subjectId, $this->getService('User')->getCurrentUserId());
    }

        $this->view->isParticipant = $this->_isParticipant;
    }

    private function _checkPermissions()
    {
        if (!$this->getService('Blog')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
        }
    }

    public function updateRatingColumn($blog_id, $rating)
    {
        $url = $this->view->url(array(
            'module'     => 'like',
            'controller' => 'index',
            'action'     => 'vote-list',
            'item_type'  => HM_Like_LikeModel::ITEM_TYPE_BLOG,
            'item_id'    => $blog_id
        ));
        return '<a href="'.$url.'">'.$rating.'</a>';
    }

    public function indexAction()
    {
        $blogServ = $this->getService('Blog');
        $this->view->blogName = $blogServ->getSubjectTitle($this->_subjectName, $this->_subjectId);

        $filter = array();
        if(isset($this->_request->filter)) {
            $filter[$this->_request->filter] = $this->_request->{$this->_request->filter};
        }
        $config = Zend_Registry::get('config');

        $blogPosts = $blogServ->getPaginator(
            $blogServ->getBlogCondition($this->_subjectId, $this->_subjectName, $filter, true),
            'created DESC', 'Tag', 'TagRefBlog'
        );
        $blogPosts->setItemCountPerPage((int)$config->dimensions->blog_posts_per_page);
        $blogPosts->setCurrentPageNumber($this->_request->getParam('page', 1));
        $blogIds = array();
        foreach($blogPosts as $blogPost) {
            $blogIds[] = $blogPost->id;
            $author = $this->getService('User')->find($blogPost->created_by)->current();
            if ( $author->MID ) {
                $blogPost->author_avatar = $config->url->base.$author->getPhoto();
                $blogPost->author = $author->getName();
            } else {
                $blogPost->author_avatar = $config->url->base.$config->src->default->photo;
                $blogPost->author = _('Пользователь был удален');
            }

            $blogPost->comments_count = $blogServ->fetchAllActivityComments($blogPost->id)->count();

            $regex = '#<object\s*(.*?)\s*classid=[\'"](.*?)[\'"]\s*(.*?)\s*codebase=[\'"](.*?)[\'"](.*)>#i';
            $blogPost->body = addslashes(preg_replace($regex, '<object $1 $3 $5>', stripslashes($blogPost->body)));
        }

        if (!empty($blogIds)) {
            $likes = $this->getService('Like')->fetchAll($this->quoteInto('item_type = 1 AND item_id IN (?)', $blogIds));
            $userLikes = $this->getService('LikeUser')->fetchAll($this->quoteInto('item_type = 1 AND item_id IN (?)', $blogIds));
        } else {
            $likes = array();
        }
        $likesIndex = array();

        foreach ($likes as $like) {
            $likesIndex[$like->item_id] = array(
                'count_like'    => $like->count_like,
                'count_dislike' => $like->count_dislike,
            );
        }

        if (count($userLikes)) {
            foreach ($userLikes as $userLike) {
                $likesIndex[$userLike->item_id]['user_vote'] = $userLike->value;
            }
        }

        $this->view->likes = $likesIndex;

        //$this->_sideBar();
        $this->view->headLink()->appendStylesheet($config->url->base.'css/blog.css');
        $this->view->blogPosts = $blogPosts;
        $this->view->isFullView = false;
    }

    public function indexGridAction()
    {
        $blogServ = $this->getService('Blog');
        $this->view->blogName = $blogServ->getSubjectTitle($this->_subjectName, $this->_subjectId);

        $select = $blogServ->getBlogSelect($this->_subjectId, $this->_subjectName);
        $grid = $this->getGrid(
            $select,
            array(
                'id' => array('hidden' => true),
                'blog_id' => array('hidden' => true),
                'title' => array('title' => _('Название'), 'escape' => false),
                'created' => array('title' => _('Дата')),
                //'author' => array('title' => 'Автор'),
                'created_by' => array('title' => _('Автор')),
                'tags' => array('title' => _('Метки')),
                'rating' => array(
                    'title' => _('Рейтинг'),
                    'callback' => array(
                        'function'=> array($this, 'updateRatingColumn'),
                        'params'=> array('{{blog_id}}', '{{rating}}')
                    )
                )
            ),
            array(
                'blog_id' => null,
                'title' => null,
                'created'   => array('render' => 'Date'),
                //'author' => null,
                'created_by' => null,
                'tags' => null
            ),
            'grid_blog'
        );

        if ($this->_isModerator) {

            $grid->addAction(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'edit'
            ),
                array('blog_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'delete'
            ),
                array('blog_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(
                array('module' => 'blog', 'controller' => 'index', 'action' => 'delete-by', 'subject' => $this->_subjectName, 'subject_id' => $this->_subjectId),
                _('Удалить'),
                _('Вы подтверждаете удаление отмеченных записей?')
            );
        }

        $pollWhere = $this->quoteInto(array('status = ?', ' AND location = ?'), array(HM_Poll_PollModel::STATUS_CONTENTONLY, HM_Poll_PollModel::LOCALE_TYPE_GLOBAL));
        $polls     = $this->getService('Poll')->fetchAll($pollWhere)->getList('quiz_id', 'title');

        $grid->addMassAction(array('action' => 'assign-poll'), _('Добавить опрос'));
        $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign-poll'))),
            'polls[]',
            $polls);

        $pollWhere = $this->quoteInto(array('status = ?', ' AND location = ?'), array(HM_Poll_PollModel::STATUS_CONTENTONLY, HM_Poll_PollModel::LOCALE_TYPE_GLOBAL));
        $polls     = $this->getService('Poll')->fetchAll($pollWhere)->getList('quiz_id', 'title');

        $grid->addMassAction(array('action' => 'assign-poll'), _('Добавить опрос'));
        $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign-poll'))),
            'polls[]',
            $polls);

        $grid->updateColumn('created', array(
            'callback' => array(
                'function'=> array($this, 'getDateForGrid'),
                'params'=> array('{{created}}')
            )
        ));
        $grid->updateColumn('created_by', array(
            'callback' => array(
                'function'=> array($this, 'getAuthorForGrid'),
                'params'=> array('{{created_by}}')
            )
        ));
        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array('{{blog_id}}', HM_Tag_Ref_RefModel::TYPE_BLOG )
            )
        ));

        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('title');
        $filters->addFilter('tags', array(
            'callback' => array(
                'function' => array($this, 'filterTags')
            )
        ));
        $filters->addFilter('created', array('render' => 'SubjectDate'));
        $filters->addFilter('author');
        $grid->addFilters($filters);

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function getAuthorForGrid($mid)
    {
        $author = $this->getService('User')->find($mid)->current();
        return  ( $author->MID )? $author->getName() : _('Пользователь был удален');
    }

    public function getDateForGrid($date)
    {
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
        // return $date->toString(HM_Locale_Format::getDateFormat());
    }

    public function newAction()
    {
        $form = new HM_Form_Blog();

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                /* @var $blogServ HM_Blog_BlogService */
                $blogServ = $this->getService('Blog');
                $blogObj = $blogServ->insert(array(
                    'title' => $form->getValue('title'),
                    'body' => $form->getValue('body'),
                    'subject_name' => $this->_subjectName,
                    'subject_id' => $this->_subjectId,
                    'created_by' => $this->getService('User')->getCurrentUserId(),
                ));

                if ($tags = $form->getParam('tags')) {
                    $this->getService('Tag')->updateTags($tags, $blogObj->id, HM_Tag_Ref_RefModel::TYPE_BLOG);
                }

//[ES!!!] //array('item' => $blogObj))

                $this->_flashMessenger->addMessage(_('Запись опубликована'));
                $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));

            } else {
                $tagServ = $this->getService('Tag');
                $values = $request->getParams();
                if($this->_request->getParam('tags')) {
                    $values['tags'] = $this->_request->getParam('tags');
                    foreach($values['tags'] as $k => $tag) {
                        if(is_numeric($tag)) {
                            $tagObj = $tagServ->getOne($tagServ->find($tag));
                            if($tagObj && $tagObj->id) {
                                $values['tags'][$tagObj->id] = $tagObj->body;
                            }
                        } else {
                            $values['tags'][$tag] = $tag;
                        }
                        unset($values['tags'][$k]);
                    }
                }
                $values['body'] = stripslashes($this->_request->getParam('body'));
                $form->setDefaults($values);
            }
        }

        $this->view->form = $form;
    }

    public function viewAction()
    {
        $blogId = (int) $this->_getParam('blog_id', 0);

        $blogServ = $this->getService('Blog');
        $blogPost = $blogServ->getOne($blogServ->find($blogId));
        if(!$blogPost->id) {
            $this->_redirector->gotoSimple('index', 'index', 'blog', array(
                'subject' => $this->_subjectName,
                'subject_id' => $this->_subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId
            ));
        }

        $like = $this->getService('Like')->fetchRow($this->quoteInto('item_type = 1 AND item_id = ?', $blogId));
        
        if ($like) {
            $like = array(
                'count_like'    => $like->count_like,
                'count_dislike' => $like->count_dislike,
            );
            
            $userLike = $this->getService('LikeUser')->fetchRow($this->quoteInto('item_type = 1 AND item_id = ?', $blogId));

            if ($userLike) {
                $like['user_vote'] = $userLike->value;
            }
        } else {
            $like = array(
                'count_like'    => 0,
                'count_dislike' => 0,
                'user_vote'     => 0
            );
        }

        $this->view->likes = array($blogId => $like);

        $form = new HM_Form_Comment();
        $form->setAction($this->view->url(array(
            'module' => 'blog',
            'controller' => 'index',
            'action' => 'view',
            'subject' => $this->_subjectName,
            'subject_id' => $this->_subjectId,
            'blog_id' => $blogId
        ), null, true));
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $comment = new HM_Comment_CommentModel();
                $comment->user_id = $this->getService('User')->getCurrentUserId();
                $comment->item_id = $blogId;
                $comment->message = preg_replace('/\r\n|\n|\r/', '<br>', $form->getValue('message'));
                $comment = $blogServ->insertActivityComment($comment);

                $this->_flashMessenger->addMessage(_('Комментарий успешно добавлен'));
                $this->_redirector->gotoSimple('view', 'index', 'blog', array(
                    'subject' => $this->_subjectName,
                    'subject_id' => $this->_subjectId,
                    'blog_id' => $blogId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId
                ));

                // $this->_redirector->gotoUrl($this->view->url(array(
                // 'module' => 'blog',
                // 'controller' => 'index',
                // 'action' => 'view',
                // 'subject' => $this->_subjectName,
                // 'subject_id' => $this->_subjectId,
                // 'blog_id' => $blogId
                // )).'#comment_'.$comment->id
                // );
            }
        }
        $this->view->form = $form;
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($config->url->base.'css/blog.css');
        $author = $this->getService('User')->find($blogPost->created_by)->current();
        $blogPost->author_avatar = $config->url->base.$author->getPhoto();
        $blogPost->author = $author->getName();
        $blogPost->comments = $blogServ->fetchAllActivityComments($blogPost->id);

        $this->_sideBar();
        $this->view->blogName = $this->getService('Blog')->getSubjectTitle($this->_subjectName, $this->_subjectId);
        $this->view->isFullView = true;
        $this->view->blogPost = $blogPost;
    }

    public function commentEditAction() 
    {
        $text = $this->_getParam('text', '');
        $comment_id = $this->_getParam('comment_id', 0);
        
        $comment = $this->getOne($this->getService('Comment')->find($comment_id));
        
        if (!$comment) {
            die(_('Комментарий не найден'));
        }
        
        if (!$this->_isModerator && $comment->user_id != $this->getService('User')->getCurrentUserId()) {
            die(_('У вас нет права редактировать этот комментарий'));
            return;
        }
        
        $this->getService('Blog')->updateActivityComment($comment->id, $text);
        die('OK');
    }



    public function commentDeleteAction()
    {
        $comment_id = $this->_getParam('comment_id', 0);

        $comment = $this->getOne($this->getService('Comment')->find($comment_id));

        if (!$comment) {
            die(_('Комментарий не найден'));
        }

        if (!$this->_isModerator && $comment->user_id != $this->getService('User')->getCurrentUserId()) {
            die(_('У вас нет права редактировать этот комментарий'));
            return;
        }

        $this->getService('Comment')->delete($comment->id);
        die('OK');
    }

    public function editAction()
    {
        $blogId = (int) $this->_getParam('blog_id', 0);
        $blogServ = $this->getService('Blog');

        $form = new HM_Form_Blog();
        $form->setAction($this->view->url(array('module' => 'blog', 'controller' => 'index', 'action' => 'edit')));

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $this->getService('Blog')->update(array(
                    'title' => $form->getValue('title'),
                    'body' => $form->getValue('body'),
                    'id' => $blogId
                ));

                $this->getService('Tag')->updateTags( $form->getParam('tags',array()), $blogId, HM_Tag_Ref_RefModel::TYPE_BLOG );



                $this->_flashMessenger->addMessage(_('Запись успешно изменена'));
                $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
            } else {
                $values = $this->_request->getParams();
                if($this->_request->getParam('tags')) {
                    $values['tags'] = $this->_request->getParam('tags');
                    foreach($values['tags'] as $k => $tag) {
                        $values['tags'][$tag] = $tag;
                        unset($values['tags'][$k]);
                    }
                }
                $blog = $blogServ->getOne($blogServ->findManyToMany('Tag', 'TagRef', $blogId));
                if ($blog && $blog->tag) {
                    foreach($blog->tag as $tag) {
                        if ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_BLOG) continue;
                        if(isset($values['tags'][$tag->body])) {
                            unset($values['tags'][$tag->body]);
                        }
                        $values['tags'][$tag->id] = $tag->body;
                    }
                }
                $values['body'] = stripslashes($this->_request->getParam('body'));
                $form->setDefaults($values);
            }
        } else {
            if ($blogId) {
                $blog = $blogServ->getOne($blogServ->findManyToMany('Tag', 'TagRef', $blogId));
                $values = array();
                if ($blog) {
                    $values = $blog->getValues();
                    $values['body'] = stripslashes($values['body']);
                    $values['tags'] = array();
                    if($blog->tag) {
                        foreach($blog->tag as $tag) {
                            if ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_BLOG) continue;
                            $values['tags'][$tag->id] = $tag->body;
                        }
                    }
                }

                $form->setDefaults($values);
            }
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $id = $this->_getParam('blog_id', 0);
        if ($id) {
            $this->getService('Blog')->delete($id);
        }

        $this->_flashMessenger->addMessage(_('Запись успешно удалена'));
        $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
    }


    public function deleteByAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_blog'));
        foreach ($ids as $value) {
            $value = (int)$value;
            if($value) {
                $this->getService('Blog')->delete($value);
            }
        }
        $this->_flashMessenger->addMessage(_('Записи успешно удалены'));
        $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId, self::PARAM_CONTEXT_TYPE => $this->_activitySubjectName, self::PARAM_CONTEXT_ID => $this->_activitySubjectId));
    }

    private function _saveTags($blogId)
    {
        $tagServ = $this->getService('Tag');

        $blogServ = $this->getService('Blog');
        $tags = $this->getRequest()->getParam('tags');

        $blog = $blogServ->findManyToMany('Tag', 'TagRefBlog', $blogId)->current();
        foreach($blog->tags as $tg) {
            if(!in_array($tg->id, $tags)) {
                $blogServ->removeTag($blogId, $tg);
            }
        }

        foreach($tags as $tag) {
            $tagObj = null;
            if(is_numeric($tag)) {
                $tagObj = $tagServ->getOne($tagServ->find($tag));
            } else {
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $tag = iconv("UTF-8", Zend_Registry::get('config')->charset, $tag);
                }
                $tagObj = $tagServ->getOne($tagServ->fetchAll(
                    $tagServ->getTagCondition($this->_subjectId, $this->_subjectName, $tag)
                ));
            }
            if(!isset($tagObj->id)) {
                $tagObj = $tagServ->insert(array(
                    'body' => $tag,
                    'subject_name' => $this->_subjectName,
                    'subject_id' => $this->_subjectId,
                ));
            }
            if(!$blogServ->hasTag($blogId, $tagObj->id)) {
                $blogServ->addTag($blogId, $tagObj->id, $tagObj->rating);
            }
        }
    }

    private function _sideBar()
    {
        $blogServ = $this->getService('Blog');

        /*$blogs = $blogServ->fetchAll($blogServ->getBlogSelect($this->_subjectId, $this->_subjectName));
        $tags = $this->getService('Tag')->getTags(HM_Tag_TagModel::ITEM_TYPE_RESOURCE);
        $min = 1000;
        $max = 0;
        foreach ($tags as $tag) {
            if ($tag->rating > $max) {
                $max = $tag->rating;
            }
            if ($tag->rating < $min) {
                $min = $tag->rating;
            }
        }
        foreach ($tags as $tag) {
            $p = $max - $min;
            if ($p == 0) {
                $p = 1;
            }
            $percent = round(100 * ($tag->rating - $min) / $p);
            $tag->percent = $percent;
            $tag->num = round($percent * 0.09);
        }*/

        $tags = $this->getService('Tag')->getTagsRating(HM_Tag_Ref_RefModel::TYPE_BLOG, $this->_subjectId, $this->_subjectName);
        $this->view->cloudTags = $tags;
        $this->view->archiveDates = $this->getService('Blog')->getArchiveDates($this->_subjectId, $this->_subjectName);
        $this->view->authors = $this->getService('Blog')->getAuthors($this->_subjectId, $this->_subjectName);
    }

    public function assignPollAction()
    {
        $polls = $this->_getParam('polls', 0);
        $blogs = explode(',', $this->_getParam('postMassIds_grid_blog', ''));

        $this->getService('PollLink')->assignPolls($blogs, HM_Poll_Link_LinkModel::TYPE_BLOG_ITEM, $polls);

        $this->_flashMessenger->addMessage(_('Опросы успешно назначены'));
        $this->_redirector->gotoUrl($this->view->url(array('action' => 'index')));
    }
}