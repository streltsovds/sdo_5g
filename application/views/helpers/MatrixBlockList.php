<?php
class HM_View_Helper_MatrixBlockList extends HM_View_Helper_Abstract
{
    public function matrixBlockList($users, $block)
    {
        $result = array();
        foreach ($users as $user){
            if ($user['matrixBlock'] == $block) {
                $result[] = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        return $result;
    }
}