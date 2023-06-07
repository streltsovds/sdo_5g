<template>
  <div class="hm-autocomplete__wrapper">
    <v-style-head-once name="style-hm-autocomplete">
      .hm-autocomplete .v-select__selections > .v-chip {
          border: 1px solid {{ colorTagBorder }};
        color: {{ colorTagText }};
      }
      .hm-autocomplete .v-select__selections > .v-chip .v-icon {
        color: {{ colorTagButtonClose }};
      }
      .hm-autocomplete .v-select__selections > .v-chip .v-icon:hover {
        color: {{ colorTagButtonCloseHover }};
      }
    </v-style-head-once>

    <v-autocomplete
      :class="addCssClassesPrefixed(
        'hm-autocomplete',
        {
          '--single': maxItems === 1,
        }
      )"
      :value="selected"
      @input="updateSelected"
      :search-input="search"
      @update:search-input="onSearchUpdate"
      v-bind="autocompleteProps"
      @keydown.enter="onKeyEnter"
      :menu-props="{closeOnContentClick: maxItems === 1 ? true : false}"
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
          v-if="!isAlreadySelected && allowNewItems"
          @click="addNewItem"
        >
          <v-btn class="hm-autocomplete__button-add-item ma-0 hm-grid__add-new-btn"
                 v-if="ruleNewItemMaxLengthResult === true"
                 text
                 small
                 color="primary"
          >
            Добавить "{{ search }}"?
          </v-btn>
          <v-chip class="hm-autocomplete__list-item-add-item__error"
                  v-else
                  color="error"
                  outlined
                  small
          >
            {{ ruleNewItemMaxLengthResult }}
          </v-chip>
        </v-list-item>
      </template>
    </v-autocomplete>
  </div>
</template>

<script>
import { decline } from "@/utilities";
import Vue from "vue";

import _map from "lodash/map"
import _parseInt from "lodash/parseInt"
import colorAddAlpha from "color-alpha"
import debounce from "lodash/debounce"
import difference from "lodash/difference"
import filter from "lodash/filter"
import isEmpty from "lodash/isEmpty"
import isNil from "lodash/isNil"
import keys from "lodash/keys"
import last from "lodash/last"
import lodashFind from "lodash/find"
import partition from "lodash/partition"
import pickBy from "lodash/pickBy"
import trimEnd from "lodash/trimEnd"
import trim from "lodash/trim"
import trimStart from "lodash/trimStart"
import uniq from "lodash/uniq"

const Color = require('color')

import {
  INVALID_EVENT,
  SELECTED_EVENT
} from "./const"
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import VStyleHeadOnce from "@/components/helpers/v-style-head-once";
import addCssClassesPrefixed from "@/utilities/addCssClassesPrefixed";
import configColors from "@/utilities/configColors";

const LOAD_AUTOCOMPLETE_ITEMS_DEBOUNCE_MS = 300;

