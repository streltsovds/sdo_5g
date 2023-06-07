<?php
class Kpi_UnitController extends HM_Controller_Action
{
    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = array('name' => $this->_getParam('title'));
        if ($cluster = $this->getService('AtKpiUnit')->insert($defaults)) {
            $result = $cluster->kpi_unit_id;
        }
        exit(HM_Json::encodeErrorSkip($result));
    }

    // автокомплит поля ввода Единицы измерения
    public function unitsAction()
    {
        $unitName = $this->getJsonParams()['tag'];
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }
        $units = $this->getService('AtKpiUnit')->fetchAll($this->getUnitCondition($unitName));
        $result = [];

        foreach($units as $unit) {
            $result [] = $unit->name;
        }

        $this->_helper->json($result);
    }

    public function getUnitCondition($unitLike = null)
    {
        $where = array();
        if($unitLike) {
            $where['LOWER(name) LIKE ?'] = '%'.mb_strtolower($unitLike).'%';
        }
        return $where;
    }
}
