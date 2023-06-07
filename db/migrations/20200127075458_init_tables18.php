<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables18 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('recruit_vacancy_candidates'))
            $this->table('recruit_vacancy_candidates', [
                'id' => false,
                'primary_key' => ['vacancy_candidate_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('vacancy_candidate_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('vacancy_id', 'integer', [
                    'null' => true,
                    'after' => 'vacancy_candidate_id',
                ])
                ->addColumn('candidate_id', 'integer', [
                    'null' => true,
                    'after' => 'vacancy_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'candidate_id',
                ])
                ->addColumn('process_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'process_id',
                ])
                ->addColumn('result', 'integer', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('reserve_position_id', 'integer', [
                    'null' => true,
                    'after' => 'result',
                ])
                ->addColumn('external_status', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'reserve_position_id',
                ])
                ->addIndex(['vacancy_id'], [
                    'name' => 'vacancy_id',
                    'unique' => false,
                ])
                ->addIndex(['candidate_id'], [
                    'name' => 'candidate_id',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['process_id'], [
                    'name' => 'process_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('recruit_vacancy_hh_resume_ignore'))
            $this->table('recruit_vacancy_hh_resume_ignore', [
                'id' => false,
                'primary_key' => ['vacancy_hh_resume_ignore_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('vacancy_hh_resume_ignore_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('vacancy_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'vacancy_hh_resume_ignore_id',
                ])
                ->addColumn('hh_resume_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'vacancy_id',
                ])
                ->addColumn('date', 'datetime', [
                    'null' => true,
                    'after' => 'hh_resume_id',
                ])
                ->addColumn('create_user_id', 'integer', [
                    'null' => true,
                    'after' => 'date',
                ])
                ->addIndex(['vacancy_id', 'hh_resume_id'], [
                    'name' => 'hh_resume_id',
                    'unique' => false,
                ])
                ->addIndex(['create_user_id'], [
                    'name' => 'create_user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('recruit_vacancy_recruiters'))
            $this->table('recruit_vacancy_recruiters', [
                'id' => false,
                'primary_key' => ['vacancy_recruiter_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('vacancy_recruiter_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('vacancy_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'vacancy_recruiter_id',
                ])
                ->addColumn('recruiter_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'vacancy_id',
                ])
                ->addIndex(['vacancy_id'], [
                    'name' => 'vacancy_id',
                    'unique' => false,
                ])
                ->addIndex(['recruiter_id'], [
                    'name' => 'recruiter_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('recruiters'))
            $this->table('recruiters', [
                'id' => false,
                'primary_key' => ['recruiter_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('recruiter_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'recruiter_id',
                ])
                ->addColumn('hh_auth_data', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->create();
        if (!$this->hasTable('report_templates'))
            $this->table('report_templates', [
                'id' => false,
                'primary_key' => ['rtid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('rtid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('template_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'rtid',
                ])
                ->addColumn('report_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'template_name',
                ])
                ->addColumn('created', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'report_name',
                ])
                ->addColumn('creator', 'integer', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('edited', 'integer', [
                    'null' => true,
                    'after' => 'creator',
                ])
                ->addColumn('editor', 'integer', [
                    'null' => true,
                    'after' => 'edited',
                ])
                ->addColumn('template', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'editor',
                ])
                ->create();
        if (!$this->hasTable('reports'))
            $this->table('reports', [
                'id' => false,
                'primary_key' => ['report_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('report_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('domain', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'report_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'domain',
                ])
                ->addColumn('fields', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'fields',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'created',
                ])
                ->addColumn('status', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'created_by',
                ])
                ->create();
        if (!$this->hasTable('reports_roles'))
            $this->table('reports_roles', [
                'id' => false,
                'primary_key' => ['role', 'report_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('role', 'string', [
                    'null' => false,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 230,
                ])
                ->addColumn('report_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'after' => 'role',
                ])
                ->create();
        if (!$this->hasTable('resource_revisions'))
            $this->table('resource_revisions', [
                'id' => false,
                'primary_key' => ['revision_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('revision_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('resource_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'revision_id',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'resource_id',
                ])
                ->addColumn('volume', 'string', [
                    'null' => true,
                    'default' => '0',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'url',
                ])
                ->addColumn('filename', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'volume',
                ])
                ->addColumn('filetype', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'filename',
                ])
                ->addColumn('content', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'filetype',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'content',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'updated',
                ])
                ->create();
        if (!$this->hasTable('resources'))
            $this->table('resources', [
                'id' => false,
                'primary_key' => ['resource_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('resource_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('resource_id_external', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'resource_id',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'resource_id_external',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('volume', 'string', [
                    'null' => true,
                    'default' => '0',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'url',
                ])
                ->addColumn('filename', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'volume',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'filename',
                ])
                ->addColumn('filetype', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'type',
                ])
                ->addColumn('edit_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'filetype',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'edit_type',
                ])
                ->addColumn('content', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'content',
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
                ->addColumn('services', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'created_by',
                ])
                ->addColumn('subject', 'string', [
                    'null' => true,
                    'default' => 'subject',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'services',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'subject',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'subject_id',
                ])
                ->addColumn('location', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'status',
                ])
                ->addColumn('db_id', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'location',
                ])
                ->addColumn('test_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'db_id',
                ])
                ->addColumn('activity_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'test_id',
                ])
                ->addColumn('activity_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'activity_id',
                ])
                ->addColumn('related_resources', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'activity_type',
                ])
                ->addColumn('parent_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'related_resources',
                ])
                ->addColumn('parent_revision_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'parent_id',
                ])
                ->addColumn('external_viewer', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'parent_revision_id',
                ])
                ->addColumn('storage_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'external_viewer',
                ])
                ->create();
        if (!$this->hasTable('responsibilities'))
            $this->table('responsibilities', [
                'id' => false,
                'primary_key' => ['responsibility_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('responsibility_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'responsibility_id',
                ])
                ->addColumn('item_type', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'after' => 'item_type',
                ])
                ->addColumn('sv_scope', 'integer', [
                    'null' => true,
                    'after' => 'item_id',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('rooms'))
            $this->table('rooms', [
                'id' => false,
                'primary_key' => ['rid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('rid', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'rid',
                ])
                ->addColumn('volume', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'volume',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'type',
                ])
                ->create();
        if (!$this->hasTable('rooms2course'))
            $this->table('rooms2course', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('rid', 'integer', [
                    'null' => true,
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'after' => 'rid',
                ])
                ->addIndex(['rid', 'cid'], [
                    'name' => 'rid_cid',
                    'unique' => false,
                ])
                ->addIndex(['rid'], [
                    'name' => 'rid',
                    'unique' => false,
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('scale_values'))
            $this->table('scale_values', [
                'id' => false,
                'primary_key' => ['value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('value_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('scale_id', 'integer', [
                    'null' => true,
                    'after' => 'value_id',
                ])
                ->addColumn('value', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'scale_id',
                ])
                ->addColumn('text', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'value',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'text',
                ])
                ->create();
        if (!$this->hasTable('scales'))
            $this->table('scales', [
                'id' => false,
                'primary_key' => ['scale_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('scale_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'scale_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'description',
                ])
                ->addColumn('mode', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'type',
                ])
                ->create();
        if (!$this->hasTable('schedule'))
            $this->table('schedule', [
                'id' => false,
                'primary_key' => ['SHEID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('SHEID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'SHEID',
                ])
                ->addColumn('url', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('descript', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'url',
                ])
                ->addColumn('begin', 'datetime', [
                    'null' => true,
                    'after' => 'descript',
                ])
                ->addColumn('end', 'datetime', [
                    'null' => true,
                    'after' => 'begin',
                ])
                ->addColumn('createID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'end',
                ])
                ->addColumn('createDate', 'datetime', [
                    'null' => true,
                    'after' => 'createID',
                ])
                ->addColumn('typeID', 'string', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'createDate',
                ])
                ->addColumn('vedomost', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'typeID',
                ])
                ->addColumn('CID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'vedomost',
                ])
                ->addColumn('CHID', 'integer', [
                    'null' => true,
                    'after' => 'CID',
                ])
                ->addColumn('startday', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'CHID',
                ])
                ->addColumn('stopday', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'startday',
                ])
                ->addColumn('timetype', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'stopday',
                ])
                ->addColumn('isgroup', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'timetype',
                ])
                ->addColumn('cond_sheid', 'string', [
                    'null' => true,
                    'default' => '-1',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'isgroup',
                ])
                ->addColumn('cond_mark', 'string', [
                    'null' => true,
                    'default' => '-',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cond_sheid',
                ])
                ->addColumn('cond_progress', 'string', [
                    'null' => true,
                    'default' => '0',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cond_mark',
                ])
                ->addColumn('cond_avgbal', 'string', [
                    'null' => true,
                    'default' => '0',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cond_progress',
                ])
                ->addColumn('cond_sumbal', 'string', [
                    'null' => true,
                    'default' => '0',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cond_avgbal',
                ])
                ->addColumn('cond_operation', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'cond_sumbal',
                ])
                ->addColumn('max_mark', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'cond_operation',
                ])
                ->addColumn('period', 'string', [
                    'null' => true,
                    'default' => '-1',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'max_mark',
                ])
                ->addColumn('rid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'period',
                ])
                ->addColumn('teacher', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'rid',
                ])
                ->addColumn('moderator', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'teacher',
                ])
                ->addColumn('gid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'moderator',
                ])
                ->addColumn('perm', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'gid',
                ])
                ->addColumn('pub', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'perm',
                ])
                ->addColumn('sharepointId', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'pub',
                ])
                ->addColumn('connectId', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'sharepointId',
                ])
                ->addColumn('recommend', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'connectId',
                ])
                ->addColumn('notice', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'recommend',
                ])
                ->addColumn('notice_days', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'notice',
                ])
                ->addColumn('all', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'notice_days',
                ])
                ->addColumn('params', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'all',
                ])
                ->addColumn('activities', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'params',
                ])
                ->addColumn('order', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'activities',
                ])
                ->addColumn('tool', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'order',
                ])
                ->addColumn('isfree', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'tool',
                ])
                ->addColumn('section_id', 'integer', [
                    'null' => true,
                    'after' => 'isfree',
                ])
                ->addColumn('session_id', 'integer', [
                    'null' => true,
                    'after' => 'section_id',
                ])
                ->addColumn('threshold', 'integer', [
                    'null' => true,
                    'after' => 'session_id',
                ])
                ->addColumn('notify_before', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'threshold',
                ])
                ->addColumn('webinar_event_id', 'integer', [
                    'null' => true,
                    'after' => 'notify_before',
                ])
                ->addColumn('material_id', 'integer', [
                    'null' => true,
                    'after' => 'webinar_event_id',
                ])
                ->addIndex(['begin'], [
                    'name' => 'begin_idx',
                    'unique' => false,
                ])
                ->addIndex(['end'], [
                    'name' => 'end_idx',
                    'unique' => false,
                ])
                ->addIndex(['typeID'], [
                    'name' => 'typeID',
                    'unique' => false,
                ])
                ->addIndex(['vedomost'], [
                    'name' => 'vedomost',
                    'unique' => false,
                ])
                ->addIndex(['CID'], [
                    'name' => 'CID',
                    'unique' => false,
                ])
                ->addIndex(['CHID'], [
                    'name' => 'CHID',
                    'unique' => false,
                ])
                ->addIndex(['period'], [
                    'name' => 'period',
                    'unique' => false,
                ])
                ->addIndex(['rid'], [
                    'name' => 'rid',
                    'unique' => false,
                ])
                ->addIndex(['gid'], [
                    'name' => 'gid',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}