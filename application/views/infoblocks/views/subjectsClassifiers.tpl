<v-list subheader class="subject-classifiers hm__scrollbar">
    <?php foreach($this->classifiers as $classifierType => $classifiers) : ?>
    <v-subheader class="title">
        <a href="<?php echo $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'catalog', 'action' => 'index-with-tree', 'type' => $classifierType)))?>">
            <?php echo $this->classifiersTypes[$classifierType];?>
        </a>
    </v-subheader>
    <div class="subject-classifiers_course">
        <?php for ($i=0; $i < count($classifiers); $i++):?>
            <v-list-item class="course_name" href="<?php echo $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'catalog', 'action' => 'index-with-tree', 'type' => $classifiers[$i]['type'], 'item' => $classifiers[$i]['key'])))?>">
                <?php echo $this->freshness($classifiers[$i]['freshness'], _('Обновляемость содержимого курсов категории'));?>
                <v-list-item-content>
                    <v-list-item-title>
                        <?php echo trim($classifiers[$i]['title'])?>
                    </v-list-item-title>
                </v-list-item-content>
                <v-list-item-action>
                    <span class="course_count">
                        <?php echo Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount((int)$classifiers[$i]['count'])?>
                    </span>
                </v-list-item-action>
            </v-list-item>
        <?php endfor; ?>
    </div>
    <?php endforeach; ?>
    <v-subheader class="title">
        <a href="<?php echo $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'catalog', 'action' => 'index')))?>">
            <?php echo _('Не классифицированы');?>
        </a>
    </v-subheader>
    <div class="subject-classifiers_course">
        <v-list-item class="course_name">
            <v-list-item-action>
                <?php echo Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount((int)$this->notClassified);?>
            </v-list-item-action>
        </v-list-item>
    </div>
</v-list>
<?php if(!count($this->classifiers) && !$this->notClassified): ?>
    <v-alert type="info" value="true" outlined>
        <?php echo _('В каталоге нет курсов со свободной регистрацией')?>
    </v-alert>
<?php endif;?>
