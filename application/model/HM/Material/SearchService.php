<?php

/**
 * Class HM_Material_SearchService
 * SQL and Sphinx search of kbase materials by query, classifiers, tags, etc..
 *
 */
class HM_Material_SearchService
{
    public const ORDER_LAST = 'last';
    public const ORDER_RATING = 'rating';
    public const ORDER_TITLE = 'title';

    /**
     * Only Sphinx search by any attributes, that were added cmd/...Index.php
     * @param array|null $params
     * @return array
     */
    public function extendedSearch(?array $params): array
    {
        $sphinx = HM_Search_Sphinx::factory();
        $sphinx->SetLimits(0,1000,1000);
        $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);

        $filterParams = self::getFilterParams();
        $fullTextParams = self::getFullTextParams();
        $rangeParams = self::getRangeParams();
        $sphinxSubQueries = [];
        $sphinxFilters = [];

        foreach ($params as $paramName => $param) {
            $param = trim($param);
            if(!strlen($param)) continue;

            $isRangeParam = in_array($paramName, $rangeParams);
            $isFullTextParam = in_array($paramName, $fullTextParams);
            $isFilterParam = in_array($paramName, $filterParams) && (-1 != $param);

            if ($isFullTextParam) {
                $paramQuery = '*' . implode('* | *', explode(' ', $param)) . '*';
                $sphinxSubQueries[] = sprintf('@%s %s', $paramName, $paramQuery);
            } elseif ($isRangeParam) {
                $paramNameParts = explode('_', $paramName);
                $paramBaseName = $paramNameParts[0];
                $paramSubName = $paramNameParts[1];

                $sphinxFilters[$paramBaseName][$paramSubName] = $param;
            } elseif($isFilterParam) {
                $sphinxFilters[$paramName] = $param;
            }
        }

        $sphinx = HM_Search_Sphinx::factory();
        $sphinx->SetLimits(0,1000,1000);
        $sphinx->SetMatchMode( SPH_MATCH_EXTENDED2 );
        foreach ($sphinxFilters as $filterName => $filterVal) {
            if (!is_array($filterVal)) {
                $sphinx->SetFilter($filterName, array($filterVal));
            } else {
                $sphinx->SetFilterRange($filterName, array_shift($filterVal), array_shift($filterVal));
            }
        }

        $sphinxQuery = implode(' ', $sphinxSubQueries);
        $kbaseAndSphinxTypesMap = HM_Kbase_KbaseModel::getKbaseAndSphinxTypesMap();
        $sphinxAndKbaseTypesMap = array_flip($kbaseAndSphinxTypesMap);
        $sphinxResult = $sphinx->Query($sphinxQuery, 'resources,courses,polls,tests,tasks');
        $matches = [];

        foreach($sphinxResult['matches'] as $sphinxItem) {
            $itemSphinxType = (int) $sphinxItem['attrs']['sphinx_type'];
            $matchItem = [
                'id' => $sphinxItem['attrs']['nid'],
                'type' => $sphinxAndKbaseTypesMap[$itemSphinxType],
            ];

            $matches[] = $matchItem;
        }

