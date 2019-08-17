import mapBoxGl from 'mapbox-gl';
import MapBoxGeocoder from 'mapbox-gl-geocoder';
import mapStep from '../utils/map-step';
import mapDraw from '../utils/map-draw';

export default {
  args: 'apiKey',

  props: {
    jId: Number,
    apiKey: String,
    zoom: Number,
    center: String,
  },

  data: {
    jId: null,
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

      if (this.jId !== null) {
        mapStep({
          id: this.jId,
          map: this.map,
        });

        mapDraw({
          id: this.jId,
          map: this.map,
          accessToken: mapBoxGl.accessToken,
        });
      }
    },
  },
};
