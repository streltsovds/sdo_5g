<?php

class HM_Tag_TagService extends HM_Service_Abstract
{
    protected $_isIndexable = false;

    // не хватает что-то вроде HM_Service_Abstract::fetchAllParent...
    public function getTags($itemId, $itemType)
    {
        $return = array();
        $select = $this->getSelect()
            ->from(array('t' => 'tag'), array('id', 'body'))
            ->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array())
            ->where('tr.item_id = ?', intval($itemId))
            ->where('tr.item_type = ?', intval($itemType));

        if ($tags = $select->query()->fetchAll()) {
            foreach ($tags as $tag) {
            	$return[$tag['id']] = $tag['body'];
            }
        }
        return $return;
    }

    public function getTagsCache($itemIds, $itemType)
    {
        $return = [];

        if (!is_array($itemIds) || !count($itemIds))
            return $return;

        $select = $this->getSelect()
            ->from(['t' => 'tag'], ['id', 'body'])
            ->join(['tr' => 'tag_ref'], 't.id = tr.tag_id', ['item_id'])
            ->where('tr.item_id IN (?)', new Zend_Db_Expr(implode(',', $itemIds)))
            ->where('tr.item_type = ?', intval($itemType));

        if ($tags = $select->query()->fetchAll()) {
            foreach ($tags as $tag) {
            	$return[$tag['item_id']][$tag['id']] = $tag['body'];
            }
        }
        return $return;
    }


    /**
     * Возвращает обект с информацией о тегах заданного типа и их рейтинге
     * @param array|int $itemTypes
     * @return Ambigous <multitype:, stdClass>
     */
    public function getTagsRating( $itemTypes, $subjectId = null, $subjectName = null )
    {
        $min = 1000;
        $max = 0;
        $arResult = array();
        /*
        $selectCourses = $this->getSelect()
            ->from(array('sc' => 'subjects_courses'), array('sc.course_id'))
            ->group(array('sc.course_id'));

        $subjectCourses = $selectCourses->query()->fetchAll();
        */
        $select = $this->getSelect()
            ->from(array('t' => 'tag'), array('id', 'body'))
            ->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array(new Zend_Db_Expr('COUNT(tr.tag_id) as rating')))
            ->group(array('t.id','t.body'));

        if ( is_int($itemTypes) ) { // для одного типа
            $select->where('tr.item_type = ?', $itemTypes);

            if (($itemTypes == HM_Tag_Ref_RefModel::TYPE_BLOG) && isset($subjectId)) {
                $select->join(array('b' => 'blog'), 'b.id = tr.item_id', array())
                    ->where($this->quoteInto('b.subject_id = ?',$subjectId));
                if (isset($subjectName)){
                    $select->where($this->quoteInto('b.subject_name = ?',$subjectName));
                }
            }

        } elseif ( is_array($itemTypes) ) { //для массивов типов
            $select->where('tr.item_type IN (?)', new Zend_Db_Expr(implode(', ', $itemTypes)));

            /**
             * Display only PUBLIc tags
             */
            $subSelect = $this->getSelect()
                    ->from(array('tr2' => 'tag_ref'), array('tr2.item_id'));
            $doSubSelect = false;
            if (in_array(HM_Tag_Ref_RefModel::TYPE_RESOURCE, $itemTypes)) {
                $subSelect
                    ->joinLeft(array('r' => 'resources'), 'tr2.item_id = r.resource_id', null)
                    ->orWhere(sprintf('tr2.item_type = %s AND r.status <> %s', HM_Tag_Ref_RefModel::TYPE_RESOURCE, HM_Resource_ResourceModel::STATUS_PUBLISHED));
                $doSubSelect = true;
        }

            if (in_array(HM_Tag_Ref_RefModel::TYPE_COURSE, $itemTypes)) {
                $subSelect
                    ->joinLeft(array('c' => 'Courses'), 'tr2.item_id = c.CID', null)
                    ->orWhere(sprintf('tr2.item_type = %s AND c.status <> %s', HM_Tag_Ref_RefModel::TYPE_COURSE, HM_Resource_ResourceModel::STATUS_PUBLISHED));
                $doSubSelect = true;
            }

            if ($doSubSelect) $select->where('tr.item_id NOT IN (?)', new Zend_Db_Expr($subSelect));
        }

        $select->order('rating DESC');
        $select->limit(50);
        $tags = $select->query()->fetchAll();

        foreach ($tags as $tag) {
            if ($tag["rating"] > $max) {
                $max = $tag["rating"];
            }
            if ($tag['rating'] < $min) {
                $min = $tag["rating"];
            }
        }
        foreach ($tags as $tag) {

            $p = $max - $min;
            if ($p == 0) {
                $p = 1;
            }
            $percent = round(100 * ($tag['rating'] - $min) / $p);

            $objTag = new stdClass();
            $objTag->id = $tag['id'];
            $objTag->body = $tag['body'];
            $objTag->percent = $percent;
            $objTag->num = round($percent * 0.09);
            $arResult[] = $objTag;
        }

        usort($arResult, array('HM_Tag_TagService', '_sortByBody'));
        return $arResult;
    }

    static public function _sortByBody($tag1, $tag2) {
        return $tag1->body < $tag2->body ? -1 : 1;
    }

    /**
     * Возвращает форматированную строку со списком меток элемента заданного типа
     * @param unknown_type $itemIds
     * @param HM_Tag_Type_Abstract $itemType
     * @param Bool $forGrid - форматировать как html для грида
     * @return string
     */
    public function getStrTagsByIds($itemId, $itemType, $forGrid = false)
    {
        $arResult = $this->getTags($itemId, $itemType);

        if ( !count($arResult) ) return '';

        asort($arResult);
        //форматирование в раскрывающийся список
        if ( $forGrid ) {
           $txt = ( count($arResult) > 1 )? '<p class="total">'. $this->pluralTagCount(count($arResult)) . '</p>' : '';
           foreach ($arResult as $item) {
               $txt .= "<p>$item</p>";
           }
           return $txt;
        }
        return implode(', ', $arResult);
    }

    /**
     * Склонятор
     * @param int $count
     * @return string
     */
    public function pluralTagCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('метка plural', '%s метка', $count), $count);
    }

    public function pluralTagCountVue($count)
    {
        $count = intval($count);
        return '{{ _pl("метка plural", ' . $count . ') }}';
    }

    public function getTagsByIds($itemIds, $itemType = false)
    {
        if ( !is_array($itemIds) ) {
            $itemIds = (array) $itemIds;
        }
        if (!count($itemIds)) return array();
        $return = array();
        $select = $this->getSelect()
            ->from(array('t' => 'tag'), array('id', 'body'))
            ->where('t.id IN (?)', new Zend_Db_Expr(implode(',', $itemIds)));
        if ($itemType) {
            $select->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array())
                ->where('tr.item_type = ?', $itemType);
        }
