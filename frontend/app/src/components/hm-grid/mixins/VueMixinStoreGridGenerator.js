import { merge } from "lodash";
// import { createNamespacedHelpers } from "vuex";
import { mapState } from "vuex";
import { keyBy as lodashKeyBy } from "lodash";

export function getDefaultOptions() {
  return {
    /**
     * Имя модуля grid в vuex store
     * Можно использовать уже существующее в объекте данное/свойство/вычислимое, не создавая новое
     */
    moduleNameProperty: "$storeGrid_moduleName",

    mapStateToComputed: {},

    // array|object
    // mapGettersToComputed: [],
  };
}

function VueMixinStoreGridGenerator(options = {}) {
  let defaultOpts = getDefaultOptions();

  let createModuleNameProperty = !options.moduleNameProperty;

  let opts = merge({}, defaultOpts, options);

  let computedStoreStateMappers = {};
  let computedStoreGetters = {};

  if (opts.mapStateToComputed) {
    // const { mapState } = createNamespacedHelpers(
    //   this[opts.moduleNameProperty]
    // );

    let namespacedMappers = {};

    for (const [name, f] of Object.entries(opts.mapStateToComputed)) {
      namespacedMappers[name] = function (state) {
        let storeModuleName = this[opts.moduleNameProperty];
        return f(state[storeModuleName]);
      };
    }

    // https://stackoverflow.com/a/55947980
    computedStoreStateMappers = mapState(namespacedMappers);
  }

  if (opts.mapGettersToComputed) {
    // ensureObject
    if (Array.isArray(opts.mapGettersToComputed)) {
      opts.mapGettersToComputed = lodashKeyBy(
        opts.mapGettersToComputed,
        val => val
      );
    }

    for (const [computedName, getterName] of Object.entries(opts.mapGettersToComputed)) {
      computedStoreGetters[computedName] = function() {
        let storeModuleName = this[opts.moduleNameProperty];
        let namespacedGetterName = storeModuleName + "/" + getterName;
        return this.$store.getters[namespacedGetterName];
      };
    }
  }

  return {
    data() {
      let result = {};

      if (createModuleNameProperty) {
        result[defaultOpts.moduleNameProperty] = "HmGrid-undefined";
      }

      return result;
    },
    created() {
      // console.log("VueMixinStoreGridGenerator: created()");

      // if (opts.bindStateToComputed) {
      //   this.$storeGrid_composeComputed(opts.bindStateToComputed);
      // }
    },
    computed: {
      ...computedStoreStateMappers,
      ...computedStoreGetters,
    },

    methods: {
      /**
       * Для текущего модуля грида
       */
      $storeGrid_namespacedName(mutationOrActionName) {
        let storeModuleName = this[opts.moduleNameProperty];
        return storeModuleName + "/" + mutationOrActionName;
      },

      $storeGrid_commit(gridMutationName, payload, options) {
        let mutationName = this.$storeGrid_namespacedName(gridMutationName);
        this.$store.commit(mutationName, payload, options);
      },

      $storeGrid_dispatch(gridActionName, payload, options) {
        let actionName = this.$storeGrid_namespacedName(gridActionName);
        this.$store.dispatch(actionName, payload, options);
      },

      // $storeGrid_composeComputed(namedStateMappers) {
      //   const { mapState } = createNamespacedHelpers(
      //     this[opts.moduleNameProperty]
      //   );
      //
      //   this.$composeComputed(mapState(namedStateMappers));
      // },
    },
  };
}

export default VueMixinStoreGridGenerator;
