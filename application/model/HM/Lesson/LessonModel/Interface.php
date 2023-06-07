<?php
interface HM_Lesson_LessonModel_Interface
{
    public function getType();

    public function getName();

    public function getDescription();

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE);

    public function isExternalExecuting();

    public function getExecuteUrl();

    public function isNewWindow();

    public function getResultsUrl($options = []);
    
    public function isResultInTable();
    
    public function isFreeModeEnabled();

    public function formatBeginEnd();

    public function getFormulaPenaltyId();

    public function getEditUrl();

}