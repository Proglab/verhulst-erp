import {Controller} from '@hotwired/stimulus';
import Handsontable from 'handsontable';
import numbro from 'numbro';
import deDE from 'numbro/languages/de-DE';
import 'handsontable/dist/handsontable.full.min.css';
import {HyperFormula} from 'hyperformula';

numbro.registerLanguage(deDE);


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

        fetch('/admin/fr/budget/supplier/get')
        .then(response => response.json())
        .then(suppliers => {

            console.log(suppliers);

            const hot = new Handsontable(this.element, {
                data: data,
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
                                        this.source.push(supplier.name)
                                        console.info('validator-supplier-push', supplier.name);

                                        callback(true);
                                    })
                            }
                        },
                    },
                    {data: 'action', editor: false},
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
                height: 'auto',
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
                afterChange: function (change, source) {
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
                licenseKey: 'non-commercial-and-evaluation'
            });
        });
    }
}