AmCharts.themes.SGK = {
  themeName: 'SGK',

  AmChart: {
    color: '#000000',
    backgroundColor: 'transparent',
  },

  AmCoordinateChart: {
    colors: [
      'rgb(38, 125, 188)',
      'rgb(21, 68, 102)',
      'rgb(60, 151, 215)',
      'rgb(103, 173, 224)',
      'rgb(145, 196, 232)',
    ],
  },

  AmStockChart: {
    colors: [
      'rgb(38, 125, 188)',
      'rgb(21, 68, 102)',
      'rgb(60, 151, 215)',
      'rgb(103, 173, 224)',
      'rgb(145, 196, 232)',
    ],
  },

  //   AmPieChart: {
  //     outlineAlpha: 1,
  //     outlineColor: '#505050',
  //     outlineThickness: 5,
  //   },

  AmSlicedChart: {
    balloonText:
      "[[title]]:<br><span style='font-size:14px'><b>[[value]]</b></span>",
    depth3D: 12,
    angle: 0,
    colors: [
      'rgb(38, 125, 188)',
      'rgb(21, 68, 102)',
      'rgb(60, 151, 215)',
      'rgb(103, 173, 224)',
      'rgb(145, 196, 232)',
    ],
    startDuration: 0.2,
    sequencedAnimation: false,
    innerRadius: '32%',
    labelText: '[[percents]]% [[title]]',
    gradientRatio: [-0.1, 0.1, 0.1, -0.1],
    baseColor: '#1D6090',
    hoverAlpha: 1,
    alpha: 0.9,
    marginTop: 0,
    pullOutEffect: 'bounce',
    pullOutOnlyOne: true,
    pullOutDuration: 0.2,
    outlineAlpha: 1,
    outlineColor: '#fff',
    marginLeft: 0,
    marginRight: 0,
    outlineThickness: 10,
    percentPrecision: 0,
    startEffect: 'easeInSine',
    startRadius: '100%',
    labelTickColor: '#000000',
    labelTickAlpha: 0.3,
    legend: {
      enabled: true,
      color: '#000000',
      equalWidths: false,
      fontSize: 14,
      markerSize: 30,
      markerType: 'square',
      rollOverColor: '#FFFFFF',
      valueText: '',
      valueWidth: 1,
    },
  },

  AmRectangularChart: {
    zoomOutButtonColor: '#000000',
    zoomOutButtonRollOverAlpha: 0.15,
    zoomOutButtonImage: 'lens',
  },

  AxisBase: {
    axisColor: '#000000',
    axisAlpha: 0.3,
    gridAlpha: 0.1,
    gridColor: '#000000',
  },

  ChartScrollbar: {
    backgroundColor: '#000000',
    backgroundAlpha: 0.12,
    graphFillAlpha: 0.5,
    graphLineAlpha: 0,
    selectedBackgroundColor: '#FFFFFF',
    selectedBackgroundAlpha: 0.4,
    gridAlpha: 0.15,
  },

  ChartCursor: {
    cursorColor: '#000000',
    color: '#FFFFFF',
    cursorAlpha: 0.5,
  },

  AmLegend: {
    color: '#000000',
  },

  AmGraph: {
    lineAlpha: 0.9,
  },
  GaugeArrow: {
    color: '#000000',
    alpha: 0.8,
    nailAlpha: 0,
    innerRadius: '40%',
    nailRadius: 15,
    startWidth: 15,
    borderAlpha: 0.8,
    nailBorderAlpha: 0,
  },

  GaugeAxis: {
    tickColor: '#000000',
    tickAlpha: 1,
    tickLength: 15,
    minorTickLength: 8,
    axisThickness: 3,
    axisColor: '#000000',
    axisAlpha: 1,
    bandAlpha: 0.8,
  },

  TrendLine: {
    lineColor: '#c03246',
    lineAlpha: 0.8,
  },

  // ammap
  AreasSettings: {
    alpha: 0.8,
    color: '#67b7dc',
    colorSolid: '#003767',
    unlistedAreasAlpha: 0.4,
    unlistedAreasColor: '#000000',
    outlineColor: '#FFFFFF',
    outlineAlpha: 0.5,
    outlineThickness: 0.5,
    rollOverColor: '#3c5bdc',
    rollOverOutlineColor: '#FFFFFF',
    selectedOutlineColor: '#FFFFFF',
    selectedColor: '#f15135',
    unlistedAreasOutlineColor: '#FFFFFF',
    unlistedAreasOutlineAlpha: 0.5,
  },

  LinesSettings: {
    color: '#000000',
    alpha: 0.8,
  },

  ImagesSettings: {
    alpha: 0.8,
    labelColor: '#000000',
    color: '#000000',
    labelRollOverColor: '#3c5bdc',
  },

  ZoomControl: {
    buttonFillAlpha: 0.7,
    buttonIconColor: '#a7a7a7',
  },

  SmallMap: {
    mapColor: '#000000',
    rectangleColor: '#f15135',
    backgroundColor: '#FFFFFF',
    backgroundAlpha: 0.7,
    borderThickness: 1,
    borderAlpha: 0.8,
  },

  // the defaults below are set using CSS syntax, you can use any existing css property
  // if you don't use Stock chart, you can delete lines below
  PeriodSelector: {
    color: '#000000',
  },

  PeriodButton: {
    color: '#000000',
    background: 'transparent',
    opacity: 0.7,
    border: '1px solid rgba(0, 0, 0, .3)',
    MozBorderRadius: '5px',
    borderRadius: '5px',
    margin: '1px',
    outline: 'none',
    boxSizing: 'border-box',
  },

  PeriodButtonSelected: {
    color: '#000000',
    backgroundColor: '#b9cdf5',
    border: '1px solid rgba(0, 0, 0, .3)',
    MozBorderRadius: '5px',
    borderRadius: '5px',
    margin: '1px',
    outline: 'none',
    opacity: 1,
    boxSizing: 'border-box',
  },

  PeriodInputField: {
    color: '#000000',
    background: 'transparent',
    border: '1px solid rgba(0, 0, 0, .3)',
    outline: 'none',
  },

  DataSetSelector: {
    color: '#000000',
    selectedBackgroundColor: '#b9cdf5',
    rollOverBackgroundColor: '#a8b0e4',
  },

  DataSetCompareList: {
    color: '#000000',
    lineHeight: '100%',
    boxSizing: 'initial',
    webkitBoxSizing: 'initial',
    border: '1px solid rgba(0, 0, 0, .3)',
  },

  DataSetSelect: {
    border: '1px solid rgba(0, 0, 0, .3)',
    outline: 'none',
  },
}
