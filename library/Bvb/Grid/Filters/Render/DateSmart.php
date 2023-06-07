<?php   //[che 5.06.2014 #16976]
        // Для защиты Oracle от "мусора" - мало ли что введут пользователи и программисты в поле даты!
class Bvb_Grid_Filters_Render_DateSmart extends Bvb_Grid_Filters_Render_RenderAbstract
{
    function getFields ()
    {
        return array('from', 'to');
    }

    public function getConditions ()
    {
        return array('from' => '>=', 'to' => '<=');
    }


    public function render ()
    {
        $this->removeAttribute('id');

        $view       = $this->getView();
        $gridId     = $this->getGridId();
        $fieldName  = $this->getFieldName();
        $attributes = $this->getAttributes();

        $dateFromId = "filter_".$gridId.$fieldName."_from";
        $dateToId   = "filter_".$gridId.$fieldName."_to";

        $datePickerOptions = array(
            'showOn'          => 'button',
            'buttonImage'     => $view->serverUrl('/images/icons/calendar.png'),
            'buttonImageOnly' => true,
        );

        $view->datePicker($dateFromId, null, $datePickerOptions);
        $view->datePicker($dateToId, null, $datePickerOptions);

        $fromAttributes = array_merge($attributes, array(
            'id' => $dateFromId
        ));

        $toAttributes = array_merge($attributes, array(
            'id' => $dateToId
        ));

        return  '<div class="grid-filter-daterange">'.
                    '<div class="grid-filter-daterange-item grid-filter-daterange-from">'.
                        '<div class="wrapFiltersInput">'.
                            '<label for="'.$dateFromId.'">'.
                                $this->__('From').':'.
                            '</label>'.
                            $view->formText($fieldName.'[from]', urldecode($this->getDefaultValue('from')), $fromAttributes).
                            '<span class="clearFilterSpan">&nbsp;</span>'.
                        '</div>'.
                    '</div>'.
                    '<div class="grid-filter-daterange-item grid-filter-daterange-to">'.
                        '<div class="wrapFiltersInput">'.
                            '<label for="'.$dateToId.'">'.
                                $this->__('To').':'.
                            '</label>'.
                            $view->formText($fieldName.'[to]', urldecode($this->getDefaultValue('to')), $toAttributes).
                            '<span class="clearFilterSpan">&nbsp;</span>'.
                        '</div>'.
                    '</div>'.
                '</div>';

    }

    public function transform($date, $key)
    {
        $date = urldecode($date);
        $date = str_replace('-', '.', $date);
        $date = explode('.', $date);

        foreach($date as $i=>$d) {
            $date[$i] = intval($d);
        }

        $D = $M = $Y = -1;
        switch(count($date))
        {
            case 1:
                $date = $date[0];
                if($date==0)
                {
                    $D = 1;
                    $M = 1;
                    $Y = $key == 'to'?2100:0;
                }
                else if($date<=31)
                {
                    $D = $date;
                    $M = date("m");
                    $Y = date("Y");
                }
                else if($date<=12)
                {
                    $D = date("d");
                    $M = $date;
                    $Y = date("Y");
                }
                else
                {
                    $D = date("d");
                    $M = date("m");
                    $Y = $date;
                }
                break;

            case 2:
                if($date[0]>=12 || $date[1]<=12)
                {
                    $D = $date[0];
                    $M = $date[1];
                    $Y = date("Y");
                }
                else if($date[1]<=12)
                {
                    $D = date("d");
                    $M = $date[0];
                    $Y = $date[1];
                }
                break;

            case 3:
                $D = $date[0];
                $M = $date[1];
                $Y = $date[2];
                break;
        }

        $Y = ($Y<2000?$Y+2000:$Y);
        $date = sprintf('%02d.%02d.%02d', $D, $M, $Y);
        $dateObject = new Zend_Date($date, 'dd.MM.yyyy');
        if ($key == 'to') {
            $value = $dateObject->toString('yyyy-MM-dd 23:59:59');
        } else {
            $value = $dateObject->toString('yyyy-MM-dd');
        }

        return $value;
    
    }

    public function hasConditions()
    {
        return false;
    }

    public function buildQuery($filter)
    {
        if (!is_array($filter)) {
            $params = $filter;
            $filter = array();
            list($from, $to) = explode(',', $params);
            if (!empty(trim($from))) $filter ['from'] = trim($from);
            if (!empty(trim($to)))   $filter ['to']   = trim($to);
        }
        $where = '';
        if (isset($filter['from'])) {
            $where .= '(';
            $where .= $this->getSelect()->getAdapter()->quoteInto($this->getFieldName().' >= ?', $this->transform($filter['from'], 'from'));
        }
        if (isset($filter['to'])) {
            if (isset($filter['from'])) {
                $where .= ' AND ';
            } else {
                $where .= '(';
            }

            $where .= $this->getSelect()->getAdapter()->quoteInto($this->getFieldName().' <= ?',$this->transform($filter['to'], 'to'));
        }
        if (strlen($where)) {
            $where .= ') ';
        }

        $this->getSelect()->where($where);
    }
    
}