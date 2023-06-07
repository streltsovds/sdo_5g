<?php
class Scale_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->settings->scales;
    }
    
    /**
     * Создание шкалы оценивания
     * 
     * @param stdObj $scale
     * @return int ID созданной шкалы
     */
    public function createScale($scale)
    {
        
        $data = [
            'name' => $scale->name,
            'type' => [
                'type' => 'select',
                'value' => $scale->type
            ],
            'mode' => [
                'type' => 'select',
                'value' => $scale->mode
            ],
        ];

        $this->createEntity($data);
        $scaleId = $this->grabEntityId('name', $scale->name);

        if (isset($scale->values)) {

            $this->page = Codeception_Registry::get('config')->pages->scale->values;
            $this->I->click('td.grid-name a');
            $this->I->waitForText($scale->name, Codeception_Registry::get('config')->global->timeToWait);

            foreach ($scale->values as $value) {

                $data = [
                    'value' => $value->value,
                    'text' => $value->text,
                ];
                $this->createEntity($data, false);
            }
        }

        $this->rollback('delete from scales where scale_id = %d', $scaleId);
        $this->rollback('delete from scale_values where scale_id = %d', $scaleId);

        $this->page = Codeception_Registry::get('config')->pages->settings->scales;

        return $scaleId;
    }
}