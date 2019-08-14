import { $, each } from 'uikit/src/js/util';

export default function MapStep(args) {
  class EditStep {
    constructor() {
      this.map = args.map;
      this.feature = null;
      this.editModal = $('.journey-overlay');
      this.map.on('click', this.loadData.bind(this));
    }

    loadData(event) {
      this.feature = null;
      this.features = this.map.queryRenderedFeatures(event.point);
      this.coords = event.lngLat;

      /** @method offcanvas */
      UIkit.offcanvas(this.editModal).show();

      if (this.features.length) {
        each(this.features, (obj) => {
          this.feature = obj;
          return false;
        });
      }
      this.setForm();
    }

    setForm() {
      $('[name=lat]', this.editModal).value = this.coords.lat;
      $('[name=lng]', this.editModal).value = this.coords.lng;

      if (this.feature && Object.prototype.hasOwnProperty.call(this.feature.properties, 'name_de')) {
        $('[name=title]', this.editModal).value = this.feature.properties.name_de;
      }
    }
  }
  return new EditStep();
}
