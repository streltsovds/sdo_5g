<template>
  <div class="modal-asses modal-assesment">
    <div class="modal-asses__title modal-assesment">
      <span class="modal-assesment">{{ _('Поставить оценку') }}</span>
    </div>
    <div class="modal-asses__assessment modal-assesment">
      <input class="modal-assesment" type="text" v-model="assesment" placeholder="0">
      <div class="input-number modal-assesment">{{ preview ? preview : '0' }}</div>
      <div class="button-number modal-assesment" @click="assCount">
        <span class="modal-assesment">{{ _('Готово') }}</span>
      </div>
    </div>
    <div class="help-assesment">
      <span>{{ _('Подсказка: значение 0-100') }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: "modalAssesment",
  props: {},
  data() {
    return {
      assesment: null
    }
  },
  methods: {
    assCount() {
      this.$emit('assCount', this.assesment)
    },
  },
  watch: {
    assesment(data) {
      if(Number(data)) {
        if(data > 100) {
          this.assesment = 100
        } else if(data < 0) {
          this.assesment = 0
        }
      } else {
       this.assesment = data.slice(0, data.length-1)
      }
    }
  },
  computed: {
    preview() {
      return this.assesment > 100 ? 100 : this.assesment< 0 ? 0 : this.assesment
    }
  }
}
</script>

<style lang="scss">
.modal-asses {
  background: #FFFFFF;
  box-shadow: 0 3px 4px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.14);
  border-radius: 4px;
  width: 192px;
  height: 129px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
  padding: 16px;
  position: absolute;
  bottom: 40px;
  z-index: 1000;
  &__title {
    margin-bottom: 17px;
    > span {
      font-weight: normal;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
  }
  &__assessment {
    display: flex;
    flex-wrap: nowrap;
    justify-content: space-between;
    width: 100%;
    > input {
      opacity: 0;
      position: absolute;
      z-index: 10;
      width: 48px;
      height: 29px;
      box-sizing: border-box;
      border-radius: 4px;
    }
    > .input-number {
      width: 48px;
      height: 29px;
      background: rgba(218, 218, 218, 0.3);
      border: 1px solid #B9C3D2;
      box-sizing: border-box;
      border-radius: 4px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    > .button-number {
      cursor: pointer;
      padding: 2px 16px;
      background: #FF9800;
      border-radius: 4px;
      display: flex;
      align-items: center;
      text-align: center;
      letter-spacing: 0.02em;
      color: #FFFFFF;
    }


    /*> div {*/
    /*  width: 26px;*/
    /*  height: 26px;*/
    /*  background: #D4E3FB;*/
    /*  border-radius: 2px;*/
    /*  box-sizing: border-box;*/
    /*  display: flex;*/
    /*  justify-content: center;*/
    /*  align-items: center;*/
    /*  cursor: pointer;*/
    /*  > span {*/
    /*    font-weight: 500;*/
    /*    font-size: 20px;*/
    /*    line-height: 24px;*/
    /*    letter-spacing: 0.02em;*/
    /*    color: #1E1E1E;*/
    /*  }*/
    /*  &:hover {*/
    /*    background: #FDE1D9;*/
    /*    border: 0.6px solid #4A90E2;*/
    /*  }*/
    /*}*/
    /*> div:not(:last-child) {*/
    /*  margin-right: 4px;*/
    /*}*/
  }
  .help-assesment {
    margin-top: 6px;
    width: 100%;
    > span {
      font-size: 10px;
      line-height: 18px;
      letter-spacing: 0.15px;
      color: #979797;
    }
  }
}
</style>
