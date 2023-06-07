<?php

class HM_Grid_ColumnCallback_Els_SubjectsList extends HM_Grid_ColumnCallback_AbstractListOfLinks
{
    protected $_defaultServiceName = 'Subject';

    protected function _getUrl($id, $item)
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