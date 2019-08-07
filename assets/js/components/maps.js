import loadGoogleMapsApi from 'load-google-maps-api';
import {
    data,
    hasOwn,
    isNumeric,
    toFloat,
} from 'uikit/src/js/util';

export default {
    args: 'apiKey',

    props: {
        apiKey: String,
        location: String,
        zoom: Number,
        zoomControl: Boolean,
        mapType: String,
        mapTypeControl: Boolean,
        fullscreenControl: Boolean,
        streetViewControl: Boolean,
        draggable: Boolean,
        scrollWheel: Boolean,
        markerIcon: String,
        styles: String,
        overlayImage: String,
        bounds: String,
    },

    data: {
        apiKey: null,
        location: '37.7576948,-122.4726192',
        locations: [],
        zoom: 12,
        zoomControl: false,
        mapTypeControl: false,
        fullscreenControl: false,
        streetViewControl: false,
        draggable: true,
        scrollWheel: false,
        markerIcon: false,
        styles: false,
        overlayImage: false,
        bounds: false,
    },

    connected() {
        loadGoogleMapsApi({key: this.apiKey})
            .then((googleMaps) => {
                this.api = googleMaps;
                this.initialize();
            });
    },

    methods: {

        initialize() {
            const options = {
                mapTypeId: this.api.MapTypeId.ROADMAP,
                zoom: this.zoom,
                zoomControl: this.zoomControl,
                mapTypeControl: this.mapTypeControl,
                fullscreenControl: this.fullscreenControl,
                streetViewControl: this.streetViewControl,
                draggable: this.draggable,
                scrollWheel: this.scrollWheel,
            };

            this.map = new this.api.Map(this.$el, options);
            this.locations = data(this.$el, 'locations') ? JSON.parse(data(this.$el, 'locations')) : [];

            if (this.locations.length > 0) {
                this.setLocations();
            } else {
                this.setLocation();
            }

            this.setStyles();
        },

        setLocation() {
            if (this.location) {
                const parts = this.location.split(',').map(part => part.trim());

                if ((parts.length === 2 && parts.every(isNumeric)) || !this.apiKey) {
                    this.setCenter(this.createPosition(parts[0], parts[1]));
                } else {
                    this.geocodeAddress(this.location)
                        .then((position) => {
                            this.setCenter(position);
                        });
                }
            }
        },

        setLocations() {
            const infoWindow = new this.api.InfoWindow();
            const bounds = new this.api.LatLngBounds();

            Promise.all(this.locations.map(location => new Promise((resolve) => {
                if (hasOwn(location, 'address')) {
                    this.geocodeAddress(location.address)
                        .then((position) => {
                            this.createMarkerAndExtendBounds(
                                position,
                                bounds,
                                infoWindow,
                                location.name,
                            );
                            resolve();
                        });
                } else {
                    this.createMarkerAndExtendBounds(
                        this.createPosition(location.lat, location.lng),
                        bounds,
                        infoWindow,
                        location.name,
                    );
                    resolve();
                }
            }))).then(() => {
                this.map.fitBounds(bounds);

                if (this.locations.length === 1) {
                    const listener = this.api.event.addListener(this.map, 'idle', () => {
                        this.map.setZoom(this.zoom);
                        this.api.event.removeListener(listener);
                    });
                }
            });
        },

        createPosition(lat, lng) {
            return new this.api.LatLng(toFloat(lat), toFloat(lng));
        },

        createMarkerAndExtendBounds(position, bounds, infoWindow, content) {
            const marker = this.setMarker(position);

            bounds.extend(marker.position);

            this.api.event.addListener(marker, 'click', () => {
                infoWindow.setContent(content);
                infoWindow.open(this.map, marker);
            });
        },

        geocodeAddress(address) {
            return new Promise((resolve, reject) => {
                const gc = new this.api.Geocoder();
                const gcStatus = this.api.GeocoderStatus;

                gc.geocode({address}, (results, status) => {
                    if (status === gcStatus.OK && status !== gcStatus.ZERO_RESULTS) {
                        resolve(results[0].geometry.location);
                    } else {
                        reject();
                    }
                });
            });
        },

        setCenter(position) {
            this.map.setCenter(position);

            if (this.overlayImage && this.bounds) {
                this.setOverlay();
            } else {
                this.setMarker(position);
            }
        },

        setMarker(position) {
            const icon = {
                url: this.markerIcon,
                size: new this.api.Size(50, 50),
                origin: new this.api.Point(0, 0),
                anchor: new this.api.Point(25, 25),
            };

            return new this.api.Marker({
                position,
                map: this.map,
                icon: this.markerIcon ? icon : this.api.Icon,
            });
        },

        setOverlay() {
            const boundParts = this.bounds.split(',').map(part => toFloat(part.trim()));
        },

        setStyles() {
            if (this.styles) {
                this.map.mapTypes.set(
                    'custom_styles',
                    new this.api.StyledMapType(JSON.parse(this.styles), {name: 'Custom Styles'}),
                );
                this.map.setMapTypeId('custom_styles');
            }
        },

    },
};
