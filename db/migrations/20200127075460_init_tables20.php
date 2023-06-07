<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables20 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('soap_activities'))
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
        if (!$this->hasTable('specializations'))
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
        if (!$this->hasTable('staff_units'))
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
        if (!$this->hasTable('state_of_process'))
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
        if (!$this->hasTable('state_of_process_data'))
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
        if (!$this->hasTable('states'))
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
        if (!$this->hasTable('storage'))
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
        if (!$this->hasTable('storage_filesystem'))
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
        if (!$this->hasTable('structure_of_organ'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}