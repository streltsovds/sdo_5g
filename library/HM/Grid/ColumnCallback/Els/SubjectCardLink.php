<?php

class HM_Grid_ColumnCallback_Els_SubjectCardLink extends HM_Grid_ColumnCallback_AbstractCardLink
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
            'subject_id' => $id
        ));
    }

}