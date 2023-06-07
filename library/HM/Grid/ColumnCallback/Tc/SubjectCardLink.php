<?php

class HM_Grid_ColumnCallback_Tc_SubjectCardLink extends HM_Grid_ColumnCallback_AbstractCardLink
{
    protected function _getCardTitle()
    {
        return _('Карточка курса');
    }

    protected function _getViewUrl($id)
    {
        return $this->_url(array(
            'baseUrl'    => '',
            'module'     => 'subject',
            'controller' => 'index',
            'action'     => 'card',
            'subject_id' => $id,
            'no_context' => 1,
            'detailed'   => 1
        ));
    }

    protected function _getCardUrl($id)
    {
        return $this->_url(array(
            'baseUrl'    => 'tc',
            'module'     => 'subject',
            'controller' => 'fulltime',
            'action'     => 'card',
            'subject_id' => $id,
        ));
    }

}