<div>
    <div class="page-body">
        <div class="container-xl">
            <div class="col-lg-12">
                @include('includes.alerts')
            </div>


            <div class="row">
                <div class="col-lg-4">
                    @livewire('menu-handler', ['menuId' => $menuId])
                </div>

                <div class="col-lg-8">
                    <h4>List of Menu Items</h4>

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Nos</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($menuItems) && count($menuItems) > 0)
                                        @foreach ($menuItems as $index => $menu)
                                            <tr>
                                                <td>
                                                    {{ $index + $menuItems->firstItem() }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span @class([
                                                            'badge',
                                                            'me-1',
                                                            'bg-success' => $menu->is_available,
                                                            'bg-danger' => !$menu->is_available,
                                                        ])></span>
                                                        <div class="text-capitalize">{{ $menu->name }}</div>

                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu->nos }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu->price }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $menu?->category?->title }}</div>
                                                </td>

                                                <td>

                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="{{ route('menu.items.list', ['menuId' => $menu->id, 'p' => $this->paginators['p'], 'pp' => $this->perPage]) }}"
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
                                        @livewire('not-found-record-row', ['colspan' => 6])
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
    </script>
@endpush
