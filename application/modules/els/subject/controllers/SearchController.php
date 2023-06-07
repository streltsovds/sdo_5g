<?php

class Subject_SearchController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_List;

    const ITEMS_PER_PAGE = 12;
    const ORDER_LAST = 'last';
    const ORDER_RATING = 'rating';
    const ORDER_TITLE = 'title';

    public function indexAction()
    {
        $words = [];
        $this->view->setHeader(_('Результаты поиска'));
        // only for sql fallback
        $orderParam = $this->_getParam('order');
        $lastParam = $this->_getParam('last');
        $query = $this->_getParam('search_query', '');
        $classifiers = $this->_getParam('classifiers', []);
        $statuses = $this->_getParam('statuses', [HM_Subject_SubjectModel::STATE_PENDING, HM_Subject_SubjectModel::STATE_ACTUAL]);

        HM_Search_FilterState::setValue(
            HM_Subject_SubjectModel::SUBJECT_CATALOG_FILTER_NAMESPACE,
            HM_Search_FilterState::CLASSIFIERS_FILTER,
            $classifiers
        );

        HM_Search_FilterState::setValue(
            HM_Subject_SubjectModel::SUBJECT_CATALOG_FILTER_NAMESPACE,
            HM_Search_FilterState::QUERY_FILTER,
            $query
        );

        $this->view->error = false;
        $this->view->query = $query;

        $sphinx = HM_Search_Sphinx::factory();

        $sphinx->SetLimits(0, 1000, 1000);
        $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);

        if (false === $sphinx->Status() or $query == '' or $orderParam) {
            $slqSearchIds = [];

            switch ($orderParam) {
                case static::ORDER_LAST:
                    $order = ['orderDateTime DESC'];
                    break;
                case static::ORDER_RATING:
                    $order = ['orderRating DESC'];
                    break;
                case static::ORDER_TITLE:
                    $order = ['orderTitle DESC'];
                    break;
                default:
                    $order = null;
                    break;
            }

            $extraWhere = ['reg_type <> ?' => HM_Subject_SubjectModel::REGTYPE_ASSIGN_ONLY];

            /** @var HM_Subject_SubjectService $subjectService */
            $subjectService = $this->getService('Subject');
            $slqSearchIds[HM_Search_Sphinx::TYPE_SUBJECT] = $subjectService->search($query, $classifiers, $statuses, $extraWhere, $order);
            $results = [];

            foreach ($slqSearchIds as $groupIndex => $groupIds) {
                foreach ($groupIds as $item) {
                    $resultItem = [];
                    $resultItem['attrs']['nid'] = $item['id'];
                    $resultItem['attrs']['sphinx_type'] = $groupIndex;
                    $resultItem['dateTime'] = strtotime($item['orderDateTime']);
                    $resultItem['rating'] = $item['orderRating'];
                    $resultItem['title'] = $item['orderTitle'];

                    $results[] = $resultItem;
                }
            }

            switch ($orderParam) {
                case static::ORDER_LAST:
                    usort($results, function ($a, $b) {
                        return $b['dateTime'] - $a['dateTime'];
                    });
                    break;
                case static::ORDER_RATING:
                    usort($results, function ($a, $b) {
                        return $b['rating'] - $a['rating'];
                    });
                    break;
                case static::ORDER_TITLE:
                default:
                    usort($results, function ($a, $b) {
                        return strcmp($a["title"], $b["title"]);
                    });
                    break;
            }
        } else {

            $queryForSphinx = '*' . implode('* | *', explode(' ', $query)) . '*';
            $resSubject = $sphinx->Query($queryForSphinx, 'subjects');

            $res['matches'] = [];
            $words = array_flip(explode(' ', $query));

            if (isset($resSubject['matches'])) {
                if (is_array($resSubject['matches']) && count($resSubject['matches']) > 0) {
                    $res['matches'] = $res['matches'] + $resSubject['matches'];
                    $words = $words + $resSubject['words'];
                }
            }

            $results = [];
            if (count($res['matches']) > 0) {
                foreach ($res['matches'] as $key => $value) {
                    if ($value['attrs']['sphinx_type'] == HM_Search_Sphinx::TYPE_SUBJECT) {
                        $results[$key] = $value;
                    }
                }
            }
        }

        $results = $this->_filterByPeriodDates($results);

        if (count($results) == 0) {
            $this->view->error = _('Искомая комбинация слов нигде не встречается');
        }

        $this->view->words = $words;
        $this->_setPaginator($results);

        //you can call this only after _setPaginator()
        if ($this->isAjaxRequest()) {
            $resultItems = [];
            foreach ($this->_data as $resultItem) {
                $convertedResultItem = $resultItem->__toArray();
                $convertedResultItem['content'] = $this->view->materialPreview($resultItem);
                $resultItems[] = $convertedResultItem;
            }

            $response = ['items' => $resultItems];

            if ($paginator = $this->view->paginator) {
                $response['pagination'] = [
                    'pageCurrent' => $paginator->getCurrentPageNumber(),
                    'pageCount' => $paginator->count(),
                ];
            }

            if (!$orderParam && !$lastParam) {
                unset($_SESSION['last_params']);
                $_SESSION['last_params'] = $this->getRequest()->getParams();
                unset($_SESSION['last_params']['ajax']);
            }
            $response['last_params'] = $_SESSION['last_params'];
            $this->_helper->json($response);
        }

        if ($format = $this->_getParam('export')) {
            $this->_export($format);
        }
    }


    private function _setPaginator($results)
    {
        if ($this->view->error == false) {
            $page = $this->_getParam('page', 0);
            $itemsPerPage = $this->_getParam('itemsPerPage', self::ITEMS_PER_PAGE);
            $paginator = Zend_Paginator::factory ($results);
            $paginator->setCurrentPageNumber((int)$page);
            $paginator->setItemCountPerPage($page === 'all' ? $paginator->getTotalItemCount() : $itemsPerPage);

            $currentItems = $paginator->getCurrentItems();
            $subjects = [];
            foreach ($currentItems as $key => $value) {
                if ($value['attrs']['sphinx_type'] == HM_Search_Sphinx::TYPE_SUBJECT) {
                    $subjects[] = $value['attrs']['nid'];
                }
            }
            
            if (count($subjects) > 0) {
                $subjectsCollection = $this->getService('Subject')->getCollectionForSearch($subjects);
            }

            foreach ($currentItems as $key => &$value) {
                // $value['obj'] - для показа в результатах поиска; только текущая страница
                // $this->_data - для экспорта; все страницы
                /** @var HM_Subject_SubjectModel $resultItem */
                $resultItem = $subjectsCollection[$value['attrs']['nid']];
                if(empty($resultItem)) continue;

                $resultItem->userIcon = $resultItem->getUserIcon();
                $resultItem->defaultIcon = $resultItem->getDefaultIcon();

                $teacher = $this->getService('User')->fetchAllDependenceJoinInner(
                    'Teacher',
                    "Teacher.CID = {$resultItem->subid}"
                )->current();

                if(!empty($teacher)) {
                    $resultItem->teacherPhoto = !empty($teacher->getPhoto()) ? $teacher->getPhoto() : $teacher->getDefaultPhoto();
                    $resultItem->teacherUrl = $this->view->url(['module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $teacher->MID]);
                } else {
                    $resultItem->teacherPhoto = '';
                    $resultItem->teacherUrl = '';
                }

                $resultItem->viewUrl = $this->view->url($resultItem->getViewUrl());

//                $resultItem->regBtnText = HM_Subject_SubjectModel::APPROVE_NONE == $resultItem->claimant_process_id ? _('Записаться') : _('Подать заявку');

                $catalogUrl = urlencode($this->view->url(['module' => 'subject', 'controller' => 'catalog', 'action' => 'index'], null, true));

                $resultItem->searchItemType = $value['attrs']['sphinx_type'];

                $resultItem->begin = date('d.m.Y', strtotime($resultItem->begin));
                $resultItem->end = date('d.m.Y', strtotime($resultItem->end));

                $o = new stdClass();
                if ($resultItem->isStudent) {
                    $o->text = _('Курс назначен');
                    $o->isButton = false;
                } elseif ($resultItem->isClaimant) {
                    $o->text = _('Заявка на рассмотрении');
                    $o->isButton = false;
                } elseif (!$resultItem->isStudent && !$resultItem->isClaimant) {
                    if ($resultItem->claimant_process_id) {
                        $o->text = _('Подать заявку');
                    } else {
                        $o->text = _('Записаться');
                    }
                    $o->isButton = true;
                }

                $o->href = $this->view->url(array('module'=> 'user', 'controller' => 'reg', 'action' => 'subject', 'subid' => $resultItem->subid, 'redirect' => $catalogUrl), null, true);
                $resultItem->regStatus = $o;

                unset($resultItem->isClaimant);
                unset($resultItem->isStudent);
                $this->_data[] = $value['obj'] = $resultItem;
            }

            $this->view->paginator = $paginator;
        }
    }

    public function tagAction()
    {
        $arItems = array();
        $tag = trim(strip_tags($this->_getParam('tag', false)));

        $this->view->setHeader(_('Результаты поиска'));
        $this->view->setSubSubHeader($tag);

        if ( !$tag ) {
            $this->_flashMessenger->addMessage(_('Не указана метка'));
            $this->_redirector->gotoSimpleAndExit('index','index','subject');
        }

        $objTags = $this->getOne($this->getService('Tag')->fetchAllDependence('TagRef',
                                                                $this->getService('Tag')->quoteInto('body LIKE ?',"%$tag%")));
        $itemIds = array();
        $types = array_keys(HM_Tag_Ref_RefModel::getBZTypes());
        $refs = $objTags->tagRef;
        if ( count($refs) ) {
            foreach ($refs as $ref) {
                if ( !in_array($ref->item_type, $types)) continue;
                $itemIds[$ref->item_type][$ref->item_id] = true;
                /*
                if ( $ref->item_type == HM_Tag_Ref_RefModel::TYPE_RESOURCE) {
                    $resource = $this->getService('Resource')->find($ref->item_id)->current();
                    if ($resource && $resource->location == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL) continue;
                }
                $objItem = new stdClass();
                $objItem->title = $ref->getService()->getItemTitle($ref->item_id);
                $objItem->icon  = $ref->getService()->getIcon();
                $objItem->description = $ref->getService()->getItemDescription($ref->item_id);
                $objItem->keywords = $this->getService('Tag')->getStrTagsByIds($ref->item_id,$ref->item_type);
                $objItem->item_id = $ref->item_id;
                $objItem->viewAction = $ref->getService()->getItemViewAction($ref->item_id);
                $arItems[] = $objItem;
                */
            }
        }

        $itemIds_resource = (isset($itemIds[HM_Tag_Ref_RefModel::TYPE_RESOURCE]) && is_array($itemIds[HM_Tag_Ref_RefModel::TYPE_RESOURCE])) ? array_keys($itemIds[HM_Tag_Ref_RefModel::TYPE_RESOURCE]) : array();
        $itemIds_course = (isset($itemIds[HM_Tag_Ref_RefModel::TYPE_COURSE]) && is_array($itemIds[HM_Tag_Ref_RefModel::TYPE_COURSE])) ? array_keys($itemIds[HM_Tag_Ref_RefModel::TYPE_COURSE]) : array();

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');

        $currentUserRole = $userService->getCurrentUserRole();

        $items = array();
        if (count($itemIds_resource)) {
            $where = array();
            $where['resource_id IN (?)'] = $itemIds_resource;
            if ($aclService->inheritsRole($currentUserRole,HM_Role_Abstract_RoleModel::ROLE_MANAGER)) {
                $where = $this->getService('Resource')->quoteInto(
                    array(
                        'resource_id IN (?)',
                        ' AND (location = ? OR location IS NULL)',
                    ),
                    array(
                        $itemIds_resource,
                        HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL,
                    ));
            }
            if (count($resourcesCollection = $this->getService('Resource')->fetchAllManyToMany('Tag', 'TagRef', $where))) {
                foreach ($resourcesCollection as $resource) {
                    $items[] = $resource;
                }
            }
        }
        if (count($itemIds_course)) {
            $where = array();
            $where['CID IN (?)'] = $itemIds_course;
            if ($aclService->inheritsRole($currentUserRole,HM_Role_Abstract_RoleModel::ROLE_MANAGER)) {
                $where = $this->getService('Course')->quoteInto(
                    array(
                        'CID IN (?)',
                        ' AND (chain IS NULL OR chain = ?)',
                    ),
                    array(
                        $itemIds_course,
                        0,
                    ));
            }
            if (count($subjectsCollection = $this->getService('Subject')->fetchAllManyToMany('Tag', 'TagRef', $where))) {
                foreach ($subjectsCollection as $subject) {
                    $items[] = $subject;
                }
            }
        }

        // @todo: подключить paginator
        $this->view->tag = $tag;
        $this->view->items = $this->_data = $items;

        if ($format = $this->_getParam('export')) {
            $this->_export($format);
        }           
    }    

    static public function getRangeParams()
    {
        return array(
            'created' => array('from' => 0, 'to' => time()), // key => defaultRange
            'developed' => array('from' => 0, 'to' => time()),
        );
    }

    static public function getFullTextParams()
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

    static public function getNonSearchParams()
    {
        return array(
            'module',
            'controller',
            'action',
            'submit',
            'cancelUrl',
            'page',
            'rubrics',
            'tags',
            'export',
        );
    }

    static public function _escapeDots(&$value)
    {
        $value = str_replace('.', '~', $value);
    }

    static public function _unescapeDots(&$value)
    {
        $value = str_replace('~', '.', $value);
    }

    protected function _getExportAttribs()
    {
        return array(
            _('Тип объекта') => 'getClassName',        
            _('Название') => 'getName',        
            _('Описание') => 'description',        
            _('Дата публикации') => 'getCreateUpdateDate',        
        );
    }

    /**
     * Метод фильтрации результатов поиска sphinx и/или sql по периодам проведения курсов
     *
     * @param array $results
     * @return array
     */
    private function _filterByPeriodDates(array $results)
    {
        if(!count($results))
            return $results;

        $returnResults = [];
        $ids = array_column(array_column($results, 'attrs'), 'nid');

        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        $select = $subjectService->getSelect();

        $select
            ->from(['s' => $subjectService->getTableName()], ['s.subid'])
            ->where(
                $this->quoteInto(
                    [
                        's.period IN (?) OR ',
                        's.period_restriction_type = ? OR ',
                        '(s.period = ? AND ',
                        's.end > ?)',
                    ],
                    [
                        [HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED],
                        HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                        HM_Subject_SubjectModel::PERIOD_DATES,
                        $this->getService('Subject')->getDateTime()
                    ]
                )
            )
            ->where('s.subid IN (?)', $ids);

        $sqlResults = $select->query()->fetchAll();

        if(count($sqlResults)) {
            $sqlIds = array_column($sqlResults, 'subid');

            foreach ($results as $key => $value){
                if(in_array($value['attrs']['nid'], $sqlIds)) {
                    $returnResults[$key] = $value;
                }
            }
        }

        return $returnResults;
    }

}