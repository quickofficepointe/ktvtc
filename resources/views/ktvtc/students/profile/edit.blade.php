@extends($layout ?? 'ktvtc.students.layout.studentlayout')

@section('title', 'My Profile')

@section('content')
<div class="flex justify-center px-4 py-6">
  <div class="w-full max-w-lg bg-white border border-gray-200 shadow-lg rounded-2xl p-8">

    <h2 class="text-center text-lg font-semibold text-black mb-6">My Profile</h2>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
      @csrf

      <!-- Phone Number -->
      <div>
        <label for="phone_number" class="sr-only">Phone Number</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-600 pointer-events-none">
            <i class="fas fa-phone"></i>
          </span>
          <input id="phone_number" name="phone_number" type="text"
            value="{{ old('phone_number', auth()->user()->phone_number) }}"
            placeholder="+2547XXXXXXXX"
            class="block w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500
                   focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600">
        </div>
        @error('phone_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <!-- Bio -->
      <div>
        <label for="bio" class="sr-only">Bio</label>
        <div class="relative">
          <span class="absolute top-3 left-0 pl-3 flex items-start text-red-600 pointer-events-none">
            <i class="fas fa-user"></i>
          </span>
          <textarea id="bio" name="bio" rows="3"
            placeholder="Tell us a little about yourself..."
            class="block w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500
                   focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600">{{ old('bio', auth()->user()->bio) }}</textarea>
        </div>
        @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <!-- Profile Picture -->
      <div>
        <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Profile Picture <span class="font-normal text-gray-500">(optional)</span></label>

        <!-- Existing -->
        @if(auth()->user()->profile_picture)
          <div class="mb-3">
            <img id="preview_existing" src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
              alt="Profile Picture" class="h-24 w-24 rounded-full object-cover border">
          </div>
        @endif

        <!-- Live Preview -->
        <div class="mb-3">
          <img id="preview_new" src="#" alt="New Profile Preview"
            class="hidden h-24 w-24 rounded-full object-cover border">
        </div>

        <input type="file" name="profile_picture" id="profile_picture"
          accept="image/png,image/jpeg,image/jpg,image/webp"
          class="block w-full text-sm text-gray-700 border rounded-lg cursor-pointer focus:outline-none
                 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                 file:text-sm file:font-semibold file:bg-[#B91C1C] file:text-white hover:file:bg-red-700">

        <p class="text-xs text-gray-500 mt-1">Max 3MB. Allowed formats: JPG, JPEG, PNG, WEBP.</p>
        @error('profile_picture') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <!-- Password -->
      <div class="border-t border-gray-200 pt-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-1"><i class="fas fa-lock text-red-600 mr-1"></i> Change password</h3>
        <p class="text-xs text-gray-500 mb-3">Leave blank to keep your current password. A new password needs at least 8 characters.</p>
        <div class="space-y-3">
          <input id="current_password" name="current_password" type="password" autocomplete="current-password" placeholder="Current password (the one you signed in with)"
            class="block w-full px-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600">
          @error('current_password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
          <input id="password" name="password" type="password" autocomplete="new-password" placeholder="New password"
            class="block w-full px-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600">
          @error('password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
          <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Type the new password again"
            class="block w-full px-3 py-2 rounded-lg border border-gray-300 text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600">
        </div>
      </div>

      <!-- Submit -->
      <button type="submit"
        class="w-full py-2 rounded-lg bg-[#B91C1C] hover:bg-red-700 text-white font-semibold
               focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500">
        Save Profile
      </button>
    </form>
  </div>
</div>

<script>
document.getElementById('profile_picture').addEventListener('change', function(event){
  const file = event.target.files[0];
  if (file){
    // check size limit (3MB)
    if (file.size > 3 * 1024 * 1024) {
      alert("File size exceeds 3MB!");
      event.target.value = "";
      return;
    }

    // show preview
    const preview = document.getElementById('preview_new');
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
  }
});
</script>
@endsection
