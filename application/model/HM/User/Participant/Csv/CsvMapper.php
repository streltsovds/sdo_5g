<?php
class HM_User_Participant_Csv_CsvMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());

        if (count($rows) > 0) {
            $dependences = array();
            foreach($rows as $index => $row) {

                $model = array();

                foreach($row as $key => $val){
                    if($val != "" ){
                        $model[$key] = $val;
                    }
                }

                $models[count($models)] = $model;
                unset($rows[$index]);
            }




            $models->setDependences($dependences);
        }

        //print_r($models); exit;
        return $models;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

}