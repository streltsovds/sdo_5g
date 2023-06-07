<template>
  <div :style="styles" class="hm-grid-footer">
    <div v-if="selectAll" class="hm-grid-footer__row-1 pa-6">
      <v-select
        v-model="selected"
        class="hm-grid-footer__mainAction"
        :disabled="mainSelectDisabled"
        dense
        hide-details
        outlined
        single-line
        :items="shownMassActions"
        item-value="path"
        return-object
        :label="selectLabel"
      >
        <template slot="selection" slot-scope="{ item }">
          <span class="hm-grid-footer__mainAction__menuItem__selection">{{ item.label }}</span>
        </template>
        <template slot="item" slot-scope="{ item }">
          <span class="hm-grid-footer__mainAction__menuItem">{{ item.label }}</span>
        </template>
      </v-select>

      <div v-if="displaySubMassAction">
        <hm-grid-sub-mass-action
          :key="submassActionKey"
          :action="selectedSubMassAction"
          @invalid="handleSubMassActionInvalid"
          @selected="handleSelectedSubMassAction"
        />
      </div>

      <div v-if="selectedRowsNumber">
        <v-btn
          :loading="isMassActionExecuting"
          :disabled="execBtnDisabled"
          color="primary"
          style="
            text-transform: none;
            font-style: normal;
            font-weight: normal;
            font-size: 12px;
            line-height: 18px;
            padding: 9px 16px;
          "
          @click="handleMassActionExec"
        >
          {{ choosenText.trim() }}
        </v-btn>
      </div>
    </div>

    <v-divider v-if="selectAll"></v-divider>

    <div class="hm-grid-footer__row-2 px-6 py-3">
      <hm-grid-pagination :grid-module-name="gridModuleName">
        <hm-grid-exports
          v-if="exports.length"
          :grid-module-name="gridModuleName"
          :exports="exports"
        />
      </hm-grid-pagination>
    </div>

    <v-dialog v-model="isConfirmModalShown" persistent max-width="320">
      <v-card>
        <v-card-title>
          {{ selectedConfirmText }}
        </v-card-title>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn small color="primary" @click="handleConfirm">Да</v-btn>
          <v-btn
            small
            color="primary"
            text
            @click="isConfirmModalShown = !isConfirmModalShown"
            >Отмена</v-btn
          >
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </div>
</template>

<script>
import HmGridPagination from "./HmGridPagination";
import HmGridExports from "./HmGridExports";
import HmGridSubMassAction from "./HmGridSubMassAction";

import {
  DEFAULT_GRID_MODULE_NAME,
  MIN_TABLE_WIDTH,
  SUB_MASS_ACTIONS_PROPERTY
} from "../constants";

import { decline } from "../../../utilities";
import { createNamespacedHelpers } from "vuex";
import * as actions from "../module/actions/actions";

