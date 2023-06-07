<?php foreach ($this->columnsOfPages as $columnOfPages): ?>
    <div class="footer-menu__column">
        <?php foreach ($columnOfPages as $page): ?>
            <?php $isSubPage = isset($page['subpage']) && $page['subpage']; ?>
            <v-tab
                class="<?php echo $isSubPage ? 'footer-menu__subpage' : 'footer-menu__page' ?>"
                href="<?php echo $page['href'] ?>"
                <?php if ($page['active']):?>
                    active
                <?php endif;?>
            >
                <?php echo $page['label'];?>
            </v-tab>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>