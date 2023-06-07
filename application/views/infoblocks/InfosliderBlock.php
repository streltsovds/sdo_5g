<?php

class HM_View_Infoblock_InfosliderBlock extends HM_View_Infoblock_Abstract
{
    const MAX_ITEMS = 10;
    
    protected $id = 'infosliderblock';

    private function generateCompletelyRandomColor()
    {
        $basicColorsClasses = array('#D4FAE4', '#FAF3D8', '#D4E3FB', '#DAD3FD', '#FDE1D9', '#EDF4FC', '#FFE9B9', '#ABDCC6', '#DAC5E2');
        $randomBasicClsIdx = mt_rand(0, count($basicColorsClasses) - 1);
        $randomColorCls = $basicColorsClasses[$randomBasicClsIdx];

        return $randomColorCls;
    }

    /**
     * @param null $param
     * @return string
     * @throws Zend_Exception
     */
    public function infosliderBlock($param = null)
    {
        $services = Zend_Registry::get('serviceContainer');
        // Перечень информационных страниц, доступных пользователю
        $pages = $services->getService('Htmlpage')->getAllPages();
        $subjects = $services->getService('Subject')->getBannerSubjects();
        $id = 0;

        $subjectsJs = [];
        if (count($pages)) {
            foreach ($pages as $page) {
                if ($page->in_slider) {
                    $banner = $page->getUserIcon();
                    $name = HM_String_Transform::crop($page->getName() ? $page->getName() : '', 90);
                    $description = HM_String_Transform::crop($page->getDescription() ? $page->getDescription() : '', 180);

                    $subjectsJs[] = [
                        'name' => $name,
                        'description' => $description,
                        'id' => ++$id,
                        'url' => $page->getUrl(),
                        'image' => $banner,
                        'color' => $this->generateCompletelyRandomColor(),
                    ];
                }
            }
        }

        if (count($subjects)) {
            foreach ($subjects as $subject) {
                $banner = $subject->getIconBanner();
                $name = HM_String_Transform::crop($subject->getName() ? $subject->getName() : '', 90);
                $description = HM_String_Transform::crop($subject->getShortDescription() ? $subject->getShortDescription() : '', 180);

                $subjectsJs[] = [
                    'name' => $name,
                    'description' => $description,
                    'id' => ++$id,
                    'url' => Zend_Registry::get('view')->url($subject->getDescriptionUrl()),
                    'image' => $banner,
                    'color' => $this->generateCompletelyRandomColor(),
                ];
            }
        }

        $this->view->slides = array_values($subjectsJs);

        $content = $this->view->render('infosliderBlock.tpl');
        return $this->render($content);
    }
}