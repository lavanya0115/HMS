<div>
    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row">
                <div class="col-lg-4">
                    @livewire('category-handler', ['categoryId' => $categoryId])
                </div>
                <div class="col-lg-8">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div>
                            <h4 class="text">List all Categories</h4>
                        </div>
                    </div>
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($categories) && count($categories) > 0)
                                        @foreach ($categories as $categoryIndex => $category)
                                            <tr wire:key='item-{{ $category->id }}'>
                                                <td>
                                                    {{ $categoryIndex + $categories->firstItem() }}
                                                </td>

                                                <td>
                                                    <div class="text-capitalize">{{ $category->title }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize">{{ $category->description ?? '--' }}
                                                    </div>
                                                </td>

                                                <td>
                                                    <div @class([
                                                        'badge',
                                                        'me-1',
                                                        'bg-success' => $category->is_active,
                                                        'bg-danger' => !$category->is_active,
                                                    ])></div>
                                                    {{ $category->is_active == 1 ? 'Active' : 'Inactive' }}

                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center gap-2">

                                                        <a
                                                            href="{{ route('category', ['categoryId' => $category->id, 'type' => $type, 'page' => $this->paginators['page'], 'pp' => $this->perPage]) }}">
                                                            <span>@include('icons.edit')</span>
                                                        </a>

                                                        <a href="#"
                                                            wire:click.prevent="$dispatch('canDeleteCategory',{{ $category->id }})"
                                                            class="text-danger">
                                                            <span>@include('icons.trash')</span>
                                                        </a>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($categories) && count($categories) == 0)
                                        @livewire('not-found-record-row', ['colspan' => 6])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="row d-flex flex-row mb-3">
                                @if (isset($categories) && count($categories) != 0)
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
                                    @if (isset($categories) && count($categories) >= 0)
                                        {{ $categories->links() }}
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
        Livewire.on('canDeleteCategory', (categoryId) => {
            if (confirm('Are you sure you want to delete this category ?')) {
                Livewire.dispatch('deleteCategory', {
                    categoryId
                });
            }
        });
    </script>
@endpush
