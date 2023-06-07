<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables17 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('recruit_planned_costs'))
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
        if (!$this->hasTable('recruit_providers'))
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
        if (!$this->hasTable('recruit_reservists'))
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
        if (!$this->hasTable('recruit_vacancies'))
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
        if (!$this->hasTable('recruit_vacancies_data_fields'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}