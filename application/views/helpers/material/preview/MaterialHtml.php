<?php

class HM_View_Helper_MaterialHtml extends HM_View_Helper_MaterialAbstract
{
    public function materialHtml($material)
    {
        return parent::render($material);
    }
}