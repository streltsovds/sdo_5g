<template>
  <div class="hm-form-element hm-multi-select">
    <label
      class="v-label theme--light"
      v-if="label"
      :class="{ required }"
      v-text="label"
    />
    <p v-if="description" v-text="description" />
    <hm-errors :errors="errors" />
    <v-layout wrap justify-space-between>
      <v-flex class="hm-multi-select_list" xs12 sm5>
        <v-layout class="hm-multi-select_list-header">

          <v-tooltip bottom>
            <template #activator="{ on }">
              <v-btn v-on="on"
                     @click="addAll"
                     icon
                     text
                     color="primary"
              >
                <v-icon>playlist_add</v-icon>
              </v-btn>
            </template>
            <span>Добавить все</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template #activator="{ on }">
              <v-btn
                slot="activator"
                v-on="on"
                @click="addSelected()"
                icon
                text
                color="success"
              >
                <v-icon>playlist_add_check</v-icon>
              </v-btn>
            </template>
            <span>Добавить выбранные</span>
          </v-tooltip>
          <v-text-field
            class="hm-multi-select_search"
            v-model="search"
            append-icon="search"
            label="Поиск"
            single-line
            hide-details
          />
        </v-layout>
        <div class="hm-multi-select_list-body scroll-area">
          <v-progress-circular
            class="hm-multi-select_loader"
            v-if="isLoading"
            indeterminate
            color="primary"
          />
          <ul v-if="treesData.length > 0">
            <div class="hm-multi-select__list-body-start" observer="start"></div>
            <select-item
              v-for="(item, index) in treesData"
              :key="index"
              :model="item"
              @add="add"
              @toggleSelect="toggleSelectNestedSets"
            />
            <div class="hm-multi-select__list-body-end" observer="end"></div>
          </ul>
        </div>
      </v-flex>
      <v-flex class="hm-multi-select__icon"
              hidden-xs-only
              sm1
              text-center
              align-self-center
      >
        <v-icon color="primary">
          swap_horiz
        </v-icon>
      </v-flex>
      <v-flex hidden-sm-and-up
              sm1
              text-center
              align-self-center
      >
        <v-icon color="primary">
          swap_horiz
        </v-icon>
      </v-flex>
      <v-flex class="hm-multi-select_list selected" xs12 sm5>
        <v-layout class="hm-multi-select_list-header">
          <v-tooltip bottom>
            <template #activator="{ on }">
              <v-btn v-on="on"
                     @click="removeAll"
                     icon
                     text
                     color="error"
              >
                <v-icon>delete_sweep</v-icon>
              </v-btn>
            </template>
            <span>Удалить все</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template #activator="{ on }">
              <v-btn
                v-on="on"
                @click="addSelected(true)"
                icon
                text
                color="warning"
              >
                <v-icon>playlist_add_check</v-icon>
              </v-btn>
            </template>
            <span>Удалить выбранные</span>
          </v-tooltip>
          <v-text-field
            class="hm-multi-select_search"
            v-model="searchSelected"
            append-icon="search"
            label="Поиск"
            single-line
            hide-details
          />
        </v-layout>
        <div
          class="hm-multi-select_list-body"
          v-if="treesDataSelected.length > 0"
        >
          <ul>
            <select-item
              v-for="item in treesDataSelected"
              :key="item.id"
              :model="item"
              :is-selected-list="true"
              @add="remove"
              @toggleSelect="toggleSelectNestedSetsSelected"
            />
          </ul>
        </div>
      </v-flex>
    </v-layout>
    <select class="hm-multi-select_hidden-select" :name="name" multiple>
      <option
        v-for="(item, key) in result"
        :key="key"
        :value="item.id"
        selected
      />
    </select>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import SelectItem from "./partials/item";
import HmErrors from "./../hm-errors";
import MixinState from "./../mixins/MixinState";

