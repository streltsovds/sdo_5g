<?php
interface HM_Meeting_MeetingModel_Interface
{
    public function getType();

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE);

    public function isExternalExecuting();

    public function getExecuteUrl();

    public function getResultsUrl($options = array());
    
    public function isResultInTable();
    
    public function isFreeModeEnabled();
}