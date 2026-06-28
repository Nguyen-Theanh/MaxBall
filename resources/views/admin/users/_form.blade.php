@csrf

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Ho ten</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">So dien thoai</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="address" class="form-label">Dia chi</label>
                        <input id="address" name="address" value="{{ old('address', $user->address) }}" class="form-control @error('address') is-invalid @enderror">
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Mat khau</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $requirePassword ? 'required' : '' }}>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @unless ($requirePassword)
                            <div class="form-text">De trong neu khong doi mat khau.</div>
                        @endunless
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Nhap lai mat khau</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" {{ $requirePassword ? 'required' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="role" class="form-label">Phan quyen</label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check form-switch">
                    <input type="checkbox" id="status" name="status" value="1" class="form-check-input" @checked(old('status', $user->status ?? true))>
                    <label for="status" class="form-check-label">Tai khoan dang hoat dong</label>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">{{ $submitLabel }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Quay lai</a>
        </div>
    </div>
</div>
