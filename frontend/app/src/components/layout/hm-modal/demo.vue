<template>
  <div class="hm-demo-modal">

    <h2>hm-modal</h2>

    <v-btn @click="showModal">
      Show modal
    </v-btn>

    <hm-modal
      :is-shown="modalShown"
      @close="closeModal"
      close-btn
    >
      <v-card>
        <v-card-text>
          Test text
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="green darken-1" text="flat" @click="closeModal">
            OK
          </v-btn>
        </v-card-actions>
      </v-card>
    </hm-modal>

    <h2>hm-modal-activator</h2>

    <hm-modal-activator
      content-html="<p><b>Test</b> content</p><p>More test content</p>"
      title="Modal title"
    >
      <a>Show modal 2</a>
    </hm-modal-activator>

    <hm-modal-activator
      content-url="/techsupport/ajax/view/support_request_id/1"
      title="Iframe modal title"
    >
      <a>Show modal 3</a>
    </hm-modal-activator>
  </div>
</template>

<script>
import HmModal from "./index"
import HmModalActivator from "./activator";

export default {
  name: "HmDemoModal",
  components: {
    HmModal,
    HmModalActivator
  },
  data: function() {
    return {
      modalShown: false,
    };
  },
  computed: {

  },
  methods: {
    closeModal() {
      this.modalShown = false;
    },
    showModal() {
      this.modalShown = true;
    }
  }
};
</script>

<style lang="scss">

</style>
