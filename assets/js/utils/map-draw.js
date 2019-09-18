import { ajax, on } from 'uikit/src/js/util';

export default function MapDraw(args) {
  class Draw {
    constructor() {
      this.map = args.map;
      this.id = args.id;
      this.accessToken = args.accessToken;
      this.geoJson = [];

      this.loadGeoJson();
      on(this.map.getContainer(), 'add-step remove-step', this.loadGeoJson.bind(this));
    }

    loadGeoJson() {
      ajax('/ajax/load-geo-json', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ id: this.id }),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200 && req.response.success) {
          this.geoJson = req.response.geoJson;
          this.drawLine();
          this.drawPoints();

          // console.log(this.geoJson.point);
          // const coordinates = this.geoJson.point.features.shift().geometry.coordinates;
          // const bounds = coordinates.reduce((bounds, coord) => {
          //   return bounds.extend(coord);
          // }, new mapboxgl.LngLatBounds(coordinates.shift(), coordinates.shift()));
          // this.map.fitBounds(bounds, { padding: 20 });
        }
      });
    }

    drawPoints() {
      if (this.map.getSource('points')) {
        this.map.getSource('points').setData(this.geoJson.point);
      } else {
        this.map.addLayer({
          id: 'points',
          type: 'circle',
          source: {
            type: 'geojson',
            data: this.geoJson.point,
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
      if (this.geoJson.line) {
        if (this.map.getSource('lines')) {
          this.map.getSource('lines').setData(this.geoJson.line);
        } else {
          this.map.addLayer({
            id: 'lines',
            type: 'line',
            source: {
              type: 'geojson',
              data: this.geoJson.line,
            },
            layout: {
              'line-join': 'round',
              'line-cap': 'round',
            },
            paint: {
              'line-color': ['get', 'color'],
              'line-width': 2,
            },
          });
        }
      }
    }
  }
  return new Draw();
}
