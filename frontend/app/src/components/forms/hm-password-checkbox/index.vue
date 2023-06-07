<template>
  <div
    class="hm-form-element hm-password-checkbox"
    :class="`hm-form-element_${name}`"
  >
    <v-checkbox
      v-model="needGeneratePassword.value"
      :label="needGeneratePassword.label || null"
      :class="{ required: needGeneratePassword.required }"
      :hint="needGeneratePassword.description || null"
      persistent-hint
      @change="clearErrors"
    ></v-checkbox>
    <input
      type="hidden"
      name="generatepassword"
      :value="needGeneratePassword.value ? 1 : 0"
    />
    <template v-if="!needGeneratePassword.value">
      <v-text-field
        v-model="password.value"
        :name="name"
        :type="password.show ? 'text' : 'password'"
        :append-icon="password.show ? 'visibility_off' : 'visibility'"
        :label="password.label"
        :rules="[rules.required, rules.min]"
        :error-messages="errorsArray"
        :error="errorsExist"
        :hint="password.description"
        :class="{ required: password.required }"
        persistent-hint
        @change="clearErrors"
        @click:append="password.show = !password.show"
      ></v-text-field>
      <v-text-field
        v-model="passwordConfirm.value"
        name="userpasswordrepeat"
        :type="passwordConfirm.show ? 'text' : 'password'"
        :append-icon="passwordConfirm.show ? 'visibility_off' : 'visibility'"
        :label="passwordConfirm.label || 'Повторите пароль'"
        :rules="[rules.required, rules.min, rules.equalPassword]"
        :hint="passwordConfirm.description || null"
        :class="{ required: passwordConfirm.required }"
        persistent-hint
        @change="clearErrors"
        @click:append="passwordConfirm.show = !passwordConfirm.show"
      ></v-text-field>
    </template>
  </div>
</template>
<script>
export default {
  name: "HmPasswordCheckbox",
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      type: String,
      default: null
    },
    attribs: {
      type: Object,
      default: () => {}
    },
    errors: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      errorsData: this.errors,
      password: {
        label: this.attribs.label || "",
        description: this.attribs.description || "",
        required: this.attribs.required || "",
        value: this.value || "",
        show: false
      },
      passwordConfirm: this.attribs.userpasswordrepeat || {},
      needGeneratePassword: this.attribs.generatepassword || {},
      formId: this.attribs.formId || null,
      validate: this.attribs.rules || {},
      rules: {
        required: v => !!v || "Поле обязательно для заполнения",
        min: v1 => {
          return (
            (typeof v1 === "string" && v1.length >= this.validate.min) ||
            `Минимальное количество символов ${this.validate.min}`
          );
        },
        equalPassword: v => v === this.password.value || "Значения не совпадают"
      }
    };
  },
  computed: {
    errorsExist() {
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key)) return true;
      }
      return false;
    },
    errorsArray() {
      let rules = [];
      for (let key in this.errorsData) {
        if (this.errorsData.hasOwnProperty(key))
          rules.push(this.errorsData[key]);
      }
      return rules;
    }
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      if (this.needGeneratePassword.value) this.password.value = null;

      this.$set(this.passwordConfirm, "show", false);
    },

    clearErrors() {
      this.errorsData = null;
    }
  }
};
</script>
