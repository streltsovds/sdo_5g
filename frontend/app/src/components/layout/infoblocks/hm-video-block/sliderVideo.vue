<template>
  <div class="slider-videos">
    <div class="slider-videos__navigation" :style="{minWidth: !editUrl ? '644px' : ''}">
      <div :class="!disabled ? 'slider-videos__navigation-back' : 'slider-videos__navigation-back slider-videos__navigation-back--disabled' " @click="activeEl('back')">
        <div class="slider-videos__navigation-back-icon">
          <svg width="12"
               height="20"
               viewBox="0 0 12 20"
               fill="none"
               xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M11.5644 17.7896C11.9738 18.199 11.9738 18.8628 11.5644 19.2722L11.1914 19.6452C10.782 20.0546 10.1182 20.0546 9.70876 19.6452L0.0712805 10.0077L9.70876 0.370209C10.1182 -0.0392016 10.782 -0.0392007 11.1914 0.37021L11.5644 0.743203C11.9738 1.15261 11.9738 1.8164 11.5644 2.22581L3.78248 10.0077L11.5644 17.7896Z" fill="white" />
          </svg>
        </div>
        <span>{{ _('Назад') }}</span>
      </div>
      <div class="slider-videos__navigation-nav">
        <div class="slider-videos__navigation-nav__el"
             v-for="(el, key) in videos"
             :key="key"
             :class="key === activeVideo ? 'activeEl' : ''"
             @click="activeEl(key)"
        />
      </div>
      <div :class="!disabled ? 'slider-videos__navigation-next' : 'slider-videos__navigation-next slider-videos__navigation-next--disabled' " @click="activeEl('next')">
        <span>{{ _('Вперед') }}</span>
        <div class="slider-videos__navigation-next-icon">
          <svg width="13"
               height="20"
               viewBox="0 0 13 20"
               fill="none"
               xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M0.56357 17.7896C0.154159 18.199 0.154159 18.8628 0.56357 19.2722L0.936562 19.6452C1.34597 20.0546 2.00976 20.0546 2.41917 19.6452L12.0566 10.0077L2.41917 0.370209C2.00976 -0.0392016 1.34597 -0.0392007 0.936562 0.37021L0.56357 0.743203C0.154159 1.15261 0.154159 1.8164 0.563569 2.22581L8.34545 10.0077L0.56357 17.7896Z" fill="white" />
          </svg>
        </div>
      </div>
    </div>
    <v-tooltip bottom>
      <template v-if="editUrl" v-slot:activator="{ on: onTooltip }">
        <a
          class="slider-videos__settings"
          v-on="onTooltip"
          :href="editUrl"
          v-if="isAdmin"
        >
          <svg width="23"
               height="24"
               viewBox="0 0 23 24"
               fill="none"
               xmlns="http://www.w3.org/2000/svg"
          >
            <path fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M6.59473 12.0021C6.59473 9.4277 8.68942 7.33301 11.2633 7.33301C13.8372 7.33301 15.9319 9.4277 15.9319 12.0021C15.9319 14.5766 13.8372 16.671 11.2633 16.671C8.68942 16.671 6.59473 14.5766 6.59473 12.0021ZM7.44434 12.0024C7.44434 14.1083 9.15769 15.8222 11.2636 15.8222C13.3695 15.8222 15.0828 14.1083 15.0828 12.0024C15.0828 9.89624 13.3695 8.18262 11.2636 8.18262C9.15769 8.18262 7.44434 9.89624 7.44434 12.0024Z"
                  fill="white"
            />
            <path fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M21.6729 9.45104L20.275 10.258C20.0846 10.3678 19.9372 10.6772 19.9721 10.8943C19.9753 10.9135 20.0484 11.3729 20.0484 12.0004C20.0484 12.6271 19.9758 13.0868 19.9724 13.106C19.9374 13.3233 20.0849 13.6324 20.2753 13.7423L21.6726 14.5492C22.067 14.7767 22.35 15.146 22.4684 15.5887C22.5865 16.0306 22.5265 16.4914 22.2988 16.8858L21.0126 19.1135C20.7852 19.5077 20.4161 19.7909 19.9737 19.9093C19.5318 20.0274 19.0705 19.9674 18.6761 19.7394L17.279 18.933C17.0889 18.8231 16.7467 18.8501 16.576 18.9887C16.5608 19.0015 16.191 19.3005 15.6568 19.609C15.1291 19.9144 14.6795 20.0877 14.6608 20.0946C14.4552 20.1736 14.2608 20.4565 14.2608 20.6765V22.2893C14.2608 23.2323 13.4933 24 12.5501 24H9.97784C9.03463 24 8.26716 23.2323 8.26716 22.2893V20.6765C8.26716 20.4562 8.07275 20.1736 7.86715 20.0946C7.84848 20.0877 7.39941 19.9146 6.87141 19.609C6.33753 19.3005 5.96793 19.0015 5.9522 18.9887C5.78179 18.8501 5.44019 18.8226 5.24979 18.933L3.85191 19.74C3.03456 20.2109 1.98682 19.9304 1.51481 19.1138L0.228666 16.8861C0.000930239 16.4919 -0.0590704 16.0303 0.0593309 15.5884C0.177732 15.1458 0.460135 14.7767 0.85454 14.549L2.25269 13.742C2.44282 13.6322 2.59029 13.3225 2.55536 13.1057C2.55216 13.0865 2.47936 12.6271 2.47936 12.0001C2.47936 11.3732 2.55216 10.9132 2.55536 10.894C2.59056 10.6769 2.44309 10.3676 2.25269 10.2577L0.85454 9.45051C0.460402 9.22277 0.177732 8.85396 0.0593309 8.41129C-0.0590704 7.96942 0.000930239 7.50835 0.228666 7.11395L1.51481 4.88619C1.74255 4.49205 2.11162 4.20911 2.55376 4.09071C2.99643 3.97258 3.45697 4.03231 3.85137 4.26005L5.25006 5.06752C5.44099 5.17766 5.78233 5.15046 5.953 5.01152C5.9682 4.99899 6.33753 4.70032 6.87141 4.39205C7.39915 4.08698 7.84822 3.91364 7.86688 3.90644C8.07249 3.82751 8.26689 3.54457 8.26689 3.32457V1.71069C8.26689 0.767209 9.03436 0 9.97757 0H12.5499C13.4931 0 14.2606 0.767209 14.2608 1.71095V3.3243C14.2608 3.54511 14.4552 3.82751 14.6608 3.90644L14.6611 3.90653C14.6828 3.91492 15.1305 4.08757 15.6563 4.39205C16.1902 4.70032 16.5595 4.99899 16.5752 5.01179C16.7462 5.15072 17.0878 5.17766 17.2779 5.06752L18.6761 4.26058C19.0705 4.03258 19.5321 3.97284 19.9737 4.09098C20.4161 4.20938 20.7849 4.49232 21.0126 4.88672L22.2988 7.11448C22.5268 7.50862 22.5865 7.96969 22.4684 8.41209C22.35 8.85423 22.0673 9.22304 21.6729 9.45104ZM19.1352 11.0338C19.1348 11.0305 19.1345 11.0286 19.1345 11.0284C19.0441 10.4681 19.3588 9.8065 19.8505 9.52277L21.2473 8.71556C21.4454 8.60142 21.5873 8.41529 21.6473 8.19182C21.7073 7.96888 21.6772 7.73661 21.5628 7.53821L20.2766 5.31045C20.1622 5.11259 19.9766 4.97072 19.7532 4.91098C19.5289 4.85072 19.2974 4.88085 19.0996 4.99552L17.7014 5.80219C17.2105 6.0862 16.4793 6.02806 16.0393 5.67019C16.0361 5.66726 15.703 5.39926 15.231 5.12645C14.764 4.85685 14.3598 4.70005 14.3555 4.69845C13.8262 4.49551 13.411 3.89177 13.411 3.32403V1.71068C13.411 1.23548 13.0246 0.848805 12.5493 0.848805H9.97705C9.50185 0.848805 9.11544 1.23548 9.11544 1.71068V3.32457C9.11544 3.89231 8.70024 4.49551 8.17063 4.69871C8.16716 4.69978 7.76263 4.85685 7.29596 5.12672C6.82368 5.39979 6.49088 5.66753 6.48741 5.67046C6.04714 6.02833 5.31727 6.0862 4.82526 5.80219L3.42658 4.99472C3.22871 4.88112 2.99644 4.85072 2.7735 4.91072C2.54977 4.97045 2.36417 5.11259 2.24977 5.31045L0.963618 7.53821C0.849483 7.73661 0.819083 7.96862 0.879084 8.19182C0.938818 8.41529 1.08095 8.60142 1.27882 8.71556L1.73201 8.9773C1.7313 8.97684 1.73083 8.97636 1.73083 8.97636L2.67697 9.52277C3.16898 9.8065 3.48365 10.4681 3.39324 11.0289L3.39323 11.029C3.39203 11.0368 3.32844 11.4482 3.32844 12.0004C3.32844 12.54 3.38922 12.945 3.39306 12.9706C3.39317 12.9713 3.39323 12.9718 3.39324 12.9719C3.48311 13.5319 3.16871 14.1938 2.67697 14.4775L1.73376 15.0228L1.73296 15.0215C1.67173 15.059 1.55779 15.1246 1.32793 15.2569L1.28015 15.2844C1.08229 15.3991 0.940151 15.585 0.880417 15.8087C0.820416 16.0316 0.85055 16.2639 0.964951 16.4623L2.2511 18.6901C2.48843 19.101 3.01617 19.2429 3.42845 19.005C3.66311 18.8698 3.79832 18.7922 3.88418 18.7474L3.88205 18.7431L4.82606 18.1986C5.31647 17.9141 6.04741 17.9725 6.48821 18.3301C6.49115 18.3333 6.82448 18.6018 7.29649 18.8743C7.76743 19.1466 8.1677 19.3007 8.17196 19.3023C8.70184 19.5055 9.11678 20.1096 9.11678 20.6768V22.2896C9.11678 22.7648 9.50318 23.1512 9.97838 23.1512H12.5507C13.0259 23.1512 13.4123 22.7648 13.4123 22.2896V20.6768C13.4123 20.1096 13.8275 19.5058 14.3571 19.3026C14.3608 19.3015 14.7656 19.1442 15.2328 18.8743C15.7048 18.6018 16.0382 18.3333 16.0414 18.3301C16.4819 17.9725 17.2126 17.9143 17.7038 18.1986L19.1006 19.0047C19.299 19.1191 19.5313 19.1495 19.7542 19.0895C19.9777 19.0293 20.1633 18.8877 20.278 18.6893L21.5641 16.4615C21.6782 16.2636 21.7086 16.0314 21.6486 15.8084C21.5886 15.5847 21.4468 15.3988 21.2486 15.2842L19.851 14.4775C19.3593 14.1938 19.0444 13.5321 19.1345 12.9713L19.1345 12.9712C19.1357 12.9635 19.1993 12.5526 19.1993 12.0004C19.1993 11.4795 19.1425 11.0842 19.1352 11.0338Z"
                  fill="white"
            />
          </svg>
        </a>
      </template>
      <span>Редактировать</span>
    </v-tooltip>
  </div>
