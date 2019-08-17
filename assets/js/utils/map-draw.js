import {ajax} from "uikit/src/js/util";

export default function MapDraw(args) {
  class Draw {
    constructor() {
      this.map = args.map;
      this.journeyId = args.journeyId;
      this.loadSteps();
      this.steps = [];
    }

    loadSteps() {
      ajax('/ajax/load-steps', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ id: this.journeyId }),
        responseType: 'json',
      }).then((xhr) => {
        console.log(xhr);
      });
    }
  }
  return new Draw();
}
