<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables7 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('chat_channels'))
            $this->table('chat_channels', [
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
                ->addColumn('subject_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'id',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'subject_name',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'subject_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'lesson_id',
                ])
                ->addColumn('start_date', 'datetime', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('end_date', 'datetime', [
                    'null' => true,
                    'after' => 'start_date',
                ])
                ->addColumn('show_history', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'end_date',
                ])
                ->addColumn('start_time', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'show_history',
                ])
                ->addColumn('end_time', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'start_time',
                ])
                ->addColumn('is_general', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'end_time',
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'subject_id',
                    'unique' => false,
                ])
                ->addIndex(['lesson_id'], [
                    'name' => 'lesson_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('chat_history'))
            $this->table('chat_history', [
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
                ->addColumn('channel_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'id',
                ])
                ->addColumn('sender', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'channel_id',
                ])
                ->addColumn('receiver', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'sender',
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'receiver',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'message',
                ])
                ->addIndex(['channel_id'], [
                    'name' => 'channel_id',
                    'unique' => false,
                ])
                ->addIndex(['created'], [
                    'name' => 'created',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('chat_ref_users'))
            $this->table('chat_ref_users', [
                'id' => false,
                'primary_key' => ['channel_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('channel_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'after' => 'channel_id',
                ])
                ->create();
        if (!$this->hasTable('claimants'))
            $this->table('claimants', [
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
                ->addColumn('base_subject', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'CID',
                ])
                ->addColumn('Teacher', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'base_subject',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'Teacher',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'created',
                ])
                ->addColumn('begin', 'datetime', [
                    'null' => true,
                    'after' => 'created_by',
                ])
                ->addColumn('end', 'datetime', [
                    'null' => true,
                    'after' => 'begin',
                ])
                ->addColumn('status', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'end',
                ])
                ->addColumn('type', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'status',
                ])
                ->addColumn('mid_external', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'type',
                ])
                ->addColumn('lastname', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_bin',
                    'encoding' => 'utf8mb4',
                    'after' => 'mid_external',
                ])
                ->addColumn('firstname', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_bin',
                    'encoding' => 'utf8mb4',
                    'after' => 'lastname',
                ])
                ->addColumn('patronymic', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'firstname',
                ])
                ->addColumn('comments', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'patronymic',
                ])
                ->addColumn('dublicate', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'comments',
                ])
                ->addColumn('process_id', 'integer', [
                    'null' => true,
                    'after' => 'dublicate',
                ])
                ->addIndex(['MID', 'CID'], [
                    'name' => 'MID_CID',
                    'unique' => false,
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->addIndex(['CID'], [
                    'name' => 'CID',
                    'unique' => false,
                ])
                ->addIndex(['base_subject'], [
                    'name' => 'base_subject',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('classifiers'))
            $this->table('classifiers', [
                'id' => false,
                'primary_key' => ['classifier_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('classifier_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('lft', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'classifier_id',
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
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'level',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'name',
                ])
                ->addColumn('classifier_id_external', 'integer', [
                    'null' => true,
                    'after' => 'type',
                ])
                ->addIndex(['lft'], [
                    'name' => 'lft',
                    'unique' => false,
                ])
                ->addIndex(['rgt'], [
                    'name' => 'rgt',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('classifiers_images'))
            $this->table('classifiers_images', [
                'id' => false,
                'primary_key' => ['classifier_image_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('classifier_image_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'classifier_image_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'type',
                ])
                ->create();
        if (!$this->hasTable('classifiers_links'))
            $this->table('classifiers_links', [
                'id' => false,
                'primary_key' => ['item_id', 'classifier_id', 'type'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('item_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('classifier_id', 'integer', [
                    'null' => false,
                    'after' => 'item_id',
                ])
                ->addColumn('type', 'integer', [
                    'null' => false,
                    'after' => 'classifier_id',
                ])
                ->create();
        if (!$this->hasTable('classifiers_types'))
            $this->table('classifiers_types', [
                'id' => false,
                'primary_key' => ['type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('type_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'type_id',
                ])
                ->addColumn('link_types', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('comments'))
            $this->table('comments', [
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
                ->addColumn('activity_name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'id',
                ])
                ->addColumn('subject_name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'activity_name',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'subject_name',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'subject_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'item_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'message',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addIndex(['activity_name'], [
                    'name' => 'activity_name',
                    'unique' => false,
                ])
                ->addIndex(['subject_name'], [
                    'name' => 'subject_name',
                    'unique' => false,
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'subject_id',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['item_id'], [
                    'name' => 'item_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('courses_marks'))
            $this->table('courses_marks', [
                'id' => false,
                'primary_key' => ['cid', 'mid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('cid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'signed' => false,
                ])
                ->addColumn('mid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'cid',
                ])
                ->addColumn('mark', 'string', [
                    'null' => true,
                    'default' => '-1',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'mid',
                ])
                ->addColumn('alias', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'mark',
                ])
                ->addColumn('confirmed', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'alias',
                ])
                ->addColumn('comments', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'confirmed',
                ])
                ->addColumn('certificate_validity_period', 'integer', [
                    'null' => true,
                    'after' => 'comments',
                ])
                ->addColumn('date', 'datetime', [
                    'null' => true,
                    'after' => 'certificate_validity_period',
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('crontask'))
            $this->table('crontask', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('crontask_id', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('crontask_runtime', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'crontask_id',
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}