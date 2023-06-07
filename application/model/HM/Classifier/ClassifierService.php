<?php
class HM_Classifier_ClassifierService extends HM_Service_Nested
{

    public function linkItem($itemId, $itemType, $classifierId)
    {
        return $this->getService('ClassifierLink')->insert(
            array(
                'item_id' => $itemId,
                'type' => $itemType,
                'classifier_id' => $classifierId
            )
        );
    }

    public function unlinkItem($itemId, $itemType)
    {
        return $this->getService('ClassifierLink')->deleteBy(
            $this->quoteInto(
                array('item_id = ?', ' AND type = ?'),
                array($itemId, $itemType)
            )
        );
    }

    public function linkExists($itemId, $itemType, $classifierId)
    {
        $collection = $this->getService('ClassifierLink')->fetchAll(
            $this->getService('ClassifierLink')->quoteInto(
                array('item_id = ?', ' AND type = ?', ' AND classifier_id = ?'),
                array($itemId, $itemType, $classifierId)
            )
        );

        return count($collection);

    }

    public function getTreeContent($itemType = null, $parent = 0, $type = null, $notEncode = false, $classifierId = 0)
    {
        $res = array();
        if (null !== $type) {
            $categories = $this->getChildren($parent, true, 'node.type = '.(int) $type);
        } else {
            $categories = $this->getChildren($parent);
        }
        $classifiersCount = 0;
        if (null !== $itemType) {
            $classifiersCount = $this->getElementCount($itemType, $categories);
        }
        $userId = $this->getService('User')->getCurrentUserId();
        if (count($categories)) {
        	$categories = $categories->asArrayOfObjects('name'); // sort by name
            foreach($categories as $category) {
                $categoryClassifierId = $category->classifier_id;
            	//$subcategories = $this->getChildren($category->classifier_id);
            	$isFolder = (($category->rgt - $category->lft) > 1) ? true : false;
                $item = array(
                    'title' => (($parent > 0 && $notEncode === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $category->name) : $category->name),
                	'count' => (int) isset($classifiersCount[$categoryClassifierId]) ? $classifiersCount[$categoryClassifierId] : 0,
                    'key' => $categoryClassifierId,
                    'keyType' => $isFolder ? HM_Classifier_ClassifierModel::FILTER_TYPE : HM_Classifier_ClassifierModel::FILTER_CLASSIFIER,
                    'isLazy' => ($isFolder ? true  : false),
                    'isFolder' => $isFolder
                );

                if ($classifierId && ($classifierId == $categoryClassifierId)) {
                    $item['activate'] = true;
                }

                $res[] = $item;

            }
        }

        if($parent === 0){
            if (count($res)) {
                $result = array();
                foreach($res as $r) {
                    $r['expand'] = true;
                    $result[] = $r;
                    $temp = $this->getTreeContent($itemType, $r['key'], $type, true, $classifierId);
                    $result[] = $temp;
                }
                $res = $result;
            }
        }
        return $res;
    }


    /**
     * deprecated! use $this->getService('ClassifierType')->getClassifierTypes($link_type)->getList('type_id', 'name')
     * @param  $link_type
     * @return array
     */
    public function getTypes($link_type)
    {
        $types = array();
        $res = $this->getService('ClassifierType')->getClassifierTypes($link_type);
        foreach($res as $value){
            $types[$value->type_id] = $value->name;
        }

        return $types;
    }

    private function _getSubjectsInCategories($type, $categories)
    {
        $lft = null;
        $rgt = null;

        foreach($categories as $value){
            if($lft == null || $lft > $value->lft){
                $lft = $value->lft;
            }
            if($rgt == null || $rgt < $value->rgt){
                $rgt = $value->rgt;
            }
        }

        if($type == HM_Classifier_Link_LinkModel::TYPE_SUBJECT){
            // some shitcode here
            $select = $this->getSelect();
            $select->from(array('cls' => 'classifiers_links'), array())
                   ->joinInner(array('s' => 'subjects'), 's.subid = cls.item_id',array('subid', 'last_updated'))
                   ->where($this->quoteInto(
                        array(
                            's.period IN (?) OR ',
                            's.period_restriction_type = ? OR ',
                            '(s.period = ? AND ',
                            's.end > ?)',
                        ),
                        array(
                            array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED),
                            HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::PERIOD_DATES,
                            $this->getService('Subject')->getDateTime()
                        )
                    ))
                   ->where('s.reg_type IN (?)', array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN))
                   ->joinInner(array('c' => 'classifiers'), 'cls.classifier_id = c.classifier_id',array('c.lft', 'c.rgt'))
                   ->where('cls.type = ?', HM_Classifier_Link_LinkModel::TYPE_SUBJECT);

