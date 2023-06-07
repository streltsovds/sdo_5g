<template>
  <div class="hm-workflow-short">
    <a v-if="!loading" @click="loadWorkflow" class="hm-workflow-short_bulbs">

        <span
          v-for="(state, key) in filteredStates"
          :key="key"
          class="hm-workflow-short_bulb"
          :title="state.title"
          :class="state.classes"
        ></span>

    </a>
    <v-progress-linear
      v-else
      indeterminate
      color="primary"
      class="hm-workflow-short_loader"
    ></v-progress-linear>
    <hm-modal
      :is-shown="isOpen"
      class-name="hm-workflow-modal"
      :close-btn="true"
      @close="close"
    >
      <hm-workflow-card
        v-if="!!workflow && isOpen"
        :workflow="workflow"
        :loading="loading"
        @update="loadWorkflow"
      ></hm-workflow-card>
    </hm-modal>
  </div>
</template>
<script>
import HmModal from "@/components/layout/hm-modal/index";
import HmWorkflowCard from "../partials/card/index";
import { mapActions } from "vuex";
export default {
  components: { HmModal, HmWorkflowCard },
  props: {
    states: {
      type: Array,
      default: () => []
    },
    url: {
      type: String,
      required: true
    },
    id: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      workflow: null,
      isOpen: false
    };
  },
  computed: {
    filteredStates() {
      return this.states.filter(state => state.isVisible);
    },
    urlWithParams() {
      return `${this.url}?index=${this.id}`;
    }
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    loadWorkflow(event) {
      event.stopImmediatePropagation();
      if (this.loading) return;
      this.loading = true;
      this.$axios
        .get(this.urlWithParams)
        .then(response => {
          if (
            response.status !== 200 ||
            !response.data ||
            !response.data.workflow
          ) {
            throw new Error("Workflow not loaded!");
          }
          this.workflow = response.data.workflow;
          this.isOpen = true;
        })
        .catch(() => {
          this.addErrorAlert("Произошла ошибка!");
        })
        .then(() => {
          this.loading = false;
        });
    },
    close() {
      this.isOpen = false;
    }
  }
};
</script>
<style lang="scss">
@import "../../../../scss/variables";

.hm-workflow-short_bulbs {
  list-style: none;
  display: flex;
  cursor: pointer;
  align-items: flex-end;
  transition: background 0.3s;
  padding: 5px;
  border-radius: 3px;
  width: fit-content;
  &:hover {
    background: lighten(#000, 95%);
  }
}
.hm-workflow-short_bulb {
  width: 7px;
  height: 7px;
  margin-left: 2px;
  margin-right: 2px;

  &.status-waiting {
    background: $waitingStatusWorkflow;
  }
  &.status-completed-success {
    background: $successStatusWorkflow;
  }
  &.status-continuing {
    height: 10px;
    background: $continuingStatusWorkflow;
  }
  &.status-completed-failed {
    background: $fialedStatusWorlflow;
  }
}
.hm-workflow-modal {
  max-width: 1000px !important;
  position: relative;
}
</style>
