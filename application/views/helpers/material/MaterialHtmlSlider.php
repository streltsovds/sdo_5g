<?php

class HM_View_Helper_MaterialHtmlSlider extends HM_View_Helper_MaterialAbstract
{
    public function materialHtmlSlider($material)
    {
        $content = unserialize($material->content);
        $slides = [];

        foreach ($content as $i=>$item) {
            $url = '/resource/index/view-pseudocode/resource_id/'.$material->resource_id.'/slide_id/'.$i;
            $slides[] = [
                'html' => "<iframe style='height:calc(100% - 10px)' width=100% src='{$url}'></iframe>"//$item['compiled']
            ];
        }

        $this->view->slidesJson = HM_Json::encodeErrorThrow($slides);

        return parent::render($material);
    }
}