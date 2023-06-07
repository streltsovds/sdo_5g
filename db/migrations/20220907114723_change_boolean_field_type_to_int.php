<?php

use Phinx\Migration\AbstractMigration;

class ChangeBooleanFieldTypeToInt extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        if ($this->hasTable('at_profiles')) {
            $table = $this->table('at_profiles');
            if ($table->hasColumn('double_time')) {
                $table
                    ->changeColumn('double_time', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
        }

        if ($this->hasTable('claimants')) {
            $table = $this->table('claimants');
            if ($table->hasColumn('Teacher')) {
                $table
                    ->changeColumn('Teacher', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
            if ($table->hasColumn('status')) {
                $table
                    ->changeColumn('status', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
            if ($table->hasColumn('type')) {
                $table
                    ->changeColumn('type', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
        }

        if ($this->hasTable('programm_events')) {
            $table = $this->table('programm_events');
            if ($table->hasColumn('isElective')) {
                $table
                    ->changeColumn('isElective', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
            if ($table->hasColumn('hidden')) {
                $table
                    ->changeColumn('hidden', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
        }

        if ($this->hasTable('reports')) {
            $table = $this->table('reports');
            if ($table->hasColumn('status')) {
                $table
                    ->changeColumn('status', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
        }

        if ($this->hasTable('schedule')) {
            $table = $this->table('schedule');
            if ($table->hasColumn('pub')) {
                $table
                    ->changeColumn('pub', 'integer', [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
        }

        if ($this->hasTable('structure_of_organ')) {
            $table = $this->table('structure_of_organ');
            if ($table->hasColumn('employee_status')) {
                $table
                    ->changeColumn('employee_status', 'integer', ['null' => true])
                    ->update();
            }
        }

        if ($this->hasTable('structure_of_organ_history')) {
            $table = $this->table('structure_of_organ_history');
            if ($table->hasColumn('employee_status')) {
                $table
                    ->changeColumn('employee_status', 'integer', ['null' => true])
                    ->update();
            }
        }

        if ($this->hasTable('sessions')) {
            $table = $this->table('sessions');
            if ($table->hasColumn('cookie')) {
                $table
                    ->changeColumn('cookie', 'integer', ['null' => true])
                    ->update();
            }
            if ($table->hasColumn('js')) {
                $table
                    ->changeColumn('js', 'integer', ['null' => true])
                    ->update();
            }
        }

        if ($this->hasTable('webinar_questions')) {
            $table = $this->table('webinar_questions');
            if ($table->hasColumn('type')) {
                $table
                    ->changeColumn('type', 'integer', ['null' => true])
                    ->update();
            }
            if ($table->hasColumn('is_voted')) {
                $table
                    ->changeColumn('is_voted', 'integer', ['null' => true])
                    ->update();
            }
        }

        if ($this->hasTable('meetings')) {
            $table = $this->table('meetings');
            if ($table->hasColumn('pub')) {
                $table
                    ->changeColumn('pub', 'integer',  [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
        }

        if ($this->hasTable('storage_filesystem')) {
            $table = $this->table('storage_filesystem');
            if ($table->hasColumn('is_file')) {
                $table
                    ->changeColumn('is_file', 'integer', ['null' => true])
                    ->update();
            }
        }

        if ($this->hasTable('storage')) {
            $table = $this->table('storage');
            if ($table->hasColumn('is_file')) {
                $table
                    ->changeColumn('is_file', 'integer', ['null' => true])
                    ->update();
            }
        }

        if ($this->hasTable('quest_questions')) {
            $table = $this->table('quest_questions');
            if ($table->hasColumn('variants_use_wysiwyg')) {
                $table
                    ->changeColumn('variants_use_wysiwyg', 'integer',  [
                        'null' => true,
                        'default' => '0'
                    ])
                    ->update();
            }
        }
    }
}
