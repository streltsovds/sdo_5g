<?php
class HM_Quest_Question_Import_Excel_ExcelMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {
            foreach($rows as $row) {
                $models[count($models)] = $row;
            }

            //$models->setDependences($dependences);
        }

        return $models;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

    public function isTest()
    {
        return $this->getAdapter()->isTest();
    }

}