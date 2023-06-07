<?php
class HM_View_Helper_WorkflowBlock extends HM_View_Helper_Abstract
{

    public function workflowBlock($model)
    {
        $this->view->model = $model;
        $this->view->process = $model->getProcess();
        return $this->view->render('workflowBlock.tpl');
    }

    /**
     * Создает список действий в хелпере из массива.
     * нужен для рекурсивной обработки массива и возможности создания нескольких списков действий.
     * Интерфейс для getItemRecursive
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 24.01.2013
     * @param array  $listOfStates
     * @param string $listDecorator
     * @param string $itemDecorator
     *
     * @return string
     */
    public function renderStatesList($listOfStates, $listDecorator = "<ul>{{item}}</ul>", $itemDecorator = "<li><div class='wid_control_link_{{class}}'></div>{{item}}</li>")
    {
        return $this->getItemRecursive($listOfStates,$listDecorator,$itemDecorator);
    }

    /**
     * Создает список действий в хелпере из массива.
     * нужен для рекурсивной обработки массива и возможности создания нескольких списков действий.
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 24.01.2013
     * @param $list
     * @param $listDecorator
     * @param $itemDecorator
     *
     * @return mixed
     */
    private function getItemRecursive($list, $listDecorator, $itemDecorator)
    {
        $result = "";
        if(is_array($list)) {
            foreach ($list as $item) {
                if (is_array($item)) {
                    $result .= $this->getItemRecursive($item, $listDecorator, $itemDecorator);
                } else {
                    $itemRendered = $item->render();
                    $decorate = $item->getDecorate();
                    if (!empty($itemRendered)) {
                        if($item->isDecorated()) {
                            $result .= str_replace(array('{{item}}', '{{class}}'), array($itemRendered, $decorate), $itemDecorator);
                        } else {
                            $result .= $itemRendered;
                        }
                    }
                }
            }
            return str_replace("{{item}}", $result, $listDecorator);
        }
        return "";
    }
}