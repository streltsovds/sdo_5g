<template>
  <tr ref="rowRef" :class="cssClasses" @click="onTrClick">
    <td
      v-if="isSelectable"
      :class="{
        'hm-grid-row__cell-checkbox': true
      }"
    >
      <v-checkbox
        v-model="checkboxValue"
        color="primary"
        hide-details
        @click.stop="handleCheckboxClick"
      />
    </td>
    <template v-for="({ align, text, color }, i) in shownItems">
      <td
        :key="i"
        :class="{
          'text-left': align === 'left',
          'text-right': align === 'right',
          'text-center': align === 'center'
        }"
      >
        <hm-grid-row-expandable
          v-if="checkTotal(text)"
          :items="getChildren(text)"
          :backgroundColor="color"
        />
        <span v-else>
          <hm-dependency
            :template="getHtml(text)"
            class="lessFormatedHtml"
          ></hm-dependency>
        </span>
      </td>
    </template>

    <hm-context-menu-button v-if="hasActions" is-table-cell />
    <hm-context-menu-no-button v-else is-table-cell />

    <v-menu
      v-if="hasActions"
      v-model="menuOpened"
      v-bind="vMenuProps"
      close-on-click
      :activator="$refs.rowRef"
      eager
    >
      <slot></slot>
    </v-menu>
  </tr>
</template>

<script>
import {
  MIN_TABLE_WIDTH,
  MIN_MENU_WIDTH,
  MAX_MENU_WIDTH,
  SELECT_EVENT,
  COLUMN_ACTIONS_NAME
} from "../constants";

import HmContextMenuButton from "@/components/layout/hm-context-menu-button";
import HmContextMenuNoButton from "@/components/layout/hm-context-menu-no-button";
import HmGridRowExpandable from "./HmGridRowExpandable";
import HmDependency from "./../../helpers/hm-dependency";
// import configColors from "@/utilities/configColors";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

const sanitizeHtml = require("sanitize-html");

/**
 * Строка таблицы данных со слотом для контекстного меню
 */
export default {
  components: {
    HmGridRowExpandable,
    HmDependency,
    HmContextMenuButton,
    HmContextMenuNoButton
  },
  mixins: [VueMixinConfigColors],
  props: {
    isSelectable: {
      type: Boolean,
      default: () => false
    },
    selected: Boolean,
    tableWidth: {
      type: Number,
      default: () => MIN_TABLE_WIDTH
    },
    rowProps: {
      type: Object,
      item: {
        type: Object,
        default: () => null
      },
      index: {
        type: Number,
        default: () => 0
      },
      selected: Boolean,
      expanded: Boolean,
      default: () => ({})
    },
    headers: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      expandX: 0,
      menuOpened: false,
    };
  },
  computed: {
    hasActions() {
      return (
        this.items[COLUMN_ACTIONS_NAME] &&
        this.items[COLUMN_ACTIONS_NAME].length > 0
      );
    },
    menuMinWidth() {
      return MIN_MENU_WIDTH > this.tableWidth
        ? this.tableWidth
        : MIN_MENU_WIDTH;
    },
    menuMaxWidth() {
      return MAX_MENU_WIDTH > this.tableWidth
        ? this.tableWidth
        : MAX_MENU_WIDTH;
    },
    vMenuProps() {
      return {
        left: this.flipMenuToLeft,
        minWidth: this.menuMinWidth,
        offsetOverflow: true,
        offsetX: true,
        offsetY: true,
        absolute: true,
        maxWidth: this.menuMaxWidth
      };
    },
    flipMenuToLeft() {
      return this.expandX + MAX_MENU_WIDTH >= this.tableWidth;
    },
    checkboxValue: {
      get() {
        return this.selected;
      },
      set(val) {
        this.$emit(SELECT_EVENT, val);
      }
    },
    cssClasses() {
      const { selected, expanded, item: dataItem } = this.rowProps;
      let result = {
        selected: selected && !expanded,
        expanded__row: expanded,
        "hm-grid-row": true,
        "state-menu-opened": this.menuOpened,
      };

      let hightlightClass = this.getHighlightClass(dataItem.highlighted);
      if (hightlightClass) {
        result[hightlightClass] = true;
      }

      return result;
    },
    shownItems() {
      return this.headers.reduce((acc, header) => {
        // leave only visible items
        if (!header.isHidden) {
          // такого свойства вообще может не оказаться в строке
          const text = this.items[header.value] || "";
          const align = header.align;
          const color = header.color;
          acc.push({ align, text, color });
        }
        return acc;
      }, []);
    },
    items() {
      // proxy to real items array
      return this.rowProps.item;
    },
    sanitizeHtmlConfig() {
      const { defaults } = sanitizeHtml;
      return { ...defaults, allowedTags: [...defaults.allowedTags, "img"] };
    },
  },
  methods: {
    getHighlightClass(hightlighted) {
      if (!hightlighted) {
        return null;
      }

      return "highlighted-" + hightlighted;
    },
    handleCheckboxClick() {
      this.$emit("select", !this.selected);
    },
    onTrClick(event) {
      this.expandX = event.x;

      // always uppercase for DOM elements
      let tagName = event.target.tagName;

      if (tagName === "A") {
        // prevent menu open
        event.stopImmediatePropagation();
      }
    },
    getHtml(noValidHtml) {
      const tempEl = document.createElement("div");
      tempEl.innerHTML = noValidHtml;
      //return sanitizeHtml(tempEl.textContent, this.sanitizeHtmlConfig);
      return tempEl.textContent || "";
    },
    getChildren(html) {
      const tempEl = document.createElement("div");
      tempEl.innerHTML = html;
      tempEl.innerHTML = tempEl.textContent;
      return Array.from(tempEl.children);
    },
    checkTotal(html) {
      const tempEl = document.createElement("div");
      tempEl.innerHTML = html;
      tempEl.innerHTML = tempEl.textContent;
      return tempEl.querySelector(".total");
    }
  }
};
</script>

<style lang="scss">
.hm-grid-row__cell-checkbox {
  padding-left: 24px !important;
  padding-right: 0 !important;

  .v-input--checkbox {
    margin-top: 0;
    max-width: 24px;
    padding-top: 0;
    &__input {
      margin-right: 0;
    }
  }
}
.hm-grid-row {
  .lessFormatedHtml {
    display: flex;
    align-items: center;
    padding-top: 6px;
    padding-bottom: 6px;
    > p:first-child {
      margin-top: 0;
    }
    > *:last-child {
      margin-bottom: 0;
    }
    > p:only-child {
      margin: 0;
    }
    a {
      text-decoration: none;
    }
    .hm-card-link {
      margin-right: 10px;
    }
  }
  .pcard {
    text-decoration: none;
    .v-icon {
      vertical-align: middle;
      line-height: 21px; // it just doesn't mess aligment
    }
    .v-icon--left {
      margin: 0;
    }
  }
  &.expanded__row {
    background-color: #eee !important;
  }
  .selected {
    background-color: #eee !important;
  }
}
</style>
