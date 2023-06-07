<?php

class HM_View_Helper_MaterialCourse extends HM_View_Helper_MaterialAbstract
{
    public function materialCourse($material)
    {
        return $this->view->render("material/preview/course.tpl");
    }
}