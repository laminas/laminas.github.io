'use strict';

{
    // Tables
    const tables = document.querySelectorAll('.content table');
    tables.forEach(element => element.classList.add('table', 'table-striped', 'table-hover'));

    // Anchors
    anchors.options.placement = 'left';
    anchors.add(
        '.content > h1:not(.content__title), .content > h2:not(.chapter__headline), .content > h3:not(.display-4), .content > h4, .content > h5'
    );

    // Pre elements
    const preElements = document.querySelectorAll('pre');
    preElements.forEach(element => {
        if (element.firstElementChild
            && ! element.firstElementChild.classList.contains('language-treeview')
        ) {
            element.classList.add('line-numbers');
        }
    });

    // Search modal
    $('#mkdocs_search_modal').on('shown.bs.modal', function () {
        $('#mkdocs-search-query').focus();
    });

    // Shift window
    const shiftWindow = function () {
        scrollBy(0, -15)
    };
    if (location.hash) {
        shiftWindow();
    }
    window.addEventListener('hashchange', shiftWindow);
}
