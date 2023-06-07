<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables9 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('estaff_spot'))
            $this->table('estaff_spot', [
                'id' => false,
                'primary_key' => ['spot_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('spot_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'spot_id',
                ])
                ->addColumn('start_date', 'datetime', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('state_date', 'datetime', [
                    'null' => true,
                    'after' => 'start_date',
                ])
                ->addColumn('state_id', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'state_date',
                ])
                ->addColumn('vacancy_name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'state_id',
                ])
                ->addColumn('resume_text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'vacancy_name',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['state_id'], [
                    'name' => 'state_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('events'))
            $this->table('events', [
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
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'event_id',
                ])
                ->addColumn('tool', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('scale_id', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'tool',
                ])
                ->addColumn('weight', 'integer', [
                    'null' => true,
                    'default' => '5',
                    'after' => 'scale_id',
                ])
                ->create();
        if (!$this->hasTable('exercises'))
            $this->table('exercises', [
                'id' => false,
                'primary_key' => ['exercise_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('exercise_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'exercise_id',
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
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'description',
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
                ->create();
        if (!$this->hasTable('faq'))
            $this->table('faq', [
                'id' => false,
                'primary_key' => ['faq_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('faq_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('question', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'faq_id',
                ])
                ->addColumn('answer', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'question',
                ])
                ->addColumn('roles', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'answer',
                ])
                ->addColumn('published', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'roles',
                ])
                ->create();
        if (!$this->hasTable('feedback'))
            $this->table('feedback', [
                'id' => false,
                'primary_key' => ['feedback_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('feedback_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'feedback_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'subject_id',
                ])
                ->addColumn('quest_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'quest_id',
                ])
                ->addColumn('date_finished', 'datetime', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'date_finished',
                ])
                ->addColumn('respondent_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'name',
                ])
                ->addColumn('assign_type', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'respondent_type',
                ])
                ->addColumn('assign_days', 'integer', [
                    'null' => true,
                    'after' => 'assign_type',
                ])
                ->addColumn('assign_new', 'integer', [
                    'null' => true,
                    'after' => 'assign_days',
                ])
                ->addColumn('assign_anonymous', 'integer', [
                    'null' => true,
                    'after' => 'assign_new',
                ])
                ->addColumn('assign_teacher', 'integer', [
                    'null' => true,
                    'after' => 'assign_anonymous',
                ])
                ->addColumn('assign_anonymous_hash', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'assign_teacher',
                ])
                ->create();
        if (!$this->hasTable('feedback_users'))
            $this->table('feedback_users', [
                'id' => false,
                'primary_key' => ['feedback_user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('feedback_user_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'feedback_user_id',
                ])
                ->addColumn('feedback_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('subordinate_id', 'integer', [
                    'null' => true,
                    'after' => 'feedback_id',
                ])
                ->addColumn('common_date_end', 'integer', [
                    'null' => true,
                    'after' => 'subordinate_id',
                ])
                ->create();
        if (!$this->hasTable('file'))
            $this->table('file', [
                'id' => false,
                'primary_key' => ['kod', 'fnum'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('kod', 'string', [
                    'null' => false,
                    'default' => '',
                    'limit' => 230,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('fnum', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'kod',
                ])
                ->addColumn('ftype', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'fnum',
                ])
                ->addColumn('fname', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'ftype',
                ])
                ->addColumn('fdata', 'blob', [
                    'null' => true,
                    'after' => 'fname',
                ])
                ->addColumn('fdate', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'fdata',
                ])
                ->addColumn('fx', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'fdate',
                ])
                ->addColumn('fy', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'fx',
                ])
                ->create();
        if (!$this->hasTable('files'))
            $this->table('files', [
                'id' => false,
                'primary_key' => ['file_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('file_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'file_id',
                ])
                ->addColumn('path', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('file_size', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'path',
                ])
                ->addColumn('item_type', 'integer', [
                    'null' => true,
                    'after' => 'file_size',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'after' => 'item_type',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'after' => 'item_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'created_by',
                ])
                ->create();
        if (!$this->hasTable('formula'))
            $this->table('formula', [
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
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'id',
                ])
                ->addColumn('formula', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'formula',
                ])
                ->addColumn('CID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'type',
                ])
                ->create();
        if (!$this->hasTable('forums_list'))
            $this->table('forums_list', [
                'id' => false,
                'primary_key' => ['forum_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('forum_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'forum_id',
                ])
                ->addColumn('subject', 'string', [
                    'null' => true,
                    'default' => 'subject',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'subject_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'subject',
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
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_ip',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'title',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('flags', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'updated',
                ])
                ->create();
        if (!$this->hasTable('forums_messages'))
            $this->table('forums_messages', [
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
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('forum_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'message_id',
                ])
                ->addColumn('section_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'forum_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'section_id',
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
                ->addColumn('level', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'user_ip',
                ])
                ->addColumn('answer_to', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'level',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'answer_to',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('text_preview', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'text',
                ])
                ->addColumn('text_size', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'text_preview',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'text_size',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('delete_date', 'datetime', [
                    'null' => true,
                    'after' => 'updated',
                ])
                ->addColumn('deleted_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'delete_date',
                ])
                ->addColumn('rating', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'deleted_by',
                ])
                ->addColumn('flags', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'rating',
                ])
                ->addColumn('is_hidden', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'flags',
                ])
                ->addIndex(['section_id', 'forum_id'], [
                    'name' => 'forum_id_section_id',
                    'unique' => false,
                ])
                ->addIndex(['forum_id', 'user_id'], [
                    'name' => 'user_id_forum_id',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}