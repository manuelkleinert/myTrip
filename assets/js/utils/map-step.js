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

export default function MapStep(args) {
  class EditStep {
    constructor() {
      this.map = args.map;
      this.feature = null;
      this.editModal = $('.journey-overlay');
      this.transportableType = $$('button[data-transportable-type]', this.editModal);
      this.addButton = $('button.mt-add-step');

      this.map.on('click', this.loadData.bind(this));
      on(this.transportableType, 'click', this.setTransportableType.bind(this));
      on(this.addButton, 'click', this.saveStep.bind(this));

      this.data = {
        id: args.id,
        title: '',
        lat: '',
        lng: '',
        transportableType: null,
      };
    }

    loadData(e) {
      this.feature = null;
      this.features = this.map.queryRenderedFeatures(e.point);
      this.data.lat = e.lngLat.lat;
      this.data.lng = e.lngLat.lng;
      this.data.title = '';

      /** @method offcanvas */
      UIkit.offcanvas(this.editModal).show();

      if (this.features.length) {
        each(this.features, (obj) => {
          this.data.title = obj.properties.name_de ? obj.properties.name_de : '';
          this.feature = obj;
          return false;
        });
      }
      this.setForm();
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
    }

    saveStep() {
      ajax('/ajax/add-step', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify(this.data),
        responseType: 'json',
      }).then((xhr) => {
        if (xhr.status === 200) {
          trigger(this.map.getContainer(), createEvent('add-step'));
        }
      });
    }
  }

  return new EditStep();
}
