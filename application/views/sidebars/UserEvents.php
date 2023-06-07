<?php
class HM_View_Sidebar_UserEvents extends HM_View_Sidebar_Ajax
{
    protected $_numberOfNotifications;

    public function __construct()
    {
        // рендеринг полученного json'а
        // $this->view->inlineScript()->appendScript($js);
        // $this->view->inlineScript()->appendFile($url);
    }

    public function getIcon()
    {
        $aclService = $this->getService('Acl');

        $types = array(
            'forumAddMessage',
            'blogAddMessage',
            'forumInternalAddMessage',
            'wikiAddPage',
            'wikiModifyPage',
            'blogInternalAddMessage',
            'wikiInternalAddPage',
            'wikiInternalModifyPage',
            'courseAddMaterial',
            'courseAttachLesson',
            'courseScoreTriggered',
            'courseTaskComplete',
            'courseTaskAction',
            'commentAdd',
            'commentInternalAdd',
            'courseTaskScoreTriggered',
            'personalMessageSend'
        );

        $user = $this->getService('User')->getCurrentUser();
        $userRole = $user->role;

        if ($userRole) {
            if ($aclService->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
                $exclude = array(
                    'courseAddMaterial',        // Добавление материала в курс
                    'courseAttachLesson',       // Назначение занятия студенту
                    'courseScoreTriggered',     // Выставление оценки за курс
                    'courseFeedbackRequest',    // Сбор обратной связи по курсу
                    'commentAdd',               // Добавление комментария к чему-либо на уровне портала
                    'commentInternalAdd',       // Добавление комментария к чему-либо на уровне курса
                    'courseTaskScoreTriggered'  // Выставление оценки за занятие
                );
            } elseif (($aclService->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_STUDENT))) {
                $exclude = array('courseTaskAction');
            } else {
                // для всех остальных вообще всё сносим
                $exclude = array(
                    'courseAddMaterial',        // Добавление материала в курс
                    'courseAttachLesson',       // Назначение занятия студенту
                    'courseScoreTriggered',     // Выставление оценки за курс
                    'courseFeedbackRequest',    // Сбор обратной связи по курсу
                    'commentAdd',               // Добавление комментария к чему-либо на уровне портала
                    'commentInternalAdd',       // Добавление комментария к чему-либо на уровне курса
                    'courseTaskScoreTriggered', // Выставление оценки за занятие
                    'courseTaskAction'          // Выполнение задания студентом
                );
            }

            $resultTypes = array();
            foreach ($types as $eventType) {
                if (!in_array($eventType, $exclude)) {
                    $resultTypes[] = $eventType;
                }
            }

            $limit = $this->getService('Option')->getOption('maxUserEvents');
            $select = $this->getService('User')->getSelect();
            $select->from(
                array(
                    'm' => 'messages'
                ),
                array(
                    'count' => new Zend_Db_Expr("COUNT(m.message_id)"),
                ))
                ->where('m.to = ?', $this->getService('User')->getCurrentUserId())
                ->where('m.readed = ?', 0)
                ->limit($limit ?: 15)
            ;

            $rowset = $select->query()->fetchAll();
            $this->_numberOfNotifications = $rowset[0]['count'];
        }

        /*switch (true) {
            case $this->_numberOfNotifications > 9:
                $iconName = 'notificationsActive';
                break;
            case $this->_numberOfNotifications > 0:
                $iconName = 'notifications';
                break;
            default:
                $iconName = 'notificationsNone';
                break;
        }*/

        $this->view->numberOfNotifications = $this->_numberOfNotifications;

        $iconName = 'notifications';

        if ($this->_numberOfNotifications > 0) {
            return $iconName;
        } else {
            return $iconName;
        }
    }

    public function getCount()
    {
        return $this->_numberOfNotifications > 9 ? '9+' : $this->_numberOfNotifications;
    }

    public function getAjaxUrl()
    {
        return $this->view->url(array(
            'baseUrl' => '',
            'module' => 'message',
            'controller' => 'ajax',
            'action' => 'get-user-events',
        ), null, true);
    }

    public function getContent()
    {
        $data = [];
        $data['ajaxUrl'] = $this->getAjaxUrl();
        $data['sidebarName'] = $this->getName();

        return $this->view->partial('userEvents.tpl', $data);
    }

    public function getTitle()
    {
        return _('Сообщения');
    }

    public function getToggle() {
        return
            '<hm-sidebar-toggle 
                has-avatar 
                sidebar-name="' . $this->getName() . '"
                title="' . $this->getTitle() . '">
             <template v-slot:notification>
                <hm-notification-counter/>
             </template>
             <svg-icon color=" #FFFFFF" name="'. $this->getIcon() .'" count="' . $this->getCount() . '" title="" />
            </hm-sidebar-toggle>';
    }
}
