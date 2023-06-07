<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables16 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('quest_settings'))
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
        if (!$this->hasTable('questionnaires'))
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
        if (!$this->hasTable('quizzes'))
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
        if (!$this->hasTable('quizzes_answers'))
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
        if (!$this->hasTable('quizzes_feedback'))
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
        if (!$this->hasTable('quizzes_results'))
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
        if (!$this->hasTable('recruit_actual_costs'))
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
        if (!$this->hasTable('recruit_application'))
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
        if (!$this->hasTable('recruit_candidate_hh_specializations'))
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
        if (!$this->hasTable('recruit_candidates'))
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
        if (!$this->hasTable('recruit_newcomer_file'))
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
        if (!$this->hasTable('recruit_newcomer_recruiters'))
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
        if (!$this->hasTable('recruit_newcomers'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}