<?php

use Phinx\Migration\AbstractMigration;

class CreateRecommendedMaterialsCriteriaTable extends AbstractMigration
{
    public function change()
    {
        if(!$this->hasTable('material_criteria')) {
            $this->table('material_criteria', [
                'id' => false,
            ])
            ->addColumn('material_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('material_type', 'string', [
                'null' => false,
            ])
            ->addColumn('criterion_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('criterion_type', 'integer', [
                'null' => false,
            ])
            ->addIndex(['material_id', 'material_type'], [
                'name' => 'material_id_type',
            ])
            ->addIndex(['criterion_id', 'criterion_type'], [
                'name' => 'criterion_id_type',
            ])
            ->create();
        }
    }
}
