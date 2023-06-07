<template>
  <div class="hm-form-element hm-tags">
    <v-autocomplete
      class="hm-autocomplete"
      v-model="values"
      :items="items"
      @input="onSelected"
      :hint="attribs.description"
      :label="attribs.label"
      persistent-hint
      hide-selected
      multiple
      chips
      deletableChips
      :item-text="attribs.itemText"
      item-value="value"
      :return-object="attribs.itemText ? true : false"
      :search-input="search"
      @update:search-input="onSearchUpdate"
      @keydown.enter="onKeyEnter"
      @update:list-index="updateListIndex"
      :loading="isLoading"
      :counter="attribs.limit ? attribs.limit : false"
    >
      <template slot="no-data">
        <v-list-item v-if="isAlreadySelected">
          "{{ search }}" уже выбрано.
        </v-list-item>
        <v-list-item v-else-if="search.length === 0">
          Введите слова для поиска
        </v-list-item>
        <v-list-item v-else>
          По запросу "{{ search }}" ничего не найдено.
        </v-list-item>
        <v-list-item
          class="hm-autocomplete__list-item-add-item"
          @click="addNewItem"
          v-if="allowNewItems && !isAlreadySelected && search.length > 0"
        >
          <v-btn class="hm-autocomplete__button-add-item ma-0 hm-grid__add-new-btn"
                 text
                 small
                 color="primary"
          >
            Добавить "{{ search }}"?
          </v-btn>
        </v-list-item>
      </template>
    </v-autocomplete>

   <div v-for="resultTagText in values" class="hm-tags_result" :key="resultTagText">
      <input v-if="!attribs.itemText" type="hidden" :name="name" :value="resultTagText" />
      <input v-else type="hidden" :name="name" :value="resultTagText.value" />
   </div>

  </div>
</template>
<script>
import MixinState from "./../mixins/MixinState";
import debounce from "lodash/debounce"

const LOAD_AUTOCOMPLETE_ITEMS_DEBOUNCE_MS = 300;

export default {
  name: "HmTags",
  mixins: [MixinState],
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: Array,
      default: () => []
    },
    attribs: {
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
      values: this.value || [],
      items: this.value,
      isLoading: false,
      search: "",
      listIndex: -1,
      allowNewItems: this.attribs.allowNewItems === undefined ? true : this.attribs.allowNewItems
    };
  },
  created() {
    this.updateValues(this.value);
  },
  computed: {
    isAlreadySelected() {
      return this.values.includes(this.search)
    },
    loadAutocompleteItemsDebounced() {
      return debounce(
        (searchFieldValue) => this.changeSearch(searchFieldValue),
        LOAD_AUTOCOMPLETE_ITEMS_DEBOUNCE_MS,
      );
    },
  },
  methods: {
    updateListIndex(val) {
      this.listIndex = val
    },
    onSelected(selectedValue) {
      if(this.attribs.limit && selectedValue.length >= this.attribs.limit) {
        const values = selectedValue.slice(0, this.attribs.limit);
        this.updateValues(values);
      } else if(this.attribs.maxitems) {
        const values = selectedValue.slice(0, this.attribs.maxitems);
        this.updateValues(values);
      }
      else this.updateValues(selectedValue);
    },
    addNewItem(event) {
      if (event) this._stopEvent(event);

      if(this.attribs.limit && this.values.length >= this.attribs.limit) return;
      else {
        this.values.push(this.search);
        this.items.push(this.search);
        this.search = '';
      }
    },
    onKeyEnter(event) {
      if(this.listIndex < 0) this.addNewItem(event);

      this._stopEvent(event);
    },
    _stopEvent(event) {
      event.stopPropagation();
      event.preventDefault();
    },
    onSearchUpdate(val) {
      if(!val) return

      this.search = val;

      if(this.search.length < 3) return

      this.loadAutocompleteItemsDebounced(val)
    },
    changeSearch(val) {
      this.isLoading = true;
      const contentInput = JSON.stringify({tag: val});
      fetch(this.attribs.json_url, {
        method: 'POST',
        headers: {
          'X_REQUESTED_WITH': 'XMLHttpRequest',
        },
        body: contentInput
      })
        .then(res => res.json())
        .then(data => {
          this.items = [...Object.values(data), ...this.values];
          this.isLoading = false;
        })
        .catch(err => console.error(err))
    },
    updateValues(newValues) {
      this.search = '';
      this.mixinStateUpdate("result", newValues);
      this.values = newValues;
    },
  }
};
</script>
<style lang="sass" src="./style.sass" />
