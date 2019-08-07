import mapBoxGl from 'mapbox-gl';
import MapBoxGeocoder from 'mapbox-gl-geocoder';

export default {
  args: 'apiKey',

  props: {
    apiKey: String,
    zoom: Number,
    center: String,
  },

  data: {
    apiKey: '',
    zoom: 12,
    center: [0, 0],
  },

  connected() {
    this.initialize();
  },

  methods: {
    initialize() {
      mapBoxGl.accessToken = this.apiKey;
      this.bounds = new mapBoxGl.LngLatBounds();

      this.map = new mapBoxGl.Map({
        style: 'mapbox://styles/mpk88/cjz1ea4ki0e0j1cmmoi7vxs2v',
        container: this.$el,
        center: this.center,
        zoom: this.zoom,
      });

      this.map.addControl(new MapBoxGeocoder({
        accessToken: mapBoxGl.accessToken,
        mapboxgl: mapBoxGl,
      }));
    },
  },
};
