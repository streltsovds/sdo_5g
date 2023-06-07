<template>
  <v-card>
    <v-card-title class="title">
      Выберите время {{ time.from ? "окончания" : "начала" }}
    </v-card-title>
    <v-card-text>
      <v-time-picker
        v-if="showPicker"
        v-model="timeToShow"
        :min="time.from"
        format="24hr"
        full-width
        :landscape="$vuetify.breakpoint.mdAndUp"
      />
    </v-card-text>
    <v-divider />
    <v-card-actions v-if="time.from" class="title">
      {{
        'Вы выбрали - "с ' +
          time.from +
          (timeToShow ? ` по ${timeToShow}"` : '"')
      }}
    </v-card-actions>
    <v-card-actions>
      <v-btn color="primary" @click="addTime">{{
        isTimeSet ? "Подтвердить" : "Выбрать"
      }}</v-btn>
      <v-btn color="primary" text @click="$emit('close-dialog')">Отмена</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  props: {
    time: {
      type: Object,
      default: () => false
    }
  },
  data() {
    return {
      timeToShow: null,
      isTimeSet: false,
      showPicker: true
    };
  },
  computed: {
    showedTime() {
      if (this.time.time) {
        return this.time.from;
      } else {
        return this.timeToShow;
      }
    }
  },
  methods: {
    allowedHours(value) {
      if (this.time.from) {
        return value >= parseInt(this.time.from.split(":")[0], 10);
      } else {
        return true;
      }
    },
    allowedMinutes(value) {
      if (this.time.from) {
        return value > parseInt(this.time.from.split(":")[1], 10);
      } else {
        return true;
      }
    },
    addTime() {
      if (this.time.from) {
        this.$emit("set-time", { to: this.timeToShow });
        this.timeToShow = null;
      } else {
        this.showPicker = false;
        this.$emit("set-time", { from: this.timeToShow });
        this.timeToShow = null;
        this.$nextTick(() => {
          this.showPicker = true;
          this.isTimeSet = true;
        });
      }
      this.timeToShow = null;
    }
  }
};
</script>

<style></style>
