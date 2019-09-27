import {
  $,
  ajax,
  on,
  each,
  append,
  html,
  attr,
  empty,
  scrollTop, removeClass, addClass,
} from 'uikit/src/js/util';

export default function MapStepList(args) {
  class StepList {
    constructor() {
      this.map = args.map;
      this.id = args.id;
      this.steps = [];

      this.list = $('#mt-step-list');

      this.loadSteps();

      this.map.on('mousemove', this.getPointInArea.bind(this));
      this.map.on('touchmove', this.getPointInArea.bind(this));

      on(this.map.getContainer(), 'add-step remove-step', this.loadSteps.bind(this));
    }

    getPointInArea() {
      const selectArea = [[0, 0], [window.innerWidth, window.innerHeight]];
      const features = this.map.queryRenderedFeatures(selectArea, { layers: ['points'] });

      removeClass($('li', this.list), 'active');

      if (features.length > 0) {
        const nextPoint = features.shift();
        if (nextPoint && nextPoint.properties != null) {
          const activeItem = $(`li[data-id="${nextPoint.properties.id}"]`);
          addClass(activeItem, 'active');
          scrollTop(this.list, activeItem.offsetTop);
        }
      }
    }

    loadSteps() {
      ajax('/ajax/load-step-list', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ id: this.id }),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200 && req.response.success) {
          this.steps = req.response.data;
          empty(this.list);
          each(this.steps, (data) => {
            const stepElement = $('<li>');
            html(stepElement, data.title);
            attr(stepElement, 'data-id', data.id);
            append(this.list, stepElement);
          });

          UIkit.scrollspyNav(this.list, { cls: 'li', y: [-100, 100], scale: [0.8, 1, 0.8] });
        }
      });
    }
  }
  return new StepList();
}
