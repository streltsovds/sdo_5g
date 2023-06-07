<?php
class Kbase_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {
        $this->addSearchSidebar();

        $resourceTreeUrl = $this->view->url(['module' => 'resource', 'controller' => 'catalog']);
        $notFoundText = _("
            <a href=\"{$resourceTreeUrl}\">просмотра по классификаторам</a>.
        ");

        $resources = $this->getService('Resource')->fetchAll(
            $this->quoteInto(
                array(
                    'parent_id = ?',
                    ' AND status = ? ',
                    ' AND location = ?'),
                array(
                    0,
                    HM_Resource_ResourceModel::STATUS_PUBLISHED,
                    HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL)
            )
        );

        $relations = array();
        $relationIds = $resources->getList('resource_id', 'related_resources');
        foreach ($relationIds as $relationId) {
            if (!empty($relationId)) {
                // Иногда в relation попадает "Array" вместо ID, оттого потом количество связей неверное
                $relations = array_merge($relations, array_filter(array_map('intval', explode(',', $relationId))));
            }
        }

        $resourcesForLastMonthCount =
             ($this->getService('Resource')->countAll(
                 $this->quoteInto(
                     array(
                         ' parent_id = ? ',
                         ' AND status = ? ',
                         ' AND location = ? ',
                         ' AND created >= ? '
                     ),
                     array(
                         0,
                         HM_Resource_ResourceModel::STATUS_PUBLISHED,
                         HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL,
                         (new \DateTime('1 month ago'))->format('Y-m-d')
                     )
                 )
             ))
        ;

        /** @var HM_Material_SearchService $materialSearchService */
        $materialSearchService = $this->getService('MaterialSearch');

        $lastResourcesParams = new HM_DataType_Kbase_SearchParams();

        $lastResourcesParams->order = $materialSearchService::ORDER_LAST;
        $lastResourcesParams->types = [HM_Kbase_KbaseModel::TYPE_RESOURCE];
        $lastResourcesParams->location = HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL;
        $lastResourcesParams->status = HM_Resource_ResourceModel::STATUS_PUBLISHED;

        $lastResourcesSearchResult = $materialSearchService->search($lastResourcesParams);
        $lastResourcesMatches = $lastResourcesSearchResult->matches;
        $lastResourcesModels = $materialSearchService->getModels(array_slice($lastResourcesMatches, 0, 6));
        $lastResources = [];

        foreach ($lastResourcesModels as $lastResourceModel) {
            $lastResourceItem = $lastResourceModel->getUnifiedData();
            $lastResourceItem['content'] = $this->view->materialPreview($lastResourceModel);
            if ($lastResourceItem['kbase_type'] == HM_Kbase_KbaseModel::TYPE_RESOURCE && $lastResourceItem['filetype'] == HM_Files_FilesModel::FILETYPE_ZIP) {
                $lastResourceItem['type'] = HM_Resource_ResourceModel::TYPE_HTML;
            }

            $lastResources[] = $lastResourceItem;
        }

        $topRatingResourcesParams = new HM_DataType_Kbase_SearchParams();

        $topRatingResourcesParams->order = $materialSearchService::ORDER_RATING;
        $topRatingResourcesParams->types = array_merge([HM_Kbase_KbaseModel::TYPE_RESOURCE], array_keys(HM_Resource_ResourceModel::getTypes()));
        $topRatingResourcesParams->location = HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL;
        $topRatingResourcesParams->status = HM_Resource_ResourceModel::STATUS_PUBLISHED;

        $topRatingResourcesSearchResult = $materialSearchService->search($topRatingResourcesParams);
        $topRatingResourcesMatches = $topRatingResourcesSearchResult->matches;
        $topRatingResourcesModels = $materialSearchService->getModels(array_slice($topRatingResourcesMatches, 0, 6));
        $topRatingResources = [];

        foreach ($topRatingResourcesModels as $topRatingResourceModel) {
            $topRatingResourceItem = $topRatingResourceModel->getUnifiedData();
            $topRatingResources[] = $topRatingResourceItem;
        }

        function sortByRating($a, $b) {
            return $b['rating'] - $a['rating'];
        }

        usort($topRatingResources, "sortByRating");

        $topRatingResourceIds = array_column($topRatingResources, 'resource_id');
        if(count($topRatingResourceIds)) {
            $assessmentCount = $this->getService('KbaseAssessment')
                ->getSelect()
                ->from(
                    ['ka' => 'kbase_assessment'],
                    [
                        'count' => new Zend_Db_Expr('count(ka.id)')
                    ]
                )
                ->where('resource_id in (?)', $topRatingResourceIds)
                ->query()->fetch();
            $resourcesAssessmentCount = $assessmentCount['count'];
        } else {
            $resourcesAssessmentCount = 0;
        }

        $data = [
            'notFoundText' => HM_Json::encodeErrorSkip($notFoundText),
            'resourcesCount' => HM_Json::encodeErrorSkip([
                'resourcesTotalCount' => count($resources),
                'resourcesRelationsCount' => count($relations) / 2, // связи дублируются у каждого ресурса, поэтому делим на 2
                'resourcesForLastMonthCount' => $resourcesForLastMonthCount,
            ]),
            'classifiersStatistics' => HM_Json::encodeErrorSkip($this->getService('Classifier')->getKnowledgeBaseClassifiersWithResourcesCount()),
            'lastResources' => HM_Json::encodeErrorSkip($lastResources),
            'topRatingResources' => HM_Json::encodeErrorSkip($topRatingResources),
            'resourcesAssessmentCount' => $resourcesAssessmentCount,
        ];

        $this->view->assign($data);
    }
    
    // tagsearch moved to SearchController
        
    public function advancedSearchAction()
    {
        $this->view->setHeader(_('Расширенный поиск'));
        $form = new HM_Form_SearchAdvanced(); 
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
            }
        }
        $this->view->form = $form;
    }

    private function addSearchSidebar()
    {
        $classifiersGroups = $this->getService('Classifier')->getKnowledgeBaseClassifiers();
        $classifiersResults = [];

        if (is_array($classifiersGroups)) {
            foreach ($classifiersGroups as $classifierGroupName => $classifiers) {

                $resultItemsBag = [];
                if(!count($classifiers['items'])) continue;

                foreach ($classifiers['items'] as $classifierKey => $classifier) {
                    $classifierUrl = Zend_Registry::get('view')->url([
                        'module' => 'resource',
                        'controller' => 'catalog',
                        'action' => 'index',
                        'classifier_id' => $classifier->classifier_id,
                    ], null, true);

                    $resultItemsBag[] = [
                        "id" => $classifier->classifier_id,
                        "name" => $classifier->name,
                        "url" => $classifierUrl
                    ];
                }

                $classifiersResults[] = [
                    'title' => $classifiers['title'],
                    'items' => $resultItemsBag
                ];

            }
        }

        $kbaseSearch = $this->view->partial("partials/search.tpl", ["classifiers" => json_encode($classifiersResults)]);
        $this->view->addSidebar('search', ['content' => $kbaseSearch, ]);
    }
}