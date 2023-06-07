<v-tooltip class="hm-main-nav-menu__item__tooltip"
           bottom
           :open-delay="150"
           content-class="hm-main-nav-menu__item__tooltip__content"
>
    <template v-slot:activator="{ on: tooltipOn, attrs: tooltipAttrs }">
        <v-list-item-action
            v-bind="tooltipAttrs"
            v-on="tooltipOn"
        >
            <svg-icon name="<?php echo $page->icon ?>" title=""> </svg-icon>
        </v-list-item-action>
    </template>
    <span class="hm-main-nav-menu__item__tooltip__text">
        <?php echo $page->getLabel(); ?>
    </span>
</v-tooltip>
