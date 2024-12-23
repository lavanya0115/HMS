@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div>
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    @include('includes.alerts')
                    <h2 class="page-title">
                        {{ $templateId ? 'Edit Templates' : 'Add New Templates' }}
                    </h2>
                </div>

                <div class="col-auto">
                    <a class="btn" href="{{ route('email-templates.summary') }}">
                        Go to Email Templates List
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-lg-8">
                    <form wire:submit.prevent="saveEmailTemplate">
                        <div class='card'>
                            <div class="card-body">

                                <div class="mb-3 row">
                                    <label class="col-3 col-form-label">Target</label>
                                    <div class="col">
                                        <select class="form-select" wire:model.live='targetElement' id="target-select">
                                            <option value="all-exhibitors">All Exhibitors</option>
                                            <option value="hall">Hall</option>
                                            <option value="specific-exhibitors">Select Specific Exhibitors</option>
                                        </select>

                                    </div>
                                </div>

                                @if ($targetElement == 'hall')
                                    <div class="mb-3 row" id="hall-select-row">
                                        <label class="col-3 col-form-label">Select Hall</label>
                                        <div class="col">
                                            <select wire:model='selectedHall' class="form-select">
                                                @foreach ($halls as $hall)
                                                    <option value="{{ $hall }}">{{ $hall }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3 row {{ $targetElement == 'specific-exhibitors' ? '' : 'd-none' }}"
                                    id="exhibitors-select-row">
                                    <label class="col-3 col-form-label">Select Exhibitors</label>
                                    <div class="col">
                                        <div wire:ignore>
                                            <select wire:model='selectedExhibitors' id="specific_Exhibitors" multiple>
                                                @foreach ($exhibitors as $exhibitor)
                                                    <option value="{{ $exhibitor->id }}">{{ $exhibitor->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2 row">
                                    <div> Use the following attributes to customize the email message. Just copy and
                                        paste the attributes.</div>
                                    <div class="d-flex gap-1">
                                        <label class="text-secondary"><i>{exhibitor_name}</i></label>,
                                        <label class="text-secondary"><i>{event_title}</i></label>
                                    </div>
                                </div>

                                <div class="mb-2 row">
                                    <label class="col-3 col-form-label">Subject</label>
                                    <div class="col">
                                        <input type="text" wire:model='subject' class="form-control"
                                            placeholder="Subject">
                                        @error('subject') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3 mb-0">
                                        <label class="form-label">Message</label>
                                        <textarea rows="5" wire:model='message' class="form-control" placeholder="Here can be your description"></textarea>
                                        @error('message') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <a href="{{ route('email-templates.create') }}" class="text-danger me-2">Reset</a>
                                <button type="submit"
                                    class="btn btn-primary">{{ $templateId ? 'Update Templates' : 'Save Templates' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>


    <script>
        new TomSelect('#specific_Exhibitors', {
            placeholder: 'Select exhibitors',
            plugins: {
                remove_button: {
                    title: 'Remove this item',
                },
                dropdown_input: {},
            },
            persist: false,
        });
    </script>
@endpush
