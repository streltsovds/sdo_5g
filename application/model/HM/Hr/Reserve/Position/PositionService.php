<?php
class HM_Hr_Reserve_Position_PositionService extends HM_Service_Abstract
{
    public static function updateIcon($positionId, $photo, $destination = null, $skipResize = false)
    {
        if (empty($destination)) {
            $destination = HM_Hr_Reserve_Position_PositionModel::getIconFolder($positionId);
            $isPosition = true;
        } else {
            $isPosition = false;
        }
        $w = HM_Hr_Reserve_Position_PositionModel::THUMB_WIDTH;
        $h = HM_Hr_Reserve_Position_PositionModel::THUMB_HEIGHT;

        $path = rtrim($destination, '/') . '/' . $positionId . '.jpg';
        if ($photo instanceof HM_Form_Element_ServerFile) {
            $photoVal = $photo->getValue();
            //если инпут пустой - удаляем ткущее изображение
            if (empty($photoVal)) {
                unlink($path);
                return true;
            }
            $original = APPLICATION_PATH . '/../public' . $photoVal;
            //если новая картинка = старой, то ничего не меняем
            if (md5_file($original) == md5_file($path)) {
                return true;
            }
            if ($skipResize) {
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);
            // костыль для виджета subjectSlider
            if($isPosition){
                $img->adaptiveResize($w, $h);
            }
            $img->save($path);
        } elseif ($photo->isUploaded()){
            $original = rtrim($photo->getDestination(), '/') . '/' . $photo->getValue();
            if ($skipResize) {
                $path = rtrim($destination, '/') . '/' . $positionId . '-full.jpg';
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);

            // костыль для виджета positionsSlider
            if($isPosition){
                $img->adaptiveResize($w, $h);
            }
            $img->save($path);
            unlink($original);
        }
        return true;
    }

    public function assignCandidate($reservePositionId, $vacancyCandidate)
    {
        $this->getService('RecruitVacancyAssign')->update(
            array(
                'vacancy_candidate_id' => $vacancyCandidate->vacancy_candidate_id,
                'reserve_position_id'  => $reservePositionId
            )
        );
    }
}
