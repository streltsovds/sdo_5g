<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables1 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('Courses'))
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
        if (!$this->hasTable('OPTIONS'))
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
        if (!$this->hasTable('Participants'))
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
        if (!$this->hasTable('People'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}