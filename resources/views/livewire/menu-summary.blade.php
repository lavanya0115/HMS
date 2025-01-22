<div>
    <div class="page-body">
        <div class="container-xl">
            <div class="col-lg-12">
                @include('includes.alerts')
            </div>
            <div class="row justify-content-end ">
                <div class="col-md-3">
                    <a title="import" data-bs-toggle="modal" data-bs-target="#importModal"
                        class="btn btn-warning d-flex float-end">
                        <span class="text-white" style="cursor: pointer;">
                            @include('icons.file-import')
                        </span>
                        Import
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    @livewire('menu-handler', ['menuId' => $menuId])
                </div>

                <div class="col-lg-8">
                    <div class="d-flex justify-content-between">
                        <h4>List of Menu Items</h4>
                        @if (!empty($selectedItems))
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="me-2">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#menuItemModal"
                                            class="text-black">
                                            <span>@include('icons.edit')</span>
                                        </a>
                                    </span>
                                    <span class="ms-2">
                                        <a href="#" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Delete Menu" wire:click.prevent="$dispatch('canDeleteMenus')"
                                            class="text-black">
                                            <span>@include('icons.trash')</span>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="selectedAll"
                                                    wire:change="toggleSelectAll"
                                                    style="border-color: rgb(134, 132, 132);">
                                            </label>
                                        </th>
                                        <th>#</th>
                                        <th>Category</th>
                                        <th>Name</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                        <th>Basic Price</th>
                                        <th>Tax %</th>
                                        <th>MRP (₹)</th>

                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($menuItems) && count($menuItems) > 0)
                                        @foreach ($menuItems as $index => $menu)
                                            <tr>
                                                {{-- @dd($menu, $menuItems, $index) --}}
                                                <td>
                                                    <div>
                                                        <label class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                onclick="event.stopPropagation();"
                                                                wire:model="selectedItems"
                                                                wire:change="getSelectedItems"
                                                                value="{{ $menu->id }}"
                                                                style="border-color:rgb(134, 132, 132);">
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $index + $menuItems->firstItem() }}
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu?->category?->title }}</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span @class([
                                                            'badge',
                                                            'me-1',
                                                            'bg-success' => $menu->is_available,
                                                            'bg-danger' => !$menu->is_available,
                                                        ])></span>
                                                        <div class="text-capitalize">
                                                            {{ $menu->name }} {{ $menu->kannada_name ?? '' }}</div>


                                                    </div>
                                                    @if (!$menu->is_available)
                                                        <small>{{ $menu->custom_status ?? 'Not Available' }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu->qty }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu->unit_type ?? '--' }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu->price }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu->tax ?? '--' }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu->mrp ?? '--' }}</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="{{ route('menu.items.list', ['menuId' => $menu->id]) }}"
                                                            title="Edit" data-toggle="tooltip" data-placement="top">
                                                            <span>@include('icons.edit')</span>
                                                        </a>

                                                        <a href="#"
                                                            wire:click.prevent="$dispatch('canDeleteMenu',{{ $menu->id }})"
                                                            class="text-danger">
                                                            <span>@include('icons.trash')</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($menuItems) && count($menuItems) == 0)
                                        @livewire('not-found-record-row', ['colspan' => 10])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">

                            <div class="row d-flex flex-row mb-3">
                                @if (isset($menuItems) && count($menuItems) != 0)
                                    <div class="col">
                                        <div class="d-flex flex-row mb-3">
                                            <div>
                                                <label class="p-2" for="perPage">Per Page</label>
                                            </div>
                                            <div>
                                                <select class="form-select" id="perPage" name="perPage"
                                                    wire:model="perPage"
                                                    wire:change="changePageValue($event.target.value)">
                                                    <option value=10>10</option>
                                                    <option value=50>50</option>
                                                    <option value=100>100</option>
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col d-flex justify-content-end">
                                    @if (isset($menuItems) && count($menuItems) >= 0)
                                        {{ $menuItems->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Updation --}}
    <div wire:ignore.self class="modal modal-blur fade" id="menuItemModal" role="dialog" aria-hidden="true"
        data-bs-backdrop='static' tabindex="-1" aria-labelledby="staticModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticModalLabel">Update Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="updateStatus">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="row row-cards">

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

                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label ">Custom Status</label>
                                                <input type="text" @class([
                                                    'form-control',
                                                    'is-invalid' => $errors->has('menu.custom_status') ? true : false,
                                                ])
                                                    placeholder="Enter Status" wire:model="menu.custom_status">
                                                @error('menu.custom_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end mt-2">
                            <a href="{{ route('menu.items.list') }}" class="text-danger me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div wire:ignore.self class="modal modal-blur fade" id="importModal" role="dialog" aria-hidden="true"
        data-bs-backdrop='static' tabindex="-1" aria-labelledby="staticModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticModalLabel">Menus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <p class="card-text">
                                Import Menus from an Excel file. Download the sample file
                                <a href="{{ asset('assets/menus.xlsx') }}" target="_blank" download>here</a>.
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
@push('scripts')
    <script>
        Livewire.on('canDeleteMenu', (menuId) => {
            if (confirm('Are you sure you want to delete this MenuItem ?')) {
                Livewire.dispatch('deleteMenu', {
                    menuId
                });
            }
        });
        Livewire.on('canDeleteMenus', () => {
            if (confirm('Are you sure you want to delete these MenuItems ?')) {
                Livewire.dispatch('deleteSelected');
            }
        });
    </script>
    <script src="{{ asset('/libs/sheetjs/xlsx.full.min.js') }}"></script>

    <script>
        if (typeof require !== 'undefined') XLSX = require('xlsx');
        jQuery(document).ready(function() {
            Livewire.on('processData', () => {
                const attachmentField = document.getElementById("file");
                const file = attachmentField ? attachmentField.files[0] : [];
                if (!file) {
                    alert("Please select a file before importing.");
                    return;
                }
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
                    console.log("Parsed JSON:", jsonData);
                    @this.call('import', jsonData);
                }
                reader.readAsArrayBuffer(file);
            });
        });
    </script>
@endpush
