<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables2 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('Students'))
            $this->table('Students', [
                'id' => false,
                'primary_key' => ['SID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('SID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'SID',
                ])
                ->addColumn('CID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'MID',
                ])
                ->addColumn('cgid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'CID',
                ])
                ->addColumn('Registered', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'cgid',
                ])
                ->addColumn('time_registered', 'timestamp', [
                    'null' => true,
                    'after' => 'Registered',
                ])
                ->addColumn('offline_course_path', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'time_registered',
                ])
                ->addColumn('time_ended', 'timestamp', [
                    'null' => true,
                    'after' => 'offline_course_path',
                ])
                ->addColumn('time_ended_planned', 'timestamp', [
                    'null' => true,
                    'after' => 'time_ended',
                ])
                ->addColumn('newcomer_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'time_ended_planned',
                ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'newcomer_id',
                ])
                ->addColumn('application_id', 'integer', [
                    'null' => true,
                    'after' => 'reserve_id',
                ])
                ->addColumn('notified', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'application_id',
                ])
                ->addColumn('comment', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'notified',
                ])
                ->addColumn('programm_event_user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'comment',
                ])
                ->addColumn('begin_personal', 'datetime', [
                    'null' => true,
                    'after' => 'programm_event_user_id',
                ])
                ->addColumn('end_personal', 'datetime', [
                    'null' => true,
                    'after' => 'begin_personal',
                ])
                ->addIndex(['MID', 'CID'], [
                    'name' => 'MID_CID',
                    'unique' => true,
                ])
                ->addIndex(['CID'], [
                    'name' => 'CID',
                    'unique' => false,
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('Teachers'))
            $this->table('Teachers', [
                'id' => false,
                'primary_key' => ['PID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('PID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'PID',
                ])
                ->addColumn('CID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'MID',
                ])
                ->addIndex(['MID', 'CID'], [
                    'name' => 'MID_CID',
                    'unique' => true,
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->addIndex(['CID'], [
                    'name' => 'CID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('absence'))
            $this->table('absence', [
                'id' => false,
                'primary_key' => ['absence_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('absence_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'absence_id',
                ])
                ->addColumn('user_external_id', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'after' => 'user_external_id',
                ])
                ->addColumn('absence_begin', 'datetime', [
                    'null' => true,
                    'after' => 'type',
                ])
                ->addColumn('absence_end', 'datetime', [
                    'null' => true,
                    'after' => 'absence_begin',
                ])
                ->create();
        if (!$this->hasTable('admins'))
            $this->table('admins', [
                'id' => false,
                'primary_key' => ['AID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('AID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'AID',
                ])
                ->addIndex(['AID'], [
                    'name' => 'AID',
                    'unique' => true,
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => true,
                ])
                ->create();

        if (!$this->hasTable('agreements'))
            $this->table('agreements', [
                'id' => false,
                'primary_key' => ['agreement_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('agreement_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'agreement_id',
                ])
                ->addColumn('item_type', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'after' => 'item_type',
                ])
                ->addColumn('agreement_type', 'integer', [
                    'null' => true,
                    'after' => 'item_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'agreement_type',
                ])
                ->create();
        if (!$this->hasTable('at_categories'))
            $this->table('at_categories', [
                'id' => false,
                'primary_key' => ['category_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('category_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'category_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('category_id_external', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->create();
        if (!$this->hasTable('at_category_criterion_values'))
            $this->table('at_category_criterion_values', [
                'id' => false,
                'primary_key' => ['category_criterion_value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('category_criterion_value_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('category_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'category_criterion_value_id',
                ])
                ->addColumn('criterion_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'category_id',
                ])
                ->addColumn('criterion_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'criterion_type',
                ])
                ->addColumn('value_id', 'integer', [
                    'null' => true,
                    'after' => 'criterion_id',
                ])
                ->addColumn('value', 'integer', [
                    'null' => true,
                    'after' => 'value_id',
                ])
                ->addColumn('method', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'value',
                ])
                ->addIndex(['category_id'], [
                    'name' => 'category_id',
                    'unique' => false,
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

        if (!$this->hasTable('at_criteria'))
            $this->table('at_criteria', [
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
                ->addColumn('cluster_id', 'integer', [
                    'null' => false,
                    'after' => 'name',
                ])
                ->addColumn('category_id', 'integer', [
                    'null' => false,
                    'after' => 'cluster_id',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'category_id',
                ])
                ->addColumn('order', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'type',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'order',
                ])
                ->addColumn('doubt', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'status',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'doubt',
                ])
                ->addIndex(['cluster_id'], [
                    'name' => 'cluster_id',
                    'unique' => false,
                ])
                ->addIndex(['category_id'], [
                    'name' => 'category_id',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}