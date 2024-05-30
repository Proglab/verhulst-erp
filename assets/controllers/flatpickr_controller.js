// assets/controllers/flatpickr_controller.ts

// @ts-nocheck
import Flatpickr from 'stimulus-flatpickr'
import 'flatpickr/dist/themes/light.css'
import { French } from 'flatpickr/dist/l10n/fr.js'

/* stimulusFetch: 'lazy' */
export default class extends Flatpickr {
    config = {}

    initialize() {
        this.config = {
            locale: 'fr',
            time_24hr: true,
            weekNumbers: true,
        };
    }
}