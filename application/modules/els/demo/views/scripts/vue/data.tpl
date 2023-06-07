<v-card class="mb-4">
    <v-card-text>
        <h3>Php:</h3>
        <p><?php echo $this->testCaption; ?></p>

        <h3>Vue:</h3>
        <v-label>{{ view.testCaption }}</v-label>
    </v-card-text>
</v-card>

<v-card>
    <v-card-title>
        <h3>Тест передачи данных в таблицу</h3>
    </v-card-title>
    <v-card-text>
        <v-data-table
                :headers="view.tableHeaders"
                :items="view.tableData"
        >
            <template v-slot:items="props">
                <td>{{ props.item.a }}</td>
                <td>{{ props.item.b }}</td>
            </template>
        </v-data-table>
    </v-card-text>
</v-card>

<v-card>
    <v-card-title>
        <h3>Передача для view helper</h3>
    </v-card-title>

    <v-card-text>
        <p>
            Передача данных через "view." работает только для шаблонов контроллеров.
            У каждого из view helper'ов свой шаблон, вид и контекст вида.
            И если необходимо передать во view helper'е, нужно
            <ol>
                <li>
                    использовать для html-атрибута одинарные кавычки, т. к. json_encode
                    названия атрибутов и строки заключает в двойные (")
                </li>
                <li>
                    json_encode запускать с параметром JSON_HEX_APOS,
                    иначе первая попавшаяся одинарная кавычка (')
                    может преждевременно закончить html-атрибут и сломать рендеринг шаблона
                </li>
            </ol>
        </p>

        <v-data-table
            :headers='<?php echo json_encode($this->tableHeaders, JSON_HEX_APOS); ?>'
            :items='<?php echo json_encode($this->tableData, JSON_HEX_APOS); ?>'
        >
            <template v-slot:items="props">
                <td>{{ props.item.a }}</td>
                <td>{{ props.item.b }}</td>
            </template>
        </v-data-table>
    </v-card-text>
</v-card>