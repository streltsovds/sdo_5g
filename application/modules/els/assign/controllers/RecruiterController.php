<?php
class Assign_RecruiterController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    public function indexAction()
    {
        $courseId = (int) $this->_getParam('subject_id', 0);
        $switcher = $this->getSwitcherSetOrder(null, 'fio_ASC', 'notempty DESC');

        if ($this->_request->getParam("assigned{$this->gridId}",false)) {
            $this->_request->setParam("assigned{$this->gridId}",urldecode(urldecode($this->_request->getParam("assigned{$this->gridId}"))));
        }

        $select = $this->getService('User')->getSelect();
        $select->from(
            array('t1' => 'People'),
            array(
                'MID',
                'rc.recruiter_id',
                'user_id' => 'MID',
                'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                'login' => 't1.Login',
                'status' => 't1.blocked'
            )
        );
        
        if (!$switcher) {
            $this->_request->setParam('masterOrdergrid', 'notempty DESC');
            $select->join(array('rc' => 'recruiters'), 'rc.user_id = t1.MID', array());
        } else {
            $select->joinLeft(array('rc' => 'recruiters'), 'rc.user_id = t1.MID', array('assigned' => 'rc.user_id', 'enabled' => 'rc.user_id'));
        }

        $select->joinLeft(
                array('r' => 'responsibilities'), 
                'rc.user_id = r.user_id AND r.item_type = ' . HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, 
                array(
                    'role' => 'rc.user_id',
                    'departments' => new Zend_Db_Expr('GROUP_CONCAT(item_id)'),
                
            )
        )
        ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.blocked', 't1.Login', 'rc.user_id', 'rc.recruiter_id'));

            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'recruiter_id' => array('hidden' => true),
                    'user_id' => array('hidden' => true),
                    'enabled' => array('hidden' => true),
                        'notempty' => array('hidden' => true),
                       // 'employer' => array('title' => _('Место работы')),
                        'login' => array('title' => _('Логин')),
                         'assigned' => array('title' => _('Назначен')),
                        //'role' => array('title' => _('Роли')),
                        'status' => array('title' => _('Статус')),
                    'role' => array(
                        'title' => _('Роль'),
                        'callback' => array(
                            'function' => array($this, 'updateResponsibilityRole'),
                            'params' => array('{{departments}}', '{{enabled}}', $select)
                        )                        
                    ),
                    'departments' => array(
                        'title' => _('Ограничение области ответственности'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{departments}}', $select)
                        )                        
                    ),
                    'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'gridmod' => null,'user_id' => '')).'{{MID}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null,'user_id' => '')) . '{{MID}}'.'">'.'{{fio}}</a>'),
                ),
                array(
                    'fio' => null,
                    'login' => null,
                    'assigned' => array(
                        'values' => array(
                            '*' => _('Да'),
                            'ISNULL' => _('Нет')
                        )
                    ),
                    'status' => array(
                        'values' => array(
                            '0' => _('Активный'),
                            '1' => _('Заблокирован')
                        )
                    )
                )
            );

        $grid->setGridSwitcher([
            'label' => _('Показать всех'),
            'title' => _('Показать всех менеджеров по подбору'),
            'param' => self::SWITCHER_PARAM_DEFAULT,
            'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
        ]);

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );

        $grid->updateColumn('assigned',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateAssigned'),
                    'params' => array('{{assigned}}')
                )
            )
        );

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        if($switcher) $grid->setClassRowCondition("'{{assigned}}' != ''", "success");

        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "success");

        $url = array('action' => 'assign');
        $grid->addMassAction(
            $url,
            _('Назначить роль'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $url = array('action' => 'unassign');
        $grid->addMassAction(
            $url,
            _('Удалить роль'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addAction(array(
               'module' => 'user',
               'controller' => 'responsibility',
               'action' => 'assign'
            ),
            array('user_id'),
            _('Редактировать область ответственности')
        );

        $grid->addAction(array(
                'module' => 'assign',
                'controller' => 'recruiter',
                'action' => 'hh-edit'
            ),
            array('recruiter_id'),
            _('Редактировать учётную запись на hh.ru')
        );
                
        $grid->addAction(array(
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index'),
            array('MID'),
            _('Отправить сообщение')
        );
        
        $grid->addMassAction(array(
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index'),
            _('Отправить сообщение')
        );

        $grid->setHeadCheckbox('all', _('Отображать пользователей только данной роли'), 1);

        $grid->addFixedRows(
            $this->_getParam('module'),
            $this->_getParam('controller'),
            $this->_getParam('action'),
            't1.MID');
        $grid->updateColumn('fixType', array('hidden' => true));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;



    }

    /**
     * Редактирование учётной записи на hh.ru
     */
    public function hhEditAction()
    {
        $form = new HM_Form_Hh();
        
        $recruiter_id = $this->_getParam('recruiter_id');
        
        if ( $this->_request->isPost() ) {
            
            $params = $this->_request->getParams();

            if ( $form->isValid($params) ) {

                $data = HM_HeadHunter::encript(array(
                    'hh_email'     => $params['hh_email'],
                    'hh_password'  => $params['hh_password'],
                    'hh_managerId' => $params['hh_managerId'],
                    'hh_region' => $params['hh_region']
                ));
                
                $update = array(
                    'hh_auth_data' => $data
                );

                $db = $this->getService('RecruitVacancy')->getMapper()->getTable()->getAdapter();
                $db->update('recruiters', $update, $this->quoteInto('recruiter_id = ?', $params['recruiter_id']));

                $this->_flashMessenger->addMessage(_('Настройка учётной записи hh.ru успешно выполнена.'));
                $this->_redirector->gotoSimple('index', 'recruiter', 'assign');

            } else {
                $form->populate($this->_request->getParams());
            }

        } else {
            
            $select = $this->getService('User')->getSelect();
            
            $select->from('recruiters', array('hh_auth_data'));
            $select->where('recruiter_id = ?', $recruiter_id);
            $item = $select->query()->fetch();
            
            $default = $item['hh_auth_data'] ? HM_HeadHunter::decript($item['hh_auth_data']) : array();

            $form->populate($default);

        }
        
        $this->view->form = $form;
    }


    public function assignAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $errors=false;
                foreach($ids as $id) {
                    if (method_exists($this, '_preAssign')) {
                        $this->_preAssign($id, $courseId);
                    }


                    $fetch = $this->getService('Recruiter')->fetchAll(array('user_id = ?' => $id));
                    try{
                        if(count($fetch) == 0){

                            $this->getService('Responsibility')->resetResponsibility($id);

                            $this->getService('Recruiter')->insert(
                                array(
                                    'user_id' => $id
                                )
                            );
                        }
                    }catch (Zend_Db_Exception  $e){
                        $errors=true;
                    }


                    if (method_exists($this, '_postAssign')) {
                        $this->_postAssign($id, $courseId);
                    }
                }


                if($errors==false){
                    $this->_flashMessenger->addMessage(_('Пользователи успешно назначены'));
                }else{
                    $this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли несущественные ошибки.'));
                }

            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        if (method_exists($this, '_finishAssign')) {
            $this->_finishAssign();
        }

        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

    public function unassignAction()
    {
        //$subjectId = 0;

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    if($this->getService('User')->getCurrentUserId() == $id){
                        $this->_flashMessenger->addMessage(_('Вы не можете удалить себя'));
                        continue;
                    }
                    if (method_exists($this, '_preUnassign')) {
                        $this->_preUnassign($id, $courseId);
                    }
                    $this->getService('Recruiter')->deleteBy(
                    sprintf("%s = %d", 'user_id', $id)
                    );
                    if (method_exists($this, '_postUnassign')) {
                        $this->_postUnassign($id, $courseId);
                    }
                }
                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        if (method_exists($this, '_finishUnassign')) {
            $this->_finishUnassign();
        }
        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

    protected function _postAssign($id, $subjectId)
    {

    }

    protected function _preAssign($personId, $courseId){}
    protected function _assign($personId, $courseId) {}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishAssign(){}
    protected function _finishUnassign(){}

    protected function _unassign($personId, $courseId){}
    protected function _preUnassign($personId, $courseId){}
}