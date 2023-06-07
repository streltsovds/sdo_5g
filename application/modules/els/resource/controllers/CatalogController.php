<?php

class Resource_CatalogController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_classifiers = Null;
    protected $_resources = Null;
    private $_itemType = HM_Classifier_Link_LinkModel::TYPE_RESOURCE;

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/material-icons.css');

        /** @var HM_Classifier_ClassifierService $classifierService */
        $classifierService = $this->getService('Classifier');

        $session = new Zend_Session_Namespace('default');
        $sessionKey = $session->classifier_id;
        $sessionKeyType = $session->classifier_type;

        /**
         * $key:
         * Элемент, по которму фильтруем. Может прилететь как /classifier_id/ без /keyType/ из /kbase, допустим.
         * Оставлено для совместимости чтобы все ссылки не выискивать
         *
         * $keyType
         * Из-за вложенности классификаторов тип может быть как весь классификатор (FILTER_TYPE), так и конкретная рубрика (FILTER_CLASSIFIER)
         */
        $key = (int)$this->_getParam('classifier_id', $this->_getParam('key', $sessionKey ?: 0));
        $keyType = $this->_getParam('keyType', $sessionKeyType ?: HM_Classifier_ClassifierModel::FILTER_CLASSIFIER);

        $session->classifier_id = $key;
        $session->classifier_type = $keyType;

        // Классификаторы по умолчанию
        $types = $classifierService->getTypes($this->_itemType);

        $select = $this->getService('Subject')->getSelect();
        $select->from(array('r' => 'resources'),
            array(
                'resource_id' => 'r.resource_id',
                'name' => 'r.title',
                'restype' => 'r.type',
                'tags' => new Zend_Db_Expr("GROUP_CONCAT(distinct t.body)"),
                'filetype',
                'filename',
                'activity_type',
                'created' => 'r.created',
                'left' => new Zend_Db_Expr('MIN(class.lft)'),
                'classes' => new Zend_Db_Expr("GROUP_CONCAT(distinct class.name)"),
            )
        )
            ->joinLeft(array('c' => 'classifiers_links'), 'r.resource_id = c.item_id AND c.type = ' . (int)$this->_itemType, array())
            ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
            ->joinLeft(array('tr' => 'tag_ref'), 'tr.item_id = r.resource_id AND item_type = ' . HM_Tag_Ref_RefModel::TYPE_RESOURCE, array())
            ->joinLeft(array('t' => 'tag'), 't.id = tr.tag_id', array())
            ->where('r.status = ' . HM_Resource_ResourceModel::STATUS_PUBLISHED)
            ->where('subject_id IS NULL OR subject_id = ?', 0)
            ->order('left ASC')
            ->group(array(
                'r.resource_id',
                'r.title',
                'r.type',
                'r.filetype',
                'r.filename',
                'r.activity_type',
                'r.created'
            ));

        // Прилетел фильтр
        $classifier = false;
        if ($key) {
            switch ($keyType) {
                case HM_Classifier_ClassifierModel::FILTER_TYPE:
                    $select->where('class.type = ' . $key);
                    break;

                case HM_Classifier_ClassifierModel::FILTER_CLASSIFIER;
                    $select
                        // Для фильтрации по выбранной рубрике
                        ->joinInner(array('c2' => 'classifiers_links'), 'r.resource_id = c2.item_id AND c2.type = ' . (int)$this->_itemType, array())
                        ->joinInner(array('class2' => 'classifiers'), 'c2.classifier_id = class2.classifier_id', array())
                        ->where('class2.classifier_id = ' . $key);

                    $classifier = $classifierService->findOne($key);
                    break;
            }
        }

        $grid = $this->getGrid($select, array(
            'resource_id' => array('hidden' => true),
            'filetype' => array('hidden' => true),
            'filename' => array('hidden' => true),
            'activity_type' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'callback' => array(
                    'function' => array($this, 'updateResourceName'),
                    'params' => array('{{resource_id}}', '{{name}}', '{{restype}}', '{{filetype}}', '{{filename}}', '{{activity_type}}')
                ),
            ),
            'restype' => array(
                'title' => _('Тип ресурса'),
                'callback' =>
                    array(
                        'function' => array($this, 'updateResourceType'),
                        'params' => array('{{restype}}'),
                    )
            ),
            'created' => array('title' => _('Дата создания'), 'format' => 'date'),
            'left' => array('hidden' => true),
            'classes' => array('title' => _('Классификация'),
                'color' => HM_DataGrid_Column::colorize('classes'),
                'callback' =>
                    array(
                        'function' => array($this, 'updateClassifiers'),
                        'params' => array('{{classes}}')
                    )
            ),
            'tags' => array(
                'title' => _('Метки'),
                'callback' =>
                    array(
                        'function' => array($this, 'updateTags'),
                        'params' => array('{{tags}}'),
                    ),
                'color' => HM_DataGrid_Column::colorize('tags')
            ),
        ),
            array(
                'name' => null,
                'created' => array('render' => 'DateSmart'),
                'restype' => array('values' => HM_Resource_ResourceModel::getTypes()),
                'classes' => null,
                'tags' => null,
            )

        );

        $this->view->types = $types;
        $this->view->type = $key;

        $rawTree = $classifierService->getKnowledgeBaseClassifiers($this->_itemType, true);
        foreach ($rawTree as $id => $raw) {

            $tree[] = [
                'title' => $raw['title'],
                'count' => 0,
                'key' => $id,
                'keyType' => 'type',
                'isLazy' => true,
                'isFolder' => true,
                'expand' => false
            ];
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

        if (!$this->isAjaxRequest()) {

//            $grid->autoGetHeaderActions = false;

            /**
             * TODO запоминание выбранного классификатора по аналогии с
             * @see Orgstructure_ListController::indexAction()
             */

            $rubricatorValue = !empty($classifier)
                ? $this->getService('Classifier')->classifierToFrontendData(
                    $classifier,
                    $classifier->classifier_id
                )
                : null;

            $rubricatorUrl = $this->view->url([
                'module' => 'classifier',
                'controller' => 'ajax',
                'action' => 'get-tree-branch',
                'classifier_id' => null,
                'key' => null,
            ]);

            $gridUrl = $this->view->url([
                'module' => 'resource',
                'controller' => 'catalog',
                'action' => 'index',
                'gridmod' => 'ajax',
                'classifier_id' => null,
                'key' => null,
            ]);

            /** @see HM_View_Helper_VueRubricatorGridButton */
            $grid->headerActionsBeforeHtml = $this->view->vueRubricatorGridButton(
                _('Классификация'),
                $rubricatorValue,
                [ // rubricator props
                    'itemsData' => $tree,
                    'gridId' => $grid->getGridId(),
                    'gridUrl' => $gridUrl,
                    'url' => $rubricatorUrl,
                ]
            );
        }

        $tree = array(
            0 => array(
                'title' => $this->_classifierType->name,
                'count' => 0,
                'key' => 0,
                'isLazy' => true,
                'isFolder' => true,
                'expand' => true
            ),
            1 => $tree
        );

        $this->view->tree = $tree;

    }

    public function updateRegType($type)
    {
        $types = HM_Subject_SubjectModel::getRegTypes();
        return $types[$type];
    }

    public function getTreeBranchAction()
    {
        $key = (int)$this->_getParam('key', 0);

        $children = $this->getService('Classifier')->getTreeContent($this->_itemType, $key);

        echo HM_Json::encodeErrorSkip($children);
        exit;
    }

    public function updateClassifiers($classes)
    {
        $items = explode(',', $classes);

        /** @var HM_Classifier_ClassifierService $classifierService */
        $classifierService = $this->getService('Classifier');

        $itemsCount = count($items);

        return (string)HM_Grid_UnfoldingList::create(array(
            'title' => $itemsCount > 1 ? $classifierService->pluralFormCount($itemsCount) : false,
            'items' => $items,
            'emptyText' => _('Нет')
        ));

    }

    public function updateTags($tags)
    {
        $items = explode(',', $tags);

        /** @var HM_Tag_TagService $tagsService */
        $tagsService = $this->getService('Tag');

        $itemsCount = count($items);

        return (string)HM_Grid_UnfoldingList::create(array(
            'title' => $itemsCount > 1 ? $tagsService->pluralTagCount($itemsCount) : false,
            'items' => $items,
            'emptyText' => _('Нет')
        ));

    }

    public function updateResourceType($type)
    {
        $types = HM_Resource_ResourceModel::getTypes();
        return $types[$type];
    }
}
