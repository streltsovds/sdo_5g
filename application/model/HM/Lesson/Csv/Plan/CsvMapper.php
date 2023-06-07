<?php
class HM_Lesson_Csv_Plan_CsvMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = [])
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass([], $this->getModelClass());

        if (count($rows) > 0) {
            $dependences = [];
            foreach ($rows as $index => $row) {
                $model = [];
                foreach ($row as $key => $val) {
                    if ($val != "") {
                        if (in_array($key, ['begin', 'end'])) {
                            $model[$key] = $this->formatDate($val);
                        } else {
                            $model[$key] = $val;
                        }
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

    protected function formatDate($date)
    {
        if (empty($date)) $date = null;
        elseif (false !== strpos($date, '.')) $date = date('Y-m-d', strtotime($date));
        elseif (is_numeric($date)) $date = date('Y-m-d', strtotime("+" . $date . " day" . ($date == 1 ? "" : "s")));

        return $date;
    }

}