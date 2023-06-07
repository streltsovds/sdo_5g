<?php
class Course_ConstructorController extends HM_Controller_Action_Course
{
    public function updateStructureAction()
    {
        $courseId = $this->_getParam('course_id', 0);
        $subjectId = $this->_getParam('subject_id', null);
        /**
         * $nodes - массив со структурой:
            array(
                delete => array(oid, oid, ...)
                update =>
                insert => array(
                    node => array(
                        sql_data => array(данные для запроса)
                        res_data => array(информация о ресурсе)
                        key => id в dynatree
                        prev_key => id предыдущего элемента
                        другие данные узла ...
                    )
                )
            )
         *
         */
        $nodes = $this->_getParam('nodes', array());

        /** @var HM_Course_Item_ItemService $courseItemService */
        $courseItemService = $this->getService('CourseItem');
        $nodes_by_key = $courseItemService->updateStructure($courseId, $nodes, $subjectId);

        $this->view->assign(array($nodes_by_key));
    }

    public function getDynatreeDataAction()
    {
        $courseId = (int) $this->_getParam('course_id', 0);

        /** @var HM_Course_Item_ItemService $courseItemService */
        $courseItemService = $this->getService('CourseItem');
        $treeData = $courseItemService->getHmTreeData($courseId);

        $this->view->assign($treeData);
    }

    public function indexAction()
    {
        //подключаем jQuery UI
        $this->view->jQuery()->enable()->uiEnable();

        /** @var HM_View_Helper_HM $HM */
        $HM = $this->view->HM();

        $courseId = (int) $this->_getParam('course_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        /** @var HM_Course_Item_ItemService $courseItemService */
        $courseItemService = $this->getService('CourseItem');
        $treeData = $courseItemService->getHmTreeData($courseId);


        /** @var HM_Course_CourseService $courseService */
        $courseService = $this->getService('Course');
        $course = $courseService->getOne($courseService->find($courseId));

        $indexerUrl = null;
        $sphinxIndexer = Zend_Registry::get('config')->sphinx->indexer;
        if ($sphinxIndexer) {
            $indexerUrl = $this->view->url(array(
                'module' => 'course',
                'controller' => 'constructor',
                'action' => 'run-indexer'
            ));
        }

        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');

        $this->view->courseId  = $courseId;
        $this->view->subjectId = $subjectId;
        $this->view->course    = $course;

        $resourceList = array();
        $resourceTypes = HM_Resource_ResourceModel::getTypes();
        $excludeTypes = array(
            HM_Resource_ResourceModel::TYPE_CARD,
            HM_Resource_ResourceModel::TYPE_WEBINAR,
        );
        foreach($resourceTypes as $type => $title) {
            if (in_array($type, $excludeTypes)) {
                continue;
            }
            $resourceList[] = array(
                'title' => _('Создать ресурс').': '.$title,
                'data' => array(
                    'title' => $title,
                    'iconClass' => HM_Resource_ResourceService::getIconClass($type, null, '', 0),
                    'sql_res_data' => array(
                        'title' => $title,
                        'type' => $type,
                        'url' => 'index.html',
                        'location' => $subjectId ? 0 : 1, //в курс или в базу знаний
                        'status' => $subjectId ? 1 : 0,   //опубликован или нет
                        'subject_id' => $subjectId ? $subjectId : 0,
                    )
                )
            );
        }

        $HM->create('hm.core.ui.trainingModulesConstructor.Constructor', array(
            'renderTo' => '#hm-constructor',
            'treeData' => array(
                'courseId' => $courseId,
                'subjectId'=> $subjectId ? $subjectId : false,
                'data'     => $treeData,
                'title'    => $course->Title,
                'resourceList' => $resourceList
            ),
            'searchData' => array(
                'indexerUrl' => $indexerUrl,
                'data' => array(
                    'tags' => $resourceService->getAllTagNames(),
                    'classifiers' => $resourceService->getAllClassifierList()
                )
            ),
            'subjectId'=> $subjectId ? $subjectId : false,
        ));

    }

    public function searchResourcesAction()
    {
        $indexes = $this->_getParam('indexes', array());
        $queryData = $this->_getParam('queryData', array());
        $filterData = $this->_getParam('filterData', array());
        $filterRangeData = $this->_getParam('filterRangeData', array());
        
        $subjectId = (int) $this->_getParam('subject_id', 0);

        /** @var HM_Search_SearchService $searchService */
        $searchService = $this->getService('Search');
        $result = $searchService->sphinxSearch($indexes, $queryData, $filterData, $filterRangeData);

        $data = array();
        $ids = array(0);
        if ($result['matches']) {
            //собираем id ресурсов из результата
            foreach ($result['matches'] as $match) {
                array_push($ids, $match['attrs']['nid']);
            }
        }
        /** @var HM_Resource_ResourceService $resourceService */
        $resourceService = $this->getService('Resource');
        $query = $searchService->extractMainField($queryData);
        if (!count($queryData) || !empty($query)) {
            $resources = $resourceService->fetchAll($resourceService->quoteInto(array(
                    'resource_id IN (?) OR ',
                    '((title LIKE ? OR ', 'resource_id_external LIKE ?)',
                    ' AND status IN (?))',
                    ' AND (subject_id = ? OR subject_id = 0)',
                ), array(
                    $ids,
                    "%{$query}%", "%{$query}%", array(
                        HM_Resource_ResourceModel::STATUS_PUBLISHED,
                        HM_Resource_ResourceModel::STATUS_STUDYONLY,
                    ),
                    $subjectId,
                )),
                'title');
        } else {
            $resources = $resourceService->fetchAll(
                array(
                    'resource_id IN (?)' => $ids
                ),
                'title'
            );
        }

        //заполняем данными
        /** @var HM_Resource_ResourceModel $resource */
        foreach($resources as $resource) {
            array_push($data, array(
                'resource_id' => $resource->resource_id,
                'title' => $resource->title,
                'icon_class' => $resource->getIconClass()
            ));
        }

        //добавляется при инициализации, но тут нам это не нужно
        unset($this->view->withoutContextMenu);

        $this->view->assign($data);
    }

    public function runIndexerAction() {
        /** @var HM_Search_SearchService $searchService */
        $searchService = $this->getService('Search');
        $searchService->runSphinxIndexer();
    }
}