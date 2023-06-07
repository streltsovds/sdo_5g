<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables13 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('meetingsID'))
            $this->table('meetingsID', [
                'id' => false,
                'primary_key' => ['SSID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('SSID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('meeting_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'SSID',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'meeting_id',
                ])
                ->addColumn('begin_personal', 'datetime', [
                    'null' => true,
                    'after' => 'MID',
                ])
                ->addColumn('end_personal', 'datetime', [
                    'null' => true,
                    'after' => 'begin_personal',
                ])
                ->addColumn('beginRelative', 'datetime', [
                    'null' => true,
                    'after' => 'end_personal',
                ])
                ->addColumn('endRelative', 'datetime', [
                    'null' => true,
                    'after' => 'beginRelative',
                ])
                ->addColumn('gid', 'integer', [
                    'null' => true,
                    'after' => 'endRelative',
                ])
                ->addColumn('isgroup', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'gid',
                ])
                ->addColumn('V_STATUS', 'integer', [
                    'null' => true,
                    'default' => '-1',
                    'after' => 'isgroup',
                ])
                ->addColumn('V_DONE', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'V_STATUS',
                ])
                ->addColumn('V_DESCRIPTION', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'V_DONE',
                ])
                ->addColumn('DESCR', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'V_DESCRIPTION',
                ])
                ->addColumn('SMSremind', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'DESCR',
                ])
                ->addColumn('ICQremind', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'SMSremind',
                ])
                ->addColumn('EMAILremind', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'ICQremind',
                ])
                ->addColumn('ISTUDremind', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'EMAILremind',
                ])
                ->addColumn('test_corr', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'ISTUDremind',
                ])
                ->addColumn('test_wrong', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'test_corr',
                ])
                ->addColumn('test_date', 'datetime', [
                    'null' => true,
                    'after' => 'test_wrong',
                ])
                ->addColumn('test_answers', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'test_date',
                ])
                ->addColumn('test_tries', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'test_answers',
                ])
                ->addColumn('toolParams', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'test_tries',
                ])
                ->addColumn('comments', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'toolParams',
                ])
                ->addColumn('chief', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'comments',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'chief',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('launched', 'datetime', [
                    'null' => true,
                    'after' => 'updated',
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->addIndex(['meeting_id'], [
                    'name' => 'meeting_id',
                    'unique' => false,
                ])
                ->addIndex(['meeting_id', 'MID'], [
                    'name' => 'meeting_id_MID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('meetings_marks_history'))
            $this->table('meetings_marks_history', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('MID', 'integer', [
                    'null' => true,
                ])
                ->addColumn('SSID', 'integer', [
                    'null' => false,
                    'after' => 'MID',
                ])
                ->addColumn('mark', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'SSID',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'mark',
                ])
                ->create();
        if (!$this->hasTable('messages'))
            $this->table('messages', [
                'id' => false,
                'primary_key' => ['message_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('message_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('from', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'message_id',
                ])
                ->addColumn('to', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'from',
                ])
                ->addColumn('subject', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'to',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'subject',
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'subject_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'message',
                ])
                ->addIndex(['from'], [
                    'name' => 'from_idx',
                    'unique' => false,
                ])
                ->addIndex(['to'], [
                    'name' => 'to_idx',
                    'unique' => false,
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'subject_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('methodologist'))
            $this->table('methodologist', [
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
        if (!$this->hasTable('moderators'))
            $this->table('moderators', [
                'id' => false,
                'primary_key' => ['moderator_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('moderator_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'moderator_id',
                ])
                ->addColumn('project_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addIndex(['user_id', 'project_id'], [
                    'name' => 'UID_PRID',
                    'unique' => true,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['project_id'], [
                    'name' => 'project_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('news'))
            $this->table('news', [
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
                ->addColumn('date', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'date',
                ])
                ->addColumn('author', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'after' => 'author',
                ])
                ->addColumn('announce', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created_by',
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'announce',
                ])
                ->addColumn('subject_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'message',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'subject_name',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'after' => 'url',
                ])
                ->addIndex(['id'], [
                    'name' => 'id',
                    'unique' => false,
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'subject_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('news2'))
            $this->table('news2', [
                'id' => false,
                'primary_key' => ['nID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('nID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('date', 'timestamp', [
                    'null' => true,
                    'after' => 'nID',
                ])
                ->addColumn('Title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'date',
                ])
                ->addColumn('author', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'Title',
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'author',
                ])
                ->addColumn('lang', 'char', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'message',
                ])
                ->addColumn('show', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lang',
                ])
                ->addColumn('standalone', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'show',
                ])
                ->addColumn('application', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'standalone',
                ])
                ->addColumn('soid', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'application',
                ])
                ->addColumn('resource_id', 'integer', [
                    'null' => true,
                    'after' => 'soid',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'resource_id',
                ])
                ->create();
        if (!$this->hasTable('notice'))
            $this->table('notice', [
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
                ->addColumn('cluster', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'id',
                ])
                ->addColumn('event', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cluster',
                ])
                ->addColumn('receiver', 'integer', [
                    'null' => true,
                    'after' => 'event',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'receiver',
                ])
                ->addColumn('message', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'after' => 'message',
                ])
                ->addColumn('enabled', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'type',
                ])
                ->create();
        if (!$this->hasTable('oauth_apps'))
            $this->table('oauth_apps', [
                'id' => false,
                'primary_key' => ['app_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('app_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'app_id',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'description',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'created',
                ])
                ->addColumn('callback_url', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created_by',
                ])
                ->addColumn('api_key', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'callback_url',
                ])
                ->addColumn('consumer_key', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'api_key',
                ])
                ->addColumn('consumer_secret', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'consumer_key',
                ])
                ->create();
        if (!$this->hasTable('oauth_nonces'))
            $this->table('oauth_nonces', [
                'id' => false,
                'primary_key' => ['nonce_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('nonce_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('app_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'nonce_id',
                ])
                ->addColumn('ts', 'datetime', [
                    'null' => true,
                    'after' => 'app_id',
                ])
                ->addColumn('nonce', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'ts',
                ])
                ->addIndex(['app_id'], [
                    'name' => 'app_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('oauth_tokens'))
            $this->table('oauth_tokens', [
                'id' => false,
                'primary_key' => ['token_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('token_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('app_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'token_id',
                ])
                ->addColumn('token', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'app_id',
                ])
                ->addColumn('token_secret', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'token',
                ])
                ->addColumn('state', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'token_secret',
                ])
                ->addColumn('verify', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'state',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'verify',
                ])
                ->addIndex(['app_id'], [
                    'name' => 'app_id',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}