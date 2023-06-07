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
                'count_functions' => new Zend_Db_Expr('COUNT(DISTINCT f.function_id)'),
                'prikaz_number',
                'prikaz_date',
                'minjust_number',
                'minjust_date',
                'url',
            )
        );

        $select
            ->joinLeft(array('f' => 'at_ps_function'), 'f.standard_id = p.standard_id', array())
            ->group(array(
                'p.standard_id',
                'p.name',
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
            'name'=> array(
                'title' => _('Название'),
                'decorator' => '<a href="'.$this->view->url(array('module' => 'standard', 'controller' => 'functions', 'action' => 'index', 'gridmod' => null, 'standard_id' => '')) . '{{standard_id}}'.'">'.'{{name}}</a>',
            ),
            'number'=> array('title' => _('Рег. номер')),
            'code'=> array('title' => _('Код')),
            'area'=> array('title' => _('Область')),
            'vid'=> array('title' => _('Вид')),
            'count_functions' => array(
                'hidden' => true,
                'title' => _('Функции'),
                'decorator' => '<a href="'.$this->view->url(array('module' => 'standard', 'controller' => 'functions', 'action' => 'index', 'gridmod' => null, 'standard_id' => '')) . '{{standard_id}}'.'">'.'{{count_functions}}</a>',
            ),
            'prikaz_number'=> array('title' => _('Ном. приказа')),
            'prikaz_date'=> array(
                'title' => _('Дата. приказа'),
                'format' => array(
                    'Date',
                    array('date_format' => Zend_Locale_Format::getDateTimeFormat())
                ),
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params' => array('{{prikaz_date}}')
                )
            ),
            'minjust_number'=> array('title' => _('Рег.номер МЮ РФ')),
            'minjust_date'=> array(
                'title' => _('Дата рег. МЮ РФ'),
                'format' => array(
                    'Date',
                    array('date_format' => Zend_Locale_Format::getDateTimeFormat())
                ),
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params' => array('{{minjust_date}}')
                )
            ),
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
                'area'=> null,
                'vid'=> null,
                'prikaz_number'=> null,
                'prikaz_date'=> array('render' => 'date'),
                'minjust_number'=> null,
                'minjust_date'=> array('render' => 'date'),
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

        $grid->addMassAction(array(
            'module' => 'standard',
            'controller' => 'list',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function updateDate($date)
    {
        return $date == '01.01.1970' ? _('Нет данных') : $date;
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['standard_id']);
        $values['prikaz_date'] = (!empty($values['prikaz_date']) && ($values['prikaz_date'] != '01.01.1970')) ? date ("Y-m-d H:i:s", strtotime($values['prikaz_date'])) : '';
        $values['minjust_date'] = (!empty($values['minjust_date']) && ($values['minjust_date'] != '01.01.1970')) ? date ("Y-m-d H:i:s", strtotime($values['minjust_date'])) : '';
        $res = $this->getService('AtStandard')->insert($values);
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
            $stID = $this->_getParam('standard_id', 0);
            if ($stID) {
                $standard = $this->getService('AtStandard')->find($stID);
                if (count($standard)) $standard = $standard->current();
                if (!empty($standard->prikaz_date) /*&& ($standard->prikaz_date != '1970-01-01')*/) {
                    $form->getElement('prikaz_date')->setValue(date('d.m.Y', strtotime($standard->prikaz_date)));
                } else {
                    $form->getElement('prikaz_date')->setValue('');
                }
                if (!empty($standard->minjust_date) /*&& ($standard->minjust_date != '1970-01-01')*/) {
                    $form->getElement('minjust_date')->setValue(date('d.m.Y', strtotime($standard->minjust_date)));
                } else {
                    $form->getElement('minjust_date')->setValue('');
                }
            }
        }
        $this->view->form = $form;
    }

    public function update($form)
    {
        $values = $form->getValues();
//        $values['category_id'] = $this->_standard->category_id;
        $values['prikaz_date'] = (!empty($values['prikaz_date']) && ($values['prikaz_date'] != '01.01.1970')) ? date ("Y-m-d H:i:s", strtotime($values['prikaz_date'])) : '';
        $values['minjust_date'] = (!empty($values['minjust_date']) && ($values['minjust_date'] != '01.01.1970')) ? date ("Y-m-d H:i:s", strtotime($values['minjust_date'])) : '';
        $res = $this->getService('AtStandard')->update($values);
    }

    public function delete($id) {
        $functions = $this->getService('AtStandardFunction')->fetchAll(array('standard_id = ?' => $id))->getList('function_id');
        $this->getService('AtStandardFunction')->deleteBy(array('standard_id = ?' => $id));
        if(!empty($functions)){
            $this->getService('AtProfileFunction')->deleteBy(array('function_id IN (?)' => $functions));
            $this->getService('AtStandardRequirement')->deleteBy(array('function_id IN (?)' => $functions));
        }
        $this->getService('AtStandard')->delete($id);
    }

    public function deleteAction()
    {
        $params = $this->_getAllParams();
        foreach($params as $key => $value) {
            if (substr($key, -3) == '_id') {
                $this->_setParam('id', $value);
                break;
            }

            if (in_array($key, array('subid', 'projid'))) { // hack
                $this->_setParam('id', $value);
            }
        }

        $id = (int) $this->_getParam('id', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_DELETE));
        }

        $this->_redirector->gotoSimple('index');
    }

    public function setDefaults(Zend_Form $form)
    {
        $standardId = $this->_getParam('standard_id', 0);
        $standard = $this->getService('AtStandard')->find($standardId)->current();
        $data = $standard->getData();
        $form->populate(array('cancelUrl' => $this->view->url(array('controller' => 'standard', 'action' => 'list'))));
        $form->populate($data);
    }

    public function updateUrl($standardId, $url)
    {
        return trim($url) ? '<a target=blank href="'.$url.'">посмотреть</a>' : '';
    }

}
