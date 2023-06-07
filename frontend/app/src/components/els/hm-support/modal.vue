<template>
  <div
    id="hm-support">
    <div
      @click="openCloseModal"
      class="request-form-event"
    >
      <div class="footer-support">
        <div class="footer-support__icon">
          <svg-icon name="Support" color="#FFFFFF"/>
<!--          <img src="/images/default/support.png" alt="">-->
        </div>
        <div class="footer-support__technical"><span>Техническая поддержка</span></div>
      </div>
    </div>
    <transition name="modal-support">
      <div
        v-if="show"
        class="request-form"
      >
        <div class="request-form__header"
             @click="openCloseModal"
        >
<!--          <img src="/images/default/support.png" alt="">-->
          <svg-icon name="Support" color="#FFFFFF"/>
          <span>Техническая поддержка</span>
        </div>
        <div class="request-form__body">
          <v-alert
            dense
            v-if="resultForm"
            style="width: 100%"
            :type="typeAlert"
          >{{ resultForm }}</v-alert>
          <div class="request-form__body__theme-label">
            <span class="support-text__stand">Тема</span>
            <span>*</span>
          </div>
          <div class="request-form__body__theme-form" :class="formNullClass">
            <input :disabled="loading" :style="{opacity: loading ? '0.3' : '1'}" v-model="dataUser.theme" @keypress.stop type="text">
          </div>
          <div class="request-form__body__desc-label">
            <span class="support-text__stand">Описание проблемы</span>
            <span class="support-text__nostand">как работает сейчас</span>
          </div>
          <div class="request-form__body__desc-form">
          <textarea
            :disabled="loading"
            :style="{opacity: loading ? '0.3' : '1'}"
            v-model="dataUser.desc"
            @keypress.stop
            name="" id="" cols="30" rows="10">

          </textarea>
          </div>
          <div class="request-form__body__result-label">
            <span class="support-text__stand">Ожидаемый результат</span>
            <span class="support-text__nostand">как должно работать</span>
          </div>
          <div class="request-form__body__result-form">
          <textarea
            :disabled="loading"
            :style="{opacity: loading ? '0.3' : '1'}"
            v-model="dataUser.result"
            @keypress.stop
            cols="30" rows="10">
          </textarea>
          </div>
          <div class="request-form__body__button">
            <v-btn
              :loading="loading"
              @click="resultSupport"
              color="#FF9800">Отправить
            </v-btn>
            <v-btn
              class="request-form__body__button-close"
              color="#666666" @click.stop="closeModal">
              <span>Закрыть</span>
            </v-btn>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
    import axios from 'axios'
    import SvgIcon from "../../icons/svgIcon";

    export default {
        name: "HmSupportModal",
        props: ['view'],
        components: {SvgIcon},
        data() {
            return {
                show: false,
                dataUser: {
                    theme: this.view._subSubHeader || this.view._subHeader || this.view._header || '',
                    desc: '',
                    result: ''
                },
                resultForm: null,
                formNullClass: '',
                loading: false,
                typeAlert: ''
            }
        },
        methods: {
            async resultSupport() {
                this.resultForm = null;
                if (this.dataUser.theme === '') {
                    this.formNullClass = 'support__error'
                } else {
                    this.formNullClass = '';
                    this.loading = true;
                    await axios
                        .post('/techsupport/ajax/post-request/', {
                            "theme": this.dataUser.theme,
                            "problem_description": this.dataUser.desc,
                            "wanted_result": this.dataUser.result
                        })
                        .then(res => {
                            this.loading = false;
                            this.resultForm = res.data;
                            if (res.data.toLowerCase() !== 'не заполнено обязательно поле!') {
                              this.typeAlert = 'success'
                              this.dataUser.desc = '';
                              this.dataUser.result = '';
                              setTimeout(() => {
                                this.resultForm = null;
                                this.openCloseModal();
                              }, 2000)
                            }
                            else this.typeAlert = 'error'
                        })
                }
            },
            openCloseModal() {
                this.show = !this.show
            },
            closeModal() {
                this.show = false
            }
        },
    }
</script>

<style lang="scss">
  #hm-support {
    .request-form-event {
      > a {
        .footer-support {
          margin: 0 0 1vh 1vh;

          &__technical {
            > span {
              font-size: 1.5vh;
            }
          }
        }
      }
    }

    .request-form {
      width: 100%;
      max-width: 600px;
      height: auto;
      position: absolute;
      bottom: 0;
      left: 0;
      background: #70889E;
      box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 3px 4px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.14);
      border-radius: 0 6px 0 0;
      z-index: 1000;
      transition: .4s ease-in-out;

      &__header {
        width: 100%;
        height: 48px;
        background: #4F4F4F;
        display: flex;
        align-items: center;
        padding: 0 26px;
        cursor: pointer;
        border-radius: 0 6px 0 0;

        > svg {
          width: 24px;
          height: 24px;
          margin-right: 12px;
        }
        > span {
          color: #FFFFFF;
        }
      }

      &__body {
        width: 100%;
        height: calc(100% - 48px);
        padding: 25px 26px 0 26px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: flex-start;

        &__result {
          width: 100%;
          height: auto;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        &__theme-label {
          margin-bottom: 9px;

          > span:nth-child(2) {
            color: #E31F28;
            margin-left: 2px;
          }
        }

        &__theme-form {
          width: 100%;
          height: 34px;
          margin-bottom: 26px;

          > input {
            width: 100%;
            height: 34px;
            background: #FFFFFF;
            border-radius: 2px;
            color: #212121 !important;
            padding: 2px 5px;
            box-sizing: border-box;
            transition: .3s ease-in-out;
          }
        }

        &__desc-label, &__result-label {
          margin-bottom: 9px;
        }

        &__result-form, &__desc-form {
          width: 100%;
          height: 80px;
          margin-bottom: 26px;

          > textarea {
            width: 100%;
            height: 80px;
            background: #FFFFFF;
            border-radius: 2px;
            color: #212121 !important;
            padding: 2px 5px;
            box-sizing: border-box;
          }
        }

        &__button {
          margin: 27px 0;

          > button {
            width: 127px;
            height: 38px;
            color: #FFFFFF;
            > span {
                margin-top: 2px;
            }
          }

          > button:nth-child(1) {
            margin-right: 26px;
            margin-left: 0;
          }
        }
      }
    }
  }

  textarea {
    resize: none;
  }

  .support__error {
    > input {
      border:2px solid #a24c4c;
      box-shadow: 0 0 5px #a24c4c;
    }
  }

  .support-text__stand {
    color: #FFFFFF;
  }

  .support-text__nostand {
    margin-left: 4px;
    color: #FFC850;
  }


  .modal-support-enter-active, .modal-support-leave-active {
    transition: opacity .3s;
  }

  .modal-support-enter, .modal-support-leave-to /* .fade-leave-active до версии 2.1.8 */
  {
    opacity: 0;
  }
</style>
