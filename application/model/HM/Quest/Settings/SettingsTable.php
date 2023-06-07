<?php
class HM_Quest_Settings_SettingsTable extends HM_Db_Table
{
	protected $_name    = "quest_settings";
	protected $_primary = array('quest_id', 'scope_id', 'scope_type');
	//protected $_sequence = 'S_100_1_QUEST';

    protected $_referenceMap = array(
        'Quest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ),
    );
}