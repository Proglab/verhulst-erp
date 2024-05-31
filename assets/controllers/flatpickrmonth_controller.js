// assets/controllers/flatpickr_controller.ts

// @ts-nocheck
import Flatpickr from 'stimulus-flatpickr';
import 'flatpickr/dist/themes/light.css';
import 'flatpickr/dist/plugins/monthSelect/style.css';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect/index.js';
import { French } from 'flatpickr/dist/l10n/fr.js';

/* stimulusFetch: 'lazy' */
export default class extends Flatpickr {
    config = {}

    initialize() {
        this.config = {
            locale: 'fr',
            plugins: [
                new monthSelectPlugin({
                    shorthand: true, //defaults to false
                    dateFormat: "Y-m-01", //defaults to "F Y"
                    altFormat: "Y-m-01", //defaults to "F Y"
                    theme: "light", // defaults to "light"
                    locale: French
                })
            ]
        };
    }
}