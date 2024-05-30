
import {Controller} from "@hotwired/stimulus";
import $ from 'jquery';

export default class extends Controller {

    initialize() {
        this.table = $(this.element).DataTable({
            language: {
                url: "/js/french.json"
            }
        });
    }
}