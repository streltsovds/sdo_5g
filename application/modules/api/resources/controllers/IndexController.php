<?php
class Resources_IndexController extends HM_Controller_Action_RestOauth
{

    /** @var HM_Material_SearchService _defaultService */
    protected $_defaultService = null;

    public function init()
    {
        parent::init();

        /** @var HM_Resource_ResourceService _defaultService */
        $this->_defaultService = $this->getService('Resource');
    }

    public function getNotFoundMessage()
    {
        return 'Resource not found';
    }

    public function findbykeywordAction()
    {
        $value = $this->_getParam('value');

        /** @var HM_Material_SearchService $materialSearchService */
        $materialSearchService = $this->getService('MaterialSearch');

        $searchParams = new HM_DataType_Kbase_SearchParams();

        $searchParams->query = $value;
        $searchParams->order = $materialSearchService::ORDER_TITLE;
        $searchParams->location = [HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL];
        $searchParams->status = [HM_Resource_ResourceModel::STATUS_PUBLISHED];

        $searchResult = $materialSearchService->search($searchParams);
        $resources = $searchResult->matches;

        if (count($resources)) {
            $collection = $this->_defaultService->fetchAll(
                [
                    'resource_id IN (?)' => array_column($resources, 'id')
                ]);

            $result = $collection->getList('resource_id', 'getRestDefinition');
            $this->view->assign(array_values($result));
        }
    }
}