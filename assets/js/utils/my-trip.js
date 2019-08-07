const createMyTrip = ({ OverlayView = google.maps.OverlayView, ...args }) => {
    class MapOverlay extends OverlayView {
        constructor() {
            super();

            this.bounds = args.bounds;
            this.image = args.image;
            this.map = args.map;
            this.div = null;

            this.setMap(this.map);
        }

        onAdd() {
            const div = document.createElement('div');
            div.style.borderStyle = 'none';
            div.style.borderWidth = '0px';
            div.style.position = 'absolute';

            const img = document.createElement('img');
            img.src = this.image;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.position = 'absolute';

            div.appendChild(img);
            this.div = div;

            const panes = this.getPanes();
            panes.overlayLayer.appendChild(div);
        }

        draw() {
            const overlayProjection = this.getProjection();
            const sw = overlayProjection.fromLatLngToDivPixel(this.bounds.getSouthWest());
            const ne = overlayProjection.fromLatLngToDivPixel(this.bounds.getNorthEast());

            const { div } = this;
            div.style.left = `${sw.x}px`;
            div.style.top = `${ne.y}px`;
            div.style.width = `${ne.x - sw.x}px`;
            div.style.height = `${sw.y - ne.y}px`;
        }

        onRemove() {
            this.div.parentNode.removeChild(this.div);
            this.div = null;
        }
    }

    return new MapOverlay();
};

export default createMyTrip;
