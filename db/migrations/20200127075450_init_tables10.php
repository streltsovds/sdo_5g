<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables10 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('forums_messages_showed'))
            $this->table('forums_messages_showed', [
                'id' => false,
                'primary_key' => ['user_id', 'message_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                ])
                ->addColumn('message_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'after' => 'user_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'message_id',
                ])
                ->create();
        if (!$this->hasTable('forums_sections'))
            $this->table('forums_sections', [
                'id' => false,
                'primary_key' => ['section_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('section_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'section_id',
                ])
                ->addColumn('subject', 'string', [
                    'null' => true,
                    'default' => 'subject',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'lesson_id',
                ])
                ->addColumn('forum_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'subject',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'forum_id',
                ])
                ->addColumn('user_name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('user_ip', 'string', [
                    'null' => true,
                    'default' => '127.0.0.1',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_name',
                ])
                ->addColumn('parent_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'user_ip',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'parent_id',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'text',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('last_msg', 'datetime', [
                    'null' => true,
                    'after' => 'updated',
                ])
                ->addColumn('count_msg', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'last_msg',
                ])
                ->addColumn('order', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'count_msg',
                ])
                ->addColumn('flags', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'order',
                ])
                ->addColumn('is_hidden', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'flags',
                ])
                ->addColumn('deleted_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'is_hidden',
                ])
                ->addColumn('deleted', 'datetime', [
                    'null' => true,
                    'after' => 'deleted_by',
                ])
                ->addColumn('edited_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'deleted',
                ])
                ->addColumn('edited', 'datetime', [
                    'null' => true,
                    'after' => 'edited_by',
                ])
                ->create();
        if (!$this->hasTable('graduated'))
            $this->table('graduated', [
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
                ->addColumn('begin', 'datetime', [
                    'null' => true,
                    'after' => 'CID',
                ])
                ->addColumn('end', 'datetime', [
                    'null' => true,
                    'after' => 'begin',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'end',
                ])
                ->addColumn('certificate_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'created',
                ])
                ->addColumn('score', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'certificate_id',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'score',
                ])
                ->addColumn('progress', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'status',
                ])
                ->addColumn('is_lookable', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'progress',
                ])
                ->addColumn('effectivity', 'float', [
                    'null' => true,
                    'after' => 'is_lookable',
                ])
                ->addColumn('application_id', 'integer', [
                    'null' => true,
                    'after' => 'effectivity',
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->addIndex(['CID'], [
                    'name' => 'CID',
                    'unique' => false,
                ])
                ->addIndex(['MID', 'CID'], [
                    'name' => 'MID_CID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('groupname'))
            $this->table('groupname', [
                'id' => false,
                'primary_key' => ['gid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('gid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'gid',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cid',
                ])
                ->addColumn('owner_gid', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('groupuser'))
            $this->table('groupuser', [
                'id' => false,
                'primary_key' => ['mid', 'gid'],
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
                    'null' => true,
                    'default' => '0',
                    'after' => 'mid',
                ])
                ->addColumn('gid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'cid',
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->addIndex(['gid'], [
                    'name' => 'gid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('hacp_debug'))
            $this->table('hacp_debug', [
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
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'id',
                ])
                ->addColumn('date', 'datetime', [
                    'null' => true,
                    'after' => 'message',
                ])
                ->addColumn('direction', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'date',
                ])
                ->create();
        if (!$this->hasTable('help'))
            $this->table('help', [
                'id' => false,
                'primary_key' => ['help_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('help_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('role', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'help_id',
                ])
                ->addColumn('module', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'role',
                ])
                ->addColumn('app_module', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'module',
                ])
                ->addColumn('controller', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'app_module',
                ])
                ->addColumn('action', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'controller',
                ])
                ->addColumn('link_subject', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'action',
                ])
                ->addColumn('is_active_version', 'integer', [
                    'null' => true,
                    'after' => 'link_subject',
                ])
                ->addColumn('link', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'is_active_version',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'link',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('moderated', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'text',
                ])
                ->addColumn('lang', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'moderated',
                ])
                ->create();
        if (!$this->hasTable('hold_mail'))
            $this->table('hold_mail', [
                'id' => false,
                'primary_key' => ['hold_mail_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('hold_mail_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('receiver_MID', 'integer', [
                    'null' => false,
                    'after' => 'hold_mail_id',
                ])
                ->addColumn('serialized_message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'receiver_MID',
                ])
                ->addIndex(['receiver_MID'], [
                    'name' => 'receiver_MID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('holidays'))
            $this->table('holidays', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('id', 'biginteger', [
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
                ->addColumn('date', 'datetime', [
                    'null' => true,
                    'after' => 'title',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'date',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'type',
                ])
                ->create();
        if (!$this->hasTable('hr_reserve_files'))
            $this->table('hr_reserve_files', [
                'id' => false,
                'primary_key' => ['reserve_file_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('reserve_file_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => true,
                    'after' => 'reserve_file_id',
                ])
                ->addColumn('file_id', 'integer', [
                    'null' => true,
                    'after' => 'reserve_id',
                ])
                ->addColumn('state_type', 'integer', [
                    'null' => true,
                    'after' => 'file_id',
                ])
                ->addIndex(['reserve_id'], [
                    'name' => 'reserve_id',
                    'unique' => false,
                ])
                ->addIndex(['file_id'], [
                    'name' => 'file_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('hr_reserve_positions'))
            $this->table('hr_reserve_positions', [
                'id' => false,
                'primary_key' => ['reserve_position_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('reserve_position_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'reserve_position_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('profile_id', 'integer', [
                    'null' => true,
                    'after' => 'position_id',
                ])
                ->addColumn('requirements', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'profile_id',
                ])
                ->addColumn('formation_source', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'requirements',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'formation_source',
                ])
                ->addColumn('in_slider', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'description',
                ])
                ->addColumn('app_gather_end_date', 'datetime', [
                    'null' => true,
                    'after' => 'in_slider',
                ])
                ->addColumn('custom_respondents', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'app_gather_end_date',
                ])
                ->addColumn('recruiters', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'custom_respondents',
                ])
                ->create();
        if (!$this->hasTable('hr_reserve_recruiters'))
            $this->table('hr_reserve_recruiters', [
                'id' => false,
                'primary_key' => ['reserve_recruiter_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('reserve_recruiter_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => true,
                    'after' => 'reserve_recruiter_id',
                ])
                ->addColumn('recruiter_id', 'integer', [
                    'null' => true,
                    'after' => 'reserve_id',
                ])
                ->addIndex(['reserve_id'], [
                    'name' => 'reserve_id',
                    'unique' => false,
                ])
                ->addIndex(['recruiter_id'], [
                    'name' => 'recruiter_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('hr_reserve_requests'))
            $this->table('hr_reserve_requests', [
                'id' => false,
                'primary_key' => ['reserve_request_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('reserve_request_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'reserve_request_id',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => true,
                    'after' => 'position_id',
                ])
                ->addColumn('request_date', 'datetime', [
                    'null' => true,
                    'after' => 'reserve_id',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'request_date',
                ])
                ->create();
        if (!$this->hasTable('hr_reserves'))
            $this->table('hr_reserves', [
                'id' => false,
                'primary_key' => ['reserve_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('reserve_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'reserve_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('state_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('state_change_date', 'datetime', [
                    'null' => true,
                    'after' => 'state_id',
                ])
                ->addColumn('profile_id', 'integer', [
                    'null' => true,
                    'after' => 'state_change_date',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'profile_id',
                ])
                ->addColumn('reserve_position_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'position_id',
                ])
                ->addColumn('manager_id', 'integer', [
                    'null' => true,
                    'after' => 'reserve_position_id',
                ])
                ->addColumn('process_id', 'integer', [
                    'null' => true,
                    'after' => 'manager_id',
                ])
                ->addColumn('session_id', 'integer', [
                    'null' => true,
                    'after' => 'process_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'session_id',
                ])
                ->addColumn('result', 'integer', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'result',
                ])
                ->addColumn('evaluation_user_id', 'integer', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('evaluation_date', 'datetime', [
                    'null' => true,
                    'after' => 'evaluation_user_id',
                ])
                ->addColumn('evaluation_start_send', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'evaluation_date',
                ])
                ->addColumn('report_notification_sent', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'evaluation_start_send',
                ])
                ->addColumn('extended_to', 'datetime', [
                    'null' => true,
                    'after' => 'report_notification_sent',
                ])
                ->addColumn('final_comment', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'extended_to',
                ])
                ->addColumn('cycle_id', 'integer', [
                    'null' => true,
                    'after' => 'final_comment',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['profile_id'], [
                    'name' => 'profile_id',
                    'unique' => false,
                ])
                ->addIndex(['position_id'], [
                    'name' => 'position_id',
                    'unique' => false,
                ])
                ->addIndex(['process_id'], [
                    'name' => 'process_id',
                    'unique' => false,
                ])
                ->addIndex(['session_id'], [
                    'name' => 'session_id',
                    'unique' => false,
                ])
                ->addIndex(['evaluation_user_id'], [
                    'name' => 'evaluation_user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('hr_rotation_files'))
            $this->table('hr_rotation_files', [
                'id' => false,
                'primary_key' => ['rotation_file_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('rotation_file_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('rotation_id', 'integer', [
                    'null' => true,
                    'after' => 'rotation_file_id',
                ])
                ->addColumn('file_id', 'integer', [
                    'null' => true,
                    'after' => 'rotation_id',
                ])
                ->addColumn('state_type', 'integer', [
                    'null' => true,
                    'after' => 'file_id',
                ])
                ->addIndex(['rotation_id'], [
                    'name' => 'rotation_id',
                    'unique' => false,
                ])
                ->addIndex(['file_id'], [
                    'name' => 'file_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('hr_rotation_recruiters'))
            $this->table('hr_rotation_recruiters', [
                'id' => false,
                'primary_key' => ['rotation_recruiter_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('rotation_recruiter_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('rotation_id', 'integer', [
                    'null' => true,
                    'after' => 'rotation_recruiter_id',
                ])
                ->addColumn('recruiter_id', 'integer', [
                    'null' => true,
                    'after' => 'rotation_id',
                ])
                ->addIndex(['rotation_id'], [
                    'name' => 'rotation_id',
                    'unique' => false,
                ])
                ->addIndex(['recruiter_id'], [
                    'name' => 'recruiter_id',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}