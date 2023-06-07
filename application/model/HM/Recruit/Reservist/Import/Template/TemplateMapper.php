<?php
class HM_Recruit_Reservist_Import_Template_TemplateMapper extends HM_Mapper_Abstract
{
    protected $_existingSnilsUnified;
    protected $_keys;

    protected function _init()
    {
        $this->_existingSnilsUnified = $this->_getExistingSnilsUnified();
        $this->_keys = array(
            'company',
            'department',
            'brigade',
            'position',
            'fio',
            'gender',
            'snils',
            'birthday',
            'age',
            'region',
            'citizenship',
            'phone',
            'phone_family',
            'email',
            'position_experience',
            'sgc_experience',
            'education',
            'retraining',
            'training',
            'qualification_result',
            'rewards',
            'violations',
            'comments_dkz_pk',
            'relocation_readiness',
            'evaluation_degree',
            'leadership',
            'productivity',
            'quality_information',
            'salary',
            'hourly_rate',
            'annual_income_rks',
            'annual_income_no_rks',
            'monthly_income_rks',
            'monthly_income_no_rks'
        );
    }

    protected function _createModel($rows)
    {
        $this->_init();
        $counter = 0;

        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        foreach ($rows as $key => $row) {
            $model = array_combine($this->_keys, $row);
            $snils = $this->_stringUnify($model['snils']);
            if (!in_array($snils, $this->_existingSnilsUnified)) {
                $models[count($models)] = $model;
                $this->_existingSnilsUnified[] = $snils;
                $counter ++;
            }
        }

        $results['models']      = $models;
        $results['rowsCount']   = $counter;

        return $results;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

    protected function _getExistingSnilsUnified()
    {
        $existingSnilsUnified = array();
        $where = array();
        $reservists = Zend_Registry::get('serviceContainer')->getService('RecruitReservist')->fetchAll($where);

        foreach ($reservists as $reservist) {
            $existingSnilsUnified[$reservist->reservist_id] = $this->_stringUnify($reservist->snils);
        }

        return $existingSnilsUnified;
    }

    private function _stringUnify($string)
    {
        $string = mb_strtolower($string);
        $string = trim(preg_replace('/[^\d]+/iu', '', $string));
        return $string;
    }

}