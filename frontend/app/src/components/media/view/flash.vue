<template>
  <v-layout
    ref="layout"
    class="material-flash"
    row
    align-center
    justify-center
    fill-height
  >
    <div class="material-flash_restart">
      <v-btn icon text color="info" @click.native.prevent.stop="restart">
        <v-icon>refresh</v-icon>
      </v-btn>
    </div>
    <v-flex>
      <object v-if="isLoaded" :classid="classid" :height="height">
        <param :value="url" name="movie" />
        <!-- [if !IE]> -->
        <object
          :data="url"
          :width="propWidth"
          :height="height"
          type="application/x-shockwave-flash"
        >
          <!-- <![endif] -->
          <p class="material-flash_getflashplayer">
            Для просмотра необходимо установить Adobe Flash Player
            <a href="http://www.adobe.com/go/getflashplayer">
              версии 10 и выше
            </a>
          </p>
          <!-- [if !IE]> -->
        </object>
        <!-- <![endif] -->
      </object>
    </v-flex>
  </v-layout>
</template>
<script>
export default {
  props: {
    url: {
      type: String,
      default: null
    },
    classid: {
      type: String,
      required: true
    },
    propWidth: {
      type: String,
      default: "100%"
    },
    propHeight: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      height: this.propHeight,
      isLoaded: true
    };
  },
  mounted() {
    this.height = this.$refs.layout.offsetHeight;
  },
  methods: {
    restart() {
      this.isLoaded = false;
      setTimeout(() => (this.isLoaded = true), 100);
    }
  }
};
</script>
<style lang="scss">
.material-flash {
  background: #ccc;
  position: relative;
  justify-content: flex-start;
  & > .flex {
    max-height: 100%;
  }
  .material-flash_getflashplayer {
    text-align: center;
  }
}
.material-flash_restart {
  position: absolute;
  left: 0;
  top: 0;
}
</style>
