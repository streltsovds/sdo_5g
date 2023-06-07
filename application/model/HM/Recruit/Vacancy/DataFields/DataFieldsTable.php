<?php 
class HM_Recruit_Vacancy_DataFields_DataFieldsTable extends HM_Db_Table
{
    protected $_name    = "recruit_vacancies_data_fields";
    protected $_primary = array("data_field_id");

    protected $_referenceMap = array(
        'Vacancy' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns'    => 'vacancy_id', // нужно еще отфильтровать по type
            'propertyName'  => 'vacancy'
        ),
    );

    public function getDefaultOrder()
    {
        return array('recruit_vacancies_data_fields.data_field_id ASC');
    }


}