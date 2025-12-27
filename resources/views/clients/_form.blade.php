<div class="card-body">
    <div class="mb-3">
        <label class="form-label required">Name</label>
        <div>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Client Name" value="{{ old('name', $client->name ?? '') }}">
            <small class="form-hint">The name of the client</small>
            @error('name')
            <p class="invalid-feedback">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="mb-3">
        <label class="form-check form-switch">
            <input type="checkbox" class="form-check-input" name="enabled" value="1"
                   @if(old('enabled', $client->enabled)) checked @endif>
            Enabled
        </label>
    </div>
    <div class="mb-3 col-6">
        <label class="form-label required">Interval</label>
        <div class="input-group mb-2 col-4">
            <input type="text" name="interval" class="form-control @error('interval') is-invalid @enderror"
               value="{{ old('interval', $client->interval) }}">
            <span class="input-group-text">s</span>
        </div>
        <small class="form-hint">The device code status refresh interval</small>
        @error('interval')
            <p class="invalid-feedback">{{ $message }}</p>
        @enderror
    </div>
    <div class="mb-3 col-6">
        <label class="form-label required">Expiry</label>
        <div class="input-group mb-2 col-4">
            <input type="text" name="expires_in" class="form-control @error('expires_in') is-invalid @enderror"
                   value="{{ old('expires_in', $client->expires_in) }}">
            <span class="input-group-text">s</span>
        </div>
        <small class="form-hint">How long before a device code expires</small>
        @error('expires_in')
        <p class="invalid-feedback">{{ $message }}</p>
        @enderror
    </div>
</div>
