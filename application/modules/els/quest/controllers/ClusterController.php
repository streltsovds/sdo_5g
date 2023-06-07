<?php
class Quest_ClusterController extends HM_Controller_Action_Quest
{
    use HM_Controller_Action_Trait_Grid;

    private $subjectId = 0;
    
    public function init()
    {
        $form = new HM_Form_Cluster();
        $this->_setForm($form);

        parent::init();

        $this->subjectId = (int) $this->_getParam('subject_id', 0);
        if ($this->subjectId > 0) {
            //$this->view->setSubHeader($this->_quest->name);
        }

        if($this->_quest) {
            $this->setActiveContextMenu('mca:quest:cluster:list');

            /*$this->view->addSidebar('test', [
                'model' => $this->_quest,
            ]);*/
        }

        $this->gridId = 'grid';
    }

    protected function _redirectToIndex()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        if ($subjectId > 0) {
            $this->_redirector->gotoSimple('list', null, null, array('subject_id' => $subjectId, 'quest_id' => $this->_quest->quest_id));
        } else {
            $this->_redirector->gotoSimple('list', null, null, array('quest_id' => $this->_quest->quest_id));
        }
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = array(
            'name'      => $this->_getParam('title'),
            'quest_id'  => $this->_getParam('quest_id')
        );
        if ($cluster = $this->getService('QuestCluster')->insert($defaults)) {
            $result = $cluster->cluster_id;
        }
        exit(HM_Json::encodeErrorSkip($result));
    }

    public function listAction()
    {
        $select = $this->getService('QuestCluster')->getSelect();
        $select->from(
            array(
                'qcl' => 'quest_clusters'
            ),
            array(
                'qcl.cluster_id',
                'qcl.name',
                'subject_id_' => 'q.subject_id',
                'q_order' => 'qcl.order',
            ))
            ->joinLeft(array('qqq' => 'quest_question_quests'), 'qqq.cluster_id = qcl.cluster_id', array())
            ->joinLeft(array('q' => 'questionnaires'), 'qcl.quest_id = q.quest_id', array())
//            ->where('qcl.quest_id = ? OR qqq.quest_id = ?' , $this->_quest->quest_id) // возможно, этот OR был нужен для обратной совместимости; сейчас всё просто: cluster принадлежит к одному конкретному quest'у
            ->where('qcl.quest_id = ?' , $this->_quest->quest_id)
            ->group(array('qcl.cluster_id', 'qcl.name', 'q.subject_id', 'qcl.order'));

        $grid = $this->getGrid(
            $select,
            array(
                'cluster_id' => array('hidden' => true),
                'subject_id_' => array('hidden' => true),
                'name'       => array('title' => _('Название')),
                'q_order'       => array('title' => _('Порядок'))
            ),
            array(
                'name' => null
            ),
            $this->gridId
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'cluster',
                'action' => 'edit'
            ),
            array('cluster_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'cluster',
                'action' => 'delete'
            ),
            array('cluster_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'quest',
                'controller' => 'cluster',
                'action' => 'delete-by',
            ),
            _('Удалить блоки вопросов'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setActionsCallback(array(
            'function' => array($this,'updateActions'),
            'params'   => array('{{subject_id_}}'),
        ));
        
        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function update($form)
    {
        $data    = $form->getValues();
        unset($data['quest_id']);
        $cluster = $this->getService('QuestCluster')->update($data);
        $this->_redirectToIndex();
    }

    public function create($form)
    {
        $data    = array(
            'name'     => $form->getValue('name'),
            'order'    => $form->getValue('order'),
            'quest_id' => $this->_quest->quest_id
        );
        $cluster = $this->getService('QuestCluster')->insert($data);
        $this->_redirectToIndex();
    }

    public function setDefaults(Zend_Form $form)
    {
        $clusterId = $this->_getParam('cluster_id', 0);
        $cluster   = $this->getService('QuestCluster')->find($clusterId)->current();
        $data = $cluster->getData();
        $form->populate($data);
    }


    public function deleteAction()
    {
        /** @var HM_Quest_Question_QuestionService $questQuestionQuestService */
        $questQuestionQuestService = $this->getService('QuestQuestionQuest');

        $id = (int) $this->_getParam('cluster_id', 0);
        if ($id) {
            $this->delete($id);
            $questQuestionQuestService->updateWhere(array(
                'cluster_id' => 0
            ), array(
                'cluster_id = ?' => $id
            ));
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }

    public function deleteByAction() {
        $ids = explode(',', $this->_request->getParam('postMassIds_'.$this->gridId));

        $questClusterService = $this->getService('QuestCluster');
        /** @var HM_Quest_Question_QuestionService $questQuestionQuestService */
        $questQuestionQuestService = $this->getService('QuestQuestionQuest');

        if(count($ids)){
            if($this->subjectId){
                $clusters = $questClusterService->fetchAll(
                    $questClusterService->quoteInto('cluster_id IN (?)', $ids)
                );
                $cantDelete = false;
                foreach ($clusters as $cluster) {
                    if($cluster->subject_id){
                        $this->delete($value);
                    } else {
                        $cantDelete = true;
                    }
                }
            } else {
                foreach ($ids as $id) {
                    $this->delete($id);

                    $questQuestionQuestService->updateWhere(array(
                        'cluster_id' => 0
                    ), array(
                        'cluster_id = ?' => $id
                    ));
                }
            }
        }
        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
        if($cantDelete){
            $this->_flashMessenger->addMessage(_('Некоторые элементы не могут быть удалены.'));
        }
        $this->_redirectToIndex();
    }

    public function delete($id) {
        $this->getService('QuestCluster')->delete($id);
    }
    
    public function updateActions($subjectId, $actions)
    {
        /*if (!$subjectId && $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                )
        )) { // если тест из базы знаний
            $this->unsetAction($actions, array('controller' => 'cluster', 'action' => 'edit'));
            $this->unsetAction($actions, array('controller' => 'cluster', 'action' => 'delete'));
        }*/
        return $actions;
    } 
    

}
