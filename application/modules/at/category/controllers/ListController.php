<?php
class Category_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_criteriaCache;

    protected $_programEventCache = array();

    protected $_role = null;

    public function init()
    {
        $this->_role = $this->getService('User')->getCurrentUserRole();
        $form = new HM_Form_Categories();
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }

        $select = $this->getService('AtCategory')->getSelect();

        $select->from(
            array(
                'ac' => 'at_categories'
            ),
            array(
                'ac.category_id',
                'programm_recruit' => 'pr.programm_id',
                'programm_reserve' => 'pre.programm_id',
                //'programm_adapting' => 'pad.programm_id',
                'programm_assessment' => 'pa.programm_id',
                'programm_elearning' => 'po.programm_id',
                'name' => 'ac.name',
                'profiles' => new Zend_Db_Expr('COUNT(DISTINCT ap.profile_id)'),
            )
        );

        $select
            ->joinLeft(array('ap' => 'at_profiles'), 'ac.category_id = ap.category_id', array())
            ->joinLeft(array('po' => 'programm'), sprintf('ac.category_id = po.item_id AND po.item_type = %s AND po.programm_type = %s',  HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY, HM_Programm_ProgrammModel::TYPE_ELEARNING), array())
            ->joinLeft(array('pr' => 'programm'), sprintf('ac.category_id = pr.item_id AND pr.item_type = %s AND pr.programm_type = %s',  HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY, HM_Programm_ProgrammModel::TYPE_RECRUIT), array())
            ->joinLeft(array('pre' => 'programm'), sprintf('ac.category_id = pre.item_id AND pre.item_type = %s AND pre.programm_type = %s',  HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY, HM_Programm_ProgrammModel::TYPE_RESERVE), array())
            ->joinLeft(array('pa' => 'programm'), sprintf('ac.category_id = pa.item_id AND pa.item_type = %s AND pa.programm_type = %s',  HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY, HM_Programm_ProgrammModel::TYPE_ASSESSMENT), array())
            ->group(array(
                'ac.category_id',
                'ac.name',
                'pr.programm_id',
                'pre.programm_id',
                'pa.programm_id',
                'po.programm_id'
                //'pad.programm_id',
            ));

        $grid = $this->getGrid($select, array(
            'category_id' => array('hidden' => true),
            'programm_recruit' => array('hidden' => true),
            'programm_reserve' => array('hidden' => true),
            //'programm_adapting' => array('hidden' => true),
            'programm_assessment' => array('hidden' => true),
            'programm_id' => array('hidden' => true),
            'programm_elearning' => array('hidden' => true),
            'name' => array(
                'title' => _('Название')
            ),
            'profiles' => array(
                'title' => _('Количество профилей должности'),
                'decorator' => '<a href="' . $this->view->url(array('module' => 'profile', 'controller' => 'list', 'action' => 'index', 'categorygrid' => '', 'namegrid' => null)) . '{{name}}/?page_id=m0803">{{profiles}}</a>',
            ),
        ),
            array(
                'name' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'category',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('category_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'category',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('category_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(array(
            'baseUrl' => '',
            'module' => 'programm',
            'controller' => 'index',
            'action' => 'index',
        ),
            array('programm_elearning' => 'programm_id'),
            $this->view->svgIcon('reports', 'Редактировать программу обучения')
        );

        $grid->addMassAction(
            array(
                'module' => 'category',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить категорию'),
            _('Вы уверены?')
        );

        // Вот всё что ниже - это наследие At, в базовой не нужно, разгребать будем потом
        $remover = $this->getService('Extension')->getRemover('HM_Extension_Remover_AtRemover');
        if(!$remover) {
            if (!$this->currentUserRole(array(
                HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
            ))) {
                if (in_array($this->_role, array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_HR))) {
                    $grid->addAction(array(
                        'module' => 'programm',
                        'controller' => 'index',
                        'action' => 'index',
                        'baseUrl' => '',
                    ),
                        array('programm_recruit'),
                        _('Редактировать программу подбора')
                    );
                }

                if (in_array($this->_role, array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_HR))) {
                    $grid->addAction(array(
                        'module' => 'programm',
                        'controller' => 'index',
                        'action' => 'index',
                        'baseUrl' => '',
                    ),
                        array('programm_reserve'),
                        _('Редактировать программу оценки КР')
                    );
                }


                /*
                $grid->addAction(array(
                    'module' => 'programm',
                    'controller' => 'index',
                    'action' => 'index',
                    'baseUrl' => '',
                ),
                    array('programm_adapting'),
                    _('Редактировать программу адаптации')
                );
                */


                if (in_array($this->_role, array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))) {

                    $grid->addAction(array(
                        'module' => 'programm',
                        'controller' => 'index',
                        'action' => 'index',
                        'baseUrl' => '',
                    ),
                        array('programm_assessment'),
                        _('Редактировать программу регулярной оценки')
                    );
                }


                if (in_array($this->_role, array(
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                ))) {
                    $grid->addAction(array(
                        'module' => 'programm',
                        'controller' => 'index',
                        'action' => 'index',
                        'baseUrl' => '',
                    ),
                        array('programm_elearning'),
                        _('Редактировать программу начального обучения')
                    );
                }

                $grid->addMassAction(
                    array(
                        'module' => 'category',
                        'controller' => 'list',
                        'action' => 'delete-by',
                    ),
                    _('Удалить категорию'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }
        }

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['category_id']);
        $res = $this->getService('AtCategory')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $res = $this->getService('AtCategory')->update($values);
    }

    public function delete($id) {
        $this->getService('AtCategory')->delete($id);
        $this->getService('AtProfile')->unlinkCategory($id);
        $this->getService('AtCriterion')->unlinkCategory($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $categoryId = $this->_getParam('category_id', 0);
        $category = $this->getService('AtCategory')->find($categoryId)->current();
        $data = $category->getData();
        $form->populate($data);
    }

    public function criteriaCache($criteriaIds){

        $criteriaIds = array_unique(explode(',', $criteriaIds));
        if($this->_criteriaCache === null){
            $this->_criteriaCache = $this->getService('AtCriterion')->fetchAll();
        }

        $result = (is_array($criteriaIds) && (($count = count($criteriaIds)) > 1)) ? array('<p class="total">' . sprintf(_n('компетенция plural', '%s компетенция', $count), $count) . '</p>') : array();
        foreach($criteriaIds as $criteriaId){
            if ($tempModel = $this->_criteriaCache->exists('criterion_id', $criteriaId)) {
                $result[] = "<p>{$tempModel->name}</p>";
            }
        }
        if($result)
            return implode(' ',$result);
        else
            return '';
    }
}
