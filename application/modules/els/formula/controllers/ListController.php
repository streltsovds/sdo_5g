<?php

class Formula_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    public function init()
    {
        parent::init();

        $subjectId = (int) $this->_getParam('subject_id', 0);
        if ($subjectId) { // Делаем страницу расширенной
            $this->id = (int) $this->_getParam($this->idParamName, 0);

            if (!$this->isAjaxRequest()) {
                $subject = $this->getOne($this->getService($this->service)->find($this->id));
                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $subject
                    )
                );
            }
        }
    }

    public function getSubjectId()
    {
        return (int) $this->id;
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'list', 'formula', array('subject_id' => $this->id));
    }

    public function indexAction()    
    {
        $select = $this->getService('Formula')->getListSource($this->id);
        $s = $select->__toString();
        $grid = $this->getGrid($select, [
            'CID' => array(
               'title' => _('Для этого курса'),
               'callback' => array(
                   'function'=> array($this, 'updateSubjectId'),
                   'params'=> array('{{CID}}')
               ),
               'hidden' => !$this->id,
            ),
            'name' => array(
               'title' => _('Название'),
            ),
            'formula' => array(
               'title' => _('Формула'),
               'callback' => array(
                   'function'=> array($this, 'updateFormula'),
                   'params'=> array('{{formula}}')
               )
            ),
            'ftype' => array(
               'title' => _('Тип'),
               'callback' => array(
                   'function'=> array($this, 'updateType'),
                   'params'=> array('{{ftype}}')
               ),

            ),
        ], [
            'ftype' => array(
               'values' => HM_Formula_FormulaModel::getFormulaTypes()
            ),
//            'CID' => false,
            'name' => null,
            'formula' => null,
        ]);

        $isDeanLocal = $this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL);

        if (!$isDeanLocal) {
            $grid->addAction(
                [
                    'module'     => 'formula',
                    'controller' => 'list',
                    'action'     => 'edit',
                ],
                ['formula_id'],
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(
                [
                    'module'     => 'formula',
                    'controller' => 'list',
                    'action'     => 'delete',
                ],
                ['formula_id'],
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(
                array(
                    'module' => 'formula',
                    'controller' => 'list',
                    'action' => 'delete-by',
                ),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        }

        $this->view->grid = $grid;
    }

    public function updateType($type)
    {
        return HM_Formula_FormulaModel::getFormulaType($type);
    }

    public function updateFormula($formula)
    {
        return nl2br($formula);
    }

    public function updateSubjectId($sId)
    {
        return $this->id == $sId ? _('Да') : _('Нет');
    }

    public function editAction()
    {
        $form = new HM_Form_Formula();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->getService('Formula')->update(
                    array(
                        'id' => $form->getValue('formula_id'),
                        'name' => $form->getValue('name'),
                        'type' => $form->getValue('type'),
                        'formula' => $form->getValue('formula'),
                        'CID' => (int) $form->getValue('subject_id'),
                    )
                );

                $this->_flashMessenger->addMessage(_('Формула успешно сохранена'));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }

        $this->view->form = $form;
    }

    public function newAction()
    {
        $form = new HM_Form_Formula();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->getService('Formula')->insert(
                    array(
                        'name' => $form->getValue('name'),
                        'type' => $form->getValue('type'),
                        'formula' => $form->getValue('formula'),
                        'CID' => (int) $form->getValue('subject_id'),
                    )
                );

                $this->_flashMessenger->addMessage(_('Формула успешно создана'));
                $this->_redirectToIndex();
            }
        } else {
            $form->populate(array(
//                'formula'    => HM_Formula_FormulaModel::getFormulaExample(),
                'subject_id' => $this->_getParam('subject_id', 0)
            ));
        }

        $this->view->form = $form;
    }

    public function setDefaults(HM_Form $form)
    {
        $formulaId = (int) $this->_getParam('formula_id', 0);
        $formula   = $this->getService('Formula')->find($formulaId)->current();

        if ($formula) {
            $form->populate(array(
                'formula_id' => $formula->id,
                'name'       => $formula->name,
                'formula'    => $formula->formula,
                'type'       => $formula->type,
                'subject_id' => $formula->CID,
            ));
        }

    }

    public function deleteAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $formula_id = (int) $this->_getParam('formula_id', 0);

        if ($formula_id) {
            $this->getService('Formula')->delete($formula_id);
        }

        $this->_flashMessenger->addMessage(_('Формула успешно удалена'));
        $this->_redirector->gotoSimple('index', 'list', 'formula', array('subject_id' => $subjectId));        

    }

    public function deleteByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $formulas = $this->getService('Formula')->fetchAll(array('id IN (?)' => $ids));
                foreach($formulas as $formula) {
                    if (
                        $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN) ||
                        (
                            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER) &&
                            ($formula->CID == $subjectId)
                        )
                    ) {
                        $this->getService('Formula')->delete($formula->id);
                        $this->_flashMessenger->addMessage(_('Формулы успешно удалены'));
                    } else {
                        $this->_flashMessenger->addMessage(_('Невозможно удалить элемент созданный в базе знаний'));
                    }
                }

            }
        }
        $this->_redirector->gotoSimple('index', 'list', 'formula', array('subject_id' => $subjectId));        
    }
}