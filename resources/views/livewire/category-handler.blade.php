@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush
<div>
    <h4>{{ isset($categoryId) ? 'Edit ' : 'New ' }}</h4>
    <div class="card">
        <form wire:submit={{ isset($categoryId) ? 'update' : 'create' }}>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row row-cards">
                            <div class="col-md-12">
                                <div>
                                    <label class="form-label required" for="name">Name</label>
                                    <input type="text" id ="name" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('category.title') ? true : false,
                                    ]) placeholder="Name"
                                        wire:model="category.title">
                                    @error('category.title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label required">Type</label>
                                    <select id="type" wire:model="type" @class([
                                        'form-select',
                                        'is-invalid' => $errors->has('type') ? true : false,
                                    ])>
                                        <option value="">Select</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}

                            <div class="col-md-12">
                                <div wire:ignore>
                                    <label class="form-label required">Type</label>
                                    <select id="type"
                                        class="form-select @error('category.type') is-invalid @enderror"
                                        wire:model="category.type" placeholder="Select Type">
                                        <option value="">Select Type</option>
                                        @isset($categoryId)
                                            <option value={{ $category['type'] ?? '' }}>{{ $category['type'] ?? '' }}
                                            </option>
                                        @endisset
                                        @foreach ($types as $type)
                                            <option value="{{ $type }}">{{ $type ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('category.type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Day</label>
                                <select id="day" class="form-select @error('category.day') is-invalid @enderror"
                                    wire:model="category.day" placeholder="Select day">
                                    <option value="">Select Day</option>
                                    <option value="sunday">Sunday</option>
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label ">Show Time From</label>
                                    <input type="time" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('category.show_time_from') ? true : false,
                                    ]) placeholder="Enter price"
                                        wire:model="category.show_time_from">
                                    @error('category.show_time_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label ">Show Time To</label>
                                    <input type="time" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('category.show_time_to') ? true : false,
                                    ]) placeholder="Enter price"
                                        wire:model="category.show_time_to">
                                    @error('category.show_time_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div>
                                    <label class="form-label" for = "desc">Description</label>
                                    <textarea id = "desc" class="form-control" wire:model="category.description" placeholder="Description"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div>
                                    <div class="form-check form-switch">
                                        <label class="form-check-label ">
                                            Is Active
                                            <input class="form-check-input " type="checkbox"
                                                wire:model.live="category.is_active">
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                @if ($categoryId)
                    <a href={{ route('category') }} class="text-danger me-2 text-decoration-none"> Cancel </a>
                @else
                    <a href=# wire:click.prevent ="resetFields" class="text-danger me-2 text-decoration-none"> Reset
                    </a>
                @endif
                <button type="submit" class="btn btn-primary ">{{ isset($categoryId) ? 'Update' : 'Create' }}</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            var category_type = new TomSelect('#type', {
                plugins: ['dropdown_input', 'remove_button'],
                create: true,
                createOnBlur: true,
            });
        });
    </script>
@endpush