            $query = $select->query();
            $res = $query->fetchAll();

        } elseif ($type == HM_Classifier_Link_LinkModel::TYPE_RESOURCE) {
            $select = $this->getSelect();
            $select->from(array('cls' => 'classifiers_links'), array())
                ->joinInner(array('r' => 'resources'), 'r.resource_id = cls.item_id', array())
                ->joinInner(array('c' => 'classifiers'), 'cls.classifier_id = c.classifier_id', array('c.lft', 'c.rgt'))
                ->where('cls.type = ?', HM_Classifier_Link_LinkModel::TYPE_RESOURCE);

            $query = $select->query();

            $res = $query->fetchAll();
        } elseif ($type == HM_Classifier_Link_LinkModel::TYPE_USER) {

            /*
             * Сделать запрос для классификации юзеров
             */


        }

        if(count($res) == 0){
            return array();
        }
        return $res;
    }

    public function getElementCount($type, $categories)
    {
    	$resArray = array();
    	$subjects = $this->_getSubjectsInCategories($type, $categories);

        foreach($subjects as $val){
            foreach($categories as $val2){
                if($val['lft'] >= $val2->lft && $val['rgt'] <= $val2->rgt){

                    if (!isset($resArray[$val2->classifier_id])) $resArray[$val2->classifier_id] = 0;
                    $resArray[$val2->classifier_id]++;
                }
            }
        }
        return $resArray;
    }

    public function deleteNode($id, $recursive = false)
    {
        $classifier = $this->getOne($this->find($id));
        if ($classifier) {
            $classifiers = $this->fetchAll(
                $this->quoteInto(
                    array('lft >= ?', ' AND rgt <= ?'),
                    array($classifier->lft, $classifier->rgt)
                )
            );
        }
        return parent::deleteNode($id, $recursive);
    }
    public function getCategoriesFreshness($categories)
    {
        $resArray = $resCount = $resTotal = array();
        $subjects = $this->_getSubjectsInCategories(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $categories);

        foreach($subjects as $val){
            foreach($categories as $val2){
                if($val['lft'] >= $val2->lft && $val['rgt'] <= $val2->rgt){

                    if (!isset($resTotal[$val2->classifier_id])) $resTotal[$val2->classifier_id] = 0;
                    if (!isset($resCount[$val2->classifier_id])) $resCount[$val2->classifier_id] = 0;

                	$resTotal[$val2->classifier_id] += (($val['last_updated']) ? HM_Subject_SubjectService::calcFreshness(strtotime($val['last_updated'])) : 0);
                	$resCount[$val2->classifier_id]++;
                }
            }
        }
        foreach ($resTotal as $classifierId => $total) {
        	$resArray[$classifierId] = floor($total/$resCount[$classifierId]);
        }
        return $resArray;
    }

    public function getUnclassifiedElementCount($type)
    {
    	$userId = $this->getService('User')->getCurrentUserId();

        $select = $this->getSelect()->distinct()
        	->from(array('s' => 'subjects'), array('subid'))
               ->joinLeft(array('cls' => 'classifiers_links'), "s.subid = cls.item_id AND cls.type = {$type}", array())
               ->where('cls.classifier_id IS NULL AND (s.status in(0, 1))')
               // скопировано из /subject/catalog чтобы совпадало кол-во курсов
//               ->where('s.reg_type = ?', HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN)
//               ->where($this->quoteInto(
//                    array(
//                        's.period IN (?) OR ',
//                        's.period_restriction_type = ? OR ',
//                        '(s.period_restriction_type = ?',' AND (s.state = ? ',' OR s.state = ? OR s.state is null) ) OR ',
//                        '(s.period = ? AND ',
//                        's.end > ?)',
//                    ),
//                    array(
//                        array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED),
//                        HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
//                        HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
//                        HM_Subject_SubjectModel::STATE_ACTUAL,
//                        HM_Subject_SubjectModel::STATE_PENDING,
//                        HM_Subject_SubjectModel::PERIOD_DATES,
//                        $this->getService('Subject')->getDateTime()
//                    )
//                ))
        ;

//        if ($userId > 0) {
//            $select->joinLeft(array('st' => 'Students'), 's.subid = st.CID AND st.MID = ' . $userId, array())
//                   ->where('st.CID IS NULL');
//            $select->joinLeft(array('cl' => 'claimants'), 's.subid = cl.CID AND cl.MID = ' . $userId, array())
//                   ->where('cl.CID IS NULL');
//        }
        $s = $select->__toString();

        $query = $select->query();
        $res = $query->fetchAll();

        return count($res);
    }

    public function deleteByType($type)
    {

        $classifiers = $this->getService('Classifier')->fetchAll(
            $this->quoteInto(
                array(
                    'type = ?', ' AND level = ?'
                ), array(
                    $type, 0
                )
            )
        );

        if (count($classifiers)) {
            foreach($classifiers as $classifier) {
                $this->getService('Classifier')->deleteNode($classifier->classifier_id, true);
            }
        }
    }

    public function delete($id)
    {
        $this->getService('ClassifierLink')->deleteBy(
            $this->quoteInto('classifier_id = ?', $id)
        );

        return parent::delete($id);
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('рубрика во множественном числе', '%s рубрика', $count), $count);
    }

    public function pluralFormCountTypes($count)
    {
        return !$count ? _('Нет') : sprintf(_n('область применения во множественном числе', '%s область применения', $count), $count);
    }
    
    // @todo: добавить атрибут классификатора "plain",
    // чтобы этим методом ожно было всегда пользоваться для такого классификатора
    public function getTopLevel($type)
    {
        $return = array();
        $collection = $this->fetchAll("level = 0 AND type=" . intval($type));
        if (count($collection)) {
            foreach($collection as $rubric) {
                $return[$rubric->classifier_id] = $rubric->name;
            }
        }
        return $return;
    }    
    
    /**
     * Возвращает классификаторы элемента(ов)
     * @param int|array $itemIds - элементы
     * @param $itemType - тип элемента
     * @param int|array $classifierTypes - можно отфильтровать по типу классификатора
     * @return HM_Collection
     */
    public function getItemClassifiers($itemIds, $itemType, $classifierTypes = null) {

        $itemIds = (array) $itemIds;
        $itemIds = array_unique($itemIds);

        $select = $this->getSelect();
        $select->from(
            array('cl' => 'classifiers_links'),
            array('cl.classifier_id')
        );

        //элементы
        $select->where('cl.item_id IN (?)', $itemIds);
        //тип элементов
        $select->where('cl.type = ?', $itemType);

        //id классификторов, которые привязаны к элементам
        $classifierIds = $select->query()->fetchAll(PDO::FETCH_COLUMN, 0);

        if (!count($classifierIds)) {
            return new HM_Collection();
        }

        $where = array(
            'classifier_id IN (?)' => array_unique($classifierIds),
        );

        //типы классификторов
        if (!is_null($classifierTypes)) {
            if (!is_array($classifierTypes)) {
                $classifierTypes = array($classifierTypes);
            }

            $where['type IN (?)'] = $classifierTypes;
        }

        return $this->fetchAll($where);
    }

    /**
     * Проверяет наличие классификатора у элементов,
     * возвращает массив айдишников, для которых задан искомый классификатор
     *
     * @param $itemIds
     * @param int $itemType - тип элемента
     * @param int|array $classifierIds - один или несколько классификаторов,
     * если указано несколько, то будут возвращены только те элементы,
     * к которым привязаны все из этих классификаторов
     *
     * @return array
     */
    public function itemsWithClassifiers($itemIds, $itemType, $classifierIds) {
        $select = $this->getSelect();

        $select->from(
            array('cl' => 'classifiers_links'),
            array('cl.item_id')
        );

        //элементы
        $select->where('cl.item_id IN (?)', $itemIds);
        //тип элементов
        $select->where('cl.type IN (?)', $itemType);

        //классификаторы
        if (!is_array($classifierIds)) {
            $classifierIds = array($classifierIds);
        }
        $classifierIds = array_unique($classifierIds);

        foreach ($classifierIds as $classifierId) {
            $select->where('cl.classifier_id = ?', $classifierId);
        }

        $itemIdsWithClassifier = $select->query()->fetchAll(PDO::FETCH_COLUMN, 0);

        return $itemIdsWithClassifier;
    }

    /**
     * Возвращает массив элементов, с совпадающими классификаторами,
     * если совпадений не найдено, то массив будет пустым
     * array[] = item_id
     *
     * @param array|int $itemIds - элементы, для которых ищем пересечения
     * @param int $itemType
     * @param array|int $withItemIds - элементы, с которыми проверяем пересечения
     * @param int $withItemType
     * @param null|int $classifierTypes - можно отфильтровать по типу классификатора
     * @return array
     */
    public function getClassifierIntersections($itemIds, $itemType, $withItemIds, $withItemType, $classifierTypes = null) {

        $intersections = $this->getClassifierIntersectionsCount(
            $itemIds, $itemType, $withItemIds, $withItemType, $classifierTypes
        );

        $result = array();
        foreach ($intersections as $intersection) {
            if ($intersection['count'] != 0) {
                $result[] = (int)$intersection['item_id'];
            }
        }

        return array_unique($result);
    }

    /**
     * Подсчитывает количество совпадающих классификаторов
     * для двух наборов элементов с разными типами.
     * Возвращает многомерный массив:
     * array[] = array['item_id', 'classifier_id', 'count']
     *
     * @param array|int $itemIds - элементы, для которых ищем пересечения
     * @param int $itemType
     * @param array|int $withItemIds - элементы, с которыми проверяем пересечения
     * @param int $withItemType
     * @param null|int $classifierTypes - можно отфильтровать по типу классификатора
     * @return array
     */
    public function getClassifierIntersectionsCount($itemIds, $itemType, $withItemIds, $withItemType, $classifierTypes = null) {

        if (!is_array($itemIds)) {
            $itemIds = array($itemIds);
        }
        if (!is_array($withItemIds)) {
            $withItemIds = array($withItemIds);
        }

        if (!count($itemIds) || !count($withItemIds)) {
            return array();
        }

        $select = $this->getSelect();

        $select->from(
            array('cl1' => 'classifiers_links'),
            array(
                'cl1.item_id',
                'c.classifier_id',
                'count' => new Zend_Db_Expr('COUNT(c.classifier_id)')
            )
        );

        $select->joinLeft(
            array('c' => 'classifiers'),
            'c.classifier_id = cl1.classifier_id',
            array()
        );

        $select->joinLeft(
            array('cl2' => 'classifiers_links'),
            'cl2.classifier_id = cl1.classifier_id',
            array()
        );

        //элементы, для которых ищем пересечения
        $select->where('cl1.item_id IN (?)', $itemIds);
        $select->where('cl1.type = ?', $itemType);

        //элементы, с которыми проверяем пересечения
        $select->where('cl2.item_id IN (?)', $withItemIds);
        $select->where('cl2.type = ?', $withItemType);

        //типы классификторов
        if (!is_null($classifierTypes)) {
            if (!is_array($classifierTypes)) {
                $classifierTypes = array($classifierTypes);
            }

            $select->where('c.type IN (?)', $classifierTypes);
        }

        $select->group(array('cl1.item_id', 'c.classifier_id'));

        $result = $select->query()->fetchAll();

        return $result;
    }

    /**
     * Возвращает максимальное количество совпадающих классификаторов
     * для каждого элемента, с которым ищем пересечения
     * array['item_id'] = count
     *
     * @param array|int $itemIds - элементы, для которых ищем пересечения
     * @param int $itemType
     * @param array|int $withItemIds - элементы, с которыми проверяем пересечения
     * @param int $withItemType
     * @param null|int $classifierTypes - можно отфильтровать по типу классификатора
     * @return array
     */
    public function getClassifierIntersectionsCountMax($itemIds, $itemType, $withItemIds, $withItemType, $classifierTypes = null) {

        $intersections = $this->getClassifierIntersectionsCount(
            $itemIds, $itemType, $withItemIds, $withItemType, $classifierTypes
        );

        if (!is_array($itemIds)) {
            $itemIds = array($itemIds);
        }

        $intersectionsMaxCountByItemId = array_fill_keys($itemIds, 0);

        foreach ($intersections as $intersection) {
            $itemId = $intersection['item_id'];
            $count = $intersection['count'];

            if ($intersectionsMaxCountByItemId[$itemId] < $count) {
                $intersectionsMaxCountByItemId[$itemId] = (int)$count;
            }
        }

        return $intersectionsMaxCountByItemId;
    }
    // проверяет, не изменился ли классификатор (считает просто число элементов)
    // если изменился - полностью удаляем и создаём заново
    // @todo: сейчас применимо тольк к двухуровневым
    public function updateContent($firstLevel, $secondLevel, $type)
    {
        $existingClassifiers = $this->fetchAll(array('type = ?' => $type));
        if (count($existingClassifiers) != array_sum(array_map("count", $secondLevel))) {
            $this->deleteBy(array('type = ?' => $type));
            foreach ($firstLevel as $externalId => $name) {
                if (empty($name)) continue;
                $node = $this->insert(array(
                    'classifier_id_external' => $externalId,
                    'type' => $type,
                    'name' => $name,
                ));
                if (isset($secondLevel[$externalId])) {
                    foreach ($secondLevel[$externalId] as $externalId => $name) {
                        if (empty($name)) continue;
                        $this->insert(array(
                            'classifier_id_external' => $externalId,
                            'type' => $type,
                            'name' => $name,
                        ), $node->classifier_id);
                    }                
                }                
            }
        }
    }

    public function getKnowledgeBaseClassifiers($classifierLink = HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $notEmpty = false)
    {
        $types = $this->getTypes($classifierLink);
        $classifiers = [];

        foreach ($types as $tk => $tv) {

            $items = $this->getChildren(0, true, 'node.type = '.(int) $tk);

            if ($notEmpty && !count($items)) continue;

            $classifiers[$tk] = [
                'title' => $tv,
                'items' => $items
            ];
        }

        return $classifiers;
    }

    public function getKnowledgeBaseClassifiersWithResourcesCount()
    {
        $result = [];
        $types = $this->getTypes(HM_Classifier_Link_LinkModel::TYPE_RESOURCE);
        if(!count($types)) return $result;

        $classifiersSelect = $this->getSelect()
            ->from(['cll' => 'classifiers_links'], [
                'classifier_type' => new Zend_Db_Expr('cl.type'),
                'classifier_name' => new Zend_Db_Expr('cl.name'),
                'classifier_id' => new Zend_Db_Expr('cll.classifier_id'),
                'count' => new Zend_Db_Expr('COUNT(cll.classifier_id)')
            ])
            ->joinInner(['cl' => 'classifiers'], 'cl.classifier_id=cll.classifier_id', [])
            ->joinInner(['r' => 'resources'], 'r.resource_id=cll.item_id', [])
            ->where('cll.type = ?', HM_Classifier_Link_LinkModel::TYPE_RESOURCE)
            ->where('cl.type in(?)', array_keys($types))
            ->group(['cll.classifier_id', 'cl.type', 'cl.name'])
        ;
        $classifiers = $classifiersSelect->query()->fetchAll();

        foreach ($types as $tk => $tv) {
            $classifiersWithCurrentType = [];
            foreach ($classifiers as $classifierData) {
                if($tk == $classifierData['classifier_type']) {
                    $classifiersWithCurrentType[] = $classifierData;
                }
            }

            $result[$tk] = [
                'title' => $tv,
                'items' => $classifiersWithCurrentType,
            ];
        }

        return $result;
    }

    public function getSubjectsClassifiers()
    {
        $types = $this->getTypes(HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
        $classifiers = [];

        foreach ($types as $typeKey => $typeValue) {
            $classifiers[$typeKey] = [
                'title' => $typeValue,
                'items' => $this->getChildren(0, true, 'node.type = '.(int) $typeKey)
            ];
        }

        return $classifiers;
    }

    public function getColor($number)
    {
        $colorMap = [
            '#DAD3FD',
            '#05C985',
            '#D4E3FB',
            '#FAF3D8',
            '#FAF3D8',
            '#CC83E9',
            '#D4E3FB',
            '#FDE1D9',
            '#EDF4FC',
            '#FFE9B9',
            '#FDE1D9',
            '#DAC5E2',
        ];

        return $colorMap[$number % count($colorMap)];
    }

    public function getItemsIdsWithClassifiersIds($classifierType)
    {
        $select = $this->getService('ClassifierLink')
            ->getSelect()
            ->from(
                array('cll' => 'classifiers_links'),
                array(
                    'classifier_id' => 'cll.classifier_id',
                    'item_id' => 'cll.item_id',
                )
            )
            ->joinLeft(
                array('cl' => 'classifiers'),
                'cl.classifier_id = cll.classifier_id',
                array()
            )
            ->where('cll.type = ?', $classifierType);

        $classifiers = $select->query()->fetchAll();

        $result = [];
        foreach($classifiers as $classifier) {
            $itemId = $classifier['item_id'];
            $result[$itemId][] = $classifier['classifier_id'];
        }

        return $result;
    }

    public function classifierToFrontendData($classifier, $currentId = null)
    {
        $isFirstLevel = $this->_classifier->level == 0;
        return array_filter([
            'active' => $currentId == $classifier->classifier_id,
            'expand' => !$isFirstLevel,
            'isFolder' => true,
            'isLazy' => !$isFirstLevel,
            'key' => (string) $classifier->classifier_id,
            'keyType' => 'classifier',
            'title' => $classifier->name,
        ]);
    }

}
