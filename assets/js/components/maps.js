import mapBoxGl from 'mapbox-gl';
import MapBoxGeocoder from 'mapbox-gl-geocoder';
import mapStep from '../utils/map-step';

export default {
  args: 'apiKey',

  props: {
    mtId: Number,
    apiKey: String,
    zoom: Number,
    center: String,
  },

  data: {
    mtId: null,
    apiKey: '',
    zoom: 12,
    center: [8.310473, 47.050052],
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

      this.map.addControl(new mapBoxGl.GeolocateControl({
        positionOptions: { enableHighAccuracy: true },
        trackUserLocation: true,
      }));

      if (this.mtId !== null) {
        mapStep({
          journeyId: this.mtId,
          map: this.map
        });
      }
    },
  },
};
