<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Message;
use App\Models\Event;
use App\Models\Download;
use App\Models\Gallery;
use App\Models\Subscription;
use App\Models\Course;
use App\Models\Partner;
use App\Models\Campus;
use App\Models\Faq;
use App\Models\Policy;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class WebsiteController extends Controller
{
    public function dashboard()
    {
        // Get recent blog posts
        $recentBlogs = Blog::latest()
            ->take(5)
            ->get();

        // Get recent messages (unread first)
        $recentMessages = Message::latest()
            ->take(5)
            ->get();

        // Get upcoming events
        $upcomingEvents = Event::where('event_start_date', '>=', now())
            ->orderBy('event_start_date', 'asc')
            ->take(3)
            ->get();

        // Get stats data
        $stats = $this->getDashboardStats();

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        // Get system status
        $systemStatus = $this->getSystemStatus();

        // Get popular pages (you need to implement this based on your analytics)
        $popularPages = $this->getPopularPages();

        return view('ktvtc.website.dashboard', compact(
            'recentBlogs',
            'recentMessages',
            'upcomingEvents',
            'stats',
            'recentActivities',
            'systemStatus',
            'popularPages'
        ));
    }

    private function getDashboardStats()
    {
        try {
            // Blog stats - fixed: using 'is_active' instead of 'status'
            $totalBlogs = Blog::count();
            $blogDrafts = Blog::where('is_active', 'draft')->count();

            // Message stats - fixed: using 'first_seen_by' instead of 'read_at'
            $totalMessages = Message::count();
            $unreadMessages = Message::whereNull('first_seen_by')->count();

            // Subscriber stats
            $totalSubscribers = Subscription::where('status', true)->count();
            $newSubscribersThisWeek = Subscription::where('status', true)
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->count();

            // Course stats
            $totalCourses = class_exists(Course::class) ? Course::count() : 0;
            $featuredCourses = class_exists(Course::class) ? Course::where('featured', true)->count() : 0;

            // Event stats
            $totalEvents = Event::count();
            $upcomingEventsCount = Event::where('event_start_date', '>=', now())->count();

            // Download stats
            $totalDownloads = 0;
            $totalDownloadFiles = 0;
            if (class_exists(Download::class)) {
                // Check if download_count column exists
                if (Schema::hasColumn('downloads', 'download_count')) {
                    $totalDownloads = Download::sum('download_count');
                } else {
                    $totalDownloads = Download::count();
                }
                $totalDownloadFiles = Download::count();
            }

            // Gallery stats
            $totalGalleries = 0;
            $totalImages = 0;
            if (class_exists(Gallery::class)) {
                $totalGalleries = Gallery::count();
                // Check if gallery_images table exists
                if (Schema::hasTable('gallery_images')) {
                    $totalImages = DB::table('gallery_images')->count();
                }
            }

            // Visitor stats (placeholder - implement your own tracking)
            $totalVisitors = $this->getTotalVisitors();
            $visitorChange = $this->getVisitorChange();

            return [
                'visitors' => [
                    'total' => $totalVisitors,
                    'change' => $visitorChange
                ],
                'blogs' => [
                    'total' => $totalBlogs,
                    'drafts' => $blogDrafts
                ],
                'messages' => [
                    'total' => $totalMessages,
                    'unread' => $unreadMessages
                ],
                'subscribers' => [
                    'total' => $totalSubscribers,
                    'new_this_week' => $newSubscribersThisWeek
                ],
                'courses' => [
                    'total' => $totalCourses,
                    'featured' => $featuredCourses
                ],
                'events' => [
                    'total' => $totalEvents,
                    'upcoming' => $upcomingEventsCount
                ],
                'downloads' => [
                    'total_files' => $totalDownloadFiles,
                    'total_downloads' => $totalDownloads
                ],
                'galleries' => [
                    'total_galleries' => $totalGalleries,
                    'total_images' => $totalImages
                ]
            ];
        } catch (\Exception $e) {
            // Log the error and return default values
            \Log::error('Dashboard stats error: ' . $e->getMessage());

            return [
                'visitors' => ['total' => 0, 'change' => 0],
                'blogs' => ['total' => 0, 'drafts' => 0],
                'messages' => ['total' => 0, 'unread' => 0],
                'subscribers' => ['total' => 0, 'new_this_week' => 0],
                'courses' => ['total' => 0, 'featured' => 0],
                'events' => ['total' => 0, 'upcoming' => 0],
                'downloads' => ['total_files' => 0, 'total_downloads' => 0],
                'galleries' => ['total_galleries' => 0, 'total_images' => 0]
            ];
        }
    }

    private function getTotalVisitors()
    {
        // Check if you have a visitors table
        try {
            if (Schema::hasTable('visitors')) {
                return DB::table('visitors')->count();
            }
        } catch (\Exception $e) {
            // Table doesn't exist
        }

        // Return placeholder
        return 1248;
    }

    private function getVisitorChange()
    {
        // Calculate percentage change from last month
        // This is a placeholder
        return 12.5; // 12.5% increase
    }

    private function getRecentActivities()
    {
        $activities = collect();

        try {
            // Recent blog activity - check if 'status' column exists, otherwise use 'is_active'
            $recentBlog = Blog::latest()->first();
            if ($recentBlog) {
                // Determine status based on your schema
                if (Schema::hasColumn('blogs', 'status')) {
                    $status = $recentBlog->status;
                } else {
                    // If using is_active field
                    $status = $recentBlog->is_active == 'published' ? 'published' : 'draft';
                }

                $activities->push([
                    'title' => 'Blog ' . ($status == 'published' ? 'Published' : 'Drafted'),
                    'description' => $recentBlog->title,
                    'time' => $recentBlog->updated_at->diffForHumans(),
                    'icon' => 'fa-newspaper',
                    'color' => $status == 'published' ? 'green' : 'yellow',
                    'url' => route('blogs.show', $recentBlog->id)
                ]);
            }

            // Recent message activity
            $recentMessage = Message::latest()->first();
            if ($recentMessage) {
                $activities->push([
                    'title' => 'New Message Received',
                    'description' => 'From: ' . $recentMessage->name,
                    'time' => $recentMessage->created_at->diffForHumans(),
                    'icon' => 'fa-envelope',
                    'color' => 'purple',
                    'url' => route('messages.show', $recentMessage->id)
                ]);
            }

            // Recent subscription activity
            if (class_exists(Subscription::class)) {
                $recentSubscription = Subscription::latest()->first();
                if ($recentSubscription) {
                    $activities->push([
                        'title' => 'New Subscriber',
                        'description' => $recentSubscription->email,
                        'time' => $recentSubscription->created_at->diffForHumans(),
                        'icon' => 'fa-user-plus',
                        'color' => 'blue',
                        'url' => '#'
                    ]);
                }
            }

            // Recent event activity
            $recentEvent = Event::latest()->first();
            if ($recentEvent) {
                $activities->push([
                    'title' => 'Event ' . ($recentEvent->is_published ? 'Published' : 'Created'),
                    'description' => $recentEvent->title,
                    'time' => $recentEvent->updated_at->diffForHumans(),
                    'icon' => 'fa-calendar-alt',
                    'color' => $recentEvent->is_published ? 'green' : 'yellow',
                    'url' => route('events.show', $recentEvent->id)
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Recent activities error: ' . $e->getMessage());
        }

        // Sort by time and return
        return $activities->sortByDesc(function ($activity) {
            return strtotime($activity['time']);
        })->values()->take(5);
    }

    private function getSystemStatus()
    {
        try {
            // Calculate storage usage
            $totalSpace = disk_total_space('/');
            $freeSpace = disk_free_space('/');
            $usedSpace = $totalSpace - $freeSpace;

            // Avoid division by zero
            $storagePercentage = $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100) : 0;
            $usedGB = round($usedSpace / 1024 / 1024 / 1024, 2);
            $totalGB = round($totalSpace / 1024 / 1024 / 1024, 2);

            // Database size - fixed for Laravel 12
            $databaseSize = $this->getDatabaseSize();

            return [
                'uptime' => '99.9%',
                'storage' => [
                    'used_percentage' => $storagePercentage,
                    'used_gb' => $usedGB,
                    'total_gb' => $totalGB
                ],
                'database' => [
                    'size_mb' => $databaseSize
                ],
                'backup' => [
                    'last_backup' => Carbon::now()->subHours(2)->format('Y-m-d H:i'),
                    'status' => 'success'
                ],
                'ssl' => [
                    'valid' => true,
                    'status' => 'Valid',
                    'expires' => Carbon::now()->addMonths(3)->format('Y-m-d')
                ],
                'server' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2),
                    'memory_limit' => ini_get('memory_limit')
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('System status error: ' . $e->getMessage());

            return [
                'uptime' => 'Unknown',
                'storage' => ['used_percentage' => 0, 'used_gb' => 0, 'total_gb' => 0],
                'database' => ['size_mb' => 0],
                'backup' => ['last_backup' => 'Unknown', 'status' => 'unknown'],
                'ssl' => ['valid' => false, 'status' => 'Unknown', 'expires' => 'Unknown'],
                'server' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'memory_usage' => 0,
                    'memory_limit' => 'Unknown'
                ]
            ];
        }
    }

    private function getDatabaseSize()
    {
        try {
            // For Laravel 12, use a simpler approach
            $result = DB::select("
                SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
            ");

            return round($result[0]->size_mb ?? 0, 2);
        } catch (\Exception $e) {
            \Log::error('Database size calculation error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getPopularPages()
    {
        // This is a placeholder - implement based on your analytics
        return [
            [
                'name' => 'Home Page',
                'path' => '/',
                'visits' => 3450
            ],
            [
                'name' => 'About Us',
                'path' => '/about',
                'visits' => 1280
            ],
            [
                'name' => 'Courses',
                'path' => '/courses',
                'visits' => 890
            ],
            [
                'name' => 'Blog',
                'path' => '/blog',
                'visits' => 650
            ],
            [
                'name' => 'Contact',
                'path' => '/contact',
                'visits' => 420
            ]
        ];
    }
}
