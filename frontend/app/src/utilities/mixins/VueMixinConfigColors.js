import VStyle from "../../components/helpers/v-style";

/**
 * Добавьте mixins: [VueMixinConfigColors] к вашему компоненту, чтобы использовать цвета из design.ini
 *
 * См. на примере HmGrid.vue
 */
export default {
  components: {
    VStyle,
  },
  computed: {
    /**
     * Для <v-style>, если нужно сгенерировать css динамически,
     * чтобы задать ненастраиваемые цвета внутри компонента vuetify
     **/
    uid() {
      return "vue" + this._uid;
    },

    /** design.ini: designSettings.themeColors */
    themeColors() {
      return this.$vuetify.theme.currentTheme;
    },

    /** design.ini: designSettings.colors */
    darkTheme() {
      return this.$vuetify.theme.dark;
    },
    colors() {
      return this.$store.getters.configColors;
    },
  },
  methods: {
    getColor(colorName, defaultColor = "#000000") {
      return (this.$store && this.$store.getters.configColor(colorName)) || defaultColor;
    },
  },
};
