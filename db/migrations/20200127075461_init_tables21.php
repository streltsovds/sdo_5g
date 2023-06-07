<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables21 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('structure_of_organ_history'))
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
        if (!$this->hasTable('structure_organ_list'))
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
        if (!$this->hasTable('study_groups'))
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
        if (!$this->hasTable('study_groups_auto'))
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
        if (!$this->hasTable('study_groups_courses'))
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
        if (!$this->hasTable('study_groups_custom'))
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
        if (!$this->hasTable('study_groups_programms'))
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
        if (!$this->hasTable('subjects'))
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
        if (!$this->hasTable('subjects_actual_costs'))
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
        if (!$this->hasTable('subjects_courses'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}