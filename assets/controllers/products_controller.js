import {Controller} from '@hotwired/stimulus';
import Handsontable from 'handsontable';
import numbro from 'numbro';
import deDE from 'numbro/languages/de-DE';
import 'handsontable/dist/handsontable.full.min.css';
import {HyperFormula} from 'hyperformula';

numbro.registerLanguage(deDE);

let globalSuppliers = [];
let handsontableInstances = [];

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    initialize() {

        const data = JSON.parse(this.element.dataset.index);

        if (globalSuppliers.length === 0) {
            fetch('/admin/fr/budget/supplier/get')
                .then(response => response.json())
                .then(suppliers => {
                    globalSuppliers = suppliers;

                    this.initializeHandsontable(data, globalSuppliers);
                });
        } else {
            this.initializeHandsontable(data, globalSuppliers);
        }
    }

    initializeHandsontable(data, suppliers) {

        let height = 'auto';
        if (data.length < 5) {
            height = 200;
        }



        const hot = new Handsontable(this.element, {
            data: data,
            height: height,
            columns: [
                {data: 'id', editor: false},
                {data: 'title'},
                {data: 'qty'},
                {
                    data: 'price',
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0,0.00 $',
                        culture: 'de-DE', // use this for EUR (German),
                        // more cultures available on http://numbrojs.com/languages.html
                    },
                },
                {
                    data: 'totalPrice', editor: false,
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0,0.00 $',
                        culture: 'de-DE', // use this for EUR (German),
                        // more cultures available on http://numbrojs.com/languages.html
                    },
                },
                {
                    data: 'offerPrice',
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0,0.00 $',
                        culture: 'de-DE', // use this for EUR (German),
                        // more cultures available on http://numbrojs.com/languages.html
                    },
                },
                {
                    data: 'offerPriceTot', editor: false,
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0,0.00 $',
                        culture: 'de-DE', // use this for EUR (German),
                        // more cultures available on http://numbrojs.com/languages.html
                    },
                },
                {
                    data: 'realPrice',
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0,0.00 $',
                        culture: 'de-DE', // use this for EUR (German),
                        // more cultures available on http://numbrojs.com/languages.html
                    },
                },
                {
                    data: 'totalRealPrice', editor: false,
                    type: 'numeric',
                    numericFormat: {
                        pattern: '0,0.00 $',
                        culture: 'de-DE', // use this for EUR (German),
                        // more cultures available on http://numbrojs.com/languages.html
                    },
                },
                {
                    data: 'supplier',
                    type: 'dropdown',
                    source: suppliers.map(supplier => supplier.name), // Remplacez ceci par votre liste de fournisseurs
                    allowInvalid: true,
                    validator: function (value, callback) {
                        if (this.source.includes(value)) {
                            callback(true);
                        } else {
                            console.info('validator-value', value);

                            fetch('/admin/fr/budget/supplier/save', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(value)
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Erreur lors de la sauvegarde du nouveau fournisseur');
                                    }
                                    return response.json();
                                })
                                .then(supplier => {
                                    console.info('validator-post-save', supplier);
                                    this.source.push(supplier.name);
                                    suppliers.push({id: supplier.id, name: supplier.name});
                                    globalSuppliers.push({id: supplier.id, name: supplier.name});
                                    console.info('validator-supplier-push', supplier.name);
                                    this.afterChange([this.row, this.col, null, supplier.id], 'loadData');
                                    console.info('globalSuppliers', globalSuppliers);
                                    callback(true);
                                    handsontableInstances.forEach(hotInstance => {
                                        const columns = hotInstance.getSettings().columns;
                                        const supplierColumn = columns.find(column => column.data === 'supplier');

                                        if (supplierColumn) {
                                            supplierColumn.source = globalSuppliers.map(supplier => supplier.name);
                                        }

                                        hotInstance.updateSettings({ columns });
                                        hotInstance.render();
                                    });
                                })
                        }
                    },
                },
                {
                    data: 'action',
                    renderer: function(instance, td, row, col, prop, value, cellProperties) {
                        // Si un bouton existe déjà, ne créez pas un nouveau bouton
                        if (td.firstChild && td.firstChild.className === 'delete-button') {
                            return;
                        }

                        const button = document.createElement('button');
                        button.textContent = 'Supprimer';
                        button.className = 'delete-button';
                        td.appendChild(button);
                    },
                    editor: false
                }
            ],
            colWidths: [20, 150, 30, 30, 30, 30, 30, 30, 30, 100, 30],
            colHeaders: [
                'ID',
                'Nom',
                'Quantité',
                'PU',
                'Tot HT',
                'PU Offre HT',
                'Tot Offre HT',
                'PU Facture HT',
                'Tot Facture HT',
                'Fournisseur',
                'Action',
            ],
            rowHeaders: false,
            formulas: {
                engine: HyperFormula,
            },
            autoWrapRow: true,
            autoWrapCol: true,
            stretchH: 'all',
            hiddenColumns: {
                columns: [0],
                // show UI indicators to mark hidden columns
                indicators: false,
            },
            dropdownMenu: true,
            filters: true,
            afterChange: function (change, source) {
                console.log('change', change);
                if (source === 'loadData') {
                    return; //don't save this change
                }

                let supplier = suppliers.find(supplier => supplier.name === change[0][3]);

                if (supplier) {
                    let send =  JSON.parse(JSON.stringify(data[change[0][0]]));
                    send.supplier = supplier.id;

                    fetch('/admin/fr/budget/product/save', {
                        method: 'POST',
                        mode: 'no-cors',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({data: send})
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Erreur lors de la sauvegarde du nouveau fournisseur');
                            }
                            return response.json();
                        })
                }
            },
            afterOnCellMouseDown: function(event, coords, element) {
                if (coords.col === this.countCols() - 1) { // If the last column was clicked
                    const rowId = this.getDataAtRowProp(coords.row, 'id'); // Assuming each row has an 'id' property
                    const rowTitle = this.getDataAtRowProp(coords.row, 'title');

                    // Update the modal's content
                    document.querySelector('#modal-delete .modal-body h4').textContent = 'Voulez-vous supprimer "' + rowTitle + '" ?';

                    // Show the modal
                    const modal = new bootstrap.Modal(document.getElementById('modal-delete'));
                    modal.show();

                    const hotInstance = this;

                    // Create a separate callback function for the click event
                    const deleteButtonClickHandler = function() {
                        // Perform your delete operation here
                        // For example, you could make an AJAX request to your server to delete the item
                        fetch('/admin/fr/budget/product/delete/' + rowId, {
                            method: 'DELETE'
                        }).then(response => {
                            if (response.ok) {
                                // If the item was deleted successfully, remove the row from the table
                                hotInstance.alter('remove_row', coords.row);

                                // Remove the click event
                                this.removeEventListener('click', deleteButtonClickHandler);
                            } else {
                                alert('Une erreur s\'est produite lors de la suppression de l\'élément.');
                            }
                        });
                    };

                    // Add an event listener to the "Supprimer" button
                    document.getElementById('modal-delete-button').addEventListener('click', deleteButtonClickHandler);
                }
            },
            licenseKey: 'non-commercial-and-evaluation'
        });
        this.element.hotInstance = hot;
        handsontableInstances.push(hot);
    }
}