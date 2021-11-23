import Alpine from 'alpinejs'
import _ from 'lodash'
import moment from 'moment'

if (typeof window.Alpine == 'undefined') {
    window.Alpine = Alpine
    Alpine.start()
}

var pdfMake = require('pdfmake/build/pdfmake')
var pdfFonts = require('./st-vfs')
pdfMake.vfs = pdfFonts.pdfMake.vfs
pdfMake.fonts = {
    'IBMPlexSans': {
        normal: 'IBMPlexSans-Regular.ttf',
        bold: 'IBMPlexSans-Bold.ttf',
        italics: 'IBMPlexSans-Italic.ttf',
        bolditalics: 'IBMPlexSans-BoldItalic.ttf'
    }
}

import 'datatables.net-dt'
import 'datatables.net'
import 'datatables.net-buttons'
import 'datatables.net-buttons/js/dataTables.buttons'
import 'datatables.net-buttons/js/buttons.html5'
import 'datatables.net-buttons/js/buttons.print'
import 'datatables.net-responsive'
import 'datatables.net-select'

export default class LaravelTable {
    table

    constructor(tableElement, columns, actions = null, export_title = null, ajax_route = null) {
        this.table = tableElement
        this.dt

        // Initiate the DataTables.
        let options = {
            dom: `${actions.filter(v => v.type == 'download').length > 0 ? '<"dataTables_B_container"B>' : ''}<"dataTables_lf_container"lf>rtip`,
            select: true,
            responsive: true,
            autoWidth: false,
            columnDefs: [],
            order: [],
        }

        // Add ajax.
        if (ajax_route != null && ajax_route != '') {
            options.processing = true
            options.serverSide = true
            options.ajax = {
                url: ajax_route,
            }
        }

        // Handle column definition.
        columns.forEach((val, idx) => {
            let colOptions = {
                targets: idx,
                orderable: false,
                searchable: false,
            }

            if ('order' in val) options.order.push([idx, val.order])

            if (val.type == 'ajax') {
                colOptions.data = val.content.name
                colOptions.className = val.class

                if (val.content.callback != null)
                    colOptions.render = (data, type, row) => {
                        if (type == 'display') {
                            const callback = new Function('data', 'type', 'row', val.content.callback)
                            return callback(data, type, row)
                        }
                        else return data
                    }
            }

            colOptions.orderable = val.sortable ? true : false
            colOptions.searchable = val.searchable ? true : false

            if (val.priority != null) colOptions.responsivePriority = val.priority

            options.columnDefs.push(colOptions)
        })

        // Add handling for date columns.
        options.columnDefs.push({
            targets: 'date',
            render: (data, type, row) => {
                if (type == 'display') {
                    if (moment(data, "YYYY-MM-DDTHH:mm:ssZ", true).isValid())
                        data = moment(data).format('DD-MM-YYYY HH:mm:ss')
                } else {
                    if (moment(data, "YYYY-MM-DDTHH:mm:ssZ", true).isValid())
                        data = moment(data)
                }
                return data
            }
        })

        if (actions != null) {
            let column_actions = actions.filter(v => v.type != 'download' && v.type != 'create')
            if (column_actions.length > 0) {
                let colOptions = {
                    responsivePriority: 1,
                    width: 0,
                    targets: -1,
                    orderable: false,
                    searchable: false,
                }

                if (ajax_route != null && ajax_route != '') {
                    colOptions.data = column_actions[0].content.name

                    let render_content = []

                    render_content.push(`<div id="st-showmore-objectid" class="st-action-showmore" title="Show More" onclick="dispatchShowMore.apply(this)">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path
            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
    </svg>
</div>`)

                    render_content.push(`<div id="st-actions-objectid"
class="st-action-container hidden md:flex md:justify-center md:w-full">`)

                    column_actions.forEach((v) => {
                        render_content.push(v.content.view)
                    })

                    render_content.push(`</div>`)

                    render_content = render_content.join('\n')


                    colOptions.render = (data, type, row) => {
                        let row_content = render_content.replaceAll('objectid', data)
                        return row_content
                    }
                }

                options.columnDefs.push(colOptions)
            }

            // Handle download.
            if (actions.filter(v => v.type == 'download').length > 0) {
                options.buttons = [
                    { extend: 'copy' },
                    { extend: 'csv' },
                    { extend: 'excel' },
                    {
                        extend: 'pdf',
                        orientation: 'landscape',
                    },
                    { extend: 'print' },
                ]

                if (export_title != null) options.buttons.forEach(val => {
                    val.title = export_title
                })

                options.buttons.forEach(val => {
                    val.exportOptions = {}
                    val.exportOptions.customizeData = (data) => {
                        if (actions.filter(v => v.type != 'download').length > 0) {
                            data.header.pop()
                            data.body.forEach(row => row.pop())
                        }
                    }
                })

                options.buttons.filter(item => item.extend == 'pdf')[0].customize = (doc) => {
                    doc.defaultStyle.font = 'IBMPlexSans'
                }
            }
        }

        // Date range filtering which will search for data ranges.
        $.fn.dataTable.ext.search.push((settings, searchData, index, rowData, counter) => {
            let dates = []
            $(`[id^=${this.table.id}_min]`).each((idx, elem) => dates.push({ index: Number(elem.id.split('_').pop().split('-').pop()), min: elem.valueAsDate == null ? null : moment(elem.valueAsDate).hour(0) }))
            $(`[id^=${this.table.id}_max]`).each((idx, elem) => dates.find((obj) => obj.index == Number(elem.id.split('_').pop().split('-').pop())).max = elem.valueAsDate == null ? null : moment(elem.valueAsDate).hour(0))

            if (dates.every(obj =>
                (obj.min == null && obj.max == null) ||
                (obj.min == null && moment(rowData[obj.index]) <= obj.max) ||
                (moment(rowData[obj.index]) >= obj.min && obj.max == null) ||
                (moment(rowData[obj.index]) >= obj.min && moment(rowData[obj.index]) <= obj.max)
            ))
                return true
            else
                return false
        })

        $(() => {
            this.dt = $(this.table).DataTable(options)

            // Add auto indexing for index column.
            this.dt.on('order.dt search.dt', () => {
                options.columnDefs.forEach((v, i) => {
                    if (columns[i]?.type == 'index')
                        this.dt.column(i, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                            cell.innerHTML = i + 1;
                        })
                })
            }).draw()

            // Add filtering event listener
            this.table.addEventListener('ltfilter', (ev) => {
                const filter_details = ev.detail
                for (let filter of filter_details.filters)
                    this.dt.column(filter.column).search(filter.value)
                this.dt.draw()
            })

            $(`[id^=${this.table.id}_min]`).change(ev => {
                if (ajax_route != null) {
                    let min_date = ev.target.valueAsDate;
                    let column = ev.target.id.split('_').pop().split('-').pop();
                    let max_date = document.getElementById(ev.target.id.replace('_min', '_max')).valueAsDate
                    max_date.setHours(23)
                    max_date.setMinutes(59)
                    this.dt.column(column).search(JSON.stringify({
                        type: 'date',
                        min: min_date,
                        max: max_date
                    }));
                }

                this.dt.draw();
            });

            $(`[id^=${this.table.id}_max]`).change(ev => {
                if (ajax_route != null) {
                    let max_date = ev.target.valueAsDate;
                    let column = ev.target.id.split('_').pop().split('-').pop();
                    max_date.setHours(23)
                    max_date.setMinutes(59)
                    this.dt.column(column).search(JSON.stringify({
                        type: 'date',
                        max: max_date,
                        min: document.getElementById(ev.target.id.replace('_max', '_min')).valueAsDate
                    }));
                }

                this.dt.draw();
            });
        })

