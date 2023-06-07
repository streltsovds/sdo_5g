<template>
  <div class="hm-resume-btn">
    <v-btn
      v-if="isShow"
      class="hm-decline-btn"
      :color="color"
      :loading="isLoading"
      :disabled="isLoading"
      @click="confirm"
      >{{ text }}</v-btn
    >
    <hm-modal :is-shown="confirmIsShow" @close="close">
      <v-card>
        <v-card-text v-text="confirmText" />
        <v-card-text v-if="chief">
          <v-textarea
            v-model="comment"
            label="Комментарий"
            :rules="[rules.required]"
            :hint="commentHint"
          ></v-textarea>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="green darken-1" text="flat" @click="close">
            Нет
          </v-btn>
          <v-btn
            color="green darken-1"
            text="flat"
            :disabled="chief && !(comment && comment.length > 0)"
            @click="action"
          >
            Да
          </v-btn>
        </v-card-actions>
      </v-card>
    </hm-modal>
  </div>
</template>
<script>
import HmModal from "@/components/layout/hm-modal/index";
import { mapActions } from "vuex";
export default {
  components: { HmModal },
  props: {
    text: {
      type: String,
      required: true
    },
    url: {
      type: String,
      required: true
    },
    confirmText: {
      type: String,
      default: "Вы уверены?"
    },
    chief: {
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: "primary"
    },
    commentHint: {
      type: String,
      default: null
    },
    formData: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      isShow: true,
      isLoading: false,
      confirmIsShow: false,
      comment: null,
      rules: {
        required: v => !!v || "Поле обязательно для заполнения!"
      }
    };
  },
  methods: {
    ...mapActions("alerts", ["addSuccessAlert", "addErrorAlert"]),
    action() {
      if (this.isLoading) return;
      this.isLoading = true;

      if (this.chief) {
        this.formData.comment = `${this.text}. ${this.comment}`;
      }
      this.$axios
        .post(this.url, this.formData)
        .then(r => {
          if (r.status !== 200) throw new Error();
          this.addSuccessAlert("Операция успешно выполнена!");
          this.isShow = false;
        })
        .catch(() => {
          this.addErrorAlert("Произошла ошибка!");
        })
        .finally(() => {
          this.isLoading = false;
          this.close();
          this.hideAllResumeBtn();
        });
    },
    confirm() {
      this.confirmIsShow = true;
    },
    close() {
      this.confirmIsShow = false;
    },
    hideAllResumeBtn() {
      document.querySelectorAll(".hm-resume-btn").forEach(node => {
        node.classList.add("hm-resume-btn__hide");
      });
    }
  }
};
</script>
<style lang="scss">
.hm-resume-btn__hide {
  display: none;
}
</style>
