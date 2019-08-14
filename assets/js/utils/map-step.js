import { $, each } from 'uikit/src/js/util';

export default function MapStep(args) {
  class EditStep {
    constructor() {
      this.map = args.map;
      this.feature = null;
      this.editModal = $('.journey-overlay');
      this.map.on('click', this.loadData.bind(this));
      this.data = {
        title: '',
        lat: '',
        lng: '',
      };
    }

    loadData(event) {
      this.feature = null;
      this.features = this.map.queryRenderedFeatures(event.point);
      this.data.lat = event.lngLat.lat;
      this.data.lng = event.lngLat.lng;
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

    setForm() {
      each(this.data, (data, key) => {
        /** @method formField */
        const formField = $('[name="' + key + '"]', this.editModal); // eslint-disable-line prefer-template
        formField.value = data;
      });
    }
  }
  return new EditStep();
}
