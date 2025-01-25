<div>
    <div wire:ignore.self class="modal modal-blur fade" id="importModal" role="dialog" aria-hidden="true"
        data-bs-backdrop='static' tabindex="-1" aria-labelledby="staticModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticModalLabel">{{ ucfirst($title) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <p class="card-text">
                                Import {{ $title }} from an Excel file. Download the sample file
                                <a href="{{ asset('assets/' . $title . '.xlsx') }}" target="_blank" download>here</a>.
                            </p>
                            <div>
                                <input type="file" name ="file1" id="file" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="$dispatch('processData')" type="button"
                            class="btn btn-primary">Import</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
