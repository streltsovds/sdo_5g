<?php
class HM_View_Helper_WorkflowBulbs extends HM_View_Helper_Abstract
{
    public function workflowBulbs ($model)
    {
        $this->view->model = $model;
        $this->view->process = $process = $model->getProcess();
        if ($processAbstract = $process->getProcessAbstract()) { // он всегда должен быть; проверка на всякий случай, если что-то не в порядке в базе
            $this->view->isStrict = $processAbstract->isStrict();
        }

        $states = [];
        foreach ($process->getStates() as $state) {
            $row = [];
            $row['isVisible'] = $state->isVisible();
            $row['classes'] = [$state->getClass(), $model->getProcessStateClass($state)];
            $row['title'] = $state->getTitle();
            $states[] = $row;
        }

        $this->view->states = $states;
        $this->view->get_url =  $this->view->url(array('action' => 'workflow'));

        return $this->view->render('workflowBulbs.tpl');
    }
}
