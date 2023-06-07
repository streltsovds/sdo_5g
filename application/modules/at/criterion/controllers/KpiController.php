<?php
class Criterion_KpiController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_Kpi();
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'name_ASC');
        }

        $select = $this->getService('AtCriterion')->getSelect();

        $select->from(
            array(
                'c' => 'at_criteria_kpi'
            ),
            array(
                'c.criterion_id',
                'c.name',
                'c.description',
            )
        );

        $select->group(array(
            'c.criterion_id',
            'c.name',
            'c.description',
        ));

        $grid = $this->getGrid($select, array(
            'criterion_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Критерий оценики'),
            ),
            'description' => array(
                'title' => _('Что оцениваем'),
            ),
        ),
            array(
                'name' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'kpi',
            'action' => 'edit'
        ),
            array('criterion_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'kpi',
            'action' => 'delete'
        ),
            array('criterion_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'criterion',
                'controller' => 'kpi',
                'action' => 'delete-by',
            ),
            _('Удалить критерии оценки'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;
		$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $values = $form->getValues();
        $this->updateScaleValues($values);
        unset($values['criterion_id']);
        $res = $this->getService('AtCriterionKpi')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $this->updateScaleValues($values);
        $res = $this->getService('AtCriterionKpi')->update($values);
    }

    public function updateScaleValues(&$values)
    {
        if ($values['criterion_id']) {
            $this->getService('AtCriterionScaleValue')->deleteBy(array('criterion_id = ?' => $values['criterion_id']));
        }
        $copy = $values;
        foreach ($copy as $key => $value) {
            $valueId = (int)str_replace('scale_value_', '', $key);
            if ($valueId) {
                if (strlen($value)) {
                    $this->getService('AtCriterionScaleValue')->insert(array(
                        'criterion_id' => $values['criterion_id'],
                        'value_id' => $valueId,
                        'description' => $value,
                    ));
                }
                unset($values[$key]);
            }
        }
    }

    public function delete($id) {
        $this->getService('AtCriterionKpi')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $criterionId = $this->_getParam('criterion_id', 0);
        $criterion = $this->getService('AtCriterionKpi')->findDependence('CriterionScaleValue', $criterionId)->current();
        $data = $criterion->getData();

        if (count($criterion->scaleValues)) {
            foreach ($criterion->scaleValues as $value) {
                $data['scale_value_' . $value->value_id] = $value->description;
            }
        }

        $form->populate($data);
    }
}
