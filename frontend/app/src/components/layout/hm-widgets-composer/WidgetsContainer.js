import * as WidgetContainerType from "./WidgetContainerType";
import * as WidgetLayoutType from "./WidgetLayoutType";

let WidgetsContainer = class {
  constructor(widget) {
    this.widgets = [];
    this.type = null;

    this.addWidget(widget);
  }

  addWidget(widget) {
    if (!widget) {
      return false;
    }

    let newType = WidgetsContainer.cssClassForWidgetLayout(widget.layout);

    if (this.type === null) {
      this.type = newType;
    }

    if (newType === this.type) {
      this.widgets.push(widget);
      return true;
    }

    return false;
  }

  isEmpty() {
    return this.widgets.length === 0;
  }

  static cssClassForWidgetLayout(layout) {
    switch (layout) {
      case WidgetLayoutType.FULL:
        return WidgetContainerType.FULL_WIDTH;

      case WidgetLayoutType.WIDE:
      default:
        return WidgetContainerType.FLEX;
    }
  }
};

export default WidgetsContainer;
