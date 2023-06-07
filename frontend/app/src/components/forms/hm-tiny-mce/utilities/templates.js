const templates = [
  {
    title: "Две адаптивные колонки",
    description:
      "Шаблон для размещения контента в две колонки с учетом адаптивности",
    content: `
      <div class="container">
        <div class="layout row wrap">
          <div class="flex sm6 xs12"><p>Первая колонка</p></div>
          <div class="flex sm6 xs12"><p>Вторая колонка</p></div>
        </div>
      </div>
    `
  },
  {
    title: "Три адаптивные колонки",
    description:
      "Шаблон для размещения контента в две колонки с учетом адаптивности",
    content: `
      <div class="container">
        <div class="layout row wrap">
          <div class="flex sm4 xs12"><p>Первая колонка</p></div>
          <div class="flex sm4 xs12"><p>Вторая колонка</p></div>
          <div class="flex sm4 xs12"><p>Третья колонка</p></div>
        </div>
      </div>
    `
  }
];

export default templates;
