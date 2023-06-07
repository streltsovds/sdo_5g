<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        $this->table('Courses', [
                'id' => false,
                'primary_key' => ['CID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('CID', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('Title', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'CID',
            ])
            ->addColumn('Description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Title',
            ])
            ->addColumn('TypeDes', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'Description',
            ])
            ->addColumn('CD', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'TypeDes',
            ])
            ->addColumn('cBegin', 'datetime', [
                'null' => true,
                'after' => 'CD',
            ])
            ->addColumn('cEnd', 'datetime', [
                'null' => true,
                'after' => 'cBegin',
            ])
            ->addColumn('Fee', 'float', [
                'null' => true,
                'default' => '0',
                'after' => 'cEnd',
            ])
            ->addColumn('valuta', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'Fee',
            ])
            ->addColumn('Status', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'valuta',
            ])
            ->addColumn('createby', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Status',
            ])
            ->addColumn('createdate', 'datetime', [
                'null' => true,
                'after' => 'createby',
            ])
            ->addColumn('longtime', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'createdate',
            ])
            ->addColumn('did', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'longtime',
            ])
            ->addColumn('credits_student', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'did',
            ])
            ->addColumn('credits_teacher', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'credits_student',
            ])
            ->addColumn('locked', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'credits_teacher',
            ])
            ->addColumn('chain', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'locked',
            ])
            ->addColumn('is_poll', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'chain',
            ])
            ->addColumn('is_module_need_check', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'is_poll',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'is_module_need_check',
            ])
            ->addColumn('tree', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addColumn('progress', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'tree',
            ])
            ->addColumn('sequence', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'progress',
            ])
            ->addColumn('provider', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'sequence',
            ])
            ->addColumn('provider_options', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'provider',
            ])
            ->addColumn('planDate', 'datetime', [
                'null' => true,
                'after' => 'provider_options',
            ])
            ->addColumn('developStatus', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'planDate',
            ])
            ->addColumn('lastUpdateDate', 'datetime', [
                'null' => true,
                'after' => 'developStatus',
            ])
            ->addColumn('archiveDate', 'datetime', [
                'null' => true,
                'after' => 'lastUpdateDate',
            ])
            ->addColumn('services', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'archiveDate',
            ])
            ->addColumn('has_tree', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'services',
            ])
            ->addColumn('new_window', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'has_tree',
            ])
            ->addColumn('emulate', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'new_window',
            ])
            ->addColumn('format', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'emulate',
            ])
            ->addColumn('author', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'format',
            ])
            ->addColumn('emulate_scorm', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'author',
            ])
            ->addColumn('extra_navigation', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'emulate_scorm',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'extra_navigation',
            ])
            ->addColumn('entry_point', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('activity_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'entry_point',
            ])
            ->create();
        $this->table('OPTIONS', [
                'id' => false,
                'primary_key' => ['OptionID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('OptionID', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'OptionID',
            ])
            ->addColumn('value', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->create();
        $this->table('Participants', [
                'id' => false,
                'primary_key' => ['participant_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('participant_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('MID', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'participant_id',
            ])
            ->addColumn('CID', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'MID',
            ])
            ->addColumn('cgid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'CID',
            ])
            ->addColumn('Registered', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'cgid',
            ])
            ->addColumn('time_registered', 'timestamp', [
                'null' => true,
                'after' => 'Registered',
            ])
            ->addColumn('offline_course_path', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'time_registered',
            ])
            ->addColumn('time_ended', 'timestamp', [
                'null' => true,
                'after' => 'offline_course_path',
            ])
            ->addColumn('time_ended_planned', 'timestamp', [
                'null' => true,
                'after' => 'time_ended',
            ])
            ->addColumn('begin_personal', 'datetime', [
                'null' => true,
                'after' => 'time_ended_planned',
            ])
            ->addColumn('end_personal', 'datetime', [
                'null' => true,
                'after' => 'begin_personal',
            ])
            ->addColumn('project_role', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'end_personal',
            ])
            ->addIndex(['MID', 'CID'], [
                'name' => 'MID_CID',
                'unique' => true,
            ])
            ->addIndex(['CID'], [
                'name' => 'CID',
                'unique' => false,
            ])
            ->addIndex(['MID'], [
                'name' => 'MID',
                'unique' => false,
            ])
            ->create();
        $this->table('People', [
                'id' => false,
                'primary_key' => ['MID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('MID', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('mid_external', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'MID',
            ])
            ->addColumn('LastName', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'mid_external',
            ])
            ->addColumn('FirstName', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'LastName',
            ])
            ->addColumn('LastNameLat', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'FirstName',
            ])
            ->addColumn('FirstNameLat', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'LastNameLat',
            ])
            ->addColumn('Patronymic', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'FirstNameLat',
            ])
            ->addColumn('Registered', 'datetime', [
                'null' => true,
                'after' => 'Patronymic',
            ])
            ->addColumn('Course', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'Registered',
            ])
            ->addColumn('EMail', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Course',
            ])
            ->addColumn('email_confirmed', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'EMail',
            ])
            ->addColumn('Phone', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email_confirmed',
            ])
            ->addColumn('Information', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Phone',
            ])
            ->addColumn('Address', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Information',
            ])
            ->addColumn('Fax', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Address',
            ])
            ->addColumn('Login', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Fax',
            ])
            ->addColumn('Domain', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Login',
            ])
            ->addColumn('Password', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Domain',
            ])
            ->addColumn('javapassword', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'Password',
            ])
            ->addColumn('Age', 'integer', [
                'null' => true,
                'after' => 'javapassword',
            ])
            ->addColumn('BirthDate', 'datetime', [
                'null' => true,
                'after' => 'Age',
            ])
            ->addColumn('CellularNumber', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'BirthDate',
            ])
            ->addColumn('ICQNumber', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'CellularNumber',
            ])
            ->addColumn('Gender', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'ICQNumber',
            ])
            ->addColumn('last', 'biginteger', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'Gender',
            ])
            ->addColumn('countlogin', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'last',
            ])
            ->addColumn('rnid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'countlogin',
            ])
            ->addColumn('Position', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'rnid',
            ])
            ->addColumn('PositionDate', 'datetime', [
                'null' => true,
                'after' => 'Position',
            ])
            ->addColumn('PositionPrev', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'PositionDate',
            ])
            ->addColumn('invalid_login', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'PositionPrev',
            ])
            ->addColumn('isAD', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'invalid_login',
            ])
            ->addColumn('polls', 'blob', [
                'null' => true,
                'after' => 'isAD',
            ])
            ->addColumn('Access_Level', 'integer', [
                'null' => true,
                'default' => '5',
                'signed' => false,
                'after' => 'polls',
            ])
            ->addColumn('rang', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'Access_Level',
            ])
            ->addColumn('preferred_lang', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'rang',
            ])
            ->addColumn('blocked', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'preferred_lang',
            ])
            ->addColumn('block_message', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'blocked',
            ])
            ->addColumn('head_mid', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'block_message',
            ])
            ->addColumn('force_password', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'head_mid',
            ])
            ->addColumn('lang', 'string', [
                'null' => true,
                'default' => 'rus',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'force_password',
            ])
            ->addColumn('need_edit', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lang',
            ])
            ->addColumn('email_backup', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'need_edit',
            ])
            ->addColumn('data_agreement', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'email_backup',
            ])
            ->addColumn('dublicate', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'data_agreement',
            ])
            ->addColumn('duplicate_of', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'dublicate',
            ])
            ->addColumn('contact_displayed', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'duplicate_of',
            ])
            ->addIndex(['MID', 'blocked'], [
                'name' => 'mid_blocked',
                'unique' => false,
            ])
            ->create();
        $this->table('Students', [
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
            ->addColumn('cgid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'CID',
            ])
            ->addColumn('Registered', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'cgid',
            ])
            ->addColumn('time_registered', 'timestamp', [
                'null' => true,
                'after' => 'Registered',
            ])
            ->addColumn('offline_course_path', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'time_registered',
            ])
            ->addColumn('time_ended', 'timestamp', [
                'null' => true,
                'after' => 'offline_course_path',
            ])
            ->addColumn('time_ended_planned', 'timestamp', [
                'null' => true,
                'after' => 'time_ended',
            ])
            ->addColumn('newcomer_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'time_ended_planned',
            ])
            ->addColumn('reserve_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'newcomer_id',
            ])
            ->addColumn('application_id', 'integer', [
                'null' => true,
                'after' => 'reserve_id',
            ])
            ->addColumn('notified', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'application_id',
            ])
            ->addColumn('comment', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'notified',
            ])
            ->addColumn('programm_event_user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'comment',
            ])
            ->addColumn('begin_personal', 'datetime', [
                'null' => true,
                'after' => 'programm_event_user_id',
            ])
            ->addColumn('end_personal', 'datetime', [
                'null' => true,
                'after' => 'begin_personal',
            ])
            ->addIndex(['MID', 'CID'], [
                'name' => 'MID_CID',
                'unique' => true,
            ])
            ->addIndex(['CID'], [
                'name' => 'CID',
                'unique' => false,
            ])
            ->addIndex(['MID'], [
                'name' => 'MID',
                'unique' => false,
            ])
            ->create();
        $this->table('Teachers', [
                'id' => false,
                'primary_key' => ['PID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('PID', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('MID', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'PID',
            ])
            ->addColumn('CID', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'MID',
            ])
            ->addIndex(['MID', 'CID'], [
                'name' => 'MID_CID',
                'unique' => true,
            ])
            ->addIndex(['MID'], [
                'name' => 'MID',
                'unique' => false,
            ])
            ->addIndex(['CID'], [
                'name' => 'CID',
                'unique' => false,
            ])
            ->create();
        $this->table('absence', [
                'id' => false,
                'primary_key' => ['absence_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('absence_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'absence_id',
            ])
            ->addColumn('user_external_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'after' => 'user_external_id',
            ])
            ->addColumn('absence_begin', 'datetime', [
                'null' => true,
                'after' => 'type',
            ])
            ->addColumn('absence_end', 'datetime', [
                'null' => true,
                'after' => 'absence_begin',
            ])
            ->create();
        $this->table('admins', [
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
                'null' => false,
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
        $this->table('agreements', [
                'id' => false,
                'primary_key' => ['agreement_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('agreement_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'agreement_id',
            ])
            ->addColumn('item_type', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('item_id', 'integer', [
                'null' => true,
                'after' => 'item_type',
            ])
            ->addColumn('agreement_type', 'integer', [
                'null' => true,
                'after' => 'item_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'agreement_type',
            ])
            ->create();
        $this->table('at_categories', [
                'id' => false,
                'primary_key' => ['category_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'category_id',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('category_id_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->create();
        $this->table('at_category_criterion_values', [
                'id' => false,
                'primary_key' => ['category_criterion_value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('category_criterion_value_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'category_criterion_value_id',
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'category_id',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'criterion_type',
            ])
            ->addColumn('value_id', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('value', 'integer', [
                'null' => true,
                'after' => 'value_id',
            ])
            ->addColumn('method', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'value',
            ])
            ->addIndex(['category_id'], [
                'name' => 'category_id',
                'unique' => false,
            ])
            ->addIndex(['criterion_id'], [
                'name' => 'criterion_id',
                'unique' => false,
            ])
            ->addIndex(['value_id'], [
                'name' => 'value_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_criteria', [
                'id' => false,
                'primary_key' => ['criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'criterion_id',
            ])
            ->addColumn('cluster_id', 'integer', [
                'null' => false,
                'after' => 'name',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'after' => 'cluster_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'category_id',
            ])
            ->addColumn('order', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'type',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'order',
            ])
            ->addColumn('doubt', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'status',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'doubt',
            ])
            ->addIndex(['cluster_id'], [
                'name' => 'cluster_id',
                'unique' => false,
            ])
            ->addIndex(['category_id'], [
                'name' => 'category_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_criteria_clusters', [
                'id' => false,
                'primary_key' => ['cluster_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cluster_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cluster_id',
            ])
            ->addColumn('order', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'name',
            ])
            ->create();
        $this->table('at_criteria_indicator_scale_values', [
                'id' => false,
                'primary_key' => ['criterion_indicator_value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('criterion_indicator_value_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('indicator_id', 'integer', [
                'null' => true,
                'after' => 'criterion_indicator_value_id',
            ])
            ->addColumn('value_id', 'integer', [
                'null' => true,
                'after' => 'indicator_id',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'value_id',
            ])
            ->addColumn('description_questionnaire', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addIndex(['indicator_id'], [
                'name' => 'indicator_id',
                'unique' => false,
            ])
            ->addIndex(['value_id'], [
                'name' => 'value_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_criteria_indicators', [
                'id' => false,
                'primary_key' => ['indicator_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('indicator_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'indicator_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'criterion_id',
            ])
            ->addColumn('name_questionnaire', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('description_positive', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name_questionnaire',
            ])
            ->addColumn('description_negative', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description_positive',
            ])
            ->addColumn('reverse', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'description_negative',
            ])
            ->addColumn('order', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'reverse',
            ])
            ->addColumn('doubt', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'order',
            ])
            ->addIndex(['criterion_id'], [
                'name' => 'criterion_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_criteria_kpi', [
                'id' => false,
                'primary_key' => ['criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'criterion_id',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('order', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'description',
            ])
            ->create();
        $this->table('at_criteria_personal', [
                'id' => false,
                'primary_key' => ['criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'criterion_id',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quest_id',
            ])
            ->addIndex(['quest_id'], [
                'name' => 'quest_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_criteria_scale_values', [
                'id' => false,
                'primary_key' => ['criterion_value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('criterion_value_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'criterion_value_id',
            ])
            ->addColumn('value_id', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'value_id',
            ])
            ->addIndex(['criterion_id'], [
                'name' => 'criterion_id',
                'unique' => false,
            ])
            ->addIndex(['value_id'], [
                'name' => 'value_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_criteria_test', [
                'id' => false,
                'primary_key' => ['criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('lft', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'criterion_id',
            ])
            ->addColumn('rgt', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lft',
            ])
            ->addColumn('level', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'rgt',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'level',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'name',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'quest_id',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('required', 'integer', [
                'null' => true,
                'after' => 'description',
            ])
            ->addColumn('validity', 'integer', [
                'null' => true,
                'after' => 'required',
            ])
            ->addColumn('employee_type', 'integer', [
                'null' => true,
                'after' => 'validity',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'employee_type',
            ])
            ->addIndex(['quest_id'], [
                'name' => 'quest_id',
                'unique' => false,
            ])
            ->addIndex(['subject_id'], [
                'name' => 'subject_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_evaluation_criteria', [
                'id' => false,
                'primary_key' => ['evaluation_type_id', 'criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('evaluation_type_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'after' => 'evaluation_type_id',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'criterion_id',
            ])
            ->addIndex(['quest_id'], [
                'name' => 'quest_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_evaluation_memo_results', [
                'id' => false,
                'primary_key' => ['evaluation_memo_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('evaluation_memo_result_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('evaluation_memo_id', 'integer', [
                'null' => true,
                'after' => 'evaluation_memo_result_id',
            ])
            ->addColumn('value', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'evaluation_memo_id',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => true,
                'after' => 'value',
            ])
            ->addIndex(['evaluation_memo_id'], [
                'name' => 'evaluation_memo_id',
                'unique' => false,
            ])
            ->addIndex(['session_event_id'], [
                'name' => 'session_event_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_evaluation_memos', [
                'id' => false,
                'primary_key' => ['evaluation_memo_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('evaluation_memo_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('evaluation_type_id', 'integer', [
                'null' => true,
                'after' => 'evaluation_memo_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'evaluation_type_id',
            ])
            ->addIndex(['evaluation_type_id'], [
                'name' => 'evaluation_type_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_evaluation_results', [
                'id' => false,
                'primary_key' => ['result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('result_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'result_id',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('session_user_id', 'integer', [
                'null' => true,
                'after' => 'session_event_id',
            ])
            ->addColumn('relation_type', 'integer', [
                'null' => true,
                'after' => 'session_user_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'relation_type',
            ])
            ->addColumn('value_id', 'integer', [
                'null' => true,
                'after' => 'position_id',
            ])
            ->addColumn('value_weight', 'float', [
                'null' => true,
                'after' => 'value_id',
            ])
            ->addColumn('indicators_status', 'integer', [
                'null' => true,
                'after' => 'value_weight',
            ])
            ->addColumn('custom_criterion_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'indicators_status',
            ])
            ->addColumn('custom_criterion_parent_id', 'integer', [
                'null' => true,
                'after' => 'custom_criterion_name',
            ])
            ->addIndex(['criterion_id'], [
                'name' => 'criterion_id',
                'unique' => false,
            ])
            ->addIndex(['session_event_id'], [
                'name' => 'session_event_id',
                'unique' => false,
            ])
            ->addIndex(['session_user_id'], [
                'name' => 'session_user_id',
                'unique' => false,
            ])
            ->addIndex(['position_id'], [
                'name' => 'position_id',
                'unique' => false,
            ])
            ->addIndex(['value_id'], [
                'name' => 'value_id',
                'unique' => false,
            ])
            ->addIndex(['custom_criterion_parent_id'], [
                'name' => 'custom_criterion_parent_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_evaluation_results_indicators', [
                'id' => false,
                'primary_key' => ['indicator_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('indicator_result_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('indicator_id', 'integer', [
                'null' => true,
                'after' => 'indicator_result_id',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'indicator_id',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('session_user_id', 'integer', [
                'null' => true,
                'after' => 'session_event_id',
            ])
            ->addColumn('relation_type', 'integer', [
                'null' => true,
                'after' => 'session_user_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'relation_type',
            ])
            ->addColumn('value_id', 'integer', [
                'null' => true,
                'after' => 'position_id',
            ])
            ->addIndex(['indicator_id'], [
                'name' => 'indicator_id',
                'unique' => false,
            ])
            ->addIndex(['criterion_id'], [
                'name' => 'criterion_id',
                'unique' => false,
            ])
            ->addIndex(['session_event_id'], [
                'name' => 'session_event_id',
                'unique' => false,
            ])
            ->addIndex(['session_user_id'], [
                'name' => 'session_user_id',
                'unique' => false,
            ])
            ->addIndex(['position_id'], [
                'name' => 'position_id',
                'unique' => false,
            ])
            ->addIndex(['value_id'], [
                'name' => 'value_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_evaluation_type', [
                'id' => false,
                'primary_key' => ['evaluation_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('evaluation_type_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'evaluation_type_id',
            ])
            ->addColumn('comment', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('scale_id', 'integer', [
                'null' => true,
                'after' => 'comment',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'after' => 'scale_id',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'category_id',
            ])
            ->addColumn('vacancy_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('newcomer_id', 'integer', [
                'null' => true,
                'after' => 'vacancy_id',
            ])
            ->addColumn('reserve_id', 'integer', [
                'null' => true,
                'after' => 'newcomer_id',
            ])
            ->addColumn('method', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'reserve_id',
            ])
            ->addColumn('submethod', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'method',
            ])
            ->addColumn('methodData', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'submethod',
            ])
            ->addColumn('relation_type', 'integer', [
                'null' => true,
                'after' => 'methodData',
            ])
            ->addColumn('programm_type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'relation_type',
            ])
            ->addIndex(['scale_id'], [
                'name' => 'scale_id',
                'unique' => false,
            ])
            ->addIndex(['category_id'], [
                'name' => 'category_id',
                'unique' => false,
            ])
            ->addIndex(['profile_id'], [
                'name' => 'profile_id',
                'unique' => false,
            ])
            ->addIndex(['vacancy_id'], [
                'name' => 'vacancy_id',
                'unique' => false,
            ])
            ->addIndex(['newcomer_id'], [
                'name' => 'newcomer_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_hh_regions', [
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
            ])
            ->addColumn('parent', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'parent',
            ])
            ->create();
        $this->table('at_kpi_clusters', [
                'id' => false,
                'primary_key' => ['kpi_cluster_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('kpi_cluster_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'kpi_cluster_id',
            ])
            ->create();
        $this->table('at_kpi_units', [
                'id' => false,
                'primary_key' => ['kpi_unit_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('kpi_unit_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'kpi_unit_id',
            ])
            ->create();
        $this->table('at_kpis', [
                'id' => false,
                'primary_key' => ['kpi_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('kpi_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'kpi_id',
            ])
            ->addColumn('kpi_cluster_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'name',
            ])
            ->addColumn('kpi_unit_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'kpi_cluster_id',
            ])
            ->addColumn('is_typical', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'kpi_unit_id',
            ])
            ->addIndex(['kpi_cluster_id'], [
                'name' => 'kpi_cluster_id',
                'unique' => false,
            ])
            ->addIndex(['kpi_unit_id'], [
                'name' => 'kpi_unit_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_managers', [
                'id' => false,
                'primary_key' => ['atmanager_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('atmanager_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'atmanager_id',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_profile_criterion_values', [
                'id' => false,
                'primary_key' => ['profile_criterion_value_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('profile_criterion_value_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'profile_criterion_value_id',
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'profile_id',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'criterion_type',
            ])
            ->addColumn('value_id', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('value', 'integer', [
                'null' => true,
                'after' => 'value_id',
            ])
            ->addColumn('method', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'value',
            ])
            ->addColumn('importance', 'integer', [
                'null' => true,
                'after' => 'method',
            ])
            ->addColumn('value_backup', 'integer', [
                'null' => true,
                'after' => 'importance',
            ])
            ->addIndex(['profile_id'], [
                'name' => 'profile_id',
                'unique' => false,
            ])
            ->addIndex(['criterion_id'], [
                'name' => 'criterion_id',
                'unique' => false,
            ])
            ->addIndex(['value_id'], [
                'name' => 'value_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_profile_education_requirement', [
                'id' => false,
                'primary_key' => ['education_id', 'profile_id', 'education_type'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('education_id', 'integer', [
                'null' => false,
                'default' => '0',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'education_id',
            ])
            ->addColumn('education_type', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'profile_id',
            ])
            ->create();
        $this->table('at_profile_function', [
                'id' => false,
                'primary_key' => ['profile_function_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('profile_function_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'profile_function_id',
            ])
            ->addColumn('function_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addIndex(['profile_id'], [
                'name' => 'profile_id',
                'unique' => false,
            ])
            ->addIndex(['function_id'], [
                'name' => 'function_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_profile_kpis', [
                'id' => false,
                'primary_key' => ['profile_kpi_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('profile_kpi_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'profile_kpi_id',
            ])
            ->addColumn('kpi_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('weight', 'float', [
                'null' => true,
                'after' => 'kpi_id',
            ])
            ->addColumn('value_plan', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'weight',
            ])
            ->addIndex(['profile_id'], [
                'name' => 'profile_id',
                'unique' => false,
            ])
            ->addIndex(['kpi_id'], [
                'name' => 'kpi_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_profile_skills', [
                'id' => false,
                'primary_key' => ['profile_skill_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('profile_skill_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'profile_skill_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('skill', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addIndex(['profile_id'], [
                'name' => 'profile_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_profiles', [
                'id' => false,
                'primary_key' => ['profile_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('profile_id_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'profile_id',
            ])
            ->addColumn('position_id_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'profile_id_external',
            ])
            ->addColumn('department_id_external', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'position_id_external',
            ])
            ->addColumn('department_id', 'integer', [
                'null' => true,
                'after' => 'department_id_external',
            ])
            ->addColumn('department_path', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'department_id',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'after' => 'department_path',
            ])
            ->addColumn('programm_id', 'integer', [
                'null' => true,
                'after' => 'category_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'programm_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('shortname', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'shortname',
            ])
            ->addColumn('requirements', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addColumn('age_min', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'requirements',
            ])
            ->addColumn('age_max', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'age_min',
            ])
            ->addColumn('gender', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'age_max',
            ])
            ->addColumn('education', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'gender',
            ])
            ->addColumn('additional_education', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'education',
            ])
            ->addColumn('academic_degree', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'additional_education',
            ])
            ->addColumn('trips', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'academic_degree',
            ])
            ->addColumn('trips_duration', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'trips',
            ])
            ->addColumn('mobility', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'trips_duration',
            ])
            ->addColumn('experience', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mobility',
            ])
            ->addColumn('comments', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'experience',
            ])
            ->addColumn('progress', 'integer', [
                'null' => true,
                'after' => 'comments',
            ])
            ->addColumn('double_time', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'progress',
            ])
            ->addColumn('blocked', 'integer', [
                'null' => true,
                'after' => 'double_time',
            ])
            ->addColumn('psk', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'blocked',
            ])
            ->addColumn('base_id', 'integer', [
                'null' => true,
                'after' => 'psk',
            ])
            ->addIndex(['category_id'], [
                'name' => 'category_id',
                'unique' => false,
            ])
            ->addIndex(['programm_id'], [
                'name' => 'programm_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['base_id'], [
                'name' => 'base_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_ps_function', [
                'id' => false,
                'primary_key' => ['function_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('function_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('standard_id', 'integer', [
                'null' => true,
                'after' => 'function_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'standard_id',
            ])
            ->addIndex(['standard_id'], [
                'name' => 'standard_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_ps_requirement', [
                'id' => false,
                'primary_key' => ['requirement_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('requirement_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('function_id', 'integer', [
                'null' => true,
                'after' => 'requirement_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'after' => 'function_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addIndex(['function_id'], [
                'name' => 'function_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_ps_standard', [
                'id' => false,
                'primary_key' => ['standard_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('standard_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('number', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'standard_id',
            ])
            ->addColumn('code', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'number',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'code',
            ])
            ->addColumn('area', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('vid', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'area',
            ])
            ->addColumn('prikaz_number', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'vid',
            ])
            ->addColumn('prikaz_date', 'datetime', [
                'null' => true,
                'after' => 'prikaz_number',
            ])
            ->addColumn('minjust_number', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'prikaz_date',
            ])
            ->addColumn('minjust_date', 'datetime', [
                'null' => true,
                'after' => 'minjust_number',
            ])
            ->addColumn('sovet', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'minjust_date',
            ])
            ->addColumn('url', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'sovet',
            ])
            ->create();
        $this->table('at_relations', [
                'id' => false,
                'primary_key' => ['relation_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('relation_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'relation_id',
            ])
            ->addColumn('respondents', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('relation_type', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'respondents',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_session_event_attempts', [
                'id' => false,
                'primary_key' => ['attempt_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('attempt_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'attempt_id',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('method', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'session_event_id',
            ])
            ->addColumn('date_begin', 'datetime', [
                'null' => true,
                'after' => 'method',
            ])
            ->addColumn('date_end', 'datetime', [
                'null' => true,
                'after' => 'date_begin',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['session_event_id'], [
                'name' => 'session_event_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_session_event_lessons', [
                'id' => false,
                'primary_key' => ['session_event_id', 'lesson_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => false,
                'default' => '0',
            ])
            ->addColumn('lesson_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'session_event_id',
            ])
            ->addColumn('criteria', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'lesson_id',
            ])
            ->create();
        $this->table('at_session_events', [
                'id' => false,
                'primary_key' => ['session_event_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_event_id',
            ])
            ->addColumn('evaluation_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_id',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'evaluation_id',
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'criterion_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'criterion_type',
            ])
            ->addColumn('session_user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'position_id',
            ])
            ->addColumn('session_respondent_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_user_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_respondent_id',
            ])
            ->addColumn('respondent_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->addColumn('programm_event_user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'respondent_id',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'programm_event_user_id',
            ])
            ->addColumn('method', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quest_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'method',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'description',
            ])
            ->addColumn('date_begin', 'datetime', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('date_end', 'datetime', [
                'null' => true,
                'after' => 'date_begin',
            ])
            ->addColumn('date_filled', 'datetime', [
                'null' => true,
                'after' => 'date_end',
            ])
            ->addColumn('is_empty_quest', 'integer', [
                'null' => true,
                'after' => 'date_filled',
            ])
            ->addIndex(['session_id'], [
                'name' => 'session_id',
                'unique' => false,
            ])
            ->addIndex(['evaluation_id'], [
                'name' => 'evaluation_id',
                'unique' => false,
            ])
            ->addIndex(['criterion_id'], [
                'name' => 'criterion_id',
                'unique' => false,
            ])
            ->addIndex(['position_id'], [
                'name' => 'position_id',
                'unique' => false,
            ])
            ->addIndex(['session_user_id'], [
                'name' => 'session_user_id',
                'unique' => false,
            ])
            ->addIndex(['session_respondent_id'], [
                'name' => 'session_respondent_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['respondent_id'], [
                'name' => 'respondent_id',
                'unique' => false,
            ])
            ->addIndex(['programm_event_user_id'], [
                'name' => 'programm_event_user_id',
                'unique' => false,
            ])
            ->addIndex(['quest_id'], [
                'name' => 'quest_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_session_pair_ratings', [
                'id' => false,
                'primary_key' => ['session_id', 'criterion_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => false,
                'default' => '0',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'session_id',
            ])
            ->addColumn('session_user_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'criterion_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'session_user_id',
            ])
            ->addColumn('rating', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->addColumn('ratio', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'rating',
            ])
            ->addColumn('parent_soid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'ratio',
            ])
            ->addIndex(['session_user_id'], [
                'name' => 'session_user_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_session_pair_results', [
                'id' => false,
                'primary_key' => ['session_pair_id', 'criterion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('session_pair_id', 'integer', [
                'null' => false,
                'default' => '0',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_pair_id',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'session_event_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'criterion_id',
            ])
            ->addColumn('parent_soid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->addIndex(['session_event_id'], [
                'name' => 'session_event_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_session_pairs', [
                'id' => false,
                'primary_key' => ['session_pair_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('session_pair_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('session_event_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_pair_id',
            ])
            ->addColumn('first_user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_event_id',
            ])
            ->addColumn('second_user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'first_user_id',
            ])
            ->addIndex(['session_event_id'], [
                'name' => 'session_event_id',
                'unique' => false,
            ])
            ->addIndex(['first_user_id'], [
                'name' => 'first_user_id',
                'unique' => false,
            ])
            ->addIndex(['second_user_id'], [
                'name' => 'second_user_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_session_respondents', [
                'id' => false,
                'primary_key' => ['session_respondent_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('session_respondent_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'session_respondent_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'position_id',
            ])
            ->addColumn('progress', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->addIndex(['session_id'], [
                'name' => 'session_id',
                'unique' => false,
            ])
            ->addIndex(['position_id'], [
                'name' => 'position_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_session_user_criterion_values', [
                'id' => false,
                'primary_key' => ['session_user_id', 'criterion_id', 'criterion_type'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('session_user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
                'after' => 'session_user_id',
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => false,
                'default' => '1',
                'after' => 'criterion_id',
            ])
            ->addColumn('value', 'float', [
                'null' => true,
                'after' => 'criterion_type',
            ])
            ->create();
        $this->table('at_session_users', [
                'id' => false,
                'primary_key' => ['session_user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('session_user_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'session_user_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'position_id',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('process_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('vacancy_candidate_id', 'integer', [
                'null' => true,
                'after' => 'process_id',
            ])
            ->addColumn('newcomer_id', 'integer', [
                'null' => true,
                'after' => 'vacancy_candidate_id',
            ])
            ->addColumn('reserve_id', 'integer', [
                'null' => true,
                'after' => 'newcomer_id',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'reserve_id',
            ])
            ->addColumn('total_competence', 'float', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('total_kpi', 'float', [
                'null' => true,
                'after' => 'total_competence',
            ])
            ->addColumn('result_category', 'integer', [
                'null' => true,
                'after' => 'total_kpi',
            ])
            ->addIndex(['session_id'], [
                'name' => 'session_id',
                'unique' => false,
            ])
            ->addIndex(['position_id'], [
                'name' => 'position_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['profile_id'], [
                'name' => 'profile_id',
                'unique' => false,
            ])
            ->addIndex(['process_id'], [
                'name' => 'process_id',
                'unique' => false,
            ])
            ->addIndex(['vacancy_candidate_id'], [
                'name' => 'vacancy_candidate_id',
                'unique' => false,
            ])
            ->addIndex(['newcomer_id'], [
                'name' => 'newcomer_id',
                'unique' => false,
            ])
            ->addIndex(['reserve_id'], [
                'name' => 'reserve_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_sessions', [
                'id' => false,
                'primary_key' => ['session_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('programm_type', 'integer', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'programm_type',
            ])
            ->addColumn('shortname', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'shortname',
            ])
            ->addColumn('report_comment', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => true,
                'after' => 'report_comment',
            ])
            ->addColumn('begin_date', 'datetime', [
                'null' => true,
                'after' => 'cycle_id',
            ])
            ->addColumn('end_date', 'datetime', [
                'null' => true,
                'after' => 'begin_date',
            ])
            ->addColumn('initiator_id', 'integer', [
                'null' => true,
                'after' => 'end_date',
            ])
            ->addColumn('checked_soids', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'initiator_id',
            ])
            ->addColumn('state', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'checked_soids',
            ])
            ->addColumn('base_color', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'state',
            ])
            ->addColumn('goal', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'base_color',
            ])
            ->addIndex(['cycle_id'], [
                'name' => 'cycle_id',
                'unique' => false,
            ])
            ->addIndex(['initiator_id'], [
                'name' => 'initiator_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_user_kpi_results', [
                'id' => false,
                'primary_key' => ['user_kpi_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('user_kpi_result_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_kpi_id', 'integer', [
                'null' => true,
                'after' => 'user_kpi_result_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'user_kpi_id',
            ])
            ->addColumn('respondent_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('relation_type', 'integer', [
                'null' => true,
                'after' => 'respondent_id',
            ])
            ->addColumn('value_fact', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'relation_type',
            ])
            ->addColumn('comments', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'value_fact',
            ])
            ->addColumn('change_date', 'datetime', [
                'null' => true,
                'after' => 'comments',
            ])
            ->addIndex(['user_kpi_id'], [
                'name' => 'user_kpi_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['respondent_id'], [
                'name' => 'respondent_id',
                'unique' => false,
            ])
            ->create();
        $this->table('at_user_kpis', [
                'id' => false,
                'primary_key' => ['user_kpi_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('user_kpi_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'user_kpi_id',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('kpi_id', 'integer', [
                'null' => true,
                'after' => 'cycle_id',
            ])
            ->addColumn('weight', 'float', [
                'null' => true,
                'after' => 'kpi_id',
            ])
            ->addColumn('value_plan', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'weight',
            ])
            ->addColumn('value_fact', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'value_plan',
            ])
            ->addColumn('comments', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'value_fact',
            ])
            ->addColumn('begin_date', 'datetime', [
                'null' => true,
                'after' => 'comments',
            ])
            ->addColumn('end_date', 'datetime', [
                'null' => true,
                'after' => 'begin_date',
            ])
            ->addColumn('value_type', 'integer', [
                'null' => true,
                'after' => 'end_date',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['cycle_id'], [
                'name' => 'cycle_id',
                'unique' => false,
            ])
            ->addIndex(['kpi_id'], [
                'name' => 'kpi_id',
                'unique' => false,
            ])
            ->create();
        $this->table('blog', [
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
            ->addColumn('title', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('body', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'body',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'created',
            ])
            ->addColumn('subject_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'created_by',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'subject_name',
            ])
            ->addIndex(['subject_id'], [
                'name' => 'subject_id',
                'unique' => false,
            ])
            ->addIndex(['created_by'], [
                'name' => 'created_by',
                'unique' => false,
            ])
            ->create();
        $this->table('captcha', [
                'id' => false,
                'primary_key' => ['login'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('login', 'string', [
                'null' => false,
                'limit' => 249,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('attempts', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'login',
            ])
            ->addColumn('updated', 'datetime', [
                'null' => true,
                'after' => 'attempts',
            ])
            ->create();
        $this->table('certificates', [
                'id' => false,
                'primary_key' => ['certificate_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('certificate_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'certificate_id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'subject_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'created',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('organization', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addColumn('startdate', 'datetime', [
                'null' => true,
                'after' => 'organization',
            ])
            ->addColumn('enddate', 'datetime', [
                'null' => true,
                'after' => 'startdate',
            ])
            ->addColumn('filename', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'enddate',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'filename',
            ])
            ->addColumn('number', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addIndex(['user_id'], [
                'name' => 'USERID',
                'unique' => false,
            ])
            ->addIndex(['subject_id'], [
                'name' => 'SUBJECTID',
                'unique' => false,
            ])
            ->addIndex(['user_id', 'subject_id'], [
                'name' => 'USER_SUBJECT',
                'unique' => false,
            ])
            ->create();
        $this->table('chat_channels', [
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
            ->addColumn('subject_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
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
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'lesson_id',
            ])
            ->addColumn('start_date', 'datetime', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('end_date', 'datetime', [
                'null' => true,
                'after' => 'start_date',
            ])
            ->addColumn('show_history', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'end_date',
            ])
            ->addColumn('start_time', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'show_history',
            ])
            ->addColumn('end_time', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'start_time',
            ])
            ->addColumn('is_general', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'end_time',
            ])
            ->addIndex(['subject_id'], [
                'name' => 'subject_id',
                'unique' => false,
            ])
            ->addIndex(['lesson_id'], [
                'name' => 'lesson_id',
                'unique' => false,
            ])
            ->create();
        $this->table('chat_history', [
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
            ->addColumn('channel_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('sender', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'channel_id',
            ])
            ->addColumn('receiver', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'sender',
            ])
            ->addColumn('message', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'receiver',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'message',
            ])
            ->addIndex(['channel_id'], [
                'name' => 'channel_id',
                'unique' => false,
            ])
            ->addIndex(['created'], [
                'name' => 'created',
                'unique' => false,
            ])
            ->create();
        $this->table('chat_ref_users', [
                'id' => false,
                'primary_key' => ['channel_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('channel_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'signed' => false,
                'after' => 'channel_id',
            ])
            ->create();
        $this->table('claimants', [
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
            ->addColumn('base_subject', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'CID',
            ])
            ->addColumn('Teacher', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'base_subject',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'Teacher',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'created',
            ])
            ->addColumn('begin', 'datetime', [
                'null' => true,
                'after' => 'created_by',
            ])
            ->addColumn('end', 'datetime', [
                'null' => true,
                'after' => 'begin',
            ])
            ->addColumn('status', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'end',
            ])
            ->addColumn('type', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'status',
            ])
            ->addColumn('mid_external', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addColumn('lastname', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'mid_external',
            ])
            ->addColumn('firstname', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_bin',
                'encoding' => 'utf8mb4',
                'after' => 'lastname',
            ])
            ->addColumn('patronymic', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'firstname',
            ])
            ->addColumn('comments', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'patronymic',
            ])
            ->addColumn('dublicate', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'comments',
            ])
            ->addColumn('process_id', 'integer', [
                'null' => true,
                'after' => 'dublicate',
            ])
            ->addIndex(['MID', 'CID'], [
                'name' => 'MID_CID',
                'unique' => false,
            ])
            ->addIndex(['MID'], [
                'name' => 'MID',
                'unique' => false,
            ])
            ->addIndex(['CID'], [
                'name' => 'CID',
                'unique' => false,
            ])
            ->addIndex(['base_subject'], [
                'name' => 'base_subject',
                'unique' => false,
            ])
            ->create();
        $this->table('classifiers', [
                'id' => false,
                'primary_key' => ['classifier_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('classifier_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('lft', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'classifier_id',
            ])
            ->addColumn('rgt', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lft',
            ])
            ->addColumn('level', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'rgt',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'level',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'name',
            ])
            ->addColumn('classifier_id_external', 'integer', [
                'null' => true,
                'after' => 'type',
            ])
            ->addIndex(['lft'], [
                'name' => 'lft',
                'unique' => false,
            ])
            ->addIndex(['rgt'], [
                'name' => 'rgt',
                'unique' => false,
            ])
            ->create();
        $this->table('classifiers_images', [
                'id' => false,
                'primary_key' => ['classifier_image_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('classifier_image_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'classifier_image_id',
            ])
            ->addColumn('item_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'type',
            ])
            ->create();
        $this->table('classifiers_links', [
                'id' => false,
                'primary_key' => ['item_id', 'classifier_id', 'type'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('item_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('classifier_id', 'integer', [
                'null' => false,
                'after' => 'item_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => false,
                'after' => 'classifier_id',
            ])
            ->create();
        $this->table('classifiers_types', [
                'id' => false,
                'primary_key' => ['type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('type_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type_id',
            ])
            ->addColumn('link_types', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->create();
        $this->table('comments', [
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
            ->addColumn('activity_name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
                'after' => 'id',
            ])
            ->addColumn('subject_name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
                'after' => 'activity_name',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subject_name',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subject_id',
            ])
            ->addColumn('item_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->addColumn('message', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'item_id',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'message',
            ])
            ->addColumn('updated', 'datetime', [
                'null' => true,
                'after' => 'created',
            ])
            ->addIndex(['activity_name'], [
                'name' => 'activity_name',
                'unique' => false,
            ])
            ->addIndex(['subject_name'], [
                'name' => 'subject_name',
                'unique' => false,
            ])
            ->addIndex(['subject_id'], [
                'name' => 'subject_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['item_id'], [
                'name' => 'item_id',
                'unique' => false,
            ])
            ->create();
        $this->table('courses_marks', [
                'id' => false,
                'primary_key' => ['cid', 'mid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cid', 'integer', [
                'null' => false,
                'default' => '0',
                'signed' => false,
            ])
            ->addColumn('mid', 'integer', [
                'null' => false,
                'default' => '0',
                'signed' => false,
                'after' => 'cid',
            ])
            ->addColumn('mark', 'string', [
                'null' => true,
                'default' => '-1',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mid',
            ])
            ->addColumn('alias', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mark',
            ])
            ->addColumn('confirmed', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'alias',
            ])
            ->addColumn('comments', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'confirmed',
            ])
            ->addColumn('certificate_validity_period', 'integer', [
                'null' => true,
                'after' => 'comments',
            ])
            ->addColumn('date', 'datetime', [
                'null' => true,
                'after' => 'certificate_validity_period',
            ])
            ->addIndex(['cid'], [
                'name' => 'cid',
                'unique' => false,
            ])
            ->addIndex(['mid'], [
                'name' => 'mid',
                'unique' => false,
            ])
            ->create();
        $this->table('crontask', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('crontask_id', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('crontask_runtime', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'crontask_id',
            ])
            ->create();
        $this->table('curators', [
                'id' => false,
                'primary_key' => ['curator_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('curator_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('MID', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'curator_id',
            ])
            ->addColumn('project_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'MID',
            ])
            ->addIndex(['MID'], [
                'name' => 'MID',
                'unique' => false,
            ])
            ->create();
        $this->table('curators_options', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('unlimited_projects', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'user_id',
            ])
            ->addColumn('unlimited_classifiers', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'unlimited_projects',
            ])
            ->addColumn('assign_new_projects', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'unlimited_classifiers',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['unlimited_projects'], [
                'name' => 'unlimited_projects',
                'unique' => false,
            ])
            ->addIndex(['unlimited_classifiers'], [
                'name' => 'unlimited_classifiers',
                'unique' => false,
            ])
            ->addIndex(['assign_new_projects'], [
                'name' => 'assign_new_projects',
                'unique' => false,
            ])
            ->create();
        $this->table('curators_responsibilities', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('classifier_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->create();
        $this->table('cycles', [
                'id' => false,
                'primary_key' => ['cycle_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cycle_id',
            ])
            ->addColumn('begin_date', 'datetime', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('end_date', 'datetime', [
                'null' => true,
                'after' => 'begin_date',
            ])
            ->addColumn('newcomer_id', 'integer', [
                'null' => true,
                'after' => 'end_date',
            ])
            ->addColumn('reserve_id', 'integer', [
                'null' => true,
                'after' => 'newcomer_id',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'reserve_id',
            ])
            ->addColumn('year', 'integer', [
                'null' => true,
                'after' => 'type',
            ])
            ->addColumn('quarter', 'integer', [
                'null' => true,
                'after' => 'year',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'quarter',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'status',
            ])
            ->create();
        $this->table('dean_poll_users', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('lesson_id', 'integer', [
                'null' => true,
                'default' => '0',
            ])
            ->addColumn('head_mid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lesson_id',
            ])
            ->addColumn('student_mid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'head_mid',
            ])
            ->addIndex(['lesson_id'], [
                'name' => 'lesson_id',
                'unique' => false,
            ])
            ->addIndex(['head_mid'], [
                'name' => 'head_mid',
                'unique' => false,
            ])
            ->addIndex(['student_mid'], [
                'name' => 'student_mid',
                'unique' => false,
            ])
            ->create();
        $this->table('deans', [
                'id' => false,
                'primary_key' => ['DID'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('DID', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('MID', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'DID',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'MID',
            ])
            ->addIndex(['MID'], [
                'name' => 'MID',
                'unique' => false,
            ])
            ->create();
        $this->table('deans_options', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('unlimited_subjects', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'user_id',
            ])
            ->addColumn('unlimited_classifiers', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'unlimited_subjects',
            ])
            ->addColumn('assign_new_subjects', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'unlimited_classifiers',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['unlimited_subjects'], [
                'name' => 'unlimited_subjects',
                'unique' => false,
            ])
            ->addIndex(['unlimited_classifiers'], [
                'name' => 'unlimited_classifiers',
                'unique' => false,
            ])
            ->addIndex(['assign_new_subjects'], [
                'name' => 'assign_new_subjects',
                'unique' => false,
            ])
            ->create();
        $this->table('deans_responsibilities', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('classifier_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->create();
        $this->table('deputy_assign', [
                'id' => false,
                'primary_key' => ['assign_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('assign_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'assign_id',
            ])
            ->addColumn('deputy_user_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('begin_date', 'datetime', [
                'null' => true,
                'after' => 'deputy_user_id',
            ])
            ->addColumn('end_date', 'datetime', [
                'null' => true,
                'after' => 'begin_date',
            ])
            ->addColumn('not_active', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'end_date',
            ])
            ->create();
        $this->table('developers', [
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
        $this->table('eclass', [
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
            ->addColumn('lesson_id', 'integer', [
                'null' => true,
                'after' => 'id',
            ])
            ->addColumn('synced', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lesson_id',
            ])
            ->addColumn('sync_date', 'datetime', [
                'null' => true,
                'after' => 'synced',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'sync_date',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'after' => 'title',
            ])
            ->create();
        $this->table('employee', [
                'id' => false,
                'primary_key' => ['user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->create();
        $this->table('es_event_group_types', [
                'id' => false,
                'primary_key' => ['event_group_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('event_group_type_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'event_group_type_id',
            ])
            ->create();
        $this->table('es_event_groups', [
                'id' => false,
                'primary_key' => ['event_group_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('event_group_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('trigger_instance_id', 'integer', [
                'null' => true,
                'after' => 'event_group_id',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'limit' => 230,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'trigger_instance_id',
            ])
            ->addColumn('data', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addIndex(['trigger_instance_id', 'type'], [
                'name' => 'group_name',
                'unique' => true,
            ])
            ->create();
        $this->table('es_event_types', [
                'id' => false,
                'primary_key' => ['event_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('event_type_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'event_type_id',
            ])
            ->addColumn('event_group_type_id', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->create();
        $this->table('es_event_users', [
                'id' => false,
                'primary_key' => ['event_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('event_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'after' => 'event_id',
            ])
            ->addColumn('views', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->create();
        $this->table('es_events', [
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
            ->addColumn('event_type_id', 'integer', [
                'null' => true,
                'after' => 'event_id',
            ])
            ->addColumn('event_trigger_id', 'integer', [
                'null' => true,
                'after' => 'event_type_id',
            ])
            ->addColumn('event_group_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'event_trigger_id',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'event_group_id',
            ])
            ->addColumn('create_time', 'biginteger', [
                'null' => true,
                'after' => 'description',
            ])
            ->addIndex(['event_type_id'], [
                'name' => 'event_type_id',
                'unique' => false,
            ])
            ->create();
        $this->table('es_notify_types', [
                'id' => false,
                'primary_key' => ['notify_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('notify_type_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'notify_type_id',
            ])
            ->create();
        $this->table('es_user_notifies', [
                'id' => false,
                'primary_key' => ['user_id', 'notify_type_id', 'event_type_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('notify_type_id', 'integer', [
                'null' => false,
                'after' => 'user_id',
            ])
            ->addColumn('event_type_id', 'integer', [
                'null' => false,
                'after' => 'notify_type_id',
            ])
            ->addColumn('is_active', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'event_type_id',
            ])
            ->create();
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
        $this->table('projects_marks', [
                'id' => false,
                'primary_key' => ['cid', 'mid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cid', 'integer', [
                'null' => false,
                'default' => '0',
                'signed' => false,
            ])
            ->addColumn('mid', 'integer', [
                'null' => false,
                'default' => '0',
                'signed' => false,
                'after' => 'cid',
            ])
            ->addColumn('mark', 'string', [
                'null' => true,
                'default' => '-1',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mid',
            ])
            ->addColumn('alias', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mark',
            ])
            ->addColumn('confirmed', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'alias',
            ])
            ->addColumn('comments', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'confirmed',
            ])
            ->addIndex(['cid'], [
                'name' => 'cid',
                'unique' => false,
            ])
            ->addIndex(['mid'], [
                'name' => 'mid',
                'unique' => false,
            ])
            ->create();
        $this->table('providers', [
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
            ->addColumn('title', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('address', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('contacts', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'address',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'contacts',
            ])
            ->create();
        $this->table('quest_attempt_clusters', [
                'id' => false,
                'primary_key' => ['quest_attempt_cluster_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('quest_attempt_cluster_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('quest_attempt_id', 'integer', [
                'null' => true,
                'after' => 'quest_attempt_cluster_id',
            ])
            ->addColumn('cluster_id', 'integer', [
                'null' => true,
                'after' => 'quest_attempt_id',
            ])
            ->addColumn('score_percented', 'float', [
                'null' => true,
                'after' => 'cluster_id',
            ])
            ->create();
        $this->table('quest_attempts', [
                'id' => false,
                'primary_key' => ['attempt_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('attempt_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'attempt_id',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quest_id',
            ])
            ->addColumn('context_event_id', 'integer', [
                'null' => true,
                'after' => 'type',
            ])
            ->addColumn('context_type', 'integer', [
                'null' => true,
                'after' => 'context_event_id',
            ])
            ->addColumn('date_begin', 'datetime', [
                'null' => true,
                'after' => 'context_type',
            ])
            ->addColumn('date_end', 'datetime', [
                'null' => true,
                'after' => 'date_begin',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'date_end',
            ])
            ->addColumn('score_weighted', 'float', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('score_raw', 'integer', [
                'null' => true,
                'after' => 'score_weighted',
            ])
            ->addColumn('score_sum', 'float', [
                'null' => true,
                'after' => 'score_raw',
            ])
            ->addColumn('duration', 'integer', [
                'null' => true,
                'after' => 'score_sum',
            ])
            ->addColumn('is_resultative', 'integer', [
                'null' => true,
                'after' => 'duration',
            ])
            ->create();
        $this->table('quest_categories', [
                'id' => false,
                'primary_key' => ['category_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'after' => 'category_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quest_id',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('formula', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->create();
        $this->table('quest_category_results', [
                'id' => false,
                'primary_key' => ['category_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('category_result_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => true,
                'after' => 'category_result_id',
            ])
            ->addColumn('attempt_id', 'integer', [
                'null' => true,
                'after' => 'category_id',
            ])
            ->addColumn('score_raw', 'integer', [
                'null' => true,
                'after' => 'attempt_id',
            ])
            ->addColumn('result', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'score_raw',
            ])
            ->create();
        $this->table('quest_clusters', [
                'id' => false,
                'primary_key' => ['cluster_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cluster_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'after' => 'cluster_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quest_id',
            ])
            ->create();
        $this->table('quest_question_quests', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('question_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => true,
                'after' => 'question_id',
            ])
            ->addColumn('cluster_id', 'integer', [
                'null' => false,
                'after' => 'quest_id',
            ])
            ->addIndex(['question_id', 'quest_id'], [
                'name' => 'question_id_quest_id',
                'unique' => false,
            ])
            ->addIndex(['question_id'], [
                'name' => 'question_id',
                'unique' => false,
            ])
            ->addIndex(['quest_id'], [
                'name' => 'quest_id',
                'unique' => false,
            ])
            ->addIndex(['cluster_id'], [
                'name' => 'cluster_id',
                'unique' => false,
            ])
            ->create();
        $this->table('quest_question_results', [
                'id' => false,
                'primary_key' => ['question_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('question_result_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('attempt_id', 'integer', [
                'null' => true,
                'after' => 'question_result_id',
            ])
            ->addColumn('question_id', 'integer', [
                'null' => true,
                'after' => 'attempt_id',
            ])
            ->addColumn('variant', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'question_id',
            ])
            ->addColumn('free_variant', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'variant',
            ])
            ->addColumn('is_correct', 'integer', [
                'null' => true,
                'after' => 'free_variant',
            ])
            ->addColumn('score_weighted', 'float', [
                'null' => true,
                'after' => 'is_correct',
            ])
            ->addColumn('score_raw', 'integer', [
                'null' => true,
                'after' => 'score_weighted',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'after' => 'score_raw',
            ])
            ->addColumn('score_min', 'float', [
                'null' => true,
                'after' => 'category_id',
            ])
            ->addColumn('score_max', 'float', [
                'null' => true,
                'after' => 'score_min',
            ])
            ->addColumn('show_feedback', 'integer', [
                'null' => true,
                'after' => 'score_max',
            ])
            ->addColumn('comment', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'show_feedback',
            ])
            ->addIndex(['question_id'], [
                'name' => 'question_id',
                'unique' => false,
            ])
            ->create();
        $this->table('quest_question_variants', [
                'id' => false,
                'primary_key' => ['question_variant_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('question_variant_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('question_id', 'integer', [
                'null' => true,
                'after' => 'question_variant_id',
            ])
            ->addColumn('variant', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'question_id',
            ])
            ->addColumn('free_variant', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'variant',
            ])
            ->addColumn('shorttext', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'free_variant',
            ])
            ->addColumn('file_id', 'integer', [
                'null' => true,
                'after' => 'shorttext',
            ])
            ->addColumn('is_correct', 'integer', [
                'null' => true,
                'after' => 'file_id',
            ])
            ->addColumn('score_weighted', 'float', [
                'null' => true,
                'after' => 'is_correct',
            ])
            ->addColumn('score_raw', 'integer', [
                'null' => true,
                'after' => 'score_weighted',
            ])
            ->addColumn('category_id', 'integer', [
                'null' => false,
                'after' => 'score_raw',
            ])
            ->addColumn('weight', 'float', [
                'null' => true,
                'after' => 'category_id',
            ])
            ->addColumn('data', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'weight',
            ])
            ->addIndex(['question_id'], [
                'name' => 'question_id',
                'unique' => false,
            ])
            ->create();
        $this->table('quest_questions', [
                'id' => false,
                'primary_key' => ['question_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('question_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('cluster_id', 'integer', [
                'null' => true,
                'after' => 'question_id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'cluster_id',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('quest_type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addColumn('question', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quest_type',
            ])
            ->addColumn('shorttext', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'question',
            ])
            ->addColumn('mode_scoring', 'integer', [
                'null' => true,
                'after' => 'shorttext',
            ])
            ->addColumn('show_free_variant', 'integer', [
                'null' => true,
                'after' => 'mode_scoring',
            ])
            ->addColumn('shuffle_variants', 'integer', [
                'null' => true,
                'after' => 'show_free_variant',
            ])
            ->addColumn('file_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'shuffle_variants',
            ])
            ->addColumn('data', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'file_id',
            ])
            ->addColumn('score_min', 'float', [
                'null' => true,
                'default' => '0.000',
                'after' => 'data',
            ])
            ->addColumn('score_max', 'float', [
                'null' => true,
                'default' => '1.000',
                'after' => 'score_min',
            ])
            ->addColumn('variants_use_wysiwyg', 'boolean', [
                'null' => true,
                'default' => '0',
                'after' => 'score_max',
            ])
            ->addColumn('justification', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'variants_use_wysiwyg',
            ])
            ->create();
        $this->table('quest_settings', [
                'id' => false,
                'primary_key' => ['quest_id', 'scope_type', 'scope_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('scope_type', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'quest_id',
            ])
            ->addColumn('scope_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'scope_type',
            ])
            ->addColumn('info', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'scope_id',
            ])
            ->addColumn('cluster_limits', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'info',
            ])
            ->addColumn('comments', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cluster_limits',
            ])
            ->addColumn('mode_selection', 'integer', [
                'null' => true,
                'after' => 'comments',
            ])
            ->addColumn('mode_selection_questions', 'integer', [
                'null' => true,
                'after' => 'mode_selection',
            ])
            ->addColumn('mode_selection_all_shuffle', 'integer', [
                'null' => true,
                'after' => 'mode_selection_questions',
            ])
            ->addColumn('mode_passing', 'integer', [
                'null' => true,
                'after' => 'mode_selection_all_shuffle',
            ])
            ->addColumn('mode_display', 'integer', [
                'null' => true,
                'after' => 'mode_passing',
            ])
            ->addColumn('mode_display_clusters', 'integer', [
                'null' => true,
                'after' => 'mode_display',
            ])
            ->addColumn('mode_display_questions', 'integer', [
                'null' => true,
                'after' => 'mode_display_clusters',
            ])
            ->addColumn('show_result', 'integer', [
                'null' => true,
                'after' => 'mode_display_questions',
            ])
            ->addColumn('show_log', 'integer', [
                'null' => true,
                'after' => 'show_result',
            ])
            ->addColumn('limit_time', 'integer', [
                'null' => true,
                'after' => 'show_log',
            ])
            ->addColumn('limit_attempts', 'integer', [
                'null' => true,
                'after' => 'limit_time',
            ])
            ->addColumn('limit_clean', 'integer', [
                'null' => true,
                'after' => 'limit_attempts',
            ])
            ->addColumn('mode_test_page', 'integer', [
                'null' => true,
                'default' => '0',
                'comment' => '???????????? ????? ?????????? ????? (0 - ???????????????? ???????????, 1 - C???????? ???????????? ????? ??????????',
                'after' => 'limit_clean',
            ])
            ->addColumn('mode_self_test', 'integer', [
                'null' => true,
                'after' => 'mode_test_page',
            ])
            ->addIndex(['quest_id', 'scope_type', 'scope_id'], [
                'name' => 'quest_id',
                'unique' => true,
            ])
            ->create();
        $this->table('questionnaires', [
                'id' => false,
                'primary_key' => ['quest_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quest_id',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'type',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'description',
            ])
            ->addColumn('scale_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subject_id',
            ])
            ->addColumn('creator_role', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'scale_id',
            ])
            ->addColumn('displaycomment', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'creator_role',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'displaycomment',
            ])
            ->create();
        $this->table('quizzes', [
                'id' => false,
                'primary_key' => ['quiz_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('quiz_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quiz_id',
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
            ->addColumn('location', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'subject_id',
            ])
            ->addColumn('calc_rating', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'location',
            ])
            ->create();
        $this->table('quizzes_answers', [
                'id' => false,
                'primary_key' => ['quiz_id', 'question_id', 'answer_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('quiz_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('question_id', 'string', [
                'null' => false,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quiz_id',
                'limit' => 220,
            ])
            ->addColumn('question_title', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'question_id',
            ])
            ->addColumn('theme', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'question_title',
            ])
            ->addColumn('answer_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'theme',
            ])
            ->addColumn('answer_title', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'answer_id',
            ])
            ->create();
        $this->table('quizzes_feedback', [
                'id' => false,
                'primary_key' => ['user_id', 'subject_id', 'lesson_id'],
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
            ->addColumn('subject_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'user_id',
            ])
            ->addColumn('lesson_id', 'integer', [
                'null' => false,
                'default' => '0',
                'after' => 'subject_id',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lesson_id',
            ])
            ->addColumn('begin', 'datetime', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('end', 'datetime', [
                'null' => true,
                'after' => 'begin',
            ])
            ->addColumn('place', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'end',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'place',
            ])
            ->addColumn('subject_name', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('trainer', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_name',
            ])
            ->addColumn('trainer_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'trainer',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'trainer_id',
            ])
            ->create();
        $this->table('quizzes_results', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('lesson_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'user_id',
            ])
            ->addColumn('question_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'lesson_id',
                'limit' => 220,
            ])
            ->addColumn('answer_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'question_id',
            ])
            ->addColumn('freeanswer_data', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'answer_id',
            ])
            ->addColumn('quiz_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'freeanswer_data',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'quiz_id',
            ])
            ->addColumn('junior_id', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'subject_id',
            ])
            ->addColumn('link_id', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'junior_id',
            ])
            ->addIndex(['user_id', 'lesson_id', 'question_id', 'answer_id', 'link_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_actual_costs', [
                'id' => false,
                'primary_key' => ['actual_cost_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('actual_cost_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('month', 'integer', [
                'null' => true,
                'after' => 'actual_cost_id',
            ])
            ->addColumn('year', 'integer', [
                'null' => true,
                'after' => 'month',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'year',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ])
            ->addColumn('document_number', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cycle_id',
            ])
            ->addColumn('pay_date_document', 'datetime', [
                'null' => true,
                'after' => 'document_number',
            ])
            ->addColumn('pay_date_actual', 'datetime', [
                'null' => true,
                'after' => 'pay_date_document',
            ])
            ->addColumn('pay_amount', 'integer', [
                'null' => true,
                'after' => 'pay_date_actual',
            ])
            ->addColumn('payment_type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'pay_amount',
            ])
            ->addIndex(['provider_id'], [
                'name' => 'provider_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_application', [
                'id' => false,
                'primary_key' => ['recruit_application_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('recruit_application_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'recruit_application_id',
            ])
            ->addColumn('soid', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('department_path', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'soid',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'department_path',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'created',
            ])
            ->addColumn('vacancy_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'created_by',
            ])
            ->addColumn('vacancy_description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'vacancy_name',
            ])
            ->addColumn('programm_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'vacancy_description',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'programm_name',
            ])
            ->addColumn('saved_status', 'integer', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('recruiter_user_id', 'integer', [
                'null' => true,
                'after' => 'saved_status',
            ])
            ->addColumn('vacancy_id', 'integer', [
                'null' => true,
                'after' => 'recruiter_user_id',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['recruiter_user_id'], [
                'name' => 'recruiter_user_id',
                'unique' => false,
            ])
            ->addIndex(['vacancy_id'], [
                'name' => 'vacancy_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_candidate_hh_specializations', [
                'id' => false,
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('specialization_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('candidate_id', 'integer', [
                'null' => true,
                'after' => 'specialization_id',
            ])
            ->create();
        $this->table('recruit_candidates', [
                'id' => false,
                'primary_key' => ['candidate_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('candidate_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('candidate_external_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
                'after' => 'candidate_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'candidate_external_id',
            ])
            ->addColumn('source', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('file_id', 'integer', [
                'null' => true,
                'after' => 'source',
            ])
            ->addColumn('resume_external_url', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'file_id',
            ])
            ->addColumn('resume_external_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
                'after' => 'resume_external_url',
            ])
            ->addColumn('resume_json', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'resume_external_id',
            ])
            ->addColumn('resume_html', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'resume_json',
            ])
            ->addColumn('resume_date', 'datetime', [
                'null' => true,
                'after' => 'resume_html',
            ])
            ->addColumn('hh_area', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'resume_date',
            ])
            ->addColumn('hh_metro', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hh_area',
            ])
            ->addColumn('hh_salary', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hh_metro',
            ])
            ->addColumn('hh_total_experience', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hh_salary',
            ])
            ->addColumn('hh_education', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hh_total_experience',
            ])
            ->addColumn('hh_citizenship', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hh_education',
            ])
            ->addColumn('hh_age', 'integer', [
                'null' => true,
                'after' => 'hh_citizenship',
            ])
            ->addColumn('hh_gender', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hh_age',
            ])
            ->addColumn('hh_negotiation_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
                'after' => 'hh_gender',
            ])
            ->addColumn('spot_id', 'integer', [
                'null' => true,
                'after' => 'hh_negotiation_id',
            ])
            ->addIndex(['candidate_external_id'], [
                'name' => 'candidate_external_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['file_id'], [
                'name' => 'file_id',
                'unique' => false,
            ])
            ->addIndex(['resume_external_id'], [
                'name' => 'resume_external_id',
                'unique' => false,
            ])
            ->addIndex(['hh_negotiation_id'], [
                'name' => 'hh_negotiation_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_newcomer_file', [
                'id' => false,
                'primary_key' => ['newcomer_file_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('newcomer_file_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('newcomer_id', 'integer', [
                'null' => true,
                'after' => 'newcomer_file_id',
            ])
            ->addColumn('file_id', 'integer', [
                'null' => true,
                'after' => 'newcomer_id',
            ])
            ->addColumn('state_type', 'integer', [
                'null' => true,
                'after' => 'file_id',
            ])
            ->addIndex(['newcomer_id'], [
                'name' => 'newcomer_id',
                'unique' => false,
            ])
            ->addIndex(['file_id'], [
                'name' => 'file_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_newcomer_recruiters', [
                'id' => false,
                'primary_key' => ['newcomer_recruiter_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('newcomer_recruiter_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('newcomer_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'newcomer_recruiter_id',
            ])
            ->addColumn('recruiter_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'newcomer_id',
            ])
            ->addIndex(['newcomer_id'], [
                'name' => 'newcomer_id',
                'unique' => false,
            ])
            ->addIndex(['recruiter_id'], [
                'name' => 'recruiter_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_newcomers', [
                'id' => false,
                'primary_key' => ['newcomer_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('newcomer_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('state', 'integer', [
                'null' => true,
                'after' => 'newcomer_id',
            ])
            ->addColumn('state_change_date', 'datetime', [
                'null' => true,
                'after' => 'state',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'state_change_date',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('vacancy_candidate_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'vacancy_candidate_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('process_id', 'integer', [
                'null' => true,
                'after' => 'position_id',
            ])
            ->addColumn('department_path', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'process_id',
            ])
            ->addColumn('manager_id', 'integer', [
                'null' => true,
                'after' => 'department_path',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'manager_id',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'created',
            ])
            ->addColumn('result', 'integer', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('evaluation_user_id', 'integer', [
                'null' => true,
                'after' => 'result',
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
            ->addColumn('extended_to', 'datetime', [
                'null' => true,
                'after' => 'evaluation_start_send',
            ])
            ->addColumn('final_comment', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'extended_to',
            ])
            ->addColumn('welcome_training', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'final_comment',
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->addIndex(['vacancy_candidate_id'], [
                'name' => 'vacancy_candidate_id',
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
        $this->table('recruit_planned_costs', [
                'id' => false,
                'primary_key' => ['planned_cost_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('planned_cost_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('month', 'integer', [
                'null' => true,
                'after' => 'planned_cost_id',
            ])
            ->addColumn('year', 'integer', [
                'null' => true,
                'after' => 'month',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'year',
            ])
            ->addColumn('base_sum', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ])
            ->addColumn('corrected_sum', 'integer', [
                'null' => true,
                'after' => 'base_sum',
            ])
            ->addColumn('status', 'string', [
                'null' => true,
                'default' => 'new',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'corrected_sum',
            ])
            ->addIndex(['provider_id'], [
                'name' => 'provider_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_providers', [
                'id' => false,
                'primary_key' => ['provider_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'provider_id',
            ])
            ->addColumn('status', 'string', [
                'null' => true,
                'default' => '1',
                'after' => 'name',
            ])
            ->addColumn('locked', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'status',
            ])
            ->addColumn('userform', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'locked',
            ])
            ->addColumn('cost', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'userform',
            ])
            ->create();
        $this->table('recruit_reservists', [
                'id' => false,
                'primary_key' => ['reservist_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('reservist_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('company', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'reservist_id',
            ])
            ->addColumn('department', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'company',
            ])
            ->addColumn('brigade', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'department',
            ])
            ->addColumn('position', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'brigade',
            ])
            ->addColumn('fio', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'position',
            ])
            ->addColumn('gender', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'fio',
            ])
            ->addColumn('snils', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'gender',
            ])
            ->addColumn('birthday', 'datetime', [
                'null' => true,
                'after' => 'snils',
            ])
            ->addColumn('age', 'integer', [
                'null' => true,
                'after' => 'birthday',
            ])
            ->addColumn('region', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'age',
            ])
            ->addColumn('citizenship', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'region',
            ])
            ->addColumn('phone', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'citizenship',
            ])
            ->addColumn('phone_family', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'phone',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'phone_family',
            ])
            ->addColumn('position_experience', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'email',
            ])
            ->addColumn('sgc_experience', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'position_experience',
            ])
            ->addColumn('education', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'sgc_experience',
            ])
            ->addColumn('retraining', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'education',
            ])
            ->addColumn('training', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'retraining',
            ])
            ->addColumn('qualification_result', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'training',
            ])
            ->addColumn('rewards', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'qualification_result',
            ])
            ->addColumn('violations', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'rewards',
            ])
            ->addColumn('comments_dkz_pk', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'violations',
            ])
            ->addColumn('relocation_readiness', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'comments_dkz_pk',
            ])
            ->addColumn('evaluation_degree', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'relocation_readiness',
            ])
            ->addColumn('leadership', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'evaluation_degree',
            ])
            ->addColumn('productivity', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'leadership',
            ])
            ->addColumn('quality_information', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'productivity',
            ])
            ->addColumn('salary', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quality_information',
            ])
            ->addColumn('hourly_rate', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'salary',
            ])
            ->addColumn('annual_income_rks', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hourly_rate',
            ])
            ->addColumn('annual_income_no_rks', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'annual_income_rks',
            ])
            ->addColumn('monthly_income_rks', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'annual_income_no_rks',
            ])
            ->addColumn('monthly_income_no_rks', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'monthly_income_rks',
            ])
            ->addColumn('import_date', 'datetime', [
                'null' => true,
                'after' => 'monthly_income_no_rks',
            ])
            ->addColumn('importer_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'import_date',
            ])
            ->create();
        $this->table('recruit_vacancies', [
                'id' => false,
                'primary_key' => ['vacancy_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('vacancy_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('vacancy_external_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'vacancy_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'vacancy_external_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'position_id',
            ])
            ->addColumn('parent_position_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('parent_top_position_id', 'integer', [
                'null' => true,
                'after' => 'parent_position_id',
            ])
            ->addColumn('department_path', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'parent_top_position_id',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'department_path',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'created_by',
            ])
            ->addColumn('reason', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('create_date', 'datetime', [
                'null' => true,
                'after' => 'reason',
            ])
            ->addColumn('open_date', 'datetime', [
                'null' => true,
                'after' => 'create_date',
            ])
            ->addColumn('close_date', 'datetime', [
                'null' => true,
                'after' => 'open_date',
            ])
            ->addColumn('complete_date', 'datetime', [
                'null' => true,
                'after' => 'close_date',
            ])
            ->addColumn('complete_year', 'integer', [
                'null' => true,
                'after' => 'complete_date',
            ])
            ->addColumn('complete_month', 'integer', [
                'null' => true,
                'after' => 'complete_year',
            ])
            ->addColumn('work_place', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'complete_month',
            ])
            ->addColumn('work_mode', 'integer', [
                'null' => true,
                'after' => 'work_place',
            ])
            ->addColumn('trip_mode', 'integer', [
                'null' => true,
                'after' => 'work_mode',
            ])
            ->addColumn('salary', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'trip_mode',
            ])
            ->addColumn('bonus', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'salary',
            ])
            ->addColumn('subordinates', 'integer', [
                'null' => true,
                'after' => 'bonus',
            ])
            ->addColumn('subordinates_count', 'integer', [
                'null' => true,
                'after' => 'subordinates',
            ])
            ->addColumn('subordinates_categories', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subordinates_count',
            ])
            ->addColumn('tasks', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subordinates_categories',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'tasks',
            ])
            ->addColumn('age_min', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->addColumn('age_max', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'age_min',
            ])
            ->addColumn('gender', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'age_max',
            ])
            ->addColumn('education', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'gender',
            ])
            ->addColumn('requirements', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'education',
            ])
            ->addColumn('search_channels_corporate_site', 'integer', [
                'null' => true,
                'after' => 'requirements',
            ])
            ->addColumn('search_channels_recruit_sites', 'integer', [
                'null' => true,
                'after' => 'search_channels_corporate_site',
            ])
            ->addColumn('search_channels_papers', 'integer', [
                'null' => true,
                'after' => 'search_channels_recruit_sites',
            ])
            ->addColumn('search_channels_papers_list', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'search_channels_papers',
            ])
            ->addColumn('search_channels_universities', 'integer', [
                'null' => true,
                'after' => 'search_channels_papers_list',
            ])
            ->addColumn('search_channels_universities_list', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'search_channels_universities',
            ])
            ->addColumn('search_channels_workplace', 'integer', [
                'null' => true,
                'after' => 'search_channels_universities_list',
            ])
            ->addColumn('search_channels_email', 'integer', [
                'null' => true,
                'after' => 'search_channels_workplace',
            ])
            ->addColumn('search_channels_inner', 'integer', [
                'null' => true,
                'after' => 'search_channels_email',
            ])
            ->addColumn('search_channels_outer', 'integer', [
                'null' => true,
                'after' => 'search_channels_inner',
            ])
            ->addColumn('experience', 'integer', [
                'null' => true,
                'after' => 'search_channels_outer',
            ])
            ->addColumn('experience_other', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'experience',
            ])
            ->addColumn('experience_companies', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'experience_other',
            ])
            ->addColumn('workflow', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'experience_companies',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'workflow',
            ])
            ->addColumn('hh_vacancy_id', 'integer', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('superjob_vacancy_id', 'integer', [
                'null' => true,
                'after' => 'hh_vacancy_id',
            ])
            ->addColumn('recruit_application_id', 'integer', [
                'null' => true,
                'after' => 'superjob_vacancy_id',
            ])
            ->addColumn('deleted', 'integer', [
                'null' => true,
                'after' => 'recruit_application_id',
            ])
            ->addIndex(['position_id'], [
                'name' => 'position_id',
                'unique' => false,
            ])
            ->addIndex(['parent_position_id'], [
                'name' => 'parent_position_id',
                'unique' => false,
            ])
            ->addIndex(['parent_top_position_id'], [
                'name' => 'parent_top_position_id',
                'unique' => false,
            ])
            ->addIndex(['profile_id'], [
                'name' => 'profile_id',
                'unique' => false,
            ])
            ->addIndex(['session_id'], [
                'name' => 'session_id',
                'unique' => false,
            ])
            ->addIndex(['hh_vacancy_id'], [
                'name' => 'hh_vacancy_id',
                'unique' => false,
            ])
            ->addIndex(['superjob_vacancy_id'], [
                'name' => 'superjob_vacancy_id',
                'unique' => false,
            ])
            ->addIndex(['recruit_application_id'], [
                'name' => 'recruit_application_id',
                'unique' => false,
            ])
            ->create();
        $this->table('recruit_vacancies_data_fields', [
                'id' => false,
                'primary_key' => ['data_field_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('data_field_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('item_type', 'integer', [
                'null' => true,
                'after' => 'data_field_id',
            ])
            ->addColumn('item_id', 'integer', [
                'null' => true,
                'after' => 'item_type',
            ])
            ->addColumn('create_date', 'datetime', [
                'null' => true,
                'after' => 'item_id',
            ])
            ->addColumn('last_update_date', 'datetime', [
                'null' => true,
                'after' => 'create_date',
            ])
            ->addColumn('soid', 'integer', [
                'null' => true,
                'after' => 'last_update_date',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'soid',
            ])
            ->addColumn('vacancy_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->addColumn('who_obeys', 'integer', [
                'null' => true,
                'after' => 'vacancy_name',
            ])
            ->addColumn('subordinates_count', 'integer', [
                'null' => true,
                'after' => 'who_obeys',
            ])
            ->addColumn('work_mode', 'integer', [
                'null' => true,
                'after' => 'subordinates_count',
            ])
            ->addColumn('type_contract', 'integer', [
                'null' => true,
                'after' => 'work_mode',
            ])
            ->addColumn('work_place', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type_contract',
            ])
            ->addColumn('probationary_period', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'work_place',
            ])
            ->addColumn('salary', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'probationary_period',
            ])
            ->addColumn('career_prospects', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'salary',
            ])
            ->addColumn('reason', 'integer', [
                'null' => true,
                'after' => 'career_prospects',
            ])
            ->addColumn('tasks', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'reason',
            ])
            ->addColumn('education', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'tasks',
            ])
            ->addColumn('skills', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'education',
            ])
            ->addColumn('additional_education', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'skills',
            ])
            ->addColumn('knowledge_of_computer_programs', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'additional_education',
            ])
            ->addColumn('knowledge_of_foreign_languages', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'knowledge_of_computer_programs',
            ])
            ->addColumn('work_experience', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'knowledge_of_foreign_languages',
            ])
            ->addColumn('experience_other', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'work_experience',
            ])
            ->addColumn('personal_qualities', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'experience_other',
            ])
            ->addColumn('other_requirements', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'personal_qualities',
            ])
            ->addColumn('number_of_vacancies', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'other_requirements',
            ])
            ->addIndex(['item_id'], [
                'name' => 'item_id',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->create();
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
        $this->table('soap_activities', [
                'id' => false,
                'primary_key' => ['activity_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('activity_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('direction', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'activity_id',
            ])
            ->addColumn('request', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'direction',
            ])
            ->addColumn('response', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'request',
            ])
            ->addColumn('method', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'response',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'method',
            ])
            ->create();
        $this->table('specializations', [
                'id' => false,
                'primary_key' => ['spid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('spid', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'spid',
            ])
            ->addColumn('discription', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->create();
        $this->table('staff_units', [
                'id' => false,
                'primary_key' => ['staff_unit_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('staff_unit_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('staff_unit_id_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'staff_unit_id',
            ])
            ->addColumn('manager_staff_unit_id_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'staff_unit_id_external',
            ])
            ->addColumn('soid', 'integer', [
                'null' => true,
                'after' => 'manager_staff_unit_id_external',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'soid',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'profile_id',
            ])
            ->addColumn('quantity', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'name',
            ])
            ->addColumn('quantity_text', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quantity',
            ])
            ->create();
        $this->table('state_of_process', [
                'id' => false,
                'primary_key' => ['state_of_process_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('state_of_process_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('item_id', 'integer', [
                'null' => true,
                'after' => 'state_of_process_id',
            ])
            ->addColumn('process_id', 'integer', [
                'null' => true,
                'after' => 'item_id',
            ])
            ->addColumn('process_type', 'integer', [
                'null' => true,
                'after' => 'process_id',
            ])
            ->addColumn('current_state', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'process_type',
            ])
            ->addColumn('passed_states', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'current_state',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'passed_states',
            ])
            ->addColumn('params', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->addColumn('last_passed_state', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'params',
            ])
            ->addIndex(['item_id'], [
                'name' => 'item_id',
                'unique' => false,
            ])
            ->addIndex(['process_id'], [
                'name' => 'process_id',
                'unique' => false,
            ])
            ->create();
        $this->table('state_of_process_data', [
                'id' => false,
                'primary_key' => ['state_of_process_data_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('state_of_process_data_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('state_of_process_id', 'integer', [
                'null' => true,
                'after' => 'state_of_process_data_id',
            ])
            ->addColumn('programm_event_user_id', 'integer', [
                'null' => true,
                'after' => 'state_of_process_id',
            ])
            ->addColumn('state', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'programm_event_user_id',
            ])
            ->addColumn('begin_date_planned', 'datetime', [
                'null' => true,
                'after' => 'state',
            ])
            ->addColumn('begin_date', 'datetime', [
                'null' => true,
                'after' => 'begin_date_planned',
            ])
            ->addColumn('begin_by_user_id', 'integer', [
                'null' => true,
                'after' => 'begin_date',
            ])
            ->addColumn('begin_auto', 'integer', [
                'null' => true,
                'after' => 'begin_by_user_id',
            ])
            ->addColumn('end_date_planned', 'datetime', [
                'null' => true,
                'after' => 'begin_auto',
            ])
            ->addColumn('end_date', 'datetime', [
                'null' => true,
                'after' => 'end_date_planned',
            ])
            ->addColumn('end_by_user_id', 'integer', [
                'null' => true,
                'after' => 'end_date',
            ])
            ->addColumn('end_auto', 'integer', [
                'null' => true,
                'after' => 'end_by_user_id',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'end_auto',
            ])
            ->addColumn('comment', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->addColumn('comment_date', 'datetime', [
                'null' => true,
                'after' => 'comment',
            ])
            ->addColumn('comment_user_id', 'integer', [
                'null' => true,
                'after' => 'comment_date',
            ])
            ->create();
        $this->table('states', [
                'id' => false,
                'primary_key' => ['scope'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('scope', 'string', [
                'null' => false,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
            ])
            ->addColumn('scope_id', 'integer', [
                'null' => true,
                'after' => 'scope',
            ])
            ->addColumn('state', 'integer', [
                'null' => true,
                'after' => 'scope_id',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'state',
            ])
            ->create();
        $this->table('storage', [
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
            ->addColumn('parent_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('hash', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'parent_id',
            ])
            ->addColumn('phash', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'hash',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'phash',
            ])
            ->addColumn('subject_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_name',
            ])
            ->addColumn('alias', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('is_file', 'boolean', [
                'null' => true,
                'after' => 'alias',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'is_file',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'description',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('changed', 'datetime', [
                'null' => true,
                'after' => 'created',
            ])
            ->addIndex(['parent_id'], [
                'name' => 'parent_id',
                'unique' => false,
            ])
            ->addIndex(['subject_id'], [
                'name' => 'subject_id',
                'unique' => false,
            ])
            ->create();
        $this->table('storage_filesystem', [
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
            ->addColumn('parent_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'parent_id',
            ])
            ->addColumn('subject_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_name',
            ])
            ->addColumn('alias', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('is_file', 'boolean', [
                'null' => true,
                'after' => 'alias',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'is_file',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'description',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('changed', 'datetime', [
                'null' => true,
                'after' => 'created',
            ])
            ->addIndex(['parent_id'], [
                'name' => 'parent_id',
                'unique' => false,
            ])
            ->addIndex(['subject_id'], [
                'name' => 'subject_id',
                'unique' => false,
            ])
            ->create();
        $this->table('structure_of_organ', [
                'id' => false,
                'primary_key' => ['soid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('soid', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('soid_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'soid',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'soid_external',
            ])
            ->addColumn('code', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('mid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'code',
            ])
            ->addColumn('info', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mid',
            ])
            ->addColumn('owner_soid', 'integer', [
                'null' => true,
                'after' => 'info',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'owner_soid',
            ])
            ->addColumn('original_profile_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('agreem', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'original_profile_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'agreem',
            ])
            ->addColumn('own_results', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'type',
            ])
            ->addColumn('enemy_results', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'own_results',
            ])
            ->addColumn('display_results', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'enemy_results',
            ])
            ->addColumn('threshold', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'display_results',
            ])
            ->addColumn('specialization', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'threshold',
            ])
            ->addColumn('claimant', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'specialization',
            ])
            ->addColumn('org_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'claimant',
            ])
            ->addColumn('lft', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'org_id',
            ])
            ->addColumn('level', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lft',
            ])
            ->addColumn('rgt', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'level',
            ])
            ->addColumn('is_manager', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'rgt',
            ])
            ->addColumn('position_date', 'datetime', [
                'null' => true,
                'after' => 'is_manager',
            ])
            ->addColumn('blocked', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'position_date',
            ])
            ->addColumn('employment_type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'blocked',
            ])
            ->addColumn('employee_status', 'boolean', [
                'null' => true,
                'after' => 'employment_type',
            ])
            ->addColumn('manager_soid', 'integer', [
                'null' => true,
                'after' => 'employee_status',
            ])
            ->addColumn('staff_unit_id', 'integer', [
                'null' => true,
                'after' => 'manager_soid',
            ])
            ->addColumn('is_first_position', 'integer', [
                'null' => true,
                'after' => 'staff_unit_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'is_first_position',
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_integration2', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'deleted_at',
            ])
            ->addColumn('deputy', 'integer', [
                'null' => true,
                'after' => 'is_integration2',
            ])
            ->addIndex(['mid'], [
                'name' => 'mid',
                'unique' => false,
            ])
            ->addIndex(['owner_soid'], [
                'name' => 'owner_soid',
                'unique' => false,
            ])
            ->addIndex(['type'], [
                'name' => 'type',
                'unique' => false,
            ])
            ->addIndex(['claimant'], [
                'name' => 'claimant',
                'unique' => false,
            ])
            ->create();
        $this->table('structure_of_organ_history', [
                'id' => false,
                'primary_key' => ['soid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('soid', 'integer', [
                'null' => false,
            ])
            ->addColumn('soid_external', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'soid',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'soid_external',
            ])
            ->addColumn('code', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('mid', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'code',
            ])
            ->addColumn('info', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mid',
            ])
            ->addColumn('owner_soid', 'integer', [
                'null' => true,
                'after' => 'info',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'after' => 'owner_soid',
            ])
            ->addColumn('original_profile_id', 'integer', [
                'null' => true,
                'after' => 'profile_id',
            ])
            ->addColumn('agreem', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'original_profile_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'agreem',
            ])
            ->addColumn('own_results', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'type',
            ])
            ->addColumn('enemy_results', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'own_results',
            ])
            ->addColumn('display_results', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'enemy_results',
            ])
            ->addColumn('threshold', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'display_results',
            ])
            ->addColumn('specialization', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'threshold',
            ])
            ->addColumn('claimant', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'specialization',
            ])
            ->addColumn('org_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'claimant',
            ])
            ->addColumn('lft', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'org_id',
            ])
            ->addColumn('level', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'lft',
            ])
            ->addColumn('rgt', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'level',
            ])
            ->addColumn('is_manager', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'rgt',
            ])
            ->addColumn('position_date', 'datetime', [
                'null' => true,
                'after' => 'is_manager',
            ])
            ->addColumn('blocked', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'position_date',
            ])
            ->addColumn('employment_type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'blocked',
            ])
            ->addColumn('employee_status', 'boolean', [
                'null' => true,
                'after' => 'employment_type',
            ])
            ->addColumn('manager_soid', 'integer', [
                'null' => true,
                'after' => 'employee_status',
            ])
            ->addColumn('staff_unit_id', 'integer', [
                'null' => true,
                'after' => 'manager_soid',
            ])
            ->addColumn('is_first_position', 'integer', [
                'null' => true,
                'after' => 'staff_unit_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'is_first_position',
            ])
            ->addColumn('deleted_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_integration2', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'deleted_at',
            ])
            ->addColumn('deputy', 'integer', [
                'null' => true,
                'after' => 'is_integration2',
            ])
            ->addIndex(['mid'], [
                'name' => 'mid',
                'unique' => false,
            ])
            ->addIndex(['owner_soid'], [
                'name' => 'owner_soid',
                'unique' => false,
            ])
            ->addIndex(['type'], [
                'name' => 'type',
                'unique' => false,
            ])
            ->addIndex(['claimant'], [
                'name' => 'claimant',
                'unique' => false,
            ])
            ->create();
        $this->table('structure_organ_list', [
                'id' => false,
                'primary_key' => ['org_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('org_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
                'after' => 'org_id',
            ])
            ->addIndex(['name'], [
                'name' => 'name',
                'unique' => false,
            ])
            ->create();
        $this->table('study_groups', [
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
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'limit' => 249,
                'after' => 'group_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addIndex(['name'], [
                'name' => 'name',
                'unique' => false,
            ])
            ->create();
        $this->table('study_groups_auto', [
                'id' => false,
                'primary_key' => ['group_id', 'position_code', 'department_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('group_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('position_code', 'string', [
                'null' => false,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'group_id',
                'limit' => 220,
            ])
            ->addColumn('department_id', 'integer', [
                'null' => false,
                'signed' => false,
                'after' => 'position_code',
            ])
            ->create();
        $this->table('study_groups_courses', [
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
            ->addColumn('group_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'id',
            ])
            ->addColumn('course_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'group_id',
            ])
            ->addColumn('lesson_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'course_id',
            ])
            ->addIndex(['group_id'], [
                'name' => 'group_id',
                'unique' => false,
            ])
            ->addIndex(['course_id'], [
                'name' => 'course_id',
                'unique' => false,
            ])
            ->addIndex(['lesson_id'], [
                'name' => 'lesson_id',
                'unique' => false,
            ])
            ->create();
        $this->table('study_groups_custom', [
                'id' => false,
                'primary_key' => ['group_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('group_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'signed' => false,
                'after' => 'group_id',
            ])
            ->create();
        $this->table('study_groups_programms', [
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
            ->addColumn('group_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'id',
            ])
            ->addColumn('programm_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'group_id',
            ])
            ->addIndex(['group_id'], [
                'name' => 'group_id',
                'unique' => false,
            ])
            ->addIndex(['programm_id'], [
                'name' => 'programm_id',
                'unique' => false,
            ])
            ->create();
        $this->table('subjects', [
                'id' => false,
                'primary_key' => ['subid'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('subid', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('is_labor_safety', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subid',
            ])
            ->addColumn('external_id', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'is_labor_safety',
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
            ->addColumn('short_description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'supplier_id',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'short_description',
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
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'period',
            ])
            ->addColumn('period_restriction_type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'created',
            ])
            ->addColumn('last_updated', 'datetime', [
                'null' => true,
                'after' => 'period_restriction_type',
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
            ->addColumn('in_slider', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'threshold',
            ])
            ->addColumn('in_banner', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'in_slider',
            ])
            ->addColumn('create_from_tc_session', 'integer', [
                'null' => true,
                'after' => 'in_banner',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'create_from_tc_session',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ])
            ->addColumn('format', 'integer', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'format',
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'criterion_type',
            ])
            ->addColumn('category', 'integer', [
                'null' => true,
                'after' => 'created_by',
            ])
            ->addColumn('city', 'integer', [
                'null' => true,
                'after' => 'category',
            ])
            ->addColumn('primary_type', 'integer', [
                'null' => true,
                'after' => 'city',
            ])
            ->addColumn('mark_required', 'integer', [
                'null' => true,
                'after' => 'primary_type',
            ])
            ->addColumn('check_form', 'integer', [
                'null' => true,
                'after' => 'mark_required',
            ])
            ->addColumn('provider_type', 'integer', [
                'null' => true,
                'default' => '2',
                'after' => 'check_form',
            ])
            ->addColumn('after_training', 'integer', [
                'null' => true,
                'after' => 'provider_type',
            ])
            ->addColumn('feedback', 'integer', [
                'null' => true,
                'after' => 'after_training',
            ])
            ->addColumn('education_type', 'integer', [
                'null' => true,
                'default' => '2',
                'after' => 'feedback',
            ])
            ->addColumn('rating', 'float', [
                'null' => true,
                'after' => 'education_type',
            ])
            ->addColumn('direction_id', 'integer', [
                'null' => true,
                'after' => 'rating',
            ])
            ->addColumn('banner_url', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'direction_id',
            ])
            ->addColumn('auto_notification', 'integer', [
                'null' => false,
                'default' => 0,
                'after' => 'banner_url',
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
        $this->table('subjects_actual_costs', [
                'id' => false,
                'primary_key' => ['actual_cost_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('actual_cost_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('month', 'integer', [
                'null' => true,
                'after' => 'actual_cost_id',
            ])
            ->addColumn('year', 'integer', [
                'null' => true,
                'after' => 'month',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'year',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'after' => 'cycle_id',
            ])
            ->addColumn('document_number', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('pay_date_document', 'datetime', [
                'null' => true,
                'after' => 'document_number',
            ])
            ->addColumn('pay_date_actual', 'datetime', [
                'null' => true,
                'after' => 'pay_date_document',
            ])
            ->addColumn('pay_amount', 'integer', [
                'null' => true,
                'after' => 'pay_date_actual',
            ])
            ->addColumn('payment_type', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'pay_amount',
            ])
            ->addIndex(['provider_id'], [
                'name' => 'provider_id',
                'unique' => false,
            ])
            ->create();
        $this->table('subjects_courses', [
                'id' => false,
                'primary_key' => ['subject_id', 'course_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('course_id', 'integer', [
                'null' => false,
                'after' => 'subject_id',
            ])
            ->create();
        $this->table('subjects_exercises', [
                'id' => false,
                'primary_key' => ['subject_id', 'exercise_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('exercise_id', 'integer', [
                'null' => false,
                'after' => 'subject_id',
            ])
            ->create();
        $this->table('subjects_feedback_users', [
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
            ->create();
        $this->table('subjects_quests', [
                'id' => false,
                'primary_key' => ['subject_id', 'quest_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('quest_id', 'integer', [
                'null' => false,
                'after' => 'subject_id',
            ])
            ->create();
        $this->table('subjects_quizzes', [
                'id' => false,
                'primary_key' => ['subject_id', 'quiz_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('quiz_id', 'integer', [
                'null' => false,
                'after' => 'subject_id',
            ])
            ->create();
        $this->table('subjects_resources', [
                'id' => false,
                'primary_key' => ['subject_id', 'resource_id', 'subject'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('resource_id', 'integer', [
                'null' => false,
                'after' => 'subject_id',
            ])
            ->addColumn('subject', 'string', [
                'null' => false,
                'default' => 'subject',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'resource_id',
                'limit' => 220,
            ])
            ->create();
        $this->table('subjects_tasks', [
                'id' => false,
                'primary_key' => ['subject_id', 'task_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('task_id', 'integer', [
                'null' => false,
                'after' => 'subject_id',
            ])
            ->create();
        $this->table('subscription_channels', [
                'id' => false,
                'primary_key' => ['channel_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('channel_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('activity_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'channel_id',
            ])
            ->addColumn('subject_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'activity_name',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subject_name',
            ])
            ->addColumn('subject', 'string', [
                'null' => true,
                'default' => 'subject',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('lesson_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subject',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'lesson_id',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('link', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->create();
        $this->table('subscription_entries', [
                'id' => false,
                'primary_key' => ['entry_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('entry_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('channel_id', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'entry_id',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'channel_id',
            ])
            ->addColumn('link', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'link',
            ])
            ->addColumn('content', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addColumn('author', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'content',
            ])
            ->create();
        $this->table('subscriptions', [
                'id' => false,
                'primary_key' => ['subscription_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('subscription_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'subscription_id',
            ])
            ->addColumn('channel_id', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'user_id',
            ])
            ->create();
        $this->table('supervisors', [
                'id' => false,
                'primary_key' => ['user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->create();
        $this->table('suppliers', [
                'id' => false,
                'primary_key' => ['supplier_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('supplier_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'default' => '',
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'supplier_id',
            ])
            ->addColumn('address', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'title',
            ])
            ->addColumn('contacts', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'address',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'contacts',
            ])
            ->create();
        $this->table('support_requests', [
                'id' => false,
                'primary_key' => ['support_request_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('support_request_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('date_', 'datetime', [
                'null' => true,
                'after' => 'support_request_id',
            ])
            ->addColumn('theme', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'date_',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'theme',
            ])
            ->addColumn('problem_description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->addColumn('wanted_result', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'problem_description',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'wanted_result',
            ])
            ->addColumn('url', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'user_id',
            ])
            ->create();
        $this->table('tag', [
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
            ->addColumn('body', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->create();
        $this->table('tag_ref', [
                'id' => false,
                'primary_key' => ['tag_id', 'item_type', 'item_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('tag_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('item_type', 'integer', [
                'null' => false,
                'signed' => false,
                'after' => 'tag_id',
            ])
            ->addColumn('item_id', 'integer', [
                'null' => false,
                'signed' => false,
                'after' => 'item_type',
            ])
            ->create();
        $this->table('tasks', [
                'id' => false,
                'primary_key' => ['task_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('task_id', 'integer', [
                'null' => false,
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('title', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'task_id',
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
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'created_by',
            ])
            ->addColumn('location', 'integer', [
                'null' => true,
                'default' => '0',
                'signed' => false,
                'after' => 'subject_id',
            ])
            ->create();
        $this->table('tasks_variants', [
                'id' => false,
                'primary_key' => ['variant_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('variant_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('task_id', 'integer', [
                'null' => true,
                'after' => 'variant_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'task_id',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->create();
        $this->table('tc_applications', [
                'id' => false,
                'primary_key' => ['application_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('application_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'application_id',
            ])
            ->addColumn('session_quarter_id', 'integer', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('session_department_id', 'integer', [
                'null' => true,
                'after' => 'session_quarter_id',
            ])
            ->addColumn('department_application_id', 'integer', [
                'null' => true,
                'after' => 'session_department_id',
            ])
            ->addColumn('department_id', 'integer', [
                'null' => true,
                'after' => 'department_application_id',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'department_id',
            ])
            ->addColumn('position_id', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'position_id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ])
            ->addColumn('period', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'period',
            ])
            ->addColumn('category', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'category',
            ])
            ->addColumn('expire', 'datetime', [
                'null' => true,
                'after' => 'created',
            ])
            ->addColumn('primary_type', 'integer', [
                'null' => true,
                'after' => 'expire',
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => true,
                'after' => 'primary_type',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'criterion_type',
            ])
            ->addColumn('department_goal', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'status',
            ])
            ->addColumn('education_goal', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'department_goal',
            ])
            ->addColumn('cost_item', 'integer', [
                'null' => true,
                'after' => 'education_goal',
            ])
            ->addColumn('price', 'integer', [
                'null' => true,
                'after' => 'cost_item',
            ])
            ->addColumn('price_employee', 'integer', [
                'null' => true,
                'after' => 'price',
            ])
            ->addColumn('event_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'price_employee',
            ])
            ->addColumn('initiator', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'event_name',
            ])
            ->addColumn('payment_type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'initiator',
            ])
            ->addColumn('payment_percent', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'payment_type',
            ])
            ->addColumn('parent_application_id', 'integer', [
                'null' => true,
                'after' => 'payment_percent',
            ])
            ->addColumn('deleted', 'integer', [
                'null' => true,
                'after' => 'parent_application_id',
            ])
            ->addColumn('study_status', 'integer', [
                'null' => true,
                'after' => 'deleted',
            ])
            ->addColumn('origin_type', 'integer', [
                'null' => true,
                'after' => 'study_status',
            ])
            ->create();
        $this->table('tc_applications_impersonal', [
                'id' => false,
                'primary_key' => ['application_impersonal_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('application_impersonal_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'application_impersonal_id',
            ])
            ->addColumn('session_quarter_id', 'integer', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('session_department_id', 'integer', [
                'null' => true,
                'after' => 'session_quarter_id',
            ])
            ->addColumn('department_application_id', 'integer', [
                'null' => true,
                'after' => 'session_department_id',
            ])
            ->addColumn('department_id', 'integer', [
                'null' => true,
                'after' => 'department_application_id',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'department_id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ])
            ->addColumn('period', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subject_id',
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => true,
                'after' => 'period',
            ])
            ->addColumn('category', 'integer', [
                'null' => true,
                'after' => 'criterion_id',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'category',
            ])
            ->addColumn('expire', 'datetime', [
                'null' => true,
                'after' => 'created',
            ])
            ->addColumn('primary_type', 'integer', [
                'null' => true,
                'after' => 'expire',
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => true,
                'after' => 'primary_type',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'criterion_type',
            ])
            ->addColumn('cost_item', 'integer', [
                'null' => true,
                'after' => 'status',
            ])
            ->addColumn('price', 'integer', [
                'null' => true,
                'after' => 'cost_item',
            ])
            ->addColumn('quantity', 'integer', [
                'null' => true,
                'after' => 'price',
            ])
            ->addColumn('event_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'quantity',
            ])
            ->create();
        $this->table('tc_corporate_learning', [
                'id' => false,
                'primary_key' => ['corporate_learning_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('corporate_learning_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'corporate_learning_id',
            ])
            ->addColumn('month', 'datetime', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => true,
                'after' => 'month',
            ])
            ->addColumn('cost_for_organizer', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cycle_id',
            ])
            ->addColumn('organizer_id', 'integer', [
                'null' => true,
                'after' => 'cost_for_organizer',
            ])
            ->addColumn('manager_name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'organizer_id',
            ])
            ->addColumn('people_count', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'manager_name',
            ])
            ->addColumn('meeting_type', 'integer', [
                'null' => true,
                'after' => 'people_count',
            ])
            ->create();
        $this->table('tc_corporate_learning_participant', [
                'id' => false,
                'primary_key' => ['participant_id', 'corporate_learning_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('participant_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('corporate_learning_id', 'integer', [
                'null' => false,
                'after' => 'participant_id',
            ])
            ->addColumn('cost', 'integer', [
                'null' => true,
                'after' => 'corporate_learning_id',
            ])
            ->create();
        $this->table('tc_department_applications', [
                'id' => false,
                'primary_key' => ['department_application_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('department_application_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('department_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'department_application_id',
            ])
            ->addColumn('session_department_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'department_id',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_department_id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'session_id',
            ])
            ->addColumn('profile_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subject_id',
            ])
            ->addColumn('is_offsite', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'profile_id',
            ])
            ->addColumn('city_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'is_offsite',
            ])
            ->addColumn('category', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'city_id',
            ])
            ->addColumn('study_month', 'datetime', [
                'null' => true,
                'after' => 'category',
            ])
            ->addColumn('session_quarter_id', 'integer', [
                'null' => true,
                'after' => 'study_month',
            ])
            ->create();
        $this->table('tc_document', [
                'id' => false,
                'primary_key' => ['document_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('document_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'document_id',
            ])
            ->addColumn('add_date', 'datetime', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'add_date',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'subject_id',
            ])
            ->addColumn('filename', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->create();
        $this->table('tc_feedbacks', [
                'id' => false,
                'primary_key' => ['subject_id', 'user_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'after' => 'subject_id',
            ])
            ->addColumn('mark', 'integer', [
                'null' => true,
                'after' => 'user_id',
            ])
            ->addColumn('text', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mark',
            ])
            ->addColumn('date', 'datetime', [
                'null' => true,
                'after' => 'text',
            ])
            ->addColumn('mark_goal', 'integer', [
                'null' => true,
                'after' => 'date',
            ])
            ->addColumn('mark_goal2', 'integer', [
                'null' => true,
                'after' => 'mark_goal',
            ])
            ->addColumn('longtime', 'integer', [
                'null' => true,
                'after' => 'mark_goal2',
            ])
            ->addColumn('mark_usefull', 'integer', [
                'null' => true,
                'after' => 'longtime',
            ])
            ->addColumn('mark_motivation', 'integer', [
                'null' => true,
                'after' => 'mark_usefull',
            ])
            ->addColumn('mark_course', 'integer', [
                'null' => true,
                'after' => 'mark_motivation',
            ])
            ->addColumn('mark_teacher', 'integer', [
                'null' => true,
                'after' => 'mark_course',
            ])
            ->addColumn('mark_papers', 'integer', [
                'null' => true,
                'after' => 'mark_teacher',
            ])
            ->addColumn('mark_organization', 'integer', [
                'null' => true,
                'after' => 'mark_papers',
            ])
            ->addColumn('recomend', 'integer', [
                'null' => true,
                'after' => 'mark_organization',
            ])
            ->addColumn('mark_final', 'integer', [
                'null' => true,
                'after' => 'recomend',
            ])
            ->addColumn('text_goal', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'mark_final',
            ])
            ->addColumn('text_usefull', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'text_goal',
            ])
            ->addColumn('text_not_usefull', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'text_usefull',
            ])
            ->create();
        $this->table('tc_prefixes', [
                'id' => false,
                'primary_key' => ['prefix_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('prefix_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'prefix_id',
            ])
            ->addColumn('counter', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'name',
            ])
            ->addColumn('prefix_type', 'integer', [
                'null' => true,
                'default' => '1',
                'after' => 'counter',
            ])
            ->create();
        $this->table('tc_provider_contacts', [
                'id' => false,
                'primary_key' => ['contact_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('contact_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'contact_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'provider_id',
            ])
            ->addColumn('position', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('phone', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'position',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'phone',
            ])
            ->create();
        $this->table('tc_provider_files', [
                'id' => false,
                'primary_key' => ['provider_id', 'file_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('file_id', 'integer', [
                'null' => false,
                'after' => 'provider_id',
            ])
            ->create();
        $this->table('tc_provider_rooms', [
                'id' => false,
                'primary_key' => ['room_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('room_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'room_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'provider_id',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('places', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'type',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'places',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'description',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'created',
            ])
            ->create();
        $this->table('tc_provider_scmanagers', [
                'id' => false,
                'primary_key' => ['user_id', 'provider_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => false,
                'after' => 'user_id',
            ])
            ->create();
        $this->table('tc_provider_teachers', [
                'id' => false,
                'primary_key' => ['teacher_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('teacher_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'teacher_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'provider_id',
            ])
            ->addColumn('description', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('contacts', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'description',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'contacts',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'created',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'after' => 'created_by',
            ])
            ->create();
        $this->table('tc_provider_teachers2subjects', [
                'id' => false,
                'primary_key' => ['teacher_id', 'subject_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('teacher_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'teacher_id',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => false,
                'after' => 'provider_id',
            ])
            ->create();
        $this->table('tc_providers', [
                'id' => false,
                'primary_key' => ['provider_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'provider_id',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'after' => 'description',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'status',
            ])
            ->addColumn('address_legal', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addColumn('address_postal', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'address_legal',
            ])
            ->addColumn('inn', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'address_postal',
            ])
            ->addColumn('kpp', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'inn',
            ])
            ->addColumn('bik', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'kpp',
            ])
            ->addColumn('subscriber_fio', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'bik',
            ])
            ->addColumn('subscriber_position', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subscriber_fio',
            ])
            ->addColumn('subscriber_reason', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subscriber_position',
            ])
            ->addColumn('account', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'subscriber_reason',
            ])
            ->addColumn('account_corr', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'account',
            ])
            ->addColumn('created', 'datetime', [
                'null' => true,
                'after' => 'account_corr',
            ])
            ->addColumn('created_by', 'integer', [
                'null' => true,
                'after' => 'created',
            ])
            ->addColumn('create_from_tc_session', 'integer', [
                'null' => true,
                'after' => 'created_by',
            ])
            ->addColumn('department_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'create_from_tc_session',
            ])
            ->addColumn('dzo_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'department_id',
            ])
            ->addColumn('licence', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'dzo_id',
            ])
            ->addColumn('registration', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'licence',
            ])
            ->addColumn('pass_by', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'registration',
            ])
            ->addColumn('prefix_id', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'pass_by',
            ])
            ->addColumn('information', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'prefix_id',
            ])
            ->create();
        $this->table('tc_providers_subjects', [
                'id' => false,
                'primary_key' => ['provider_subject_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('provider_subject_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('subject_id', 'integer', [
                'null' => true,
                'after' => 'provider_subject_id',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'subject_id',
            ])
            ->create();
        $this->table('tc_session_departments', [
                'id' => false,
                'primary_key' => ['session_department_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'FIXED',
            ])
            ->addColumn('session_department_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('department_id', 'integer', [
                'null' => true,
                'after' => 'session_department_id',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'department_id',
            ])
            ->addColumn('session_quarter_id', 'integer', [
                'null' => true,
                'after' => 'session_id',
            ])
            ->addColumn('parent_session_department_id', 'integer', [
                'null' => true,
                'after' => 'session_quarter_id',
            ])
            ->create();
        $this->table('tc_sessions', [
                'id' => false,
                'primary_key' => ['session_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'session_id',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('date_begin', 'datetime', [
                'null' => true,
                'after' => 'cycle_id',
            ])
            ->addColumn('date_end', 'datetime', [
                'null' => true,
                'after' => 'date_begin',
            ])
            ->addColumn('norm', 'integer', [
                'null' => true,
                'after' => 'date_end',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'norm',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'status',
            ])
            ->addColumn('checked_items', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'checked_items',
            ])
            ->addColumn('responsible_id', 'integer', [
                'null' => true,
                'after' => 'provider_id',
            ])
            ->create();
        $this->table('tc_sessions_quarter', [
                'id' => false,
                'primary_key' => ['session_quarter_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('session_quarter_id', 'integer', [
                'null' => false,
                'identity' => 'enable',
            ])
            ->addColumn('session_id', 'integer', [
                'null' => true,
                'after' => 'session_quarter_id',
            ])
            ->addColumn('name', 'string', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'session_id',
            ])
            ->addColumn('cycle_id', 'integer', [
                'null' => true,
                'after' => 'name',
            ])
            ->addColumn('date_begin', 'datetime', [
                'null' => true,
                'after' => 'cycle_id',
            ])
            ->addColumn('date_end', 'datetime', [
                'null' => true,
                'after' => 'date_begin',
            ])
            ->addColumn('norm', 'integer', [
                'null' => true,
                'after' => 'date_end',
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'norm',
            ])
            ->addColumn('type', 'integer', [
                'null' => true,
                'default' => '0',
                'after' => 'status',
            ])
            ->addColumn('checked_items', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'type',
            ])
            ->addColumn('provider_id', 'integer', [
                'null' => true,
                'after' => 'checked_items',
            ])
            ->create();
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
