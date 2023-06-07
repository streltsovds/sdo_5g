<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables23 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('tc_corporate_learning_participant'))
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
        if (!$this->hasTable('tc_department_applications'))
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
        if (!$this->hasTable('tc_document'))
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
        if (!$this->hasTable('tc_feedbacks'))
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
        if (!$this->hasTable('tc_prefixes'))
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
        if (!$this->hasTable('tc_provider_contacts'))
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
        if (!$this->hasTable('tc_provider_files'))
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
        if (!$this->hasTable('tc_provider_rooms'))
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
        if (!$this->hasTable('tc_provider_scmanagers'))
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
        if (!$this->hasTable('tc_provider_teachers'))
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
        if (!$this->hasTable('tc_provider_teachers2subjects'))
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
        if (!$this->hasTable('tc_providers'))
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
        if (!$this->hasTable('tc_providers_subjects'))
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
        if (!$this->hasTable('tc_session_departments'))
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
        if (!$this->hasTable('tc_sessions'))
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
        if (!$this->hasTable('tc_sessions_quarter'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}