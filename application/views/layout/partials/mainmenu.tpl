<?php
$iterator = new RecursiveIteratorIterator($this->container,
    RecursiveIteratorIterator::SELF_FIRST);
$iterator->setMaxDepth(0);
?>
<v-list class="hm-main-nav-menu block-links__nav-menu"
        :class="{
            'mini-menu' : miniNavMenu,
            'big-menu': !miniNavMenu,
            'hm-main-nav-menu--mini': miniNavMenu,
            'hm-main-nav-menu--big': !miniNavMenu,
        }"
        style=""
>
    <?php foreach ($iterator as $page): ?>
        <?php $href = ''; ?>
        <?php if ($page->isHiddenInMenu()) continue; ?>
        <?php if ($this->helper->accept($page)): ?>
            <!--        ЕСЛИ ЗАКРЫТО-->
            <div 
                v-if="miniNavMenu && $vuetify.breakpoint.smAndUp"
                <?php if ($page->isActive(true) && !$page->isSubjectForum()): ?>
                    class="list-nav-menu list-nav-menu_active list-nav-menu-left"
                    :value="true"
                <?php else: ?>
                    class="list-nav-menu list-nav-menu-left"
                <?php endif; ?>
            >
                <!--        ЕСЛИ ЕСТЬ ВЛОЖЕННОСТЬ-->
                <?php if ($page->hasPages()): ?>
                    <v-menu class="hm-main-nav-menu__item-wrapper"
                            open-on-hover
                            close-delay="100"
                            open-delay="100"
                            offset-x class="v-menu-dev"
                            transition="slide-x-reverse-transition"
                    >
                        <template v-slot:activator="{ on, attrs }">

                            <?php foreach ($page->getPages() as $subPage): ?>
                                <?php if ($subPage->isHiddenInMenu()) continue; ?>
                                <?php if ($this->helper->accept($subPage)): ?>
                                    <?php $href = $subPage->getHref(); ?>
                                    <?php break; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <v-list-item class="hm-main-nav-menu__item"
                                    v-on="on"
                                    v-bind="attrs"
                                <?php if ($page->isActive(true)): ?>
                                    :value="true"
                                <?php else: ?>
                                <?php endif; ?>
                                href="<?php echo $href;?>"
                            >
                                <hm-main-nav-menu-action-with-tooltip
                                    icon-name="<?php echo $page->icon; ?>"
                                    label="<?php echo $page->getLabel(); ?>"
                                >
                                </hm-main-nav-menu-action-with-tooltip>

