import {
  $,
  $$,
  on,
  each,
  attr,
  ajax,
  createEvent,
  trigger,
} from 'uikit/src/js/util';
import mapBoxGl from 'mapbox-gl';

export default function MapStep(args) {
  class EditStep {
    constructor() {
      this.map = args.map;
      this.feature = null;
      this.editModal = $('.journey-overlay');
      this.transportableType = $$('button[data-transportable-type]', this.editModal);
      this.addButton = $('button.mt-add-step');

      this.map.on('click', this.mapClickEvent.bind(this));
      on(this.addButton, 'click', this.saveStep.bind(this));
      on(this.transportableType, 'click', this.setTransportableType.bind(this));

      this.data = {
        id: args.id,
        title: '',
        lat: '',
        lng: '',
        transportableType: null,
      };
    }

    mapClickEvent(e) {
      const bbox = [[e.point.x - 5, e.point.y - 5], [e.point.x + 5, e.point.y + 5]];
      const selectPoints = this.map.queryRenderedFeatures(bbox, { layers: ['points'] });

      if (selectPoints.length > 0) {
        each(selectPoints, this.openStepLabel.bind(this));
      } else {
        this.feature = null;
        this.features = this.map.queryRenderedFeatures(e.point);
        this.data.lat = e.lngLat.lat;
        this.data.lng = e.lngLat.lng;
        this.data.title = '';

        if (this.features.length) {
          each(this.features, (obj) => {
            this.data.title = obj.properties.name_de ? obj.properties.name_de : '';
            this.feature = obj;
            return false;
          });
        }
        this.setForm();
      }
    }

    setTransportableType(e) {
      each(this.transportableType, (type) => { type.classList.remove('mt-select'); });
      e.currentTarget.classList.add('mt-select');
      this.data.transportableType = attr(e.currentTarget, 'data-transportable-type');
    }

    setForm() {
      each(this.data, (data, key) => {
        const formField = $('[name="' + key + '"]', this.editModal); // eslint-disable-line prefer-template
        if (formField) { formField.value = data; }
      });

      /** @method offcanvas */
      UIkit.offcanvas(this.editModal).show();
    }

    saveStep() {
      ajax('/ajax/add-step', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify(this.data),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200) {
          trigger(this.map.getContainer(), createEvent('add-step'));
        }
      });
    }

    openStepLabel(point) {
      if (point.type === 'Feature') {
        /** @method offcanvas */
        UIkit.offcanvas(this.editModal).hide();

        ajax('/ajax/load-step', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          data: JSON.stringify({ id: point.properties.id }),
          responseType: 'json',
        }).then((req) => {
          if (req.status === 200) {
            new mapBoxGl.Popup()
              .setLngLat(point.geometry.coordinates)
              .setHTML(req.response.data.title)
              .addTo(this.map);
          }
        });
      }
    }
  }

  return new EditStep();
}
