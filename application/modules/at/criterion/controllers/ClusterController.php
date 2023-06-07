<?php
class Criterion_ClusterController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_Cluster();
        $this->_setForm($form);
        parent::init();
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
//         $defaults = $this->getService('AtCriterionClusterCluster')->getDefaults();
        $defaults = array('name' => $this->_getParam('title'));
        if ($cluster = $this->getService('AtCriterionClusterCluster')->insert($defaults)) {
            $result = $cluster->cluster_id;
        }
        exit(Zend_Json::encode($result));
    }

    public function indexAction()
    {
        if ($delete = $this->_getParam('delete', 0)) {
            $cluster_id = $this->_getParam('cluster_id', 0);
            $criteria = $this->getService('AtCriterion')->fetchAll(array(
                'cluster_id = ?' => $cluster_id
            ));
            if (count($criteria)) {
                $this->view->showDialog = true;
                $this->view->redirectUrl = $this->view->url(array(
                    'module' => 'criterion',
                    'controller' => 'cluster',
                    'action' => 'delete',
                    'delete' => null,
                ));
            }  else {
                $this->deleteAction();
            }
        }

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'order_ASC');
        }

        $select = $this->getService('AtCriterionCluster')->getSelect();

        $select->from(
            array(
                'cl' => 'at_criteria_clusters'
            ),
            array(
                'cluster_id',
                'name',
                'criteria' => new Zend_Db_Expr('COUNT(DISTINCT c.criterion_id)'),
            )
        );

        $select
            ->joinLeft(array('c' => 'at_criteria'), 'cl.cluster_id = c.cluster_id', array())
            ->group(array(
                'cl.cluster_id',
                'cl.name',
            ))
        ;

        $grid = $this->getGrid($select, array(
            'cluster_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
            ),
            'criteria' => array(
                'title' => _('Количество компетенций'),
                'callback' => array(
                    'function'=> array($this, 'updateCompetences'),
                    'params'=> array('{{name}}', '{{criteria}}')
                )
            ),
        ),
            array(
                'name' => null,
                'criteria' => null,
            )
        );

            $grid->addAction(array(
                'module' => 'criterion',
                'controller' => 'cluster',
                'action' => 'edit'
            ),
                array('cluster_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module' => 'criterion',
                'controller' => 'cluster',
                'action' => 'index',
                'delete' => 1
            ),
                array('cluster_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(
                array(
                    'module' => 'criterion',
                    'controller' => 'cluster',
                    'action' => 'check-before-delete-by',
                ),
                _('Удалить кластеры'),
                _('Вы уверены?')
            );
 
        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function checkBeforeDeleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');

        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $criteria = $this->getService('AtCriterion')->fetchAll(array(
                        'cluster_id = ?' => $id
                    ));
                    if (count($criteria)) continue;
                    $this->delete($id);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Были удалены только кластеры, не связанные с компетенциями.'));
        $this->_redirectToIndex();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['cluster_id']);
        $this->getService('AtCriterionCluster')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $this->getService('AtCriterionCluster')->update($values);
    }

    public function delete($id)
    {
        return $this->getService('AtCriterionCluster')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $clusterId = $this->_getParam('cluster_id', 0);
        $cluster = $this->getService('AtCriterionCluster')->find($clusterId)->current();
        $form->populate($cluster->getData());
    }

    public function updateSessionType($type)
    {
        $types = HM_At_Session_SessionModel::getSessionTypes();
        return isset($types[$type]) ? $types[$type] : '';
    }

    public function updateCompetences($clusterId, $str)
    {
        if ($str == '0') return $str;
        return '<a href="' . $this->view->url(array('controller' => 'competence', 'action' => 'index', 'clustergrid' => $clusterId)) . '?page_id=m1426&page_id=m1426' .'">' . $this->view->escape($str) . '</a>';
    }

}
