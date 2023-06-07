<?php

interface HM_Multipage_Controller_Interface
{
    public function _getMultipageId();
    public function _isCurrentMultipage();
    public function _redirectToIndex();
    public function _getBaseUrl();
    public function _getPersistentModel();
    public function _getProgressTitle($itemId);
    public function _getItemProgress($itemId);
    public function _getTotalResults();
    public function _saveResults($itemId, $results);    
    public function _saveMemoResults($memos);    
    public function _setInfo();    
    public function _finalize();    
    public function _isFinalizeable($totalResults);
    public function _isExecutable();
}
