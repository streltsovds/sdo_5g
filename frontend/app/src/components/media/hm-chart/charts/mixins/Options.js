class Options {
  constructor(options) {
    this.setOptions(options);
  }

  setOptions(options) {
    this.options = Options.getDefaultOptions();

    // TODO: Добавить валидацию опций и вывод ошибок
    //height
    if (options.height) this.setHeight(options.height);

    //width
    if (options.width) this.setWidth(options.width);

    //margin
    if (options.margin) this.setMargin(options.margin);

    //axisX
    if (options.axisX) this.setAxisX(options.axisX);

    //axisY
    if (options.axisY) this.setAxisY(options.axisY);

    //legend
    if (options.legend) this.setLegend(options.legend);

    //tooltip
    if (options.tooltip) this.setTooltip(options.tooltip);

    //label
    if (options.label) this.setLabel(options.label);

    //area
    if (options.area) this.setOptionsForArea(options.area);

    //line
    if (options.line) this.setOptionsForLine(options.line);

    //radar
    if (options.radar) this.setOptionsForRadar(options.radar);
  }

  setHeight(height) {
    this.options.height = height;
  }

  getHeight() {
    return this.options.height;
  }

  setWidth(width) {
    this.options.width = width;
  }

  getWidth() {
    return this.options.width;
  }

  setMargin(margin) {
    if (!isNaN(margin.top)) {
      this.options.margin.top = margin.top;
    }

    if (!isNaN(margin.right)) {
      this.options.margin.right = margin.right;
    }

    if (!isNaN(margin.bottom)) {
      this.options.margin.bottom = margin.bottom;
    }

    if (!isNaN(margin.left)) {
      this.options.margin.left = margin.left;
    }
  }

  getMargin() {
    return this.options.margin;
  }

  setAxisX(axisX) {
    if (axisX.text && axisX.text.rotate) {
      this.options.axisX.text.rotate = axisX.text.rotate;
    }

    if (axisX.text && axisX.text.wrap) {
      this.options.axisX.text.wrap = axisX.text.wrap;
    }
  }

  getAxisX() {
    return this.options.axisX;
  }

  setAxisY(axisY) {
    if (axisY.ticksCount > 0) {
      this.options.axisY.ticksCount = axisY.ticksCount;
    }
  }

  getAxisY() {
    return this.options.axisY;
  }

  setLegend(legend) {
    if (legend.show) {
      this.options.legend.show = legend.show;
    }
  }

  // tooltip
  setTooltip(tooltip) {
    if (tooltip.show) {
      this.options.tooltip.show = tooltip.show;
    }

    if (tooltip.content) {
      this.options.tooltip.content = tooltip.content;
    }
  }

  getTooltip(d) {
    return this.parseTextForReplaceTemplateOnValue(
      this.options.tooltip.content,
      d
    );
  }

  tooltipIsShow() {
    return this.options.tooltip.show;
  }

  setLabel(label) {
    if (label.inner && label.inner.show) {
      this.options.label.inner.show = label.inner.show;
    }

    if (label.inner && label.inner.content) {
      this.options.label.inner.content = label.inner.content;
    }

    if (label.outer && label.outer.show) {
      this.options.label.outer.show = label.outer.show;
    }

    if (label.outer && label.outer.value) {
      if (label.outer.value.show) {
        this.options.label.outer.value.show = label.outer.value.show;
      }

      if (label.outer.value.content) {
        this.options.label.outer.value.content = label.outer.value.content;
      }
    }
  }

  parseTextForReplaceTemplateOnValue(text, d) {
    return text.replace(/(\s\S)*\[\[([\s\S]*?)\]\](\s\S)*?/g, function(
      ...groups
    ) {
      return `${d[groups[2].trim()]} `;
    });
  }

  getOuterLabel() {
    return this.options.label.outer;
  }

  getOuterLabelContent(d) {
    d.value = (d.value ^ 0) === d.value ? d.value : d.value.toFixed(1);  // добавлено округление до десятых, если число не целое
    return this.parseTextForReplaceTemplateOnValue(
      this.getOuterLabel().value.content,
      d
    );
  }

  outerLabelIsShow() {
    return this.getOuterLabel().show;
  }

  getInnerLabel() {
    return this.options.label.inner;
  }

  innerLabelIsShow() {
    return this.getInnerLabel().show;
  }

  setOptionsForArea(area) {
    if (area.fill) {
      this.options.area.fill = area.fill;
    }
  }

  getArea() {
    return this.options.area;
  }

  setOptionsForLine(line) {
    if (line.fill) {
      this.options.line.fill = line.fill;
    }

    if (line.stroke && line.stroke.fill) {
      this.options.line.dots.fill = line.dots.fill;
    }

    if (line.stroke && line.stroke.width) {
      this.options.line.stroke.width = line.stroke.width;
    }

    if (line.dots && line.dots.fill) {
      this.options.line.dots.fill = line.dots.fill;
    }

    if (line.dots && line.dots.radius) {
      this.options.line.dots.radius = line.dots.radius;
    }
  }

  getLine() {
    return this.options.line;
  }
  setOptionsForRadar(radar) {
    if (radar.ticks) {
      this.options.radar.ticks = radar.ticks;
    }

    if (radar.factor) {
      this.options.radar.factor = radar.factor;
    }

    if (radar.factorLegend) {
      this.options.radar.factorLegend = radar.factorLegend;
    }

    if (radar.maxValue) {
      this.options.radar.maxValue = radar.maxValue;
    }

    if (radar.stroke && radar.stroke.width) {
      this.options.radar.stroke.width = radar.stroke.width;
    }

    if (radar.dots && radar.dots.radius) {
      this.options.radar.dots.radius = radar.dots.radius;
    }
  }

  getRadar() {
    return this.options.radar;
  }

  static getDefaultOptions() {
    return {
      height: 400,
      width: 400,
      margin: {
        top: 25,
        right: 25,
        bottom: 25,
        left: 25
      },
      legend: {
        show: false
      },
      tooltip: {
        show: true,
        content: "[[ value ]]"
      },
      label: {
        inner: {
          show: false,
          content: d => d.value
        },
        outer: {
          show: false,
          title: {
            show: false
          },
          value: {
            show: false,
            content: d => d.value
          }
        }
      },
      axisX: {
        text: {
          rotate: false,
          wrap: false
        }
      },
      axisY: {
        ticksCount: 5
      },
      area: {
        fill: "#666"
      },
      line: {
        stroke: {
          fill: "#666",
          width: 1
        },
        dots: {
          fill: "#333",
          radius: 2
        }
      },
      radar: {
        factor: 1,
        maxValue: 0,
        stroke: {
          width: 1
        },
        dots: {
          radius: 2
        }
      }
    };
  }
}

export default Options;
