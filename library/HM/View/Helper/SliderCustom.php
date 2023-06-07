<?php

class HM_View_Helper_SliderCustom extends ZendX_JQuery_View_Helper_Slider
{
    public function SliderCustom($id, $value = null, array $params = array(), array $attribs = array())
    {
        $js = "$(document).ready(function(){
                  $('#".$attribs['id']."-slider').bind('slidecreate', function(){showSliderValue_".$attribs['id']."()});
                  $('#".$attribs['id']."-slider').bind('slidestop', function(){showSliderValue_".$attribs['id']."()});
                  $('#".$attribs['id']."-slider').bind('slide', function(){showSliderValue_".$attribs['id']."()});
              });
              function showSliderValue_".$attribs['id']."() {
                  $('#".$attribs['id']."-slider').find('.ui-slider-handle').html($('#".$attribs['id']."-slider').slider('values', 0));
              };";
        
        $this->jquery->addOnLoad($js);
        return parent::slider($id, $value, $params, $attribs);
    }
}