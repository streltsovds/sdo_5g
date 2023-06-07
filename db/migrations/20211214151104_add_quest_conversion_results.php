<?php

use Phinx\Migration\AbstractMigration;

class AddQuestConversionResults extends AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('quest_category_conversion_results'))
            $this->table('quest_category_conversion_results', [
                'id' => false,
                'primary_key' => ['conversion_result_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('conversion_result_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('category_id', 'integer', [
                    'null' => true,
                    'after' => 'conversion_result_id',
                ])
                ->addColumn('attempt_id', 'integer', [
                    'null' => true,
                    'after' => 'category_id',
                ])
                ->addColumn('score_raw', 'integer', [
                    'null' => true,
                    'after' => 'attempt_id',
                ])
                ->addColumn('result', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'score_raw',
                ])
                ->create();

    }

    public function down()
    {
        if ($this->hasTable('quest_category_conversion_results')) {
            $this->table('quest_category_conversion_results')->drop()->save();
        }
    }
}
