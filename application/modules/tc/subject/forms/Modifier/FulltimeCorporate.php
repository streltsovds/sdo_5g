<?php
class HM_Form_Modifier_FulltimeCorporate extends HM_Form_Modifier_Fulltime
{
    protected $hideFields    = array('primary_type', 'criterion', 'plan_users', 'mark_required', 'check_form', 'after_training');
    protected $displayGroups = array('Fulltime1', 'Fulltime2', 'Fulltime3', 'Fulltime4', 'classifiers');
    protected $modifyFields  = array(
        array('provider_id', 'disable'),
        array('category',    'disable'),
    );
}