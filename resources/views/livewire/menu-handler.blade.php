<div>
    @if (isset($menu['id']))
        <h4>Edit menu <a class="btn btn-outline-primary btn-sm ms-3" href="{{ route('menu.items.list') }}">Add New</a>
        </h4>
    @else
        <h4>Add new menu</h4>
    @endif
    <div class='card'>

        <form wire:submit="{{ isset($menu['id']) ? 'update' : 'create' }}">

            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row row-cards">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label required">Name</label>
                                    <input type="text" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.name') ? true : false,
                                    ])
                                        placeholder="Enter Menu Item Name" wire:model="menu.name">
                                    @error('menu.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label required">Name in Kannada</label>
                                    <input type="text" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.kannada_name') ? true : false,
                                    ])
                                        placeholder="Enter Menu Item Name" wire:model="menu.kannada_name">
                                    @error('menu.kannada_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="mb-1">
                                    <div class="form-label required">Category</div>
                                    <select wire:model="menu.category_id" @class([
                                        'form-select',
                                        'is-invalid' => $errors->has('menu.category_id') ? true : false,
                                    ])>
                                        <option value="">Select Category</option>
                                        @if (isset($categories) && !empty($categories))
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->title }}</option>
                                            @endforeach
                                        @endif

                                    </select>
                                    @error('menu.category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="mb-1">
                                    <label class="form-label required">Qty</label>
                                    <input type="number" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.qty') ? true : false,
                                    ]) placeholder="Enter qty"
                                        wire:model="menu.qty">
                                    @error('menu.qty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div wire:ignore class="mb-1">
                                    <label class="form-label required">Unit type</label>
                                    <select id="unit_type" wire:model="menu.unit_type" @class([
                                        'form-select',
                                        'is-invalid' => $errors->has('menu.unit_type') ? true : false,
                                    ])>
                                        <option value="">Select</option>
                                        @if (!empty($unitTypes))
                                            @foreach ($unitTypes as $type)
                                                <option value="{{ $type->title }}">{{ $type->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('menu.unit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label required">Basic Price</label>
                                    <input type="text" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.price') ? true : false,
                                    ]) placeholder="Enter price  "
                                        wire:model="menu.price">
                                    @error('menu.price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label required">Tax %</label>
                                    <input type="number" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.tax') ? true : false,
                                    ]) placeholder="Enter tax %  "
                                        wire:model.live="menu.tax">
                                    @error('menu.tax')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label required">Tax Amount</label>
                                    <input type="number" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.tax_amount') ? true : false,
                                    ]) placeholder="Enter price"
                                        disabled wire:model="menu.tax_amount">
                                    @error('menu.tax_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label required">MRP</label>
                                    <input type="number" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.mrp') ? true : false,
                                    ]) placeholder="Item MRP" disabled
                                        wire:model="menu.mrp">
                                    @error('menu.mrp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 align-self-center mt-4">
                                <div class="mb-1">
                                    <div class="form-check form-switch">
                                        <label class="form-check-label ">
                                            Is Available
                                            <input class="form-check-input " type="checkbox"
                                                wire:model.live="menu.is_available">
                                        </label>
                                    </div>
                                    @error('menu.is_available')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if (!$menu['is_available'])
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label required">Custom Status</label>
                                        <input type="text" @class([
                                            'form-control',
                                            'is-invalid' => $errors->has('menu.custom_status') ? true : false,
                                        ]) placeholder="Enter Status"
                                            wire:model="menu.custom_status">
                                        @error('menu.custom_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label ">Description</label>
                                    <textarea @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.description') ? true : false,
                                    ]) wire:model="menu.description"></textarea>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('menu.items.list') }}" class="text-danger me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>

        </form>
    </div>
</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            var category_type = new TomSelect('#unit_type', {
                plugins: ['dropdown_input', 'remove_button'],
                create: true,
                createOnBlur: true,
            });
        });
    </script>
@endpush
