<template>
  <div class="hm-workflow-state">
    <v-tabs

      v-model="tabs"
      class="hm-workflow-state_tabs"
    >
      <v-tabs-slider></v-tabs-slider>
      <v-tab :href="`#${generalTabName}`">
        <template v-slot:activator="{ on }">
          <v-tooltip bottom>
            <v-progress-circular
                    v-if="loading"
                    color="primary"
                    indeterminate
                    class="hm-workflow-state_progress"
            ></v-progress-circular>
            <v-icon slot="activator">assignment</v-icon>
            <span>{{ title }}</span>
          </v-tooltip>
        </template>
      </v-tab>
      <v-tab
        v-for="(form, key) in forms"
        :key="key"
        :href="`#hm-workflow-state-tab${key}`"
      >
        <v-tooltip bottom>
          <v-icon slot="activator">{{ form.icon.name }}</v-icon>
          <span>{{ form.icon.title }}</span>
        </v-tooltip>
      </v-tab>
    </v-tabs>
    <v-tabs-items v-model="tabs" class="hm-workflow-state_items">
      <v-tab-item :value="generalTabName" class="hm-workflow-state_item">
        <hm-workflow-general-tab
          :deadline="deadline"
          :description="description"
          :extended-description="extendedDescription"
          :control-links="controlLinks"
          :status="status"
        ></hm-workflow-general-tab>
      </v-tab-item>
      <v-tab-item
        v-for="(form, key) in forms"
        :key="key"
        class="hm-workflow-state_item"
        :value="`hm-workflow-state-tab${key}`"
      >
        <hm-dependency :template="form.template"></hm-dependency>
      </v-tab-item>
    </v-tabs-items>
  </div>
</template>
<script>
import { STATUS } from "../../const";
import HmWorkflowGeneralTab from "./generalTab";
import HmDependency from "../../../../../helpers/hm-dependency/index";
export default {
  components: { HmDependency, HmWorkflowGeneralTab },
  props: {
    status: {
      validator: value => {
        return [
          STATUS.STATE_STATUS_CONTINUING,
          STATUS.STATE_STATUS_PASSED,
          STATUS.STATE_STATUS_WAITING,
          STATUS.STATE_STATUS_FAILED
        ].includes(value);
      }
    },
    deadline: {
      type: Object,
      default: () => {} // !! add validation
    },
    title: {
      type: String,
      default: null
    },
    description: {
      type: String,
      default: null
    },
    extendedDescription: {
      type: Object,
      default: () => {}
    },
    controlLinks: {
      type: Array,
      default: () => []
    },
    forms: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      generalTabName: "hm-workflow-state-tab-info",
      tabs: this.generalTabName
    };
  }
};
</script>
<style lang="scss">
.hm-workflow-state_tabs {
  margin-top: -8px;
  margin-bottom: 16px;
  display: none; /*возможно понадобится для кастомных комментариев и вложений*/
}
.hm-workflow-state_item {
  .hm-submit {
    min-width: auto;
  }
}
.hm-workflow-state_progress {
  height: 26px !important;
  position: absolute;
  left: 8px;
}
</style>
