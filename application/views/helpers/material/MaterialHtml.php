<?php

class HM_View_Helper_MaterialHtml extends HM_View_Helper_MaterialAbstract
{
    public function materialHtml($material)
    {
        if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $material->content, $matches)) {
            $material->content = $matches[1];
        }

        return parent::render($material);
    }
}