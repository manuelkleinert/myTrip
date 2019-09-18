import {
  $,
  ajax,
  on,
  each,
  append,
  html,
  attr
} from 'uikit/src/js/util';

export default function MapStepList(args) {
  class StepList {
    constructor() {
      this.map = args.map;
      this.id = args.id;
      this.steps = [];

      this.list = $('#mt-step-list');

      this.loadSteps();
      on(this.map.getContainer(), 'add-step remove-step', this.loadSteps.bind(this));
    }

    loadSteps() {
      ajax('/ajax/load-step-list', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ id: this.id }),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200 && req.response.success) {
          console.log(req.response);
          this.steps = req.response.data;

          each(this.steps, (data) => {
            const stepElement = $('<li>');
            html(stepElement, data.title);
            attr(stepElement,'data-id', data.id);
            append(this.list, stepElement);
          });



          // console.log(this.geoJson.point);
          // const coordinates = this.geoJson.point.features.shift().geometry.coordinates;
          // const bounds = coordinates.reduce((bounds, coord) => {
          //   return bounds.extend(coord);
          // }, new mapboxgl.LngLatBounds(coordinates.shift(), coordinates.shift()));
          // this.map.fitBounds(bounds, { padding: 20 });
        }
      });
    }
  }
  return new StepList();
}
