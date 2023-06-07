<?php

class HM_Grid_ColumnCallback_Tc_SubjectsList extends HM_Grid_ColumnCallback_Els_SubjectsList
{
    protected function _getUrl($id, $item)
    {
        return $this->_url(array(
            'baseUrl'    => 'tc',
            'module'     => 'subject',
            'controller' => 'fulltime',
            'action'     => 'view',
            'subject_id' => $id
        ));
    }
}