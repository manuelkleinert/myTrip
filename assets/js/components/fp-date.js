import {
  on,
  removeAttr,
  trigger,
} from 'uikit/src/js/util';

import flatpickr from 'flatpickr';
import { German } from 'flatpickr/dist/l10n/de';

export default {
  args: 'defaultDate',

  props: {
    altFormat: String,
    altInput: Boolean,
    dateFormat: String,
    defaultDate: String,
    enableTime: Boolean,
    maxDate: String,
    minDate: String,
    noCalendar: Boolean,
    originalValue: String,
  },

  data: {
    altFormat: 'l, j. F Y',
    altInput: true,
    dateFormat: 'Y-m-d',
    defaultDate: null,
    enableTime: false,
    maxDate: null,
    minDate: null,
    noCalendar: false,
    originalValue: null,
    time_24hr: true,
  },

  created() {
    flatpickr.localize(German);
  },

  connected() {
    this.flatpickr = flatpickr(this.$el, this.$props);

    // Workaround to enable HTML5 validation, see https://github.com/flatpickr/flatpickr/issues/892
    if (this.altInput) {
      removeAttr(this.flatpickr.altInput, 'readonly');
      on(this.flatpickr.altInput, 'focus', ({ currentTarget }) => currentTarget.blur());
    }

    trigger(this.$el, 'initialized', [this]);
  },
};
