<template>
  <div class="pa-2">
    <div
      ref="imageContainer"
      :class="{
        'image-container': true,
        'is-hovering': isHoveringOverAnswer
      }"
      :style="{
        // чтобы предотвратить relayout в браузере
        minHeight: configKonvaStage.height
          ? `${configKonvaStage.height}px`
          : null
      }"
    >
      <div class="image-load-progress" v-show="isImageLoading">
        <v-alert class="image-loading-alert"
                 :value="true"
                 outlined
                 type="info"
        >
          <v-progress-circular
            class="mr-3"
            indeterminate
            color="primary"
          />
          Загрузка изображения...
        </v-alert>
      </div>
      <v-stage v-if="!isImageLoading" :config="configKonvaStage">
        <v-layer>
          <v-image
            :config="{
              image,
              // разрешаем скроллировать контейнер
              preventDefault: false,
              // не слушаем события чтобы улучшить производительность
              listening: false
            }"
          />
          <template v-for="{ data, question_variant_id } in answers">
            <component
              class="image-konva-element"
              :is="`v-${getKonvaElType(getParsedAnswerData(data).type)}`"
              :key="question_variant_id"
              :config="getKonvaElConfig(data, question_variant_id)"
              @mouseenter="
                e => {
                  handleKonvaElMouseEnter(e, question_variant_id);
                }
              "
              @mouseout="
                e => {
                  handleKonvaElMouseOut(e, question_variant_id);
                }
              "
              @click="handleKonvaElClickAndTap(question_variant_id)"
              @tap="handleKonvaElClickAndTap(question_variant_id)"
            />
          </template>
        </v-layer>
      </v-stage>
    </div>
    <div
      class="pb-2 pt-2 image-variants"
      v-if="!isImageLoading && showVariants"
    >
      <template v-for="(answer, i) in answers">
        <v-checkbox
          :key="answer.question_variant_id"
          :class="i !== answers.length - 1 ? 'ma-0 mb-1' : 'ma-0'"
          v-model="select[answer.question_variant_id]"
          hide-details
        >
          <!-- eslint-disable -->
          <div slot="label" v-if="variantsAsHtml" class="checkbox__label variant-with-html-markup" v-html="answer.variant" />
          <!-- eslint-enable -->
          <div class="checkbox__label" slot="label" v-else>
            {{ answer.variant }}
          </div>
        </v-checkbox>
        <v-divider
          class="mb-2"
          v-if="i !== answers.length - 1"
          :key="i"
        />
      </template>
    </div>
  </div>
</template>

<script>
import Vue from "vue";
import VueKonva from "vue-konva";

Vue.use(VueKonva);

// Эта константа задана в конструкторе
// упражнения такого типа
const MAX_WIDTH = 800;

const ZERO_OPACITY = 0;
const HALF_OPACITY = 0.5;
const FULLY_SHOWN = 1;
const FILL_COLOR = "rgba(255, 255, 255, 0.3)";
const STROKE_COLOR = "rgb(230, 93, 42)";
const STROKE_WIDTH = 2;

const konvaElType = {
  LINE: "line",
  CIRCLE: "circle",
  ELLIPSE: "ellipse",
  RECT: "rect"
};

const recievedElType = {
  RECT: "rect",
  SQUARE: "square",
  CIRCLE: "circle",
  ELLIPSE: "ellipse",
  POLYGON: "polygon"
};

const getKonvaElType = (type = recievedElType.RECT) => {
  // мапим типы элементов с бэкенда
  // на типы которы понимает Konva
  switch (type) {
    case recievedElType.RECT:
    case recievedElType.SQUARE:
      return konvaElType.RECT;
    case recievedElType.CIRCLE:
      return konvaElType.CIRCLE;
    case recievedElType.ELLIPSE:
      return konvaElType.ELLIPSE;
    case recievedElType.POLYGON:
      return konvaElType.LINE;
  }
};

const getParsedAnswerData = encodedData => JSON.parse(encodedData);

const getLineElPoints = pathArray => {
  // Массив точек с бэкенда нужен для рисования <path />
  // но в Konva нам просто нужен массив координат вершин
  // многоугольника
  return pathArray.reduce((acc, pathArrayEl) => {
    pathArrayEl.forEach(element => {
      if (!isNaN(element)) {
        acc.push(element);
      }
    });
    return acc;
  }, []);
};

const getImageDimensions = (width, height) => {
  // получаем соотношение сторон картинки
  // чтобы правильно её уменьшить
  const ratio = width / height;
  // превышают ли размеры картинки максимальную ширину,
  const isImageWidthOverMaxWidth = width > MAX_WIDTH;
  // возвращаем уже сконвертированные размеры
  return {
    width: isImageWidthOverMaxWidth ? MAX_WIDTH : width,
    height: isImageWidthOverMaxWidth ? MAX_WIDTH / ratio : height
  };
};

