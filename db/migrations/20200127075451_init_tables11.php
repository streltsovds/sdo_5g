<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables11 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('hr_rotations'))
            $this->table('hr_rotations', [
                'id' => false,
                'primary_key' => ['rotation_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('rotation_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'rotation_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('position_id', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('begin_date', 'datetime', [
                    'null' => true,
                    'after' => 'position_id',
                ])
                ->addColumn('end_date', 'datetime', [
                    'null' => true,
                    'after' => 'begin_date',
                ])
                ->addColumn('state_change_date', 'datetime', [
                    'null' => true,
                    'after' => 'end_date',
                ])
                ->addColumn('state_id', 'integer', [
                    'null' => true,
                    'after' => 'state_change_date',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'state_id',
                ])
                ->addColumn('result', 'integer', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('report_notification_sent', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'result',
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->addIndex(['position_id'], [
                    'name' => 'position_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('hrs'))
            $this->table('hrs', [
                'id' => false,
                'primary_key' => ['hr_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('hr_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => false,
                    'after' => 'hr_id',
                ])
                ->create();
        if (!$this->hasTable('htmlpage'))
            $this->table('htmlpage', [
                'id' => false,
                'primary_key' => ['page_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('page_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('group_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'page_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'group_id',
                ])
                ->addColumn('ordr', 'integer', [
                    'null' => true,
                    'default' => '10',
                    'after' => 'name',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'ordr',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'text',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'url',
                ])
                ->addColumn('icon_url', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->addColumn('visible', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'icon_url',
                ])
                ->create();
        if (!$this->hasTable('htmlpage_groups'))
            $this->table('htmlpage_groups', [
                'id' => false,
                'primary_key' => ['group_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('group_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('lft', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'group_id',
                ])
                ->addColumn('rgt', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'lft',
                ])
                ->addColumn('level', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'rgt',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'level',
                ])
                ->addColumn('ordr', 'integer', [
                    'null' => true,
                    'default' => '10',
                    'after' => 'name',
                ])
                ->addColumn('role', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'ordr',
                ])
                ->create();
        if (!$this->hasTable('idea'))
            $this->table('idea', [
                'id' => false,
                'primary_key' => ['idea_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('idea_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'idea_id',
                ])
                ->addColumn('description', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'description',
                ])
                ->addColumn('anonymous', 'integer', [
                    'null' => true,
                    'after' => 'status',
                ])
                ->addColumn('date_created', 'datetime', [
                    'null' => true,
                    'after' => 'anonymous',
                ])
                ->create();
        if (!$this->hasTable('idea_chat'))
            $this->table('idea_chat', [
                'id' => false,
                'primary_key' => ['idea_chat_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('idea_chat_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'idea_chat_id',
                ])
                ->addColumn('message', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('date_created', 'datetime', [
                    'null' => true,
                    'after' => 'message',
                ])
                ->addColumn('parent_idea_chat_id', 'integer', [
                    'null' => true,
                    'after' => 'date_created',
                ])
                ->create();
        if (!$this->hasTable('idea_like'))
            $this->table('idea_like', [
                'id' => false,
                'primary_key' => ['idea_like_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('idea_like_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('idea_id', 'integer', [
                    'null' => true,
                    'after' => 'idea_like_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'idea_id',
                ])
                ->addColumn('value', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('date_created', 'datetime', [
                    'null' => true,
                    'after' => 'value',
                ])
                ->create();
        if (!$this->hasTable('idea_url'))
            $this->table('idea_url', [
                'id' => false,
                'primary_key' => ['idea_url_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('idea_url_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('idea_id', 'integer', [
                    'null' => true,
                    'after' => 'idea_url_id',
                ])
                ->addColumn('url', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'idea_id',
                ])
                ->create();
        if (!$this->hasTable('interesting_facts'))
            $this->table('interesting_facts', [
                'id' => false,
                'primary_key' => ['interesting_facts_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('interesting_facts_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'interesting_facts_id',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'after' => 'text',
                ])
                ->create();
        if (!$this->hasTable('interface'))
            $this->table('interface', [
                'id' => false,
                'primary_key' => ['interface_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('interface_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('role', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                    'after' => 'interface_id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'role',
                ])
                ->addColumn('block', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('necessity', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'block',
                ])
                ->addColumn('x', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'necessity',
                ])
                ->addColumn('y', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'x',
                ])
                ->addColumn('width', 'integer', [
                    'null' => true,
                    'default' => '100',
                    'after' => 'y',
                ])
                ->addColumn('param_id', 'string', [
                    'null' => true,
                    'default' => '0',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'width',
                ])
                ->addColumn('skin', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'param_id',
                ])
                ->addIndex(['role'], [
                    'name' => 'role',
                    'unique' => false,
                ])
                ->addIndex(['user_id'], [
                    'name' => 'user_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('kbase_assessment'))
            $this->table('kbase_assessment', [
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
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'id',
                ])
                ->addColumn('resource_id', 'integer', [
                    'null' => true,
                    'after' => 'type',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'resource_id',
                ])
                ->addColumn('assessment', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'MID',
                ])
                ->create();
        if (!$this->hasTable('labor_safety_specs'))
            $this->table('labor_safety_specs', [
                'id' => false,
                'primary_key' => ['labor_safety_spec_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('labor_safety_spec_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'labor_safety_spec_id',
                ])
                ->create();
        if (!$this->hasTable('library'))
            $this->table('library', [
                'id' => false,
                'primary_key' => ['bid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('bid', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'bid',
                ])
                ->addColumn('parent', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'cid',
                ])
                ->addColumn('cats', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'parent',
                ])
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'cats',
                ])
                ->addColumn('uid', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'mid',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'uid',
                ])
                ->addColumn('author', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'title',
                ])
                ->addColumn('publisher', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'author',
                ])
                ->addColumn('publish_date', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'publisher',
                ])
                ->addColumn('description', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'publish_date',
                ])
                ->addColumn('keywords', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'description',
                ])
                ->addColumn('filename', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'keywords',
                ])
                ->addColumn('location', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'filename',
                ])
                ->addColumn('metadata', 'blob', [
                    'null' => true,
                    'after' => 'location',
                ])
                ->addColumn('need_access_level', 'integer', [
                    'null' => true,
                    'default' => '5',
                    'signed' => false,
                    'after' => 'metadata',
                ])
                ->addColumn('upload_date', 'datetime', [
                    'null' => true,
                    'after' => 'need_access_level',
                ])
                ->addColumn('is_active_version', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'upload_date',
                ])
                ->addColumn('type', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'is_active_version',
                ])
                ->addColumn('is_package', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'type',
                ])
                ->addColumn('quantity', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'is_package',
                ])
                ->addColumn('content', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'quantity',
                ])
                ->addColumn('scorm_params', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'content',
                ])
                ->addColumn('pointId', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'scorm_params',
                ])
                ->addColumn('courses', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'pointId',
                ])
                ->addColumn('lms', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'courses',
                ])
                ->addColumn('cms', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'lms',
                ])
                ->addColumn('place', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cms',
                ])
                ->addColumn('not_moderated', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'place',
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->addIndex(['need_access_level'], [
                    'name' => 'need_access_level',
                    'unique' => false,
                ])
                ->addIndex(['is_active_version'], [
                    'name' => 'is_active_version',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('library_categories'))
            $this->table('library_categories', [
                'id' => false,
                'primary_key' => ['catid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('catid', 'string', [
                    'null' => false,
                    'default' => '',
                    'limit' => 249,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'catid',
                ])
                ->addColumn('parent', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('library_index'))
            $this->table('library_index', [
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
                ->addColumn('module', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'id',
                ])
                ->addColumn('file', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'module',
                ])
                ->addColumn('keywords', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'file',
                ])
                ->create();
        if (!$this->hasTable('like_user'))
            $this->table('like_user', [
                'id' => false,
                'primary_key' => ['like_user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('like_user_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('item_type', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'like_user_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'item_type',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'item_id',
                ])
                ->addColumn('value', 'integer', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->addColumn('date', 'datetime', [
                    'null' => true,
                    'after' => 'value',
                ])
                ->create();
        if (!$this->hasTable('likes'))
            $this->table('likes', [
                'id' => false,
                'primary_key' => ['like_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('like_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('item_type', 'integer', [
                    'null' => true,
                    'after' => 'like_id',
                ])
                ->addColumn('item_id', 'integer', [
                    'null' => true,
                    'after' => 'item_type',
                ])
                ->addColumn('count_like', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'item_id',
                ])
                ->addColumn('count_dislike', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'count_like',
                ])
                ->create();
        if (!$this->hasTable('list'))
            $this->table('list', [
                'id' => false,
                'primary_key' => ['kod'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('kod', 'string', [
                    'null' => false,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                ])
                ->addColumn('qtype', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'kod',
                ])
                ->addColumn('qdata', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'qtype',
                ])
                ->addColumn('qtema', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'qdata',
                ])
                ->addColumn('qmoder', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'qtema',
                ])
                ->addColumn('adata', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'qmoder',
                ])
                ->addColumn('balmax', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'adata',
                ])
                ->addColumn('balmin', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'balmax',
                ])
                ->addColumn('url', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'balmin',
                ])
                ->addColumn('last', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'url',
                ])
                ->addColumn('timelimit', 'integer', [
                    'null' => true,
                    'after' => 'last',
                ])
                ->addColumn('weight', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'timelimit',
                ])
                ->addColumn('is_shuffled', 'integer', [
                    'null' => true,
                    'default' => '1',
                    'after' => 'weight',
                ])
                ->addColumn('created_by', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'is_shuffled',
                ])
                ->addColumn('timetoanswer', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'created_by',
                ])
                ->addColumn('prepend_test', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'timetoanswer',
                ])
                ->addColumn('is_poll', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'prepend_test',
                ])
                ->addColumn('id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                    'after' => 'is_poll',
                ])
                ->addColumn('ordr', 'integer', [
                    'null' => true,
                    'default' => '10',
                    'after' => 'id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'ordr',
                ])
                ->addIndex(['id'], [
                    'name' => 'id',
                    'unique' => false,
                ])
                ->addIndex(['qtype'], [
                    'name' => 'qtype',
                    'unique' => false,
                ])
                ->addIndex(['is_poll'], [
                    'name' => 'is_poll',
                    'unique' => false,
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}