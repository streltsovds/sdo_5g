<template>
  <div class="hm-programm-builder">
    <v-layout wrap>
      <v-flex xs12 sm6>
        <v-card-text>
          <h2 class="title"><slot name="allSubjectsTitle">Учебные курсы</slot></h2>
          <v-select
            v-if="allItemsSelects.length > 1"
            v-model="selectedItem"
            :items="allItemsSelects"
          >
            <template slot="label"
              ><slot name="selectItemLabel"></slot
            ></template>
          </v-select>
          <v-list>
            <hm-transition-staggered-fade>
              <div v-for="subitem in subsubjectsFiltered" :key="subitem.value">
                <v-list-item
                  class="hm-programm-builder_item"
                  :class="[subitem.classes]"
                >
                  <v-list-item-content>
                    <v-list-item-title>{{ subitem.text }}</v-list-item-title>
                  </v-list-item-content>
                  <v-list-item-action v-if="!readOnly">
                    <v-tooltip v-if="actions.includes('add')" bottom>
                      <v-btn
                        slot="activator"
                        text
                        icon
                        color="success"
                        @click="addItem(subitem)"
                      >
                        <v-icon>add</v-icon>
                      </v-btn>
                      <span><slot name="add">Добавить</slot></span>
                    </v-tooltip>
                  </v-list-item-action>
                </v-list-item>
                <v-divider></v-divider>
              </div>
            </hm-transition-staggered-fade>
          </v-list>
        </v-card-text>
        <v-divider v-if="$vuetify.breakpoint.xsOnly"></v-divider>
      </v-flex>
      <v-flex xs12 sm6>
        <v-card-text>
          <h2 class="title">
            <slot name="includeItemsTitle">Включенные в программу</slot>
          </h2>
          <v-list>
            <draggable v-model="includeItemsFormatted">
              <transition-group
                :css="false"
                @enter="transitionStaggeredFadeEnter"
                @leave="transitionStaggeredFadeLeave"
              >
                <div
                  v-for="includeItem in includeItemsFormatted"
                  :key="includeItem.value"
                >
                  <v-tooltip
                    bottom
                    :disabled="!hiddenItems.includes(includeItem.value)"
                  >
                    <v-list-item
                      slot="activator"
                      class="hm-programm-builder_item"
                      :class="[
                        {
                          'v-list__tile_disabled': hiddenItems.includes(
                            includeItem.value
                          )
                        },
                        includeItem.classes
                      ]"
                    >
                      <v-list-item-action>
                        <v-icon>drag_indicator</v-icon>
                      </v-list-item-action>
                      <v-list-item-content>
                        <v-list-item-title>
                          {{ includeItem.text }}
                          <span v-if="!includeItem.pin" class="error--text"
                            >*</span
                          >
                        </v-list-item-title>
                      </v-list-item-content>
                      <v-list-item-action
                        v-if="!readOnly"
                        class="hm-programm-builder_item-actions"
                      >
                        <v-tooltip
                          v-if="
                            actions.includes('edit') && includeItem.editable
                          "
                          bottom
                        >
                          <v-btn
                            slot="activator"
                            text
                            icon
                            color="blue"
                            :href="`${editUrl}${includeItem.value}`"
                          >
                            <v-icon>edit</v-icon>
                          </v-btn>
                          <span><slot name="edit">Редактировать</slot></span>
                        </v-tooltip>
                        <template v-if="actions.includes('pin')">
                          <v-tooltip v-if="!includeItem.pin" bottom>
                            <v-btn
                              slot="activator"
                              text
                              icon
                              color="blue"
                              @click="unpin(includeItem)"
                            >
                              <v-icon>link_off</v-icon>
                            </v-btn>
                            <span><slot name="unpin"></slot></span>
                          </v-tooltip>
                          <v-tooltip v-else bottom>
                            <v-btn
                              slot="activator"
                              text
                              icon
                              color="blue"
                              @click="pin(includeItem)"
                            >
                              <v-icon>link</v-icon>
                            </v-btn>
                            <span><slot name="pin"></slot></span>
                          </v-tooltip>
                        </template>
                        <v-tooltip v-if="actions.includes('remove')" bottom>
                          <v-btn
                            slot="activator"
                            text
                            icon
                            color="error"
                            @click="removeItem(includeItem)"
                          >
                            <v-icon>remove</v-icon>
                          </v-btn>
                          <span><slot name="remove">Удалить</slot></span>
                        </v-tooltip>
                      </v-list-item-action>
                    </v-list-item>
                    <span
                      ><slot name="hiddenItemTooltip"
                        >Для включения данного этапа отредактируйте элемент
                        программы</slot
                      ></span
                    >
                  </v-tooltip>
                  <v-divider></v-divider>
                </div>
              </transition-group>
            </draggable>
            <hm-transition-staggered-fade>
              <v-list-item
                v-if="
                  optionsModel.hasOwnProperty('mode_finalize') &&
                    optionsModel.mode_finalize
                "
                key="mode_finalize"
                class="primary lighten-3"
              >
                <v-list-item-content
                  ><slot name="modeFinalize"
                    >Итоговая оценочная форма</slot
                  ></v-list-item-content
                >
              </v-list-item>
            </hm-transition-staggered-fade>
          </v-list>
          <hm-checkbox
            v-for="(option, key) in optionsModel"
            :key="key"
            :name="key"
            :attribs="{ label: options[key].label, disabled: readOnly }"
            :checked="option == '1'"
            @change="optionsModel[key] = $event"
          ></hm-checkbox>
          <v-btn
            v-if="showCopyButton"
            :disabled="readOnly"
            color="primary"
            @click="save(true)"
            ><slot name="copyButton"></slot
          ></v-btn>
        </v-card-text>
      </v-flex>
      <v-flex xs12 sm6 v-if="subsessionsFiltered.length > 1">
        <v-card-text>
          <h2 class="title"><slot name="allSessionsTitle">Учебные сессии</slot></h2>
          <v-select
                  v-if="allItemsSelects.length > 1"
                  v-model="selectedItem"
                  :items="allItemsSelects"
          >
            <template slot="label"
            ><slot name="selectItemLabel"></slot
            ></template>
          </v-select>
          <v-list>
            <hm-transition-staggered-fade>
              <div v-for="subitem in subsessionsFiltered" :key="subitem.value">
                <v-list-item
                        class="hm-programm-builder_item"
                        :class="[subitem.classes]"
                >
                  <v-list-item-content>
                    <v-list-item-title>{{ subitem.text }}</v-list-item-title>
                  </v-list-item-content>
                  <v-list-item-action v-if="!readOnly">
                    <v-tooltip v-if="actions.includes('add')" bottom>
                      <v-btn
                              slot="activator"
                              text
                              icon
                              color="success"
                              @click="addItem(subitem)"
                      >
                        <v-icon>add</v-icon>
                      </v-btn>
                      <span><slot name="add">Добавить</slot></span>
                    </v-tooltip>
                  </v-list-item-action>
                </v-list-item>
                <v-divider></v-divider>
              </div>
            </hm-transition-staggered-fade>
          </v-list>
        </v-card-text>
        <v-divider v-if="$vuetify.breakpoint.xsOnly"></v-divider>
      </v-flex>
    </v-layout>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import draggable from "vuedraggable";
