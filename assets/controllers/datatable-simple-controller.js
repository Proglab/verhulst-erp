
import {Controller} from "@hotwired/stimulus";
import DataTable from 'datatables.net-bs5';
import $ from 'jquery';

export default class extends Controller {

    initialize() {
        super.initialize();
        this.table = $(this.element).DataTable(
            {
                ajax: this.element.attributes.getNamedItem('data-ajax').value,
                sDom: "<'row'<'col-sm-4'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-3'i><'col-sm-2'l><'col-sm-3 offset-sm-4'p>>",
            }
        );
    }
}