<?php
class HM_Form_MeetingStep3 extends HM_Form_SubForm
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('meetingStep3');

        $session = $this->getSession();
        $isManualAssign = ($session['step2']['assign_type'] == HM_Meeting_Task_TaskModel::ASSIGN_TYPE_MANUAL);

        $prevSubForm = 'step2';
        if ($this->getParam('subForm', false) == 'step2') {
            $prevSubForm = 'step1';
        }
        $this->addElement('hidden', 'prevSubForm', array(
            'Required' => false,
            'Value' => $prevSubForm
        ));

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'meeting', 'controller' => 'list', 'action' => 'index', 'project_id' => $this->getParam('project_id', 0)), null, true)
        ));

        $this->addElement('hidden', 'meeting_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'project_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $participants = array();
        $this->addElement($this->getDefaultMultiSelectElementName(), 'participants',
            array(
                'Label' => '',
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'remoteUrl' => $this->getView()->url(array('module' => 'meeting', 'controller' => 'ajax', 'action' => 'participants-list')),
                'multiOptions' => $participants
            )
        );

        $this->addElement('RadioGroup', 'switch', array(
            'Label' => '',
        	'Value' => 0,
            //'Required' => true,
            'MultiOptions' => array(0 => _('Все участники конкурса'), 1 => _('Список участников')),
            'form' => $this,
            'dependences' => array(1 => array('participants'),
                                   2 => array('subgroups')
                             )
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'all', array(
            'Label' => _('Автоматически назначать всем новым участникам конкурса'),
            'Required' => false,
            //'Validators' => array('Int'),
            //'Filters' => array('Int'),
            'Value' => 1,
            /*'MultiOptions' => array(
                0 => _('Нет'),
                1 => _('Да')
            ) */
            //'Checked' => true
        ));

        //$displayArray = array("moderator");
        $moderatorRequired = false;

        $session = $this->getSession();
        $eventId = $session['step1']['event_id'];
        if ($eventId < 0) {
            $event = $this->getService('Event')->getOne(
                $this->getService('Event')->find(-$eventId)
            );

            if ($event) {
                $eventId = $event->tool;
            }
        }

        switch($eventId) {
            case HM_Event_EventModel::TYPE_EXERCISE:
            case HM_Event_EventModel::TYPE_TEST:
                $moderatorRequired = true;
                break;
            case HM_Event_EventModel::TYPE_WEBINAR:
                $moderatorRequired = true;
                $displayArray[] = "moderator";
                break;
        }

        $moderators = $moderatorRequired ? array() : array(0 => _('Нет'));
		$moderators = array(/*0 => _('Нет')*/);
        $collection = $this->getService('Moderator')->fetchAllDependence(
            'User',
            $this->getService('Moderator')->quoteInto('project_id = ?', $this->getParam('project_id', 0))
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $moderator = $item->getUser();
                if ($moderator) {
                    $moderators[$moderator->MID] = $moderator->getName();
                    $moderators[$moderator->MID] = $moderator->getName();
                }
            }
        }


        $collection = $this->getService('Participant')->fetchAllDependence(
            'User',
            $this->getService('Participant')->quoteInto('CID = ?', $this->getParam('project_id', 0))
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $moderator = $item->getUser();
                if ($moderator) {
                    $moderators[$moderator->MID] = $moderator->getName();
                }
            }
        }

        asort($moderators);
        $moderators_list = array(0 => _('Нет'));
        foreach ($moderators as $key=>$value) {
            $moderators_list[$key] = $value;
        }

        if (!is_null($displayArray) && in_array("moderator", $displayArray)) {
            $this->addElement($this->getDefaultSelectElementName(), 'moderator', array(
                'Label' => _('Тьютор'),
                'Required' => $moderatorRequired,
                'Validators' => array(
                    'Int',
                    //array('GreaterThan', false, array(0))
                ),
                'Filters' => array(
                    'Int'
                ),
                'MultiOptions' => $moderators
            ));
        }

        if (!is_null($displayArray) && in_array("moderator", $displayArray)) {
            $this->addElement($this->getDefaultSelectElementName(), 'moderator', array(
                'Label' => _('Модератор'),
                'Required' => false,
                'Validators' => array(
                    'Int',
                    //array('GreaterThan', false, array(0))
                ),
                'Filters' => array(
                    'Int'
                ),
                'MultiOptions' => $moderators_list
            ));
        }

        if (count($displayArray)) {
            $this->addDisplayGroup(
                $displayArray,
                'MeetingControl',
                array('legend' => _('Модератор'))
            );
        }

        $this->addDisplayGroup(
            array('switch',
                'prevSubForm',
                'cancelUrl',
                'meeting_id',
                'project_id',
                'participants',
            	'subgroups',
                'all',
                //'submit'
            ),
            'MeetingGroup',
            array('legend' => _('Участники'))
        );

        // ручное назначение вариантов заданий
        if ($isManualAssign) {

            $this->removeElement('switch');
            $this->removeElement('participants');
            $this->removeElement('subgroups');
            $this->removeElement('all');
            $this->removeDisplayGroup('MeetingGroup');

            $where           = $this->getService('Participant')->quoteInto('project_id=?', $this->getParam('project_id', -1));
            $projectParticipants = $this->getService('Participant')->fetchAllDependence('User', $where);
            $participantsList    = array();

            if (count($projectParticipants)) {
                foreach ($projectParticipants as $participant) {
                    if (count($participant->users)) {
                        foreach ($participant->users as $user) {
                            $participantsList[$user->MID] = $user->getName();
                        }
                    }
                }
            }

            $taskId       = $session['step2']['module'];
            $task         = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));
            $ids          = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $task->data);
            $questionList = $this->getService('Question')->fetchAll(array('kod IN(?)' => $ids))->getList('kod','qdata');
            $userVariants = array();

            foreach ($questionList as &$value) $value = substr(strip_tags($value), 0, 100);

            if ($this->getParam('meeting_id',0)) {
                $interviews = $this->getService('Interview')->fetchAll(array('meeting_id=?' => (int) $this->getParam('meeting_id',0)));
                if (count($interviews)) {
                    foreach ($interviews as $itwItem) {
                        if (array_key_exists($itwItem->user_id, $participantsList) || array_key_exists($itwItem->to_whom, $participantsList)) {
                            $userId = (array_key_exists($itwItem->user_id, $participantsList))? $itwItem->user_id : $itwItem->to_whom;
                            $userVariants[$userId] = $itwItem->question_id;
                        }
                    }
                }
            }
            $this->addElement(
                'associativeSelect',
                'user_variant',
                array(
                    'Label'  => _('Варианты'),
                    'keys'   => $participantsList,
                    'values' => $questionList,
                    'Value'  => $userVariants
                )
            );

            $this->addDisplayGroup(
                array('user_variant'),
                'MeetingVariants',
                array('legend' => _('Назначение вариантов участникам'))
            );
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}