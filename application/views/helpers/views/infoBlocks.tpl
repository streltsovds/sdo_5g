<hm-widgets-composer
    class="view-helper-infoblocks"
    :widgets='<?php echo $this->infoBlocksJson ?>'
>
    <template v-slot:widget="{ widget: infoblock }">
        <v-card
            class="view-helper-infoblocks__widget-slot infoblock-card"
            tile
            :data-debug-infoblock-name="infoblock.name"
            :data-debug-infoblock-category="infoblock.category"
        >
            <v-card-title
                v-if='((infoblock.showTitle === undefined) || infoblock.showTitle) && infoblock.title !== "РћРїСЂРѕСЃ"'
                class="title  lighten-4"
            >
                {{ infoblock.title }}
            </v-card-title>

            <?php
                /**
                 * NOTE: после попытки убирания обёртывания в span (:wrap="true")
                 * hm-dependency, вылезла ошибка
                 * "Component template should contain exactly one root element.*
                 */
            ?>
            <hm-dependency :template="infoblock.innerHtml"></hm-dependency>
        </v-card>
    </template>
</hm-widgets-composer>
