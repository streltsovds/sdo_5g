<?php
class HM_Documents_Grid_DocumentsGrid extends HM_Grid
{

    public function init($source = null)
    {
        parent::init($source);
    }

    public function getProviderId()
    {
        return $this->_options['providerId'];
    }
    
    protected function _initColumns()
    {
        $this->_columns = array(
            'document_template_id' => array('hidden' => true),
            'title' => array(
                'title' => _('Название'),
            ),
            'document_type' => array(
                'title' => _('Тип документа'),
                'callback' => array(
                    'function' => array('HM_Document_DocumentTemplateModel', 'getTypeName'),
                    'params'   => array('{{document_type}}')
                )
            ),
            'item_type' => array(
                'title' => _('Тип сущности'),
                'callback' => array(
                    'function' => array('HM_Document_DocumentTemplateModel', 'getItemTypeName'),
                    'params'   => array('{{item_type}}')
                )
            ),
            'item_id' => array(
                'title' => _('Id сущности'),
            ),
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'title' => null,
            'document_type' => array('values' => HM_Document_DocumentTemplateModel::getTypes()),
            'item_type' => array('values' => HM_Document_DocumentTemplateModel::getItemTypes()),
        ));
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        $actions
            ->add('edit', array(
                'module'     => 'documents',
                'controller' => 'list',
                'action'     => 'edit',
            ))
            ->setParams(array(
                'document_template_id'
            ));

        $actions
            ->add('delete', array(
                'module'     => 'documents',
                'controller' => 'list',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'document_template_id'
            ));

    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        $menu->addItem(array(
            'urlParams' => array(
                'module'     => 'documents',
                'controller' => 'list',
                'action'     => 'new'
            ),
            'title' => _('Создать шаблон')
        ));
    }
}