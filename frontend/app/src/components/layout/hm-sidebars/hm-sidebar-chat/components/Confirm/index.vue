<template>
  <v-dialog class="hm-recruiters-chat__confirm"
            v-model="dialog"
            :max-width="options.width"
            :style="{ zIndex: options.zIndex }"
            @keydown.esc="cancel"
  >
    <v-card>
      <p class="hm-recruiters-chat__confirm-title">
        {{ title }}
      </p>
      <v-card-text class="pa-4" v-show="!!message">
        {{ message }}
      </v-card-text>
      <v-card-actions class="pt-0 text-center justify-space-around">
        <v-btn @click.native="cancel" color="grey" text>
          Нет
        </v-btn>
        <v-btn @click.native="agree" color="primary darken-1" text>
          Да
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
/**
 * Vuetify Confirm Dialog component
 *
 * Insert component where you want to use it:
 * <confirm ref="confirm"></confirm>
 *
 * Call it:
 * this.$refs.confirm.open('Delete', 'Are you sure?', { color: 'red' }).then((confirm) => {})
 * Or use await:
 * if (await this.$refs.confirm.open('Delete', 'Are you sure?', { color: 'red' })) {
 *   // yes
 * }
 * else {
 *   // cancel
 * }
 *
 * Alternatively you can place it in main App component and access it globally via this.$root.$confirm
 * <template>
 *   <v-app>
 *     ...
 *     <confirm ref="confirm"></confirm>
 *   </v-app>
 * </template>
 *
 * mounted() {
 *   this.$root.$confirm = this.$refs.confirm.open
 * }
 */
export default {
  data: () => ({
    dialog: false,
    resolve: null,
    reject: null,
    message: null,
    title: null,
    options: {
      color: 'primary',
      width: 290,
      zIndex: 200
    }
  }),
  methods: {
    open(title, message, options) {
      this.dialog = true
      this.title = title
      this.message = message
      this.options = Object.assign(this.options, options)
      return new Promise((resolve, reject) => {
        this.resolve = resolve
        this.reject = reject
      })
    },
    agree() {
      this.resolve(true)
      this.dialog = false
    },
    cancel() {
      this.resolve(false)
      this.dialog = false
    }
  }
}
</script>
<style lang="sass" src="./index.sass"/>