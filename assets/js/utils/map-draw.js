import {
  ajax,
  each,
  attr,
  addClass,
  // on,
} from 'uikit/src/js/util';
import mapBoxGl from 'mapbox-gl';

export default function MapDraw(args) {
  class Draw {
    constructor() {
      this.map = args.map;
      this.id = args.id;
      this.accessToken = args.accessToken;
      this.steps = [];

      this.map.addLayer({
        id: 'test-line',
        type: 'line',
        source: {
          type: 'geojson',
          data: {
            type: 'FeatureCollection',
            features: [{
              type: 'Feature',
              properties: { },
              geometry: {
                type: 'LineString',
                coordinates: [
                  [8.310856819152832, 47.05108985312085],
                  [8.30390453338623, 47.04780754012035],
                ],
              },
            }],
          },
        },
        layout: {
          'line-join': 'round',
          'line-cap': 'round',
        },
        paint: {
          'line-color': '#ff0000',
          'line-width': 8,
        },
      });

      this.map.addLayer({
        id: 'fly',
        type: 'symbol',
        source: {
          type: 'geojson',
          data: {
            type: 'FeatureCollection',
            features: [{
              type: 'Feature',
              geometry: {
                type: 'Point',
                coordinates: [-77.03238901390978, 38.913188059745586],
              },
              properties: {
                title: 'Mapbox DC',
                icon: 'harbor',
              },
            }, {
              type: 'Feature',
              geometry: {
                type: 'Point',
                coordinates: [-122.414, 37.776],
              },
              properties: {
                title: 'Mapbox SF',
                icon: 'harbor',
              },
            }, {
              type: 'Feature',
              geometry: {
                type: 'Point',
                coordinates: [8.310341835021973, 47.05019070951365],
              },
              properties: {
                title: 'TEST',
                icon: 'harbor',
              },
            }],
          },
        },
        layout: {
          'icon-image': '{icon}-15',
          'text-field': '{title}',
          'text-font': ['Open Sans Semibold', 'Arial Unicode MS Bold'],
          'text-offset': [0, 0.6],
          'text-anchor': 'top',
        },
      });


      // this.loadSteps();
      // on(this.map.getContainer(), 'add-step', this.loadSteps.bind(this));
    }

    loadSteps() {
      ajax('/ajax/load-geo-json', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ id: this.id }),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200 && req.response.success) {
          // this.steps = req.response.data;
          // this.draw();
          console.log(req.response.data);
          this.map.addSource('test', req.response.data);
        }
      });
    }

    draw() {
      if (this.steps) {
        each(this.steps, (obj) => {
          console.log(obj);
          const el = document.createElement('div');
          addClass(el, 'step');
          attr(el, 'data-uk-icon', 'mt-car');
          new mapBoxGl.Marker(el).setLngLat([obj.lng, obj.lat]).addTo(this.map);
          this.getRoute(obj.id, [8.310473, 47.050052], [obj.lng, obj.lat]);
        });
      }
    }

    getRoute(id, start, end) {
      const routeId = 'route-' + id; // eslint-disable-line prefer-template
      const url = 'https://api.mapbox.com/directions/v5/mapbox/cycling/' + start[0] + ',' + start[1] + ';' + end[0] + ',' + end[1] + '?steps=true&geometries=geojson&access_token=' + this.accessToken; // eslint-disable-line prefer-template

      ajax(url, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200) {
          const data = req.response.routes[0]; // eslint-disable-line prefer-template
          const geoJson = {
            type: 'Feature',
            properties: {},
            geometry: {
              type: 'LineString',
              coordinates: data.geometry.coordinates,
            },
          };

          // if the route already exists on the map, reset it using setData
          if (this.map.getSource(routeId)) {
            this.map.getSource(routeId).setData(geoJson);
          } else {
            this.map.addSource(routeId, {
              type: 'line',
              source: {
                type: 'geojson',
                data: {
                  type: 'Feature',
                  properties: {},
                  geometry: { type: 'LineString', coordinates: geoJson },
                },
              },
              layout: {
                'line-join': 'round',
                'line-cap': 'round',
              },
              paint: {
                'line-color': '#3887be',
                'line-width': 5,
                'line-opacity': 0.75,
              },
            });
          }
        }
      });
    }
  }
  return new Draw();
}
