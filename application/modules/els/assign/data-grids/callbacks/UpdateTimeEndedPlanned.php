<?php

/**
 *
 */
class HM_Assign_DataGrid_Callback_UpdateTimeEndedPlanned extends HM_DataGrid_Column_Callback_Abstract
{
    private $cache;

    public function callback(...$args)
    {
        list($dataGrid, $date, $CID, $newcomerId) = func_get_args();
        $subjectService = $dataGrid->getServiceContainer()->getService('Subject');
        if (!isset($this->cache['subject-period'])) {
            $this->cache['subject-period'] = $subjectService->fetchAll()->getList('subid', 'period');
        }

        return
            isset($this->cache['subject-period'][$CID]) &&
            ($this->cache['subject-period'][$CID] == HM_Subject_SubjectModel::PERIOD_FREE) &&
            !$newcomerId ?
                _('Нет') :
                $date;
    }
}