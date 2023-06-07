<?php

// аккордеон теста
class HM_Controller_Action_Quest extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Context;

    protected $_quest;
    protected $_questId;

    public function init()
    {
        $backUrl = null;
        $this->questRestrict();

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        $questId = (int) $this->_getParam('quest_id', 0);
        if ($questId) {

            $quest = $this->_quest = $this->getOne(
                $this->getService('Quest')->findDependence(array('Cluster', 'QuestionQuest'), $questId)
            );

            if ($quest) {

                $this->view->quest = $this->_quest;

                $isDeny = $this->getService('Quest')->isDenyByCreatorRole($this->_quest->creator_role);
                if($isDeny) {
                    $flashMessenger = $this->_helper->getHelper('FlashMessenger');
                    $redirector = $this->_helper->getHelper('ConditionalRedirector');

                    $flashMessenger->addMessage(_("Доступ запрещен"));
                    $redirector->gotoSimpleAndExit('index', 'index', 'index');
                }

                $subjectId = (int) $this->_getParam('subject_id', $this->_quest->subject_id);

                if ($acl->isSubjectContext()) {
                    // @todo: не всегда
                    $backUrl = $this->view->url([
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'edit',
                        'subject_id' => $subjectId,
                    ], null, true);
                }

                // в любых контекстах показываем actions и sidebar от теста
                if(!empty($this->_quest)) {

                    $this->initContext($this->_quest, 'quest');

                    switch ($this->_quest->type) {
                        case HM_Quest_QuestModel::TYPE_TEST:

                            $this->view->addSidebar('test', ['model' => $this->_quest]);

                            if (!$acl->isSubjectContext()) {
                                $backUrl = $this->view->url([
                                    'module' => 'quest',
                                    'controller' => 'list',
                                    'action' => 'tests',
                                ], null, true);
                            }
                            break;

                        case HM_Quest_QuestModel::TYPE_POLL:

                            $this->view->addSidebar('poll', ['model' => $this->_quest]);

                            if (!$acl->isSubjectContext()) {
                                $backUrl = $this->view->url([
                                    'module' => 'quest',
                                    'controller' => 'list',
                                    'action' => 'polls',
                                ], null, true);
                            }
                            break;

                        case HM_Quest_QuestModel::TYPE_PSYCHO:

                            $this->view->addSidebar('poll', ['model' => $this->_quest]);

                            if (!$acl->isSubjectContext()) {
                                $backUrl = $this->view->url([
                                    'module' => 'quest',
                                    'controller' => 'list',
                                    'action' => 'psycho',
                                ], null, true);
                            }
                            break;

                        case HM_Quest_QuestModel::TYPE_FORM:

                            // просто form ломает автозагрузку
                            $this->view->addSidebar('freeform', ['model' => $this->_quest]);

                            if (!$subjectId) {
                                $backUrl = $this->view->url([
                                    'module' => 'quest',
                                    'controller' => 'list',
                                    'action' => 'form',
                                ], null, true);
                            }

                            break;
                    }

/* Перебивает работу switch выше!
                    if (!$acl->isSubjectContext()) {
                        $backUrl = $this->view->url([
                            'module' => 'quest',
                            'controller' => 'list',
                            'action' => 'tests',
                        ], null, true);
                    }
*/

                    $this->view->setHeader($this->_quest->name);
                }
            }
        }

        if ($backUrl) $this->view->setBackUrl($backUrl);

        parent::init();
    }

    public function getContextNavigationModifiers()
    {
        $modifiers = [];
        $params = $this->getRequest()->getParams();

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        // Запрещаем тьюторам добавлять вопросы И БЛОКИ ТОЖЕ. Этот Remover работает НА ВСЕ экшны "new"
        if($acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER) && !$acl->isSubjectContext())
            $modifiers[] = new HM_Navigation_Modifier_Remove_Action('action', 'new');

        if($this->_quest) {
            if ($this->_quest->type == HM_Quest_QuestModel::TYPE_PSYCHO) {
                $modifiers[] = new HM_Navigation_Modifier_Remove_Page('label', _('Статистика ответов теста'));
            } else {
                $modifiers[] = new HM_Navigation_Modifier_Remove_Page('label', _('Показатели'));
                $modifiers[] = new HM_Navigation_Modifier_Remove_Page('label', _('Категории'));
                $modifiers[] = new HM_Navigation_Modifier_Remove_Page('label', _('Пересчеты'));
            }

            if ($this->_quest->type === HM_Quest_QuestModel::TYPE_POLL) {
                $modifiers[] = new HM_Navigation_Modifier_Rename_Page('label', _('Статистика ответов теста'), _('Результаты'));

                // Не через remover, потому что quest->type никак не определяется по URL
                if (sprintf('%s:%s:%s', $params['module'], $params['controller'], $params['action'] === 'quest:question:list')) {
                    $modifiers[] = new HM_Navigation_Modifier_Remove_Action('action', 'import');
                }
            }
        }


        return $modifiers;
    }
}