export default {
  props: {
    answers: {
      type: Array,
      default: () => []
    },
    selectedAnswer: {
      type: Array,
      default: () => []
    },
    highlight: {
      type: Boolean,
      default: false
    },
    fileId: {
      type: Number,
      default: null
    },
    variantsAsHtml: Boolean,
    showVariants: Boolean
  },
  data() {
    return {
      configKonvaStage: {},
      isHoveringOverAnswer: false,
      isImageLoading: true,
      // getFileUrl: `/file/get/file/file_id`,
      getFileUrl:`/file/get/file/file_id`,
      select: {},
      image: null,
      selectedAnswerWasInit: false
    };
  },
  computed: {
    url() {
      return `${this.getFileUrl}/${this.fileId}`;
    },
    result() {
      let res = [];
      for (let prop in this.select) {
        if (!this.select.hasOwnProperty(prop)) continue;

        if (this.select[prop]) res.push(+prop);
      }
      return res;
    },
    imageContainerWidth() {
      return this.$refs.imageContainer.offsetWidth;
    },
    maxDragX() {
      return this.configKonvaStage.width - this.imageContainerWidth;
    },
    initialKonvaElOpacity() {
      // Если мы на мобилке то элемент не скрываем полностью
      return this.$vuetify.breakpoint.smAndDown ? HALF_OPACITY : ZERO_OPACITY;
    }
  },
  watch: {
    result() {
      this.sendAnswer();
    },
    selectedAnswer(selectedAnswer) {
      if (this.selectedAnswerWasInit) return;
      this.selectedAnswerWasInit = true;
      this.$nextTick(() => {
        this.toggleCheckboxs(selectedAnswer.map(item => +item));
      });
    }
  },
  mounted() {
    this.init();
    this.loadImage();
  },
  methods: {
    getKonvaElType,
    getParsedAnswerData,
    handleKonvaElClickAndTap(id) {
      this.$nextTick().then(() => {
        this.toggleVariant(id);
      });
    },
    handleKonvaElMouseEnter(event, id) {
      // Включаем курсор
      this.isHoveringOverAnswer = true;
      // Если мы уже выбрали этот ответ и он подсвечен
      // То не надо изменять прозрачность
      if (this.select[id]) return;

      const { target } = event;

      this.$nextTick()
        .then(() => {
          target.opacity(HALF_OPACITY);
        })
        .then(() => {
          // Обновляем только сам элемент
          target.draw();
        });
    },
    handleKonvaElMouseOut(event, id) {
      // Выключаем курсор
      this.isHoveringOverAnswer = false;
      // Если мы уже выбрали этот ответ и он подсвечен
      // То не надо изменять прозрачность
      if (this.select[id]) return;

      const { target } = event;
      const newOpacity = this.initialKonvaElOpacity;
      this.$nextTick()
        .then(() => {
          target.opacity(newOpacity);
        })
        .then(() => {
          // update Layer
          target.getLayer().batchDraw();
        });
    },
    getKonvaElConfig(data, id) {
      const { width, height, x, y, cy, cx, r, rx, ry, path, type } = getParsedAnswerData(data);
      // если элемент уже выбран то он полностю показан
      const opacity = this.select[id]
        ? FULLY_SHOWN
        : this.initialKonvaElOpacity;
      // основные свойства элементов
      const defaultConfig = {
        fill: FILL_COLOR,
        stroke: STROKE_COLOR,
        strokeWidth: STROKE_WIDTH,
        perfectDrawEnabled: false,
        shadowForStrokeEnabled: false,
        opacity
      };
      const konvaType = getKonvaElType(type);
      // добавляем свойства, зависящие от типа
      // и возвращаем
      switch (konvaType) {
        case konvaElType.RECT:
          return {
            x,
            y,
            width,
            height,
            ...defaultConfig
          };
        case konvaElType.CIRCLE:
          return {
            x: cx,
            y: cy,
            radius: r,
            ...defaultConfig
          };
        case konvaElType.ELLIPSE:
          return {
            x: cx,
            y: cy,
            radius: {
              x: rx,
              y: ry
            },
            ...defaultConfig
          };
        case konvaElType.LINE:
          return {
            points: getLineElPoints(path),
            closed: true,
            ...defaultConfig
          };
      }
    },
    init() {
      for (const answer of this.answers) {
          this.$set(this.select, answer.question_variant_id, false);
      }
    },
    loadImage() {
      let img = new Image();
      img.onload = () => {
        const { width, height } = getImageDimensions(img.width, img.height);
        this.$nextTick()
          .then(() => {
            this.configKonvaStage = {
              width,
              height,
              ...this.configKonvaStage
            };
            // устанавливаем картинке теже размеры
            img.width = width;
            img.height = height;
          })
          .then(() => {
            // записываем объект картинки для Konva
            this.image = img;
          })
          .then(() => {
            // Отключаем спиннер загрузки
            this.isImageLoading = false;
          });
      };

      // Загружаем картинку!
      this.$nextTick().then(() => {
        img.src = this.url;
      });
    },
    toggleVariant(id) {
      this.$nextTick().then(() => {
        this.select[id] = !this.select[id];
      });
    },
    toggleCheckboxs(selectedCheckboxs) {
      for (let key in this.select) {
        if (!this.select.hasOwnProperty(key)) continue;
        this.$set(this.select, key, selectedCheckboxs.includes(+key));
      }
    },
    sendAnswer() {
      this.$nextTick(() => {
        this.$emit("hm:test:answer-chosen", this.result);
      });
    }
  }
};
</script>

<style lang="scss" scoped>
  .image-container {
    width: 100%;
    height: auto;
    overflow-x: auto;
    &.is-hovering {
      cursor: pointer;
    }
  }
  .image-load-progress {
    width: 100%;
    height: 400px;
    display: flex;
    position: relative;
    & .image-loading-alert {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
    }
    align-items: center;
    justify-content: center;
  }
</style>
<style lang="scss">
  .image-loading-alert .v-alert__icon {
    display: none !important;
  }
</style>
