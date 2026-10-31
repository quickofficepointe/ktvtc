{{--
    Student portal status: no account / never signed in / last sign-in, with the
    button to (create the account and) text the login details.
    $student (with ->user loaded or not), $compact (table cell) or full panel.
--}}
@php
    $portalUser = $student->relationLoaded('user') ? $student->user : $student->user()->first();
    $state = !$portalUser ? 'none' : ($portalUser->last_login_at ? 'active' : 'never');
    $compact = $compact ?? false;
@endphp

@if($compact)
    @if($state === 'active')
        <span class="inline-block whitespace-nowrap text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800" title="Last signed in {{ $portalUser->last_login_at->format('d M Y H:i') }}">
            <i class="fas fa-circle-check mr-1"></i>{{ $portalUser->last_login_at->diffForHumans(null, true) }} ago
        </span>
    @else
        <span class="block whitespace-nowrap text-xs {{ $state === 'none' ? 'text-gray-500' : 'text-amber-700' }}">
            {{ $state === 'none' ? 'No account' : 'Never signed in' }}
            @if($portalUser?->login_details_sent_at)<span class="text-gray-400">· texted {{ $portalUser->login_details_sent_at->format('d M') }}</span>@endif
        </span>
        <form method="POST" action="{{ route('admin.students.send-login', $student) }}" class="mt-1"
              onsubmit="return confirm('{{ $state === 'none' ? 'Create an account and text' : 'Text' }} the login details to {{ $student->phone ?: 'this student' }}?')">
            @csrf
            <button class="whitespace-nowrap text-xs text-red-700 hover:underline" @disabled(!$student->phone) title="{{ $student->phone ? '' : 'No phone number on record' }}">
                <i class="fas fa-paper-plane mr-0.5"></i>{{ $state === 'none' ? 'Create & send' : 'Send login' }}
            </button>
        </form>
    @endif
@else
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-semibold text-gray-900 mb-3"><i class="fas fa-key text-red-700 mr-1"></i> Student portal</h3>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-gray-500">Account</dt>
            <dd>{{ $portalUser ? 'Created ' . $portalUser->created_at?->format('d M Y') : 'Not created yet' }}</dd>
            <dt class="text-gray-500">Signs in with</dt>
            <dd class="font-mono">{{ $portalUser->username ?? $student->student_number ?? '—' }}</dd>
            <dt class="text-gray-500">Last sign-in</dt>
            <dd class="{{ $state === 'active' ? 'text-green-700' : 'text-amber-700' }}">
                {{ $state === 'active' ? $portalUser->last_login_at->format('d M Y H:i') . ' (' . $portalUser->last_login_at->diffForHumans() . ')' : 'Never' }}
            </dd>
            <dt class="text-gray-500">Login details texted</dt>
            <dd>{{ $portalUser?->login_details_sent_at ? $portalUser->login_details_sent_at->format('d M Y H:i') : 'Not yet' }}</dd>
        </dl>
        <div class="mt-4 flex flex-wrap gap-2">
            @if($state !== 'active')
                <form method="POST" action="{{ route('admin.students.send-login', $student) }}"
                      onsubmit="return confirm('{{ $state === 'none' ? 'Create an account and text' : 'Text' }} the login details to {{ $student->phone ?: 'this student' }}?')">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-sm" @disabled(!$student->phone)>
                        <i class="fas fa-paper-plane mr-1"></i>{{ $state === 'none' ? 'Create account & text login details' : 'Text login details' }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.students.send-login', $student) }}"
                      onsubmit="return confirm('This resets their password to their student number and texts it. They will have to choose a new one. Continue?')">
                    @csrf
                    <input type="hidden" name="reset" value="1">
                    <button class="px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-sm"><i class="fas fa-rotate mr-1"></i>Forgot password: reset & text</button>
                </form>
            @endif
        </div>
        @if(!$student->phone)<p class="text-xs text-red-700 mt-2">No phone number on record: add one to text the details.</p>@endif
        @if(!$student->student_number)<p class="text-xs text-gray-500 mt-2">The account is created when the student is enrolled and gets their number.</p>@endif
    </div>
@endif
