@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.css" rel="stylesheet">
    <style>
        trix-toolbar .trix-button-group--file-tools {
            display: none;
        }
    </style>
@endpush
<div class="page-body">
    <div class="container xl">
        @include('includes.alerts')
        <h4>{{ isset($announcementId) ? 'Edit Announcement' : 'Create Announcement' }}</h4>
        <div class="card">
            <form wire:submit={{ isset($announcementId) ? 'update' : 'create' }}>
                <div class="card-body row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="title" class="form-label required">Title</label>
                            <input type="text" placeholder="Enter annoucement title."
                                class="form-control @error('title') is-invalid @enderror" wire:model="title">
                            @error('title')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 ">
                        <label for="visible_type" class="form-label required">Visible Type</label>
                        <select class="form-select @error('visible_type') is-invalid @enderror"
                            wire:model="visible_type">
                            <option value="">Select visible type</option>
                            <option value="visitors_only">Visitors</option>
                            <option value="exhibitors_only">Exhibitors</option>
                            <option value="both">Both</option>
                        </select>
                        @error('visible_type')
                            <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 pt-4">
                        {{-- <div class="mb-3"> --}}
                        <div class="form-check form-switch">
                            <label class="form-check-label ">
                                Is Active
                                <input class="form-check-input" type="checkbox" wire:model="is_active">
                            </label>
                        </div>
                        {{-- </div> --}}
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="description" class="form-label required">Description</label>
                            <input id="trixId" type="hidden" name="content" value="{{ $description }}">
                            <trix-editor input="trixId" class="trix-content"></trix-editor>

                            @error('description')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href={{ route('announcements.index', ['eventId' => $eventId]) }} class="btn btn-secondary me-1">
                        Back </a>
                    <button type="submit"
                        class="btn btn-primary">{{ isset($announcementId) ? 'Update' : 'Create' }}</button>
                </div>
            </form>
        </div>

    </div>
</div>
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>
    <script>
        var trixEditor = document.getElementById("trixId")

        addEventListener("trix-blur", function(event) {
            @this.set('description', trixEditor.getAttribute('value'))
        });
    </script>
@endpush
