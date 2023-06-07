import WidgetsContainer from "./WidgetsContainer";

let WidgetsToContainers = class {
  constructor() {
    this.widgets = [];
    this.containers = [];
    this.currentContainer = null;
  }

  run(widgets) {
    this.widgets = widgets;
    this.containers = [];
    this.newContainer();

    this.widgets.forEach(widget => {
      let wasAdded = this.addWidgetToCurrent(widget);

      if (!wasAdded) {
        this.pushContainer();
        this.newContainer(widget);
      }
    });
    this.pushContainer();

    return this.containers;
  }

  addWidgetToCurrent(widget) {
    return this.currentContainer.addWidget(widget);
  }

  newContainer(widget) {
    this.currentContainer = new WidgetsContainer(widget);
  }

  pushContainer() {
    if (this.currentContainer && !this.currentContainer.isEmpty()) {
      this.containers.push(this.currentContainer);
      this.newContainer();
    }
  }
};

export default WidgetsToContainers;
