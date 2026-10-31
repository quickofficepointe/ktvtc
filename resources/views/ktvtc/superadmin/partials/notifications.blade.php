@forelse($notifications as $n)
    <div class="px-4 py-3 border-b text-sm">
        <p class="text-gray-800">{{ $n->description }}</p>
        <p class="text-xs text-gray-500">{{ $n->user->name ?? 'System' }} · {{ $n->created_at?->diffForHumans() }}</p>
    </div>
@empty
    <div class="px-4 py-6 text-center text-sm text-gray-500">No notifications.</div>
@endforelse
