<template>
  <v-card
    v-if="items.length > 0"
    class="hm-kbase-short-info hm-kbase_recently-released mb-4"
  >
    <v-card-title class="headline">{{ title }}</v-card-title>
    <v-card-text>
      <hm-loading-alert v-if="isLoading"></hm-loading-alert>
      <v-layout v-else wrap>
        <template v-for="item in items">
          <hm-dependency
            :key="item.resource_id"
            :template="item.content"
            :wrap="false"
          ></hm-dependency>
        </template>
      </v-layout>
    </v-card-text>
    <v-divider></v-divider>
    <v-card-actions>
      <v-btn small text :href="linkShowAll" color="primary"
        >Посмотреть все</v-btn
      >
    </v-card-actions>
  </v-card>
</template>
<script>
import HmLoadingAlert from "../../../helpers/hm-loading/alert";
import HmDependency from "../../../helpers/hm-dependency/index";
export default {
  components: { HmLoadingAlert, HmDependency },
  props: {
    url: {
      type: String,
      default: "/resource/search/index/"
    },
    order: {
      type: String,
      default: "last"
    },
    title: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      isLoading: false,
      items: [],
      params: {
        ajax: true,
        page: 1,
        itemsPerPage: 8
      }
    };
  },
  computed: {
    linkShowAll() {
      return `${this.url}?order=${this.order}`;
    }
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      if (this.isLoading) return;

      this.isLoading = true;
      const params = { ...this.params, order: this.order };
      this.$axios
        .get(this.url, { params })
        .then(r => {
          if (r.status !== 200 || !r.data || !r.data.items)
            throw new Error(
              "Ошибка при загрузке недавно добавленных материалов"
            );
          return r.data;
        })
        .then(data => (this.items = data.items))
        .catch(e => console.error(e))
        .finally(() => (this.isLoading = false));
    }
  }
};
</script>
