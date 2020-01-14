@extends('layouts.app')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/dxDataGrid/css/dx.common.css') }}" />
    <link rel="dx-theme" data-theme="generic.light" href="{{ asset('vendor/dxDataGrid/css/dx.light.css') }}" />
    <style>
        .dx-datagrid .dx-data-row > td.bullet {
            padding-top: 0;
            padding-bottom: 0;
        }
        .dx-datagrid-content .dx-datagrid-table .dx-row .dx-command-edit {
            width: auto;
            min-width: 140px;
        }
    </style>
@endsection

@section('contents')
<h3>test</h3>
<div class="dx-viewport">
    <div class="demo-container">
        <div id="gridContainer"></div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('vendor/dxDataGrid/js/cldr.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/cldr/event.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/cldr/supplemental.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/cldr/unresolved.min.js') }}"></script>	
    <script src="{{ asset('vendor/dxDataGrid/js/globalize.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/globalize/message.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/globalize/number.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/globalize/date.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/globalize/currency.min.js') }}"></script>
    <script src="{{ asset('vendor/dxDataGrid/js/dx.web.js') }}"></script>
    <script>
        var gridDataSource = new DevExpress.data.DataSource({
            key: "id",
            load: function(loadOptions) {
                var d = $.Deferred(),
                        params = {};
                [
                    "skip",     
                    "take", 
                    "requireTotalCount", 
                    "requireGroupCount", 
                    "sort", 
                    "filter", 
                    "totalSummary", 
                    "group", 
                    "groupSummary"
                ].forEach(function(i) {
                    if(i in loadOptions && isNotEmpty(loadOptions[i])) 
                        params[i] = JSON.stringify(loadOptions[i]);
                });
                // params['related'] for group by in filter, added by dandisy
                params['related'] = {
                    'category' : 'category.name'
                };
                // $.getJSON("{{url('api/dxDatagrid/test-datagrid-service')}}", params)
                $.getJSON("{{url('api/dxDatagrid/User')}}", params)
                    .done(function(result) {
                        console.log(result);
                        d.resolve(result.data, { 
                            totalCount: result.totalCount,
                            summary: result.summary,
                            groupCount: result.groupCount
                        });
                    });
                return d.promise();
            }
        });

        function isNotEmpty(value) {
            return value !== undefined && value !== null && value !== "";
        }

        $("#gridContainer").dxDataGrid({
            dataSource: gridDataSource,
            remoteOperations: { groupPaging: true },
            columnAutoWidth: true,
            allowColumnResizing: true,
            columnResizingMode: 'widget', // or 'nextColumn'
            rowAlternationEnabled: true,
            hoverStateEnabled: true, 
            // // showBorders: true,
            grouping: {
                autoExpandAll: false,
                contextMenuEnabled: true
            },
            groupPanel: {
                visible: true
            },       
            searchPanel: {
                visible: true
            },   
            filterRow: {
                visible: true
            },
            headerFilter: {
                visible: true
            },
            columnChooser: {
                enabled: true,
                mode: "dragAndDrop" // or "select"
            },
            columnFixing: {
                enabled: true
            },
            // selection: {
            //     mode: "multiple",
            //     // allowSelectAll : false,
            //     selectAllMode: 'page',
            //     showCheckBoxesMode : "always"
            // },
            // height: 420,            
            paging: {
                pageSize: 10
            },
            pager: {
                showPageSizeSelector: true,
                allowedPageSizes: [10, 50, 100],
                showInfo: true
            },
            keyExpr: "id",
            columns: [
                {
                    caption: "#",
                    allowEditing: false,
                    allowSorting: false,
                    cellTemplate: function (cellElement, cellInfo) {
                        cellElement.html('<span>'+(parseInt(cellInfo.rowIndex, 10)+1)+'</span>');
                    }
                },
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ],
            summary: {
                totalItems: [
                    {
                        column: "id",
                        summaryType: "sum"
                    }, 
                    {
                        column: "name",
                        summaryType: "count"
                    }
                ]
            }
        });
    </script>
@endsection