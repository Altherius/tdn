import { Controller } from 'stimulus';

export default class extends Controller {
    connect() {
        $('select.select2').select2({
            theme: "bootstrap-5",
            selectionCssClass: "select2--small",
        });

        document.addEventListener("turbo:before-cache", function() {
            $("select.select2").each(function() {
                $(this).select2('destroy');
                console.log('select2 destroyed');
            });

            $('table.bs-table').each(function() {
                $(this).bootstrapTable('destroy');
                console.log('table destroyed');
            });
        });

        $('table.bs-table').bootstrapTable({
            search: true,
            locale: "fr-FR",
            sortable: true,
            searchAccentNeutralise: true,
            searchTimeOut: 200
        })
    }
}

