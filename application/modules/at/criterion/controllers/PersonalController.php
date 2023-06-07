<?php
class Criterion_PersonalController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_Personal();
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $select = $this->getService('AtCriterion')->getSelect();

        $select->from(
            array(
                'c' => 'at_criteria_personal'
            ),
            array(
                'c.criterion_id',
                'c.name',
                'quest' => 'q.name',
            )
        )->joinLeft(array('q' => 'questionnaires'), 'c.quest_id = q.quest_id', array());

        $select->group(array(
            'c.criterion_id',
            'c.name',
            'q.name',
        ));

        $grid = $this->getGrid($select, array(
            'criterion_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
            ),
            'quest' => array(
                'title' => _('Психологический опрос'),
            ),
        ),
            array(
                'name' => null,
                'quest' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'personal',
            'action' => 'edit'
        ),
            array('criterion_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'personal',
            'action' => 'delete'
        ),
            array('criterion_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'criterion',
                'controller' => 'personal',
                'action' => 'delete-by',
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['criterion_id']);
        $res = $this->getService('AtCriterionPersonal')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $res = $this->getService('AtCriterionPersonal')->update($values);
    }

    public function delete($id) {
        $this->getService('AtCriterionPersonal')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $criterionId = $this->_getParam('criterion_id', 0);
        $criterion = $this->getService('AtCriterionPersonal')->find($criterionId)->current();
        $data = $criterion->getData();

        $form->populate($data);
    }
}
