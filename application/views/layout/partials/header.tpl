<?php echo $this->sidebars()->render(); ?>
<?php  $this->getMainNavigation()->hasVisiblePages() ?>
<v-app-bar
        v-if="$vuetify.breakpoint.smAndUp && view.showMainLayout"
        extension-height="48"
        :scroll-off-screen="$vuetify.breakpoint.mdAndDown"
        class="pr-0 main-nav"
        style="padding-left: 0 !important; z-index:200;"
        :color="themeColors.header || 'primary'"
        dark
        clipped-left
        app
>
    <!-- TODO v-if="activeUser"   -->
    <?php if ($this->getMainNavigation()->hasVisiblePages()) :?>
        <v-app-bar-nav-icon @click="toggleDrawer"></v-app-bar-nav-icon>
    <?php endif;?>
    <span></span>
    <v-toolbar-title style="margin-left: 20px; max-width: 60%">
        <?php if ($this->getBackUrl()):?>
            <a href="<?php echo $this->getBackUrl(); ?>" style="margin-top: -2px;text-decoration: none; margin-right: 16px !important; height: 21px; width: 12px;"><v-icon left>arrow_back_ios</v-icon></a>
        <?php endif; ?>
        <h1 style="font-size:inherit;font-weight: inherit; width: 100%; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
            <?php echo $this->getHeader(); ?>
        </h1>
        <?php if ($this->getSwitchContextUrls() && count($this->getSwitchContextUrls())):?>
            <v-menu offset-y >
                <template v-slot:activator="{ on }" >
                    <v-icon v-on="on" class="icon-header__v-menu">arrow_drop_down</v-icon>
                </template>

                <v-list light class="v-toolbar-title-v-list modified-list-styles" style="max-height: 60vh;">
                    <?php foreach ($this->getSwitchContextUrls() as $url):?>
                        <div class="modified-list-element">
                            <a href="<?php echo $url['url'] ?>">
                                <span><?php echo $url['name'] ?> </span>
                                <?php if (!empty($url['date'])): ?>
                                    <span>  |  </span>
                                    <span> <?php echo $url['date'] ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </v-list>
            </v-menu>
        <?php endif; ?>
    </v-toolbar-title>
    <v-spacer></v-spacer>

    <?php
    /**
     * Перелючение ролей пользователя - в
     * @see \HM_View_Sidebar_Userhome::getToggle()
     *
     * См. также 'user-home` по коду
     * @see \HM_Controller_Action::getDefaultSidebars()
     * @see \HM_Controller_Action::initView()
     */
    ?>
    <div class="hm-app__sidebars-togglers"
         style="display: flex; align-items: center; margin-right: 16px; "
    >
        <?php echo $this->sidebars()->togglers();?>
    </div>
    <div class="hm-app__btn-exit">
        <?php if($this->showExitBtn()): ?>
        <?php echo $this->exitBtn();?>
        <?php endif; ?>
    </div>

    <hm-global-loader></hm-global-loader>
</v-app-bar>
<v-app-bar
        v-else-if="view.showMainLayout"
        scroll-off-screen
        class="pr-0 main-nav"
        :color="colors.header"
        dark
        clipped-right
        app
        style="z-index:200;"
>
    <v-app-bar-nav-icon @click="toggleMenu"></v-app-bar-nav-icon>
    <!-- <v-toolbar-side-icon slot="extension" @click="toggleDrawer"></v-toolbar-side-icon> -->
    <v-toolbar-title class="title">
        <h1 style="font-size:inherit;font-weight: inherit;"><?php echo $this->getHeader(); ?></h1>
    </v-toolbar-title>
    <v-spacer></v-spacer>

    

    <template slot="extension">

        <?php echo $this->sidebars()->togglers();?>

        <?php if($this->showExitBtn()): ?>
            <?php echo $this->exitBtn();?>
        <?php endif; ?>
    </template>
    <hm-global-loader></hm-global-loader>
</v-app-bar>
<!-- TODO v-if="view.showMainLayout && activeUser" -->
<?php if ($this->getMainNavigation()->hasVisiblePages()) :?>
    <v-navigation-drawer
        v-if="$vuetify.breakpoint.smAndUp && view.showMainLayout"
        v-model="isDrawerShown"
        :class="{
            'hm-nav-menu': true,
            'hm-nav-menu-mini': miniNavMenu,
            'share-width-with-content': !miniNavMenu && appComputedNavMenuShareWidth,
        }"
        :permanent="miniNavMenu || appComputedNavMenuShareWidth"
        :mini-variant="miniNavMenu"
        :mini-variant-width="appComputedNavMenuMiniWidth"
        :width="appComputedNavMenuWidth"
        app
        clipped
        hide-overlay
        :color="themeColors.nav || '#ffffff'"
        :temporary="!miniNavMenu && !appComputedNavMenuShareWidth"
    >
        <v-layout column class="hm-nav-menu__layout">
            <v-flex v-if="!miniNavMenu" class="hm-nav-menu__logo">
                <a href="/">
                    <?php if ($logoUrl = $this->getDesignSetting('logo')): ?>
                        <v-img :aspect-ratio="300/100" contain max-height="100" src="<?php echo $logoUrl; ?>" style="margin: 14px 21px;"></v-img>
                    <?php else: ?>
                        <div style="margin: 14px 21px;">
                            <v-img :aspect-ratio="300/100" contain max-width="200" src="/images/default/logo.svg"></v-img>
                        </div>
                    <?php endif; ?>
                </a>
            </v-flex>
            <v-flex class="hm-main-nav-menu__wrapper">
                <?php echo $this->mainMenu($this->getMainNavigation());?>
            </v-flex>
        </v-layout>
    </v-navigation-drawer>
    <v-navigation-drawer
        v-else-if="view.showMainLayout"
        v-model="isDrawerShown"
        :class="{
            'hm-nav-menu': true,
        }"
        :permanent="false"
        app
        clipped
        hide-overlay
    >
        <v-layout column class="hm-nav-menu__layout">
            <v-flex class="hm-nav-menu__logo">
                <a href="/">
                    <?php if ($logoUrl = $this->getDesignSetting('logo')): ?>
                        <v-img :aspect-ratio="300/100" contain max-height="100" src="<?php echo $logoUrl; ?>" style="margin: 14px 21px;"></v-img>
                    <?php else: ?>
                        <div style="margin: 14px 21px;">
                            <v-img :aspect-ratio="300/100" contain max-width="200" src="/images/default/logo.svg"></v-img>
                        </div>
                    <?php endif; ?>
                </a>
            </v-flex>
            <v-flex class="hm-main-nav-menu__wrapper">
                <?php echo $this->mainMenu($this->getMainNavigation());?>
            </v-flex>
        </v-layout>
    </v-navigation-drawer>
<?php endif;?>
