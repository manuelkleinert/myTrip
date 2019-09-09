import { ajax, on } from 'uikit/src/js/util';

export default function MapDraw(args) {
  class Draw {
    constructor() {
      this.map = args.map;
      this.id = args.id;
      this.accessToken = args.accessToken;
      this.data = [];

      this.loadSteps();
      on(this.map.getContainer(), 'add-step remove-step', this.loadSteps.bind(this));
    }

    loadSteps() {
      ajax('/ajax/load-geo-json', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ id: this.id }),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200 && req.response.success) {
          this.data = req.response.data;
          this.drawLine();
          this.drawPoints();
        }
      });
    }

    drawPoints() {
      if (this.map.getSource('points')) {
        this.map.getSource('points').setData(this.data.point);
      } else {
        this.map.addLayer({
          id: 'points',
          type: 'circle',
          source: {
            type: 'geojson',
            data: this.data.point,
          },
          paint: {
            'circle-radius': ['get', 'radius'],
            'circle-color': '#ffffff',
            'circle-blur': 1,
            'circle-stroke-width': 2,
            'circle-stroke-color': '#ff0000',
          },
        });
      }
    }

    drawLine() {
      if (this.data.line) {
        if (this.map.getSource('lines')) {
          this.map.getSource('lines').setData(this.data.line);
        } else {
          this.map.addLayer({
            id: 'lines',
            type: 'line',
            source: {
              type: 'geojson',
              data: this.data.line,
            },
            layout: {
              'line-join': 'round',
              'line-cap': 'round',
            },
            paint: {
              'line-color': '#ff0000',
              'line-width': 2,
            },
          });
        }
      }
    }
  }
  return new Draw();
}
