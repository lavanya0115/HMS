@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div class="p-3">
    <div class="d-flex flex-row justify-content-between align-items-center">
        <div>
            <h4 class="text">{{ isset($stallId) ? 'Edit Stall' : 'New Stall' }}</h4>
        </div>
        <div class="mb-2">
            <a href="{{ route('stall-summary') }}" class="btn btn-secondary">Back To List</a>
        </div>
    </div>
    <div class="card">
        <form id='stallForm' wire:submit={{ isset($stallId) ? 'update' : 'create' }}>
            <div class="card-body">
                <div class="row row-cards">
                    <div class="col-md-6">
                        <div class="mb-2" id="ts">
                            {{-- @dd($events) --}}
                            <div wire:ignore>
                                <label class="form-label required tomselect" for="event_id">Event</label>
                                <select id="event_id" type="select" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('event_id') ? true : false,
                                ])
                                    wire:model.live="event_id" autocomplete="off">
                                    <option value="">Select The Event</option>
                                    @foreach ($events as $event)
                                        <option value={{ $event->id }}>
                                            {{ $event->event_description ?? $event->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('event_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="mb-2">
                            <label class="form-label required" for="stall_number">Stall No</label>
                            <input id="stall_number" @class([
                                'form-control',
                                'is-invalid' => $errors->has('stall_number') ? true : false,
                            ]) wire:model="stall_number">
                            @error('stall_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="mb-2">
                            <label class="form-label " for="status">Status</label>
                            <input id="status" @class([
                                'form-control',
                                'is-invalid' => $errors->has('status') ? true : false,
                            ]) wire:model="status" placeholder="status"
                                disabled>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label required" for="hall_number">Hall No</label>
                            <input id="hall_number" type="text" @class([
                                'form-control',
                                'is-invalid' => $errors->has('hall_number') ? true : false,
                            ])
                                wire:model="hall_number">
                            @error('hall_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2" id="ts">
                            <div>
                                <label class="form-label" for="special-feature">Special
                                    Feature</label>
                                <select id="special-feature" type="select" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('special_feature') ? true : false,
                                ])
                                    wire:model.live="special_feature">
                                    <option value="">Select Special Feature</option>
                                    <option value="corner">Corner</option>
                                    <option value="side-open">Side Open</option>
                                    <option value="corner-and-side-open"> Corner + Side Open</option>
                                    <option value="near-entrance">Near Entrance</option>
                                    <option value="others">Others</option>
                                </select>
                            </div>
                            @error('special_feature')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2" id="ts">
                            <label class="form-label required" for="stall_type">Stall Type</label>
                            <select id="stall_type" type="select" @class([
                                'form-control',
                                'is-invalid' => $errors->has('stall_type') ? true : false,
                            ])
                                wire:model.live="stall_type">
                                <option value="">Select Stall Type</option>
                                <option value="shell">Shell</option>
                                <option value="bare">Bare</option>
                            </select>
                            @error('stall_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label required " for="size">Size (Sq.Mtr)</label>
                            <input id="size" type="text" @class([
                                'form-control',
                                'is-invalid' => $errors->has('size') ? true : false,
                            ]) wire:model.live="size"
                                placeholder="size">
                            @error('size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                @if ($stallId)
                    <a href={{ route('stall-summary') }} class="text-danger me-2"> Cancel </a>
                @else
                    <a href=# wire:click.prevent ="resetFields" class="text-danger me-2"> Reset </a>
                @endif
                <button class="btn btn-primary">{{ isset($stallId) ? 'Update Stall' : 'Create Stall' }}</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            var events = new TomSelect('#event_id', {
                plugins: ['dropdown_input'],
            });
        });
    </script>
@endpush
