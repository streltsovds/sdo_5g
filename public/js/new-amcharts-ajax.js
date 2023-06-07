function onResponse(chartData, chartGraphs, container, options) {
  if (chartData.length === 0 && container == 'analytics-chart-container') {
    $('div#analytics-chart-container')
      .html("<p id='no-data'>Нет данных для отображения</p>")
      .css({ position: 'relative' })
    $('p#no-data').css({
      color: 'red',
      'font-weight': 'bold',
      left: '45%',
      position: 'absolute',
      top: '50%',
    })
  } else {
    $(function() {
      var graphs = []

      for (prop in chartGraphs) {
        chartGraph = chartGraphs[prop]
        var obj = {
          title: chartGraph.legend,
          type: options.graphsType,
          lineColor: chartGraph.color,
          valueField: prop,
          lineAlpha: 0,
          fillAlphas: 0.8,
          balloonText: options.balloonText,
        }
        graphs.push(obj)
      }

      settings = {
        type: 'serial',
        categoryField: 'title',
        dataProvider: chartData,
        fontSize: 14,
        categoryAxis: {
          gridPosition: 'start',
          labelRotation: 0,
          fontSize: 10,
          color: '#565051',
          gridAlpha: 0,
          ignoreAxisWidth: false,
          autoWrap: true,
        },
        valueAxis: [
          {
            axisAlpha: 0.15,
            minimum: 0,
            maximum: 5,
            dashLength: 3,
          },
          {
            position: 'left',
            title: options.axisY,
          },
          {
            position: 'bottom',
            title: options.axisX,
          },
        ],
        balloon: {
          color: '#000000',
          cornerRadius: '4',
          borderWidth: '3',
          borderAlpha: '50',
          borderColor: '#DFCA51',
          alpha: '80',
        },
        graphs: graphs,
        legend: {
          enabled: options.legendEnabled,
          useGraphSettings: true,
          borderAlpha: 0.2,
          horizontalGap: 10,
          autoMargins: false,
          marginLeft: 20,
          marginRight: 20,
          marginBottom: 20,
          valueAlign: [[open]],
        },
        chartCursor: {
          cursorAlpha: 0,
          zoomable: false,
          categoryBalloonEnabled: false,
        },
      }

      if (options.graphsType && options.graphsType == 'line') {
        settings.balloon = options.settings.balloon
        settings.graphs.forEach(function(graph) {
          Object.assign(graph, options.settings.graphs.graph)
        })
        var chart = AmCharts.makeChart(container, settings)
      } else if (options.graphsType && options.graphsType == 'pie') {
        var settingsNew = {
          fontFamily: 'inherit',
          type: options.graphsType,
          titleField: 'category',
          valueField: 'column-1',
          dataProvider: chartData,
          balloon: {
            fixedPosition: false,
          },
          export: {
            enabled: false,
          },
          legend: {
            useGraphSettings: true,
          },
        }
        if (options.radius) settingsNew.radius = options.radius
        if (options.theme) settingsNew.theme = options.theme
        if (options.labelRadius) settingsNew.labelRadius = options.labelRadius
        if (options.angle) settingsNew.angle = options.angle
        if (options.depth3D) settingsNew.depth3D = options.depth3D
        if (options.innerRadius) settingsNew.innerRadius = options.innerRadius
        if (options.labelText) settingsNew.labelText = options.labelText
        if (options.balloon) settingsNew.balloon = options.balloon
        if (options.balloonText) settingsNew.balloonText = options.balloonText
        if (options.legend) settingsNew.legend = options.legend
        if (options.addClassNames) {
          settingsNew.addClassNames = options.addClassNames
        }

        var chart = AmCharts.makeChart(container, settingsNew)
          if(typeof chart !== "undefined")
          {
            if (hm.timesheet) {
                hm.timesheet.chart = chart;
            }

          }

        if (chartData.length === 0) {
          // sIeEf - это костыль для IE11 который запись вида
          // ('blabla',) считает что запятая синтаксическая ошибка, о боже
          var sIeEf = $('#top-subjects-chart-container')
          sIeEf.html('<p>Нет данных дпя отображения.</p>')
        }
      } else {
        var chart = AmCharts.makeChart(container, settings)
      }
    })
  }
}

function loadData(url, container, obj) {
  if (url == '') {
    url = window.location.href
  } else if (url == $('#resourcesBlock input.hasDatepicker')) {
    url = window.location.href
  }
  // if (obj === null) {
    data = $('#user_analytics_form :input').serializeArray()
  // } else {
  //   data = {
  //     key: $(obj).attr('name'),
  //     value: $(obj).val(),
  //   }
  // }
  // alert(Object.values(data));
  result = $.ajax({
    url: url,
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(response) {
      hm.core.Console.log(response)
      if (response) {
        var chartData = response.data ? response.data : []
        var chartGraphs = response.graphs
          ? response.graphs
          : { profile: { legend: 'нет данных', color: '#C759D2' } }
        var options = response.options
          ? response.options
          : {
              legendEnabled: 1,
              graphsType: 'column',
              settings: { balloon: {}, graphs: { graph: {} } },
            }
        onResponse(chartData, chartGraphs, container, options)
      }
    },
  })
}
