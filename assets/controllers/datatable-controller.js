
import {Controller} from "@hotwired/stimulus";
import DataTable from 'datatables.net-bs5';
import $ from 'jquery';

export default class extends Controller {

    initialize() {
        super.initialize();
        this.table = $(this.element).DataTable(
            {
                ajax: this.element.attributes.getNamedItem('data-ajax').value
            }
        );
    }
}