<template>
  <div class="hm-workflow-card">
    <v-card>
      <v-card-title
        class="hm-workflow-card_title headline"
        v-html="title"
      ></v-card-title>
      <v-card-text>
        <v-stepper v-model="activeStep" class="hm-workflow_steps" vertical>
          <div
            v-for="(state, key) in formattedStates"
            :key="key"
            class="hm-workflow_step"
            :class="state.class"
          >
            <v-stepper-step
              :step="key + 1"
              editable
              :complete="true"
              :rules="[() => !state.isFailed]"
              :edit-icon="state.icon"
            >
              {{ state.title }}
            </v-stepper-step>

            <v-stepper-content :step="key + 1">
              <hm-workflow-state
                v-bind="state"
                :loading="loading"
              ></hm-workflow-state>
            </v-stepper-content>
          </div>
        </v-stepper>
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
import { STATUS } from "../const";
import HmWorkflowState from "./partials/state";
import { mapActions } from "vuex";
export default {
  components: { HmWorkflowState },
  props: {
    workflow: {
      type: Object,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      title: "",
      states: [],
      activeStep: null
    };
  },
  computed: {
    formattedStates() {
      return this.states.map(state => {
        let stateItem = JSON.parse(JSON.stringify(state));
        stateItem.icon = this.getIcon(state.status);
        return stateItem;
      });
    }
  },
  watch: {
    workflow() {
      this.init();
    },
    "$store.state.state_files.isSubmit"() {
      this.$emit("update");
      this.stateFilesResetIsSubmit();
    },
    "$store.state.state_comment.isSubmit"() {
      this.$emit("update");
      this.stateCommentResetIsSubmit();
    }
  },
  created() {
    this.init();
  },
  methods: {
    ...mapActions({
      stateFilesResetIsSubmit: "state_files/resetIsSubmit",
      stateCommentResetIsSubmit: "state_comment/resetIsSubmit"
    }),
    init() {
      this.states = this.workflow.states || [];
      this.title = this.workflow.title || "";
      this.initActiveStep();
    },
    getIcon(statusId) {
      switch (statusId) {
        case STATUS.STATE_STATUS_CONTINUING:
          return "hourglass_empty";
        case STATUS.STATE_STATUS_WAITING:
          return "lock";
        case STATUS.STATE_STATUS_PASSED:
          return "done";
        case STATUS.STATE_STATUS_FAILED:
          return "clear";
        default:
          return null;
      }
    },
    initActiveStep() {
      let idStateContinuing = this.states.findIndex(
        state => state.status === STATUS.STATE_STATUS_CONTINUING
      );

      this.activeStep =
        idStateContinuing !== -1 ? idStateContinuing + 1 : false;
    }
  }
};
</script>
<style lang="scss">
@import "../../../../../scss/variables";
.hm-workflow-card {
  overflow-x: hidden;
}
.hm-workflow-card_title {
  margin-right: 35px;
}
.hm-workflow_steps {
  box-shadow: none;
  margin-left: -16px;
  margin-right: -16px;
}
.hm-workflow_step {
  &.status-completed-success {
    .v-stepper__step__step {
      background-color: $successStatusWorkflow !important;
    }
  }
  &.status-continuing {
    .v-stepper__step__step {
      background-color: $continuingStatusWorkflow !important;
    }
  }
  &.status-waiting {
    .v-stepper__step__step {
      background-color: $waitingStatusWorkflow !important;
    }
  }
  &.status-completed-failed {
    .v-stepper__step__step {
      background-color: $fialedStatusWorlflow !important;
    }
  }
  .v-stepper__step {
    padding: 24px;
  }
}
.hm-sidebar {
  .hm-workflow-card {
    .hm-workflow-card_title {
      display: none;
    }
    .hm-workflow_step {
      .v-stepper__step {
        padding: 12px 24px;
      }
    }
  }
  .hm-workflow-general-tab {
    > .flex {
      flex-basis: 100%;
      max-width: 100%;
      &.order-xs1 {
        order: 1;
      }
      &.order-xs2 {
        order: 2;
      }
    }
    .hm-workflow-general-tab_deadline {
      margin-left: 0 !important;
      .hm-date-picker {
        margin-right: 0 !important;
      }
    }
  }
}
</style>
