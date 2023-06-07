<?php
class HM_Form_Modifier_FulltimeBase extends HM_Form_Modifier_Fulltime
{
    protected $hideFields    = array('criterion_text', 'begin', 'end', 'period_restriction_type');
    protected $displayGroups = array('Fulltime1', 'Fulltime2', 'Fulltime3', 'Fulltime4', 'classifiers');
}