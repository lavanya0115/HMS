<div class="page-body">
    <div class="container row">
        @include('includes.alerts')
        <div class="col-md-4">
            <h4>{{ isset($roleId) ? 'Edit Role' : 'New Role' }}</h4>
            <div class="card">
                <form wire:submit={{ isset($roleId) ? 'update' : 'create' }}>
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label required" for="name">Role</label>
                                <input type="text" id ="role" @class([
                                    'form-control',
                                    'is-invalid' => $errors->has('role.name') ? true : false,
                                ]) placeholder="Name"
                                    wire:model="role.name">
                                @error('role.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <label class="form-check-label ">
                                        Is Active
                                        <input class="form-check-input " type="checkbox"
                                            wire:model.live="role.is_active">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        @if ($roleId)
                            <a href={{ route('roles') }} class="text-danger me-2"> Cancel </a>
                        @endif
                        <button type="submit"
                            class="btn btn-primary ">{{ isset($roleId) ? 'Update' : 'Create' }}</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="d-flex flex-row justify-content-between align-items-center">
                <div>
                    <h4 class="text">List of Roles</h4>
                </div>
            </div>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Role</th>
                                <th>Is Active</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($roles) && count($roles) > 0)
                                @foreach ($roles as $roleIndex => $role)
                                    <tr wire:key='item-{{ $role->id }}'>
                                        <td>
                                            {{ $roleIndex + $roles->firstItem() }}
                                        </td>
                                        <td>
                                            <div class="text-capitalize">{{ $role->name }}</div>
                                        </td>
                                        <td>
                                            <div @class([
                                                'badge',
                                                'me-1',
                                                'bg-success' => $role->is_active,
                                                'bg-danger' => !$role->is_active,
                                            ])></div>
                                            {{ $role->is_active == 1 ? 'Active' : 'Inactive' }}

                                        </td>

                                        <td>
                                            <div class="btn-group">
                                                @can('Update Role')
                                                    <a href="#"
                                                        wire:click='edit({{ $role->id }})'>@include('icons.edit')</a>
                                                @endcan
                                                @can('Delete Role')
                                                    <a href="#" class="text-danger ms-2"
                                                        wire:confirm="Are you sure you want to delete this role?"
                                                        wire:click='delete({{ $role->id }})'>@include('icons.trash')</a>
                                                @endcan
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            @if (isset($roles) && count($roles) == 0)
                                @livewire('not-found-record-row', ['colspan' => 4])
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
