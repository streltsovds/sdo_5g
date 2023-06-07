<template>
  <div>
    <v-snackbar v-model="snackbar" auto-height>
      {{ text }}
      <v-btn color="red" text @click="snackbar = false"> Закрыть </v-btn>
    </v-snackbar>
    <v-btn v-if="inDevelopment" disabled>
      <span class="white--text text-capitalize">
        Lowest FPS: {{ lowest_fps }}
      </span>
    </v-btn>
    <v-btn v-if="inDevelopment" disabled>
      <span class="white--text text-capitalize">
        Current FPS: {{ registered_fps }}
      </span>
    </v-btn>
  </div>
</template>

<script>
export default {
  data() {
    return {
      registered_fps: null,
      lowest_fps: 1000,
      fps_checks: 0,
      bad_fps_checks: 0,
      check_animations_capability: false,
      text: `Анимации интерфейса отключены из-за низкой производительности Вашего устройства.`,
      snackbar: false
    };
  },
  computed: {
    inDevelopment() {
      return this.$root.inDevelopment;
    },
    percentOfBadChecks() {
      return Math.round((this.bad_fps_checks / this.fps_checks) * 100);
    }
  },
  watch: {
    registered_fps(val) {
      let intVal = parseInt(val, 10);

      if (this.lowest_fps > intVal) {
        this.lowest_fps = intVal;
      }
      this.fps_checks = this.fps_checks + 1;
      if (intVal < 24) {
        this.bad_fps_checks = this.bad_fps_checks + 1;
        if (this.check_animations_capability) {
          if (this.percentOfBadChecks > 15) {
            this.restrictTransitions();
            this.snackbar = true;
          }
        }
      }
    }
  },
  mounted() {
    this.measureFPS();
    window.setTimeout(() => {
      this.check_animations_capability = true;
    }, 10000);
  },
  methods: {
    measureFPS() {
      let then = performance.now() / 1000; // get time in seconds
      const checkFPS = () => {
        let now = performance.now() / 1000; // get time in seconds

        // compute time since last frame
        let elapsedTime = now - then;
        then = now;

        // compute fps
        let fps = 1 / elapsedTime;
        this.registered_fps = Math.round(fps);
        this.$nextTick(() => {
          requestAnimationFrame(checkFPS);
        });
      };
      checkFPS();
    },
    restrictTransitions() {
      document.querySelector("body").classList.add("restrictTransitions");
    }
  }
};
</script>

<style></style>
