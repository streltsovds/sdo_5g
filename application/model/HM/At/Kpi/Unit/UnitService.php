<?php
class HM_At_Kpi_Unit_UnitService extends HM_Service_Abstract
{
    public function getUnit($kpiId)
    {
        $return = array();
        $select = $this->getSelect()
            ->from(array('ku' => 'at_kpi_units'), array('kpi_unit_id', 'name'))
            ->join(array('k' => 'at_kpis'), 'ku.kpi_unit_id = k.kpi_unit_id', array())
            ->where('k.kpi_id = ?', intval($kpiId));

        if ($unit = $select->query()->fetchAll()) {
            return array($unit[0]['name']);
        }
        return array();
    }

    public function insertUnit($unit)
    {
        $unit = $unit['name'];
        if (!empty($unit)) {
            $allUnits = $this->fetchAll()->getList('kpi_unit_id', 'name');
            if ($exist_id = array_search($unit, $allUnits)) {
                    return $exist_id;
            } else {
                $data = $this->insert((array(
                    'name' => urldecode($unit),
                )));
                return $data->kpi_unit_id;
            }
        } else return 0;
    }

    /**
     * Функция удаляет все единицы измерения с которыми не связан ни показатель эффективности
     */
    public function clearUnits()
    {
        $select = $this->getSelect()->from(array('ku'=>'at_kpi_units'),'kpi_unit_id')
            ->joinLeft(array('k' => 'at_kpis'), 'ku.kpi_unit_id = k.kpi_unit_id', array())
            ->where('k.kpi_unit_id IS NULL');
        $arRes = $select->query()->fetchAll();

        foreach ($arRes as $unit) {
            $this->delete(intval($unit['kpi_unit_id']));
        }
    }
}