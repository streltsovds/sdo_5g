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
                v-if='((infoblock.showTitle === undefined) || infoblock.showTitle) && infoblock.title !== "Опрос"'
                class="title  lighten-4"
            >
                {{ infoblock.title }}
            </v-card-title>

            <?php
                /**
                 * NOTE: ����� ������� �������� ���������� � span (:wrap="true")
                 * hm-dependency, ������� ������
                 * "Component template should contain exactly one root element.*
                 */
            ?>
            <hm-dependency :template="infoblock.innerHtml"></hm-dependency>
        </v-card>
    </template>
</hm-widgets-composer>
