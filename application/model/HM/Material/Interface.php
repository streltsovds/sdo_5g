<?php

interface HM_Material_Interface
{
    public function getName();

    public function getDescription();

    /*
     * 5G
     * Универсальный метод включения материала в курс в кач-ве занятия
     */
    public function becomeLesson($subjectId);

    /*
     * 5G
     * Универсальный метод просмотра материала
     */
//    public function getPreviewUrl();
}
