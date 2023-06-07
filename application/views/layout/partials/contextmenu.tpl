<?php
$iterator = new RecursiveIteratorIterator($this->container,
    RecursiveIteratorIterator::SELF_FIRST);
$iterator->setMaxDepth(0);
?>
<v-toolbar-items>
    <?php foreach($iterator as $page):?>
        <?php if($page->isHiddenInMenu()) continue; ?>
        <?php if ($this->helper->accept($page)):?>
            <?php if ($page->hasPages()):?>
                <?php foreach($page->getPages() as $subPage):?>
                    <?php if($subPage->isHiddenInMenu()) continue; ?>
                    <?php if($this->helper->accept($subPage)): ?>
                        <?php if ($subPage->hasPages()):?>
                            <v-menu offset-y>
                                <template  v-slot:activator="{ on: menu }">
                                    <?php if ($subPage->icon):?>
                                        <v-tooltip bottom >
                                            <template v-slot:activator="{ on: tooltip }">
                                                <v-btn text v-on="{ ...tooltip, ...menu }">
                                                    <v-icon><?php echo $subPage->icon?></v-icon>
                                                </v-btn>
                                            </template>
                                            <span><?php echo $subPage->getLabel();?></span>
                                        </v-tooltip>
                                    <?php else:?>
                                        <v-btn text v-on=" menu "
                                            <?php if ($subPage->isActive(true)):?>
                                                class="active-link"
                                            <?php endif;?>
                                        >
                                            <?php echo $subPage->getLabel();?>
                                            <v-icon style="margin-left: 5px !important" right>arrow_drop_down</v-icon>
                                        </v-btn>
                                    <?php endif;?>
                                </template>

                                <v-list dense style="width: 418px !important;">
                                    <?php foreach($subPage->getPages() as $subSubPage):?>
                                        <?php if($subSubPage->isHiddenInMenu()) continue; ?>
                                        <?php if($this->helper->accept($subSubPage)): ?>
                                            <v-list-item
                                                    class="no-padd-inside"
                                                <?php if ($subSubPage->isActive(true)):?>
                                                    :inactive="true"
                                                    color="primary"
                                                <?php endif;?>
                                                    href="<?php echo $subSubPage->getHref();?>">
                                                <?php if($subSubPage->delimiter && $subSubPage->delimiter === 'before') :?>
                                                    <div style="position: absolute; top: 0; left: 0; height: 1px; width: 100%; background: rgb(212,219,226)"></div>
                                                <?php endif; ?>
                                                <span>
                                                    <?php echo $subSubPage->getLabel();?>
                                                </span>
                                                <?php if($subSubPage->delimiter && $subSubPage->delimiter === 'after') :?>
                                                    <div style="position: absolute; bottom: 0; left: 0; height: 1px; width: 100%; background: rgb(212,219,226)"></div>
                                                <?php endif; ?>
                                            </v-list-item>
                                        <?php endif;?>
                                    <?php endforeach;?>
                                </v-list>
                            </v-menu>
                        <?php else:?>
                            <v-btn text
                                <?php if ($subPage->isActive(true)):?>
                                    class="primary lighten-1 active-link"
                                <?php endif;?>
                               href="<?php echo $subPage->getHref();?>">
                                <?php if ($subPage->isActive(true)):?>
                                    <span class="white--text">
                                <?php else:?>
                                    <span>
                                <?php endif;?>
                                    <?php echo $subPage->getLabel();?>
                                </span>
                            </v-btn>
                        <?php endif;?>
                    <?php endif;?>
                <?php endforeach;?>
            <?php else:?>
            <?php endif;?>
        <?php endif;?>
    <?php endforeach;?>
</v-toolbar-items>
<!-- <v-menu :close-on-content-click="false" v-if="$vuetify.breakpoint.xsOnly">
    <template v-slot:activator="{ on: onMenu }">
        <v-btn icon v-on="onMenu">
            <v-icon>more_vert</v-icon>
        </v-btn>
    </template>
    <v-list dense>
        <?php foreach($iterator as $page):?>
            <?php if($page->isHiddenInMenu()) continue; ?>
            <?php if ($this->helper->accept($page)):?>
                <?php if ($page->hasPages()):?>
                    <?php foreach($page->getPages() as $subPage):?>
                        <?php if($subPage->isHiddenInMenu()) continue; ?>
                        <?php if($this->helper->accept($subPage)): ?>
                            <?php if ($subPage->hasPages()):?>
                                <v-menu offset-x left>
                                    <template v-slot:activator="{ on: onMenu }">
                                        <v-list-item class="no-padd-inside" on="onMenu">
                                            <v-list-item-title>
                                                <?php echo $subPage->getLabel();?>
                                            </v-list-item-title>
                                            <v-list-item-action>
                                                <v-icon right>arrow_drop_down</v-icon>
                                            </v-list-item-action>
                                        </v-list-item>
                                    </template>
                                    <v-list dense>
                                        <?php foreach($subPage->getPages() as $subSubPage):?>
                                            <?php if($subSubPage->isHiddenInMenu()) continue; ?>
                                            <v-list-item
                                                class="no-padd-inside"
                                                <?php if ($subSubPage->isActive(true)):?>
                                                    :inactive="true"
                                                    color="primary"
                                                <?php endif;?>
                                                href="<?php echo $subSubPage->getHref();?>">
                                                <span>
                                                    <?php echo $subSubPage->getLabel();?>
                                                </span>
                                            </v-list-item>
                                        <?php endforeach;?>
                                    </v-list>
                                </v-menu>
                            <?php else:?>
                                <v-list-item
                                    href="<?php echo $subPage->getHref();?>"
                                    <?php if ($subPage->isActive(true)):?>
                                        :inactive="true"
                                        color="primary"
                                    <?php endif;?>
                                    >
                                    <v-list-item-title>
                                        <?php echo $subPage->getLabel();?>
                                    </v-list-item-title>
                                </v-list-item>
                            <?php endif;?>
                            <?php if ($subPage->get('divider')):?>
                                <v-divider></v-divider>
                            <?php endif;?>
                        <?php endif;?>
                    <?php endforeach;?>
                <?php else:?>
                <?php endif;?>
            <?php endif;?>
        <?php endforeach;?>
    </v-list>
</v-menu> -->

