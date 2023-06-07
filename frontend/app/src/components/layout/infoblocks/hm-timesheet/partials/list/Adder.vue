<template>
  <v-card>
    <v-form ref="form" v-model="formValid" @submit.prevent="onSubmit">
      <v-card-title :class="$vuetify.breakpoint.mdAndUp ? 'headline' : 'title'">
        Добавить время
        <v-spacer />
        <v-btn icon @click="$emit('close-dialog')">
          <v-icon> close </v-icon>
        </v-btn>
      </v-card-title>
      <v-divider />
      <v-card-text>
        <v-select
          v-model="selected_action"
          class="mb-3"
          label="Тип активности"
          :validate-on-blur="true"
          placeholder="Выберите тип активности"
          :items="computedActions"
          :rules="[rules.required]"
        />
        <v-textarea
          v-model="activity_description"
          class="mb-3"
          hint="Опишите Вашу деятельность"
          no-resize
          outlined
          label="Описание"
          :rules="[rules.required]"
        />
        <v-text-field
          :value="timeFieldValue"
          label="Время"
          :loading="isTimeFieldDialogShown"
          :rules="[rules.required]"
          @click.prevent="onTimeFieldFocus"
        />
        <v-dialog
          v-model="isTimeFieldDialogShown"
          max-width="500"
          :fullscreen="$vuetify.breakpoint.smAndDown"
        >
          <timepick
            :time="time"
            @set-time="setTime"
            @close-dialog="isTimeFieldDialogShown = !isTimeFieldDialogShown"
          />
        </v-dialog>
      </v-card-text>
      <v-divider />

      <v-card-actions>
        <v-btn :loading="isLoading" color="primary" type="submit">
          Добавить
        </v-btn>
      </v-card-actions>
    </v-form>
  </v-card>
</template>

<script>
import Timepick from "./Timepick";
export default {
  components: {
    Timepick
  },
  props: {
    actions: {
      type: Array,
      default: () => []
    },
    isLoading: Boolean
  },
  data() {
    return {
      selected_action: null,
      time: {
        from: null,
        to: null
      },
      rules: {
        required: v => {
          if (!v) {
            return "Необходимо заполнить";
          }
        }
      },
      formValid: false,
      isTimeFieldDialogShown: false,
      activity_description: null
    };
  },
  computed: {
    computedActions() {
      return this.actions.map(action => {
        return {
          value: action.classifier_id,
          text: action.name
        };
      });
    },
    timeFieldValue() {
      if (this.time.from) {
        return (
          `с ${this.time.from}` + (this.time.to ? ` по ${this.time.to}` : "")
        );
      } else {
        return null;
      }
    }
  },
  methods: {
    onSubmit() {
      this.$refs.form.validate();
      if (this.formValid) {
        this.$emit("activity-submit", {
          type: this.selected_action,
          description: this.activity_description,
          time: { ...this.time }
        });
        this.restoreDefaults();
      }
    },
    restoreDefaults() {
      this.$refs.form.reset();
      this.time = { from: null, to: null };
    },
    onTimeFieldFocus(event) {
      event.preventDefault();
      this.isTimeFieldDialogShown = true;
    },
    setTime(payload) {
      this.time = { ...this.time, ...payload };
      if (this.time.from && this.time.to) {
        this.isTimeFieldDialogShown = !this.isTimeFieldDialogShown;
      }
    }
  }
};
</script>

<style></style>
