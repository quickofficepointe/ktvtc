@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'System Settings')

@section('content')
<form method="POST" action="{{ route('super-admin.system.update-settings') }}" class="bg-white rounded-xl shadow-sm border p-5 space-y-4 max-w-2xl text-sm">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-xs text-gray-500 mb-1">Application name</label>
        <input name="app_name" required value="{{ old('app_name', $settings['app_name'] ?? config('app.name')) }}" class="w-full border rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Application URL</label>
        <input name="app_url" type="url" required value="{{ old('app_url', $settings['app_url'] ?? config('app.url')) }}" class="w-full border rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Timezone</label>
        <input name="timezone" required value="{{ old('timezone', $settings['timezone'] ?? config('app.timezone')) }}" class="w-full border rounded-lg px-3 py-2">
    </div>
    @foreach(['registration_enabled' => 'Allow public registration', 'email_verification' => 'Require email verification'] as $key => $label)
        <label class="flex items-center gap-2">
            <input type="hidden" name="{{ $key }}" value="0">
            <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $settings[$key] ?? true))> {{ $label }}
        </label>
    @endforeach
    <p class="text-xs text-gray-500">These are stored for reference; the running app still takes its core settings from the server's .env file.</p>
    <button class="px-4 py-2 bg-gray-800 text-white rounded-lg">Save</button>
</form>
@endsection
