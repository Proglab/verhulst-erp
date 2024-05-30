
import {Controller} from "@hotwired/stimulus";
import DataTable from 'datatables.net-bs5';
import $ from 'jquery';

export default class extends Controller {

    initialize() {
        super.initialize();
        this.table = $(this.element).DataTable(
            {
                ajax: this.element.attributes.getNamedItem('data-ajax').value,
                language: {
                    url: "/js/french.json"
                }
            }
        );

        const minEl = document.querySelector('#min');
        const maxEl = document.querySelector('#max');

        this.table.search.fixed('range', function (searchStr, data, index) {
            if (minEl.value === '' && maxEl.value === '') {
                return true;
            }

            var  dateStart = new Date(minEl.value);
            var  dateEnd = new Date(maxEl.value);

            var dataDate = data[5].split('/').reverse().join('-');

            if (new Date(dataDate) >= new Date(dateStart) && new Date(dataDate) <= new Date(dateEnd)) {
                return true;
            }

            if (minEl.value === '' && new Date(dataDate) <= new Date(dateEnd)) {
                return true;
            }

            if (new Date(dataDate) >= new Date(dateStart) && maxEl.value === '') {
                return true;
            }
            return false;


        });

        let table = this.table;

        minEl.onchange = function(){
            table.draw();
        }

        maxEl.onchange = function(){
            table.draw();
        }
    }
}