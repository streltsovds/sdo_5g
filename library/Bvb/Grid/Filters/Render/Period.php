<?php   //[che 5.06.2014 #16976]
        // Для защиты Oracle от "мусора" - мало ли что введут пользователи и программисты в поле даты!
class Bvb_Grid_Filters_Render_Period extends Bvb_Grid_Filters_Render_RenderAbstract
{


    function getFields ()
    {
        return array('from', 'to');
    }

    /*   function normalize($value,$part ='')
    {    
    //echo date('d.m.Y',strtotime($value));
        return date('d.m.Y',strtotime($value));
    }*/


    public function getConditions ()
    {
        return array('from' => '>=', 'to' => '<=');
    }


    function render ()
    {
        $this->removeAttribute('id');
        $date_from_id = "filter_".$this->getGridId().$this->getFieldName()."_from";
        $date_to_id = "filter_".$this->getGridId().$this->getFieldName()."_to";
        //$this->setAttribute('style','width:85px !important;');
        //print_r($this);
        $script="
                    $('#{$date_from_id}, #{$date_to_id}').datepicker({
                           showOn: 'button',
                           buttonImage: '".$this->getView()->serverUrl()."/images/icons/calendar.png',
                           buttonImageOnly: true });";
        $this->getView()->jQuery()->addOnload($script);

        return '<div class="grid-filter-daterange">'.
                    '<div class="grid-filter-daterange-item grid-filter-daterange-from">'.
                        '<div class="wrapFiltersInput">'._('От').':'.
                            $this->getView()->formText($this->getFieldName().'[from]', $this->getDefaultValue('from'), array_merge($this->getAttributes(),array('id'=>'filter_'.$this->getGridId().$this->getFieldName().'_from'))).'<span class="clearFilterSpan">&nbsp;</span>'.
                        '</div>'.
                    '</div>'.
                    '<div class="grid-filter-daterange-item grid-filter-daterange-to">'.
                        '<div class="wrapFiltersInput">'._('До').':'.
                            $this->getView()->formText($this->getFieldName().'[to]', $this->getDefaultValue('to'), array_merge($this->getAttributes(),array('id'=>'filter_'.$this->getGridId().$this->getFieldName().'_to'))).'<span class="clearFilterSpan">&nbsp;</span>'.
                        '</div>'.
                    '</div>'.
               '</div>';
    }

    public function transform($date, $key)
    {
        $date = urldecode($date);
        $date = str_replace('-', '.', $date);

        $dateObject = new Zend_Date($date, 'dd.MM.yyyy');
        if ($key == 'to') {
            $value = $dateObject->toString('yyyy_MM');
        } else {
            $value = $dateObject->toString('yyyy_MM');
        }
        
        return $value;
    
    }
   
}