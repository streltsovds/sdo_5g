<template>
  <v-card
    v-once
    style="position:absolute;transform: translateY(100%);bottom:0;z-index:1;left:0;right:0;"
  >
    <v-list>
      <v-list-item>
        <v-list-item-action> <v-icon>subject</v-icon> </v-list-item-action>
        <v-list-item-content>
          <v-list-item-action-text>
            {{ details.exerciseName.key }}
          </v-list-item-action-text>
          <v-list-item-title>
            {{ details.exerciseName.value }}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item>
        <v-list-item-action>
          <v-icon>local_library</v-icon>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-action-text>
            {{ details.courseName.key }}
          </v-list-item-action-text>
          <v-list-item-title>
            {{ details.courseName.value }}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item>
        <v-list-item-action> <v-icon>help</v-icon> </v-list-item-action>
        <v-list-item-content>
          <v-list-item-action-text>
            {{ details.questionsCount.key }}
          </v-list-item-action-text>
          <v-list-item-title>
            {{ details.questionsCount.value }}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item>
        <v-list-item-action> <v-icon>thumb_up</v-icon> </v-list-item-action>
        <v-list-item-content>
          <v-list-item-action-text>
            {{ getAttemptsText }}
          </v-list-item-action-text>
          <v-list-item-title>
            {{
              `${details.attemptsCount.value.left.value} / ${
                details.attemptsCount.value.total.value
              }`
            }}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script>
export default {
  props: {
    details: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    getAttemptsText() {
      const {
        key,
        value: { left, total, delimiter }
      } = this.details.attemptsCount;
      return `${left.key}${delimiter.value}${total.key.toLowerCase()} ${key}`;
    }
  },
  mounted() {
    this.$root.$el.addEventListener("click", this.handleOutsideClick);
  },
  beforeDestroy() {
    this.$root.$el.removeEventListener("click", this.handleOutsideClick);
  },
  methods: {
    handleOutsideClick(event) {
      if (this.$el.contains(event.target)) return false;
      event.preventDefault();
      this.$emit("click-outside");
    }
  }
};
</script>

<style></style>
