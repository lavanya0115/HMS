<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row ">
                <div class="col">
                    <h2 class="page-title">
                        Import visitors for Tele-Calling
                    </h2>
                </div>

                <div class="col">
                    <a class="btn btn-primary">
                        {{--  href="{{ route('visitor-registration') }}" --}}
                        @include('icons.plus')
                        Tele-Calling Visitors
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row row-cards">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-text">
                                Import visitors from an XLSX file. Sample file can be downloaded <a
                                    href="{{ asset('assets/visitors.xlsx') }}" download>here</a>.
                            </p>
                            <div class="">
                                <input type="file" id="import-file" class="form-control">
                            </div>

                            <div class="mt-3">
                                <button id="import-visitors" class="btn btn-primary">Import</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('/libs/sheetjs/xlsx.full.min.js') }}"></script>

    <script>
        if (typeof require !== 'undefined') XLSX = require('xlsx');
        jQuery(document).ready(function() {
            Livewire.on('processData', (data) => {
                Livewire.emit('importingData', data);
            });

            $('#import-visitors').on('click', function() {
                const attachmentField = document.getElementById("import-file");
                const file = attachmentField ? attachmentField.files[0] : [];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const data = e.target.result;
                    let workbook;
                    try {
                        workbook = XLSX.read(data, {
                            type: "array",
                            cellDates: true,
                        });
                    } catch (e) {
                        alert(e);
                        return false;
                    }

                    let workbookSheetName = workbook.SheetNames[0];
                    let workSheet = workbook.Sheets[workbookSheetName];
                    let readArgs = {
                        defval: "",
                        header: 1,
                        blankrows: false,
                    };
                    let jsonData = XLSX.utils.sheet_to_json(workSheet, readArgs);

                    console.log('[File Data] : ', jsonData);
                    @this.dispatch('importingData', {
                        data: jsonData
                    });
                }
                reader.readAsArrayBuffer(file);
            });
        });
    </script>
@endpush
