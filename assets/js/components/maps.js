import mapBoxGl from 'mapbox-gl';

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
        center: {lng: 0, lat: 0},
    },

    connected() {
        this.initialize();
    },

    methods: {
        initialize() {
            mapBoxGl.accessToken = this.apiKey;
            this.bounds = new mapBoxGl.LngLatBounds();

            this.map = new mapBoxGl.Map({
                sprite: 'mapbox://styles/mpk88/cjz1ea4ki0e0j1cmmoi7vxs2v',
                container: this.$el,
                center: this.center,
                zoom: this.zoom,
            });
        },
    },
};
