<?php

use Phinx\Migration\AbstractMigration;

class AddQuestConversions extends AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('quest_category_conversions'))
            $this->table('quest_category_conversions', [
                'id' => false,
                'primary_key' => ['conversion_id'],
                'engine' => 'MyISAM',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
                ->addColumn('conversion_id', 'integer', [
                    'null' => false,
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('quest_id', 'integer', [
                    'null' => true,
                    'after' => 'conversion_id',
                ])
                ->addColumn('name', 'string', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'quest_id',
                ])
                ->addColumn('gender', 'integer', [
                    'null' => true,
                    'after' => 'name',
                ])
                ->addColumn('age_from', 'integer', [
                    'null' => true,
                    'after' => 'gender',
                ])
                ->addColumn('age_to', 'integer', [
                    'null' => true,
                    'after' => 'age_from',
                ])
                ->addColumn('formula', 'text', [
                    'null' => true,
                    'collation' => 'utf8mb4_unicode_ci',
                    'encoding' => 'utf8mb4',
                    'after' => 'age_to',
                ])
                ->create();
    }

    public function down()
    {
        if ($this->hasTable('quest_category_conversions')) {
            $this->table('quest_category_conversions')->drop()->save();
        }
    }
}
