<?php

class HM_View_Helper_MaterialResponsive extends HM_View_Helper_Abstract
{
    /**
     * @param HM_Resource_ResourceModel $resource
     * @param null $lesson
     * @param null $materialContent
     * @param array $overrideProps
     * @return string
     * @throws Exception
     */
    public function materialResponsive($resource, $lesson = null, $materialContent = null, $overrideProps = [])
    {
        if (!$materialContent) {
            /** @see \HM_View_Helper_MaterialView::materialView() */
            $materialContent = $this->view->materialView($resource, $lesson);
            $slotTitleBelow = '';

            switch ($resource->type) {
                case HM_Resource_ResourceModel::TYPE_URL:

                    $slotTitleBelow =
                        '<span 
                            style="font-size: 16px;" 
                            :style="{color: colors.textLight}"
                        >
                            В случае проблем с показом контента 
                            <a 
                                href="' . $resource->url  . '" 
                                target="_blank"
                                style="text-decoration: none"
                                :style="{color: colors.primaryDark}"
                            >
                                перейдите по ссылке
                            </a>
                        </span>';
                    break;
            }

            if ($slotTitleBelow) {
                $materialContent = $materialContent . '<template v-slot:title-below>' . $slotTitleBelow . '</template>';
            }
        }

        $fullHeight = true;
        $showTitleBelow = false;
        $fullScreenAllowed = true;

        if ($resource) {
            switch ($resource->type) {
                case HM_Resource_ResourceModel::TYPE_URL:
                    $showTitleBelow = true;
                    break;
                case HM_Resource_ResourceModel::TYPE_CARD:
                    $fullHeight = false;
                    $showTitleBelow = true;
                    break;
                case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                    if ($resource->external_viewer) {
                        $fullScreenAllowed = false;
                    } else {
                        $showTitleBelow = false;
                    }
                    break;
                default:
                    if ($resource->isViewable()) {
                        $fileType = HM_Files_FilesModel::getFileType($resource->filename);
                        switch($fileType) {
                            case HM_Files_FilesModel::FILETYPE_IMAGE:
                                $fullHeight = false;
                                $showTitleBelow = true;
                                break;
                            /** скачивание или проигрывание музыки */
                            case HM_Files_FilesModel::FILETYPE_AUDIO:
                                $fullHeight = false;
                                $fullScreenAllowed = false;
                                break;
//                            case HM_Files_FilesModel::FILETYPE_VIDEO:
//                                $fullHeight = true;
//                                $showTitleBelow = false;
//                                break;
                        }
                    } else {
                        /** скачивание? */
                        $fullHeight = false;
                        $fullScreenAllowed = false;
                        $showTitleBelow = true;
                    }
                    break;
            }
        }

        $props = [
            'fullHeight' => $fullHeight,
            'fullScreenAllowed' => $fullScreenAllowed,
            'showTitleBelow' => $showTitleBelow,
            'title' => $resource ? $resource->title : '',
            'type' => $resource ? $resource->type : '',
        ];

        $props = array_merge($props, $overrideProps);

        $this->view->propsJson = HM_Json::encodeErrorThrow($props);

        $this->view->content = $materialContent;
        


        return $this->view->render('materialResponsive.tpl');
    }
}
