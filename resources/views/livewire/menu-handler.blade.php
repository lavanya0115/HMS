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
                                <div class="mb-1">
                                    <label class="form-label required">Unit type</label>
                                    <select wire:model="menu.unit_type" @class([
                                        'form-select',
                                        'is-invalid' => $errors->has('menu.unit_type') ? true : false,
                                    ])>
                                        <option value="">Select</option>
                                        <option value="plate">Plate </option>
                                        <option value="pieces">Pieces</option>
                                        <option value="grams">Grams</option>
                                        <option value="ltr">Litters</option>
                                    </select>
                                    @error('menu.unit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label required">Price</label>
                                    <input type="number" @class([
                                        'form-control',
                                        'is-invalid' => $errors->has('menu.price') ? true : false,
                                    ]) placeholder="Enter price  "
                                        wire:model="menu.price">
                                    @error('menu.price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
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
