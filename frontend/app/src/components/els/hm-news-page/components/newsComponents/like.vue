<template>
    <!--<transition name="fadeMy">
      <v-btn class="nobubbling" key="hasLike" @click="performLike" v-if="likeData.count" icon :outline="!likeData.hasUserLike" color="primary">
        <v-badge color="success" left>
          <v-icon>
            thumb_up_alt
          </v-icon>
          <transition slot="badge" :name="likeData.hasUserLike ? 'slideUp' : 'slideDown'" mode="out-in">
            <span :key="data.count">
              {{ likesCount }}
            </span>
          </transition>
        </v-badge>
      </v-btn>
      <v-btn class="nobubbling" key="doesNothaveLike" icon color="primary" outline @click="performLike" v-else>
        <v-icon>
          thumb_up_alt
        </v-icon>
      </v-btn>
    </transition>-->
    <v-layout :style="$vuetify.breakpoint.xsOnly ? '' : 'padding-top: 15px;'">
        <div class="like" :class="likeData.hasUserLike ? 'liked' : 'no-like'" @click="performLike"></div>
        <transition :name="likeData.hasUserLike ? 'slideDown' : 'slideUp'" mode="out-in">
            <span :key="data.count" class="counter" :class="likeData.hasUserLike ? 'liked' : 'no-like'">
              {{ likesCount }}
            </span>
        </transition>
    </v-layout>
</template>

<script>
    export default {
        props: {
            data: Object
        },
        data() {
            return {
                likeData: this.data
            };
        },
        computed: {
            likesCount() {
                return this.likeData.count < 99 ? this.likeData.count : "99+";
            }
        },
        methods: {
            performLike() {
                if (this.likeData.hasUserLike) {
                    this.likeData.count--;
                    this.$emit("down");
                } else {
                    this.likeData.count++;
                    this.$emit("up");
                }
                this.likeData.hasUserLike = !this.likeData.hasUserLike;
            }
        }
    };
</script>

<style lang="scss">

    @import '../colors.scss';
    @import '../mixins.scss';

    .like {
        width: 16px;
        height: 14px;
        cursor: pointer;
        margin-right: 4px;
        @media (max-width: 600px) {
            margin-top: 3px;
            width: 14px;
            height: 12px;
            &.liked, &.no-like {
                background-size: contain;
            }
        }

        &.liked {
            background-image: url("/images/news/component/like.svg");
        }

        &.no-like {
            background-image: url("/images/news/component/no_like.svg");
        }
    }

    .counter {
        @include newsText(14px, normal, $black);
        &.liked {
            color: $red;
        }
        &.no-like {
            color: $black;
        }
    }

    @-webkit-keyframes slideInDown {
        from {
            -webkit-transform: translate3d(0, -100%, 0);
            transform: translate3d(0, -100%, 0);
            visibility: visible;
        }
        to {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes slideInDown {
        from {
            -webkit-transform: translate3d(0, -100%, 0);
            transform: translate3d(0, -100%, 0);
            visibility: visible;
        }
        to {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
    }

    @-webkit-keyframes slideOutDown {
        from {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
        to {
            visibility: hidden;
            -webkit-transform: translate3d(0, 100%, 0);
            transform: translate3d(0, 100%, 0);
        }
    }

    @keyframes slideOutDown {
        from {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
        to {
            visibility: hidden;
            -webkit-transform: translate3d(0, 100%, 0);
            transform: translate3d(0, 100%, 0);
        }
    }

    @-webkit-keyframes slideInUp {
        from {
            -webkit-transform: translate3d(0, 100%, 0);
            transform: translate3d(0, 100%, 0);
            visibility: visible;
        }
        to {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes slideInUp {
        from {
            -webkit-transform: translate3d(0, 100%, 0);
            transform: translate3d(0, 100%, 0);
            visibility: visible;
        }
        to {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
    }

    @-webkit-keyframes slideOutUp {
        from {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
        to {
            visibility: hidden;
            -webkit-transform: translate3d(0, -100%, 0);
            transform: translate3d(0, -100%, 0);
        }
    }

    @keyframes slideOutUp {
        from {
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
        }
        to {
            visibility: hidden;
            -webkit-transform: translate3d(0, -100%, 0);
            transform: translate3d(0, -100%, 0);
        }
    }

    .slideDown-enter-active,
    .slideInDown,
    .slideDown-leave-active,
    .slideOutDown {
        -webkit-animation-duration: 0.1s;
        animation-duration: 0.1s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .slideDown-enter-active,
    .slideInDown {
        -webkit-animation-name: "slideInDown";
        animation-name: "slideInDown";
    }

    .slideDown-leave-active,
    .slideOutDown {
        -webkit-animation-name: "slideOutDown";
        animation-name: "slideOutDown";
    }

    .slideUp-enter-active,
    .slideInUp,
    .slideUp-leave-active,
    .slideOutUp {
        -webkit-animation-duration: 0.1s;
        animation-duration: 0.1s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .slideUp-enter-active,
    .slideInUp {
        -webkit-animation-name: "slideInUp";
        animation-name: "slideInUp";
    }

    .slideUp-leave-active,
    .slideOutUp {
        -webkit-animation-name: "slideOutUp";
        animation-name: "slideOutUp";
    }

    @-webkit-keyframes fadeInMy {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes fadeInMy {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @-webkit-keyframes fadeOutMy {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    @keyframes fadeOutMy {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }

    .fadeMy-enter-active,
    .fadeMyIn {
        -webkit-animation-duration: 0.7s;
        animation-duration: 0.7s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .fadeMy-leave-active,
    .fadeMyOut {
        -webkit-animation-duration: 0.5s;
        animation-duration: 0.5s;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
    }

    .fadeMy-enter-active,
    .fadeMyIn {
        -webkit-animation-name: "fadeIn";
        animation-name: "fadeIn";
    }

    .fadeMy-leave-active,
    .fadeMyOut {
        -webkit-animation-name: "fadeOut";
        animation-name: "fadeOut";
    }
</style>
