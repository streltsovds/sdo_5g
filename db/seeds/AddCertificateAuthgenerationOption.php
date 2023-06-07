<?php


use Phinx\Seed\AbstractSeed;

class AddCertificateAuthgenerationOption extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $this->table('OPTIONS')
            ->insert([
                'name' => 'generateCertificateFiles',
                'value' => '0',
            ])
            ->saveData();
    }
}
