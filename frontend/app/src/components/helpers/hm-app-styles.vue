<template>
  <v-style class="hm-app-styles">
    .v-menu__content .v-list-item:not(.v-list-item--active):not(.v-list-item--disabled) {
      color: {{ colorTextContrast }}  !important;
    }
    .v-menu__content .v-list-item:not(.v-list-item--disabled):hover {
      background-color: {{ colorMenuSelectedBackground }} !important;
    }
    .v-menu__content .v-list-item:not(.v-list-item--disabled):hover * {
      color: {{ colorTextContrast }}  !important;
    }
    .v-menu__content .v-list-item:not(.v-list-item--disabled):hover .svg-icon path,
    .edit-actions-button:hover .svg-icon path {
      fill: {{ colorMenuSelectedIcon }}
    }
    .v-menu__content .theme--light.v-list-item--active:hover:before {
      opacity: 0;
    }
    .v-tooltip__content {
      background-color: {{ colorHintBackground }};
      border: 1px solid {{ colorHintBackgroundBorder }};
      box-shadow: 0px 4px 8px rgba(50, 50, 71, 0.06), 0px 4px 4px rgba(50, 50, 71, 0.08);
      border-radius: 2px;
      font-size: 12px;
      line-height: 18px;
      padding: 4px 12px;
    }
    .color-text-contrast {
      color: {{ colorTextContrast }}
    }
    .color-text-default {
      color: {{ colorTextDefault }}
    }
    .color-text-light {
      color: {{ colorTextLight }}
    }

    .hm-user-content a {
      color: {{ colorUserContentLink }}
    }

    .hm-app-content {
      background-color: {{ backgroundColorMain }}
    }
    .v-card {
      background-color: {{ backgroundColorContent }} !important;
      color: {{ colorText }} !important;
      box-shadow: {{ shadow }} !important;
    }
    .v-select-list {
      background-color: {{ colorContextMenu }} !important;
    }
    .v-list,
    .fc-content {
      background-color: {{ backgroundColorContent }} !important;
    }
    .v-menu__content .v-list {
      background-color: {{ colorContextMenu }} !important;
    }
    .v-navigation-drawer__content .v-list {
      background-color: inherit !important;
    }
    .hm-grid-pagination .v-pagination__item:not(.v-pagination__item--active),
    .hm-grid-pagination .v-pagination__navigation:not(.v-pagination__navigation--active) {
      background-color: {{ paginationColor }} !important;
    }
    .v-footer *,
    .v-footer .footer__info__copyright a {
      color: {{ footerTextColor }} !important;
    }
    .footer-support * {
      fill: {{ footerTextColor }} !important;
    }
    .hm-nav-menu .list-nav-menu_active * {
      fill: {{ accentColor }} !important;
    }
    .hm-nav-menu .list-nav-menu_active {
      border-left: 4px solid {{ accentColor }} !important;
    }
    .hm-nav-menu .v-list-item[aria-expanded='true'],
    .hm-nav-menu .no-list:hover > div > svg {
      fill: {{ accentColor }} !important;
    }

  }
  </v-style>
</template>

<script>
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

// Компонент, генерирующий стили для применения палитры цветов из конфига на компоненты vuetify
export default {
  name: "HmAppStyles",
  mixins: [VueMixinConfigColors],
  computed: {
    colorMenuSelectedBackground() {
      return this.themeColors.menuBackgroundSelected;
    },
    colorMenuSelectedIcon() {
      return this.themeColors.accent;
    },
    colorHintBackground() {
      return this.getColor(configColors.hintBackground, "#1e1e1e");
    },
    colorHintBackgroundBorder() {
      return this.getColor(configColors.hintBackgroundBorder, "#111");
    },
    colorTextContrast() {
      return this.getColor(configColors.textContrast, "#000");
    },
    colorTextDefault() {
      return this.getColor(configColors.textDefault, "#222");
    },
    colorTextLight() {
      return this.getColor(configColors.textLight, "#444");
    },
    colorUserContentLink() {
      return this.getColor(configColors.header, "#0000FF");
    },
    backgroundColorMain() {
      return this.themeColors.main;
    },
    backgroundColorContent() {
      return this.themeColors.contentColor;
    },
    colorContextMenu() {
      return this.themeColors.contextMenu;
    },
    colorText() {
      return this.themeColors.text;
    },
    shadow() {
      return this.themeColors.shadow;
    },
    paginationColor() {
      return this.themeColors.paginationColor
    },
    footerTextColor() {
      return this.themeColors.footerText
    },
    accentColor() {
      return this.themeColors.accent
    }
  },
};
</script>