export default {
  components: {
    HmGridPagination,
    HmGridExports,
    HmGridSubMassAction
  },
  props: {
    width: {
      type: Number,
      default: () => MIN_TABLE_WIDTH
    },
    selectAll: Boolean,
    gridModuleName: {
      type: String,
      default: () => DEFAULT_GRID_MODULE_NAME
    }
  },
  data() {
    return {
      selected: null,
      selectedSubMassActionValue: null,
      isConfirmModalShown: false,
      subMassActionInvalid: false,
      isMassActionExecuting: false,
      isOnStudentAssignPage: false
    };
  },
  computed: {
    styles() {
      const config = {
        width: `${this.width}px`
      };
      if (this.isOnSmallScreen) {
        config[`maxWidth`] = `${this.width}px`;
        config[`overflowX`] = `scroll`;
      }
      return config;
    },
    choosenText() {
      const { selectedRowsNumber } = this;
      const declanationsRows = ["строки", "строк", "строк"];
      const declinedRows = decline(selectedRowsNumber, declanationsRows);
      return `Выполнить для ${selectedRowsNumber} ${declinedRows}`;
    },
    submassActionKey() {
      return Object.keys(this.selectedSubMassAction)[0];
    },
    shownMassActions() {
      // Первый передаваемый масс-экшн нам не нужен
      // Первый элемент в массиве масс экшенов это всегда "Выберите действие"
      // Соответственно он не нужен.
      // Возможно как то надо его фильтровать на бэкенде
      // потому что я видел ситуации когда есть два пункта "Выберите действие"
      return [...this.massActions].splice(1, this.massActions.length);
    },
    selectedConfirmText() {
      if (this.selected && this.selected.confirm) {
        return this.selected.confirm;
      }
      return "";
    },
    selectLabel() {
      // Первый элемент в массиве масс экшенов это всегда "Выберите действие"
      // Соответственно мы забираем его как лейбл для инпута
      return [...this.massActions][0].label;
    },
    selectedRowsNumber() {
      return this.selectedRows.length;
    },
    displaySubMassAction() {
      if (this.selectedMassActionHasSubMassAction) {
        if (
          !this.isOnStudentAssignPage &&
          this.selected.label === "Назначить слушателей на курс"
        )
          return false;
        return true;
      }
      return false;
    },
    selectedMassActionHasSubMassAction() {
      return (
        this.selected && this.selected[SUB_MASS_ACTIONS_PROPERTY] !== undefined
      );
    },
    selectedSubMassAction() {
      return this.selected[SUB_MASS_ACTIONS_PROPERTY];
    },
    massActionUrl() {
      return (this.selected && this.selected.path) || "";
    },
    massActionField() {
      return (this.selected && this.selected.options.postMassIdsColumn) || "";
    },
    execBtnDisabled() {
      if (this.subMassActionInvalid) return true;
      if (!this.selectedRowsNumber) return true;
      if (this.selectedMassActionHasSubMassAction) {
        if (
          !this.isOnStudentAssignPage &&
          this.selected.label === "Назначить слушателей на курс"
        ) {
          return !this.selected;
        } else if (
          !this.isOnStudentAssignPage &&
          this.selected.label === "Перевести в прошедшие обучение"
        ) {
          return !this.selected;
        } else {
          return (
            !this.selectedSubMassActionValue ||
            !this.selectedSubMassActionValue.value ||
            !this.selectedSubMassActionValue.value.length
          );
        }
      } else {
        return !this.selected;
      }
    },
    mainSelectDisabled() {
      return !this.selectedRowsNumber;
    }
  },
  watch: {
    selected() {
      // обнуляем значени сабмасс экшена при смене главного масс экшена
      this.selectedSubMassActionValue = null;
    },
    selectedRowsNumber(val) {
      if (!val) {
        // обнуляем значени когда пользователь посбрасывал все выбранные строки
        this.selected = null;
        this.selectedSubMassActionValue = null;
      }
    }
  },
  beforeCreate() {
    const namespace = this.$options.propsData.gridModuleName;
    const { mapState } = createNamespacedHelpers(namespace);
    const mappedState = mapState({
      massActions: state => state.massActions,
      exports: state => state.exports,
      selectedRows: state => state.selectedRows,
      apiUrl: state => state.apiUrl,
    });
    this.$composeComputed(mappedState);
  },
  mounted() {
    if (window.location.pathname === "/assign/student")
      this.isOnStudentAssignPage = true;
  },
  methods: {
    /**
     * Получить экшн или мутацию уже с неймспейсом
     *
     * @param {String} mutationOrActionName Название мутации или экшена
     */
    getNamespacedName(actionName) {
      return `${this.gridModuleName}/${actionName}`;
    },
    handleConfirm() {
      this.isConfirmModalShown = false;
      this.isMassActionExecuting = true;
      this.execMassAction();
    },
    execMassAction() {
      const actionName = this.getNamespacedName(actions.EXEC_MASS_ACTION);

      let url = this.massActionUrl;

      let confirmText = this.selected.confirm;

      if (confirmText && !confirm(confirmText)) {
        return;
      }

      const payload = {
        url: url,
        field: this.massActionField
      };
      if (this.selectedMassActionHasSubMassAction) {
        payload["submassAction"] = this.selectedSubMassActionValue;
      }
      this.$store.dispatch(actionName, payload);
    },
    handleSelectedSubMassAction(value) {
      this.selectedSubMassActionValue = value;
    },
    async handleMassActionExec() {
      this.isMassActionExecuting = true;
      await this.execMassAction();
      this.isMassActionExecuting = false;
    },
    handleSubMassActionInvalid(isValid) {
      this.subMassActionInvalid = isValid;
    }
  }
};
</script>

<style lang="scss">
.hm-grid-footer {

  &__row-1 {
    display: flex;
    align-items: center;

    > * + * {
      margin-left: 24px;
    }
  }

  .hm-grid-footer__mainAction {
    min-width: 220px;
    max-width: 400px;
    flex-grow: 1;

    .v-select__slot {
      label {
        font-size: 14px;
      }
    }

    .v-select__selections {

      // Иначе при малой ширине селекта и длинной выьранной строке появляется
      // пустая строка которая и есть этот инпут /Dvukzhilov
      & span + input {
        width: 0;
        margin: 0;
        padding: 0;
        height: 0;
      }
    }
  }
}

.hm-grid-footer__mainAction__menuItem {
  &, &__selection {
    font-size: 14px;
  }
}
@media(max-width: 599px) {
  .hm-grid-footer {
    &__row-1 {
      flex-direction: column;

      > * + * {
        margin-left: 0;
        width: 100%;
        margin-bottom: 10px;
      }
    }
    .hm-grid-footer__mainAction {
      min-width: auto;
      width: 100%;
      max-width: 100%;
      margin-bottom: 10px;
    }
  }
}
</style>
