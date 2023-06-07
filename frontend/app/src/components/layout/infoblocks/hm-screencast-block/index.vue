<template>
  <div class="INFOBLOCK infoblock-screencast">
    <v-select
      class="infoblock-screencast_select"
      :label="label"
      :items="items"
      :selected="selected"
      :disabled="isLoading"
      :loading="isLoading"
      @change="loadScreencast"
    ></v-select>
    <div
      v-if="screencast"
      class="infoblock-screencast_content"
      v-html="screencast"
    ></div>
  </div>
</template>
<script>
import { mapActions } from "vuex";
export default {
  props: {
    label: {
      type: String,
      default: "Выберите видеоролик"
    },
    screencasts: {
      type: Object,
      default: () => {}
    },
    selected: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      url: "infoblock/screencast/get-screencast",
      isLoading: false,
      screencast: null
    };
  },
  computed: {
    items() {
      if (!this.screencasts) return [];

      let items = [];
      for (let key in this.screencasts) {
        if (this.screencasts.hasOwnProperty(key)) {
          items.push({
            value: key,
            text: this.screencasts[key]
          });
        }
      }
      return items;
    }
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    loadScreencast(value) {
      if (this.isLoading) return;

      this.isLoading = true;
      this.$axios
        .post(this.url, { screencast: value })
        .then(r => {
          if (r.status !== 200 || r.data) throw new Error();
          this.screencast = r.data;
        })
        .catch(e => {
          console.error(e);
          this.addErrorAlert("Произошла ошибка при загрузке видеоролика");
        })
        .finally(() => (this.isLoading = false));
    }
  }
};
</script>
<style lang="scss">
.infoblock-screencast_select {
  max-width: 500px;
}
</style>
