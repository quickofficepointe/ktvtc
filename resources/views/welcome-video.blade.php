<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome video · KTVTC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-red-700 text-white">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-3">
            <img src="{{ asset('Assets/images/Kenswed_logo.png') }}" alt="KTVTC" class="h-9 bg-white rounded p-1">
            <span class="font-semibold">Welcome, {{ Str::before(Auth::user()->name, ' ') }}</span>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $video['title'] }}</h1>
            <p class="text-gray-600 mt-1">
                @if($watched)
                    Watch it again whenever you need a reminder.
                @else
                    Before you start, please watch this short video ({{ $video['minutes'] }} {{ Str::plural('minute', $video['minutes']) }}). It shows you how everything works.
                @endif
            </p>
        </div>

        <div class="bg-black rounded-xl overflow-hidden shadow-lg">
            <video id="howto" class="w-full aspect-video" controls playsinline preload="metadata"
                   src="{{ asset($video['file']) }}?v={{ filemtime(public_path($video['file'])) }}"></video>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap items-center justify-between gap-3">
            <p id="hint" class="text-sm text-gray-600 min-w-0">
                @if($watched)
                    <i class="fas fa-check-circle text-green-600 mr-1"></i>You have already watched this video.
                @else
                    <i class="fas fa-play-circle text-red-700 mr-1"></i>"Continue" unlocks when the video ends.
                @endif
            </p>
            <div class="flex flex-wrap items-center gap-3">
                @unless($watched)
                    <form method="POST" action="{{ route('welcome-video.skip') }}">
                        @csrf
                        <button class="text-sm text-gray-500 hover:text-gray-700 underline">Skip for now</button>
                    </form>
                @endunless
                <form method="POST" action="{{ route('welcome-video.watched') }}">
                    @csrf
                    <button id="continueBtn" @disabled(!$watched)
                            class="bg-red-700 hover:bg-red-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-6 py-3 rounded-lg font-semibold">
                        Continue <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </form>
            </div>
        </div>
        @unless($watched)
            <p class="text-xs text-gray-500">If you skip, the video will be shown again the next time you sign in.</p>
        @endunless
    </main>

    <script>
        (function () {
            const v = document.getElementById('howto'), btn = document.getElementById('continueBtn'), hint = document.getElementById('hint');
            let furthest = 0;
            // Jumping ahead does not count as watching: go back to the furthest point reached
            v.addEventListener('timeupdate', () => { if (!v.seeking && v.currentTime > furthest && v.currentTime - furthest < 2) furthest = v.currentTime; });
            v.addEventListener('seeking', () => { if (btn.disabled && v.currentTime > furthest + 1) v.currentTime = furthest; });
            v.addEventListener('ended', () => {
                btn.disabled = false;
                hint.innerHTML = '<i class="fas fa-check-circle text-green-600 mr-1"></i>Thank you! Press Continue.';
            });
        })();
    </script>
</body>
</html>
