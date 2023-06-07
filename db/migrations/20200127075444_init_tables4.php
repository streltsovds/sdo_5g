<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables4 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('at_hh_regions'))
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
        if (!$this->hasTable('at_kpi_clusters'))
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
        if (!$this->hasTable('at_kpi_units'))
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
        if (!$this->hasTable('at_kpis'))
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
        if (!$this->hasTable('at_managers'))
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
        if (!$this->hasTable('at_profile_criterion_values'))
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
        if (!$this->hasTable('at_profile_education_requirement'))
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
        if (!$this->hasTable('at_profile_function'))
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
        if (!$this->hasTable('at_profile_kpis'))
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
        if (!$this->hasTable('at_profile_skills'))
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
        if (!$this->hasTable('at_profiles'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}