import hmCheckbox from "../../forms/hm-checkbox/index";
import GlobalActions from "../../../store/modules/global/const/actions";
import hmTransitionStaggeredFade from "../../helpers/transition/hm-transition-staggered-fade";
// используется миксин вместо компонента hmTransitionStaggeredFade, так как draggable не признает
// кастомные transition-group (не включается transition mode)
import hmTransitionStaggeredFadeMixin from "../../helpers/transition/hm-transition-staggered-fade-mixin";
export default {
  name: "HmProgrammBuilder",
  components: { hmTransitionStaggeredFade, hmCheckbox, draggable },
  mixins: [hmTransitionStaggeredFadeMixin],
  props: {
    allItems: {
      type: Object,
      default: () => {}
    },
    includeItems: {
      type: Object,
      default: () => {}
    },
    hiddenItems: {
      type: Array,
      default: () => []
    },
    saveUrl: {
      type: String,
      required: true
    },
    editUrl: {
      type: String,
      required: true
    },
    options: {
      type: Object,
      default: () => {}
    },
    readOnly: {
      type: Boolean,
      default: false
    },
    showCopyButton: {
      type: Boolean,
      default: false
    },
    actions: {
      type: Array,
      default: () => []
    },
    removeConfirm: {
      type: String,
      default: null
    },
    pinConfirm: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedItem: null,
      includeItemsFormatted: this.formattedItems(this.includeItems),
      optionsModel: {},
      optionsModelIsInit: false,
      requiredItems: [],
      cancelToken: this.$axios.CancelToken,
      axiosSource: null
    };
  },
  computed: {
    allItemsSelects() {
      let items = [];
      for (let prop in this.allItems) {
        if (
          !this.allItems.hasOwnProperty(prop) ||
          !this.allItems[prop].hasOwnProperty("name")
        )
          continue;
        items.push({
          value: prop,
          text: this.allItems[prop]["name"]
        });
      }
      return items;
    },
    subsubjects() {
      if (
        !this.selectedItem ||
        !this.allItems.hasOwnProperty(this.selectedItem) ||
        !this.allItems[this.selectedItem].hasOwnProperty("subsubjects")
      )
        return null;
      return this.allItems[this.selectedItem]["subsubjects"];
    },
    subsubjectsFormatted() {
      return this.formattedItems(this.subsubjects);
    },
    subsubjectsFiltered() {
      return this.subsubjectsFormatted.filter(item => {
        return (
          this.includeItemsFormatted.findIndex(
            method => method.value === item.value
          ) == -1
        );
      });
    },
    subsessions() {
      if (
              !this.selectedItem ||
              !this.allItems.hasOwnProperty(this.selectedItem) ||
              !this.allItems[this.selectedItem].hasOwnProperty("subsessions")
      )
        return null;
      return this.allItems[this.selectedItem]["subsessions"];
    },
    subsessionsFormatted() {
      return this.formattedItems(this.subsessions);
    },
    subsessionsFiltered() {
      return this.subsessionsFormatted.filter(item => {
        return (
                this.includeItemsFormatted.findIndex(
                        method => method.value === item.value
                ) == -1
        );
      });
    }
  },
  watch: {
    optionsModel: {
      handler: function() {
        if (this.optionsModelIsInit) this.save();
        this.optionsModelIsInit = true;
      },
      deep: true
    },
    includeItemsFormatted: {
      handler: function() {
        this.save();
      },
      deep: true
    }
  },
  mounted() {
    this.init();
  },
  methods: {
    ...mapActions("notifications", [
      "addSuccessNotification",
      "addErrorNotification"
    ]),
    ...mapActions("alerts", ["addInfoAlert", "addErrorAlert"]),
    ...mapActions([
      GlobalActions.setLoadingOn,
      GlobalActions.setLoadingOff
    ]),
    init() {
      // init selectedItem
      for (let prop in this.allItems) {
        if (!this.allItems.hasOwnProperty(prop)) continue;
        this.selectedItem = prop;
        break;
      }

      for (let prop in this.options) {
        if (
          !this.options.hasOwnProperty(prop) ||
          !this.options[prop].hasOwnProperty("value")
        )
          continue;
        this.$set(this.optionsModel, prop, this.options[prop].value == "1");
      }

      this.isInit = true;
    },
    formattedItems(items) {
      let formattedItems = [];
      for (let key in items) {
        if (!items.hasOwnProperty(key) || !items[key].hasOwnProperty("name"))
          continue;

        formattedItems.push({
          value: key,
          text: items[key]["name"],
          freemode: items[key].hasOwnProperty("freemode")
            ? items[key]["freemode"]
            : null,
          pin: items[key].hasOwnProperty("pin") ? items[key]["pin"] : null,
          classes: items[key].hasOwnProperty("class")
            ? items[key]["class"]
            : null,
          editable: items[key].hasOwnProperty("editable")
            ? items[key]["editable"]
            : true
        });
      }

      return formattedItems.sort((a, b) => a.text.localeCompare(b.text));
    },
    save(applyToRelatedProfiles = false) {
      if (this.axiosSource) this.axiosSource.cancel("Kbase request canceled");
      if (this.readOnly) return;
      this[GlobalActions.setLoadingOn](this.$options.name);

      let formData = new FormData();

      for (let prop in this.optionsModel) {
        if (!this.optionsModel.hasOwnProperty(prop)) continue;
        formData.append(prop, this.optionsModel[prop] ? "1" : "0");
      }

      this.includeItemsFormatted.forEach(method => {
        formData.append("item_id[]", method.value);
        if (method.hasOwnProperty("pin"))
          formData.append("is_unpin[]", method.pin ? "1" : "0");
      });

      formData.append("mode", applyToRelatedProfiles ? "1" : "0");

      this.axiosSource = this.cancelToken.source();
      const cancelToken = this.axiosSource.token;

      this.$axios
        .post(this.saveUrl, formData, { cancelToken })
        .then(r => {
          if (r.status !== 200) throw new Error();
          this.addSuccessNotification("Изменения сохранены");
          this[GlobalActions.setLoadingOff](this.$options.name);
        })
        .catch(error => {
          if (this.$axios.isCancel(error)) {
            console.log("Request canceled", error.message);
          } else {
            this.addErrorNotification(
              "Произошла ошибка при сохранении изменений"
            );
            this[GlobalActions.setLoadingOff](this.$options.name);
            console.error(error.message);
          }
        });
    },
    addItem(item) {
      let index = this.includeItemsFormatted.findIndex(
        method => method.value == item.value
      );
      if (index !== -1) return;

      this.includeItemsFormatted.push(item);
    },
    removeItem(item) {
      if (!this.removeConfirm) return this.remove(item);

      this.$confirmModal({ text: this.removeConfirm })
        .then(() => this.remove(item))
        .catch(() => {});
    },
    remove(item) {
      let index = this.includeItemsFormatted.findIndex(
        method => method.value == item.value
      );
      if (index === -1) return;

      this.includeItemsFormatted.splice(index, 1);
    },
    pin({ value }) {
      if (!this.pinConfirm) return this.togglePin(value, true);

      this.$confirmModal({ text: this.pinConfirm })
        .then(() => this.togglePin(value, true))
        .catch(() => {});
    },
    unpin({ value, freemode = false }) {
      if (freemode) {
        return this.addInfoAlert(
          "Данный курс не предполагает возможность подачи заявок, поэтому его нельзя сделать курсом по выбору."
        );
      }
      this.togglePin(value, false);
    },
    togglePin(value, needToBePin) {
      let index = this.includeItemsFormatted.findIndex(
        item => item.value === value
      );

      if (index == -1) return;
      this.includeItemsFormatted[index].pin = !needToBePin;
    }
  }
};
</script>
<style lang="scss">
.hm-programm-builder {
  .v-list__tile_disabled {
    opacity: 0.5;
  }
  .v-list__tile {
    &:hover {
      background: rgba(0, 0, 0, 0.04);
      .hm-programm-builder_item-actions {
        opacity: 1;
        pointer-events: auto;
      }
    }
    .hm-programm-builder_item-actions {
      opacity: 0;
      pointer-events: none;
      flex-direction: row;
    }
  }
  .hm-programm-builder_item {
    &.highlighted {
      background-color: rgba(red, 0.1);
    }
  }
}
</style>
