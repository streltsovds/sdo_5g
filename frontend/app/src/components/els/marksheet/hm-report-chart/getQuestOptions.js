const getQuestReportChartOptions = (quest, id) => {
  const def = {
    chartOptions: {
      id: "question" + id,
      type: "pie",
      dataValue: "count",
      title: quest.title,
      labelText: "[[percents]]%",
      labelRadius: 10,
      hideLegend: false,
    },
    tableOptions: {
      dataTitle: "Вариант ответа",
      showTable: 1,
      procentColumn: true,
      procentColumnName: "Распределение",
      totalValue: quest.totalValue,
    },
    graphs: [],
  };
  let options = { ...def };
  switch (quest.type) {
    case "single":
    case "multiple":
    case "imagemap":
      options = {
        graphs: [
          ...options.graphs,
          {
            legend: "Количество",
          },
        ],
        chartOptions: {
          ...options.chartOptions,
          radius: 100,
          labelsEnabled: false,
        },
        tableOptions: {
          ...options.tableOptions,
        },
      };
      break;
    case "text":
    case "mapping":
    case "classification":
    case "sorting":
    case "placeholder":
      options = {
        graphs: [
          ...options.graphs,
          {
            legend: "Процент правильных",
          },
        ],
        chartOptions: {
          ...options.chartOptions,
          radius: 70,
          height: 250,
          colors: quest.colors,
        },
        tableOptions: {
          ...options.tableOptions,
          hideData: true,
        },
      };
      break;
  }

  return options;
};

export default getQuestReportChartOptions;
