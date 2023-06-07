<template>
  <v-flex
    xs12
    sm4
    class="hm-form-element hm-submit"
    :class="`hm-form-element_${name}`"
  >
    <input
      v-if="redirectUrl"
      type="hidden"
      name="redirectUrl"
      :value="redirectUrl"
    />
    <v-overflow-btn
      v-if="urls"
      :items="urls"
      :label="label"
      @change="changeSubmitUrl"
    ></v-overflow-btn>

    <v-btn
      v-else
      :id="`btn_${name}`"
      :loading="loading"
      :disabled="loading"
      color="primary"
      type="submit"
      :name="`btn_${name}`"
      large
      v-text="label"
      @click.prevent="onSubmit"
    ></v-btn>
  </v-flex>
</template>
<script>
import { mapActions } from "vuex";
export default {
  name: "HmSubmit",
  props: {
    id: {
      type: String,
      required: true
    },
    name: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    redirectUrls: {
      type: Array,
      default: () => []
    },
    isAjax: {
      type: Boolean,
      default: false
    },
    formId: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      redirectUrl: null,
      form: null,
      loading: false
    };
  },
  computed: {
    urls() {
      if (!this.redirectUrls || this.redirectUrls.length === 0) return false;

      let urls = [];

      this.redirectUrls
        .filter(redirect => !!(redirect.label && redirect.url))
        .forEach(redirect => urls.push({ text: redirect.label }));
      return urls;
    },
    formHasInStateFieldIsUpdate() {
      let formState = this.$store.state[this.formId];
      if (!formState) return false;
      return formState.hasOwnProperty("isSubmit");
    }
  },
  mounted() {
    this.form = this.getFormEl();

    if (this.formHasInStateFieldIsUpdate)
      this.$store.dispatch(`${this.formId}/resetIsSubmit`);
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert", "addSuccessAlert"]),
    /**
     * @returns HTMLFormElement
     */
    getFormEl() {
      return this.$el.closest("form");
    },
    changeSubmitUrl(item) {
      let submitRedirect = this.redirectUrls.find(
        redirect => redirect.label === item
      );
      if (!submitRedirect || !submitRedirect.url) return;

      this.redirectUrl = encodeURI(submitRedirect.url);
      this.$nextTick(
        () => this.submit(false)
      );
    },
    async runIframesBeforeFormSave() {
      const iframes = this.form.querySelectorAll("iframe");

      let results = [];

      for (/** @type HTMLIFrameElement */ const iframe of iframes) {
        let fnName = iframe.getAttribute("data-form-element-iframe-before-form-save");
        if (!fnName) {
          continue;
        }
        console.log(`hm-submit: invoking ${fnName}() on iframe`);
        // same domain
        try {
          let result = iframe.contentWindow[fnName]();
          results.push(result)
        } catch (e) {
          console.log("iframe window object access fail");
        }
      }

      for (let result of results) {
        await result;
      }
    },
    onSubmit(event) {
      this.loading = true;
      this.submit();
    },
    async submit(ajax = undefined) {

      await this.runIframesBeforeFormSave();

      if (typeof ajax == "undefined") {
        ajax = this.isAjax;
      }

      if (ajax) {
        this.ajaxSubmit();
      } else {
        this.form.submit();
      }
    },
    ajaxSubmit() {
      if (!this.form || !this.form.action)
        return this.addErrorAlert("Произошла ошибка отправки формы!");

      if (this.loading)
        return;

      let formData = new FormData(this.form);

      this.$axios
        .post(this.form.action, formData)
        .then(response => {
          if (response.status !== 200) throw new Error();
          this.addSuccessAlert("Данные успешно сохранены!");

          if (this.formHasInStateFieldIsUpdate)
            this.$store.dispatch(`${this.formId}/setIsSubmit`);
        })
        .catch(() => this.addErrorAlert("Произошла ошибка сохранения формы!"))
        .then(() => (this.loading = false));
    }
  }
};
</script>
<style lang="scss">
.hm-submit {
  display: inline-grid;
  min-width: 265px;
  button {
    margin: 0;
    width: 100%;
    font-weight: 400;
  }
  .v-label {
    font-size: 14px;
  }
  .v-overflow-btn {
    margin-top: 12px;
    padding-top: 0;
    font-size: 14px;
    text-transform: uppercase;

    .v-select__selections {
      flex-wrap: nowrap;
    }
    .v-select__selection {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      display: block;
    }
  }
}
</style>
