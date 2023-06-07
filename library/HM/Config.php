<?php

class HM_Config extends Zend_Config {

    public function __construct(array $array, $allowModifications = false, $sections = array())
    {
        $result = array();

        if (!is_array($sections)) $sections = array($sections);
        if (!count($sections)) return parent::__construct($array, $allowModifications);

        foreach ($sections as $section) {
            if (isset($array[$section])) {
                $result[$section] = $array[$section];
            }
        }
        return parent::__construct($result, $allowModifications);
    }
}