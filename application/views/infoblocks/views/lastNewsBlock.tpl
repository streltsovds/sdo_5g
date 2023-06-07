<?php if ($this->allow):?>
    <?php if ($this->news):?>
        <v-list>
            <?php foreach($this->news as $news):?>
                <?php $url = (!empty($news->url)) ? $news->url : $this->baseUrl($this->url(array('module' => 'news', 'controller' => 'view', 'action' => 'index', 'news_id' => $news->id))); ?>
                <v-list-item href="<?php  echo $url ?>">
                    <v-list-item-content>
                        <v-list-item-title><?php echo strip_tags($news->announce)?></v-list-item-title>
                        <v-list-item-subtitle>
                            <?php if (strlen($news->getFilteredMessage()) > 150):?>
                                <?php echo mb_substr($news->getFilteredMessage(), 0, 150)?>...
                            <?php else:?>
                                <?php echo $news->getFilteredMessage()?>
                            <?php endif;?>
                        </v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-action>
                        <v-list-item-action-text>
                            <?php echo date('d.m.Y H:i', strtotime($news->created))?>
                        </v-list-item-action-text>
                    </v-list-item-action>
                </v-list-item>
            <?php endforeach;?>
        </v-list>
        <v-divider></v-divider>
        <v-card-actions>
            <v-btn text small color="primary" href="<?php echo $this->baseUrl($this->url(array('module' => 'news', 'controller' => 'index', 'action' => 'index')))?>?page_id=m00">
                <?php echo _('Все новости')?>
            </v-btn>
        </v-card-actions>
    <?php else:?>
        <v-alert type="info" value="true" outlined>
            <?php echo _('Отсутствуют данные для отображения')?>
        </v-alert>
    <?php endif;?>
<?php else:?>
    <v-alert type="info" value="true" outlined>
        <?php echo sprintf(_('Сервис взаимодействия "%s" отключен на уровне портала.'), $this->serviceName)?>
    </v-alert>
<?php endif;?>