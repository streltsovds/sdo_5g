<?php


class HM_View_Infoblock_RecommendedMaterialsBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'recommendedMaterials';

    public function recommendedMaterialsBlock($param = null)
    {
        $userId = $this->getService('User')->getCurrentUserId();
        $isModerator = $this->getService('News')->isUserActivityPotentialModerator($userId);
        $this->view->isModerator = $isModerator;

        $widgetProps = [];
        /** @var HM_Resource_ResourceModel $material */
        $material = $this->getService('Material')->getRecommendedMaterials($userId);

        $widgetProps = $material
            ? [
                'image' => $material->getRecommendedImage(),
                'description' => $material->getRecommendedDescription(),
                'tags' => array_values($material->getRecommendedRubrics()),
                'title' => $material->getRecommendedName(),
                'url' =>  $this->view->url($material->getKbaseUrl()),
            ] :
            [];

        $this->view->widgetProps = $widgetProps;

        $content = $this->view->render('recommendedMaterialsBlock.tpl');

        return $this->render($content);
    }
}
