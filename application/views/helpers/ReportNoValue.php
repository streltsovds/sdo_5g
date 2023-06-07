<?php
class HM_View_Helper_ReportNoValue extends HM_View_Helper_Abstract
{
    public function reportNoValue($bNotNeed=false)
    {
        return sprintf('<span class="report-no-value">%s</span>', $bNotNeed?_('не важно'):_('не задано'));
    }
}