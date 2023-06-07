<?php
class HM_View_Helper_FooterMenu extends HM_View_Helper_Abstract
{
//    public function footerMenu(Zend_Navigation_Container $container = null)
    /** @see HM_View_Helper_FooterMenuData::footerMenuData() */
    public function footerMenu($pagesData)
    {
        if (is_object($pagesData)) {
            $pagesData = $this->view->footerMenuData($pagesData);
        }

        $columnsOfPages = [];

        $maxPagesInColumn = 3;
        $desiredColumns = 3;

        $pagesCount = count($pagesData);

        $pagesInColumn = min($pagesCount/$desiredColumns, $maxPagesInColumn);

        $currentColumn = [];

        for ($i = 0; $i < count($pagesData); $i++) {
            $currentColumn[] = $pagesData[$i];
            if (
                count($currentColumn) >= $pagesInColumn ||
                $i >= count($pagesData) - 1
            ) {
                $columnsOfPages[] = $currentColumn;
                $currentColumn = [];
            }
        }


        $this->view->columnsOfPages = $columnsOfPages;
        $this->view->helper = $this;

        return $this->view->render('partials/footer-menu.tpl');
    }
}