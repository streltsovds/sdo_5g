<?php
class HM_Absence_Csv_CsvService extends HM_Service_Import_Abstract
{
    public function fetchAll($filename = null, $where = null, $order = null, $count = null, $offset = null)
    {
        if (null !== $filename) {
            $this->getMapper()->getAdapter()->setFileName($filename);
        }

        return $this->getMapper()->fetchAll($filename, $where, $order, $count, $offset);
    }
}