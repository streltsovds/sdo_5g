<?php if (count($sidebars = $this->getSidebars())): ?>
        <?php foreach ($sidebars as $name => $sidebar): ?>
        <hm-sidebar
            v-if="view.showMainLayout"
            sidebar-name="<?php echo $sidebar->getName();?>"
            <?php if ($sidebar->isOpened()):?>
                opened
            <?php endif;?>
            <?php if ($sidebar->isModal()):?>
                hoverable
            <?php endif;?>
            :width="appComputedHmSidebarWidth"
            :opened-width="appComputedShareWidthThreshold"
            :share-width-with-content="appComputedHmSidebarShareWidth"
        >
                <?php
                    echo $sidebar->getContent();
                ?>
        </hm-sidebar>
    <?php endforeach; ?>
<?php endif;?>
