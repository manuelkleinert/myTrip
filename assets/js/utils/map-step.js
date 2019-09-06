import {
  $,
  $$,
  on,
  each,
  ajax,
  attr,
  createEvent,
  trigger,
  html,
  addClass,
  removeClass,
  hasClass,
} from 'uikit/src/js/util';

export default function MapStep(args) {
  class EditStep {
    constructor() {
      this.map = args.map;
      this.feature = null;

      this.detailModal = $('#mt-step-detail');
      this.detailModalEditButteon = $('.mt-edit', this.detailModal);
      this.editModal = $('#mt-step-edit');

      this.transportableType = $$('button[data-transportable-type]', this.editModal);
      this.addButton = $('button.mt-add-step');
      this.removeButton = $('button.mt-remove-step');

      this.map.on('click', this.mapClickEvent.bind(this));

      on(this.addButton, 'click', this.saveStep.bind(this));
      on(this.removeButton, 'click', this.removeStep.bind(this));
      on(this.transportableType, 'click', this.setTransportableType.bind(this));
      on(this.detailModalEditButteon, 'click', this.openEditor.bind(this));

      this.data = {
        journeyId: args.id,
        stepId: '',
        title: '',
        lat: '',
        lng: '',
        transportableId: null,
      };
    }

    mapClickEvent(e) {
      const bbox = [[e.point.x - 5, e.point.y - 5], [e.point.x + 5, e.point.y + 5]];
      const selectPoints = this.map.queryRenderedFeatures(bbox, { layers: ['points'] });

      if (selectPoints.length > 0) {
        each(selectPoints, this.openDetail.bind(this));
      } else {
        this.feature = null;
        this.features = this.map.queryRenderedFeatures(e.point);

        this.data.lat = e.lngLat.lat;
        this.data.lng = e.lngLat.lng;
        this.data.stepId = '';
        this.data.title = '';
        this.openEditor();
      }
    }

    setTransportableType(e) {
      each(this.transportableType, (obj) => {
        if (hasClass($(obj), 'mt-select')) {
          removeClass($(obj), 'mt-select');
        }
      });
      addClass(e.currentTarget, 'mt-select');
      this.data.transportableId = attr($(e.currentTarget), 'data-transportable-type');
    }

    openEditor() {
      if (this.features && this.features.length) {
        each(this.features, (obj) => {
          this.data.title = obj.properties.name_de ? obj.properties.name_de : '';
          this.feature = obj;
          return false;
        });
      }

      each(this.data, (data, key) => {
        const formField = $(`[name=${key}]`, this.editModal);
        if (formField) { formField.value = data; }
      });

      if (this.data.transportableId) {
        addClass($(`[data-transportable-type=${this.data.transportableId}]`, this.transportableType), 'mt-select');
      }

      if (this.data.stepId) {
        removeClass(this.removeButton, 'uk-hidden');
      } else {
        addClass(this.removeButton, 'uk-hidden');
      }

      UIkit.offcanvas(this.editModal).show();
    }

    saveStep() {
      each(this.data, (data, key) => {
        const formField = $(`[name=${key}]`, this.editModal);
        if (formField) { this.data[key] = formField.value; }
      });

      ajax('/ajax/update-step', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify(this.data),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200) {
          UIkit.offcanvas(this.editModal).hide();
          trigger(this.map.getContainer(), createEvent('add-step'));
        }
      });
    }

    removeStep() {
      if (this.data.stepId) {
        ajax('/ajax/remove-step', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          data: JSON.stringify(this.data),
          responseType: 'json',
        }).then((req) => {
          UIkit.offcanvas(this.editModal).hide();
          trigger(this.map.getContainer(), createEvent('remove-step'));
        });
      }
    }

    openDetail(point) {
      if (point.type === 'Feature') {
        UIkit.offcanvas(this.editModal).hide();

        ajax('/ajax/load-step', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          data: JSON.stringify({ id: point.properties.id }),
          responseType: 'json',
        }).then((req) => {
          if (req.status === 200) {
            this.data = req.response.data;
            if (this.data.title || this.data.text) {
              html($('.uk-modal-title', this.detailModal), this.data.title);
              html($('.uk-modal-body', this.detailModal), this.data.text);
              UIkit.modal(this.detailModal).show();
            } else {
              this.openEditor();
            }
          }
        });
      }
    }
  }

  return new EditStep();
}
