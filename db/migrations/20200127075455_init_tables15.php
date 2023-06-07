<?php

use Phinx\Db\Adapter\MysqlAdapter;

class InitTables15 extends Phinx\Migration\AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('projects_marks'))
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
        if (!$this->hasTable('providers'))
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
        if (!$this->hasTable('quest_attempt_clusters'))
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
        if (!$this->hasTable('quest_attempts'))
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
        if (!$this->hasTable('quest_categories'))
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
        if (!$this->hasTable('quest_category_results'))
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
        if (!$this->hasTable('quest_clusters'))
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
        if (!$this->hasTable('quest_question_quests'))
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
                    'null' => true,
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
        if (!$this->hasTable('quest_question_results'))
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
        if (!$this->hasTable('quest_question_variants'))
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
                    'null' => true,
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
        if (!$this->hasTable('quest_questions'))
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
    }

    public function down()
    {
        die('Foolproof here. If you want to delete all tables, do it manually.');
    }
}