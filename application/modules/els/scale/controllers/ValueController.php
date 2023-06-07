<?php
class Scale_ValueController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    private $_scale;

    public function init()
    {
        $form = new HM_Form_Value();
        $this->_setForm($form);

        parent::init();

        $scaleId = (int) $this->_getParam('scaleId', 0);
        if ($scaleId) {
            $this->_scale = $this->getOne(
                $this->getService('Scale')->find($scaleId)
            );
        } else {
            $this->_redirector->gotoSimple('index', 'list', 'scale');
        }

        $this->view->setSubHeader(_('Значения шкалы'));
        $this->view->setHeader($this->_scale->name);

    }

    public function indexAction()
    {

            if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
                $this->_request->setParam('ordergrid', 'value_DESC');
            }

        $select = $this->getService('ScaleValue')->getSelect();

        $select->from(
            array(
                'sv' => 'scale_values'
            ),
            array(
                'value_id',
                'value',
                'text',
                'description',
            )
        );

        $select
            ->where('scale_id = ?', $this->_scale->scale_id);

        $grid = $this->getGrid($select, array(
            'value_id' => array('hidden' => true),
            'value' => array('title' => _('Значение')),
            'text' => array('title' => _('Текстовое значение')),
            'description' => array('title' => _('Описание')),
        ),
            array(
                'value' => null,
                'text' => null,
                'description' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'scale',
            'controller' => 'value',
            'action' => 'edit'
        ),
            array('value_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'scale',
            'controller' => 'value',
            'action' => 'delete'
        ),
            array('value_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'scale',
                'controller' => 'value',
                'action' => 'delete-by',
            ),
            _('Удалить значения'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $backUrl = $this->view->url([
            'module' => 'scale',
            'controller' => 'list',
            'action' => 'index',
        ], null, true);

        $this->view->setBackUrl($backUrl);

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', null, null, array('scaleId' => $this->_scale->scale_id));
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['value_id']);
        $values['scale_id'] = $this->_scale->scale_id;
        $res = $this->getService('ScaleValue')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $values['scale_id'] = $this->_scale->scale_id;
        $res = $this->getService('ScaleValue')->update($values);
    }

    public function delete($id) {
        $this->getService('ScaleValue')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $valueId = $this->_getParam('value_id', 0);
        $value = $this->getService('ScaleValue')->find($valueId)->current();
        $data = $value->getData();
        $form->populate($data);
    }
}