        return $matches;
    }

    /**
     * Search by query, classifiers or tags in the DB/Sphinx
     * HM_DataType_Kbase_SearchResult::matches contains array with id and kbase type
     *
     *
     * @param HM_DataType_Kbase_SearchParams $params
     * @return HM_DataType_Kbase_SearchResult
     */
    public function search(HM_DataType_Kbase_SearchParams $params): HM_DataType_Kbase_SearchResult
    {
        $sphinx = HM_Search_Sphinx::factory();
        $result = $this->sqlSearch($params);

        if (false !== $sphinx->Status() and $params->query != '') {
            $result = $this->sphinxSearch($params, $result);
        }

        return $result;
    }

    public function getModels($sourceItems)
    {
        $collectionIds = [];

        foreach ($sourceItems as $sourceItem) {
            $itemType = $sourceItem['type'];
            $collectionIds[$itemType][] = (int) $sourceItem['id'];
        }

        $models = [];

        foreach ($collectionIds as $type => $ids) {
            switch ($type) {
                case HM_Kbase_KbaseModel::TYPE_RESOURCE:
                    $models = array_merge($models, $this->getResourceModels($ids));
                    break;
                case HM_Kbase_KbaseModel::TYPE_COURSE:
                    $models = array_merge($models, $this->getCourseModels($ids));
                    break;
                case HM_Kbase_KbaseModel::TYPE_TASK:
                    $models = array_merge($models, $this->getTaskModels($ids));
                    break;
                case HM_Kbase_KbaseModel::TYPE_TEST:
                    $models = array_merge($models, $this->getQuestModels($ids, HM_Kbase_KbaseModel::TYPE_TEST));
                    break;
                case HM_Kbase_KbaseModel::TYPE_POLL:
                    $models = array_merge($models, $this->getQuestModels($ids, HM_Kbase_KbaseModel::TYPE_POLL));
                    break;
            }
        }

        /** @var HM_Classifier_ClassifierService $classifierService */
        $classifierService = $this->getService('Classifier');

        foreach ($models as $model) {
            $model->tag = array_filter(explode(',', $model->tag));
            $classifiersItems = array_filter(explode(',', $model->classifiers));
            $resultClassifiers = [];
            foreach ($classifiersItems as $classifiersItem) {
                $classifiersItemSplit = array_filter(explode('#', $classifiersItem));
                if(!count($classifiersItemSplit)) continue;
                $classifiersItemId = $classifiersItemSplit[0];
                $classifiersItemName = $classifiersItemSplit[1];
                $classifierType = $classifiersItemSplit[2];

                $resultClassifiers[$classifiersItemId] = [
                    'color' => $classifierService->getColor($classifierType),
                    'name' => $classifiersItemName,
                ];
            }
            $model->classifiers = $resultClassifiers;

            if ($model->kbase_type == HM_Kbase_KbaseModel::TYPE_RESOURCE and
                $model->type == HM_Resource_ResourceModel::TYPE_EXTERNAL and
                $model->filetype == HM_Files_FilesModel::FILETYPE_IMAGE
            ) {
                $model->imageUrl = Zend_Registry::get('view')->url([
                    'module' => 'file',
                    'controller' => 'get',
                    'action' => 'resource',
                    'resource_id' => $model->resource_id,
                ]);
            }
        }

        return $models;
    }

    /**
     * Второй параметр - опциональный объект уже найденных объектов для объединения с результатами поиска в БД
     *
     * @param HM_DataType_Kbase_SearchParams $params
     * @param HM_DataType_Kbase_SearchResult|null $previousResult
     * @return HM_DataType_Kbase_SearchResult
     * @throws HM_Exception
     * @throws Zend_Db_Select_Exception
     */
    private function sqlSearch(HM_DataType_Kbase_SearchParams $params, HM_DataType_Kbase_SearchResult $previousResult = null): HM_DataType_Kbase_SearchResult
    {
        $unionSelects = [];

        if ($params->hasType(HM_Kbase_KbaseModel::TYPE_RESOURCE)) {
            $unionSelects[]  = $this->getResourceSearchSelect($params);
        }
        if ($params->hasType(HM_Kbase_KbaseModel::TYPE_COURSE)) {
            $unionSelects[]  = $this->getCourseSearchSelect($params);
        }
        if ($params->hasType(HM_Kbase_KbaseModel::TYPE_TASK)) {
            $unionSelects[]  = $this->getTaskSearchSelect($params);
        }
        if ($params->hasType(HM_Kbase_KbaseModel::TYPE_TEST)) {
            $unionSelects[]  = $this->getQuestSearchSelect($params, HM_Kbase_KbaseModel::TYPE_TEST);
        }
        if ($params->hasType(HM_Kbase_KbaseModel::TYPE_POLL)) {
            $unionSelects[]  = $this->getQuestSearchSelect($params, HM_Kbase_KbaseModel::TYPE_POLL);
        }

        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');
        $select = $resourceService
            ->getSelect()
            ->union($unionSelects);

        switch ($params->order) {
            case self::ORDER_LAST:
                $order = ['updated DESC'];
                break;
            case self::ORDER_RATING:
                $order = ['rating DESC'];
                break;
            case self::ORDER_TITLE:
                $order = ['title ASC'];
                break;
            default:
                $order = null;
                break;
        }

        if ($order) {
            $select->order($order);
        }

        $matches = $previousResult ? $previousResult->matches : [];
        $sqlMatches = empty($unionSelects) ? $unionSelects : $select->query()->fetchAll();

        foreach ($sqlMatches as $sqlMatch) {
            $matchKey = sprintf('%s_%s', $sqlMatch['type'], $sqlMatch['id']);
            if(!$matches[$matchKey])
                $matches[$matchKey] = $sqlMatch;
        }

        $result = new HM_DataType_Kbase_SearchResult();
        $result->matches = $matches;

        return $result;
    }

    /**
     * Второй параметр - опциональный объект уже найденных объектов для объединения с результатами поиска сфинкса
     *
     * @param HM_DataType_Kbase_SearchParams $params
     * @param HM_DataType_Kbase_SearchResult|null $previousResult
     * @return HM_DataType_Kbase_SearchResult
     */
    private function sphinxSearch(HM_DataType_Kbase_SearchParams $params, HM_DataType_Kbase_SearchResult $previousResult = null): HM_DataType_Kbase_SearchResult
    {
        $typeFilter = [];
        $kbaseAndSphinxTypesMap = HM_Kbase_KbaseModel::getKbaseAndSphinxTypesMap();
        $sphinxAndKbaseTypesMap = array_flip($kbaseAndSphinxTypesMap);

        foreach ($kbaseAndSphinxTypesMap as $kbaseType => $sphinxType) {
            if ($params->hasType($kbaseType) and
                !empty($params->types) and
                count($params->types)
            ) {
                $typeFilter[] = $sphinxType;
            }
        }

        $sphinx = HM_Search_Sphinx::factory();
        $sphinx->SetLimits(0,1000,1000);
        $sphinx->SetMatchMode( SPH_MATCH_EXTENDED2 );
        $sphinx->SetFilter('classifiers', $params->classifiers);
        $sphinx->SetFilter('sphinx_type', $typeFilter);
        $sphinx->SetFilter('subject_id', 0);
        $sphinx->SetFieldWeights([
            'title' => 20000,
            'description' => 10000,
            'keywords' => 100,
            'content' => 99,
            'filename' => 98,
            'tags' => 97,
        ]);

        $query = '*' . implode('* | *', explode(' ', $params->query)) . '*';

        $sphinxResult = $sphinx->Query("{$query}", 'resources,courses,tests,tasks,polls');
        $matches = $previousResult ? $previousResult->matches : [];

        foreach($sphinxResult['matches'] as $sphinxItem) {
            $itemSphinxType = (int) $sphinxItem['attrs']['sphinx_type'];
            $matchItem = [
                'id' => $sphinxItem['attrs']['nid'],
                'type' => $sphinxAndKbaseTypesMap[$itemSphinxType],
            ];

            $matchKey = sprintf('%s_%s', $matchItem['type'], $matchItem['id']);
            if(!$matches[$matchKey])
                $matches[$matchKey] = $matchItem;
        }

        $result = new HM_DataType_Kbase_SearchResult();
        $result->matches = $matches;
        $result->words = $sphinxResult['words'];

        return $result;
    }

    private function getResourceSearchSelect(HM_DataType_Kbase_SearchParams $params)
    {
        /** @var HM_Project_Resource_ResourceService $currentService */
        $currentService = $this->getService('Resource');
        $select = $currentService->getSelect()
            ->from(['r' => 'resources'], [
                'id' => 'distinct(r.resource_id)',
                'updated' => 'r.updated',
                'title' => 'r.title',
                'rating' => 'AVG(kb_as.assessment)',
                'type' => new Zend_Db_Expr("'".HM_Kbase_KbaseModel::TYPE_RESOURCE."'"),
            ])
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $currentService->quoteInto('tr.item_id = r.resource_id and tr.item_type = ?', HM_Tag_Ref_RefModel::TYPE_RESOURCE),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                []
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $currentService->quoteInto('cll.item_id = r.resource_id and cll.type = ?', HM_Classifier_Link_LinkModel::TYPE_RESOURCE),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                []
            )
            ->joinLeft(
                ['kb_as' => 'kbase_assessment'],
                $currentService->quoteInto(
                    ['kb_as.resource_id = r.resource_id and kb_as.type IN (?)'],
                    array_keys(HM_Resource_ResourceModel::getTypes())
                ),
                []
            )
            ->group([
                'r.resource_id',
                'r.updated',
                'r.title',
            ]);

        if ($params->order == self::ORDER_RATING) {
            $select->having('AVG(kb_as.assessment) > 0');
        }

        $select->where('r.parent_id = ?', 0);
        $select->where('r.subject_id = ?', 0);
        $select->where('r.status IN (?)', [HM_Resource_ResourceModel::STATUS_PUBLISHED, HM_Resource_ResourceModel::STATUS_STUDYONLY]);

        if (count($params->classifiers)) {
            $select->where("cll.classifier_id in(?)", $params->classifiers);
        }

        if (count($params->location)) {
            $select->where("r.location = ?", $params->location);
        }

        if (count($params->status)) {
            $select->where("r.status = ?", $params->status);
        }

        if(count($params->tags)) {
            $this->_addTagsWhere($params, $select);
        }

        // the more fields to search, the less relevance
        if($params->query) {
            $select->where($currentService->quoteInto([
                'LOWER(r.title) LIKE LOWER(?) or ',
                /*'r.filename LIKE ? or ',*/
                'LOWER(t.body) = LOWER(?) or ',
                /*'r.description LIKE ? or ',
                'r.content LIKE ? or ',*/
                'LOWER(cl.name) LIKE LOWER(?)',
            ], [
                '%'.$params->query.'%',
                /*'%'.$params->query.'%',*/
                $params->query,
                '%'.$params->query.'%',
                /*'%'.$params->query.'%',
                '%'.$params->query.'%',*/
            ]));
        }

        return $select;
    }

    private function getCourseSearchSelect(HM_DataType_Kbase_SearchParams $params)
    {
        /** @var HM_Course_CourseService $currentService */
        $currentService = $this->getService('Course');

        $select = $currentService->getSelect()
            ->from(['c' => 'Courses'], [
                'id' => 'distinct(c.CID)',
                'updated' => 'c.lastUpdateDate',
                'title' => 'c.Title',
                'rating' => 'AVG(kb_as.assessment)',
                'type' => new Zend_Db_Expr("'".HM_Kbase_KbaseModel::TYPE_COURSE."'"),
            ])
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $currentService->quoteInto('tr.item_id = c.CID and tr.item_type = ?', HM_Tag_Ref_RefModel::TYPE_COURSE),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                []
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $currentService->quoteInto('cll.item_id = c.CID and cll.type = ?', HM_Classifier_Link_LinkModel::TYPE_COURSE),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                []
            )
            ->joinLeft(
                ['kb_as' => 'kbase_assessment'],
                $currentService->quoteInto(
                    ['kb_as.resource_id = c.CID and kb_as.type = ?'],
                    [HM_Kbase_KbaseModel::TYPE_COURSE]
                ),
                []
            )
            ->group([
                'c.CID',
                'c.lastUpdateDate',
                'c.Title',
            ]);

        $select->where('c.subject_id = ?', 0);
        $select->where('c.status IN (?)', [HM_Course_CourseModel::STATUS_ACTIVE, HM_Course_CourseModel::STATUS_STUDYONLY]);

        if(count($params->classifiers)) {
            $select->where("cll.classifier_id in(?)", $params->classifiers);
        }

        if(count($params->tags)) {
            $this->_addTagsWhere($params, $select);
        }

        if($params->query) {
            $select->where($currentService->quoteInto([
                'LOWER(c.Title) LIKE LOWER(?) or ',
                /*'c.Description LIKE ? or ',*/
                'LOWER(t.body) = LOWER(?) or ',
                'LOWER(cl.name) LIKE LOWER(?) ',
            ], [
                '%'.$params->query.'%',
                /*'%'.$params->query.'%',*/
                $params->query,
                '%'.$params->query.'%',
            ]));
        }

        return $select;
    }

    /**
     * @param HM_DataType_Kbase_SearchParams $params
     * @param $type
     * @return mixed
     * @throws HM_Exception
     * type - one of HM_Kbase_KbaseModel TYPE constants
     */
    private function getQuestSearchSelect(HM_DataType_Kbase_SearchParams $params, $type)
    {
        /** @var HM_Quest_QuestService $currentService */
        $currentService = $this->getService('Quest');

        if(HM_Kbase_KbaseModel::TYPE_TEST === $type) {
            $tagType = HM_Tag_Ref_RefModel::TYPE_TEST;
            $classifierLinkType = HM_Classifier_Link_LinkModel::TYPE_TEST;
            $questType = HM_Quest_QuestModel::TYPE_TEST;
        } elseif(HM_Kbase_KbaseModel::TYPE_POLL === $type) {
            $tagType = HM_Tag_Ref_RefModel::TYPE_POLL;
            $classifierLinkType = HM_Classifier_Link_LinkModel::TYPE_POLL;
            $questType = HM_Quest_QuestModel::TYPE_POLL;
        } else {
            throw new HM_Exception('Использован неизвестный тип события');
        }

        $select = $currentService->getSelect()
            ->from(['qs' => 'questionnaires'], [
                'id' => 'distinct(qs.quest_id)',
                'updated' => new Zend_Db_Expr('NULL'),
                'title' => 'qs.name',
                'rating' => new Zend_Db_Expr('NULL'),
                'type' => new Zend_Db_Expr("'".$type."'"),
            ])
            ->joinLeft(['qqq' => 'quest_question_quests'], 'qqq.quest_id=qs.quest_id', [])
            ->joinLeft(['qq' => 'quest_questions'], 'qq.question_id=qqq.question_id', [])
            ->joinLeft(['qv' => 'quest_question_variants'], 'qv.question_id=qqq.question_id', [])
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $currentService->quoteInto('tr.item_id = qs.quest_id and tr.item_type = ?', $tagType),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                []
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $currentService->quoteInto('cll.item_id = qs.quest_id and cll.type = ?', $classifierLinkType),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                []
            )
            ->group([
                'qs.quest_id',
                'qs.name',
            ]);

        $select->where('qs.subject_id = ?', 0);
        $select->where('qs.status = ?', HM_Quest_QuestModel::STATUS_RESTRICTED);
        $select->where('qs.type = ?', $questType);

        if(count($params->classifiers)) {
            $select->where("cll.classifier_id in(?)", $params->classifiers);
        }

        if(count($params->tags)) {
            $this->_addTagsWhere($params, $select);
        }

        if($params->query) {
            $select->where($currentService->quoteInto([
                'LOWER(qs.name) LIKE LOWER(?) or ',
                /*'qs.description LIKE ? or ',*/
                /*'qq.question LIKE ? or ',
                'qv.variant LIKE ? or ',*/
                'LOWER(t.body) = LOWER(?) or ',
                'LOWER(cl.name) LIKE LOWER(?) ',
            ], [
                '%'.$params->query.'%',
                /*'%'.$params->query.'%',*/
                /*'%'.$params->query.'%',
                '%'.$params->query.'%',*/
                $params->query,
                '%'.$params->query.'%',
            ]));
        }

        if (HM_Role_Abstract_RoleModel::ROLE_ATMANAGER != $this->getService('User')->getCurrentUserRole()) {
            $select
                ->where('qs.creator_role is null or qs.creator_role <> ?', [HM_Role_Abstract_RoleModel::ROLE_ATMANAGER])
                ->where('qs .quest_id != ?', HM_Quest_QuestModel::NEWCOMER_POLL_ID);
        }

        //die($select);
        return $select;
    }

    private function getTaskSearchSelect(HM_DataType_Kbase_SearchParams $params)
    {
        /** @var HM_Task_TaskService $currentService */
        $currentService = $this->getService('Task');

        $select = $currentService->getSelect()
            ->from(['ts' => 'tasks'], [
                'id' => 'distinct(ts.task_id)',
                'updated' => 'ts.updated',
                'title' => 'ts.title',
                'rating' => new Zend_Db_Expr('NULL'),
                'type' => new Zend_Db_Expr("'".HM_Kbase_KbaseModel::TYPE_TASK."'"),
            ])
            ->joinLeft(['tv' => 'tasks_variants'], 'tv.task_id=ts.task_id', [])
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $currentService->quoteInto('tr.item_id = ts.task_id and tr.item_type = ?', HM_Tag_Ref_RefModel::TYPE_TASK),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                []
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $currentService->quoteInto('cll.item_id = ts.task_id and cll.type = ?', HM_Classifier_Link_LinkModel::TYPE_TASK),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                []
            )
            ->group([
                'ts.task_id',
                'ts.updated',
                'ts.title',
            ]);

        $select->where('ts.subject_id = ?', 0);
        $select->where('ts.status = ?', HM_Task_TaskModel::STATUS_PUBLISHED);

        if(count($params->classifiers)) {
            $select->where("cll.classifier_id in(?)", $params->classifiers);
        }

        if(count($params->tags)) {
            $this->_addTagsWhere($params, $select);
        }

        if($params->query) {
            $select->where($currentService->quoteInto([
                'LOWER(ts.title) LIKE LOWER(?) or ',
                /*'ts.description LIKE ? or ',
                'tv.description LIKE ? or ',*/
                'LOWER(t.body) = LOWER(?) or ',
                'LOWER(cl.name) LIKE LOWER(?) ',
            ], [
                '%'.$params->query.'%',
                /*'%'.$params->query.'%',
                '%'.$params->query.'%',*/
                $params->query,
                '%'.$params->query.'%',
            ]));
        }

        //die($select);
        return $select;
    }

    protected function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function getResourceModels(array $ids): array
    {
        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');

        $resources = $resourceService->getSelect()
            ->from(
                ['r' => 'resources'],
                [
                    'r.resource_id',
                    'r.resource_id_external',
                    'r.title',
                    'r.url',
                    'r.volume',
                    'r.filename',
                    'r.type',
                    'r.filetype',
                    'r.created',
                    'r.updated',
                    'r.created_by',
                    'r.services',
                    'r.subject',
                    'r.subject_id',
                    'r.status',
                    'r.location',
                    'r.db_id',
                    'r.test_id',
                    'r.activity_id',
                    'r.activity_type',
                    'r.parent_id',
                    'r.parent_revision_id',
                    'r.external_viewer',
                    'description' => new Zend_Db_Expr('CAST(r.description as CHAR)'),
                    'content' => new Zend_Db_Expr('CAST(r.content as CHAR)'),
                    'related_resources' => new Zend_Db_Expr('CAST(r.related_resources as CHAR)'),
                    'kbase_type' => new Zend_Db_Expr("'".HM_Kbase_KbaseModel::TYPE_RESOURCE."'"),
                ]
            )
            ->joinLeft(
                ['kb_as' => 'kbase_assessment'],
                $resourceService->quoteInto(
                    ['kb_as.resource_id = r.resource_id and kb_as.type IN (?)'],
                    array_keys(HM_Resource_ResourceModel::getTypes())
                ),
                ['rating' => new Zend_Db_Expr('AVG(kb_as.assessment)')]
            )
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $resourceService->quoteInto(
                    'tr.item_id = r.resource_id and tr.item_type = ?',
                    HM_Tag_Ref_RefModel::TYPE_RESOURCE
                ),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                ['tag' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t.body)')]
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $resourceService->quoteInto(
                    'cll.item_id = r.resource_id and cll.type = ?',
                    HM_Classifier_Link_LinkModel::TYPE_RESOURCE
                ),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                ['classifiers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(CONCAT(CONCAT(cl.classifier_id, '#'), cl.name), '#'), cl.type))")]
            )
            ->group([
                'r.resource_id',
                'r.resource_id_external',
                'r.title',
                'r.url',
                'r.volume',
                'r.filename',
                'r.type',
                'r.filetype',
                'r.created',
                'r.updated',
                'r.created_by',
                'r.services',
                'r.subject',
                'r.subject_id',
                'r.status',
                'r.location',
                'r.db_id',
                'r.test_id',
                'r.activity_id',
                'r.activity_type',
                'r.parent_id',
                'r.parent_revision_id',
                'r.external_viewer',
                'CAST(r.description as CHAR)',
                'CAST(r.content as CHAR)',
                'CAST(r.related_resources as CHAR)',
                
            ])
            ->where('r.resource_id in (?)', $ids)
            ->query()->fetchAll();

        return (new HM_Collection($resources, 'HM_Resource_ResourceModel'))->asArrayOfObjects();
    }

    private function getCourseModels(array $ids): array
    {
        /** @var HM_Course_CourseService $courseService */
        $courseService = $this->getService('Course');

        $courses = $courseService->getSelect()
            ->from(
                ['c' => 'Courses'],
                [
                    'c.Title',
                    'c.Status',
                    'c.createby',
                    'c.did',
                    'c.provider_options',
                    'c.developStatus',
                    'Description' => new Zend_Db_Expr('CAST(c.Description as CHAR)'),
                    'CD' => new Zend_Db_Expr('CAST(c.CD as CHAR)'),
                    'tree' => new Zend_Db_Expr('CAST(c.tree as CHAR)'),
                    'c.CID',
                    'c.TypeDes',
                    'c.valuta',
                    'c.longtime',
                    'c.credits_student',
                    'c.credits_teacher',
                    'c.locked',
                    'c.chain',
                    'c.is_poll',
                    'c.is_module_need_check',
                    'c.type',
                    'c.progress',
                    'c.sequence',
                    'c.provider',
                    'c.services',
                    'c.has_tree',
                    'c.new_window',
                    'c.emulate',
                    'c.format',
                    'c.author',
                    'c.emulate_scorm',
                    'c.extra_navigation',
                    'c.Fee',
                    'c.cBegin',
                    'c.cEnd',
                    'c.createdate',
                    'c.planDate',
                    'c.lastUpdateDate',
                    'c.archiveDate',
                    'kbase_type' => new Zend_Db_Expr("'".HM_Kbase_KbaseModel::TYPE_COURSE."'"),
                ]
            )
            ->joinLeft(
                ['kb_as' => 'kbase_assessment'],
                $courseService->quoteInto(
                    ['kb_as.resource_id = c.CID and kb_as.type = ?'],
                    [HM_Kbase_KbaseModel::TYPE_COURSE]
                ),
                ['rating' => new Zend_Db_Expr('AVG(kb_as.assessment)')]
            )
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $courseService->quoteInto(
                    'tr.item_id = c.CID and tr.item_type = ?',
                    HM_Tag_Ref_RefModel::TYPE_COURSE
                ),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                ['tag' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t.body)')]
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $courseService->quoteInto(
                    'cll.item_id = c.CID and cll.type = ?',
                    HM_Classifier_Link_LinkModel::TYPE_COURSE
                ),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                ['classifiers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(CONCAT(CONCAT(cl.classifier_id, '#'), cl.name), '#'), cl.type))")]
            )
            ->group([
                'c.Title',
                'c.Status',
                'c.createby',
                'c.did',
                'c.provider_options',
                'c.developStatus',
                new Zend_Db_Expr('CAST(c.Description as CHAR)'),
                new Zend_Db_Expr('CAST(c.CD as CHAR)'),
                new Zend_Db_Expr('CAST(c.tree as CHAR)'),
                'c.CID',
                'c.TypeDes',
                'c.valuta',
                'c.longtime',
                'c.credits_student',
                'c.credits_teacher',
                'c.locked',
                'c.chain',
                'c.is_poll',
                'c.is_module_need_check',
                'c.type',
                'c.progress',
                'c.sequence',
                'c.provider',
                'c.services',
                'c.has_tree',
                'c.new_window',
                'c.emulate',
                'c.format',
                'c.author',
                'c.emulate_scorm',
                'c.extra_navigation',
                'c.Fee',
                'c.cBegin',
                'c.cEnd',
                'c.createdate',
                'c.planDate',
                'c.lastUpdateDate',
                'c.archiveDate',
                
            ])
            ->where('c.CID in (?)', $ids)
            ->query()->fetchAll();

        return (new HM_Collection($courses, 'HM_Course_CourseModel'))->asArrayOfObjects();
    }

    private function getQuestModels(array $ids, $type): array
    {
        if(HM_Kbase_KbaseModel::TYPE_TEST === $type) {
            $tagType = HM_Tag_Ref_RefModel::TYPE_TEST;
            $classifierLinkType = HM_Classifier_Link_LinkModel::TYPE_TEST;
            $kbaseType = HM_Kbase_KbaseModel::TYPE_TEST;
        } elseif(HM_Kbase_KbaseModel::TYPE_POLL === $type) {
            $tagType = HM_Tag_Ref_RefModel::TYPE_POLL;
            $classifierLinkType = HM_Classifier_Link_LinkModel::TYPE_POLL;
            $kbaseType = HM_Kbase_KbaseModel::TYPE_POLL;
        } else {
            throw new HM_Exception('Использован неизвестный тип события');
        }

        /** @var HM_Quest_QuestService $questService */
        $questService = $this->getService('Quest');
        $quest = $questService->getSelect()
            ->from(
                ['qs' => 'questionnaires'],
                [
                    'qs.quest_id',
                    'qs.type',
                    'qs.status',
                    'qs.description',
                    'qs.name',
                    'qs.subject_id',
                    'qs.scale_id',
                    'qs.displaycomment',
                    'qs.profile_id',
                    'qs.creator_role',
                    'kbase_type' => new Zend_Db_Expr("'".$type."'"),
                ]
            )
            ->joinLeft(
                ['kb_as' => 'kbase_assessment'],
                $questService->quoteInto(
                    ['kb_as.resource_id = qs.quest_id and kb_as.type = ?'],
                    [$kbaseType]
                ),
                ['rating' => new Zend_Db_Expr('AVG(kb_as.assessment)')]
            )
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $questService->quoteInto(
                    'tr.item_id = qs.quest_id and tr.item_type = ?',
                    $tagType
                ),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                ['tag' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t.body)')]
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $questService->quoteInto(
                    'cll.item_id = qs.quest_id and cll.type = ?',
                    $classifierLinkType
                ),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                ['classifiers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(CONCAT(CONCAT(cl.classifier_id, '#'), cl.name), '#'), cl.type))")]
            )
            ->group([
                'qs.quest_id',
                'qs.type',
                'qs.status',
                'qs.description',
                'qs.name',
                'qs.subject_id',
                'qs.scale_id',
                'qs.displaycomment',
                'qs.profile_id',
                'qs.creator_role',
                
            ])
            ->where('qs.quest_id in (?)', $ids)
            ->query()->fetchAll();

        return (new HM_Collection($quest, 'HM_Quest_QuestModel'))->asArrayOfObjects();
    }

    private function getTaskModels(array $ids): array
    {
        /** @var HM_Task_TaskService $taskService */
        $taskService = $this->getService('Task');
        $tasksSelect = $taskService->getSelect()
            ->from(
                ['ts' => 'tasks'],
                [
                    'ts.task_id',
                    'ts.title',
                    'ts.status',
                    'description' => new Zend_Db_Expr('CAST(ts.description as CHAR)'),
                    'ts.created',
                    'ts.updated',
                    'ts.created_by',
                    'ts.subject_id',
                    'ts.location',
                    'kbase_type' => new Zend_Db_Expr("'".HM_Kbase_KbaseModel::TYPE_TASK."'"),
                ]
            )
            ->joinLeft(
                ['kb_as' => 'kbase_assessment'],
                $taskService->quoteInto(
                    ['kb_as.resource_id = ts.task_id and kb_as.type = ?'],
                    [HM_Kbase_KbaseModel::TYPE_TASK]
                ),
                ['rating' => new Zend_Db_Expr('AVG(kb_as.assessment)')]
            )
            ->joinLeft(
                ['tr' => 'tag_ref'],
                $taskService->quoteInto(
                    'tr.item_id = ts.task_id and tr.item_type = ?',
                    HM_Tag_Ref_RefModel::TYPE_TASK
                ),
                []
            )
            ->joinLeft(
                ['t' => 'tag'],
                't.id = tr.tag_id',
                ['tag' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT t.body)')]
            )
            ->joinLeft(
                ['cll' => 'classifiers_links'],
                $taskService->quoteInto(
                    'cll.item_id = ts.task_id and cll.type = ?',
                    HM_Classifier_Link_LinkModel::TYPE_TASK
                ),
                []
            )
            ->joinLeft(
                ['cl' => 'classifiers'],
                'cl.classifier_id = cll.classifier_id',
                ['classifiers' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(CONCAT(CONCAT(cl.classifier_id, '#'), cl.name), '#'), cl.type))")]
            )
            ->group([
                'ts.task_id',
                'ts.title',
                'ts.status',
                 new Zend_Db_Expr('CAST(ts.description as CHAR)'),
                'ts.created',
                'ts.updated',
                'ts.created_by',
                'ts.subject_id',
                'ts.location',
                
            ])
            ->where('ts.task_id in (?)', $ids);
            $tasks = $tasksSelect->query()->fetchAll();

        return (new HM_Collection($tasks, 'HM_Task_TaskModel'))->asArrayOfObjects();
    }
    
    private static function getRangeParams()
    {
        return array(
            'created',
            'developed',
        );
    }

    private static function getFullTextParams()
    {
        return array(
            'content',
            'title',
            'filename',
            'description',
            'resource_id_external',
            'comment',
            'placement',
            'requirements',
        );
    }

    private static function getFilterParams()
    {
        return array(
            'tags',
            'classifiers',
        );
    }

    /**
     * @param HM_DataType_Kbase_SearchParams $params
     * @param Zend_Db_Select $select
     */
    private function _addTagsWhere(HM_DataType_Kbase_SearchParams $params, Zend_Db_Select $select)
    {
        $tagsWhereText = [];
        $tagsWhereBindings = [];

        foreach ($params->tags as $key => $tag) {
            $tagWhereText = 'LOWER(t.body) LIKE LOWER(?)';

            if (array_key_first($params->tags) !== $key) {
                $tagWhereText = ' or ' . $tagWhereText;
            }

            $tagsWhereText[] = $tagWhereText;
            $tagsWhereBindings[] = "%{$tag}%";
        }

        $select->where('(' . $this->getService('Classifier')->quoteInto($tagsWhereText, $tagsWhereBindings) . ')');
    }
}