<h2>Profil</h2>
<form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
<form method="post" action="{{ route('profile.update') }}" class="profile-form" enctype="multipart/form-data">@csrf
    @method('patch')
    <div class="avatar-editor"><img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Foto profil saat ini">
        <div><label class="secondary upload-label" for="avatar"><x-icon name="image" />Ganti foto</label><input
                class="sr-only" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif"
                data-preview="avatar-preview"><small>JPG, PNG, atau GIF. Maks. 2 MB.</small><x-input-error
                :messages="$errors->get('avatar')" /></div>
    </div>
    <div class="form-field"><label for="name">Nama</label><input id="name" name="name"
            value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name"><x-input-error
            :messages="$errors->get('name')" /></div>
    <div class="form-field"><label for="bio">Bio <small>Opsional</small></label><textarea id="bio" name="bio" rows="3"
            maxlength="280">{{ old('bio', $user->bio) }}</textarea><x-input-error :messages="$errors->get('bio')" />
    </div>
    <div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email"
            value="{{ old('email', $user->email) }}" required autocomplete="email"><x-input-error
            :messages="$errors->get('email')" /></div>
    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
        <p class="muted">Email belum diverifikasi. <button form="send-verification" class="text-link">Kirim ulang
    verifikasi</button></p>@endif
    @if(session('status') === 'verification-link-sent')
    <p role="status">Email verifikasi dikirim.</p>@endif
    <div class="form-field"><label for="header_image">Foto sampul
            <small>Opsional</small></label>@if($user->header_image)<img id="cover-preview" class="cover-preview"
            src="{{ $user->header_image_url }}" alt="Sampul saat ini">@else<img id="cover-preview" class="cover-preview"
            alt="Pratinjau sampul" hidden>@endif<input type="file" id="header_image" name="header_image"
            accept="image/jpeg,image/png,image/gif" data-preview="cover-preview"><small>JPG, PNG, atau GIF. Maks. 4
            MB.</small><x-input-error :messages="$errors->get('header_image')" /></div>
    <details class="image-url-options">
        <summary>Gunakan URL gambar</summary>
        <div class="form-field"><label for="avatar_url">URL foto profil</label><input type="url" id="avatar_url"
                name="avatar_url"
                value="{{ old('avatar_url', filter_var($user->avatar, FILTER_VALIDATE_URL) ? $user->avatar : '') }}"
                placeholder="https://…"><x-input-error :messages="$errors->get('avatar_url')" /></div>
        <div class="form-field"><label for="header_image_url">URL foto sampul</label><input type="url"
                id="header_image_url" name="header_image_url"
                value="{{ old('header_image_url', filter_var($user->header_image, FILTER_VALIDATE_URL) ? $user->header_image : '') }}"
                placeholder="https://…"><x-input-error :messages="$errors->get('header_image_url')" /></div>
    </details>
    <div class="form-footer">@if(session('status') === 'profile-updated')<span role="status">Perubahan
    disimpan.</span>@endif<button class="primary" type="submit">Simpan perubahan</button></div>
</form>