<template>
  <div class="material-player">
    <v-tooltip v-if="!isLoaded" right :disabled="!tooltip">
      <v-btn
        slot="activator"
        class="material-card_btn"
        fab
        dark
        color="white"
        @click.native.stop.prevent="isLoaded = true"
      >
        <v-icon
          :color="$vuetify.theme.primary"
          class="icon-play"
          v-text="type == 'audio' ? 'audiotrack' : 'play_circle_outline'"
        />
      </v-btn>
      <span>{{ tooltip }}</span>
    </v-tooltip>

    <transition name="fade">
      <audio v-if="isLoaded && type === 'audio'" :src="url" controls autoplay />
      <video v-if="isLoaded && type === 'video'" :src="url" controls autoplay />
      <flash
        v-if="isLoaded && type === 'flash'"
        :url="url"
        :prop-height="100"
        class="material-resource material-flash"
        classid="material-flash"
      />
    </transition>
  </div>
</template>
<script>
import flash from "./../view/flash";
export default {
  components: { flash },
  props: {
    type: {
      type: String,
      required: true
    },
    url: {
      type: String,
      required: true
    },
    tooltip: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      isLoaded: false,
      content: null
    };
  }
};
</script>
<style lang="scss">
.material-player {
  display: flex;
  flex: 1 0 100%;
  justify-content: center;
  align-content: center;

  video,
  .material-flash {
    max-width: 100%;
    width: 100%;
    height: 100px;
  }
  audio {
    width: 100%;
  }
}
</style>
