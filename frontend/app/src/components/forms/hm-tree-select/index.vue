<template>
  <div class="hm-form-element hm-tree-select">
    <label
      v-if="label"
      class="v-label theme--light"
      :class="{ required }"
      v-text="label"
    ></label>
    <p v-if="description" v-text="description"></p>
    <hm-errors :errors="errors"></hm-errors>
    <v-container class="hm-tree-select_container">
      <v-layout class="hm-tree-select_nav">
        <v-tooltip bottom>
          <template v-slot:activator="{ on }">
            <v-btn v-on="on" icon text color="primary" @click="goRoot">
              <v-icon>home</v-icon>
            </v-btn>
          </template>
          <span>Вернуться в корень</span>
        </v-tooltip>
        <v-tooltip bottom>
          <template v-slot:activator="{ on }">
            <v-btn v-on="on" icon text color="success" @click="goPrev">
              <v-icon>arrow_back</v-icon>
            </v-btn>
          </template>
          <span>Назад</span>
        </v-tooltip>
        <v-breadcrumbs :items="breadcrumbs" divider="/">
          <template slot="item" slot-scope="props">
            <a
              :class="[props.item.disabled && 'disabled']"
              @click.prevent="goItem(props.item.href)"
              >{{ props.item.text }}</a
            >
          </template>
        </v-breadcrumbs>
      </v-layout>
      <v-progress-circular
        v-if="isLoading"
        indeterminate
        color="primary"
        class="hm-tree-select_loader"
      ></v-progress-circular>
      <v-layout v-if="!isLoading">
        <v-list
          v-if="filteredItems && filteredItems.length > 0"
          subheader
          class="hm-tree-select_list"
        >
          <v-list-item v-for="(item, key) in filteredItems" :key="key">
            <v-list-item-action>
              <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                  <v-checkbox
                    v-on="on"
                    v-model="selectedItem"
                    :value="item.id"
                    :disabled="!allowedAdd(item)"
                  ></v-checkbox>
                </template>
                <span>Выбрать</span>
              </v-tooltip>
            </v-list-item-action>

            <v-list-item-content>
              <v-list-item-title>
                <v-icon color="orange">folder</v-icon>
                <a
                  title="Открыть следующий уровень"
                  v-if="!item.leaf"
                  @click.prevent="goItem(item.id)"
                  v-html="item.value"
                >
                </a>
                <span v-else v-html="item.value" />
              </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-layout>
      <input type="hidden" :name="name" :value="result" />
    </v-container>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import HmErrors from "./../hm-errors";
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmTreeSelect",
  components: { HmErrors },
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      required: true
    },
    attribs: {
      type: Object,
      required: true
    },
    optionsProp: {
      type: Object,
      default: () => {}
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      root: 0,
      owner: null,
      items: [],
      url: "",
      selectedItem: null,
      options: {
        allow: {
          leaf: true,
          node: true
        },
        show: {
          leaf: true,
          node: true
        }
      },
      formId: this.attribs.formId || null,
      label: this.attribs.label || "",
      description: this.attribs.description || "",
      required: this.attribs.required || false,
      result: null,
      isLoading: false,
      path: null
    };
  },
  computed: {
    filteredItems() {
      if (!this.items) return [];
      return this.items.filter(item =>
        item.leaf ? this.options.show.leaf : this.options.show.node
      );
    },
    breadcrumbs() {
      if (!this.path || !this.path.length) return [];
      let path = JSON.parse(JSON.stringify(this.path));
      return path.reverse().map(item => {
        return {
          text: item.name,
          href: this.getUrlById(item.id),
          disabled: false
        };
      });
    }
  },
  watch: {
    selectedItem: {
      handler: function(v) {
        this.mixinStateUpdate("result", v);
      },
      immediate: true
    }
  },
  created() {
    this.init();

    if (this.url) {
      let url = this.owner ? this.getUrlById(this.owner) : this.url;

      this.getData(url);
    }

    if (this.optionsProp) {
      this.setOptions(this.optionsProp);
    }
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    getData(url) {
      if (this.isLoading) return;
      this.isLoading = true;

      this.$axios
        .post(url)
        .then(r => {
          if (r.status !== 200 || !r.data) throw new Error("Error");
          return r.data;
        })
        .then(data => {
          const { items = [], owner = null, path = null } = data;
          this.items = items;
          this.owner = owner;
          this.path = path;
        })
        .catch(() => this.addErrorAlert("Произошла ошибка"))
        .then(() => (this.isLoading = false));
    },

    init() {
      const { params } = this.attribs;
      if (!params) return;

      if (params.remoteUrl) {
        this.url = params.remoteUrl;
      }

      if (params.ownerId) {
        this.owner = params.ownerId;
      }

      if (
        params.selected &&
        params.selected.length > 0 &&
        params.selected[0].id
      ) {
        this.selectedItem = params.selected[0].id;
      }
    },

    goRoot() {
      let rootUrl = this.getUrlById(this.root);
      this.selectedItem = null;
      this.getData(rootUrl);
    },

    goPrev() {
      let prevUrl = this.getUrlById(this.owner);
      this.selectedItem = null;
      this.getData(prevUrl);
    },

    goItem(itemId) {
      let itemUrl = this.getUrlById(itemId);
      this.selectedItem = null;
      this.getData(itemUrl);
    },

    getUrlById(id) {
      return `${this.url}/item_id/${id}`;
    },

    setOptions(options) {
      const { allow = false, show = false } = options;

      this.setAllowOptions(allow);
      this.setShowOptions(show);
    },

    setAllowOptions(allow) {
      if (!allow) return;

      if (allow.leaf !== undefined) this.options.allow.leaf = allow.leaf;
      if (allow.node !== undefined) this.options.allow.node = allow.node;
    },

    setShowOptions(show) {
      if (!show) return;

      if (show.leaf !== undefined) this.options.show.leaf = show.leaf;
      if (show.node !== undefined) this.options.show.node = show.node;
    },

    allowedAdd(item) {
      return item.leaf ? this.options.allow.leaf : this.options.allow.node;
    }
  }
};
</script>
<style lang="scss">
.hm-tree-select_container {
  border: 1px solid rgba(0, 0, 0, 0.12);
  margin: 0;
  .hm-tree-select_nav {
    align-items: center;
  }
  .v-list-item {
    &__action {
      margin: 0;
    }
    &__title {
      i {
        margin-right: 8px;
        position: relative;
        top: -3px;
      }
    }
  }
  .v-list__tile__title {
    white-space: normal;
    height: auto;
    a {
      padding-left: 5px;
    }
    span {
      vertical-align: text-bottom;
    }
  }
  .v-list__tile__action {
    min-width: auto;
    .v-input__slot {
      margin-bottom: 0;
    }
  }
  select {
    display: none;
  }
}

.hm-tree-select_list {
  max-height: 500px;
  overflow-y: auto;
  margin-left: 5px;
  margin-bottom: 5px;
  width: calc(100% - 10px);
}
.hm-tree-select_loader {
  display: block;
  margin: auto;
}
</style>
