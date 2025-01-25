<div>
    <h4>New</h4>
    <div class="card">
        
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row row-cards">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-2">
                                        <div class="avatar-edit ">
                                            <label class="form-label requred" for="video">Video Upload</label>
                                            <input type='file' class="form-control" wire:model="video" id="video"
                                                name="video" />
                                            <span wire:loading wire:target="video" class="text-info fw-bold mt-2 fs-5">
                                                Uploading...
                                            </span>
                                        </div>
                                        @error('video')
                                            <span class="text text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href={{ route('video') }} class="text-danger me-2 text-decoration-none"> Cancel </a>
            <button wire:click.prevent="create" class="btn btn-primary ">Upload</button>
        </div>
    
    </div>
</div>
