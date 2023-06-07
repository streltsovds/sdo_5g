<?php
class HM_Form_Modifier_FulltimeSession extends HM_Form_Modifier_Fulltime
{
    protected $hideFields    = array('criterion', 'longtime');
    protected $displayGroups = array('Fulltime1', 'Fulltime2', 'Fulltime3', 'Fulltime4','fieldset-classifiers', 'classifiers');
    protected $modifyFields  = array(
        array('begin', 'required'),
        array('end',   'required'),
        array('primary_type',    'disable'),
    );
}