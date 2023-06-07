<?php

use HM_Grid_ColumnCallback_Tc_TeacherCardLink as TeacherCardLink;

class HM_Grid_ColumnCallback_Tc_TeachersList extends HM_Grid_ColumnCallback_AbstractListOfCardLinks
{
    protected $_defaultServiceName = 'TcProviderTeacher';

    protected function _createCardLinkCallback()
    {
        return new TeacherCardLink();
    }

}