# Vuex store

 ## Зачем?

Vuex — это паттерн управления состоянием и библиотека для приложений на Vue.js. Он служит центральным хранилищем данных для всех компонентов приложения и обеспечивает предсказуемость изменения данных при помощи определённых правил. Кроме того, Vuex интегрируется с [официальным расширением инструментов разработчика](https://github.com/vuejs/vue-devtools) Vue, предоставляя "из коробки" такие продвинутые возможности, как "time travel" при отладке и экспорт/импорт слепков состояния данных.
_Если вы знакомы с Redux, Flux и т.д. - это всё о том же._

## Структура

Все разделено на модули. Если для какого-то компонента вам нужна работа с Vuex - создайте для него отдельный модуль.

```js
moduleA = {
  namespaced: true,
  state: {
    // some state
  },
  actions: {
    // some actions
  },
  mutations: {
    // some mutations
  },
  getters: {
    // some getters
  }
}
```

Модули лежат в отдельных папках в папке `/modules`
```bash
module_name
├╴actions
│  └╴index.js # ваши actions
├╴getters
│  └╴index.js # ваши getters
├╴mutations
│  └╴index.js # ваши mutations
├╴state
│  └╴index.js # состояние модуля
└╴index.js # здесь импортируем всё что выше, 
           # и экспортируем весь модуль
```

Подробнее в официальной документации:

* [Модули](https://vuex.vuejs.org/ru/guide/modules.html)
* [Состояния](https://vuex.vuejs.org/ru/guide/state.html)
* [Геттеры](https://vuex.vuejs.org/ru/guide/getters.html)
* [Мутации](https://vuex.vuejs.org/ru/guide/mutations.html)
* [Экшены](https://vuex.vuejs.org/ru/guide/actions.html)
  - в том числе [mapActions()](https://vuex.vuejs.org/guide/actions.html#dispatching-actions-in-components)
