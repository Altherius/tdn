import { Controller } from 'stimulus';

export default class extends Controller {
    connect() {
        $('.select2').select2({
            theme: "bootstrap-5",
            selectionCssClass: "select2--small",
        });
        $('table.table').bootstrapTable({
            search: true,
            locale: "fr-FR",
            sortable: true,
            searchAccentNeutralise: true,
            searchTimeOut: 200
        })
    }
}

