<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables12 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('list_files'))
            $this->table('list_files', [
                'id' => false,
                'primary_key' => ['file_id', 'kod'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('file_id', 'integer', [
                    'null' => false,
                ])
                ->addColumn('kod', 'string', [
                    'null' => false,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'file_id',
                    'limit' => 230,
                ])
                ->create();
        if (!$this->hasTable('load'))
            $this->table('load', [
                'id' => false,
                'primary_key' => ['load_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('load_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('load_date', 'datetime', [
                    'null' => true,
                    'after' => 'load_id',
                ])
                ->addColumn('sessions', 'integer', [
                    'null' => true,
                    'after' => 'load_date',
                ])
                ->addColumn('hdd', 'integer', [
                    'null' => true,
                    'after' => 'sessions',
                ])
                ->create();
        if (!$this->hasTable('logseance'))
            $this->table('logseance', [
                'id' => false,
                'primary_key' => ['stid', 'kod'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('stid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'signed' => false,
                ])
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'stid',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'mid',
                ])
                ->addColumn('tid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'cid',
                ])
                ->addColumn('kod', 'string', [
                    'null' => false,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'tid',
                    'limit' => 230,
                ])
                ->addColumn('number', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'kod',
                ])
                ->addColumn('time', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'number',
                ])
                ->addColumn('bal', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'time',
                ])
                ->addColumn('balmax', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'bal',
                ])
                ->addColumn('balmin', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'balmax',
                ])
                ->addColumn('good', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'balmin',
                ])
                ->addColumn('vopros', 'blob', [
                    'null' => true,
                    'after' => 'good',
                ])
                ->addColumn('otvet', 'blob', [
                    'null' => true,
                    'after' => 'vopros',
                ])
                ->addColumn('attach', 'blob', [
                    'null' => true,
                    'after' => 'otvet',
                ])
                ->addColumn('filename', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'attach',
                ])
                ->addColumn('text', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'filename',
                ])
                ->addColumn('sheid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'text',
                ])
                ->addColumn('comments', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'sheid',
                ])
                ->addColumn('review', 'blob', [
                    'null' => true,
                    'after' => 'comments',
                ])
                ->addColumn('review_filename', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'review',
                ])
                ->addColumn('qtema', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'review_filename',
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->addIndex(['kod'], [
                    'name' => 'kod',
                    'unique' => false,
                ])
                ->addIndex(['sheid'], [
                    'name' => 'sheid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('loguser'))
            $this->table('loguser', [
                'id' => false,
                'primary_key' => ['stid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('stid', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'stid',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'mid',
                ])
                ->addColumn('tid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'cid',
                ])
                ->addColumn('balmax', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'tid',
                ])
                ->addColumn('balmin', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'balmax',
                ])
                ->addColumn('balmax2', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'balmin',
                ])
                ->addColumn('balmin2', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'balmax2',
                ])
                ->addColumn('bal', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'balmin2',
                ])
                ->addColumn('mark', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'bal',
                ])
                ->addColumn('questdone', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'mark',
                ])
                ->addColumn('questall', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'questdone',
                ])
                ->addColumn('qty', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'questall',
                ])
                ->addColumn('free', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'qty',
                ])
                ->addColumn('skip', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'free',
                ])
                ->addColumn('start', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'skip',
                ])
                ->addColumn('stop', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'start',
                ])
                ->addColumn('fulltime', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'stop',
                ])
                ->addColumn('moder', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'fulltime',
                ])
                ->addColumn('needmoder', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'moder',
                ])
                ->addColumn('status', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'needmoder',
                ])
                ->addColumn('moderby', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'status',
                ])
                ->addColumn('modertime', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'moderby',
                ])
                ->addColumn('teachertest', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'modertime',
                ])
                ->addColumn('log', 'blob', [
                    'null' => true,
                    'after' => 'teachertest',
                ])
                ->addColumn('sheid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'log',
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->addIndex(['cid'], [
                    'name' => 'cid',
                    'unique' => false,
                ])
                ->addIndex(['tid'], [
                    'name' => 'tid',
                    'unique' => false,
                ])
                ->addIndex(['sheid'], [
                    'name' => 'sheid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('managers'))
            $this->table('managers', [
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
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'id',
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('meetings'))
            $this->table('meetings', [
                'id' => false,
                'primary_key' => ['meeting_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('meeting_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('title', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'meeting_id',
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
                ->addColumn('typeID', 'integer', [
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
                ->addColumn('project_id', 'integer', [
                    'null' => true,
                    'after' => 'CID',
                ])
                ->addColumn('startday', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'project_id',
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
                ->addColumn('cond_project_id', 'string', [
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
                    'after' => 'cond_project_id',
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
                ->addColumn('moderator', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'rid',
                ])
                ->addColumn('gid', 'integer', [
                    'null' => true,
                    'default' => '-1',
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
                ->addIndex(['project_id'], [
                    'name' => 'project_id',
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