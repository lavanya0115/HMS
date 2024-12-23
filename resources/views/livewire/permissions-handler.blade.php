<div class="page-body">
    <div class="container-xl">
        @include('includes.alerts')
        <div class="col-md-6">
            <div class="mb-4">
                <label for="roles" class="form-label required fw-bold">Roles</label>
                <div>
                    <select class="form-select" wire:model.live="role" wire:change='getPermissionIds()'
                        @error('role') is-invalid @enderror">
                        <option value="">Select Role</option>
                        @foreach ($roles as $roleKey => $role)
                            <option value={{ $roleKey }}>{{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('role')
                    <div class="error text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row ms-4">
                    @foreach ($permissionsCategory as $categoryName => $categoryPermissions)
                        <div class="col-md-3 mb-4">
                            <h4>{{ $categoryName }}</h4>
                            @foreach ($categoryPermissions as $permission)
                                <div class="mt-2">
                                    <label>
                                        <input type="checkbox" wire:model="permissionIds" value="{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                @can('Assign Permission')
                    <div class="card-footer text-end mt-3">
                        <button class="btn btn-secondary" wire:click="assignPermissions">Assign Permissions</button>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</div>
