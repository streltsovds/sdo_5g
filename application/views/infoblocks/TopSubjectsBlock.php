<?php


class HM_View_Infoblock_TopSubjectsBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'topSubjects';

    public function topSubjectsBlock($param = null)
    {
        $period = HM_Date::getCurrendPeriod(HM_Date::PERIOD_YEAR_CURRENT);
//		$this->view->begin = $period['begin']->toString(HM_Locale_Format::getDateFormat());
//		$this->view->end = $period['end']->toString(HM_Locale_Format::getDateFormat());
		$this->view->begin = $period['begin']->toString('dd.MM.yyyy');
		$this->view->end = $period['end']->toString('dd.MM.yyyy');

		$content = $this->view->render('topSubjectsBlock.tpl');
        
        return $this->render($content);
    }
}