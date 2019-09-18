import {
  $,
  $$,
  on,
  each,
  ajax,
  attr,
  append,
  createEvent,
  trigger,
  html,
  addClass,
  removeClass,
  hasClass,
} from 'uikit/src/js/util';
import mapBoxGl from 'mapbox-gl';
import flatpickr from 'flatpickr';
import { German } from 'flatpickr/dist/l10n/de';

export default function MapStep(args) {
  class EditStep {
    constructor() {
      flatpickr.localize(German);
      this.map = args.map;
      this.editMode = false;
      this.lastDate = null;
      this.lastTime = null;

      this.dateSettings = {
        dateFormat: 'd.m.Y',
      };

      this.timeSettings = {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        minDate: '00:00',
        maxDate: '24:00',
      };

      this.detailModal = $('#mt-step-detail');
      this.openEditButteon = $('.mt-edit', this.detailModal);
      this.closeDetailButton = $('.mt-detail-close', this.detailModal);

      this.editModal = $('#mt-step-edit');
      this.closeEditorButton = $('.mt-edit-close', this.editModal);

      this.inputDateFrom = $('[name="step_edit[dateFrom]"]', this.editModal);
      this.inputDateTo = $('[name="step_edit[dateTo]"]', this.editModal);
      this.inputTimeFrom = $('[name="step_edit[timeFrom]"]', this.editModal);
      this.inputTimeTo = $('[name="step_edit[timeTo]"]', this.editModal);

      this.transportableType = $$('button[data-transportable-type]', this.editModal);
      this.addButton = $('#step_edit_update');
      this.removeButton = $('button.mt-remove-step');

      this.popup = new mapBoxGl.Popup({
        closeButton: false,
        closeOnClick: false,
      });

      this.map.on('click', this.mapClickEvent.bind(this));
      this.map.on('mouseenter', 'lines', this.mapMouseEnterLine.bind(this));
      this.map.on('mouseleave', 'lines', this.mapMouseLeaveLine.bind(this));

      on(this.addButton, 'click', this.saveStep.bind(this));
      on(this.removeButton, 'click', this.removeStep.bind(this));
      on(this.transportableType, 'click', (e) => {
        this.setTransportableType(attr(e.currentTarget, 'data-transportable-type'), true);
      }).bind(this);

      on(this.closeDetailButton, 'click', UIkit.offcanvas(this.detailModal).hide);

      on(this.openEditButteon, 'click', this.openEditor.bind(this));
      on(this.closeEditorButton, 'click', this.closeEditor.bind(this));

      this.editDateFrom = flatpickr(this.inputDateFrom, this.dateSettings);
      this.editDateTo = flatpickr(this.inputDateTo, this.dateSettings);

      flatpickr(this.inputTimeFrom, this.timeSettings);
      flatpickr(this.inputTimeTo, this.timeSettings);

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
      const selectArea = [[e.point.x - 10, e.point.y - 10], [e.point.x + 10, e.point.y + 10]];
      const selectPoint = this.map.queryRenderedFeatures(selectArea, { layers: ['points'] });

      if (selectPoint.length > 0) {
        each(selectPoint, this.openDetail.bind(this));
      } else if (this.editMode) {
        if (!this.data.title) {
          this.setTitleByFeatures(this.map.queryRenderedFeatures(e.point));
        }
        this.data.lat = e.lngLat.lat;
        this.data.lng = e.lngLat.lng;
        this.setEditor();
      } else {
        this.setTitleByFeatures(this.map.queryRenderedFeatures(e.point));
        this.data.lat = e.lngLat.lat;
        this.data.lng = e.lngLat.lng;
        this.data.stepId = '';
        this.data.title = '';
        this.openEditor();
      }
    }

    mapMouseEnterLine(e) {
      if (e.features[0] && e.features[0].geometry.coordinates[0] && e.lngLat) {
        const feature = e.features[0];
        const data = feature.properties;
        const title = data.distance !== 'null' ? `${data.distance} km` : data.title;
        if (title) {
          this.popup.setLngLat(e.lngLat).setHTML(title).addTo(this.map);
        }
      }
    }

    mapMouseLeaveLine() {
      this.map.getCanvas().style.cursor = '';
      this.popup.remove();
    }

    setTransportableType(transportableId, $toggle = false) {
      each(this.transportableType, (obj) => {
        if (hasClass($(obj), 'mt-select')) {
          removeClass($(obj), 'mt-select');
        }
      });

      if (transportableId !== this.data.transportableId || !$toggle) {
        addClass($(`[data-transportable-type="${transportableId}"]`, this.editModal), 'mt-select');
        this.data.transportableId = transportableId;
      } else {
        this.data.transportableId = null;
      }
    }

    setEditor() {
      if (!this.data.timeFrom) { this.data.timeFrom = '00:00'; }
      if (!this.data.timeTo) { this.data.timeTo = '00:00'; }

      each(this.data, (data, key) => {
        if (!this.editMode || (key === 'lat' || key === 'lng')) {
          const formField = $(`[name="step_edit[${key}]"]`, this.editModal);
          if (formField) { formField.value = data; }
        }
      });

      const dateFromField = $('[name="step_edit[dateFrom]"]', this.editModal);
      const dateToField = $('[name="step_edit[dateTo]"]', this.editModal);
      if (!this.data.dateFrom || !this.data.dateTo) {
        this.getNextDate(() => {
          if (!dateFromField.value) {
            this.editDateFrom.setDate(this.lastDate);
            dateFromField.value = this.lastDate;
          }
          if (!dateToField.value) {
            this.editDateTo.setDate(this.lastDate);
            dateToField.value = this.lastDate;
          }
        });
      }

      this.setTransportableType(this.data.transportableId);

      if (this.data.stepId) {
        removeClass(this.removeButton, 'uk-hidden');
      } else {
        addClass(this.removeButton, 'uk-hidden');
      }
    }

    openEditor() {
      this.setEditor();
      this.editMode = true;
      UIkit.offcanvas(this.editModal).show();
    }

    closeEditor() {
      this.editMode = false;
      UIkit.offcanvas(this.editModal).hide();
    }

    saveStep() {
      each(this.data, (data, key) => {
        const formField = $(`[name="step_edit[${key}]"]`, this.editModal);
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
          this.editMode = false;
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
        }).then(() => {
          this.closeEditor();
          trigger(this.map.getContainer(), createEvent('remove-step'));
        });
      }
    }

    openDetail(point) {
      if (point.type === 'Feature') {
        this.closeEditor();

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
              if (this.data.distance) {
                append($('.uk-modal-title', this.detailModal), ` - ${this.data.distance}km`);
              }
              html($('.mt-content', this.detailModal), this.data.text);
              UIkit.offcanvas(this.detailModal).show();
            } else {
              this.openEditor();
            }
          }
        });
      }
    }

    getNextDate(fn) {
      ajax('/ajax/next-date', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ id: this.data.journeyId }),
        responseType: 'json',
      }).then((req) => {
        if (req.status === 200) {
          this.lastDate = req.response.date;
          this.lastTime = req.response.time;
          if (fn) { fn(); }
        }
      });
    }

    setTitleByFeatures(features) {
      this.title = null;
      if (features && features.length) {
        each(features, (obj) => {
          if (obj.properties && obj.properties.name_de && this.title !== null) {
            this.title = obj.properties.name_de;
          }
        });
      }
      return true;
    }
  }

  return new EditStep();
}
