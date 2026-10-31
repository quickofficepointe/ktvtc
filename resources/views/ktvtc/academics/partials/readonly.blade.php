{{-- Shown to admin on academic pages: they can look, the academics office makes the changes. --}}
@unless(auth()->user()->canManageAcademics())
    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        <i class="fas fa-eye mr-1"></i> View only. The academics office manages trainers, units, marks, results and exam registrations.
    </div>
    <style>
        /* Hide anything that changes data; search and filter forms (GET) stay */
        .academics-ro form:not([method="GET"]):not([method="get"]),
        .academics-ro [data-write],
        .academics-ro button[onclick*="openModal"], .academics-ro button[onclick*="Modal("],
        .academics-ro a[href*="/create"], .academics-ro a[href*="/edit"],
        .academics-ro input[type="checkbox"].bulk, .academics-ro #bulkActionsBar { display: none !important; }
    </style>
@endunless
