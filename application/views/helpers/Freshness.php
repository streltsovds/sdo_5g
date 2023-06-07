<?php
/*
 * @todo: в 4.x была прехитрая логика раскрашивания квадратиков на JS
 *
 */
class HM_View_Helper_Freshness extends HM_View_Helper_Abstract
{
    public function freshness($freshness, $freshnessTitle = null)
    {
		if (empty($freshnessTitle)) {
			$freshnessTitle = _('Обновляемость');
		}    	
		$freshnessTitle .= ': ';
		if ($freshness >= 75) {
			$freshnessTitle .= _('часто');
			$color = '#E54242';
		} elseif (($freshness >= 50) && ($freshness < 75)) {
			$freshnessTitle .= _('относительно часто');
            $color = '#FBAE07';
		} elseif (($freshness >= 25) && ($freshness < 50)) {
			$freshnessTitle .= _('относительно редко');
            $color = '#01B174';
		} else {
			$freshnessTitle .= _('редко');
            $color = '#843FA0';
		}
    	return <<<HTML
<svg-icon
        color="{$color}"
        height="20"
        name="classification"
        title="{$freshnessTitle}"
        style="margin-right: 10px;"
/>
HTML;
    }
}