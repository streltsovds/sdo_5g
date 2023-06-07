<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables3 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('at_criteria_clusters'))
            $this->table('at_criteria_clusters', [
                'id' => false,
                'primary_key' => ['cluster_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('cluster_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cluster_id',
                ])
                ->addColumn('order', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('at_criteria_indicator_scale_values'))
            $this->table('at_criteria_indicator_scale_values', [
                'id' => false,
                'primary_key' => ['criterion_indicator_value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('criterion_indicator_value_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('indicator_id', 'integer', [
                    'null' => true,
                    'after' => 'criterion_indicator_value_id',
                ])
                ->addColumn('value_id', 'integer', [
                    'null' => true,
                    'after' => 'indicator_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'value_id',
                ])
                ->addColumn('description_questionnaire', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->addIndex(['indicator_id'], [
                    'name' => 'indicator_id',
                    'unique' => false,
                ])
                ->addIndex(['value_id'], [
                    'name' => 'value_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_criteria_indicators'))
            $this->table('at_criteria_indicators', [
                'id' => false,
                'primary_key' => ['indicator_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('indicator_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => true,
                    'after' => 'indicator_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'criterion_id',
                ])
                ->addColumn('name_questionnaire', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('description_positive', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name_questionnaire',
                ])
                ->addColumn('description_negative', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description_positive',
                ])
                ->addColumn('reverse', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'description_negative',
                ])
                ->addColumn('order', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'reverse',
                ])
                ->addColumn('doubt', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'order',
                ])
                ->addIndex(['criterion_id'], [
                    'name' => 'criterion_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_criteria_kpi'))
            $this->table('at_criteria_kpi', [
                'id' => false,
                'primary_key' => ['criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'criterion_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('order', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'description',
                ])
                ->create();
        if (!$this->hasTable('at_criteria_personal'))
            $this->table('at_criteria_personal', [
                'id' => false,
                'primary_key' => ['criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'criterion_id',
                ])
                ->addColumn('quest_id', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'quest_id',
                ])
                ->addIndex(['quest_id'], [
                    'name' => 'quest_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_criteria_scale_values'))
            $this->table('at_criteria_scale_values', [
                'id' => false,
                'primary_key' => ['criterion_value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('criterion_value_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => true,
                    'after' => 'criterion_value_id',
                ])
                ->addColumn('value_id', 'integer', [
                    'null' => true,
                    'after' => 'criterion_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'value_id',
                ])
                ->addIndex(['criterion_id'], [
                    'name' => 'criterion_id',
                    'unique' => false,
                ])
                ->addIndex(['value_id'], [
                    'name' => 'value_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_criteria_test'))
            $this->table('at_criteria_test', [
                'id' => false,
                'primary_key' => ['criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('lft', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'criterion_id',
                ])
                ->addColumn('rgt', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lft',
                ])
                ->addColumn('level', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'rgt',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'level',
                ])
                ->addColumn('quest_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'name',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'quest_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'subject_id',
                ])
                ->addColumn('required', 'integer', [
                    'null' => true,
                    'after' => 'description',
                ])
                ->addColumn('validity', 'integer', [
                    'null' => true,
                    'after' => 'required',
                ])
                ->addColumn('employee_type', 'integer', [
                    'null' => true,
                    'after' => 'validity',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'employee_type',
                ])
                ->addIndex(['quest_id'], [
                    'name' => 'quest_id',
                    'unique' => false,
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'subject_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_evaluation_criteria'))
            $this->table('at_evaluation_criteria', [
                'id' => false,
                'primary_key' => ['evaluation_type_id', 'criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('evaluation_type_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'after' => 'evaluation_type_id',
                ])
                ->addColumn('quest_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'criterion_id',
                ])
                ->addIndex(['quest_id'], [
                    'name' => 'quest_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_evaluation_memo_results'))
            $this->table('at_evaluation_memo_results', [
                'id' => false,
                'primary_key' => ['evaluation_memo_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('evaluation_memo_result_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('evaluation_memo_id', 'integer', [
                    'null' => true,
                    'after' => 'evaluation_memo_result_id',
                ])
                ->addColumn('value', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'evaluation_memo_id',
                ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => true,
                    'after' => 'value',
                ])
                ->addIndex(['evaluation_memo_id'], [
                    'name' => 'evaluation_memo_id',
                    'unique' => false,
                ])
                ->addIndex(['session_event_id'], [
                    'name' => 'session_event_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_evaluation_memos'))
            $this->table('at_evaluation_memos', [
                'id' => false,
                'primary_key' => ['evaluation_memo_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('evaluation_memo_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('evaluation_type_id', 'integer', [
                    'null' => true,
                    'after' => 'evaluation_memo_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'evaluation_type_id',
                ])
                ->addIndex(['evaluation_type_id'], [
                    'name' => 'evaluation_type_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_evaluation_results'))
            $this->table('at_evaluation_results', [
                'id' => false,
                'primary_key' => ['result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('result_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => true,
                    'after' => 'result_id',
                ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => true,
                    'after' => 'criterion_id',
                ])
                ->addColumn('session_user_id', 'integer', [
                    'null' => true,
                    'after' => 'session_event_id',
                ])
                ->addColumn('relation_type', 'integer', [
                    'null' => true,
                    'after' => 'session_user_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'relation_type',
                ])
                ->addColumn('value_id', 'integer', [
                    'null' => true,
                    'after' => 'position_id',
                ])
                ->addColumn('value_weight', 'float', [
                    'null' => true,
                    'after' => 'value_id',
                ])
                ->addColumn('indicators_status', 'integer', [
                    'null' => true,
                    'after' => 'value_weight',
                ])
                ->addColumn('custom_criterion_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'indicators_status',
                ])
                ->addColumn('custom_criterion_parent_id', 'integer', [
                    'null' => true,
                    'after' => 'custom_criterion_name',
                ])
                ->addIndex(['criterion_id'], [
                    'name' => 'criterion_id',
                    'unique' => false,
                ])
                ->addIndex(['session_event_id'], [
                    'name' => 'session_event_id',
                    'unique' => false,
                ])
                ->addIndex(['session_user_id'], [
                    'name' => 'session_user_id',
                    'unique' => false,
                ])
                ->addIndex(['position_id'], [
                    'name' => 'position_id',
                    'unique' => false,
                ])
                ->addIndex(['value_id'], [
                    'name' => 'value_id',
                    'unique' => false,
                ])
                ->addIndex(['custom_criterion_parent_id'], [
                    'name' => 'custom_criterion_parent_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_evaluation_results_indicators'))
            $this->table('at_evaluation_results_indicators', [
                'id' => false,
                'primary_key' => ['indicator_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('indicator_result_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('indicator_id', 'integer', [
                    'null' => true,
                    'after' => 'indicator_result_id',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => true,
                    'after' => 'indicator_id',
                ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => true,
                    'after' => 'criterion_id',
                ])
                ->addColumn('session_user_id', 'integer', [
                    'null' => true,
                    'after' => 'session_event_id',
                ])
                ->addColumn('relation_type', 'integer', [
                    'null' => true,
                    'after' => 'session_user_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'relation_type',
                ])
                ->addColumn('value_id', 'integer', [
                    'null' => true,
                    'after' => 'position_id',
                ])
                ->addIndex(['indicator_id'], [
                    'name' => 'indicator_id',
                    'unique' => false,
                ])
                ->addIndex(['criterion_id'], [
                    'name' => 'criterion_id',
                    'unique' => false,
                ])
                ->addIndex(['session_event_id'], [
                    'name' => 'session_event_id',
                    'unique' => false,
                ])
                ->addIndex(['session_user_id'], [
                    'name' => 'session_user_id',
                    'unique' => false,
                ])
                ->addIndex(['position_id'], [
                    'name' => 'position_id',
                    'unique' => false,
                ])
                ->addIndex(['value_id'], [
                    'name' => 'value_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_evaluation_type'))
            $this->table('at_evaluation_type', [
                'id' => false,
                'primary_key' => ['evaluation_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('evaluation_type_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'evaluation_type_id',
                ])
                ->addColumn('comment', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('scale_id', 'integer', [
                    'null' => true,
                    'after' => 'comment',
                ])
                ->addColumn('category_id', 'integer', [
                    'null' => false,
                    'after' => 'scale_id',
                ])
                ->addColumn('profile_id', 'integer', [
                    'null' => true,
                    'after' => 'category_id',
                ])
                ->addColumn('vacancy_id', 'integer', [
                    'null' => true,
                    'after' => 'profile_id',
                ])
                ->addColumn('newcomer_id', 'integer', [
                    'null' => true,
                    'after' => 'vacancy_id',
                ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => true,
                    'after' => 'newcomer_id',
                ])
                ->addColumn('method', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'reserve_id',
                ])
                ->addColumn('submethod', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'method',
                ])
                ->addColumn('methodData', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'submethod',
                ])
                ->addColumn('relation_type', 'integer', [
                    'null' => true,
                    'after' => 'methodData',
                ])
                ->addColumn('programm_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'relation_type',
                ])
                ->addIndex(['scale_id'], [
                    'name' => 'scale_id',
                    'unique' => false,
                ])
                ->addIndex(['category_id'], [
                    'name' => 'category_id',
                    'unique' => false,
                ])
                ->addIndex(['profile_id'], [
                    'name' => 'profile_id',
                    'unique' => false,
                ])
                ->addIndex(['vacancy_id'], [
                    'name' => 'vacancy_id',
                    'unique' => false,
                ])
                ->addIndex(['newcomer_id'], [
                    'name' => 'newcomer_id',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}