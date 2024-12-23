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
        <h3 class="ms-5">{{ isset($seminarId) ? 'Update Seminar' : 'Create Seminar' }}</h3>
        <div class="card mx-5">
            <form wire:submit={{ isset($seminarId) ? 'update' : 'create' }}>
                <div class="card-body">
                    <div class="row">
                        @if ($seminarId)
                            <div class="text-center">
                                <div>
                                    <img src="{{ asset('storage/' . ($seminar['image'] ?? '')) }}"
                                        class="rounded-circle avatar-xl" height="100" width="100" />
                                </div>
                                <input type="file"class="form-control" id="updateImage" wire:model="photo" hidden />
                                <br>
                                <button class="w-25 btn mx-auto border-0 bg-default mb-1"
                                    onclick="document.getElementById('updateImage').click(event.preventDefault())">
                                    @include('icons.edit')
                                </button>
                            </div>
                        @else
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="avatar-edit ">
                                        <label class="form-label" for="imageUpload">Image Upload</label>
                                        <input type='file' class="form-control" wire:model="photo"
                                            id="imageUpload" />
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required" for="title">Title</label>
                                <input id="title" type="text" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('seminar.title') ? true : false,
                                ])
                                    wire:model="seminar.title" placeholder="Enter Seminar Title">
                                @error('seminar.title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label required" for="date">Date</label>
                                <input id="startDate" type="date"
                                    class="form-control @error('seminar.date') is-invalid @enderror"
                                    wire:model="seminar.date" min="{{ $start }}" max="{{ $end }}"
                                    placeholder="select date">
                                @error('seminar.date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label required" for="amount">Amount</label>
                                <input type="text" class="form-control @error('seminar.amount') is-invalid @enderror"
                                    wire:model="seminar.amount" pattern="^\d+(\.\d{1,2})?$" placeholder="Enter amount">
                                @error('seminar.amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label required" for="starttime">Start Time</label>
                                <input id="starttime" type="time"
                                    class="form-control @error('seminar.start_time') is-invalid @enderror"
                                    wire:model.defer="seminar.start_time" placeholder="HH:MM" min="10:00"
                                    max="18:00">
                                @error('seminar.start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label required" for="endtime">End Time</label>
                                <input id="endtime" type="time"
                                    class="form-control @error('seminar.end_time') is-invalid @enderror"
                                    wire:model.defer="seminar.end_time" placeholder="HH:MM" min="10:00"
                                    max="18:00">
                                @error('seminar.end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label required" for="location">Location</label>
                            <textarea class="form-control @error('seminar.location') is-invalid @enderror" wire:model="seminar.location"
                                placeholder="Enter location"></textarea>
                            @error('seminar.location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label required" for="description">Description</label>
                            <input id="trixId" type="hidden" name="content" value="{{ $seminar['description'] }}">
                            <trix-editor input="trixId" class="trix-content"></trix-editor>

                            @error('seminar.description')
                                <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="seminar.is_active">
                                <span class="form-check-label">Status</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href={{ route('seminars', ['eventId' => $eventId]) }} class="btn btn-secondary me-1">
                        Back </a>
                    <button type="submit"
                        class="btn btn-primary">{{ isset($seminarId) ? 'Update Seminar' : 'Create Seminar' }}</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>
        <script>
            var trixEditor = document.getElementById("trixId")

            addEventListener("trix-blur", function(event) {
                @this.set('seminar.description', trixEditor.getAttribute('value'))
            });
        </script>
    @endpush
