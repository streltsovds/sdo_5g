<template>
  <div
    class="hm-card-link"
    :style="{
      color: textColor,
    }"
  >
    <a v-show="!isLoading"
       :title="title"
       :rel="rel"
       @click="loadData"
       class="hm-card-link__link"
    >
      <slot />
    </a>
    <v-progress-linear
      class="hm-card-link_loader"
      v-if="isLoading"
      indeterminate
      color="primary"
    />
    <hm-card-link-modal
      v-if="isOpen && title !== 'Карточка учебного курса' && title !== 'Карточка учебной сессии'"
      :is-open="isOpen"
      :type-card="title"
      v-bind="modalProps"
      @close="close"
    />
    <hm-card-link-modal-course
      v-if="isOpen && (title === 'Карточка учебного курса' || title === 'Карточка учебной сессии')"
      :is-open="isOpen"
      :type-card="title"
      v-bind="modalProps"
      @close="close"
    />
  </div>
</template>

<script>
import { mapActions } from "vuex";
import HmCardLinkModal from "./partials/modal";
import HmCardLinkModalCourse from "./partials/modal-course";

export default {
  components: { HmCardLinkModal, HmCardLinkModalCourse },
  props: {
    title: {
      type: String,
      default: "",
    },
    url: {
      type: String,
      required: true,
    },
    rel: {
      type: String,
      default: null,
    },
    textColor: {
      type: String,
      default: null,
    },
    // float: {
    //   type: String,
    //   default: null,
    // },
  },
  data() {
    return {
      modalProps: {},
      isLoading: false,
      isOpen: false,
    };
  },
  computed: {

  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    loadData() {
      if (this.isLoading) return;
      this.isLoading = true;

      this.$axios
        .get(this.url)
        .then(r => {
          if (r.status !== 200 || !r.data) throw new Error();
          return r.data;
        })
        .then(
          ({
            fields = [],
            photo,
            defaultPhoto,
            actions = [],
            title = null,
            content = null,
            fullscreen = false,
            links = []
          }) => {
            this.modalProps = {
              fields,
              photo,
              defaultPhoto,
              actions,
              title,
              content,
              fullscreen,
              links,
              rel: this.rel
            };
            this.isOpen = true;
          }
        )
        .catch(e => {
          console.error(e);
          this.addErrorAlert("Произошла ошибка при загрузке данных!");
        })
        .finally(() => (this.isLoading = false));
    },
    close() {
      this.isOpen = false;
    }
  },
};
</script>
<style lang="scss">
.hm-card-link__link {
  line-height: 0;
}
.hm-card-link_loader {
  width: 100%;
  min-width: 20px;
  margin: 0;
}
</style>
