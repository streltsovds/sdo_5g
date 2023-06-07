<?php
class HM_At_Kpi_Unit_UnitTable extends HM_Db_Table
{
    protected $_name = "at_kpi_units";
    protected $_primary = "kpi_unit_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Kpi' => array(
            'columns'       => 'kpi_unit_id',
            'refTableClass' => 'HM_At_Kpi_KpiTable',
            'refColumns'    => 'kpi_unit_id',
            'propertyName'  => 'kpis'
        ), 
    );    
}