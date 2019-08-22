export default function MapSlider(args) {
  class Slider {
    constructor() {
      this.map = args.map;
      this.steps = [];
    }

    uodateSlider(steps) {
      this.steps = steps;
    }
  }

  return new Slider();
}
