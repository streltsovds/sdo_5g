<template>
  <v-chip
    label
    class="hm-role-switcher ma-0 accent darken-2"
    style="
      border-left: none;
      margin-left: 18px !important;
      border-radius: 17px !important;
      overflow: inherit !important;
      cursor: pointer;
    "
    :style="{
         backgroundColor: colorUser+'!important',
         width: getWidth()
    }"
  >
    <hm-sidebar-toggle
      :sidebar-name="sidebarName"
      :title="userName"
      class="accent darken-2"
      :class="Object.keys(availableRolesLinks).length === 0 ? 'none-user-role-links' : ''"
      style="
        margin: 0;
      "
      :style="{
        backgroundColor: colorAvatar+'!important',
        marginLeft: getMargin()
      }"
    >
      <v-avatar v-if="avatar" style="margin: 0;">
        <v-img :src="avatar" :aspect-ratio="1"></v-img>
      </v-avatar>
      <span class="initials" v-else> {{ userInitials }} </span>
    </hm-sidebar-toggle>
    <v-menu
      class="hm-role-switcher-menu"
      v-if="Object.keys(availableRolesLinks).length > 0"
      :title="_('Переключить роль')"
      v-model="menuOpened"
      bottom
      offset-y
      transition="slide-y-transition"
    >

      <template
        v-if="Object.keys(availableRolesLinks).length > 0"
        v-slot:activator="{ on: onMenu }"
      >

        <v-tooltip
          class="hm-role-switcher-menu__activator__tooltip ma-0"
          :value="tooltipShownCurrent"
          bottom
        >
            {{ _('Переключить роль') }}

            <template v-slot:activator="{ on: tooltip }">
              <v-hover v-model="tooptipShown" :open-delay="V_TOOLTIP_OPEN_DELAY">
                <div
                  class="hm-role-switcher-menu__activator"
                  v-on="{ ...onMenu, ...tooltip}"
                  style="line-height: 44px;
                        padding-left: 9px;
                        padding-right: 20px;
                        font-size: 14px;
                        padding-top: 4px;"
                  :class="Object.keys(availableRolesLinks).length > 0 ? 'text-in-role' : 'text-in-role-nolinks'"
                >
                  {{ currentRoleName }}
                </div>
              </v-hover>
            </template>
        </v-tooltip>
      </template>
      <!-- <template
        v-if="Object.keys(availableRolesLinks).length > 0"
        v-slot:activator="{ on: onMenu }"
      >
        <v-hover v-model="tooptipShown" :open-delay="V_TOOLTIP_OPEN_DELAY">
          <div
            class="hm-role-switcher-menu__activator"
            v-on="{ ...onMenu}"
            style="line-height: 44px;
                   padding-left: 9px;
                   padding-right: 20px;
                   font-size: 14px;
                   padding-top: 4px;"
            :class="Object.keys(availableRolesLinks).length > 0 ? 'text-in-role' : 'text-in-role-nolinks'"
          >
            {{ currentRoleName }}
          </div>
        </v-hover>

        <v-tooltip
          class="hm-role-switcher-menu__activator__tooltip ma-0"
          :value="tooltipShownCurrent"
          attach
          content-class="hm-role-switcher-menu__activator__tooltip__content"
          bottom
          nudge-bottom="28"
        >
            {{ _('Переключить роль') }}
        </v-tooltip>
      </template> -->
      <v-list
        v-if="Object.keys(availableRolesLinks).length > 0"
        :color="themeColors.contextMenu"
        class="hm-role-switcher-menu-list " dense>
        <v-list-item
          v-for="(roleLink, roleName) in availableRolesLinks"
          :key="roleName"
          :href="roleLink"
          class="hm-role-switcher-menu-list__link"
          :style="{marginTop: roleName.toLowerCase() === 'пользователь' ? '4px' : ''}"
        >
            <v-list-item-title v-if="roleName.toLowerCase() !== 'пользователь'">
              {{ roleName }}
            </v-list-item-title>

          <v-tooltip bottom>
            <template v-slot:activator="{on: oneone}">
              <v-list-item-title
                v-on="oneone"
                class="list-user"
                style="margin-top: 5px; display: flex; align-items: flex-start;"
                v-if="roleName.toLowerCase() === 'пользователь'">
                <svg-icon name="user"  style="margin:0px 6px 0 0"> </svg-icon>
                <span>{{ roleName }}</span>

              </v-list-item-title>
            </template>
            <span>{{ _('Войти от имени обычного пользователя') }}</span>
          </v-tooltip>

        </v-list-item>
      </v-list>
    </v-menu>
  </v-chip>
