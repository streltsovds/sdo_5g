<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables14 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('offlines'))
            $this->table('offlines', [
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
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'subject_id',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'title',
                ])
                ->create();
        if (!$this->hasTable('organizations'))
            $this->table('organizations', [
                'id' => false,
                'primary_key' => ['oid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('oid', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'oid',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'after' => 'title',
                ])
                ->addColumn('root_ref', 'integer', [
                    'null' => true,
                    'after' => 'cid',
                ])
                ->addColumn('level', 'integer', [
                    'null' => true,
                    'after' => 'root_ref',
                ])
                ->addColumn('next_ref', 'integer', [
                    'null' => true,
                    'after' => 'level',
                ])
                ->addColumn('prev_ref', 'integer', [
                    'null' => true,
                    'after' => 'next_ref',
                ])
                ->addColumn('mod_ref', 'integer', [
                    'null' => true,
                    'after' => 'prev_ref',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'mod_ref',
                ])
                ->addColumn('vol1', 'integer', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('vol2', 'integer', [
                    'null' => true,
                    'after' => 'vol1',
                ])
                ->addColumn('metadata', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'vol2',
                ])
                ->addColumn('module', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'metadata',
                ])
                ->addIndex(['prev_ref'], [
                    'name' => 'prev_ref',
                    'unique' => false,
                ])
                ->addIndex(['vol1'], [
                    'name' => 'vol1',
                    'unique' => false,
                ])
                ->addIndex(['vol2'], [
                    'name' => 'vol2',
                    'unique' => false,
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->addIndex(['level'], [
                    'name' => 'level',
                    'unique' => false,
                ])
                ->addIndex(['module'], [
                    'name' => 'module',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('organizations_bookmarks'))
            $this->table('organizations_bookmarks', [
                'id' => false,
                'primary_key' => ['bookmark_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('bookmark_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('parent_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'bookmark_id',
                ])
                ->addColumn('prev_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'parent_id',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'prev_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'title',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'item_id',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'user_id',
                ])
                ->addColumn('resource_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lesson_id',
                ])
                ->create();
        if (!$this->hasTable('password_history'))
            $this->table('password_history', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                ])
                ->addColumn('password', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('change_date', 'datetime', [
                    'null' => true,
                    'after' => 'password',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('periods'))
            $this->table('periods', [
                'id' => false,
                'primary_key' => ['lid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('lid', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('starttime', 'integer', [
                    'null' => true,
                    'default' => '540',
                    'after' => 'lid',
                ])
                ->addColumn('stoptime', 'integer', [
                    'null' => true,
                    'default' => '630',
                    'after' => 'starttime',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'stoptime',
                ])
                ->addColumn('count_hours', 'integer', [
                    'null' => true,
                    'default' => '2',
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('permission2act'))
            $this->table('permission2act', [
                'id' => false,
                'primary_key' => ['pmid', 'acid', 'type'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('pmid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'signed' => false,
                ])
                ->addColumn('acid', 'string', [
                    'null' => false,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'pmid',
                    'limit' => 100,
                ])
                ->addColumn('type', 'string', [
                    'null' => false,
                    'default' => 'dean',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'acid',
                    'limit' => 100,
                ])
                ->create();
        if (!$this->hasTable('permission2mid'))
            $this->table('permission2mid', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('pmid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                ])
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'pmid',
                ])
                ->addIndex(['pmid', 'mid'], [
                    'name' => 'pmid_mid',
                    'unique' => false,
                ])
                ->addIndex(['pmid'], [
                    'name' => 'pmid',
                    'unique' => false,
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('permission_groups'))
            $this->table('permission_groups', [
                'id' => false,
                'primary_key' => ['pmid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('pmid', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'pmid',
                ])
                ->addColumn('default', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'name',
                ])
                ->addColumn('type', 'string', [
                    'null' => true,
                    'default' => 'dean',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'default',
                ])
                ->addColumn('rang', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'type',
                ])
                ->addColumn('application', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'rang',
                ])
                ->create();
        if (!$this->hasTable('ppt2swf'))
            $this->table('ppt2swf', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                ])
                ->addColumn('process', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'status',
                ])
                ->addColumn('success_date', 'datetime', [
                    'null' => true,
                    'after' => 'process',
                ])
                ->addColumn('pool_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'success_date',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'pool_id',
                ])
                ->addColumn('webinar_id', 'integer', [
                    'null' => true,
                    'after' => 'url',
                ])
                ->create();
        if (!$this->hasTable('process_steps_data'))
            $this->table('process_steps_data', [
                'id' => false,
                'primary_key' => ['process_step_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('process_step_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('process_type', 'integer', [
                    'null' => true,
                    'after' => 'process_step_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'after' => 'process_type',
                ])
                ->addColumn('step', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'item_id',
                ])
                ->addColumn('date_begin', 'datetime', [
                    'null' => true,
                    'after' => 'step',
                ])
                ->addColumn('date_end', 'datetime', [
                    'null' => true,
                    'after' => 'date_begin',
                ])
                ->create();
        if (!$this->hasTable('processes'))
            $this->table('processes', [
                'id' => false,
                'primary_key' => ['process_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('process_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'process_id',
                ])
                ->addColumn('chain', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'chain',
                ])
                ->addColumn('programm_id', 'integer', [
                    'null' => true,
                    'after' => 'type',
                ])
                ->create();
        if (!$this->hasTable('programm'))
            $this->table('programm', [
                'id' => false,
                'primary_key' => ['programm_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('programm_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('programm_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'programm_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'after' => 'programm_type',
                ])
                ->addColumn('item_type', 'integer', [
                    'null' => true,
                    'after' => 'item_id',
                ])
                ->addColumn('mode_strict', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'item_type',
                ])
                ->addColumn('mode_finalize', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'mode_strict',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'mode_finalize',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('programm_events'))
            $this->table('programm_events', [
                'id' => false,
                'primary_key' => ['programm_event_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('programm_event_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('programm_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'programm_event_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'programm_id',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'name',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'type',
                ])
                ->addColumn('day_begin', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'item_id',
                ])
                ->addColumn('day_end', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'day_begin',
                ])
                ->addColumn('ordr', 'integer', [
                    'null' => true,
                    'after' => 'day_end',
                ])
                ->addColumn('isElective', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'ordr',
                ])
                ->addColumn('hidden', 'boolean', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'isElective',
                ])
                ->create();
        if (!$this->hasTable('programm_events_users'))
            $this->table('programm_events_users', [
                'id' => false,
                'primary_key' => ['programm_event_user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('programm_event_user_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('programm_event_id', 'integer', [
                    'null' => true,
                    'after' => 'programm_event_user_id',
                ])
                ->addColumn('programm_id', 'integer', [
                    'null' => true,
                    'after' => 'programm_event_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'programm_id',
                ])
                ->addColumn('begin_date', 'datetime', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('end_date', 'datetime', [
                    'null' => true,
                    'after' => 'begin_date',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'end_date',
                ])
                ->create();
        if (!$this->hasTable('programm_users'))
            $this->table('programm_users', [
                'id' => false,
                'primary_key' => ['programm_user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('programm_user_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('programm_id', 'integer', [
                    'null' => true,
                    'after' => 'programm_user_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'programm_id',
                ])
                ->addColumn('assign_date', 'datetime', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->create();
        if (!$this->hasTable('projects'))
            $this->table('projects', [
                'id' => false,
                'primary_key' => ['projid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('projid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('external_id', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'projid',
                ])
                ->addColumn('code', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'external_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'code',
                ])
                ->addColumn('shortname', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('supplier_id', 'integer', [
                    'null' => true,
                    'after' => 'shortname',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'supplier_id',
                ])
                ->addColumn('type', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'description',
                ])
                ->addColumn('reg_type', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'type',
                ])
                ->addColumn('begin', 'datetime', [
                    'null' => true,
                    'after' => 'reg_type',
                ])
                ->addColumn('end', 'datetime', [
                    'null' => true,
                    'after' => 'begin',
                ])
                ->addColumn('begin_planned', 'datetime', [
                    'null' => true,
                    'after' => 'end',
                ])
                ->addColumn('end_planned', 'datetime', [
                    'null' => true,
                    'after' => 'begin_planned',
                ])
                ->addColumn('longtime', 'integer', [
                    'null' => true,
                    'after' => 'end_planned',
                ])
                ->addColumn('price', 'float', [
                    'null' => true,
                    'after' => 'longtime',
                ])
                ->addColumn('price_currency', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'price',
                ])
                ->addColumn('plan_users', 'integer', [
                    'null' => true,
                    'after' => 'price_currency',
                ])
                ->addColumn('services', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'plan_users',
                ])
                ->addColumn('period', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'services',
                ])
                ->addColumn('period_restriction_type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'period',
                ])
                ->addColumn('created', 'datetime', [
                    'null' => true,
                    'after' => 'period_restriction_type',
                ])
                ->addColumn('last_updated', 'datetime', [
                    'null' => true,
                    'after' => 'created',
                ])
                ->addColumn('access_mode', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'last_updated',
                ])
                ->addColumn('access_elements', 'integer', [
                    'null' => true,
                    'after' => 'access_mode',
                ])
                ->addColumn('mode_free_limit', 'integer', [
                    'null' => true,
                    'after' => 'access_elements',
                ])
                ->addColumn('auto_done', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'mode_free_limit',
                ])
                ->addColumn('base', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'auto_done',
                ])
                ->addColumn('base_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'base',
                ])
                ->addColumn('base_color', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'base_id',
                ])
                ->addColumn('claimant_process_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'base_color',
                ])
                ->addColumn('state', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'claimant_process_id',
                ])
                ->addColumn('default_uri', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'state',
                ])
                ->addColumn('scale_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'default_uri',
                ])
                ->addColumn('auto_mark', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'scale_id',
                ])
                ->addColumn('auto_graduate', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'auto_mark',
                ])
                ->addColumn('formula_id', 'integer', [
                    'null' => true,
                    'after' => 'auto_graduate',
                ])
                ->addColumn('threshold', 'integer', [
                    'null' => true,
                    'after' => 'formula_id',
                ])
                ->addColumn('is_public', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'threshold',
                ])
                ->addColumn('protocol', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'is_public',
                ])
                ->addIndex(['begin'], [
                    'name' => 'begin_idx',
                    'unique' => false,
                ])
                ->addIndex(['end'], [
                    'name' => 'end_idx',
                    'unique' => false,
                ])
                ->addIndex(['type'], [
                    'name' => 'type',
                    'unique' => false,
                ])
                ->addIndex(['reg_type'], [
                    'name' => 'reg_type',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}