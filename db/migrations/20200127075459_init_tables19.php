<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables19 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('scheduleID'))
            $this->table('scheduleID', [
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
                ->addColumn('SHEID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'SSID',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'SHEID',
                ])
                ->addColumn('begin_personal', 'datetime', [
                    'null' => true,
                    'after' => 'MID',
                ])
                ->addColumn('end_personal', 'datetime', [
                    'null' => true,
                    'after' => 'begin_personal',
                ])
                ->addColumn('gid', 'integer', [
                    'null' => true,
                    'after' => 'end_personal',
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
                ->addIndex(['SHEID'], [
                    'name' => 'SHEID',
                    'unique' => false,
                ])
                ->addIndex(['SHEID', 'MID'], [
                    'name' => 'SHEID_MID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('schedule_log'))
            $this->table('schedule_log', [
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
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'after' => 'id',
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'after' => 'lesson_id',
                ])
                ->addColumn('date_start', 'datetime', [
                    'null' => true,
                    'after' => 'user_id',
                ])
                ->create();
        if (!$this->hasTable('schedule_marks_history'))
            $this->table('schedule_marks_history', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('MID', 'integer', [
                    'null' => false,
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
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => false,
                ])
                ->addIndex(['SSID'], [
                    'name' => 'SSID',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('scorm_report'))
            $this->table('scorm_report', [
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
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'report_id',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'mid',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'cid',
                ])
                ->addColumn('report_data', 'blob', [
                    'null' => true,
                    'after' => 'lesson_id',
                ])
                ->addColumn('updated', 'datetime', [
                    'null' => true,
                    'after' => 'report_data',
                ])
                ->create();
        if (!$this->hasTable('scorm_tracklog'))
            $this->table('scorm_tracklog', [
                'id' => false,
                'primary_key' => ['trackID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('trackID', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'trackID',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'mid',
                ])
                ->addColumn('ModID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'cid',
                ])
                ->addColumn('McID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'ModID',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'McID',
                ])
                ->addColumn('trackdata', 'text', [
                    'null' => true,
                    'after' => 'lesson_id',
                ])
                ->addColumn('stop', 'datetime', [
                    'null' => true,
                    'after' => 'trackdata',
                ])
                ->addColumn('start', 'datetime', [
                    'null' => true,
                    'after' => 'stop',
                ])
                ->addColumn('score', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'start',
                ])
                ->addColumn('scoremax', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'score',
                ])
                ->addColumn('scoremin', 'float', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'scoremax',
                ])
                ->addColumn('status', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'scoremin',
                ])
                ->addIndex(['mid', 'lesson_id'], [
                    'name' => 'mid_lesson_id',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('seance'))
            $this->table('seance', [
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
                ->addColumn('kod', 'string', [
                    'null' => false,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'tid',
                    'limit' => 230,
                ])
                ->addColumn('attach', 'blob', [
                    'null' => true,
                    'after' => 'kod',
                ])
                ->addColumn('filename', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'attach',
                ])
                ->addColumn('text', 'blob', [
                    'null' => true,
                    'after' => 'filename',
                ])
                ->addColumn('time', 'timestamp', [
                    'null' => true,
                    'after' => 'text',
                ])
                ->addColumn('bal', 'float', [
                    'null' => true,
                    'after' => 'time',
                ])
                ->addColumn('lastbal', 'float', [
                    'null' => true,
                    'after' => 'bal',
                ])
                ->addColumn('comments', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'lastbal',
                ])
                ->addColumn('review', 'blob', [
                    'null' => true,
                    'after' => 'comments',
                ])
                ->addColumn('review_filename', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'review',
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->addIndex(['stid'], [
                    'name' => 'stid',
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
                ->addIndex(['kod'], [
                    'name' => 'kod',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('sections'))
            $this->table('sections', [
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
                    'identity' => 'enable',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'section_id',
                ])
                ->addColumn('project_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'subject_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'project_id',
                ])
                ->addColumn('order', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->create();
        if (!$this->hasTable('sequence_current'))
            $this->table('sequence_current', [
                'id' => false,
                'primary_key' => ['mid', 'cid', 'subject_id', 'lesson_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('mid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'signed' => false,
                ])
                ->addColumn('cid', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'mid',
                ])
                ->addColumn('current', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cid',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'current',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'after' => 'subject_id',
                ])
                ->create();
        if (!$this->hasTable('sequence_history'))
            $this->table('sequence_history', [
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
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'id',
                ])
                ->addColumn('cid', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'mid',
                ])
                ->addColumn('item', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'cid',
                ])
                ->addColumn('date', 'datetime', [
                    'null' => true,
                    'after' => 'item',
                ])
                ->addColumn('subject_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'date',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'subject_id',
                ])
                ->create();
        if (!$this->hasTable('session_guest'))
            $this->table('session_guest', [
                'id' => false,
                'primary_key' => ['session_guest_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('session_guest_id', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('start', 'datetime', [
                    'null' => true,
                    'after' => 'session_guest_id',
                ])
                ->addColumn('stop', 'datetime', [
                    'null' => true,
                    'after' => 'start',
                ])
                ->addIndex(['start'], [
                    'name' => 'start',
                    'unique' => false,
                ])
                ->addIndex(['stop'], [
                    'name' => 'stop',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('sessions'))
            $this->table('sessions', [
                'id' => false,
                'primary_key' => ['sessid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('sessid', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('sesskey', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'sessid',
                ])
                ->addColumn('mid', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'signed' => false,
                    'after' => 'sesskey',
                ])
                ->addColumn('course_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'mid',
                ])
                ->addColumn('lesson_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'course_id',
                ])
                ->addColumn('lesson_type', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'lesson_id',
                ])
                ->addColumn('start', 'datetime', [
                    'null' => true,
                    'after' => 'lesson_type',
                ])
                ->addColumn('stop', 'datetime', [
                    'null' => true,
                    'after' => 'start',
                ])
                ->addColumn('ip', 'string', [
                    'null' => true,
                    'default' => '',
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'stop',
                ])
                ->addColumn('logout', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'ip',
                ])
                ->addColumn('browser_name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'logout',
                ])
                ->addColumn('browser_version', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'browser_name',
                ])
                ->addColumn('flash_version', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'browser_version',
                ])
                ->addColumn('os', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'flash_version',
                ])
                ->addColumn('screen', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'os',
                ])
                ->addColumn('cookie', 'boolean', [
                    'null' => true,
                    'after' => 'screen',
                ])
                ->addColumn('js', 'boolean', [
                    'null' => true,
                    'after' => 'cookie',
                ])
                ->addColumn('java_version', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'js',
                ])
                ->addColumn('silverlight_version', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'java_version',
                ])
                ->addColumn('acrobat_reader_version', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'silverlight_version',
                ])
                ->addColumn('msxml_version', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'acrobat_reader_version',
                ])
                ->addIndex(['mid'], [
                    'name' => 'mid',
                    'unique' => false,
                ])
                ->addIndex(['start'], [
                    'name' => 'start',
                    'unique' => false,
                ])
                ->create();
        if (!$this->hasTable('simple_admins'))
            $this->table('simple_admins', [
                'id' => false,
                'primary_key' => ['AID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
                ->addColumn('AID', 'integer', [
                    'null' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('MID', 'integer', [
                    'null' => true,
                    'default' => '0',
                    'after' => 'AID',
                ])
                ->addIndex(['AID'], [
                    'name' => 'AID',
                    'unique' => true,
                ])
                ->addIndex(['MID'], [
                    'name' => 'MID',
                    'unique' => true,
                ])
                ->create();
        if (!$this->hasTable('simple_auth'))
            $this->table('simple_auth', [
                'id' => false,
                'primary_key' => ['auth_key'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('auth_key', 'string', [
                    'null' => false,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'limit' => 249,
                ])
                ->addColumn('user_id', 'integer', [
                    'null' => true,
                    'signed' => false,
                    'after' => 'auth_key',
                ])
                ->addColumn('link', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'user_id',
                ])
                ->addColumn('valid_before', 'datetime', [
                    'null' => true,
                    'after' => 'link',
                ])
                ->create();
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}