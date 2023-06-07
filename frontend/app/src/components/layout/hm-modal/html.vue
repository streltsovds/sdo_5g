<template>
  <hm-dialog
    size="medium"
    :title="title"
    semanticAccent="info"
    :status="isShown"
    @close="$emit('close')"
    :buttonClose="true"
  >
    <template v-slot:content>
      <iframe class="hm-modal-html__content_iframe"
        v-if="contentUrl"
        :src="contentUrl"
      >
      </iframe>

      <div class="hm-modal-html__content_html hm-user-content"
        v-else
        v-html="content"
      >
      </div>
    </template>
    <template v-slot:buttons>
      <v-btn
        @click="$emit('close')"
        color="primary"
        depressed
      >
        OK
      </v-btn>
      <v-btn
        v-if="printUrl"
        @click="print"
        text
      >
        {{ _('Печать') }}
      </v-btn>
    </template>
  </hm-dialog>
</template>

<script>
import HmDialog from "@/components/controls/hm-dialog/HmDialog.vue";

export default {
  name: "HmModalHtml",
  components: {HmDialog},
  props: {
    content: {
      type: String,
      default: null,
    },
    contentUrl: {
      type: String,
      default: null,
    },
    printUrl: {
      type: String,
      default: null,
    },
    title: {
      type: String,
      default: null,
    },
    isShown: {
      type: Boolean,
      default: false,
    },
    maxWidth: {
      type: Number,
      default: 700,
    }
  },
  computed: {},
  methods: {
    print() {
      window.open(this.printUrl);
    },
  },
};
</script>

<style lang="scss">
.hm-modal-html {
  &__content_iframe {
    border: none;
    width: 100%;
  }
}
</style>
