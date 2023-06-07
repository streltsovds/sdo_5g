<?php

class HM_Grid_MassActionsList
{
    protected $_grid;

    public function __construct(HM_Grid_AbstractGrid $grid)
    {
        $this->_grid = $grid;
    }

    public function add($url, $caption, $confirm = null)
    {
        return new HM_Grid_MassAction(array(
            'hmGrid'  => $this->_grid,
            'url'     => $url,
            'caption' => $caption,
            'confirm' => $confirm
        ));
    }
}