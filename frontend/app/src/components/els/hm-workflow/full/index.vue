<template>
  <div class="hm-workflow-full">
    <hm-workflow-card
      v-if="!!workflow"
      :workflow="workflow"
      :loading="loading"
      @update="loadWorkflow"
    ></hm-workflow-card>
  </div>
</template>
<script>
import HmWorkflowCard from "../partials/card/index";
import { mapActions } from "vuex";
export default {
  name: "HmWorkflowFull",
  components: { HmWorkflowCard },
  props: {
    workflowProps: {
      type: Object,
      required: true
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
      workflow: this.workflowProps
    };
  },
  computed: {
    urlWithParams() {
      return `${this.url}?index=${this.id}`;
    }
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    loadWorkflow() {
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
        })
        .catch(() => {
          this.addErrorAlert("Произошла ошибка!");
        })
        .then(() => {
          this.loading = false;
        });
    }
  }
};
</script>
<style lang="scss">
@import "../../../../scss/variables";
</style>
