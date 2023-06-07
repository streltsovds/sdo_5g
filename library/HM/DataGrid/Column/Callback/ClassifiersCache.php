<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_ClassifiersCache extends HM_DataGrid_Column_Callback_Abstract
{
    private $classifierCache;

    public function callback(...$args) {
        list($dataGrid, $field) = func_get_args();
        $serviceContainer = $dataGrid->getServiceContainer();

        if (empty($this->classifierCache)) {
            $smtp = $dataGrid->getSelect()->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach ($res as $val) {
                $tmp[] = $val['classifiers'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->classifierCache = $serviceContainer->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $tmp));
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result = (is_array($fields) && (count($fields) > 1)) ?
            ['<p class="total">' . $serviceContainer->getService('Classifier')->pluralFormCount(count($fields)) . '</p>'] :
            [];
        foreach ($fields as $value) {
            if (!$this->classifierCache) continue;
            $tempModel = $this->classifierCache->exists('classifier_id', $value);
            $result[] = "<p>{$tempModel->name}</p>";
        }

        if ($result) return implode('',$result);
        else return _('Нет');
    }
}