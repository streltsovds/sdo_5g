<?php

class HM_Grid_ActionsList extends HM_Grid_AbstractClass
{
    protected $_actions = array();

    /**
     * @var HM_Grid_AbstractGrid
     */
    protected $_grid = null;

    protected $_invisibleActions = array();

    public function __construct(HM_Grid_AbstractGrid $grid)
    {
        $this->_grid = $grid;
    }

    public function add($code, $url)
    {
        $action = new HM_Grid_Action($code, $url);

        if (!$this->_urlAllowed($url)) {
            return $action;
        }

        $this->_actions[] = $action;
        $this->_grid->getBvbGrid()->setAction($action);

        return $action;
    }

    public function setInvisibleActions($actions)
    {
        $this->_invisibleActions = $actions;
    }

    public function isEmpty()
    {
        return count($this->_actions) === 0;
    }

    public function toString($row)
    {
        $this->setInvisibleActions(array());

        $this->_grid->checkActionsList($row, $this);

        $invisibleActions = $this->_invisibleActions;

        $result  = '<menu class="grid-row-actions">';
        $result .= '<ul class="dropdown">';

        /** @var HM_Grid_Action $action */
        foreach ($this->_actions as $action) {

            if (in_array($action->getCode(), $invisibleActions)) {
                continue;
            }

            $result .= '<li>'.$action->toString($row).'</li>';
        }

        $result .= '</ul>';
        $result .= '</menu>';

        return $result;

    }
}