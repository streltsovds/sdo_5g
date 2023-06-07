<?php
class Like_IndexController extends HM_Controller_Action_Activity
{
    protected $service     = 'Like';
    protected $idParamName = 'like_id';
    protected $idFieldName = 'like_id';
    protected $id          = 0;
    
    public function updateValueColumn($value) {
        if ($value == 1) {
            return '<div class="hm-like-liked hm-like"><div class="hm-like-button-like-image">&nbsp;</div></div>';
        } else {
            return '<div class="hm-like-disliked hm-like"><div class="hm-like-button-dislike-image">&nbsp;</div></div>';
        }
        
    }

    public function voteListAction()
    {
        $item_type = (int) $this->_getParam('item_type', false);
        $item_id   = (int) $this->_getParam('item_id',   false);

        switch ($item_type) {
            // БЛОГ
            case HM_Like_LikeModel::ITEM_TYPE_BLOG:
                $blog = $this->getOne($this->getService('Blog')->find($item_id));
                // если не нашли блог, переадрисовываем на главную страницу
                if (!$blog) {
                    $this->redirectToIndex();
                }
                
                $title = _('Подробная информация по голосованию для записи блога').' "'.$blog->title.'"';
                
                break;
            default:
                $this->redirectToIndex();
        }
        
        $this->view->title = $title;
        $this->view->isAjaxRequest = $this->isAjaxRequest();

        $select = $this->getService('Like')->getSelect();
        $select->from(array('lu' => 'like_user'), array(
            'lu.like_user_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(u.LastName, ' ') , u.FirstName), ' '), u.Patronymic)"),
            'lu.value',
            'lu.date',
        ));
        $select->joinLeft(array('u' => 'People'), 'u.MID = lu.user_id', array());
        $select->where('item_type = ?', $item_type);
        $select->where('item_id = ?',   $item_id);
        
        $grid = $this->getGrid(
            $select,
            array(
                'like_user_id' => array('hidden' => true),
                'fio' => array('title' => _('ФИО')),
                'value' => array(
                    'title' => _('Проголосовал'),
                    'callback' => array(
                        'function'=> array($this, 'updateValueColumn'),
                        'params'=> array('{{value}}')
                    )
                ),
                'date' => array('title' => _('Дата голосования'))
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

        $grid->deploy();
        
        $this->view->grid = $grid;
        
    }
    
    public function redirectToIndex()
    {
        $this->_redirect('/');
    }
    
    public function likeAction()
    {
        /** @var HM_Like_LikeService $likeService */
        $likeService = $this->getService('Like');

        /**
         * возможные значаения:
         * 'LIKE' | 'DISLIKE'
         */
        $like_type = $this->_getParam('like_type', false);

        /**
         * const ITEM_TYPE_NEWS = 3;
         * const ITEM_TYPE_RESOURCES = 4;
         */
        $item_type = (int) $this->_getParam('item_type', false);
        // id лайкаемого объекта
        $item_id   = (int) $this->_getParam('item_id',   false);

        if ($this->getRequest()->isXmlHttpRequest() && (!$like_type || !$item_type || !$item_id)) {
            $data = $this->getJsonParams();

            $like_type = $data['like_type'];
            $item_type = $data['item_type'];
            $item_id   = (int)$data['item_id'];

        }
        try {
            if (!$like_type || !$item_type || !$item_id) {
                throw new HM_Exception(_('Неверно указаны параметры для голосования'));
            }

            $stats = $likeService->like($item_type, $item_id, $like_type);

            $result = array(
                'result' => $stats,
                'message' => 'OK'
            );

        } catch (HM_Exception $e) {

            $result = array(
                'result'  => false,
                'message' => $e->getMessage()
            );

        } catch (Exception $e) {

            $result = array(
                'result'  => false,
                'message' => $e->getMessage()//_('Голосование не удалось. Попробуйте позже.')
            );

        }

        die(json_encode($result));
    }
}

