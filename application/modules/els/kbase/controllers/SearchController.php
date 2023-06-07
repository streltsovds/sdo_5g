<?php

class Kbase_SearchController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_List;
    const ITEMS_PER_PAGE = 12;

    public function indexAction()
    {
        $query = $this->_getParam('search_query', '');
        /** @var HM_Material_SearchService $materialSearchService */
        $materialSearchService = $this->getService('MaterialSearch');

        $searchParams = new HM_DataType_Kbase_SearchParams();

        $searchParams->query = $query;
        $searchParams->order = $this->_getParam('order', $materialSearchService::ORDER_TITLE);
        $searchParams->types = (array) $this->_getParam('types', []);
        $searchParams->classifiers = $this->_getParam('classifiers', []);
        $searchParams->tags = $this->_getParam('tags', []);
        $searchParams->location = HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL;
        $searchParams->status = HM_Resource_ResourceModel::STATUS_PUBLISHED;

        $searchResult = $materialSearchService->search($searchParams);
        $result = $searchResult->matches;

        if(count($result) == 0) {
           $this->view->error = _('Искомая комбинация слов нигде не встречается');
        }

        $this->view->setHeader(_('Результаты поиска'));
        $this->view->error = false;
        $this->view->query = $query;
        $this->view->words = array_flip(explode(' ', $query)) + (array) $searchResult->words;

        $this->_setPaginator($result);

        //you can call this only after _setPaginator()
        if ($this->isAjaxRequest()) {
            $isEndUser = $this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ENDUSER]);
            $isSupervisor = $this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR]);
            //die(var_dump($this->_data));
            $resultItems = [];
            foreach ($this->_data as $resultItem) {
                /** @var HM_Model_Abstract  $resultItem */
                $testOrPoll = is_a($resultItem, 'HM_Quest_Type_TestModel') || is_a($resultItem, 'HM_Quest_Type_PollModel');
                if ($testOrPoll && ($isEndUser || $isSupervisor))
                    continue;

                $convertedResultItem = $resultItem->getUnifiedData();
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

            $this->_helper->json($response);
        }

        if ($format = $this->_getParam('export')) {
            $this->_export($format);
        }
    }

    /**
     *  Логика поиска следующая:
     *  между элементами формы используется логическое И
     *  между значениями multiOptions в пределах одного элемента используется ИЛИ
     *  Напрмер: "название == 'блабла' И носитель = (CD ИЛИ DVD)"
     */
    public function advancedSearchAction()
    {
        $session = new Zend_Session_Namespace('advanced_search_results');
        $this->view->setHeader(_('Результаты поиска'));
        $params = $this->_getAllParams();
        array_walk($params, array('Resource_SearchController', '_unescapeDots'));

        require_once(APPLICATION_PATH . '/modules/els/kbase/forms/SearchAdvanced.php'); // как правильно подключить форму из другого module?
        $form = new HM_Form_SearchAdvanced();

        /** @var HM_Material_SearchService $materialSearchService */
        $materialSearchService = $this->getService('MaterialSearch');
        $results = $materialSearchService->extendedSearch($params);

        $this->view->query = $params['content'];
        $this->_setPaginator($results);
        //храним результаты в сессии, для переключения по страницам
        $session->results = $results;

        if ($format = $this->_getParam('export')) {
            $this->_export($format);
        }

        $form->setDefaults($params);

        array_walk($params, array('Resource_SearchController', '_escapeDots'));
        $this->view->params = $params;

        $page = $this->_getParam('page', 0);
        if ($page) {
            $this->view->params = $params;
            $results = $session->results;
            $this->_setPaginator($results);
        }

        if (count($results) == 0){
           $this->view->error = _('Не найдено результатов, удовлетворяющих поисковому запросу');
        }

        $this->view->form = $form;
    }

    private function _setPaginator($results)
    {
        if (empty($this->view->error)) {
            $page = $this->_getParam('page', 0);
            $itemsPerPage = $this->_getParam('itemsPerPage', self::ITEMS_PER_PAGE);
    		$paginator = Zend_Paginator::factory($results);
    		$paginator->setCurrentPageNumber((int) $page);
    		$paginator->setItemCountPerPage($page === 'all' ? $paginator->getTotalItemCount() : $itemsPerPage);

    		$currentItems = (array) $paginator->getCurrentItems();
    		/** @var HM_Material_SearchService $materialSearchService */
    		$materialSearchService = $this->getService('MaterialSearch');
    		$itemsModels = $materialSearchService->getModels($currentItems);

    		foreach ($itemsModels as $model) {
    		    $this->_data[] = $model;
    		}

            $this->view->resultItems = $itemsModels;
            $this->view->paginator = $paginator;
        }
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
}