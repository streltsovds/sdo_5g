<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on }">
      <v-btn v-on="on" icon :background-color="color" @click="logoutUser">
        <!--      <v-icon>exit_to_app</v-icon>-->
  <!--      <icon-exit :color="color"></icon-exit>-->

        <svg-icon
          name="exit"
          :color="color"
        >
        </svg-icon>

      </v-btn>
    </template>
    <span>Выйти</span>
  </v-tooltip>
</template>
<script>
import { mapActions } from "vuex";
import svgIcon from "@/components/icons/svgIcon";

export default {
  components: {
    svgIcon
  },
  props: {
    backgroundColor: {
      type: String,
      default: "primary"
    },
    color: {
      type: String,
      default: "#fff"
    }
  },
  methods: {
    ...mapActions("user", ["logoutUser"])
  },
    mounted() {
    }
};
</script>
