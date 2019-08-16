// Import base
import UIkit from './base/uikit';

// Components
import Maps from './components/maps';
import FlatDate from './components/fp-date';

// Global variables
global.UIkit = UIkit;

// UIkit Components
UIkit.component('maps', Maps);
UIkit.component('date-picker', FlatDate);