        document.addEventListener('redrawlaraveltable', (e) => {
            this.dt.columns.adjust().draw(false)
        })

        // Add click events for mobile responsive.
        let actionCells = this.table.querySelectorAll('.st-action-cell')

        actionCells.forEach((el) => {
            let showMore = el.querySelector('.st-action-showmore')
            let actions = el.querySelector('.st-action-container')
            showMore.toggleVisible = () => {
                actions.classList.toggle('hidden')
                actions.classList.toggle('flex')
            }
            showMore.hide = () => {
                actions.classList.add('hidden')
                actions.classList.remove('flex')
            }
            window.addEventListener('click', _.throttle((ev) => {
                let el_id = showMore.getAttribute('id')
                let ev_source_id = null
                try {
                    ev_source_id = ev.target.closest('div').getAttribute('id')
                } catch (error) {
                    ev_source_id = null
                }

                if (ev_source_id == el_id) showMore.toggleVisible()
                else {
                    if (!showMore.classList.contains('hidden')) showMore.hide()
                }
            }, 500, { trailing: false }))
        })
    }

    static init(id, columns, actions = null, export_title = null, ajax_route = null) {
        let tableEl = document.getElementById(id)
        return new LaravelTable(tableEl, columns, actions, export_title, ajax_route)
    }

    static actionData() {
        return {
            showActions: false
        }
    }
}