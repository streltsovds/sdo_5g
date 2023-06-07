<?php

class HM_View_Infoblock_SubjectsClassifiers extends HM_View_Infoblock_Abstract
{
    protected $id = 'subjectsClassifiers';
    protected $class = 'subjectsClassifiers';
    protected $itemType = HM_Classifier_Link_LinkModel::TYPE_SUBJECT;

    public function subjectsClassifiers($param = null)
    {
        $services = Zend_Registry::get('serviceContainer');
        $classifiersTypes = $services->getService('ClassifierType')
            ->getClassifierTypesNames(HM_Classifier_Link_LinkModel::TYPE_SUBJECT);

        // в списке нам нужен только верхний уровень
        // Скрываем "Направления обучения", потому что они сейчас используются не совсем как классификатор и криво считаются
        $categories = $services->getService('Classifier')->getChildren(0, true, 'node.type != ' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS);
        // подсчет - рекурсивно, включая нижние уровни
        $classifiersCount = $services->getService('Classifier')
            ->getElementCount(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $categories);
        // подсчет - рекурсивно, включая нижние уровни
        $classifiersFreshness = $services->getService('Classifier')->getCategoriesFreshness($categories);
        // не совсем так; при подсчёте не учитывались сложны условия доступности курсов
        $notClassified = $services->getService('Classifier')
            ->getUnclassifiedElementCount(HM_Classifier_Link_LinkModel::TYPE_SUBJECT);

        $classifiers = array();
        if (count($categories)) {
            foreach ($categories as $category) {

                $count = $category->classifier_id && isset($classifiersCount[$category->classifier_id]) ?
                    (int) $classifiersCount[$category->classifier_id] :
                    0;

                if ($count) {
                    $type = ($category->type) ?
                        $category->type :
                        (($category->TYPE) ?
                            $category->TYPE :
                            $this->classifierType);

                    if (!isset($classifiers[$type])) {
                        $classifiers[$type] = array();
                    }

                    $classifiers[$type][] = array(
                        'title' => $category->name,
                        'count' => $count,
                        'key' => $category->classifier_id,
                        'type' => $type,
                        'freshness' => $classifiersFreshness[$category->classifier_id],
                    );
                }
            }
        }

        $this->view->classifiers = $classifiers;
        $this->view->classifiersTypes = $classifiersTypes;
        $this->view->notClassified = $notClassified;
        
        $this->view->headScript()->appendFile($this->view->publicFileToUrlWithHash(
            '/js/infoblocks/subjects-classifiers/script.js'
        ));

        $this->view->headLink()->appendStylesheet($this->view->publicFileToUrlWithHash(
            '/css/infoblocks/subjects-classifiers/style.css'
        ));
        
        $content = $this->view->render('subjectsClassifiers.tpl');

        return $this->render($content);
    }
}
