<template>
  <div class="hm-rubricator__menu-wrapper">
    <v-menu
      v-if="autoWrap && $vuetify.breakpoint.smAndDown"
      v-model="menu"
      :close-on-content-click="false"
      offset-y
      content-class="hm-rubricator__menu"
    >
      <div slot="activator" slot-scope="{ on }">
        <v-text-field
          class="hm-rubricator__menu-text"
          :label="label"
          :append-icon="menu ? 'arrow_drop_up' : 'arrow_drop_down'"
          :value="activeObject ? activeObject.title : ''"
          v-on="on"
        ></v-text-field>
      </div>
      <v-card>
        <v-card-text>
          <slot />
        </v-card-text>
      </v-card>
    </v-menu>

    <div v-else>
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  name: "HmRubricatorMenuWrapper",
  props: {
    activeObject: {
      type: Object,
      default: null,
    },
    autoWrap: {
      type: Boolean,
      default: true,
    }
  },
  data() {
    return {
      menu: false,
    }
  }
};
</script>
