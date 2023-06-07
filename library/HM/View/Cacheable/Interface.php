<?php

/**
 * Created by PhpStorm.
 * User: klekotnev
 * Date: 20.02.2019
 * Time: 12:09
 */
interface HM_View_Cacheable_Interface
{
    public function getCachedContent();
    public function getNotCachedContent();
}