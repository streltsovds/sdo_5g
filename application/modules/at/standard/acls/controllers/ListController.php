<?php
class Standard_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $positionsCache = array();
    protected $_standard = array();

    public function init()
    {
        $form = new HM_Form_Standards();
        $this->_setForm($form);
        
        if ($standardId = $this->_getParam('standard_id')) {
            $this->_standard = $this->getOne($this->getService('AtStandard')->find($standardId));        
        }
        
        parent::init();
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }
        
        $select = $this->getService('AtStandard')->getSelect();

        $select->from(
            array(
                'p' => 'at_ps_standard'
            ),
            array(
                'standard_id',
                'name',
                'number',
                'code',
                'area',
                'vid',
                'prikaz_number',
                'prikaz_date',
                'minjust_number',
                'minjust_date',
                'url',
            )
        );

        $select
            ->joinLeft(array('f' => 'at_ps_functions'), 'f.standard_id = p.standard_id', array())
            ->group(array(
                'standard_id',
                'name',
                'number',
                'code',
                'area',
                'vid',
                'prikaz_number',
                'prikaz_date',
                'minjust_number',
                'minjust_date',
                'url',
            ));
        ;

        $grid = $this->getGrid($select, array(
            'standard_id' => array('hidden' => true),
            'name'=> array('title' => _('Название')),
            'number'=> array('title' => _('Рег. номер')),
            'code'=> array('title' => _('Код')),
            'area'=> array('title' => _('Область')),
            'vid'=> array('title' => _('Вид')),
            'prikaz_number'=> array('title' => _('Ном. приказа')),
            'prikaz_date'=> array('title' => _('Дата. приказа')),
            'minjust_number'=> array('title' => _('Рег.номер МЮ РФ')),
            'minjust_date'=> array('title' => _('Дата рег. МЮ РФ')),
            'url' => array(
                'title' => _('Подробнее'),
                'callback' => array(
                    'function'=> array($this, 'updateUrl'),
                    'params'=> array('{{standard_id}}', '{{url}}')
                ),
            ),
            ),
            array(
                'name' => null,
                'number' => null,
                'code' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'standard',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('standard_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'standard',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('standard_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );
        
/*
        $grid->addMassAction(
            array(
                'module' => 'standard',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить профстандарт'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
*/
        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['standard_id']);
        $res = $this->getService('AtStandard')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
//        $values['category_id'] = $this->_standard->category_id;
        $res = $this->getService('AtStandard')->update($values);
    }

    public function delete($id) {
        $this->getService('AtStandard')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $standardId = $this->_getParam('standard_id', 0);
        $standard = $this->getService('AtStandard')->find($standardId)->current();
        $data = $standard->getData();
        $form->populate(array('cancelUrl' => $this->view->url(array('controller' => 'report', 'action' => 'index'))));
        $form->populate($data);
    }

    public function updateUrl($standardId, $url)
    {
        return '<a href="' . $url . '</a>';
    }

}
