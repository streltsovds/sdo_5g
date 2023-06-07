<?php
class HM_Absence_Csv_CsvMapper extends HM_Mapper_Abstract
{

    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {
            foreach($rows as $index => $row) {

                if ($row['absence_begin']) {
                    $row['absence_begin'] = $this->toDate($row['absence_begin']);
                }
                if ($row['absence_end']) {
                    $row['absence_end'] = $this->toDate($row['absence_end']);
                }

                $row['type'] = HM_Absence_AbsenceModel::getTypeId($row['type']);

                $models[count($models)] = $row;
                unset($rows[$index]);
            }
        }

        return $models;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {

        $adapter = $this->getAdapter();

        $rows = $adapter->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

    public function toDate($field){
        if($field != ''){
            $date = new HM_Date($field, 'dd.MM.YYYY');
            return $date->get("yyyy-MM-dd");
        }
       return '0000-00-00';
    }
}