<?php
class HM_Tc_Provider_Subject_SubjectTable extends HM_Db_Table
{
	protected $_name = "tc_providers_subjects";
	protected $_primary = "provider_subject_id";

    protected $_referenceMap = array(
        'Subject' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'ScProvider' => array(
            'columns' => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_ProviderTable',
            'refColumns' => 'provider_id',
            'propertyName' => 'scProvider'
        ),
    );
}