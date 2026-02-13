@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Website Dashboard</h1>
        <p class="text-gray-600 mt-2">Welcome back, {{ auth()->user()->name }}! Here's what's happening with your website.</p>
    </div>

    <!-- Stats Overview - First Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Visitors -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-eye text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Visitors</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($stats['visitors']['total']) }}</h3>
                    <p class="text-sm {{ $stats['visitors']['change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="fas fa-arrow-{{ $stats['visitors']['change'] >= 0 ? 'up' : 'down' }} mr-1"></i>
                        {{ $stats['visitors']['change'] >= 0 ? '+' : '' }}{{ $stats['visitors']['change'] }}% from last month
                    </p>
                </div>
            </div>
        </div>

        <!-- Blog Posts -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-newspaper text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Blog Posts</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['blogs']['total'] }}</h3>
                    <p class="text-sm text-blue-600">{{ $stats['blogs']['drafts'] }} drafts pending</p>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <i class="fas fa-envelope text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Messages</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['messages']['total'] }}</h3>
                    <p class="text-sm text-yellow-600">{{ $stats['messages']['unread'] }} unread</p>
                </div>
            </div>
        </div>

        <!-- Subscribers -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Subscribers</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['subscribers']['total'] }}</h3>
                    <p class="text-sm text-green-600">+{{ $stats['subscribers']['new_this_week'] }} new this week</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Courses -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mr-4">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Courses</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['courses']['total'] }}</h3>
                    <p class="text-sm text-indigo-600">{{ $stats['courses']['featured'] }} featured</p>
                </div>
            </div>
        </div>

        <!-- Events -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Events</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['events']['total'] }}</h3>
                    <p class="text-sm text-yellow-600">{{ $stats['events']['upcoming'] }} upcoming</p>
                </div>
            </div>
        </div>

        <!-- Downloads -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-pink-100 text-pink-600 mr-4">
                    <i class="fas fa-download text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Downloads</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['downloads']['total_files'] }}</h3>
                    <p class="text-sm text-pink-600">{{ number_format($stats['downloads']['total_downloads']) }} downloads</p>
                </div>
            </div>
        </div>

        <!-- Galleries -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-teal-100 text-teal-600 mr-4">
                    <i class="fas fa-images text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Galleries</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['galleries']['total_galleries'] }}</h3>
                    <p class="text-sm text-teal-600">{{ $stats['galleries']['total_images'] }} images</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('blogs.index') }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition duration-300 flex items-center justify-center flex-col text-center hover:bg-blue-50 group">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mb-3 group-hover:bg-blue-200 group-hover:text-blue-700">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <span class="font-medium text-gray-800 group-hover:text-blue-600">New Blog Post</span>
            </a>

            <a href="{{ route('events.index') }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition duration-300 flex items-center justify-center flex-col text-center hover:bg-green-50 group">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mb-3 group-hover:bg-green-200 group-hover:text-green-700">
                    <i class="fas fa-calendar-plus text-xl"></i>
                </div>
                <span class="font-medium text-gray-800 group-hover:text-green-600">Add Event</span>
            </a>

            <a href="{{ route('galleries.index') }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition duration-300 flex items-center justify-center flex-col text-center hover:bg-purple-50 group">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mb-3 group-hover:bg-purple-200 group-hover:text-purple-700">
                    <i class="fas fa-images text-xl"></i>
                </div>
                <span class="font-medium text-gray-800 group-hover:text-purple-600">Manage Gallery</span>
            </a>

            <a href="{{ route('mschools.index') }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition duration-300 flex items-center justify-center flex-col text-center hover:bg-red-50 group">
                <div class="p-3 rounded-full bg-red-100 text-red-600 mb-3 group-hover:bg-red-200 group-hover:text-red-700">
                    <i class="fas fa-map-marker-alt text-xl"></i>
                </div>
                <span class="font-medium text-gray-800 group-hover:text-red-600">Mobile Schools</span>
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Recent Activities & Popular Pages -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">Recent Activity</h2>
                        <span class="text-sm text-gray-500">{{ count($recentActivities) }} activities</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($recentActivities as $activity)
                        <div class="flex items-start hover:bg-gray-50 p-3 rounded transition duration-200">
                            <div class="flex-shrink-0 w-10 h-10 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas {{ $activity['icon'] }} text-{{ $activity['color'] }}-600"></i>
                            </div>
                            <div class="flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-800">{{ $activity['title'] }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">{{ $activity['description'] }}</p>
                                    </div>
                                    @if(isset($activity['url']))
                                    <a href="{{ $activity['url'] }}" class="text-blue-600 hover:text-blue-800 text-sm transition duration-200">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-2">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-history text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No recent activity</p>
                        </div>
                        @endforelse
                    </div>
                    <div class="mt-6 text-center">
                        <a href="" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition duration-200">View All Activities →</a>
                    </div>
                </div>
            </div>

            <!-- Popular Pages -->
            @if(!empty($popularPages))
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800">Popular Pages</h2>
                    <p class="text-sm text-gray-500 mt-1">Most visited pages in the last 30 days</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($popularPages as $page)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded transition duration-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-gray-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-800">{{ $page['name'] }}</h4>
                                    <p class="text-sm text-gray-500">{{ $page['path'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-gray-800">{{ number_format($page['visits']) }}</span>
                                <p class="text-xs text-gray-500">visits</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: System Status & Quick Links -->
        <div class="space-y-8">
            <!-- System Status -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800">System Status</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Website Uptime -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-server text-green-600"></i>
                                </div>
                                <span class="text-gray-600">Website Uptime</span>
                            </div>
                            <span class="font-medium text-green-600">{{ $systemStatus['uptime'] ?? '99.9%' }}</span>
                        </div>

                        <!-- Storage Used -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-hdd text-blue-600"></i>
                                </div>
                                <span class="text-gray-600">Storage Used</span>
                            </div>
                            <div class="text-right">
                                <span class="font-medium {{ ($systemStatus['storage']['used_percentage'] ?? 0) > 80 ? 'text-red-600' : (($systemStatus['storage']['used_percentage'] ?? 0) > 60 ? 'text-yellow-600' : 'text-blue-600') }}">
                                    {{ $systemStatus['storage']['used_percentage'] ?? 0 }}%
                                </span>
                                <p class="text-xs text-gray-500">
                                    {{ $systemStatus['storage']['used_gb'] ?? 0 }} GB / {{ $systemStatus['storage']['total_gb'] ?? 0 }} GB
                                </p>
                            </div>
                        </div>

                        <!-- Database Size -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-database text-purple-600"></i>
                                </div>
                                <span class="text-gray-600">Database Size</span>
                            </div>
                            <span class="font-medium text-purple-600">{{ $systemStatus['database']['size_mb'] ?? 0 }} MB</span>
                        </div>

                        <!-- Last Backup -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-{{ $systemStatus['backup']['status'] == 'success' ? 'green' : ($systemStatus['backup']['status'] == 'warning' ? 'yellow' : 'red') }}-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-save text-{{ $systemStatus['backup']['status'] == 'success' ? 'green' : ($systemStatus['backup']['status'] == 'warning' ? 'yellow' : 'red') }}-600"></i>
                                </div>
                                <span class="text-gray-600">Last Backup</span>
                            </div>
                            <span class="font-medium text-gray-800">{{ $systemStatus['backup']['last_backup'] ?? 'Unknown' }}</span>
                        </div>

                        <!-- SSL Certificate -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-{{ $systemStatus['ssl']['valid'] ? 'green' : 'red' }}-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-lock text-{{ $systemStatus['ssl']['valid'] ? 'green' : 'red' }}-600"></i>
                                </div>
                                <span class="text-gray-600">SSL Certificate</span>
                            </div>
                            <span class="font-medium text-{{ $systemStatus['ssl']['valid'] ? 'green' : 'red' }}-600">{{ $systemStatus['ssl']['status'] ?? 'Unknown' }}</span>
                        </div>
                    </div>

                    <!-- Server Info -->
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Server Information</h3>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-600">PHP:</span>
                                <span class="font-medium ml-1">{{ $systemStatus['server']['php_version'] ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Laravel:</span>
                                <span class="font-medium ml-1">{{ $systemStatus['server']['laravel_version'] ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Memory:</span>
                                <span class="font-medium ml-1">{{ $systemStatus['server']['memory_usage'] ?? 0 }} MB</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Limit:</span>
                                <span class="font-medium ml-1">{{ $systemStatus['server']['memory_limit'] ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800">Quick Links</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('banners.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-image text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">Banner Management</span>
                        </a>
                        <a href="{{ route('campuses.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-school text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">Campuses</span>
                        </a>
                        <a href="{{ route('testimonials.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-comment-dots text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">Testimonials</span>
                        </a>
                        <a href="{{ route('downloads.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-download text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">Downloads</span>
                        </a>
                        <a href="{{ route('faqs.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-question-circle text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">FAQs</span>
                        </a>
                        <a href="{{ route('departments.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-building text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">Departments</span>
                        </a>
                        <a href="{{ route('partners.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-handshake text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">Partners</span>
                        </a>
                        <a href="{{ route('policies.index') }}" class="flex items-center p-3 hover:bg-gray-50 rounded transition duration-200 group">
                            <i class="fas fa-file-contract text-blue-600 mr-3 group-hover:text-blue-800"></i>
                            <span class="group-hover:text-blue-800">Policies</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800">Upcoming Events</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($upcomingEvents as $event)
                        <div class="p-3 border-l-4 border-blue-600 hover:bg-gray-50 rounded-r transition duration-200">
                            <h4 class="font-medium text-gray-800">{{ $event->title }}</h4>
                            <div class="flex items-center text-sm text-gray-500 mt-1">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                {{ $event->event_start_date->format('M d, Y') }}
                                @if($event->event_end_date)
                                <span class="mx-1">-</span>
                                {{ $event->event_end_date->format('M d, Y') }}
                                @endif
                            </div>
                            @if($event->location)
                            <div class="flex items-center text-sm text-gray-500 mt-1">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                {{ $event->location }}
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fas fa-calendar text-3xl text-gray-300 mb-2"></i>
                            <p class="text-gray-500">No upcoming events</p>
                            <a href="{{ route('events.index') }}" class="text-blue-600 hover:text-blue-800 hover:underline text-sm mt-1 inline-block transition duration-200">Schedule an event</a>
                        </div>
                        @endforelse
                    </div>
                    @if($upcomingEvents->isNotEmpty())
                    <div class="mt-4 text-center">
                        <a href="{{ route('events.index') }}" class="text-blue-600 hover:text-blue-800 hover:underline text-sm transition duration-200">View All Events →</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Blog Posts & Messages -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Blog Posts -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Recent Blog Posts</h2>
                    <span class="text-sm text-gray-500">{{ $stats['blogs']['total'] }} total posts</span>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentBlogs as $blog)
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded transition duration-200 group">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 group-hover:bg-blue-200">
                                <i class="fas fa-newspaper text-blue-600 group-hover:text-blue-800"></i>
                            </div>
                            <div>
                                <a href="{{ route('blogs.show', $blog->id) }}" class="font-medium text-gray-800 group-hover:text-blue-600">
                                    {{ Str::limit($blog->title, 50) }}
                                </a>
                                <div class="flex items-center text-sm text-gray-500 mt-1">
                                    <span>{{ $blog->created_at->diffForHumans() }}</span>
                                    @if($blog->category)
                                    <span class="mx-2">•</span>
                                    <span>{{ $blog->category->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $blog->status == 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($blog->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="fas fa-newspaper text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No blog posts yet</p>
                        <a href="{{ route('blogs.create') }}" class="text-blue-600 hover:text-blue-800 hover:underline mt-2 inline-block transition duration-200">Create your first post</a>
                    </div>
                    @endforelse
                </div>
                <div class="mt-6 text-center">
                    <a href="{{ route('blogs.index') }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition duration-200">View All Posts →</a>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Recent Messages</h2>
                    <span class="text-sm text-gray-500">{{ $stats['messages']['total'] }} total messages</span>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentMessages as $message)
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded transition duration-200 group">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3 group-hover:bg-purple-200">
                                <i class="fas fa-envelope text-purple-600 group-hover:text-purple-800"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">{{ $message->name }}</h4>
                                <p class="text-sm text-gray-600">{{ Str::limit($message->message, 60) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $message->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $message->read_at ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $message->read_at ? 'Read' : 'New' }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="fas fa-envelope text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No messages yet</p>
                    </div>
                    @endforelse
                </div>
                <div class="mt-6 text-center">
                    <a href="{{ route('messages.index') }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition duration-200">View All Messages →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Dashboard -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects for stats cards
    const statCards = document.querySelectorAll('.bg-white.rounded-lg.shadow');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Auto-refresh stats every 5 minutes
    setTimeout(() => {
        window.location.reload();
    }, 300000); // 5 minutes

    // Add smooth transitions for all links
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                // You can add page transition logic here
                window.location.href = this.href;
            }
        });
    });
});
</script>
@endpush
@endsection