export default {
  name: "HmAutocomplete",
  components: {
    VStyleHeadOnce,
  },
  mixins: [VueMixinConfigColors],
  props: {
    isTags: {
      type: Boolean,
      default: false,
    },
    allowNewItems: {
      type: Boolean,
      default: true,
    },
    autocompleteUrl: {
      type: String,
      default: null,
    },
    color: {
      type: String,
      default: null,
    },
    description: {
      type: String,
      default: null,
    },
    disabled: {
      type: Boolean,
      default: false,
    },

    errors: {
      type: Object,
      default: () => {}
    },

    fullPreload: {
      type: Boolean,
      default: true,
    },

    itemText: {
      type: String,
      default: "text",
    },

    /** Поле, содержащее id */
    itemValue: {
      type: String,
      default: "value",
    },

    maxItems: {
      type: Number,
      default: 10
    },

    newItemMaxLength: {
      type: Number,
      default: 255,
    },

    label: {
      type: String,
      default: '',
    },

    /**
     * Показывать ли отдельно сверху списка вариантов
     * удалённые в процессе изменения поля формы значения
     **/
    separateMissingItems: {
      type: Boolean,
      default: true,
    },

    /**
     * Показывать id как "#1234: " вначале вариантов
     */
    showIdPrefix: {
      type: Boolean,
      default: false,
    },

    /**
     * На данный момент Array из текстовых значений не поддерживается!
     * Ключи должны быть правильные и содержащие id
     *
     * {[id] => text}
     */
    value: {
      type: Object,
      default: () => {}
    },
  },
  data() {
    let valueInitial = {...this.value};

    let selectedInitial = _map(keys(valueInitial), _parseInt);

    // copy:
    let selected = [].concat(selectedInitial)

    return {
      disabledNow: false,
      errorsData: this.errors,
      items: [],
      newItems: [],
      search: '',
      selected,
      selectedInitial,
      isLoading: false,
      rules: [this.ruleItemsCount, this.ruleNewItemMaxLength],
      valueInitial,
    };
  },
  computed: {
    autocompleteProps() {
      return {
        chips: this.maxItems > 1,
        // class: "hm-autocomplete__real",
        counter: this.maxItems > 1 && this.selected.length > 0,
        dense: true,
        deletableChips: this.maxItems > 1,

        /**
         * Без этого флага возникает ошибка
         * VMenu: Error in render: "TypeError: Cannot read property 'height' of null,
         *
         * если первым действием удалить тэг, а потом открыть меню
         *
         * https://github.com/vuetifyjs/vuetify/issues/10606
         *
         **/
        eager: true,

        error: this.errorsExist,
        errorMessages: this.errorsArray,
        disabled: this.disabled || this.disabledNow,

        // hideSelected: this.maxItems > 1,
        hideSelected: true,
        hint: this.hint,
        items: this.itemsShown,
        itemText: this.itemText,
        itemValue: this.itemValue,
        label: this.label,
        loading: this.isLoading,
        // maxlength: this.newItemMaxLength,
        // maxlength: this.maxItems,
        menuProps: {
          contentClass: 'hm-autocomplete__menu',
          /** TODO Отключено, при некоторой высоте и положениях прокрутки перекрывает поле ввода */
          //   maxHeight: Math.min(window.innerHeight - 100, 650),
        },

        multiple: true,
        // multiple: this.maxItems > 1,

        outline: true,
        persistentHint: true,
        rules: this.rules,
        singleLine: true,

        /** value: this.selected, выше в шаблоне */
      };
    },
    colorTagText() {
      return this.color || this.getColor(configColors.textLight, "#333");
    },
    colorTagBorder() {
      /** делаем обводку "тоньше" добавлением прозрачности */
      return colorAddAlpha(this.colorTagText, 0.5);
    },
    colorTagButtonClose() {
      return this.color || this.colors.grayBase || "#333";
    },
    colorTagButtonCloseHover() {
      return colorAddAlpha(this.themeColors.error || "#F00", 0.8);
    },
    debug_ItemsByValue() {

      let { items, itemText, itemValue } = this;

      let result = {};
      for (let item of items) {
        let key = item[itemText]
        let value = item[itemValue]
        result[value] = key;
      }
      return result;
    },
    /**
     * Если подсказки нет, то она равна placeholder (label),
     * когда label пропадает из-за введённого значения
     **/
    hint() {
      let { selected, label, description } = this;

      return filter([
        (isEmpty(selected) ? null : label),
        description,
      ]).join(': ');
    },
    allItemsSorted() {
      let {
        items,
        itemText,
        itemValue,
        newItems,
        selectedIdsMissing,
        separateMissingItems,
        showIdPrefix,
      } = this

      let allItems = items.concat(newItems);

      allItems = allItems.map((item ) => {
        const text = item[itemText];
        const value = item[itemValue];

        return {
          ...item,
          textToCompareOrder: text,
          [itemText]: showIdPrefix
            ? `#${value}: ${text}`
            : text
        }
      })

      allItems.sort((a, b) => {
        return this.strcmp(a.textToCompareOrder, b.textToCompareOrder);
      });

      if (separateMissingItems) {
        let [itemsBegin, itemsRest] = partition(allItems, item => selectedIdsMissing.includes(item[itemValue]) );

        if (!isEmpty(itemsBegin)) {
          let separatorListItem = {divider: true}
          allItems = [...itemsBegin, separatorListItem, ...itemsRest];
        }
      }

      return allItems;
    },
    errorsExist() {
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key)) return true;
      }
      return false;
    },
    errorsArray() {
      let rules = [];
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key))
          rules.push(this.errorsData[key]);
      }
      return rules;
    },
    itemsShown() {
      return this.allItemsSorted;
    },
    isItemsCountAllowed() {
      return this.selected.length <= this.maxItems;
    },
    newItemValue() {
      const { newItems, itemValue } = this;

      let minValue = -1000;

      for (let item of newItems) {
        let value = item[itemValue];
        minValue = Math.min(value, minValue);
      }

      return minValue - 1;
    },
    isAlreadySelected() {

      const { itemText } = this;

      return this.selected
        .map(selectedValue => {
          const newItemIndex = this.newItems.findIndex(
            ({ value }) => value === selectedValue
          );
          const itemIndex = this.items.findIndex(
            ({ value }) => value === selectedValue
          );
          if (newItemIndex !== -1) {
            return this.newItems[newItemIndex][itemText];
          }
          if (itemIndex !== -1) {
            return this.items[itemIndex][itemText];
          }
        })
        .includes(this.search);
    },
    loadAutocompleteItemsDebounced() {
      return debounce(
        (searchFieldValue, setDisabled) => this._loadAutocompleteItems(searchFieldValue, setDisabled),
        LOAD_AUTOCOMPLETE_ITEMS_DEBOUNCE_MS,
        // { leading: true },
      );
    },
    ruleNewItemMaxLengthResult() {
      return this.ruleNewItemMaxLength();
    },
    selectedIdsMissing() {
      return difference(this.selectedInitial, this.selected)
    },
  },
  watch: {
    isUnderMaxLength(val) {
      this.$emit(INVALID_EVENT, !val);
    },
  },
  mounted() {
    this.itemsAddInitialValue();

    if (this.fullPreload) {
      this._loadAutocompleteItems();
    }
  },
  methods: {
    addCssClassesPrefixed,
    itemsAddInitialValue() {
      let { itemValue, itemText, items } = this;

      let initialItems = [];

      _map(this.valueInitial || {}, (text, val) => {
        initialItems.push({
          [itemValue]: _parseInt(val),
          [itemText]: text,
        });
      });

      this.items = [...items, ...initialItems];
    },
    async _loadAutocompleteItems(searchFieldValue, setDisabled = true) {
      let { itemValue } = this;

      this.isLoading = true;
      if (setDisabled) {
        this.disabledNow = true;
      }

      let requestParams = pickBy({
        tag: searchFieldValue
      }, v => !isNil(v));

      let response = await this.$axios.get(
        this.autocompleteUrl,
        { params: requestParams }
      );
      let data = response.data;

      /**
       * с mysql value приходит в виде строки, а не числа
       * http://projects.hypermethod.com:8080/redmine/issues/31203#note-37
       **/
      let dataFixed = data.map(item => {
        if(this.isTags) {
          return {
            text: item,
            value: item
          }
        } else return {...item, [itemValue]: parseInt(item[itemValue])};
      })

      this.items = dataFixed;
      this.isLoading = false;
      this.disabledNow = false;
    },
    strcmp(a, b) {
      a = (a + "").toLowerCase();
      b = (b + "").toLowerCase();
      return (a < b ? -1 : (a > b ? 1 : 0));
    },

    findNewItemByText(searchText) {
      return lodashFind(this.newItems,
        {[this.itemText]: searchText}
      );
    },

    findItemByText(searchText) {
      return lodashFind(this.items,
        {[this.itemText]: searchText}
      );
    },

    findAnyItemByText(searchText) {
      return this.findNewItemByText(searchText) || this.findItemByText(searchText)
    },

    findNewItemByValue(searchValue) {
      return lodashFind(this.newItems,
        {[this.itemValue]: searchValue}
      );
    },

    findItemByValue(searchValue) {
      return lodashFind(this.items,
        {[this.itemValue]: searchValue}
      );
    },

    findAnyItemByValue(searchValue) {
      return this.findNewItemByValue(searchValue) || this.findItemByValue(searchValue)
    },

    /**
     * Преобразуем значение для HmGridFooter.selectedSubMassActionValue:
     **/
    getSelectedValue(val) {
      const { itemText, itemValue } = this;

      let selected = {};

      val.map(selectedValue => {
        let foundItem = this.findAnyItemByValue(selectedValue);

        if (foundItem) {
          selected[foundItem[itemValue]] = foundItem[itemText];
        } else {
          console.error('hm-autocomplete: "' + foundItem + '" not found in items')
        }
      });
      return selected;
    },
    _stopEvent(event) {
      event.stopPropagation();
      event.preventDefault();
    },
    addNewItem(event) {
      const {
        allowNewItems,
        isAlreadySelected,
        itemText,
        itemValue,
        ruleNewItemMaxLengthResult,
        search,
      } = this;

      if (ruleNewItemMaxLengthResult !== true) {
        return
      }

      if (isAlreadySelected || !allowNewItems) {
        return;
      }

      const searchTrimmed = trim(trimEnd(search, ","));

      if (!searchTrimmed) {
        return;
      }

      const newItem = {
        [itemText]: searchTrimmed,
        [itemValue]: this.newItemValue
      };
      this.search = '';

      if (event) {
        /**
         * Обязательно вызывать, иначе стандартный обработчик Vuetify выполняет клик
         *   и добавляет случайный для нас элемент
         *
         * в `VSelect` вызывается `selectItem()`
         * - Приходит из `VSelectList.genTile()`: `click`
         * - Приходит из `VListItem.methods.click()`: `this.$emit('click', e)`
         */
        this._stopEvent(event);
      }

      let existingItem = this.findAnyItemByText(newItem[itemText])

      let newSelectedValue = newItem[itemValue];

      if (existingItem) {
        newSelectedValue = existingItem[itemValue];
      } else {
        this.newItems.push(newItem);
      }

      this.updateSelected([...this.selected, newSelectedValue]);
    },
    ruleItemsCount() {
      const { isItemsCountAllowed, maxItems } = this;
      if (isItemsCountAllowed) {
        return true;
      }

      const endings = ["го", "х", "ти"];
      const ending = decline(maxItems, endings);
      return `Не более ${maxItems}-${ending}`;
    },
    /**
     * Пришлось создать, потому что стандартный maxlength странно считал длину:
     * складывал число элементов и их количество. Может быть в новых версиях будет исправлено.
     **/
    ruleNewItemMaxLength() {
      const { newItemMaxLength, search } = this;

      if (search.length <= newItemMaxLength) {
        return true;
      }

      return `Не длиннее ${newItemMaxLength} символов`;
    },
    onKeyEnter(event) {
      console.log('hm-autocomplete: onKeyEnter ' + event.type);
      this.addNewItem(event);

      this._stopEvent(event);
    },
    // onKeyDown(event) {
    //   console.log('hm-autocomplete: onKeyDown', event);
    // },
    onSearchUpdate(newSearch) {
      this.search = trimStart(newSearch)

      if (last(this.search) == ',') {
        this.addNewItem();
        return;
      }

      if (!this.fullPreload && this.search.length > 0) {
        this.loadAutocompleteItemsDebounced(newSearch, false)
      }
    },
    updateSelected(newSelected) {
      if (this.maxItems === 1 && newSelected.length > 1) {
        newSelected = [last(newSelected)];
      }

      this.selected = newSelected;

      this.search = '';

      const valueToEmit = this.getSelectedValue(newSelected);
      this.$emit(SELECTED_EVENT, valueToEmit);
    },
  }
};
</script>

<style lang="sass" src="./style.sass" />