<!--                                <v-tooltip class="hm-main-nav-menu__item__tooltip"-->
<!--                                           bottom-->
<!--                                           :open-delay="150"-->
<!--                                           content-class="hm-main-nav-menu__item__tooltip__content"-->
<!--                                >-->
<!--                                    <template v-slot:activator="{ on: tooltipOn, attrs: tooltipAttrs }">-->
<!--                                        <v-list-item-action-->
<!--                                            v-bind="tooltipAttrs"-->
<!--                                            v-on="tooltipOn"-->
<!--                                        >-->
<!--                                            <svg-icon name="--><?php //echo $page->icon ?><!--" title=""> </svg-icon>-->
<!--                                        </v-list-item-action>-->
<!--                                    </template>-->
<!--                                    <span class="hm-main-nav-menu__item__tooltip__text">-->
<!--                                        --><?php //echo $page->getLabel(); ?>
<!--                                    </span>-->
<!--                                </v-tooltip>-->

                                <v-list-item-content class="hm-main-nav-menu__submenu-wrap-wrap">
                                    <v-list-item-title class="hm-main-nav-menu__submenu-wrap">
                                        <v-list-group class="hm-main-nav-menu__submenu"
                                                no-action
                                            <?php if ($page->isActive(true)): ?>
                                                :value="true"
                                            <?php endif; ?>
                                        >
                                            <v-list-item no-action slot="activator"
                                                         class="hm-main-nav-menu__submenu-activator"
                                            >
                                                <span class="hm-main-nav-menu__submenu-label">
                                                    <?php echo $page->getLabel(); ?>
                                                </span>
                                            </v-list-item>
                                            <?php foreach ($page->getPages() as $subPage): ?>
                                                <?php if ($subPage->isHiddenInMenu()) continue; ?>
                                                <?php if ($this->helper->accept($subPage)): ?>
                                                    <v-list-item class="hm-main-nav-menu__submenu-item no-padd-inside"
                                                        <?php if ($subPage->isActive(true)): ?>
                                                            :inactive="true"
                                                            color="primary"
                                                        <?php endif; ?>
                                                            href="<?php echo $subPage->getHref(); ?>">
                                                            <span class="hm-main-nav-menu__submenu-item-label">
                                                                <?php echo $subPage->getLabel(); ?>
                                                            </span>
                                                    </v-list-item>
                                                    <?php if ($subPage->get('divider')): ?>
                                                        <v-divider></v-divider>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </v-list-group>
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </template>
                        <v-card class="dev-v-card">
                            <div class='dev-v-card-header'>
                            </div>
                            <?php foreach ($page->getPages() as $subPage): ?>
                                <?php if ($subPage->isHiddenInMenu()) continue; ?>
                                <?php if ($this->helper->accept($subPage)): ?>
                                    <div>
                                        <?php if($subPage->delimiter === 'before') :?>
                                            <v-divider></v-divider>
                                        <?php endif; ?>
                                        <v-list-item
                                                class="no-padd-inside"
                                            <?php if ($subPage->isActive(true)): ?>
                                                :inactive="true"
                                                color="primary"
                                            <?php endif; ?>
                                                href="<?php echo $subPage->getHref(); ?>">
                                            <span>
                                                <?php echo $subPage->getLabel(); ?>
                                            </span>

                                        </v-list-item>
                                        <?php if($subPage->delimiter === 'after') :?>
                                            <v-divider></v-divider>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($subPage->get('divider')): ?>
                                        <v-divider></v-divider>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </v-card>
                    </v-menu>
                    <!--            ЕСЛИ НЕТ ВЛОЖЕННОСТИ-->
                <?php else: ?>
                       <v-list-item
                               class="no-list"
                           <?php if ($page->isActive(true)): ?>
                               :value="true"
                           <?php else: ?>
                           <?php endif; ?>
                               href="<?php echo $page->getHref(); ?>">

                           <hm-main-nav-menu-action-with-tooltip
                                   icon-name="<?php echo $page->icon; ?>"
                                   label="<?php echo $page->getLabel(); ?>"
                           >
                           </hm-main-nav-menu-action-with-tooltip>

                       </v-list-item>
                <?php endif; ?>
            </div>
            <!--                        ЕСЛИ ОТКРЫТО-->
            <div v-if="!miniNavMenu || $vuetify.breakpoint.xs" class="list-nav-menu--big">
                <?php if ($page->hasPages()): ?>
                    <v-list-group
                            no-action
                        <?php if ($page->isActive(true)): ?>
                            class="v-list-header__active"
                            :value="true"
                        <?php else: ?>
                            class="v-list-header__no-active"
                        <?php endif; ?>
                    >
                        <v-list-item no-action slot="activator" class="open-list-links">
                            <svg-icon name="<?php echo $page->icon ?>" > </svg-icon>
                            <span style="margin-left: 24px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                        <?php echo $page->getLabel(); ?>
                            </span>
                        </v-list-item>
                        <?php foreach ($page->getPages() as $subPage): ?>
                            <?php if ($subPage->isHiddenInMenu()) continue; ?>
                            <?php if ($this->helper->accept($subPage)): ?>
                                <?php if($subPage->delimiter === 'before') :?>
                                    <v-divider></v-divider>
                                <?php endif; ?>
                                <v-list-item
                                        class="no-padd-inside-open"
                                    <?php if ($subPage->isActive(true)): ?>
                                        :inactive="true"
                                        color="primary"
                                    <?php endif; ?>
                                        href="<?php echo $subPage->getHref(); ?>">
                                            <span>
                                                <?php echo $subPage->getLabel(); ?>
                                            </span>
                                </v-list-item>
                                <?php if($subPage->delimiter === 'after') :?>
                                    <v-divider></v-divider>
                                <?php endif; ?>
                                <?php if ($subPage->get('divider')): ?>
                                    <v-divider></v-divider>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </v-list-group>
                <?php else: ?>
                    <v-list-item
                        <?php if ($page->isActive(true) && !$page->isSubjectForum()): ?>
                            :inactive="true"
                            :value="true"
                            class="v-list-header__active"
                        <?php else: ?>
                            class="v-list-header__no-active"
                        <?php endif; ?>
                            href="<?php echo $page->getHref(); ?>">
                        <svg-icon name="<?php echo $page->icon ?>" > </svg-icon>
                        <span style="margin-left: 24px">
                                    <?php echo $page->getLabel(); ?>
                                </span>
                    </v-list-item>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    <?php endforeach; ?>

</v-list>
