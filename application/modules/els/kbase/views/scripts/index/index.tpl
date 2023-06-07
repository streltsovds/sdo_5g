<v-layout fill-height>
    <v-flex>
            <hm-kbase :not-found-text="view.notFoundText"></hm-kbase>
       <?php /* ?>
       <v-card>
            <?php <v-toolbar card>
                <?php echo $this->partial('_search-form-simple.tpl', array('url' => $this->url(array('module' => 'resource', 'controller' => 'search', 'action' => 'index', 'baseUrl' => '')) . '?page_id=unknown'));?>
            </v-toolbar>
            <v-card-text>
                <v-layout wrap fill-height>
                    <v-flex xs6>
                        <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, HM_Role_Abstract_RoleModel::ROLE_MANAGER))): ?>
                            <?php echo $this->Actions('resource', array(
                                array('title' => _('Создать информационный ресурс'), 'url' => $this->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'new'))),
                                array('title' => _('Создать учебный модуль'), 'url' => $this->url(array('module' => 'course', 'controller' => 'list', 'action' => 'new'))),
                            ));?>
                        <?php endif;?>
                        <v-layout column>
                            <?php if ($this->tags) :?>
                                <v-flex>
                                    <v-layout wrap>
                                        <?php foreach($this->tags as $tag) :?>
                                            <v-flex>
                                                <v-btn color="info" round small outlined href="<?php echo $this->url(array('module'=> 'resource','controller' => 'search', 'action' => 'tag', 'tag' => $tag->body))?>">
                                                    <?php echo $tag->body?>
                                                </v-btn>
                                            </v-flex>
                                        <?php endforeach;?>
                                    </v-layout>
                                </v-flex>
                            <?php endif;?>
                            <v-flex>
                                <v-card tile color="secondary darken-1" dark>
                                    <v-card-title class="subheading">
                                        <?php echo _('Статистика базы знаний');?>
                                    </v-card-title>
                                    <v-list dense class="white" light>
                                        <v-list-item>
                                            <v-list-item-content>
                                                <v-list-item-title>
                                                    <?php echo _('Всего информационных ресурсов');?>: <b><?php echo $this->statIRCount;?></b>
                                                </v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content>
                                                <v-list-item-title>
                                                    <?php echo _('Связей между ресурсами');?>: <b><?php echo $this->statRCount;?></b>
                                                </v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content>
                                                <v-list-item-title>
                                                    <?php echo _('Общее количество пользователей');?>: <b><?php echo $this->statUCount;?></b>
                                                </v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>
                                        <v-list-item>
                                            <v-list-item-content>
                                                <v-list-item-title>
                                                    <?php echo _('Новых ресурсов за последний месяц');?>: <b><?php echo $this->statMIRCount;?></b>
                                                </v-list-item-title>
                                            </v-list-item-content>
                                        </v-list-item>
                                    </v-list>
                                </v-card>
                            </v-flex>

                            <?php if($this->lastAdd): ?>
                                <v-flex>
                                    <v-card tile color="secondary darken-1" dark>
                                        <v-card-title class="subheading">
                                            <?php echo _('Последние добавления в базу знаний');?>
                                        </v-card-title>
                                        <v-list dense class="white" light>
                                            <?php foreach($this->lastAdd as $irItem):?>
                                                <v-list-item>
                                                    <v-list-item-content>
                                                        <v-list-item-title>
                                                            <?php echo $irItem['type'];?>:

                                                            <?php if($irItem['url']):?>
                                                                <a href="<?php echo $this->url($irItem['url']);?>" target="blank">
                                                                    <?php echo $irItem['title'];?>
                                                                </a>
                                                            <?php else:?>
                                                                <?php echo $irItem['title'];?>
                                                            <?php endif;?>
                                                        </v-list-item-title>
                                                    </v-list-item-content>
                                                </v-list-item>
                                            <?php endforeach;?>
                                        </v-list>
                                    </v-card>
                                </v-flex>
                            <?php endif;?>
                        </v-layout>
                    </v-flex>
                    <?php if ($this->classifiers):?>
                        <v-flex xs6>
                            <v-layout column>
                                <?php foreach($this->classifiers as $k=>$item):?>
                                    <v-flex>
                                        <v-card>
                                            <v-card-title class="subheading secondary darken-1 white--text">
                                                <?php echo $item['title'];?>
                                            </v-card-title>
                                            <v-card-text>
                                                <?php if ($item['items']):?>
                                                    <v-list dense>
                                                        <?php foreach ($item['items'] as $rubric):?>
                                                            <v-list-item>
                                                                <v-list-item-title>
                                                                    <a href="<?php echo $this->url(array('module'=>'resource','controller' => 'catalog','action' => 'index', 'type' => $k, 'classifier_id' => $rubric->classifier_id));?>">
                                                                        <?php echo $rubric->name;?>
                                                                    </a>
                                                                </v-list-item-title>
                                                            </v-list-item>
                                                        <?php endforeach;?>
                                                    </v-list>
                                                <?php else:?>
                                                    <v-alert type="info" value="true">
                                                        <?php echo _('Нет рубрик в классификаторе');?>
                                                    </v-alert>
                                                <?php endif;?>
                                            </v-card-text>
                                        </v-card>
                                    </v-flex>
                                <?php endforeach;?>
                            </v-layout>
                        </v-flex>
                    <?php endif;?>
                </v-layout>
            </v-card-text>
        </v-card>*/ ?>
    </v-flex>
</v-layout>
