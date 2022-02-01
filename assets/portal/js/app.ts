'use strict';

const bootstrap = require('bootstrap');
// @ts-ignore
global.bootstrap = bootstrap;

/* ===== Enable Bootstrap Popover (on element  ====== */

let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
popoverTriggerList.map(function (popoverTriggerEl: HTMLElement) {
    return new bootstrap.Popover(popoverTriggerEl)
})

/* ==== Enable Bootstrap Alert ====== */
let alertList = document.querySelectorAll('.alert')
alertList.forEach(function (alert: HTMLElement) {
    new bootstrap.Alert(alert)
});


/* ===== Responsive Sidepanel ====== */
const sidePanelToggler: HTMLElement = document.getElementById('sidepanel-toggler');
const sidePanel: HTMLElement = document.getElementById('app-sidepanel');
const sidePanelDrop: HTMLElement = document.getElementById('sidepanel-drop');
const sidePanelClose: HTMLElement = document.getElementById('sidepanel-close');

if (null !== sidePanel) {
    window.addEventListener('load', function(){
        responsiveSidePanel();
    });

    window.addEventListener('resize', function(){
        responsiveSidePanel();
    });

    sidePanelToggler.addEventListener('click', () => {
        if (sidePanel.classList.contains('sidepanel-visible')) {
            sidePanel.classList.remove('sidepanel-visible');
            sidePanel.classList.add('sidepanel-hidden');

        } else {
            sidePanel.classList.remove('sidepanel-hidden');
            sidePanel.classList.add('sidepanel-visible');
        }
    });

    sidePanelClose.addEventListener('click', (e) => {
        e.preventDefault();
        sidePanelToggler.click();
    });

    sidePanelDrop.addEventListener('click', (e) => {
        sidePanelToggler.click();
    });
}

function responsiveSidePanel() {
    let w: number = window.innerWidth;
    if (w >= 1200) {
        sidePanel.classList.remove('sidepanel-hidden');
        sidePanel.classList.add('sidepanel-visible');

    } else {
        sidePanel.classList.remove('sidepanel-visible');
        sidePanel.classList.add('sidepanel-hidden');
    }
}



/* ====== Mobile search ======= */
const searchMobileTrigger: HTMLElement = document.querySelector('.search-mobile-trigger');
const searchBox: HTMLElement = document.querySelector('.app-search-box');

if (searchMobileTrigger && searchBox) {
    searchMobileTrigger.addEventListener('click', () => {
        searchBox.classList.toggle('is-visible');

        let searchMobileTriggerIcon = document.querySelector('.search-mobile-trigger-icon');

        if (searchMobileTriggerIcon.classList.contains('fa-search')) {
            searchMobileTriggerIcon.classList.remove('fa-search');
            searchMobileTriggerIcon.classList.add('fa-times');
        } else {
            searchMobileTriggerIcon.classList.remove('fa-times');
            searchMobileTriggerIcon.classList.add('fa-search');
        }
    });
}

require('../scss/portal.scss');