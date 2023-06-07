<template>
  <transition name="form-fade" mode="out-in">
    <v-form
      class="hm-form-alternative"
      ref="form"
      v-if="isFormShown"
      :id="settings.id"
      v-model="isValid"
      :name="settings.name"
      :action="settings.action"
      @submit="onSubmit"
    >
      <v-alert :value="warning" type="warning">
        {{ warning }}
      </v-alert>
      <v-alert :value="error" type="error">
        {{ error }}
      </v-alert>

      <div v-if="!hasFormGroups">
        <component
          :is="element.type"
          v-for="element in formElements"
          :key="element.id"
          :is-submiting="isSubmitting"
          :element="element"
          @field-input="onFieldInput"
        />
      </div>

      <v-expansion-panel v-if="hasFormGroups">
        <v-expansion-panel-content
          v-for="formGroup in formGroups"
          :key="formGroup.settings.id"
        >
          <h4 class="title" slot="header">
            {{ formGroup.settings.legend }}
          </h4>
          <v-card>
            <v-card-text>
              <component
                :is="element.type"
                v-for="element in formGroup.elements"
                :key="element.id"
                :is-submiting="isSubmitting"
                :element="element"
                @field-input="onFieldInput"
              />
            </v-card-text>
          </v-card>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-form>
    <v-alert v-else :value="success" type="success">
      {{ success }}
    </v-alert>
  </transition>
</template>

<script>
import formCheckbox from "./partials/formCheckbox";
import formHidden from "./partials/formHidden";
import formPassword from "./partials/formPassword";
import formSubmit from "./partials/formSubmit";
import formText from "./partials/formText";
import formCaptcha from "./partials/formCaptcha";
import { mapActions } from "vuex";

export default {
  /* eslint-disable */
  components: {
    formCheckbox,
    formHidden,
    formPassword,
    formSubmit,
    formText,
    formCaptcha
  },
  /* eslint-enable */
  props: {
    jsonData: {
      type: String,
      default: () => null
    },

    debug: {
      type: Boolean,
      default: () => false
    }
  },
  data() {
    return {
      raw: {},
      isValid: true,
      info: {},
      error: null,
      warning: null,
      success: null,
      isFormShown: true,
      isSubmitting: false
    };
  },
  computed: {
    formElements() {
      if (this.raw && this.raw.elements && this.raw.elements.length)
        return this.raw.elements;
      return false;
    },
    settings() {
      if (this.raw && this.raw.settings) return this.raw.settings;
      return {};
    },
    hasFormGroups() {
      return this.raw.display_groups ? true : false;
    },
    formGroups() {
      if (!this.hasFormGroups) return;
      return this.raw.display_groups;
    }
  },
  watch: {
    success(value) {
      if (value && this.info.ref) {
        window.location.href = this.info.ref;
      }
    }
  },
  created() {},
  mounted() {
    this.raw = JSON.parse(this.$slots.default[0].text);
  },
  methods: {
    ...mapActions("user", ["initUser"]),
    onSubmit(event) {
      event.preventDefault();
      this.$refs.form.validate();
      if (this.isValid) {
        let ret = {};
        for (let item in this.info) {
          if (this.info.hasOwnProperty(item)) {
            ret[item] = this.info[item];
          }
        }
        this.post(ret);
      }
    },
    post(data) {
      this.isSubmitting = true;
      this.$axios
        .post(this.settings.action, data)
        .then(response => response.data)
        .then(this.getResponseCodes)
        .catch(error => {
          this.isSubmitting = false;
          this.error = error;
        });
    },
    getResponseCodes(data) {
      this.initUser()
        .then(() => {
          this.isSubmitting = false;
          if (data.code == 0) {
            this.warning = data.message;
          }
          if (data.code == 1) {
            this.success = data.message;
            this.isFormShown = false;
            if (this.raw.settings && this.raw.settings.id) {
              let id = this.raw.settings.id;
              let firstLetterUppercaseId =
                id.charAt(0).toUpperCase() + id.substr(1);
              this.$root.$emit(`hmForm${firstLetterUppercaseId}`);
            }
          }
        });
    },
    onFieldInput(event) {
      this.warning = null;
      this.error = null;
      this.info = { ...this.info, ...event };
    }
  }
};
</script>
<style lang="scss">
.hm-form-alternative {
  & .label-active {
    .v-label {
      transform: translateY(-18px) scale(.75) !important;
    }
  }
}
@-webkit-keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@-webkit-keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

.form-fade-enter-active,
.fadeIn,
.form-fade-leave-active,
.fadeOut {
  -webkit-animation-duration: 0.3s;
  animation-duration: 0.3s;
  -webkit-animation-fill-mode: both;
  animation-fill-mode: both;
}

.form-fade-enter-active,
.fadeIn {
  -webkit-animation-name: fadeIn;
  animation-name: fadeIn;
}

.form-fade-leave-active,
.fadeOut {
  -webkit-animation-name: fadeOut;
  animation-name: fadeOut;
}
</style>
