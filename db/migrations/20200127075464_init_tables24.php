<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables24 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('test'))
            $this->table('test', [
                'id' => false,
                'primary_key' => ['tid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('tid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'tid',
                ])
                ->addColumn('cidowner', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'cid',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cidowner',
                ])
                ->addColumn('datatype', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'title',
                ])
                ->addColumn('data', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'datatype',
                ])
                ->addColumn('random', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'data',
                ])
                ->addColumn('lim', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'random',
                ])
                ->addColumn('qty', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'lim',
                ])
                ->addColumn('sort', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'qty',
                ])
                ->addColumn('free', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'sort',
                ])
                ->addColumn('skip', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'free',
                ])
                ->addColumn('rating', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'skip',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'rating',
                ])
                ->addColumn('questres', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'status',
                ])
                ->addColumn('endres', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'questres',
                ])
                ->addColumn('showurl', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'endres',
                ])
                ->addColumn('showotvet', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'showurl',
                ])
                ->addColumn('timelimit', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'showotvet',
                ])
                ->addColumn('startlimit', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'timelimit',
                ])
                ->addColumn('limitclean', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'startlimit',
                ])
                ->addColumn('last', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'limitclean',
                ])
                ->addColumn('lastmid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'last',
                ])
                ->addColumn('cache_qty', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lastmid',
                ])
                ->addColumn('random_vars', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cache_qty',
                ])
                ->addColumn('allow_view_log', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'random_vars',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'allow_view_log',
                ])
                ->addColumn('comments', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created_by',
                ])
                ->addColumn('mode', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'comments',
                ])
                ->addColumn('is_poll', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'mode',
                ])
                ->addColumn('poll_mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'is_poll',
                ])
                ->addColumn('test_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'poll_mid',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'test_id',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lesson_id',
                ])
                ->addColumn('threshold', 'integer', [
                    'null' => true,
                    'default' => '75',
                    'after' => 'type',
                ])
                ->addColumn('adaptive', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'threshold',
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->addIndex(['is_poll'], [
                    'name' => 'is_poll',
                    'unique' => false,
                ])
                ->addIndex(['poll_mid'], [
                    'name' => 'poll_mid',
                    'unique' => false,
                ])
                ->addIndex(['test_id'], [
                    'name' => 'test_id',
                    'unique' => false,
                ])
                ->addIndex(['lesson_id'], [
                    'name' => 'lesson_id',
                    'unique' => false,
                ])
                ->addIndex(['type'], [
                    'name' => 'type',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('test_abstract'))
            $this->table('test_abstract', [
                'id' => false,
                'primary_key' => ['test_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('test_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'test_id',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'title',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'status',
                ])
                ->addColumn('keywords', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'keywords',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'updated',
                ])
                ->addColumn('questions', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'created_by',
                ])
                ->addColumn('data', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'questions',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'data',
                ])
                ->addColumn('location', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'subject_id',
                ])
                ->create();
        if (!$this->hasTable('test_feedback'))
            $this->table('test_feedback', [
                'id' => false,
                'primary_key' => ['test_feedback_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('test_feedback_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'test_feedback_id',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'title',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'type',
                ])
                ->addColumn('parent', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'text',
                ])
                ->addColumn('treshold_min', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'parent',
                ])
                ->addColumn('treshold_max', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'treshold_min',
                ])
                ->addColumn('test_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'treshold_max',
                ])
                ->addColumn('question_id', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'test_id',
                ])
                ->addColumn('answer_id', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'question_id',
                ])
                ->addColumn('show_event', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'answer_id',
                ])
                ->addColumn('show_on_values', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'show_event',
                ])
                ->addIndex(['parent'], [
                    'name' => 'parent',
                    'unique' => false,
                ])
                ->addIndex(['type'], [
                    'name' => 'type',
                    'unique' => false,
                ])
                ->addIndex(['treshold_min', 'treshold_max'], [
                    'name' => 'treshold',
                    'unique' => false,
                ])
                ->addIndex(['test_id'], [
                    'name' => 'test_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('testcount'))
            $this->table('testcount', [
                'id' => false,
                'primary_key' => ['mid', 'tid', 'cid', 'lesson_id'],
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
                ->addColumn('tid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'mid',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'tid',
                ])
                ->addColumn('qty', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'cid',
                ])
                ->addColumn('last', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'qty',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'last',
                ])
                ->create();
        if (!$this->hasTable('tests_questions'))
            $this->table('tests_questions', [
                'id' => false,
                'primary_key' => ['subject_id', 'test_id', 'kod'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('subject_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('test_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'subject_id',
                ])
                ->addColumn('kod', 'string', [
                    'null' => false,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'test_id',
                    'limit' => 220,
                ])
                ->addIndex(['kod'], [
                    'name' => 'kod',
                    'unique' => false,
                ])
                ->addIndex(['subject_id'], [
                    'name' => 'subject_id',
                    'unique' => false,
                ])
                ->addIndex(['test_id'], [
                    'name' => 'test_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('timesheets'))
            $this->table('timesheets', [
                'id' => false,
                'primary_key' => ['timesheet_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('timesheet_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'timesheet_id',
                ])
                ->addColumn('action_type', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'action_type',
                ])
                ->addColumn('action_date', 'datetime', [
                    'null' => true,
                    'after' => 'description',
                ])
                ->addColumn('begin_time', 'time', [
                    'null' => true,
                    'after' => 'action_date',
                ])
                ->addColumn('end_time', 'time', [
                    'null' => true,
                    'after' => 'begin_time',
                ])
                ->create();
        if (!$this->hasTable('tracks2group'))
            $this->table('tracks2group', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('trid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'id',
                ])
                ->addColumn('level', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'trid',
                ])
                ->addColumn('gid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'level',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'gid',
                ])
                ->create();
        if (!$this->hasTable('updates'))
            $this->table('updates', [
                'id' => false,
                'primary_key' => ['update_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('update_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('version', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'update_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'version',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'created',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'created_by',
                ])
                ->addColumn('organization', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'updated',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'organization',
                ])
                ->addColumn('servers', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}