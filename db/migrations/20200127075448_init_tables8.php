<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables8 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('curators'))
            $this->table('curators', [
                'id' => false,
                'primary_key' => ['curator_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('curator_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'curator_id',
                ])
                ->addColumn('project_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'MID',
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('curators_options'))
            $this->table('curators_options', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('unlimited_projects', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'user_id',
                ])
                ->addColumn('unlimited_classifiers', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'unlimited_projects',
                ])
                ->addColumn('assign_new_projects', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'unlimited_classifiers',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['unlimited_projects'], [
                    'name' => 'unlimited_projects',
                    'unique' => false,
                ])
                ->addIndex(['unlimited_classifiers'], [
                    'name' => 'unlimited_classifiers',
                    'unique' => false,
                ])
                ->addIndex(['assign_new_projects'], [
                    'name' => 'assign_new_projects',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('curators_responsibilities'))
            $this->table('curators_responsibilities', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('classifier_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->create();
        if (!$this->hasTable('cycles'))
            $this->table('cycles', [
                'id' => false,
                'primary_key' => ['cycle_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('cycle_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cycle_id',
                ])
                ->addColumn('begin_date', 'datetime', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('end_date', 'datetime', [
                    'null' => true,
                    'after' => 'begin_date',
                ])
                ->addColumn('newcomer_id', 'integer', [
                    'null' => true,
                    'after' => 'end_date',
                ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => true,
                    'after' => 'newcomer_id',
                ])
                ->addColumn('type', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'reserve_id',
                ])
                ->addColumn('year', 'integer', [
                    'null' => true,
                    'after' => 'type',
                ])
                ->addColumn('quarter', 'integer', [
                    'null' => true,
                    'after' => 'year',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'quarter',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->create();
        if (!$this->hasTable('dean_poll_users'))
            $this->table('dean_poll_users', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                ])
                ->addColumn('head_mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lesson_id',
                ])
                ->addColumn('student_mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'head_mid',
                ])
                ->addIndex(['lesson_id'], [
                    'name' => 'lesson_id',
                    'unique' => false,
                ])
                ->addIndex(['head_mid'], [
                    'name' => 'head_mid',
                    'unique' => false,
                ])
                ->addIndex(['student_mid'], [
                    'name' => 'student_mid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('deans'))
            $this->table('deans', [
                'id' => false,
                'primary_key' => ['DID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('DID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'DID',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'MID',
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('deans_options'))
            $this->table('deans_options', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('unlimited_subjects', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'user_id',
                ])
                ->addColumn('unlimited_classifiers', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'unlimited_subjects',
                ])
                ->addColumn('assign_new_subjects', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'unlimited_classifiers',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['unlimited_subjects'], [
                    'name' => 'unlimited_subjects',
                    'unique' => false,
                ])
                ->addIndex(['unlimited_classifiers'], [
                    'name' => 'unlimited_classifiers',
                    'unique' => false,
                ])
                ->addIndex(['assign_new_subjects'], [
                    'name' => 'assign_new_subjects',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('deans_responsibilities'))
            $this->table('deans_responsibilities', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('classifier_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->create();
        if (!$this->hasTable('deputy_assign'))
            $this->table('deputy_assign', [
                'id' => false,
                'primary_key' => ['assign_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('assign_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'assign_id',
                ])
                ->addColumn('deputy_user_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('begin_date', 'datetime', [
                    'null' => true,
                    'after' => 'deputy_user_id',
                ])
                ->addColumn('end_date', 'datetime', [
                    'null' => true,
                    'after' => 'begin_date',
                ])
                ->addColumn('not_active', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'end_date',
                ])
                ->create();
        if (!$this->hasTable('developers'))
            $this->table('developers', [
                'id' => false,
                'primary_key' => ['mid', 'cid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('mid', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'mid',
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('eclass'))
            $this->table('eclass', [
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
                    'identity' => 'enable',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'after' => 'id',
                ])
                ->addColumn('synced', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lesson_id',
                ])
                ->addColumn('sync_date', 'datetime', [
                    'null' => true,
                    'after' => 'synced',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'sync_date',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'after' => 'title',
                ])
                ->create();
        if (!$this->hasTable('employee'))
            $this->table('employee', [
                'id' => false,
                'primary_key' => ['user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                ])
                ->create();
        if (!$this->hasTable('es_event_group_types'))
            $this->table('es_event_group_types', [
                'id' => false,
                'primary_key' => ['event_group_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('event_group_type_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'event_group_type_id',
                ])
                ->create();
        if (!$this->hasTable('es_event_groups'))
            $this->table('es_event_groups', [
                'id' => false,
                'primary_key' => ['event_group_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('event_group_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('trigger_instance_id', 'integer', [
                    'null' => true,
                    'after' => 'event_group_id',
                ])
                ->addColumn('type', 'string', [
                    'null' => true,
                    'limit' => 230,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'trigger_instance_id',
                ])
                ->addColumn('data', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'type',
                ])
                ->addIndex(['trigger_instance_id', 'type'], [
                    'name' => 'group_name',
                    'unique' => true,
                ])
                ->create();
        if (!$this->hasTable('es_event_types'))
            $this->table('es_event_types', [
                'id' => false,
                'primary_key' => ['event_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('event_type_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'event_type_id',
                ])
                ->addColumn('event_group_type_id', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('es_event_users'))
            $this->table('es_event_users', [
                'id' => false,
                'primary_key' => ['event_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('event_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                    'after' => 'event_id',
                ])
                ->addColumn('views', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->create();
        if (!$this->hasTable('es_events'))
            $this->table('es_events', [
                'id' => false,
                'primary_key' => ['event_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('event_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('event_type_id', 'integer', [
                    'null' => true,
                    'after' => 'event_id',
                ])
                ->addColumn('event_trigger_id', 'integer', [
                    'null' => true,
                    'after' => 'event_type_id',
                ])
                ->addColumn('event_group_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'event_trigger_id',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'event_group_id',
                ])
                ->addColumn('create_time', 'biginteger', [
                    'null' => true,
                    'after' => 'description',
                ])
                ->addIndex(['event_type_id'], [
                    'name' => 'event_type_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('es_notify_types'))
            $this->table('es_notify_types', [
                'id' => false,
                'primary_key' => ['notify_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('notify_type_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'notify_type_id',
                ])
                ->create();
        if (!$this->hasTable('es_user_notifies'))
            $this->table('es_user_notifies', [
                'id' => false,
                'primary_key' => ['user_id', 'notify_type_id', 'event_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('notify_type_id', 'integer', [
                    'null' => false,
                    'after' => 'user_id',
                ])
                ->addColumn('event_type_id', 'integer', [
                    'null' => false,
                    'after' => 'notify_type_id',
                ])
                ->addColumn('is_active', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'event_type_id',
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}