export default {
  name: "HmMultiSelect",
  components: { SelectItem, HmErrors },
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
    value: {
      type: Array,
      default: () => []
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      data: [],
      nestedSetsSelected: [],
      nestedSets: [],
      search: null,
      searchSelected: null,
      label: this.attribs.label || null,
      description: this.attribs.description || null,
      required: this.attribs.required || false,
      remoteUrl: this.attribs.remoteUrl || null,
      multiOptions: this.attribs.multiOptions || [],
      idName: this.attribs.idName || null,
      formId: this.attribs.formId || null,
      result: [],
      renderItems: [0, 20],
      isLoading: false
    };
  },
  computed: {
    filteredNestedSetsSelected() {
      return this.filteredSets(this.searchSelected, this.nestedSetsSelected);
    },
    filteredNestedSets() {
      return this.filteredSets(this.search, this.nestedSets);
    },

    treesData() {
      let items = this.prepareTreesData(this.filteredNestedSets);
      let [start, end] = this.renderItems;

      return items.slice(start, end);
    },

    treesDataSelected() {
      return this.prepareTreesData(this.filteredNestedSetsSelected);
    }
  },
  watch: {
    nestedSetsSelected: {
      handler: function(v) {
        this.mixinStateUpdate("result", v);
      },
      immediate: true
    },
    treesData(val, oldVal){
      if(oldVal.length === 0 && val.length !== 0){
        this.$nextTick(() => {
          this.useIntersectionObserver()
        })

      }
    },
  },
  created() {
    this.getData();
  },
  methods: {
    useIntersectionObserver(){
      let scrollArea = document.querySelector('.scroll-area');

      var options = {
        root: scrollArea,
        rootMargin: '0px',
        threshold: 1.0
      }

      var callback = ([entry], observer) => {
        if(entry.isIntersecting) {
          let obsType = entry.target.getAttribute('observer');
          console.log(obsType)

          let [start, end] = this.renderItems;

          if(obsType === 'end'){
            end = (end + 20 > this.nestedSets.length) ? this.nestedSets.length : end + 20;
            start = end > 100 ? end - 80: 0;
          }else{
            start = start > 0 ? start - 20: 0;
            start = start < 0 ? 0 : start;

            end = end > 20 ? end - 20 : 20;
          }


          this.renderItems = [start, end];

          this.$nextTick(() => {
            this.$nextTick(() => {
              if(end > 100){
                scrollArea.scrollTop = scrollArea.scrollTop - 20;
              }

            })

          })
        }
      };
      var observer = new IntersectionObserver(callback, options);
      var endEl = scrollArea.querySelector('.hm-multi-select__list-body-end');
      var startEl = scrollArea.querySelector('.hm-multi-select__list-body-start');
      observer.observe(endEl);
      observer.observe(startEl);

    },
    ...mapActions("alerts", ["addErrorAlert"]),
    filteredSets(searchField, Sets) {
      if (!searchField || !searchField.length) return Sets;

      return Sets.filter(
        item => item.name.toLowerCase().indexOf(searchField.toLowerCase()) !== -1
      );
    },

    prepareTreesData(filteredSets) {
      if (!filteredSets) return null;

      return this.makeTree(filteredSets);
    },

    makeTree(arr) {
      let sets = JSON.parse(JSON.stringify(arr)).sort((cur, prev) => {
        const nameCur = cur.name.toLowerCase();
        const namePrev = prev.name.toLowerCase();
        if (nameCur < namePrev) return -1
        else if (nameCur > namePrev)  return 1
        else return 0
      });

      for (let i = 0; i < sets.length; i++) {
        if (!sets[i]) break;
        sets = this.findChildren(sets, sets[i]);
      }

      return sets;
    },

    findChildren(arr, set) {
      let childrenItems = arr.filter(
        item =>
          item.lft > set.lft &&
          item.rgt < set.rgt &&
          item.level - 1 === +set.level
      );

      if (childrenItems.length === 0) {
        return arr;
      }
      childrenItems.forEach(child => {
        arr = arr.filter(item => child.id != item.id);
        arr = this.findChildren(arr, child);
      });
      set.isFolder = true;
      set.children = childrenItems;
      return arr;
    },

    getData() {
      if (!this.remoteUrl) return this.setData(this.multiOptions);
      if (this.isLoading) return;
      this.isLoading = true;

      this.$axios
        .post(this.remoteUrl)
        .then(r => {
          if (r.status !== 200 && r.data) return;
          if (!Array.isArray(r.data)) return;
          this.setData(r.data);
        })
        .catch(() => this.addErrorAlert("Произошла ошибка"))
        .then(() => (this.isLoading = false));
    },

    setData(data) {
      let idName = this.idName ? this.idName : "id";
      data.forEach(item => {
        if (idName !== "id") item.id = item[idName];

        let isSelected =
          this.value.length > 0 ? this.value.includes(+item.id) : item.selected;
        if (isSelected) {
          item.selected = false;
          this.nestedSetsSelected.push(item);
        } else {
          this.nestedSets.push(item);
        }
      });
    },

    addAll() {
      if (this.nestedSets.length === 0) return;

      this.nestedSets.forEach(item => this.transferFromList(item));
    },

    removeAll() {
      if (this.nestedSetsSelected.length === 0) return;

      this.nestedSetsSelected.forEach(item => this.transferFromSelected(item));
    },

    transferFromList(item) {
      this.transfer(item);
    },

    transferFromSelected(item) {
      this.transfer(item, true);
    },

    addSelected(isSelected = false) {
      let fromSets = isSelected ? this.nestedSetsSelected : this.nestedSets;

      fromSets.forEach(item => {
        if (item.selected) {
          if (isSelected) this.remove(item);
          else this.add(item);
        }
      });
    },

    add(item) {
      this.transferFromList(item);
    },

    remove(item) {
      //делать проверку на возможность переноса
      // если есть родители, то перенос запретить!
      let parents = this.nestedSetsSelected.find(
        set => item.lft > set.lft && item.rgt < set.rgt
      );

      if (parents !== undefined) {
        return this.addErrorAlert(
          "Нельзя снимать выделение с элемента, родитель которого остается выделенным"
        );
      }

      this.transferFromSelected(item);
    },
    /**
     * Перемещение элемента с детьми в другой список
     * @param transferItem Object - элемент, который переносим
     * @param fromSelected Boolean - если true, то перенос из списка выбранных элементов (nestedSetsSelected)
     */
    transfer(transferItem, fromSelected = false) {
      let fromSets = fromSelected ? this.nestedSetsSelected : this.nestedSets;

      let selectModelWithChildren = fromSets.filter(
        v => v.lft >= transferItem.lft && v.rgt <= transferItem.rgt
      );
      if (!selectModelWithChildren) {
        console.error("selectModelWithChildren is empty");
        return;
      }

      let whereSets = fromSelected ? this.nestedSets : this.nestedSetsSelected;

      selectModelWithChildren.forEach(item => {
        item.selected = false;
        whereSets.push(item);
      });

      //delete items:
      whereSets = fromSets.filter(
        v => v.lft < transferItem.lft || v.rgt > transferItem.rgt
      );

      if (fromSelected) {
        this.setNestedSetsSelected(whereSets);
      } else {
        this.setNestedSets(whereSets);
      }
    },

    setNestedSetsSelected(sets) {
      this.nestedSetsSelected = sets;
    },

    setNestedSets(sets) {
      this.nestedSets = sets;
    },

    toggleSelectNestedSetsSelected(item) {
      this.toggleSelect(item, this.nestedSetsSelected);
    },

    toggleSelectNestedSets(item) {
      this.toggleSelect(item, this.nestedSets);
    },

    toggleSelect(item, fromSets) {
      let toggleSelectItem = fromSets.find(set => set.id === item.id);

      if (!toggleSelectItem) return;

      toggleSelectItem.selected = !toggleSelectItem.selected;
    }
  }
};
</script>
<style lang="scss">
.hm-multi-select {
  margin-top: 15px;
  margin-bottom: 15px;
  height: 100%;
}
.hm-multi-select__icon {
  display: flex;
  justify-content: center !important;
}
.hm-multi-select_list {
  flex-basis: 50%;
  border: 1px solid rgba(0, 0, 0, 0.12);
}
.hm-multi-select_list-body {
  height: 250px;
  overflow-y: auto;
  ul {
    list-style: none;
    padding-left: 0px;
  }
}
.hm-multi-select_list-header {
  .hm-multi-select_search {
    padding: 3px 15px;
  }
}
.hm-multi-select_list-body {
  position: relative;
}
.hm-multi-select_loader {
  position: absolute;
  left: calc(50% - 16px);
  top: calc(50% - 16px);
}
.hm-multi-select .hm-multi-select_hidden-select {
  display: none;
}
</style>
