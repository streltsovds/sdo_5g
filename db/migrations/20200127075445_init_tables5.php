<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables5 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('at_ps_function'))
            $this->table('at_ps_function', [
                'id' => false,
                'primary_key' => ['function_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('function_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('standard_id', 'integer', [
                    'null' => true,
                    'after' => 'function_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'standard_id',
                ])
                ->addIndex(['standard_id'], [
                    'name' => 'standard_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_ps_requirement'))
            $this->table('at_ps_requirement', [
                'id' => false,
                'primary_key' => ['requirement_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('requirement_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('function_id', 'integer', [
                    'null' => true,
                    'after' => 'requirement_id',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'after' => 'function_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'type',
                ])
                ->addIndex(['function_id'], [
                    'name' => 'function_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_ps_standard'))
            $this->table('at_ps_standard', [
                'id' => false,
                'primary_key' => ['standard_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('standard_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('number', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'standard_id',
                ])
                ->addColumn('code', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'number',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'code',
                ])
                ->addColumn('area', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('vid', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'area',
                ])
                ->addColumn('prikaz_number', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'vid',
                ])
                ->addColumn('prikaz_date', 'datetime', [
                    'null' => true,
                    'after' => 'prikaz_number',
                ])
                ->addColumn('minjust_number', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'prikaz_date',
                ])
                ->addColumn('minjust_date', 'datetime', [
                    'null' => true,
                    'after' => 'minjust_number',
                ])
                ->addColumn('sovet', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'minjust_date',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'sovet',
                ])
                ->create();
        if (!$this->hasTable('at_relations'))
            $this->table('at_relations', [
                'id' => false,
                'primary_key' => ['relation_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('relation_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'relation_id',
                ])
                ->addColumn('respondents', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('relation_type', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'respondents',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_session_event_attempts'))
            $this->table('at_session_event_attempts', [
                'id' => false,
                'primary_key' => ['attempt_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('attempt_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'attempt_id',
                ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('method', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'session_event_id',
                ])
                ->addColumn('date_begin', 'datetime', [
                    'null' => true,
                    'after' => 'method',
                ])
                ->addColumn('date_end', 'datetime', [
                    'null' => true,
                    'after' => 'date_begin',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['session_event_id'], [
                    'name' => 'session_event_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_session_event_lessons'))
            $this->table('at_session_event_lessons', [
                'id' => false,
                'primary_key' => ['session_event_id', 'lesson_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'session_event_id',
                ])
                ->addColumn('criteria', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'lesson_id',
                ])
                ->create();
        if (!$this->hasTable('at_session_events'))
            $this->table('at_session_events', [
                'id' => false,
                'primary_key' => ['session_event_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('session_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'session_event_id',
                ])
                ->addColumn('evaluation_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'session_id',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'evaluation_id',
                ])
                ->addColumn('criterion_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'criterion_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'criterion_type',
                ])
                ->addColumn('session_user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'position_id',
                ])
                ->addColumn('session_respondent_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'session_user_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'session_respondent_id',
                ])
                ->addColumn('respondent_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addColumn('programm_event_user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'respondent_id',
                ])
                ->addColumn('quest_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'programm_event_user_id',
                ])
                ->addColumn('method', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'quest_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'method',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'description',
                ])
                ->addColumn('date_begin', 'datetime', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('date_end', 'datetime', [
                    'null' => true,
                    'after' => 'date_begin',
                ])
                ->addColumn('date_filled', 'datetime', [
                    'null' => true,
                    'after' => 'date_end',
                ])
                ->addColumn('is_empty_quest', 'integer', [
                    'null' => true,
                    'after' => 'date_filled',
                ])
                ->addIndex(['session_id'], [
                    'name' => 'session_id',
                    'unique' => false,
                ])
                ->addIndex(['evaluation_id'], [
                    'name' => 'evaluation_id',
                    'unique' => false,
                ])
                ->addIndex(['criterion_id'], [
                    'name' => 'criterion_id',
                    'unique' => false,
                ])
                ->addIndex(['position_id'], [
                    'name' => 'position_id',
                    'unique' => false,
                ])
                ->addIndex(['session_user_id'], [
                    'name' => 'session_user_id',
                    'unique' => false,
                ])
                ->addIndex(['session_respondent_id'], [
                    'name' => 'session_respondent_id',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['respondent_id'], [
                    'name' => 'respondent_id',
                    'unique' => false,
                ])
                ->addIndex(['programm_event_user_id'], [
                    'name' => 'programm_event_user_id',
                    'unique' => false,
                ])
                ->addIndex(['quest_id'], [
                    'name' => 'quest_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_session_pair_ratings'))
            $this->table('at_session_pair_ratings', [
                'id' => false,
                'primary_key' => ['session_id', 'criterion_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('session_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'session_id',
                ])
                ->addColumn('session_user_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'criterion_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'session_user_id',
                ])
                ->addColumn('rating', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addColumn('ratio', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'rating',
                ])
                ->addColumn('parent_soid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'ratio',
                ])
                ->addIndex(['session_user_id'], [
                    'name' => 'session_user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_session_pair_results'))
            $this->table('at_session_pair_results', [
                'id' => false,
                'primary_key' => ['session_pair_id', 'criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('session_pair_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'session_pair_id',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'session_event_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'criterion_id',
                ])
                ->addColumn('parent_soid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addIndex(['session_event_id'], [
                    'name' => 'session_event_id',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_session_pairs'))
            $this->table('at_session_pairs', [
                'id' => false,
                'primary_key' => ['session_pair_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('session_pair_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('session_event_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'session_pair_id',
                ])
                ->addColumn('first_user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'session_event_id',
                ])
                ->addColumn('second_user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'first_user_id',
                ])
                ->addIndex(['session_event_id'], [
                    'name' => 'session_event_id',
                    'unique' => false,
                ])
                ->addIndex(['first_user_id'], [
                    'name' => 'first_user_id',
                    'unique' => false,
                ])
                ->addIndex(['second_user_id'], [
                    'name' => 'second_user_id',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}