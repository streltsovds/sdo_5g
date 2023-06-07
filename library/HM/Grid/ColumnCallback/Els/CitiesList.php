<?php

class HM_Grid_ColumnCallback_Els_CitiesList extends HM_Grid_ColumnCallback_Els_ClassifiersList
{
    protected function _pluralCount($count)
    {
        $service = $this->getService();
        if (method_exists($service, 'pluralCitiesCount')) {
            return $this->getService()->pluralCitiesCount($count);
        }

        return $count;
    }
}