</template>

<script>
import hmSidebarToggle from "./hm-sidebar-toggle";
import svgIcon from "../icons/svgIcon";
import { V_TOOLTIP_OPEN_DELAY } from "@/libs/monkeyPatch/vuetifyComponents.ts";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import configColors from "@/utilities/configColors";

export default {
  inheritAttrs: false,
  components: {
    hmSidebarToggle,
    svgIcon,
  },
  mixins: [
    VueMixinConfigColors,
  ],
  props: {
    userStore : {
        type: String,
        default: "__userNameStore__"
    },
    sidebarName: {
      type: String,
      default: "userHome",
    },
    userName: {
      type: String,
      default: "__userName__",
    },
    avatar: {
      type: String,
      default: null,
    },
    userRestore: {
        type: String,
        default: null
    },
    currentRoleName: {
      type: String,
      default: "__currentRoleName__",
    },
    availableRolesLinks: {
      type: Object,
      default: function() {
        // [roleName => link]
        return {};
      },
    },
    color: {
      type: String,
      default: '#FF8364'
    }
  },
  data() {
    return {
      colorUser: '',
      colorAvatar: '',
      menuOpened: false,
      tooptipShown: false,
      V_TOOLTIP_OPEN_DELAY,
    }
  },
  computed: {
    tooltipShownCurrent() {
      return this.tooptipShown && !this.menuOpened;
    },
    userInitials() {
      return this.userName.split(' ').map(el=> el[0]).join('')
    }
  },
  methods: {
    getMargin() {
      if (this.availableRolesLinks.length < 1 || Object.values(this.availableRolesLinks).length < 1) return '0 !important'
      else return '-16px !important'
    },
    getWidth() {
      if(Object.keys(this.availableRolesLinks).length === 0 || this.availableRolesLinks.length === 0) {
        if(this.$vuetify.breakpoint.xs) return '40px'
        else return '42px'
      } else {
        return ''
      }
    },
    showProfileSidebar() {
      this.$store.dispatch("sidebars/changeSidebarState", {
        name: this.sidebarName,
        options: { opened: true },
      });
    },
    tooltipShownChange(newValue) {
      this.tooptipShown = newValue;
    },
    /**
     * Инициализируем пользователя, при загрузке и выборе роли, для разного отображения
     * :TODO можно написать короче, енсли не нужно проверять зашел ли пользователь в другую роль под собой
     */
    initStoreUser() {
      if(this.userStore !== '') {
        if(this.userStore.toLowerCase() === this.userName.toLowerCase()) {
          this.colorUser = '#FF8364';
          this.colorAvatar = this.getColor('primaryDark', '#444');
        } else {
          this.colorUser = '#FF8364';
          this.colorAvatar = '#E57373';
        }
      } else {
        this.colorUser = this.themeColors.accent;
        this.colorAvatar = this.themeColors.accent;
      }
    }
},
  mounted() {
    // console.log(this.availableRolesLinks)
    this.initStoreUser()
  },
};
</script>
<style lang="scss">
.hm-role-switcher.v-chip {
  .v-chip__content {
    padding: 0;

    .hm-sidebar-toggle {
      height: 42px;
      width: 42px;
    }
  }
}

.hm-role-switcher-menu-list.v-list {
  max-width: 300px;

  .v-list__tile__title {
    white-space: nowrap;
    text-overflow: ellipsis;
  }

}
.text-in-role:after {
  content: "";
  position: relative;
  left: 7px;
  top: -2px;
  border: 5px solid transparent;
  border-top: 5px solid #F5F5F5;
  font-size: 0;
}

.initials {
  font-weight: normal;
  font-size: 18px;
  line-height: 21px;
  letter-spacing: 0.02em;
  color: #FFFFFF;
}

.v-btn__content {
  .v-avatar {
    width: 100% !important;
    height: 100% !important;
  }
}


.list-user:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 1px;
  background: rgba(112, 136, 158, 0.3) !important;
}
.hm-role-switcher-menu-list__link {
  color: #1E1E1E !important;
}

.hm-role-switcher-menu-list__link:hover {
  background: rgba(212, 227, 251, 0.3) !important;
  > div {
    color: #44556B !important;
  }
}

.none-user-role-links {
  position: absolute;
  left: 0;
  padding: 0;
  margin: 0 !important;
}

.hm-role-switcher-menu__activator__tooltip__content {
  /** nudge-right почему-то не работает */
  left: 40px !important;
}
@media(max-width: 440px) {
  .hm-role-switcher-menu__activator {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>
