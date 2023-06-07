<?php
class HM_Scale_ScaleService extends HM_Service_Abstract
{
    public function getMainSelect()
    {
        $select = $this->getSelect()
            ->from(
                array('es' => 'edo_estimation_scales'),
                array('es.estimation_scale_id','es.designation','es.capacity')
                );
        return $select;
    }

    public function delete($id)
    {
        $id = (int)$id;
        $this->getService('ScaleValue')->deleteBy(array('scale_id = ?' => $id));
        parent::delete($id);
    }
    
    public function getPattern($scaleId)
    {
        switch ($scaleId) {
            case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
            return "^[1-9]{1}\d?$|^0$|^100$";
        }
        return false;
    }
    
    public function getMaxValue($scaleId)
    {
        if ($scaleId && ($scale = $this->getService('Scale')->getOne($this->getService('Scale')->fetchAllDependence('ScaleValue', array('scale_id = ?' => $scaleId))))) { // шкала одинаковая у всех evaluation'ов
            if (count($scale->scaleValues)) {
                $scaleValues = $scale->scaleValues->getList('value');
                return max($scaleValues);    
            }
        }
        return 0;
            
    }
    
}