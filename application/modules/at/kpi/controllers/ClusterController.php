<?php
class Kpi_ClusterController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_KpiCluster();
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'order_ASC');
        }

        $select = $this->getService('AtKpiCluster')->getSelect();

        $select->from(
            array(
                'cl' => 'at_kpi_clusters'
            ),
            array(
                'kpi_cluster_id' => 'kpi_cluster_id',
                'name',
                'kpi' => new Zend_Db_Expr('COUNT(DISTINCT k.kpi_id)'),
            ))
            ->joinLeft(array('k' => 'at_kpis'), 'cl.kpi_cluster_id = k.kpi_cluster_id', array())
            ->group(array(
                'cl.kpi_cluster_id',
                'cl.name',
            ));

        $grid = $this->getGrid($select, array(
            'kpi_cluster_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
            ),
            'kpi' => array(
                'title' => _('Количество показателей эффективности'),
                'callback' => array(
                    'function'=> array($this, 'updateCompetences'),
                    'params'=> array('{{name}}', '{{kpi}}')
                )
            ),
        ),
            array(
                'name' => null,
                'kpi' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'kpi',
            'controller' => 'cluster',
            'action' => 'edit'
        ),
            array('kpi_cluster_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'kpi',
            'controller' => 'cluster',
            'action' => 'index',
            'delete' => 1
        ),
            array('kpi_cluster_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'kpi',
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
                    $criteria = $this->getService('AtKpi')->fetchAll(array(
                        'kpi_cluster_id = ?' => $id
                    ));
                    if (count($criteria)) continue;
                    $this->delete($id);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Были удалены только кластеры, не связанные с показателями эффективности.'));
        $this->_redirectToIndex();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['kpi_cluster_id']);
        $this->getService('AtKpiCluster')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $this->getService('AtKpiCluster')->update($values);
    }

    public function delete($id)
    {
        return $this->getService('AtKpiCluster')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $clusterId = $this->_getParam('kpi_cluster_id', 0);
        $cluster = $this->getService('AtKpiCluster')->find($clusterId)->current();
        $form->populate($cluster->getData());
    }

    public function updateCompetences($clusterId, $str)
    {
        if ($str == '0') return $str;
        return '<a href="' . $this->view->url(array('controller' => 'list', 'action' => 'index', 'clustergrid' => $clusterId)) . '">' . $this->view->escape($str) . '</a>';
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
//         $defaults = $this->getService('AtCriterionCluster')->getDefaults();
        $defaults = array('name' => $this->_getParam('title'));
        if ($cluster = $this->getService('AtKpiCluster')->insert($defaults)) {
            $result = $cluster->kpi_cluster_id;
        }
        exit(HM_Json::encodeErrorSkip($result));
    }
}
