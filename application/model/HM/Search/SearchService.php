<?php
class HM_Search_SearchService
{
    public function runSphinxIndexer() {
        $indexerPath = Zend_Registry::get('config')->sphinx->indexer;
        $configPath = Zend_Registry::get('config')->sphinx->config;

        //"C:\Sphinx\bin\indexer" --config "C:\Sphinx\data\sphinx.conf" --all --rotate
        $exec = '';
        if ($indexerPath) {
            $exec = '"'.$indexerPath.'"';
            if ($configPath) {
                $exec .= ' --config "'.$configPath.'"';
            }
            $exec .= ' --all --rotate';
            exec($exec);
            return true;
        }

        return false;
    }

    /**
     * @param $indexes - индексы по которым происходит поиск (например resources)
     *
     * @param $queryData
     * - многомерный массив, где каждый элемент - ассоциативный массив,
     * field - одно или несколько значений(полей, по которым происходит поиск)
     * value - строка, вхождение которой мы ищем
     *
     * @param null|array $filterData
     * - массив значений, где key - это атрибут, value - ассоциативный массив:
     * value - значения
     * exclude - исключаемые занчения
     *
     * @param null|array $filterRangeData
     * - массив значений, где key - это атрибут, value - ассоциативный массив:
     * value - массив из двух значений min, max
     * exclude - исключаемые занчения
     *
     * @return bool
     *
     * TODO: добавить проверки корректности данных
     */
    public function sphinxSearch($indexes, $queryData, $filterData = null, $filterRangeData = null)
    {
        if (!is_array($indexes)) {
            $indexes = array($indexes);
        }

        $sphinx = HM_Search_Sphinx::factory();
        $sphinx->SetLimits(0,1000,1000);
        $sphinx->SetMatchMode( SPH_MATCH_EXTENDED2 );

        //настраиваем фильтры
        if ($filterData) {
            foreach ($filterData as $attribute => $data) {
                $value = $data['value'];
                $exclude = empty($data['exclude']) ? false : $data['exclude'];
                if (!is_array($value)) {
                    $value = array($value);
                }
                $sphinx->SetFilter(
                    $attribute,
                    $value,
                    $exclude
                );
            }
        }
        if ($filterRangeData) {
            foreach ($filterRangeData as $attribute => $data) {
                $value = $data['value'];
                $exclude = empty($data['exclude']) ? false : $data['exclude'];
                $sphinx->SetFilterRange(
                    $attribute,
                    $value[0],
                    $value[1],
                    $exclude
                );
            }
        }

        //формируем запрос
        $query = array();
        foreach ($queryData as $queryItem) {
            $field = $queryItem['field'];
            $value = $queryItem['value'];
            if (is_array($field)) {
                $field = '(' . implode(',', $field) . ')';
            }
            array_push($query, sprintf('@%s %s', $field, $value));
        }
        $query = implode(' ', $query);

        $result = $sphinx->Query($query, implode(',', $indexes));

        return $result;
    }



    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function extractMainField($queryData = array())
    {
        foreach ($queryData as $queryItem) {
            if ($queryItem['field'] == '*') return $queryItem['value'];
        }
        return false;
    }
}