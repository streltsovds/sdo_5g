<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables25 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('user_additional_fields'))
            $this->table('user_additional_fields', [
                'id' => false,
                'primary_key' => ['user_id', 'field_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                ])
                ->addColumn('field_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addColumn('value', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'field_id',
                ])
                ->addIndex(['user_id', 'field_id'], [
                    'name' => 'user_id',
                    'unique' => true,
                ])
                ->create();
        if (!$this->hasTable('user_login_log'))
            $this->table('user_login_log', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('login', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('date', 'datetime', [
                    'null' => true,
                    'after' => 'login',
                ])
                ->addColumn('event_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'date',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'event_type',
                ])
                ->addColumn('comments', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'status',
                ])
                ->addColumn('ip', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'comments',
                ])
                ->create();
        if (!$this->hasTable('video'))
            $this->table('video', [
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
                ->addColumn('filename', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'id',
                ])
                ->addColumn('created', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'filename',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created',
                ])
                ->addColumn('main_video', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'title',
                ])
                ->create();
        if (!$this->hasTable('videoblock'))
            $this->table('videoblock', [
                'id' => false,
                'primary_key' => ['videoblock_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('videoblock_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('file_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'videoblock_id',
                ])
                ->addColumn('is_default', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'file_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'is_default',
                ])
                ->addColumn('embedded_code', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('videochat_users'))
            $this->table('videochat_users', [
                'id' => false,
                'primary_key' => ['pointId', 'userId'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('pointId', 'string', [
                    'null' => false,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 230,
                ])
                ->addColumn('userId', 'integer', [
                    'null' => false,
                    'after' => 'pointId',
                ])
                ->addColumn('last', 'datetime', [
                    'null' => true,
                    'after' => 'userId',
                ])
                ->create();
        if (!$this->hasTable('webinar_answers'))
            $this->table('webinar_answers', [
                'id' => false,
                'primary_key' => ['aid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('aid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('qid', 'integer', [
                    'null' => true,
                    'after' => 'aid',
                ])
                ->addColumn('text', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'qid',
                ])
                ->create();
        if (!$this->hasTable('webinar_chat'))
            $this->table('webinar_chat', [
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
                ->addColumn('pointId', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'id',
                ])
                ->addColumn('message', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'pointId',
                ])
                ->addColumn('datetime', 'datetime', [
                    'null' => true,
                    'after' => 'message',
                ])
                ->addColumn('userId', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'datetime',
                ])
                ->create();
        if (!$this->hasTable('webinar_dbs'))
            $this->table('webinar_dbs', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('db_id', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('host', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'db_id',
                ])
                ->addColumn('port', 'integer', [
                    'null' => true,
                    'after' => 'host',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'port',
                ])
                ->addColumn('login', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('pass', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'login',
                ])
                ->create();
        if (!$this->hasTable('webinar_files'))
            $this->table('webinar_files', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('webinar_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                ])
                ->addColumn('file_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'webinar_id',
                ])
                ->addColumn('num', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'file_id',
                ])
                ->create();
        if (!$this->hasTable('webinar_history'))
            $this->table('webinar_history', [
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
                ->addColumn('pointId', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'id',
                ])
                ->addColumn('userId', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'pointId',
                ])
                ->addColumn('action', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'userId',
                ])
                ->addColumn('item', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'action',
                ])
                ->addColumn('datetime', 'datetime', [
                    'null' => true,
                    'after' => 'item',
                ])
                ->create();
        if (!$this->hasTable('webinar_plan'))
            $this->table('webinar_plan', [
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
                ->addColumn('pointId', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'id',
                ])
                ->addColumn('href', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'pointId',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'href',
                ])
                ->addColumn('bid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'title',
                ])
                ->create();
        if (!$this->hasTable('webinar_plan_current'))
            $this->table('webinar_plan_current', [
                'id' => false,
                'primary_key' => ['pointId'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('pointId', 'integer', [
                    'null' => false,
                ])
                ->addColumn('currentItem', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'pointId',
                ])
                ->create();
        if (!$this->hasTable('webinar_questions'))
            $this->table('webinar_questions', [
                'id' => false,
                'primary_key' => ['qid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('qid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('text', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'qid',
                    'limit' => 230,
                ])
                ->addColumn('type', 'boolean', [
                    'null' => true,
                    'after' => 'text',
                ])
                ->addColumn('point_id', 'integer', [
                    'null' => true,
                    'after' => 'type',
                ])
                ->addColumn('is_voted', 'boolean', [
                    'null' => true,
                    'after' => 'point_id',
                ])
                ->addIndex(['text', 'point_id'], [
                    'name' => 'text',
                    'unique' => true,
                ])
                ->create();
        if (!$this->hasTable('webinar_records'))
            $this->table('webinar_records', [
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
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'after' => 'id',
                ])
                ->addColumn('webinar_id', 'integer', [
                    'null' => true,
                    'after' => 'subject_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'webinar_id',
                ])
                ->create();
        if (!$this->hasTable('webinar_users'))
            $this->table('webinar_users', [
                'id' => false,
                'primary_key' => ['pointId', 'userId'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('pointId', 'integer', [
                    'null' => false,
                ])
                ->addColumn('userId', 'integer', [
                    'null' => false,
                    'after' => 'pointId',
                ])
                ->addColumn('last', 'datetime', [
                    'null' => true,
                    'after' => 'userId',
                ])
                ->create();
        if (!$this->hasTable('webinar_votes'))
            $this->table('webinar_votes', [
                'id' => false,
                'primary_key' => ['vid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('vid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'vid',
                ])
                ->addColumn('qid', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('aid', 'integer', [
                    'null' => true,
                    'after' => 'qid',
                ])
                ->create();
        if (!$this->hasTable('webinar_whiteboard'))
            $this->table('webinar_whiteboard', [
                'id' => false,
                'primary_key' => ['actionId'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('actionId', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('pointId', 'integer', [
                    'null' => true,
                    'after' => 'actionId',
                ])
                ->addColumn('userId', 'integer', [
                    'null' => true,
                    'after' => 'pointId',
                ])
                ->addColumn('actionType', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'userId',
                ])
                ->addColumn('datetime', 'datetime', [
                    'null' => true,
                    'after' => 'actionType',
                ])
                ->addColumn('color', 'integer', [
                    'null' => true,
                    'after' => 'datetime',
                ])
                ->addColumn('tool', 'integer', [
                    'null' => true,
                    'after' => 'color',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'tool',
                ])
                ->addColumn('width', 'integer', [
                    'null' => true,
                    'after' => 'text',
                ])
                ->addColumn('height', 'integer', [
                    'null' => true,
                    'after' => 'width',
                ])
                ->create();
        if (!$this->hasTable('webinar_whiteboard_points'))
            $this->table('webinar_whiteboard_points', [
                'id' => false,
                'primary_key' => ['pointId'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('pointId', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('actionId', 'integer', [
                    'null' => true,
                    'after' => 'pointId',
                ])
                ->addColumn('x', 'integer', [
                    'null' => true,
                    'after' => 'actionId',
                ])
                ->addColumn('y', 'integer', [
                    'null' => true,
                    'after' => 'x',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'after' => 'y',
                ])
                ->create();
        if (!$this->hasTable('webinars'))
            $this->table('webinars', [
                'id' => false,
                'primary_key' => ['webinar_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('webinar_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'webinar_id',
                ])
                ->addColumn('create_date', 'datetime', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'after' => 'create_date',
                ])
                ->addColumn('subject', 'string', [
                    'null' => true,
                    'default' => 'subject',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'subject_id',
                ])
                ->create();
        if (!$this->hasTable('wiki_archive'))
            $this->table('wiki_archive', [
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
                ->addColumn('article_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'article_id',
                ])
                ->addColumn('author', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'created',
                ])
                ->addColumn('body', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'author',
                ])
                ->create();
        if (!$this->hasTable('wiki_articles'))
            $this->table('wiki_articles', [
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
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'id',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'created',
                ])
                ->addColumn('subject_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
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
                ->addColumn('changed', 'datetime', [
                    'null' => true,
                    'after' => 'lesson_id',
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}