</template>


<script>
export default {
  name: "SliderVideo",
  props: {
    videos: {
      type: Array,
      default: () => []
    },
    activeVideo: {
      type: Number,
      default: null
    },
    editUrl: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    },
    isAdmin:{
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      activeElement: this.activeVideo || 0
    }
  },
  watch: {
    activeElement(data) {
      if(this.disabled) return;
      this.$emit('activeEl', {id:this.videos[data].videoblock_id, active: data})
    }
  },
  methods: {
    /**
   * переключение активного видоса
   * @param el
   */
    activeEl(el) {
      if(typeof el === 'number') this.activeElement = el;
      else {
        if(el==='next') this.activeElement === this.videos.length-1 ? this.activeElement = 0 : this.activeElement++;
        else if(el === 'back') this.activeElement === 0 ? this.activeElement = this.videos.length-1 : this.activeElement--
      }
    }
  }
}
</script>

<style lang="scss">
.slider-videos {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 26px;
  &__navigation {
    width: 644px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    &-back, &-next {
      width: 25%;
      height: 100%;
      display: flex;
      align-items: center;
      cursor: pointer;
      &--disabled{
        opacity: 0.5;
      }
      &-icon {
        width: 48px;
        min-width: 48px;
        min-height: 48px;
        height: 48px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        background: #51565F;
      }
      > span {
        font-weight: normal;
        font-size: 16px;
        line-height: 24px;
        letter-spacing: 0.02em;
        color: #FFFFFF;
      }
    }
    &-back {
      justify-content: flex-start;
      &-icon {
        margin-right: 16px;
      }
    }
    &-next {
      justify-content: flex-end;
      span {
        margin-right: 16px;
      }
    }
    &-nav {
      width: 50%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      &__el {
        width: 12px;
        height: 12px;
        background:#AAB1BA;
        border-radius: 50%;
        cursor: pointer;
        transition: .2s ease-in-out;
        &:not(:last-child) {
          margin-right: 12.5px;
        }
      }
      .activeEl {
        border: 2px solid #AAB1BA;
        width: 14px !important;
        height: 14px !important;
        background: #D4E3FB !important;
      }
    }
  }
  &__settings {
    display: flex;
    cursor: pointer;
    text-decoration: none;
    color: transparent;
    background: transparent;
    margin-left: 26px;
  }
}
@media(max-width: 768px) {
  .slider-videos {
    padding: 0 13px;
    &__navigation {
      width: 100%;
      &-nav .activeEl {
        border-color: #1F8EFA;
      }
    }
    &__navigation-back > span,
    &__navigation-next > span {
      font-size: 12px;
      line-height: 12px;
      margin: 0;
    }
    &__navigation-back-icon {
      margin-right: 8px;
    }
    &__navigation-next-icon {
      margin-left: 8px;
    }
    &__navigation-back-icon,
    &__navigation-next-icon {
      min-width: 24px;
      width: 24px;
      min-height: 24px;
      height: 24px;
      & svg {
        width: 6px;
        height: 10px;
      }
    }
  }
}
</style>
