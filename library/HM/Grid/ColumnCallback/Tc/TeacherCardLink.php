<?php

class HM_Grid_ColumnCallback_Tc_TeacherCardLink extends HM_Grid_ColumnCallback_AbstractCardLink
{
    protected $_subjectId = 0;
    protected $_providerId = 0;

    public function setSubjectId($subjectId)
    {
        $this->_subjectId = $subjectId;
    }

    public function setProviderId($providerId)
    {
        $this->_providerId = $providerId;
    }

    protected function _getCardTitle()
    {
        return _('Карточка тьютора');
    }

    protected function _getViewUrl($id)
    {
        $urlParams = array(
            'baseUrl'    => 'tc',
            'module'     => 'teacher',
            'controller' => 'view',
            'action'     => 'card',
            'teacher_id' => $id
        );

        if ($this->_subjectId) {
            $urlParams['subject_id'] = $this->_subjectId;
        } elseif ($this->_providerId) {
            $urlParams['provider_id'] = $this->_providerId;
        }

        return $this->_url($urlParams);
    }
}