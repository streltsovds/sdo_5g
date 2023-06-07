<?php

interface HM_Search_Item_Interface
{
    public function getName();
    public function getDescription();
    public function getCreateUpdateDate();
    public function getIconClass();
    public function getViewUrl();
    public function getCardUrl();
}
