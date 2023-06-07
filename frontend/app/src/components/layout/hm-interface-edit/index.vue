<template>
  <div class="hm-interface-edit">
    <v-card class="hm-interface-edit__add-panel pa-4 mb-6" style="max-width: 800px">
      {{ /* Выбор роли */ }}

      <v-select
        v-model="selectedRole"
        :items="rolesFormatted"
        :label="_(selectRoleLabel)"
      />

      {{ /* Выбор виджета */ }}
      <div
        v-if="Object.keys(availableInfoblocks).length > 0"
        style="display: flex;"
      >
        <hm-grouped-select
          :label="_(selectBlockLabel)"
          :items="availableInfoblocks"
          :selected="selectedBlock"
          @update="selectedBlock = $event"
        />

        <v-btn
          class="ml-5 mt-3"
          color="primary"
          @click="addBlock"
        >
          Добавить
        </v-btn>
      </div>
    </v-card>

    {{ /* Добавленные инфоблоки */ }}
<!--      <v-col cols="12">-->
<!--        <draggable-->
<!--          v-model="infoblocks"-->
<!--          class="hm-interface-edit_draggable layout wrap"-->
<!--          :options="dragOptions"-->
<!--          @change="saveBlocks()"-->
<!--        >-->

        <!--
          TODO Draggable component should directly wrap the draggable elements,
            or a transition-component containing the draggable elements.
            Найти другую drag'n'drop библиотеку
        -->
<!--          <hm-typical-widget-options-->
<!--            v-for="(infoblock, key) in infoblocks"-->
<!--            :key="infoblock.y + infoblock.name"-->
<!--            :size-prop="infoblock.width"-->
<!--          >-->
    <hm-widgets-composer
      :widgets="infoblocks"
    >
<!--          <v-col-->
<!--            v-for="(infoblock, key) in infoblocks"-->
<!--            :key="infoblock.y + infoblock.name"-->
<!--          >-->
      <template v-slot:widget="{ widget: infoblock }">
        <div class="hm-interface-edit_draggable-item">
          <v-card class="mb-1 infoblock-card" tile>
            <v-card-title class="title grey lighten-2">
              {{ infoblock.title }}
            </v-card-title>

            <hm-dependency :template="infoblock.innerHtml" />
          </v-card>
          <div class="hm-interface-edit_draggable-item_overlay">
            <hm-interface-edit-action-button
              v-if="infoblock.y !== 0"
              icon="arrow_back"
              tooltip="Поменять местами с предыдущим блоком"
              @click="upBlock(infoblock)"
            />

            <hm-interface-edit-action-button
              v-if="infoblock.y !== infoblocks.length - 1"
              icon="arrow_forward"
              tooltip="Поменять местами со следующим блоком"
              @click="downBlock(infoblock)"
            />

            <hm-interface-edit-action-button
              color="error"
              icon="close"
              tooltip="Удалить"
              @click="openModal(infoblock)"
            />
          </div>
        </div>
      </template>

    </hm-widgets-composer>

    <hm-dialog
      size="small"
      semanticAccent="info"
      :status="statusDualog"
    >
      <template v-slot:content>
        <p>Вы уверены что хотите удалить блок?</p>
      </template>
      <template v-slot:buttons>
        <v-btn @click="blockRemove" color="primary" depressed>
          Ок
        </v-btn>
        <v-btn @click="modalClose" depressed>
          Отмена
        </v-btn>
      </template>
    </hm-dialog>
<!--          </hm-typical-widget-options>-->
<!--        </draggable>-->
<!--      </v-col>-->

    <hm-loading-dialog :is-open="isLoading" />
  </div>
</template>
<script>
import { mapActions } from "vuex";
import HmDialog from "@/components/controls/hm-dialog/HmDialog.vue";
// import draggable from "vuedraggable";
import HmLoadingDialog from "../../helpers/hm-loading/dialog";
import HmDependency from "../../helpers/hm-dependency";
import HmGroupedSelect from "../../forms/hm-select/partials/grouped";
import GlobalActions from "@/store/modules/global/const/actions";
import HmInterfaceEditActionButton from "./_action_button";
import HmWidgetsComposer from "../hm-widgets-composer";

