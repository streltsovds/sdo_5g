<?php
class HM_Poll_Link_LinkService extends HM_Service_Abstract
{
    private $_linksCache = null;

    private $_linkKeys   = array();

    /**
     * Инит кеша моделей
     */
    private function _initCache()
    {
        if ( $this->_linksCache === null ) {
            $pollLinks = $this->fetchAll();
            if ( count($pollLinks) ) {
                foreach ($pollLinks as $link) {
                    $key = $this->_getCacheKey($link->item_id, $link->item_type, $link->quiz_id);
                    $this->_linksCache[$key]         = $link;
                    $this->_linkKeys[$link->link_id] = $key;
                }
            } else {
                $this->_linksCache = array();
            }
        }
    }

    /**
     * @param $link int - ID || HM_Poll_Link_LinkModel - object
     * @return HM_Poll_Link_LinkModel
     */
    public function getLinkObject($link)
    {
        if ($link instanceof HM_Poll_Link_LinkModel) {
            return $link;
        }

        $linkId = (int) $link;
        $this->_initCache();

        if ( isset($this->_linkKeys[$linkId]) && isset($this->_linksCache[$this->_linkKeys[$linkId]])) {
            return $this->_linksCache[$this->_linkKeys[$linkId]];
        }

        return null;
    }

    /**
     * Возвращает форматированный ключ кеш-массива
     * @param $itemId
     * @param $itemType
     * @param $pollId
     * @return string
     */
    private function _getCacheKey($itemId, $itemType, $pollId)
    {
        return sprintf('%s:%s:%s', $itemId, $itemType, $pollId);
    }

    /**
     * Проверка назначения опроса на элемент указанного типа
     * @param $itemId
     * @param $itemType
     * @param $pollId
     * @return bool
     */
    public function isLinkExists($itemId, $itemType, $pollId)
    {
        $this->_initCache();
        return array_key_exists($this->_getCacheKey($itemId, $itemType, $pollId), $this->_linksCache);
    }

    /**
     * Назначение опроса элементу указанного типа
     * @param $itemsIds int|array
     * @param $itemType int
     * @param $polls int|array
     * @return bool
     */
    public function assignPolls($itemsIds, $itemType, $polls)
    {
        $itemsIds = (array) $itemsIds;
        $polls    = (array) $polls;
        $itemType = (int)   $itemType;

        if ( !count($itemsIds) || !count($polls) || !array_key_exists($itemType, HM_Poll_Link_LinkModel::getTypes()) ) {
            return false;
        }

        foreach ( $itemsIds as $itemId ) {
            foreach( $polls as $pollId ) {

                $itemId = (int) $itemId;
                $pollId = (int) $pollId;

                if ( !$itemId || !$pollId || $this->isLinkExists($itemId, $itemType, $pollId) ) continue;

                $this->insert(array(
                    'item_id'   => $itemId,
                    'item_type' => $itemType,
                    'quiz_id'   => $pollId,
                    'item_page' => $this->getItemPageUrl($itemId, $itemType)
                ));
            }
        }
        return true;
    }

    public function getItemPageUrl($itemId, $itemType)
    {
        return HM_Poll_Link_LinkModel::getItemPageUrl($itemId, $itemType);
    }


    /**
     * Возвращает коллекцию объектов ссылок для объекта текущего request запроса
     * @param $pollId - ИД ид опроса, указывается если нужно получить конкретный линк по MCA объекта и ИД опроса
     */
    public function getCurrentLinks($pollId = null)
    {
        $pollId  = (int) $pollId;
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $mca     = sprintf("%s:%s:%s", $request->getModuleName(), $request->getControllerName(), $request->getActionName());
        $type    = HM_Poll_Link_LinkModel::getTypeByMCA($mca);
        $pollLinks = array();
        if ( $type !== false ) {

            $paramName = HM_Poll_Link_LinkModel::getUrlItemIdParamName($type);
            $itemId    = $request->getParam($paramName, 0);

            if ( $itemId ) {
                $whereCond = array('item_id = ?', ' AND item_type = ?');
                $whereVals = array($itemId, $type);

                if ( $pollId ) {
                    $whereCond[] = ' AND quiz_id = ?';
                    $whereVals[] = $pollId;
                }
                $where     = $this->quoteInto($whereCond, $whereVals);
                $pollLinks = $this->fetchAll($where);
            } elseif (false) {
                /**
                 * @todo: Некоторые страницы могут не иметь $itemId в запросе, в данном случае сопоставлять по item_page
                 */
            }
        }

        return $pollLinks;
    }

    /**
     * Возвращает ИД ссылок для объекта текущего request запроса
     * @param null $pollId
     * @return array
     */
    public function getCurrentLinksIds($pollId = null)
    {
        $links = $this->getCurrentLinks($pollId);
        return (count($links))? $links->getList('link_id') : array();
    }

    /**
     * Возвращает массив объектов ссылок для указанного опроса
     * @param $quizId
     * @return array
     */
    public function getLinksByQuizId($quizId)
    {
        $this->_initCache();
        $links = array();

        foreach ($this->_linksCache as $linkItem) {
            if ( $linkItem->quiz_id != $quizId ) continue;
            $links[] = $linkItem;
        }
        return $links;
    }

    /**
     * Функция возвращает заголовок объекта опроса
     * !!! Не кешируется, избегать вызова в цикле
     * @param $linkId
     * @return null|string
     */
    public function getItemTitle($linkId)
    {
        $link = $this->getLinkObject($linkId);

        if ( !$link ) { return; }

        switch($link->item_type) {
            case HM_Poll_Link_LinkModel::TYPE_BLOG_ITEM:
                $item = $this->getService('Blog')->getOne($this->getService('Blog')->find($link->item_id));
                $title = ($item)? $item->title : _('Без заголовка');
                break;
            case HM_Poll_Link_LinkModel::TYPE_NEWS_ITEM:
                $item = $this->getService('News')->getOne($this->getService('News')->find($link->item_id));
                if ($item) {
                    $title = (mb_strlen($item->announce) > 50)? mb_substr($item->announce, 0, 50) . '...' : $item->announce;
                } else {
                    $title = _('Без заголовка');
                }
                break;
            default:
                $title = '';
        }

        return $title;
    }
}