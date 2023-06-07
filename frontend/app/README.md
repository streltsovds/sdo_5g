# Фронтенд eLs 5.0

Клиентская часть проекта, написана с использованием Vuejs.

## ! Совместимость !

`sortablejs > 1.7.0` вызывает проблемы с drag'n'drop плагина `vuedraggable` в компоненте `TestClassification` в тестах с мобильным приложением (apache in-app plugin) на iOS.

Upd: `vuedraggable` был обновлён для #32524. Старая версия тестов изолирована в ветку `origin/project/danone/5g_for_hmtest`.

## Установка зависимостей

```bash
npm install
# или
yarn
```

## Команды для работы

### Разработка

```bash
npm run dev
# или
yarn dev
```

Эта команда запустит дев сервер вебпака по адресу http://localhost:9000/. Этот же адрес нужно указать в `applications/config.ini` в разделе [ development : production ] в `webpack.devserver.address`.

```ini
[ development : production ]
webpack.devserver.address = 'http://localhost:9000/'
```

Это даёт нам [Hot Module Replacement](https://webpack.js.org/concepts/hot-module-replacement/) от вебпака. Все изменения во Vue файлах добавлются на лету, что существенно увеличивает скорость разработки.

Если билд не перестраивается после изменения файлов и у вас Ubuntu, попробуйте выполнить 

`echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p`  

(см. https://stackoverflow.com/a/33537743). 

### Если source maps глючат

...и в DevTools браузера не поставить точку останова на нужной строке, запускать в режиме 

`npm run dev:modernCode` 

(только для новейших браузеров). Тогда сгенерированный код и source maps к нему будут проще и надёжнее.

``

### Production

```bash
npm run build
# или
yarn build
```

Эта команда запустит процесс сборки фронтенда. Включает в себя полную оптимизацию билда. Создает в `public/frontend/app` структуру:
```bash
├╴css
│  └╴стилевые файлы
└╴js
   └╴скрипты 
```

## Автодополнение в IDE

Для PhpStorm чтобы алиасы в начале import'ов работали, см. инструкцию в `webpack.autocomplete.js`, 

## Подсветка ошибок в IDE

Включите Eslint и Stylelint в настройках вашей IDE (может понадобиться указать Node interpreter)


## Как это работает?

Вэбпак собирает компоненты, транспайлит, минифицирует и отправляет все в `public/frontend/app/`.

## Используемые технологии/либы.

* [Vuetify.js](https://vuetifyjs.com/en/components/api-explorer) - библиотека компонентов следующая гайдлайнам Material Design.

* [Axios](https://github.com/axios/axios) - библиотека для связи с бэкендом по AJAX.
* [Moment.js](https://momentjs.com/) - библиотека для работы с датами/временем.
* [Locutus](http://locutus.io/) - набор полезных функций, которых нет в JS (например `strip_tags`).
* [d3](https://d3js.org/) - JavaScript библиотека для визуализации данных (графики).
* [lodash](https://lodash.com/docs/) для вспомогательных функций по работе со строками и коллекциями (см. также [сравнение с underscore.js](https://blog.semmle.com/lodash-vs-underscore/)).
* [hex-to-rgba](https://www.npmjs.com/package/hex-to-rgba) - добавить прозрачность в hex-цвет

### Выбор метода реализации повторяющихся алгоритмических задач

#### Итерация по объекту в js

Одним из самых быстрых оказался `lodash.forOwn()`.

https://www.measurethat.net/Benchmarks/Show/7600/0/loop-over-object-lodash-vs-objectentries-fork-by-d9k-ra 

#### Обеспечение уникальности значений в массиве

`lodash.uniq()` оказался быстрее `Set`.

https://www.measurethat.net/Benchmarks/Show/3889/0/lodash-uniq-vs-set

### Линтеры

В проекте настроен максимально подходящий ESLint конфиг с расчетом на работу в VSCode. Также дружит с Prettier.

```bash
npm run lint
# или
yarn lint
```

## Мобильное приложение

Перейти в `/fronted/app`

Разработка интерфейса:

`npm run dev:mobile`

Проверка на телефоне:

```
# npx cap add android

npm run build:mobile
npx cap copy
npx cap sync
```

## Архитектурная документация

* [Документация компонентов приложения](./src/components/README.md)

## Flex

[Интерактивная шпаргалка](https://demos.scotch.io/visual-guide-to-css3-flexbox-flexbox-playground/demos/)

[Расстояние между элементами](https://stackoverflow.com/a/48752954)

[Space-between: последняя строка по левому краю](https://stackoverflow.com/a/22018710)

[Вмещение img во flex](https://stackoverflow.com/questions/59193285/fitting-image-into-flex-direction-column-explanation-and-fixes#comment104611732_59195263)

## Передача данных из Php во Vue

url: `/demo/vue/data`