export default {
  name: "HmInterfaceEdit",
  components: {
    HmWidgetsComposer,
    HmDependency,
    HmGroupedSelect,
    // draggable,
    HmLoadingDialog,
    // HmTypicalWidgetOptions,
    HmInterfaceEditActionButton,
    HmDialog
  },
  props: {
    getUrl: {
      type: String,
      required: true,
    },
    saveUrl: {
      type: String,
      required: true,
    },
    roles: {
      type: Object,
      default: () => {},
    },
    role: {
      type: String,
      default: null,
    },
    selectRoleLabel: {
      type: String,
      default: "Выберите роль",
    },
    selectBlockLabel: {
      type: String,
      default: "Выберите виджет",
    },
  },
  data() {
    return {
      selectedRole: this.role,
      isLoading: false,
      infoblocks: [],
      availableInfoblocks: {},
      selectedBlock: null,
      cancelToken: this.$axios.CancelToken,
      axiosSource: null,
      test: null,
      statusDualog: false,
      idBlock: null
    };
  },
  computed: {
    rolesFormatted() {
      let rolesFormatted = [];

      for (let roleName in this.roles) {
        if (!this.roles.hasOwnProperty(roleName)) continue;
        rolesFormatted.push({
          value: roleName,
          text: this.roles[roleName],
        });
      }

      return rolesFormatted;
    },
    dragOptions() {
      return {
        animation: 0,
        disabled: this.$store.state.global.isLoading,
      };
    },
  },
  watch: {
    selectedRole() {
      this.loadInfoblocks();
    },
    availableInfoblocks() {},
  },
  created() {
    this.loadInfoblocks();
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert", "addAlert"]),
    ...mapActions("notifications", ["addSuccessNotification"]),
    ...mapActions([GlobalActions.setLoadingOn, GlobalActions.setLoadingOff]),
    modalClose() {
      this.statusDualog = false;
    },
    blockRemove(id) {
      const infoblocks = JSON.parse(JSON.stringify(this.infoblocks));
      infoblocks.splice(this.idBlock, 1);
      this.saveBlocks(infoblocks);
      this.loadInfoblocks()
      this.modalClose();
    },
    loadInfoblocks() {
      const params = { role: this.selectedRole };
      this[GlobalActions.setLoadingOn](this.$options.name);
      this.$axios
        .get(this.getUrl, { params })
        .then(r => {
          // console.warn(r.data);
          if (r.status !== 200 || !r.data || !r.data.items) throw new Error();

          this.infoblocks = this.sortInfoblocks(r.data.items);
          this.availableInfoblocks = this.infoblockFormatterForSelect(
            r.data.allItems
          );
          // console.log('запрос then')
          // console.log(this.availableInfoblocks)
        })
        .catch(e => {
          // console.error(e.message);
          this.addErrorAlert("Произошла ошибка при загрузке инфоблоков");
        })
        .finally(() => {
          this[GlobalActions.setLoadingOff](this.$options.name);
        });
    },
    infoblockFormatterForSelect(infoblocks) {
      let infoblockFormatter = {};
      const currentInfoblocks = this.infoblocks.map(item => item.name);

      for (let prop in infoblocks) {
        if (!infoblocks.hasOwnProperty(prop)) continue;
        let blocksSection = JSON.parse(JSON.stringify(infoblocks[prop]));
        if (!blocksSection.hasOwnProperty("block")) continue;

        let section = (infoblockFormatter[blocksSection.title] = {});

        let blocks = {};
        if(blocksSection["block"].hasOwnProperty("name")) blocks = [blocksSection["block"]];
        else blocks = blocksSection["block"];

        const newBlocks = {};
        for (let key in blocks) {
          if(!currentInfoblocks.includes(blocks[key].name)) newBlocks[key] = blocks[key];
        };

        if (newBlocks.hasOwnProperty("name")) {
          this.addBlockInSection(section, newBlocks);
        } else {
          for (let blockKey in newBlocks) {
            if (!newBlocks.hasOwnProperty(blockKey)) continue;
            let block = newBlocks[blockKey];
            this.addBlockInSection(section, block);
          };
        };
      };
      const endInfoblockFormatter = {};
      for (let key in infoblockFormatter) {
        if(Object.keys(infoblockFormatter[key]).length > 0) endInfoblockFormatter[key] = infoblockFormatter[key];
      };
      return endInfoblockFormatter;
    },
    addBlockInSection(section, block) {
      section[block["name"]] = block.hasOwnProperty("title")
        ? block["title"]
        : block["name"];
    },
    sortInfoblocks(infoblocks) {
      return infoblocks.sort(this.fnSortInfoblocks);
    },
    fnSortInfoblocks(b1, b2) {
      if (b1.y < b2.y) return -1;
      if (b1.y > b2.y) return 1;
      return 0;
    },
    getInfoblockId(block) {
      return this.infoblocks.findIndex(item => item.name === block.name);
    },
    addBlock() {
      let infoblocks = JSON.parse(JSON.stringify(this.infoblocks));

      /**
       * Блок добавляется в начало.
       * TODO может быть в конец?
       **/
      infoblocks.unshift({ name: this.selectedBlock });
      this.saveBlocks(infoblocks);
    },
    openModal(block) {
      const id = this.getInfoblockId(block);
      if (id === -1) return this.addErrorAlert("Удаляемый инфоблок не найден");
      this.idBlock = id;
      this.statusDualog = true;
    },
    saveBlocks(infoblocks = null) {
      let params = this.getInfoblocksParams(infoblocks);
      if (this.axiosSource) this.axiosSource.cancel("Kbase request canceled");
      this.axiosSource = this.cancelToken.source();

      const cancelToken = this.axiosSource.token;
      this[GlobalActions.setLoadingOn](this.$options.name);

      return this.$axios
        .get(this.saveUrl, { params, cancelToken })
        .then(r => {
          if (r.status !== 200 || !r.data) throw new Error();
          this.addSuccessNotification("Изменения в инфоблоках сохранены");
          this.infoblocks = this.sortInfoblocks(r.data);
          this.selectedBlock = null;
          this.loadInfoblocks();
        })
        .catch(e => {
          if (this.$axios.isCancel(e)) {
            // console.log("Request canceled", e.message);
          } else {
            this.addErrorAlert("Произошла ошибка при сохранении инфоблоков");
            // console.error(e.message);
          }
        })
        .finally(() => {
          this[GlobalActions.setLoadingOff](this.$options.name);
        });
    },
    getInfoblocksParams(infoblocks) {
      let params = [];
      const items = infoblocks || this.infoblocks;
      items.forEach((infoblock, key) => {
        params.push({
          y: key,
          block: infoblock.name,
          // width: 0,
          param_id: infoblock.param,
        });
      });
      return { widgets: JSON.stringify(params), role: this.selectedRole };
    },
    downBlock(block) {
      this.changePositionBlock(block, 1);
    },
    upBlock(block) {
      this.changePositionBlock(block, -1);
    },
    changePositionBlock(block, step) {
      const id = this.getInfoblockId(block);

      if (id === -1 || id > this.infoblocks.length - 1) {
        return;
      }

      const infoblocksCopy = JSON.parse(JSON.stringify(this.infoblocks));
      const movedInfoblock = infoblocksCopy.splice(id, 1);
      infoblocksCopy.splice(id + step, 0, movedInfoblock[0]);
      this.saveBlocks(infoblocksCopy);
    },
  },
  mounted() {
    // console.log(this.getUrl);
  },
  beforeUpdate() {
    // console.log(this.getUrl);
    // this.infoblocks.map(el => console.log(el));
  },
};
</script>
<style lang="scss">
.hm-interface-edit__add-panel {
  margin-left: 36px !important;
  margin-right: 36px !important;
}
.hm-interface-edit_draggable-item {
  position: relative;
  .infoblock-card {
    //pointer-events: none;
  }
}
.hm-interface-edit_draggable-item_overlay {
  position: absolute;
  top: 0;
  //left: 0;
  //bottom: 0;
  right: 42px;

  /* cursor: move; */
  display: flex;
  justify-content: flex-end;
  padding: 8px 16px;

  &:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;

    /** место для действия инфоблока */
    right: 0;
    background: black;
    opacity: 0;
    transition: opacity 0.3s;
  }
  &:hover {
    box-shadow: 0 10px 30px rgba(209, 213, 223, .5) !important;

    &:before {
      //opacity: 0.1;
    }
  }

  .hm-interface-edit-action-button {
    margin-left: 4px;
  }
}
</style>
