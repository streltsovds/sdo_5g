<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables6 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('at_session_respondents'))
            $this->table('at_session_respondents', [
                'id' => false,
                'primary_key' => ['session_respondent_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('session_respondent_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('session_id', 'integer', [
                    'null' => true,
                    'after' => 'session_respondent_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'session_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'position_id',
                ])
                ->addColumn('progress', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addIndex(['session_id'], [
                    'name' => 'session_id',
                    'unique' => false,
                ])
                ->addIndex(['position_id'], [
                    'name' => 'position_id',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_session_user_criterion_values'))
            $this->table('at_session_user_criterion_values', [
                'id' => false,
                'primary_key' => ['session_user_id', 'criterion_id', 'criterion_type'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('session_user_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'after' => 'session_user_id',
                ])
                ->addColumn('criterion_type', 'integer', [
                    'null' => false,
                    'default' => '1',
                    'after' => 'criterion_id',
                ])
                ->addColumn('value', 'float', [
                    'null' => true,
                    'after' => 'criterion_type',
                ])
                ->create();
        if (!$this->hasTable('at_session_users'))
            $this->table('at_session_users', [
                'id' => false,
                'primary_key' => ['session_user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('session_user_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('session_id', 'integer', [
                    'null' => true,
                    'after' => 'session_user_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'session_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'position_id',
                ])
                ->addColumn('profile_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('process_id', 'integer', [
                    'null' => true,
                    'after' => 'profile_id',
                ])
                ->addColumn('vacancy_candidate_id', 'integer', [
                    'null' => true,
                    'after' => 'process_id',
                ])
                ->addColumn('newcomer_id', 'integer', [
                    'null' => true,
                    'after' => 'vacancy_candidate_id',
                ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => true,
                    'after' => 'newcomer_id',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'reserve_id',
                ])
                ->addColumn('total_competence', 'float', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('total_kpi', 'float', [
                    'null' => true,
                    'after' => 'total_competence',
                ])
                ->addColumn('result_category', 'integer', [
                    'null' => true,
                    'after' => 'total_kpi',
                ])
                ->addIndex(['session_id'], [
                    'name' => 'session_id',
                    'unique' => false,
                ])
                ->addIndex(['position_id'], [
                    'name' => 'position_id',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['profile_id'], [
                    'name' => 'profile_id',
                    'unique' => false,
                ])
                ->addIndex(['process_id'], [
                    'name' => 'process_id',
                    'unique' => false,
                ])
                ->addIndex(['vacancy_candidate_id'], [
                    'name' => 'vacancy_candidate_id',
                    'unique' => false,
                ])
                ->addIndex(['newcomer_id'], [
                    'name' => 'newcomer_id',
                    'unique' => false,
                ])
                ->addIndex(['reserve_id'], [
                    'name' => 'reserve_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_sessions'))
            $this->table('at_sessions', [
                'id' => false,
                'primary_key' => ['session_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('session_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('programm_type', 'integer', [
                    'null' => true,
                    'after' => 'session_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'programm_type',
                ])
                ->addColumn('shortname', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'shortname',
                ])
                ->addColumn('report_comment', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->addColumn('cycle_id', 'integer', [
                    'null' => true,
                    'after' => 'report_comment',
                ])
                ->addColumn('begin_date', 'datetime', [
                    'null' => true,
                    'after' => 'cycle_id',
                ])
                ->addColumn('end_date', 'datetime', [
                    'null' => true,
                    'after' => 'begin_date',
                ])
                ->addColumn('initiator_id', 'integer', [
                    'null' => true,
                    'after' => 'end_date',
                ])
                ->addColumn('checked_soids', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'initiator_id',
                ])
                ->addColumn('state', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'checked_soids',
                ])
                ->addColumn('base_color', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'state',
                ])
                ->addColumn('goal', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'base_color',
                ])
                ->addIndex(['cycle_id'], [
                    'name' => 'cycle_id',
                    'unique' => false,
                ])
                ->addIndex(['initiator_id'], [
                    'name' => 'initiator_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('at_user_kpi_results'))
            $this->table('at_user_kpi_results', [
                'id' => false,
                'primary_key' => ['user_kpi_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('user_kpi_result_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_kpi_id', 'integer', [
                    'null' => true,
                    'after' => 'user_kpi_result_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'user_kpi_id',
                ])
                ->addColumn('respondent_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('relation_type', 'integer', [
                    'null' => true,
                    'after' => 'respondent_id',
                ])
                ->addColumn('value_fact', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'relation_type',
                ])
                ->addColumn('comments', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'value_fact',
                ])
                ->addColumn('change_date', 'datetime', [
                    'null' => true,
                    'after' => 'comments',
                ])
                ->addIndex(['user_kpi_id'], [
                    'name' => 'user_kpi_id',
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
                ->create();
        if (!$this->hasTable('at_user_kpis'))
            $this->table('at_user_kpis', [
                'id' => false,
                'primary_key' => ['user_kpi_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('user_kpi_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'user_kpi_id',
                ])
                ->addColumn('cycle_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('kpi_id', 'integer', [
                    'null' => true,
                    'after' => 'cycle_id',
                ])
                ->addColumn('weight', 'float', [
                    'null' => true,
                    'after' => 'kpi_id',
                ])
                ->addColumn('value_plan', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'weight',
                ])
                ->addColumn('value_fact', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'value_plan',
                ])
                ->addColumn('comments', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'value_fact',
                ])
                ->addColumn('begin_date', 'datetime', [
                    'null' => true,
                    'after' => 'comments',
                ])
                ->addColumn('end_date', 'datetime', [
                    'null' => true,
                    'after' => 'begin_date',
                ])
                ->addColumn('value_type', 'integer', [
                    'null' => true,
                    'after' => 'end_date',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['cycle_id'], [
                    'name' => 'cycle_id',
                    'unique' => false,
                ])
                ->addIndex(['kpi_id'], [
                    'name' => 'kpi_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('blog'))
            $this->table('blog', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'id',
                ])
                ->addColumn('body', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'body',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'created',
                ])
                ->addColumn('subject_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created_by',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'subject_name',
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'subject_id',
                    'unique' => false,
                ])
                ->addIndex(['created_by'], [
                    'name' => 'created_by',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('captcha'))
            $this->table('captcha', [
                'id' => false,
                'primary_key' => ['login'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('login', 'string', [
                    'null' => false,
                    'limit' => 249,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('attempts', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'login',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'attempts',
                ])
                ->create();
        if (!$this->hasTable('certificates'))
            $this->table('certificates', [
                'id' => false,
                'primary_key' => ['certificate_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('certificate_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'certificate_id',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'subject_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('organization', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->addColumn('startdate', 'datetime', [
                    'null' => true,
                    'after' => 'organization',
                ])
                ->addColumn('enddate', 'datetime', [
                    'null' => true,
                    'after' => 'startdate',
                ])
                ->addColumn('filename', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'enddate',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'filename',
                ])
                ->addColumn('number', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'type',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'USERID',
                    'unique' => false,
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'SUBJECTID',
                    'unique' => false,
                ])
                ->addIndex(['user_id', 'subject_id'], [
                    'name' => 'USER_SUBJECT',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}