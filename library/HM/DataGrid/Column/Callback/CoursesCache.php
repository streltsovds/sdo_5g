<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_CoursesCache extends HM_DataGrid_Column_Callback_Abstract
{
    private $courseCache;

    public function callback(...$args) {
        list($dataGrid, $field) = func_get_args();

        $serviceContainer = $dataGrid->getServiceContainer();
        if ($serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(),
            HM_Responsibility_ResponsibilityModel::getResponsibilityRoles())) {
            if (!isset($this->_deanSubjectIds) || !$this->_deanSubjectIds) {
                $this->_deanSubjectIds = $serviceContainer->getService('Responsibility')->getSubjectIds($serviceContainer->getService('User')->getCurrentUserId());
            }

            $subjectIds = explode(',', $field);
            $subjectIds = array_intersect($subjectIds, empty($this->_deanSubjectIds) ? array() : $this->_deanSubjectIds);
            $field      = implode(',', $subjectIds);
        }

        if (!$this->courseCache) {
            $this->courseCache = [];
            $smtp = $dataGrid->getSelect()->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach ($res as $val) {
                $tmp[] = $val['courses'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $tmp = array_filter($tmp);
            if (count($tmp)) {
                $this->courseCache = $serviceContainer->getService('Subject')->fetchAll(array('subid IN (?)' => $tmp), 'name');
            }
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result =  array();
        if (is_a($this->courseCache, 'HM_Collection')) {
            foreach ($fields as $value) {
                if ($tempModel = $this->courseCache->exists('subid', $value)) {
                    $marker = '';
                    if ($tempModel->base_id) {
                        $marker = HM_View_Helper_Footnote::marker(1);
                        if (isset($this->view) && !is_null($this->view)) $this->view->footnote(_('Учебная сессия'), 1);
                    }
                    $result[] = "<p>{$tempModel->name}{$marker}</p>";
                }
            }
        }

        if ($result) {
            if (count($result) > 1) {
                array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount(count($result)) . '</p>');
            }
            return implode('',$result);
        } else {
            return _('Нет');
        }
    }
}