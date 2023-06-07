<?php
class HM_Tc_Prefix_PrefixService extends HM_Service_Abstract
{

    public function setPrefix($prefix, $type = HM_Tc_Prefix_PrefixModel::TYPE_SUBJECT)
    {
        $prefix = $prefix[0]; // ибо фцбк передает массив, но нам нужен ток 1 элемент
        $prefixObj = is_numeric($prefix) ? $this->getOne($this->find($prefix)) : NULL;
        if(!$prefixObj) {
            $prefixObj = $this->insert(array(
                'name' => $prefix,
                'prefix_type' => $type
            ));
        }

        return $prefixObj->prefix_id;
    }

    public function setDefaultPrefix($prefixId)
    {
        $return = array();
        if (!empty($prefixId)) {
            $prefix = $this->getOne($this->find($prefixId));
            if ($prefix) {
                $return[$prefix->prefix_id] = sprintf('%s', $prefix->name);
            }
        }
        return $return;
    }

    public function getGroupNumber($prefixId, $scId)
    {
        $prefix = $this->getOne($this->find($prefixId));
        $scPrefix = $this->getOne($this->find($scId));
        $scPrefixName = $scPrefix ? "{$scPrefix->name}-" : '';
        return "{$scPrefixName}{$prefix->name}-{$prefix->counter}";
    }

    public function updateCounter($prefix)
    {
        $prefixObj = $this->getOne($this->find($prefix));
        $prefixObj->counter += 1;
        return $this->update($prefixObj->getValues());
    }


    // максимум  1 prefix у курса
    public function convertToString($prefixesIds, $type = HM_Tc_Prefix_PrefixModel::TYPE_SUBJECT)
    {
        $return = array();
        if (!is_array($prefixesIds)) {
            $prefixesIds = array($prefixesIds);
        }
        foreach($prefixesIds as $prefixId) {
            if (!self::isNewPrefix($prefixId)) {
                $prefix = $this->fetchAll($this->quoteInto(
                    array('prefix_id = ? ', ' AND prefix_type = ?'),
                    array($prefixId, $type)
                ))->current();
                $return[$prefix->prefix_id] = $prefix->name;
            } else {
                $return[] = $prefixId;
            }
        }
        return $return;
    }

    static public function isNewPrefix($prefix)
    {
        return !preg_match("/^([0-9])+$/",$prefix); // если не новый - то здес ь целочисленный id
    }
}