//        exit($select->__toString());
        return $select->query()->fetchAll();
    }

    /**
     * Возварщает выборку элементов по метке и типам
     * @param string $tags
     * @param array | int $itemTypes
     * @return Ambigous <multitype:, string, boolean, mixed>
     */
    public function getIdsByTags($tags,$itemTypes = NULL)
    {
        $select = $this->getSelect()
                       ->from(array('t' => 'tag'), array('body'))
                       ->join(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array('item_id','item_type'))
                       ->where('t.body LIKE ?', $tags);

        if ( is_numeric($itemTypes)) { // для одного типа
            $select->where('tr.item_type = ?', $itemTypes);
        } elseif ( is_array($itemTypes) ) { //для массивов типов
            $select->where('tr.item_type IN (?)', implode(',', $itemTypes));
        }

        return $select->query()->fetchAll();
    }

    public function getAllTags()
    {
        $tagsDb = $this->fetchAll();
        $tagsDb = $tagsDb->getList('id', 'body');
        return $tagsDb;
    }

    public function deleteTags($itemId, $itemType)
    {
        $this->getService('TagRef')->deleteBy(array(
            'item_id = ?' => $itemId,
            'item_type = ?' => $itemType,
        ));
    }

    /**
     * Функция удаляет все теги с которыми не связан ни один элемент
     */
    public function clearTags()
    {
        $select = $this->getSelect()->from(array('t'=>'tag'),'id')
                                    ->joinLeft(array('tr' => 'tag_ref'), 't.id = tr.tag_id', array())
                                    ->where('tag_id IS NULL');

        $arRes = $select->query()->fetchAll();

        foreach ( $arRes as $tag) {
            $this->delete(intval($tag["id"]));
        }
    }

    public function getTagCondition($tag = null, $tagLike = null)
    {
        $where = array();
        if($tag) {
            $where['body LIKE ?'] = $tag;
        }
        if($tagLike) {
            $where['LOWER(body) LIKE ?'] = '%'.mb_strtolower($tagLike).'%';
        }
        return $where;
    }

    /**
     * @param $tags
     * @param $itemId
     * @param $itemType
     * @param bool $allowNumericIds - false для поддержки числовых тэгов
     */
    public function updateTags($tags, $itemId, $itemType, $allowNumericIds = true)
    {
        $allTags = $this->getAllTags();

        $t = array(); //временный массив для заполнения актуальными id
        foreach ($tags as $id => $tag) {
            if($exist_id = array_search($tag, $allTags)) {
                $t[$exist_id] = $tag;
            } else {
                $data = $this->insert((array( //создаем новые метки
                    'body' => urldecode($tag),
                )));
                $t[$data->id] = $tag;
            }
        }
        $tags = $t; // массив меток из поста с актульными id

        $tagsDb = $this->getTags($itemId, $itemType); // массив меток из БД

        $forDelete = array_keys(array_diff($tagsDb, $tags));

        if (!empty($forDelete)) {
            foreach ($forDelete as $id) {
                $this->getService('TagRef')->deleteBy(array(
                    'tag_id = ?' => $id,
                    'item_id = ?' => $itemId,
                    'item_type = ?' => $itemType
                ));
            }
        }

        $forInsert = array_keys(array_diff($tags, $tagsDb));

        if(!empty($forInsert)) {
            foreach ($forInsert as $id) {
                $this->getService('TagRef')->insert(array(
                    'tag_id' => $id,
                    'item_id' => $itemId,
                    'item_type' => $itemType
                ));
            }
        }

        // убираем за собой мусор
        $this->clearTags();

        return array_keys($tags);
    }

    public function convertAllToStrings($_tags)
    {
        $tags = array();
        if(!empty($_tags)) {
            foreach ($_tags as $id => $body) {
                $tags[] = $body;
            }
        }
        return $tags;
    }

    static public function isNewTag($tag)
    {
        return !preg_match("/^([0-9])+$/",$tag); // если не новый - то здес ь целочисленный id
    }

    public function getItemsByTagIds($ids, $itemType = false)
    {
        return array();
    }

    public function getItemsIdsWithTags($tagType)
    {
        $select = $this->getSelect()
            ->from(
                array('tr' => 'tag_ref'),
                array(
                    'item_id' => 'tr.item_id',
                    'name' => 't.body'
                )
            )
            ->joinLeft(
                array('t' => 'tag'),
                't.id = tr.tag_id',
                array()
            )
            ->where('tr.item_type = ?', $tagType);

        $tags = $select->query()->fetchAll();

        $result = [];
        foreach($tags as $tag) {
            $itemId = $tag['item_id'];
            $result[$itemId][] = $tag['name'];
        }

        return $result;
    }
}
