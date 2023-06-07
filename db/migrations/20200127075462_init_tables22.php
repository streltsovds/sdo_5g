<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables22 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
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
        if (!$this->hasTable('subjects_feedback_users'))
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
        if (!$this->hasTable('subjects_quests'))
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
        if (!$this->hasTable('subjects_quizzes'))
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
        if (!$this->hasTable('subjects_resources'))
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
        if (!$this->hasTable('subjects_tasks'))
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
        if (!$this->hasTable('subscription_channels'))
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
        if (!$this->hasTable('subscription_entries'))
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
        if (!$this->hasTable('subscriptions'))
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
        if (!$this->hasTable('supervisors'))
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
        if (!$this->hasTable('suppliers'))
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
        if (!$this->hasTable('support_requests'))
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
        if (!$this->hasTable('tag'))
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
        if (!$this->hasTable('tag_ref'))
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
        if (!$this->hasTable('tasks'))
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
        if (!$this->hasTable('tasks_variants'))
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
        if (!$this->hasTable('tc_applications'))
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
        if (!$this->hasTable('tc_applications_impersonal'))
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
        if (!$this->hasTable('tc_corporate_learning'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}