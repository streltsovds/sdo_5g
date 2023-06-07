<?php
class HM_Cycle_CycleService extends HM_Service_Abstract
{
    public function getDefaults()
    {
        return array(
            'begin_date' => $this->getDateTime(null, true),
            'end_date' => $this->getDateTime(time() + 31536000, true), // one year plus
        );
    }
    
    public function getCurrent($all = false)
    {
        $cycles = $this->fetchAll(array(
            'begin_date <= ?' => date('Y-m-d'),
            'end_date >= ?' => date('Y-m-d'),
        ));
        if (count($cycles)) {
            return $all ? $cycles : $cycles->current();
        }
        return false;
    }

    public function getPlanningCycles()
    {
        $planingCycles = $this->fetchAll(
            $this->quoteInto(
                array('type = ?'),
                array(HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING)
            )
        );

        return $planingCycles->getList('cycle_id', 'name');
    }
}