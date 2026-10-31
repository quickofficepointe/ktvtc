<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scholarships | KTVTC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-sm border p-8 max-w-md w-full text-center">
        <h1 class="text-xl font-semibold text-gray-800">Scholarships</h1>
        <p class="text-gray-600 mt-3">Hello {{ auth()->user()->name }}. The scholarship area is not open yet. Ask the system administrator if you need a different role.</p>
        <div class="mt-6 flex justify-center gap-3 text-sm">
            <a href="{{ route('profile.edit') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">My profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="px-4 py-2 bg-gray-800 text-white rounded-lg">Log out</button>
            </form>
        </div>
    </div>
</body>
</html>
