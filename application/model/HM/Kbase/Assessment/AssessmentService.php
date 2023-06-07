<?php
class HM_Kbase_Assessment_AssessmentService extends HM_Service_Abstract
{
    
    /*
     * Оценить ресурс.
     * 
     * @param $assessment - Оценка, которую необходимо установить
     * @param $user - id оценивающего пользователя, либо 0, если оценивает гость
     * @param $resource - оцениваемый ресурс
     * @param $type - тип ресурса (HM_Kbase_KbaseModel::TYPE_...)
     * 
     * @return true - в случае успешного сохранения оценки
     * 
     * @throw HM_Exception
     */
    public function estimate($assessment, $user, $resource, $type){
        if ($type == 'scorm') $type = HM_Resource_ResourceModel::TYPE_FILESET;

        if($resource < 1){
            throw new HM_Exception('Не задан оцениваемый ресурс');
        }
        if(!$this->getService('Kbase')->checkResourceType($type)){
            throw new HM_Exception(_('Неизвестный тип ресурса'));
        }
        
        if($user > 0){
            // Проверить, оценивал ли данный пользователь данный ресурс.
            // Для гостя такая проверка не требуется.
            // Она производится на уровне контроллера через куки
            
            $testAssessment = $this->getService('KbaseAssessment')->fetchAll(
                    $this->getService('KbaseAssessment')->quoteInto(
                        array(
                            'type = ?',
                            ' AND resource_id = ?',
                            ' AND MID = ?'
                        ),
                        array(
                            $type,
                            (int) $resource,
                            (int) $user
                        )
                    )
                );
            if(count($testAssessment)){
                throw new HM_Exception(_('Данный ресурс уже оценен'));
            }
        }
        else{
            $user = 0;
        }
        
        if($this->getService('KbaseAssessment')->insert(
            array(
                'type'          => $type,
                'resource_id'   => (int) $resource,
                'MID'           => (int) $user,
                'assessment'    => (float) $assessment,
            )
        )){
            return $this;
        }
        
        throw new HM_Exception(_('Не удалось записать оценку'));
    }
    
    /*
     * Возвращает среднюю арифметическую оценку по ресурсу
     * @param $resource - оцениваемый ресурс
     * @param $type - тип ресурса (HM_Kbase_KbaseModel::TYPE_...)
     * 
     * @return array(
     *      value - среднее арифм.
     *      count - количество оценок
     * )
     */
    public function getAverage($resource, $type, $valueFieldName = 'value')
    {
        if ($resource < 1) {
            throw new HM_Exception('Не задан оцениваемый ресурс');
        }
        if (!$this->getService('Kbase')->checkResourceType($type)) {
            throw new HM_Exception(_('Неизвестный тип ресурса'));
        }
        $assessments = $this->getSelect()
                ->from(
                    'kbase_assessment',
                    array(
                        'count' => new Zend_Db_Expr('count(*)'),
                        'value' => new Zend_Db_Expr('SUM(assessment)')
                    )
                )
                ->where('resource_id = ?', $resource)
                ->where('type = ?', $type)
                ->query()
                ->fetchAll();
        
        return array(
            'count' => $assessments[0]['count'],
            $valueFieldName => ($assessments[0]['count'] ? round(($assessments[0]['value']/$assessments[0]['count']), 1) : 0)
        );
    }

    public function getTopByType($count, $type)
    {
        if(!$this->getService('Kbase')->checkResourceType($type)){
            throw new HM_Exception(_('Неизвестный тип ресурса'));
        }
        $types = [$type];
        if ($type == HM_Kbase_KbaseModel::TYPE_RESOURCE) {
            $types = array_keys(HM_Resource_ResourceModel::getTypes());
        }
        $assessments = $this->getSelect()
            ->from(
                array( 'kba' => 'kbase_assessment'),
                array(
                    'resource_id' => 'kba.resource_id',
                    'title' => 'r.title',
                    'type' => 'kba.type',
                    'count' => new Zend_Db_Expr('count(kba.id)'),
                    'value' => new Zend_Db_Expr('SUM(kba.assessment)'),
                    'average' => new Zend_Db_Expr('SUM(kba.assessment)/count(kba.id)'),
                )
            )
            ->joinInner(
                array('r' => 'resources'),
                'kba.resource_id = r.resource_id',
                array()
            )
            ->where('kba.type IN (?)', $types)
            ->group(array('kba.resource_id', 'kba.type', 'r.title'))
            ->order('average DESC')
            ->limit($count)
            ->query()
            ->fetchAll();

        return $assessments;
    }
}