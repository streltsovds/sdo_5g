<?php
class HM_View_Helper_Workflow extends HM_View_Helper_Abstract
{
    protected $states;
    protected $process;

	public function workflow($model)
	{
        $this->view->id = $model->getPrimaryKey();
        $this->view->get_url = $this->view->url(array('action' => 'workflow'));
        $this->view->workflow = $this->getFormattedDataWorkflow($model, $this->getUserRole());

		return $this->view->render('workflow.tpl');
	}

	public function getUserRole() {
        return Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
    }

	public function getControlLinks($listOfStates) {
        return $this->getControlLinksRecursive($listOfStates);
    }

    public function getControlLinksRecursive($listOfStates)
    {
        $result = [];
        if(!is_array($listOfStates)) return null;

        /** @var HM_State_Action_Link $state */
        foreach ($listOfStates as $state) {
            $result[] = is_array($state) ? $this->getControlLinkRecursive($state) : $state->getFormattedParams();
        }

        return $result;
    }

    public function getFormattedDataWorkflow($model) {

        $this->process = $model->getProcess();
        $this->states = $this->process->getStates();

	    $response = [];
        $response['title'] = $model->getName();

        $formattedStates = [];

        foreach ($this->states as $state) {
            if (!$state->isVisible()) continue;

            $row = [];
            $row['title'] = $state->getTitle();
            $row['class'] = $state->getClass();
            $row['status'] = $status = $state->getStatus();

            if ($this->process->getStateDatesMode() != HM_Process_Abstract::MODE_STATE_DATES_HIDDEN) {
                $row['deadline'] = $this->getDeadlineData($state);
            }

            $row['description'] = $this->getDescription($state);
            $row['extendedDescription'] = $this->getExtendedDescription($state);
            $row['controlLinks'] = $this->getControlLinks($state->getActions());
            $row['forms'] = $this->getForms($state);

            $formattedStates[] = $row;
        }

        $response['states'] = $formattedStates;

        return $response;

    }

    private function getDeadlineData($state)
    {
        $status = $state->getStatus();
        $deadline = [];

        if (in_array($status, [
                HM_State_Abstract::STATE_STATUS_WAITING,
                HM_State_Abstract::STATE_STATUS_CONTINUING,
            ])
            && ($this->process->getStateDatesMode() != HM_Process_Abstract::MODE_STATE_DATES_READONLY)) {

            $data = $state->getStateData();
            if (!isset($dateSaveUrl))
                $deadline['dateSaveUrl'] = $this->view->url(array('baseUrl'=>'','module'=>'state','controller'=>'edit','action' =>'edit','field'=> 'date',
                    'stateId'    => $data->state_of_process_id,
                    'state'      => null,
                ));

            if ($data->begin_date_planned) {
                $deadline['begin'] = [
                    'label' => _('начало'),
                    'date'  => (new HM_Date($data->begin_date_planned))->get('dd.MM.YYYY')
                ];
                $deadline['end'] = [
                    'label' => _('окончание'),
                    'date'  => (new HM_Date($data->end_date_planned))->get('dd.MM.YYYY')
                ];

                $deadline['label'] = _('Запланировано на');

                $allowedRoles = $this->getAllowedRoles();

                if (in_array($this->getUserRole(), $allowedRoles)) {
                    $deadline['state'] = $this->process->getProcessAbstract()->getStateClass($state);
                }
            }

        } elseif ($status == HM_State_Abstract::STATE_STATUS_FAILED || $status == HM_State_Abstract::STATE_STATUS_PASSED) {
            $isFailed = $status == HM_State_Abstract::STATE_STATUS_FAILED;
            $deadline = [
                'message'   => $isFailed ? $state->getFailMessage() : $state->getSuccessMessage()
            ];
            if ($date = $state->getFactDate()) {
                $deadline['date'] = $date;
            }
        }

        return $deadline;
    }

    private function getAllowedRoles()
    {
        $allowedRoles = [];
        foreach ($this->states as $state) {
            // разрешаем редактировать календарь, если хотя бы на одном шаге у роли есть хотя бы один Action
            $stateActions = $state->getActions();
            foreach ($stateActions as $stateAction) {
                $roles = is_array($stateAction->_restriction) ? $stateAction->_restriction['roles'] : array();
                $allowedRoles = array_unique(array_merge($allowedRoles, $roles));
            }
        }
        return $allowedRoles;
    }

    private function getForms($state)
    {
        $forms = $state->getForms();
        if (!is_array($forms)) $forms = array($forms);

        $formsData = [];
        foreach ($forms as $form) {
            if (!is_object($form)) continue;
            $formsData[] = [
                'template'  =>  str_replace("'", "Ⓠ", $form->render()),
                'icon'      => $form->getIconConfig()
            ];
        }
        return $formsData;
    }

    private function getDescription($state)
    {
        $text = trim($state->getDescription());

        return !empty($text) ? $text : null;
    }


    private function getExtendedDescription($state)
    {
        $extendedDescription = [];
        $desc = $state->getExtendedDescription();
        if (!$desc) return null;

        if (trim(strip_tags($desc['comment']))) {
            $extendedDescription['comment'] = nl2br($desc['comment']);
        }

        if (!empty($desc['files'])) {
            $extendedDescription['files'] = [];
            foreach ($desc['files'] as $file) {
                $extendedDescription['files'][] = [
                    'url' => $file->getUrl(),
                    'name' => $file->getDisplayName(),
                    'creator' => ($creator = $file->getCreator()) ? $creator['fio'] : null
                ];
            }
        }

        return count($extendedDescription) ? $extendedDescription : null;
    }
}