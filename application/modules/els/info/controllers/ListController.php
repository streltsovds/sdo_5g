<?php
class Info_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    /**
     * Инит формы
     * @see HM_Controller_Action::init()
     */
    public function init()
    {
        $this->_setForm(new HM_Form_Info());
        parent::init();
    }

    /**
     * Грид с инфо-новостями
     */
    public function indexAction ()
    {
        // настраиваем селект
        $select = $this->getService('Info')->getSelect()->from(array('n' =>'news2'),array('nID','Title','show','used' => 'nID'));

        // настраиваем поля
        $arFields = array(
            'nID' => array('hidden' => true),
            'type' => array('hidden' => true),
            'Title' => array('title' => _('Название')),
            'show' => array(
                'title' => _('Статус'),
                'style' => 'width: 150px; ',
                'callback' => array(
                    'function' => array($this, 'visibleDecorator'),
                    'params' => array('{{show}}')
                )
            ),
            'used' => array(
                'title' => _('Используется на страницах'),
                'callback' => array(
                    'function' => array($this, 'usedDecorator'),
                    'params' => array('{{used}}'),
                ),
                'color' => HM_DataGrid_Column::colorize('used')
            )
        );

        // настраиваем фильтры
        $arFilters = array('Title' => null);

        $grid = $this->getGrid($select, $arFields, $arFilters);
        $grid->addAction(array('module' => 'info',
            'controller' => 'list',
            'action' => 'edit'),
            array('nID'),
            $this->view->svgIcon('edit', _('Редактировать'))
        );

        $grid->addAction(array('module' => 'info',
            'controller' => 'list',
            'action' => 'delete'),
            array('nID'=>'id'),
            $this->view->svgIcon('delete', _('Удалить')));

        $grid->addMassAction(array('module' => 'info',
            'controller' => 'list',
            'action' => 'visrevers'),
            _('Инвертировать статус'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

        $grid->addMassAction(array('module' => 'info',
            'controller' => 'list',
            'action' => 'delete-by'),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

        $grid->setActionsCallback(array('function' => array($this,'updateActions'),
            'params'   => array('{{show}}')));

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $message = $request->getParam('message');
            $resourceId = $request->getParam('resource_id');

            if(empty($message) and empty($resourceId)) {
                $form->getElement('message')
                    ->setAllowEmpty(false)
                    ->addValidator('MessageOrResourceIsNotEmpty');

                $form->getElement('resource_id')
                    ->setAllowEmpty(false)
                    ->addValidator('MessageOrResourceIsNotEmpty');
            }

            if ($form->isValid($request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    /**
     * Отображение строковых представлений статусов
     * вместо числовых аналогов в столбце "Видимость"
     * @param int $show
     * @return string
     */
    public function visibleDecorator($show)
    {
        return $show? _(HM_Info_InfoModel::VISIBLE_ON) : _(HM_Info_InfoModel::VISIBLE_OFF);
    }

    /**
     * Функция выставляет заголовок действия "Скрыть-показать"
     * в зависимости от текущего статуса видимости
     * @param string|int $show
     * @param unknown_type $actions
     * @return string
     */
    public function updateActions($show, $actions)
    {

        $replace_text = ( $show == strval(intval($show)) )? array(_('Опубликовать'), _('Скрыть')) :
            array(HM_Info_InfoModel::VISIBLE_OFF => _('Опубликовать'),
                HM_Info_InfoModel::VISIBLE_ON => _('Скрыть'));
        return str_replace('[replace_mark]', $replace_text[$show], $actions);
    }

    public function usedDecorator($nID)
    {
        $arUsers = array();
        $arRoles = HM_Role_Abstract_RoleModel::getBasicRoles(TRUE);
        $iBlicks = $this->getService('Infoblock')->fetchAll(array('block=?'=>'news','param_id=?'=>$nID));

        if ( count($iBlicks) ) {
            foreach( $iBlicks as $block ) {
                if ( $block->user_id ){
                    $user = $this->getService('User')->getOne($this->getService('User')->find($block->user_id));
                    if ($user) {
                        $arUsers[] = _('Пользователь') . ' ' . $user->getName();
                    }

                } else {
                    $arUsers[] = _('Роль') . ' ' . $arRoles[$block->role];
                }
            }
        }

        $result = ( count($arUsers) > 1 ) ? array('<p class="total">' . _('Всего') . ' ' . (count($arUsers)) . '</p>') : array();
        foreach($arUsers as $value){
            $result[] = "<p>{$value}</p>";
        }
        if($result)
            return implode($result);
        else
            return _('Нет');

    }
    
    /**
     * Меняет видимость инфо-новостей на противоположную
     */
    public function visreversAction()
    {
        $arID = $this->_getParam('nID',$this->_getParam('postMassIds_grid',array()));
        
        //дальше работаем с массивом
        if ( !is_array($arID) ) {
            $arID = explode(',', $arID);
        }
        $arID = array_unique($arID);
        
        if ( !$arID ) {
            
            $this->_flashMessenger->addMessage(_('Не выбраны элементы'));
            $this->_redirector->gotoSimple('index','list','info');
            
        } else {
            
            $service = $this->getService('Info');
            
            $arInfo = $service->fetchAll($service->quoteInto('nID IN(?)',$arID));
            
            if ( count ($arInfo)) {
                foreach($arInfo as $info) {
                    $info->invertVisible();
                    $service->update($info->getValues());
                }
                $this->_flashMessenger->addMessage(_('Видимость успешно изменена'));
                $this->_redirector->gotoSimple('index','list','info');
            } else {
                $this->_flashMessenger->addMessage(_('При изменении видимости произошла ошибка'));
                $this->_redirector->gotoSimple('index','list','info');
            }
        }
    }
    
    /* (non-PHPdoc)
     * @see HM_Controller_Action_Crud::setDefaults()
     */
    public function setDefaults(Zend_Form $form) 
    {
        $nID = (int) $this->_getParam('nID', 0);
        $info = $this->getOne($this->getService('Info')->find($nID));
        if ( $info ) {
            $data = $info->getValues();
            $data['resource_id'] = $this->getService('Resource')->setDefaultRelatedResources($data['resource_id']);
            $form->setDefaults($data);
        }
    }
    
     /* (non-PHPdoc)
      * @see HM_Controller_Action_Crud::update()
      */
     public function update(Zend_Form $form) 
     {
         $resourceIds = $form->getValue('resource_id');
         $this->getService('Info')->update(array(
             'nID' => $form->getValue('nID'),
             'show' => $form->getValue('show',0),
             'Title' => $form->getValue('Title',''),
             'message' => $form->getValue('message',''),
             'resource_id' => count($resourceIds) ? array_shift($resourceIds) : 0,
         ));

         $this->getService('Info')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);
     }
     
    /* (non-PHPdoc)
     * @see HM_Controller_Action_Crud::delete()
     */
    public function delete($id)
    {
        $return = $this->getService('Info')->delete($id);

        $this->getService('Info')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);

        return $return;
    }
    
    /* (non-PHPdoc)
     * @see HM_Controller_Action_Crud::create()
     */
    public function create(Zend_Form $form)
    {
        $resourceIds = $form->getValue('resource_id');
        $this->getService('Info')->insert(array(
            'show' => $form->getValue('show',0),
            'Title' => $form->getValue('Title',''),
            'message' => $form->getValue('message',''),
            'resource_id' => count($resourceIds) ? array_shift($resourceIds) : 0,
        ));

        $this->getService('Info')->cleanUpCache(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_ALL);
    }
}
