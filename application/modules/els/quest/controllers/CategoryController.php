<?php
class Quest_CategoryController extends HM_Controller_Action_Quest
{
    use HM_Controller_Action_Trait_Grid {
        newAction as newActionTraitGrid;
        editAction as editActionTraitGrid;
    }

    protected $_category;
    
    public function init()
    {
        $form = new HM_Form_Category();
        $this->_setForm($form);

        parent::init();
        
        $categoryId = (int) $this->_getParam('category_id', 0);
        
        if ($categoryId) {
            $this->_category = $this->getOne(
                $this->getService('QuestCategory')->find($categoryId)
            );
        }
                
    }
    
    public function listAction()
    {
        $select = $this->getService('QuestCategory')->getSelect();

        $select->from(
            array(
                'qq' => 'quest_categories'
            ),
            array(
                'category_id',
                'name',
            )
        );

        $select
            ->where('quest_id = ?', $this->_quest->quest_id);

        $grid = $this->getGrid($select, array(
            'category_id' => array('hidden' => true),
            'name' => array('title' => _('Название')),
        ),
            array(
                'name' => null, 
            )
        );

        $grid->addAction(array(
            'module' => 'quest',
            'controller' => 'category',
            'action' => 'edit'
        ),
            array('category_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'quest',
            'controller' => 'category',
            'action' => 'delete'
        ),
            array('category_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'quest',
                'controller' => 'category',
                'action' => 'delete-by',
            ),
            _('Удалить показатели'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('list', null, null, array('quest_id' => $this->_quest->quest_id));
    }

    public function create($form)
    {
        $data = $form->getValues();
        unset($data['category_id']);
        $data['quest_id'] = $this->_quest->quest_id;
        $category = $this->getService('QuestCategory')->insert($data);
    }

    public function update($form)
    {
        $data = $form->getValues();
        $data['quest_id'] = $this->_quest->quest_id;
        $category = $this->getService('QuestCategory')->update($data);
    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam('ques_category_id', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }       
    
    public function delete($id) {
        $this->getService('QuestCategory')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $categoryId = $this->_getParam('category_id', 0);
        $category = $this->getService('QuestCategory')->find($categoryId)->current();
        $data = $category->getData();
        
        $data['formula'] = unserialize($data['formula']);
        $form->populate($data);
    }

    public function newAction()
    {
        $this->view->setSubSubHeader(_('Создание показателя'));
        $this->newActionTraitGrid();
    }

    public function editAction()
    {
        $this->view->setSubSubHeader(_('Редактирование показателя'));
        $this->editActionTraitGrid();
    }
}
