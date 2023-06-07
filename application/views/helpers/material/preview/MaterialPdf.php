<?php

class HM_View_Helper_MaterialPdf extends HM_View_Helper_MaterialAbstract
{
    public function materialPdf($material)
    {
        return parent::render($material);
    }
}