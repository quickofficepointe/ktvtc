<?php

use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\AcquisitionRequestController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookCategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookPopularityController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BusinessSectionController;
use App\Http\Controllers\CafeteriaController;
use App\Http\Controllers\CafeteriaDailyProductionController;
use App\Http\Controllers\ContactInfoController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseIntakesController;
use App\Http\Controllers\DirectPurchaseController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EventApplicationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventPaymentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FineRuleController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GoodsReceivedNoteController;
use App\Http\Controllers\InventoryStocksController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MCourseSubjectController;
use App\Http\Controllers\MExamController;
use App\Http\Controllers\MExamResultController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MAttendanceController;
use App\Http\Controllers\MCertificateController;
use App\Http\Controllers\MCertificateTemplateController;
use App\Http\Controllers\MCourseCategoriesController;
use App\Http\Controllers\MCourseController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MEnrollmentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MobileSchoolController;
use App\Http\Controllers\MschoolController;
use App\Http\Controllers\MStudentController;
use App\Http\Controllers\MSubjectController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PaymentTransactionController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReadingHistoryController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ScholarshipControlle;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TrainersController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UsageStatisticController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\WeedingCandidateController;
use Illuminate\Support\Facades\Route;

// In web.php
Route::get('/', [AboutPageController::class, 'welcome'])->name('welcome');
Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {
    // Profile completion
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/home', function () {
    $user = Auth::user();

    // Force profile completion first
    if (!$user->phone_number || !$user->bio || !$user->profile_picture) {
        return redirect()->route('profile.edit');
    }

    // If not approved yet → student dashboard only
    if (!$user->is_approved) {
        return redirect()->route('student.dashboard');
    }

    // If approved → use role-based dashboard
    switch ($user->role) {
        case 1:
            return redirect()->route('mschool.dashboard');
        case 2:
            return redirect()->route('admin.dashboard');
        case 3:
            return redirect()->route('scholarship.dashboard');
        case 4:
            return redirect()->route('library.dashboard');
        case 5:
            return redirect()->route('student.dashboard');
        case 6:
            return redirect()->route('cafeteria.dashboard');
        case 7:
            return redirect()->route('finance.dashboard');
        case 8:
            return redirect()->route('trainers.dashboard');
        case 9:
            return redirect()->route('website.dashboard');
        default:
            return redirect()->route('student.dashboard');
    }
})->name('home');

// Mschool Routes
Route::middleware(['auth', 'verified', 'role.mschool'])->prefix('mschool')->group(function () {
    Route::get('/dashboard', [MschoolController::class, 'dashboard'])->name('mschool.dashboard');

    // Course Categories Routes
    Route::prefix('course-categories')->name('course-categories.')->group(function () {
        Route::get('/', [MCourseCategoriesController::class, 'index'])->name('index');
        Route::post('/', [MCourseCategoriesController::class, 'store'])->name('store');
        Route::put('/{id}', [MCourseCategoriesController::class, 'update'])->name('update');
        Route::delete('/{id}', [MCourseCategoriesController::class, 'destroy'])->name('destroy');
    });

    // Courses Routes
    Route::prefix('mcourses')->name('mcourses.')->group(function () {
        Route::get('/', [MCourseController::class, 'index'])->name('index');
        Route::post('/', [MCourseController::class, 'store'])->name('store');
        Route::put('/{id}', [MCourseController::class, 'update'])->name('update');
        Route::delete('/{id}', [MCourseController::class, 'destroy'])->name('destroy');
    });

    // Students Routes
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [MStudentController::class, 'index'])->name('index');
        Route::post('/', [MStudentController::class, 'store'])->name('store');
        Route::put('/{id}', [MStudentController::class, 'update'])->name('update');
        Route::delete('/{id}', [MStudentController::class, 'destroy'])->name('destroy');
    });

    // Subjects routes (for individual subject management)
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [MSubjectController::class, 'index'])->name('index');
        Route::post('/', [MSubjectController::class, 'store'])->name('store');
        Route::put('/{id}', [MSubjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [MSubjectController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('course-subjects')->name('course-subjects.')->group(function () {
        Route::get('/', [MCourseSubjectController::class, 'index'])->name('index');
        Route::post('/', [MCourseSubjectController::class, 'store'])->name('store');
        Route::put('/{id}', [MCourseSubjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [MCourseSubjectController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('enrollments')->name('enrollments.')->group(function () {
        Route::get('/', [MEnrollmentController::class, 'index'])->name('index');
        Route::post('/', [MEnrollmentController::class, 'store'])->name('store');
        Route::put('/{id}', [MEnrollmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [MEnrollmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [MExamController::class, 'index'])->name('index');
        Route::post('/', [MExamController::class, 'store'])->name('store');
        Route::put('/{exam}', [MExamController::class, 'update'])->name('update');
        Route::delete('/{exam}', [MExamController::class, 'destroy'])->name('destroy');

        // Additional routes
        Route::post('/{exam}/toggle-publication', [MExamController::class, 'togglePublication'])->name('toggle-publication');
        Route::post('/{exam}/toggle-active', [MExamController::class, 'toggleActive'])->name('toggle-active');
        Route::get('/course/{courseId}', [MExamController::class, 'getExamsByCourse'])->name('by-course');
        Route::get('/subject/{subjectId}', [MExamController::class, 'getExamsBySubject'])->name('by-subject');
        Route::get('/upcoming', [MExamController::class, 'upcomingExams'])->name('upcoming');
    });

    Route::prefix('exam-results')->name('exam-results.')->group(function () {
        Route::get('/', [MExamResultController::class, 'index'])->name('index');
        Route::post('/', [MExamResultController::class, 'store'])->name('store');
        Route::put('/{examResult}', [MExamResultController::class, 'update'])->name('update');
        Route::delete('/{examResult}', [MExamResultController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('certificate-templates')->name('certificate-templates.')->group(function () {
        Route::get('/', [MCertificateTemplateController::class, 'index'])->name('index');
        Route::post('/', [MCertificateTemplateController::class, 'store'])->name('store');
        Route::put('/{certificateTemplate}', [MCertificateTemplateController::class, 'update'])->name('update');
        Route::delete('/{certificateTemplate}', [MCertificateTemplateController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [MCertificateController::class, 'index'])->name('index');
        Route::post('/', [MCertificateController::class, 'store'])->name('store');
        Route::put('/{certificate}', [MCertificateController::class, 'update'])->name('update');
        Route::delete('/{certificate}', [MCertificateController::class, 'destroy'])->name('destroy');
        Route::get('/{certificate}/download', [MCertificateController::class, 'download'])->name('download');
        Route::post('/{certificate}/revoke', [MCertificateController::class, 'revoke'])->name('revoke');
        Route::post('/{certificate}/restore', [MCertificateController::class, 'restore'])->name('restore');
    });

    Route::resource('attendances', MAttendanceController::class);
    Route::post('/attendances/{attendance}/lock', [MAttendanceController::class, 'lock'])->name('attendances.lock');
    Route::post('/attendances/{attendance}/unlock', [MAttendanceController::class, 'unlock'])->name('attendances.unlock');
    Route::post('/attendances/{attendance}/generate-qrcode', [MAttendanceController::class, 'generateQrCode'])->name('attendances.generate-qrcode');
    Route::post('/attendances/{attendance}/update-statistics', [MAttendanceController::class, 'updateStatistics'])->name('attendances.update-statistics');
    Route::get('/attendances/{attendance}/records', [MAttendanceController::class, 'records'])->name('attendances.records');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'role.admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::post('/users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::put('/users/{id}/role', [AdminController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::put('/users/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.users.updateStatus');
    Route::delete('/users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
    Route::put('/users/{id}/approve', [AdminController::class, 'approve'])->name('admin.users.approve');

    Route::get('/applications', [ApplicationController::class, 'list'])->name('admin.applications.index');
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('admin.applications.show');
    Route::put('/applications/{id}/status', [ApplicationController::class, 'updateStatus'])->name('admin.applications.updateStatus');

    // Event Applications
    Route::get('/event-applications', [EventApplicationController::class, 'adminindex'])->name('admin.event-applications.index');
    Route::get('/event-applications/{application}', [EventApplicationController::class, 'adminshow'])->name('admin.event-applications.show');
    Route::put('/event-applications/{application}/status', [EventApplicationController::class, 'updateStatus'])->name('admin.event-applications.updateStatus');

    // Subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::delete('/subscriptions/{id}', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('admin.messages.index');
    Route::put('/messages/{id}', [MessageController::class, 'update'])->name('message.update');
    Route::delete('/messages/{id}', [MessageController::class, 'destroy'])->name('message.destroy');
});

// Scholarship Routes
Route::middleware(['auth', 'verified', 'role.scholarship'])->prefix('scholarship')->group(function () {
    Route::get('/dashboard', [ScholarshipControlle::class, 'dashboard'])->name('scholarship.dashboard');
    // Add more scholarship routes here
});

// Library Routes
Route::middleware(['auth', 'verified', 'role.library'])->prefix('library')->group(function () {
    // Dashboard
    Route::get('/dashboard', [LibraryController::class, 'dashboard'])->name('library.dashboard');

    // Acquisition Requests
    Route::get('/acquisition-requests', [AcquisitionRequestController::class, 'index'])->name('acquisition-requests.index');
    Route::post('/acquisition-requests', [AcquisitionRequestController::class, 'store'])->name('acquisition-requests.store');
    Route::get('/acquisition-requests/{acquisitionRequest}', [AcquisitionRequestController::class, 'show'])->name('acquisition-requests.show');
    Route::post('/acquisition-requests/{acquisitionRequest}/approve', [AcquisitionRequestController::class, 'approve'])->name('acquisition-requests.approve');
    Route::post('/acquisition-requests/{acquisitionRequest}/reject', [AcquisitionRequestController::class, 'reject'])->name('acquisition-requests.reject');
    Route::post('/acquisition-requests/{acquisitionRequest}/mark-ordered', [AcquisitionRequestController::class, 'markOrdered'])->name('acquisition-requests.mark-ordered');
    Route::post('/acquisition-requests/{acquisitionRequest}/mark-received', [AcquisitionRequestController::class, 'markReceived'])->name('acquisition-requests.mark-received');

    // Authors
    Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
    Route::post('/authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::get('/authors/{author}', [AuthorController::class, 'show'])->name('authors.show');
    Route::put('/authors/{author}', [AuthorController::class, 'update'])->name('authors.update');
    Route::delete('/authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');

    // Book Categories
    Route::get('/book-categories', [BookCategoryController::class, 'index'])->name('book-categories.index');
    Route::post('/book-categories', [BookCategoryController::class, 'store'])->name('book-categories.store');
    Route::get('/book-categories/{bookCategory}', [BookCategoryController::class, 'show'])->name('book-categories.show');
    Route::put('/book-categories/{bookCategory}', [BookCategoryController::class, 'update'])->name('book-categories.update');
    Route::delete('/book-categories/{bookCategory}', [BookCategoryController::class, 'destroy'])->name('book-categories.destroy');

    // Books
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    // Book Popularities
    Route::get('/book-popularities', [BookPopularityController::class, 'index'])->name('book-popularities.index');
    Route::post('/book-popularities/refresh', [BookPopularityController::class, 'refresh'])->name('book-popularities.refresh');
    Route::get('/book-popularities/report', [BookPopularityController::class, 'report'])->name('book-popularities.report');
    Route::post('/book-popularities/{bookPopularity}/update', [BookPopularityController::class, 'updatePopularity'])->name('book-popularities.update');

    // Branches
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Fine Rules
    Route::get('/fine-rules', [FineRuleController::class, 'index'])->name('fine-rules.index');
    Route::post('/fine-rules', [FineRuleController::class, 'store'])->name('fine-rules.store');
    Route::post('/fine-rules/{fineRule}/activate', [FineRuleController::class, 'activate'])->name('fine-rules.activate');
    Route::post('/fine-rules/{fineRule}/deactivate', [FineRuleController::class, 'deactivate'])->name('fine-rules.deactivate');

    // Items (Book Copies)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

    // Members
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');

    // Reading Histories
    Route::get('/reading-histories', [ReadingHistoryController::class, 'index'])->name('reading-histories.index');
    Route::post('/reading-histories', [ReadingHistoryController::class, 'store'])->name('reading-histories.store');
    Route::get('/reading-histories/{readingHistory}', [ReadingHistoryController::class, 'show'])->name('reading-histories.show');
    Route::put('/reading-histories/{readingHistory}', [ReadingHistoryController::class, 'update'])->name('reading-histories.update');
    Route::delete('/reading-histories/{readingHistory}', [ReadingHistoryController::class, 'destroy'])->name('reading-histories.destroy');

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservations/{reservation}/fulfill', [ReservationController::class, 'fulfill'])->name('reservations.fulfill');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transactions/{transaction}/return', [TransactionController::class, 'returnBook'])->name('transactions.return');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Usage Statistics
    Route::get('/usage-statistics', [UsageStatisticController::class, 'index'])->name('usage-statistics.index');
    Route::get('/usage-statistics/{usageStatistic}/details', [UsageStatisticController::class, 'details'])->name('usage-statistics.details');
    Route::get('/usage-statistics/export/monthly', [UsageStatisticController::class, 'exportMonthly'])->name('usage-statistics.export.monthly');
    Route::get('/usage-statistics/export/annual', [UsageStatisticController::class, 'exportAnnual'])->name('usage-statistics.export.annual');
    Route::get('/usage-statistics/{usageStatistic}/export', [UsageStatisticController::class, 'exportDaily'])->name('usage-statistics.export.daily');

    // Weeding Candidates
    Route::get('/weeding-candidates', [WeedingCandidateController::class, 'index'])->name('weeding-candidates.index');
    Route::post('/weeding-candidates', [WeedingCandidateController::class, 'store'])->name('weeding-candidates.store');
    Route::get('/weeding-candidates/{weedingCandidate}/review', [WeedingCandidateController::class, 'review'])->name('weeding-candidates.review');
    Route::put('/weeding-candidates/{weedingCandidate}/review', [WeedingCandidateController::class, 'processReview'])->name('weeding-candidates.process-review');
    Route::post('/weeding-candidates/{weedingCandidate}/process', [WeedingCandidateController::class, 'process'])->name('weeding-candidates.process');
});

// Student Routes
Route::middleware(['auth', 'verified', 'role.student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    // Add more student routes here
});

// Cafeteria Routes
Route::middleware(['auth', 'verified', 'role.cafeteria'])->prefix('cafeteria')->name('cafeteria.')->group(function () {
    // DASHBOARD
    Route::get('/dashboard', [CafeteriaController::class, 'dashboard'])->name('dashboard');
    Route::get('/stats', [CafeteriaController::class, 'getStats'])->name('stats');
    Route::get('/recent-activity', [CafeteriaController::class, 'recentActivity'])->name('recent-activity');

    // PRODUCT MANAGEMENT
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::get('/{product}/stock-history', [ProductController::class, 'stockHistory'])->name('stock-history');
        Route::get('/export', [ProductController::class, 'export'])->name('export');
        Route::post('/import', [ProductController::class, 'import'])->name('import');
        Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('low-stock');
        Route::get('/expiring-soon', [ProductController::class, 'expiringSoon'])->name('expiring-soon');
    });

    // CATEGORIES
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [ProductCategoryController::class, 'index'])->name('index');
        Route::post('/', [ProductCategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [ProductCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [ProductCategoryController::class, 'destroy'])->name('destroy');
        Route::get('/{category}/products', [ProductCategoryController::class, 'products'])->name('products');
    });

    // SUPPLIERS
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
        Route::post('/{supplier}/restore', [SupplierController::class, 'restore'])->name('restore');
        Route::get('/{supplier}/purchase-orders', [SupplierController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/{supplier}/transactions', [SupplierController::class, 'transactions'])->name('transactions');
        Route::get('/export', [SupplierController::class, 'export'])->name('export');
    });

    // PURCHASE ORDERS
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('show');
        Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('update');
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
        Route::post('/{purchaseOrder}/update-items', [PurchaseOrderController::class, 'updateItems'])->name('update-items');
        Route::post('/{purchaseOrder}/update-status', [PurchaseOrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('approve');
        Route::post('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
        Route::get('/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('print');
        Route::get('/{purchaseOrder}/email', [PurchaseOrderController::class, 'email'])->name('email');
        Route::get('/export', [PurchaseOrderController::class, 'export'])->name('export');
        Route::get('/pending-approval', [PurchaseOrderController::class, 'pendingApproval'])->name('pending-approval');
        Route::get('/overdue', [PurchaseOrderController::class, 'overdue'])->name('overdue');
    });

    // GOODS RECEIVED NOTES (GRN)
    Route::prefix('grn')->name('grn.')->group(function () {
        Route::get('/', [GoodsReceivedNoteController::class, 'index'])->name('index');
        Route::get('/create', [GoodsReceivedNoteController::class, 'create'])->name('create');
        Route::post('/', [GoodsReceivedNoteController::class, 'store'])->name('store');
        Route::get('/{grn}', [GoodsReceivedNoteController::class, 'show'])->name('show');
        Route::get('/{grn}/edit', [GoodsReceivedNoteController::class, 'edit'])->name('edit');
        Route::put('/{grn}', [GoodsReceivedNoteController::class, 'update'])->name('update');
        Route::delete('/{grn}', [GoodsReceivedNoteController::class, 'destroy'])->name('destroy');
        Route::post('/{grn}/update-status', [GoodsReceivedNoteController::class, 'updateStatus'])->name('update-status');
        Route::post('/{grn}/quality-check', [GoodsReceivedNoteController::class, 'qualityCheck'])->name('quality-check');
        Route::post('/{grn}/approve', [GoodsReceivedNoteController::class, 'approve'])->name('approve');
        Route::get('/{grn}/print', [GoodsReceivedNoteController::class, 'print'])->name('print');
        Route::get('/today', [GoodsReceivedNoteController::class, 'today'])->name('today');
        Route::get('/pending-quality', [GoodsReceivedNoteController::class, 'pendingQuality'])->name('pending-quality');
        Route::get('/by-purchase-order/{purchaseOrder}', [GoodsReceivedNoteController::class, 'byPurchaseOrder'])->name('by-purchase-order');
    });

    // DIRECT PURCHASES
    Route::prefix('direct-purchases')->name('direct-purchases.')->group(function () {
        Route::get('/', [DirectPurchaseController::class, 'index'])->name('index');
        Route::get('/create', [DirectPurchaseController::class, 'create'])->name('create');
        Route::post('/', [DirectPurchaseController::class, 'store'])->name('store');
        Route::get('/{directPurchase}', [DirectPurchaseController::class, 'show'])->name('show');
        Route::get('/{directPurchase}/edit', [DirectPurchaseController::class, 'edit'])->name('edit');
        Route::put('/{directPurchase}', [DirectPurchaseController::class, 'update'])->name('update');
        Route::delete('/{directPurchase}', [DirectPurchaseController::class, 'destroy'])->name('destroy');
        Route::post('/{directPurchase}/update-payment', [DirectPurchaseController::class, 'updatePayment'])->name('update-payment');
        Route::get('/{directPurchase}/print', [DirectPurchaseController::class, 'print'])->name('print');
        Route::get('/today', [DirectPurchaseController::class, 'today'])->name('today');
        Route::get('/by-supplier/{supplier}', [DirectPurchaseController::class, 'bySupplier'])->name('by-supplier');
    });

    // SALES MANAGEMENT
  // SALES MANAGEMENT
// SALES MANAGEMENT
Route::prefix('sales')->name('sales.')->group(function () {
    // CRUD Operations
    Route::get('/', [SaleController::class, 'pos'])->name('index');
    Route::get('/create', [SaleController::class, 'create'])->name('create');
    Route::post('/', [SaleController::class, 'store'])->name('store');

    Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
    Route::put('/{sale}', [SaleController::class, 'update'])->name('update');
    Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');

    // Sale Actions
    Route::post('/{sale}/update-status', [SaleController::class, 'updateStatus'])->name('update-status');
    Route::post('/{sale}/add-payment', [SaleController::class, 'addPayment'])->name('add-payment');
    Route::post('/{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel');

    // Reports
    Route::get('/today', [SaleController::class, 'today'])->name('today');
    Route::get('/pending-payment', [SaleController::class, 'pendingPayment'])->name('pending-payment');
    Route::get('/cancelled', [SaleController::class, 'cancelled'])->name('cancelled');
    Route::get('/daily-report', [SaleController::class, 'dailySalesReport'])->name('daily-report');
    Route::get('/monthly-report', [SaleController::class, 'monthlyReport'])->name('monthly-report');
    Route::get('/export', [SaleController::class, 'export'])->name('export');

    // Print/Email
    Route::get('/{sale}/print', [SaleController::class, 'print'])->name('print');
    Route::get('/{sale}/print-receipt', [SaleController::class, 'printReceipt'])->name('print-receipt');
    Route::get('/{sale}/email-receipt', [SaleController::class, 'emailReceipt'])->name('email-receipt');

    // POS Terminal Routes
    Route::get('/pos', [SaleController::class, 'pos'])->name('pos');
    Route::post('/pos/quick-sale', [SaleController::class, 'quickSale'])->name('pos.quick-sale');
    Route::post('/pos/initiate-mpesa', [SaleController::class, 'initiateMpesa'])->name('pos.initiate-mpesa');
    Route::post('/pos/check-mpesa-status', [SaleController::class, 'checkMpesaStatus'])->name('pos.check-mpesa-status');
});

// API Routes for POS (Separate from web routes)
Route::prefix('api')->name('api.')->group(function () {
    // POS Products API
    Route::get('/products/pos', [SaleController::class, 'apiPosProducts'])->name('products.pos');

    // Customer search API
    Route::get('/customers/search', [SaleController::class, 'apiSearchCustomers'])->name('customers.search');

    // Today's stats API
    Route::get('/sales/today-stats', [SaleController::class, 'apiTodayStats'])->name('sales.today-stats');

    // Recent sales API
    Route::get('/sales/recent', [SaleController::class, 'apiRecentSales'])->name('sales.recent');

    // Save draft API
    Route::post('/sales/save-draft', [SaleController::class, 'apiSaveDraft'])->name('sales.save-draft');
});

    // INVENTORY STOCKS// INVENTORY MANAGEMENT (Single Route for All)
Route::prefix('inventory')->name('inventory.')->group(function () {
    // Main inventory dashboard
    Route::get('/', [InventoryStocksController::class, 'index'])->name('index');

    // Stock operations via modals (these will be handled by the main index method)
    Route::post('/adjustment', [InventoryStocksController::class, 'storeAdjustment'])->name('adjustment.store');
    Route::post('/transfer', [InventoryStocksController::class, 'storeTransfer'])->name('transfer.store');
    Route::post('/movement/delete/{movement}', [InventoryStocksController::class, 'deleteMovement'])->name('movement.delete');
    Route::get('/movements/data', [InventoryStocksController::class, 'getMovementsData'])->name('movements.data');
    Route::get('/stocks/data', [InventoryStocksController::class, 'getStocksData'])->name('stocks.data');
});

    // STOCK ADJUSTMENTS
    Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function () {
        Route::get('/', [StockAdjustmentController::class, 'index'])->name('index');
        Route::get('/create', [StockAdjustmentController::class, 'create'])->name('create');
        Route::post('/', [StockAdjustmentController::class, 'store'])->name('store');
        Route::get('/{adjustment}', [StockAdjustmentController::class, 'show'])->name('show');
        Route::get('/{adjustment}/edit', [StockAdjustmentController::class, 'edit'])->name('edit');
        Route::put('/{adjustment}', [StockAdjustmentController::class, 'update'])->name('update');
        Route::delete('/{adjustment}', [StockAdjustmentController::class, 'destroy'])->name('destroy');
        Route::post('/{adjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('approve');
        Route::post('/{adjustment}/process', [StockAdjustmentController::class, 'process'])->name('process');
        Route::post('/{adjustment}/reject', [StockAdjustmentController::class, 'reject'])->name('reject');
        Route::get('/{adjustment}/print', [StockAdjustmentController::class, 'print'])->name('print');
        Route::get('/pending-approval', [StockAdjustmentController::class, 'pendingApproval'])->name('pending-approval');
        Route::get('/today', [StockAdjustmentController::class, 'today'])->name('today');
        Route::get('/by-type/{type}', [StockAdjustmentController::class, 'byType'])->name('by-type');
        Route::get('/export', [StockAdjustmentController::class, 'export'])->name('export');
    });

    // STOCK ALERTS
    Route::prefix('stock-alerts')->name('stock-alerts.')->group(function () {
        Route::get('/', [StockAlertController::class, 'index'])->name('index');
        Route::get('/active', [StockAlertController::class, 'active'])->name('active');
        Route::get('/resolved', [StockAlertController::class, 'resolved'])->name('resolved');
        Route::get('/{alert}', [StockAlertController::class, 'show'])->name('show');
        Route::post('/{alert}/resolve', [StockAlertController::class, 'resolve'])->name('resolve');
        Route::post('/{alert}/reopen', [StockAlertController::class, 'reopen'])->name('reopen');
        Route::delete('/{alert}', [StockAlertController::class, 'destroy'])->name('destroy');
        Route::get('/check-low-stock', [StockAlertController::class, 'checkLowStock'])->name('check-low-stock');
        Route::get('/check-expiring', [StockAlertController::class, 'checkExpiring'])->name('check-expiring');
    });

    // PAYMENT TRANSACTIONS
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentTransactionController::class, 'index'])->name('index');
        Route::get('/{transaction}', [PaymentTransactionController::class, 'show'])->name('show');
        Route::put('/{transaction}', [PaymentTransactionController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [PaymentTransactionController::class, 'destroy'])->name('destroy');
        Route::post('/{transaction}/reconcile', [PaymentTransactionController::class, 'reconcile'])->name('reconcile');
        Route::post('/{transaction}/reverse', [PaymentTransactionController::class, 'reverse'])->name('reverse');
        Route::get('/{transaction}/print', [PaymentTransactionController::class, 'print'])->name('print');
        Route::get('/today', [PaymentTransactionController::class, 'today'])->name('today');
        Route::get('/pending-reconciliation', [PaymentTransactionController::class, 'pendingReconciliation'])->name('pending-reconciliation');
        Route::get('/by-method/{method}', [PaymentTransactionController::class, 'byMethod'])->name('by-method');
        Route::get('/export', [PaymentTransactionController::class, 'export'])->name('export');

        // MPesa Integration
        Route::post('/mpesa/callback', [PaymentTransactionController::class, 'mpesaCallback'])->name('mpesa.callback');
        Route::post('/mpesa/validate', [PaymentTransactionController::class, 'mpesaValidate'])->name('mpesa.validate');
        Route::get('/mpesa/transactions', [PaymentTransactionController::class, 'mpesaTransactions'])->name('mpesa.transactions');
    });

    // SHOPS & BUSINESS SECTIONS
   Route::prefix('shops')->name('shops.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::post('/shopstore', [ShopController::class, 'store'])->name('store');
    Route::get('/{shop}', [ShopController::class, 'show'])->name('show');
    Route::get('/{shop}/inventory', [ShopController::class, 'inventory'])->name('inventory');
    Route::get('/{shop}/sales', [ShopController::class, 'sales'])->name('sales');
    Route::get('/{shop}/performance', [ShopController::class, 'performance'])->name('performance');

    // Add these missing routes for full CRUD
    Route::put('/{shop}', [ShopController::class, 'update'])->name('update');
    Route::delete('/{shop}', [ShopController::class, 'destroy'])->name('destroy');
});

   // Business Sections Routes
    Route::get('/business-sections', [BusinessSectionController::class, 'index'])->name('business-sections.index');
    Route::post('/business-sections', [BusinessSectionController::class, 'store'])->name('business-sections.store'); // MISSING ROUTE
    Route::get('/business-sections/{section}', [BusinessSectionController::class, 'show'])->name('business-sections.show');
    Route::put('/business-sections/{section}', [BusinessSectionController::class, 'update'])->name('business-sections.update'); // For edit modal
    Route::delete('/business-sections/{section}', [BusinessSectionController::class, 'destroy'])->name('business-sections.destroy');

    // API route for AJAX loading
    Route::get('/api/business-sections', [BusinessSectionController::class, 'getSections'])->name('api.business-sections.index');
    // REPORTS & ANALYTICS
    Route::prefix('reports')->name('reports.')->group(function () {
        // Sales Reports
        Route::get('/sales/daily', [CafeteriaController::class, 'dailySalesReport'])->name('sales.daily');
        Route::get('/sales/weekly', [CafeteriaController::class, 'weeklySalesReport'])->name('sales.weekly');
        Route::get('/sales/monthly', [CafeteriaController::class, 'monthlySalesReport'])->name('sales.monthly');
        Route::get('/sales/custom', [CafeteriaController::class, 'customSalesReport'])->name('sales.custom');
        Route::get('/sales/by-product', [CafeteriaController::class, 'salesByProduct'])->name('sales.by-product');
        Route::get('/sales/by-category', [CafeteriaController::class, 'salesByCategory'])->name('sales.by-category');
        Route::get('/sales/by-hour', [CafeteriaController::class, 'salesByHour'])->name('sales.by-hour');

        // Inventory Reports
        Route::get('/inventory/stock-levels', [CafeteriaController::class, 'stockLevelsReport'])->name('inventory.stock-levels');
        Route::get('/inventory/movements', [CafeteriaController::class, 'inventoryMovementsReport'])->name('inventory.movements');
        Route::get('/inventory/turnover', [CafeteriaController::class, 'inventoryTurnover'])->name('inventory.turnover');
        Route::get('/inventory/valuation', [CafeteriaController::class, 'inventoryValuation'])->name('inventory.valuation');
        Route::get('/inventory/wastage', [CafeteriaController::class, 'wastageReport'])->name('inventory.wastage');

        // Purchase Reports
        Route::get('/purchase/summary', [CafeteriaController::class, 'purchaseSummary'])->name('purchase.summary');
        Route::get('/purchase/by-supplier', [CafeteriaController::class, 'purchaseBySupplier'])->name('purchase.by-supplier');
        Route::get('/purchase/top-products', [CafeteriaController::class, 'topPurchasedProducts'])->name('purchase.top-products');

        // Financial Reports
        Route::get('/financial/profit-loss', [CafeteriaController::class, 'profitLoss'])->name('financial.profit-loss');
        Route::get('/financial/cash-flow', [CafeteriaController::class, 'cashFlow'])->name('financial.cash-flow');
        Route::get('/financial/expenses', [CafeteriaController::class, 'expensesReport'])->name('financial.expenses');

        // Export Reports
        Route::post('/export/sales', [CafeteriaController::class, 'exportSalesReport'])->name('export.sales');
        Route::post('/export/inventory', [CafeteriaController::class, 'exportInventoryReport'])->name('export.inventory');
        Route::post('/export/financial', [CafeteriaController::class, 'exportFinancialReport'])->name('export.financial');
    });

    // SETTINGS & CONFIGURATION
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [CafeteriaController::class, 'settings'])->name('index');
        Route::put('/general', [CafeteriaController::class, 'updateGeneralSettings'])->name('general.update');
        Route::put('/inventory', [CafeteriaController::class, 'updateInventorySettings'])->name('inventory.update');
        Route::put('/sales', [CafeteriaController::class, 'updateSalesSettings'])->name('sales.update');
        Route::put('/notifications', [CafeteriaController::class, 'updateNotificationSettings'])->name('notifications.update');
        Route::put('/integrations', [CafeteriaController::class, 'updateIntegrationSettings'])->name('integrations.update');

        // Printer Settings
        Route::get('/printers', [CafeteriaController::class, 'printers'])->name('printers.index');
        Route::post('/printers', [CafeteriaController::class, 'addPrinter'])->name('printers.store');
        Route::put('/printers/{printer}', [CafeteriaController::class, 'updatePrinter'])->name('printers.update');
        Route::delete('/printers/{printer}', [CafeteriaController::class, 'deletePrinter'])->name('printers.delete');
        Route::post('/printers/test/{printer}', [CafeteriaController::class, 'testPrinter'])->name('printers.test');

        // Tax Settings
        Route::get('/taxes', [CafeteriaController::class, 'taxes'])->name('taxes.index');
        Route::post('/taxes', [CafeteriaController::class, 'addTax'])->name('taxes.store');
        Route::put('/taxes/{tax}', [CafeteriaController::class, 'updateTax'])->name('taxes.update');
        Route::delete('/taxes/{tax}', [CafeteriaController::class, 'deleteTax'])->name('taxes.delete');
    });
// DAILY PRODUCTION MANAGEMENT
Route::prefix('daily-productions')->name('daily-productions.')->group(function () {
    Route::get('/', [CafeteriaDailyProductionController::class, 'index'])->name('index');
    Route::post('/', [CafeteriaDailyProductionController::class, 'store'])->name('store');
    Route::get('/{id}', [CafeteriaDailyProductionController::class, 'show'])->name('show'); // For API
    Route::post('/{id}', [CafeteriaDailyProductionController::class, 'update'])->name('update');
    Route::delete('/{id}', [CafeteriaDailyProductionController::class, 'destroy'])->name('destroy');

    // Additional actions
    Route::post('/{id}/verify', [CafeteriaDailyProductionController::class, 'verify'])->name('verify');
    Route::post('/{id}/update-sales', [CafeteriaDailyProductionController::class, 'updateSales'])->name('update-sales');
    Route::get('/statistics', [CafeteriaDailyProductionController::class, 'statistics'])->name('statistics');
});
    // API ENDPOINTS (for AJAX calls)
    Route::prefix('api')->name('api.')->group(function () {
        // Products API
        Route::get('/products', [ProductController::class, 'apiIndex'])->name('products.index');
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
        Route::get('/products/{product}/stock', [ProductController::class, 'stock'])->name('products.stock');
        Route::get('/products/low-stock', [ProductController::class, 'apiLowStock'])->name('products.low-stock');

        // Inventory API
        Route::get('/inventory/stats', [InventoryStocksController::class, 'stats'])->name('inventory.stats');
        Route::get('/inventory/movements/stats', [InventoryStocksController::class, 'movementStats'])->name('inventory.movements.stats');

        // Sales API
        Route::get('/sales/stats', [SaleController::class, 'stats'])->name('sales.stats');
        Route::get('/sales/today-stats', [SaleController::class, 'todayStats'])->name('sales.today-stats');

        // Suppliers API
        Route::get('/suppliers', [SupplierController::class, 'apiIndex'])->name('suppliers.index');

        // Shops API
        Route::get('/shops', [ShopController::class, 'apiIndex'])->name('shops.index');

        // Categories API
        Route::get('/categories', [ProductCategoryController::class, 'apiIndex'])->name('categories.index');

        // Business Sections API
        Route::get('/business-sections', [BusinessSectionController::class, 'apiIndex'])->name('business-sections.index');
    });

    // QUICK ACTIONS
    Route::post('/quick/reorder/{product}', [ProductController::class, 'quickReorder'])->name('quick.reorder');
    Route::post('/quick/adjust-stock', [InventoryStocksController::class, 'quickAdjustStock'])->name('quick.adjust-stock');
    Route::post('/quick/transfer-stock', [InventoryStocksController::class, 'quickTransferStock'])->name('quick.transfer-stock');
    Route::post('/quick/process-payment', [PaymentTransactionController::class, 'quickProcessPayment'])->name('quick.process-payment');

    // BULK ACTIONS
    Route::post('/bulk/update-product-prices', [ProductController::class, 'bulkUpdatePrices'])->name('bulk.update-product-prices');
    Route::post('/bulk/update-stock-levels', [InventoryStocksController::class, 'bulkUpdateStockLevels'])->name('bulk.update-stock-levels');
    Route::post('/bulk/process-payments', [PaymentTransactionController::class, 'bulkProcessPayments'])->name('bulk.process-payments');
    Route::post('/bulk/approve-purchase-orders', [PurchaseOrderController::class, 'bulkApprove'])->name('bulk.approve-purchase-orders');
    Route::post('/bulk/process-grn', [GoodsReceivedNoteController::class, 'bulkProcess'])->name('bulk.process-grn');

    // PRINT & EXPORT
    Route::get('/print/labels/{product}', [ProductController::class, 'printLabels'])->name('print.labels');
    Route::get('/print/price-tags/{product}', [ProductController::class, 'printPriceTags'])->name('print.price-tags');
    Route::get('/print/barcode/{product}', [ProductController::class, 'printBarcode'])->name('print.barcode');
    Route::post('/export/data', [CafeteriaController::class, 'exportData'])->name('export.data');
    Route::post('/import/data', [CafeteriaController::class, 'importData'])->name('import.data');

    // BACKUP & RESTORE
    Route::get('/backup', [CafeteriaController::class, 'backup'])->name('backup');
    Route::post('/backup/create', [CafeteriaController::class, 'createBackup'])->name('backup.create');
    Route::post('/backup/restore', [CafeteriaController::class, 'restoreBackup'])->name('backup.restore');
    Route::get('/backup/download/{file}', [CafeteriaController::class, 'downloadBackup'])->name('backup.download');
    Route::delete('/backup/{file}', [CafeteriaController::class, 'deleteBackup'])->name('backup.delete');

    // NOTIFICATIONS
    Route::get('/notifications', [CafeteriaController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/mark-all-read', [CafeteriaController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/mark-read', [CafeteriaController::class, 'markNotificationRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{notification}', [CafeteriaController::class, 'deleteNotification'])->name('notifications.delete');

    // USER MANAGEMENT (for cafeteria users only)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [CafeteriaController::class, 'users'])->name('index');
        Route::get('/create', [CafeteriaController::class, 'createUser'])->name('create');
        Route::post('/', [CafeteriaController::class, 'storeUser'])->name('store');
        Route::get('/{user}', [CafeteriaController::class, 'showUser'])->name('show');
        Route::get('/{user}/edit', [CafeteriaController::class, 'editUser'])->name('edit');
        Route::put('/{user}', [CafeteriaController::class, 'updateUser'])->name('update');
        Route::delete('/{user}', [CafeteriaController::class, 'deleteUser'])->name('delete');
        Route::post('/{user}/toggle-status', [CafeteriaController::class, 'toggleUserStatus'])->name('toggle-status');
        Route::post('/{user}/reset-password', [CafeteriaController::class, 'resetUserPassword'])->name('reset-password');
        Route::get('/roles', [CafeteriaController::class, 'roles'])->name('roles');
        Route::post('/roles', [CafeteriaController::class, 'storeRole'])->name('roles.store');
    });
}); // ← This closes the Cafeteria Routes group

// Finance Routes
Route::middleware(['auth', 'verified', 'role.finance'])->prefix('finance')->group(function () {
    Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('finance.dashboard');
    // Add more finance routes here
});

// Trainers Routes
Route::middleware(['auth', 'verified', 'role.trainers'])->prefix('trainers')->group(function () {
    Route::get('/dashboard', [TrainersController::class, 'dashboard'])->name('trainers.dashboard');
    // Add more trainers routes here
});

// Website Routes
Route::middleware(['auth', 'verified', 'role.website'])->prefix('website')->group(function () {
    Route::get('/dashboard', [WebsiteController::class, 'dashboard'])->name('website.dashboard');

    // Contact Info
    Route::get('contact-infos', [ContactInfoController::class, 'index'])->name('contact-infos.index');
    Route::post('contact-infos', [ContactInfoController::class, 'store'])->name('contact-infos.store');
    Route::put('contact-infos/{id}', [ContactInfoController::class, 'update'])->name('contact-infos.update');
    Route::delete('contact-infos/{id}', [ContactInfoController::class, 'destroy'])->name('contact-infos.destroy');

    // Policies
    Route::get('policies', [PolicyController::class, 'index'])->name('policies.index');
    Route::post('policies', [PolicyController::class, 'store'])->name('policies.store');
    Route::put('policies/{id}', [PolicyController::class, 'update'])->name('policies.update');
    Route::delete('policies/{id}', [PolicyController::class, 'destroy'])->name('policies.destroy');

    // Partners
    Route::get('partners', [PartnerController::class, 'index'])->name('partners.index');
    Route::post('partners', [PartnerController::class, 'store'])->name('partners.store');
    Route::put('partners/{id}', [PartnerController::class, 'update'])->name('partners.update');
    Route::delete('partners/{id}', [PartnerController::class, 'destroy'])->name('partners.destroy');

    // Mobile Schools
    Route::get('mschools', [MobileSchoolController::class, 'index'])->name('mschools.index');
    Route::post('mschools', [MobileSchoolController::class, 'store'])->name('mschools.store');
    Route::put('mschools/{id}', [MobileSchoolController::class, 'update'])->name('mschools.update');
    Route::delete('mschools/{id}', [MobileSchoolController::class, 'destroy'])->name('mschools.destroy');

    // Banners
    Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
    Route::post('banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');

    // Gallery Routes
    Route::prefix('galleries')->name('galleries.')->group(function () {
        Route::get('/', [GalleryController::class, 'index'])->name('index');
        Route::post('/', [GalleryController::class, 'store'])->name('store');
        Route::get('/{gallery}', [GalleryController::class, 'show'])->name('show');
        Route::put('/{gallery}', [GalleryController::class, 'update'])->name('update');
        Route::delete('/{gallery}', [GalleryController::class, 'destroy'])->name('destroy');

        // Gallery Images Routes
        Route::post('/{gallery}/images', [GalleryController::class, 'storeImages'])->name('images.store');
        Route::delete('/{gallery}/images/{image}', [GalleryController::class, 'destroyImage'])->name('images.destroy');
    });

    // Testimonials Routes
    Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
    Route::post('testimonials/{id}/approve', [TestimonialController::class, 'approve'])->name('testimonials.approve');
    Route::delete('testimonials/{id}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
    Route::put('testimonials/{id}', [TestimonialController::class, 'update'])->name('testimonials.update');

    // Downloads
    Route::get('downloads', [DownloadController::class, 'index'])->name('downloads.index');
    Route::post('downloads', [DownloadController::class, 'store'])->name('downloads.store');
    Route::put('downloads/{id}', [DownloadController::class, 'update'])->name('downloads.update');
    Route::delete('downloads/{id}', [DownloadController::class, 'destroy'])->name('downloads.destroy');

    // Subscriptions
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::delete('subscriptions/{id}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    // Messages
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::put('messages/{id}/mark-read', [MessageController::class, 'markRead'])->name('messages.mark-read');
    Route::delete('messages/{id}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Departments
    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // Courses
    Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
    Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
    Route::put('courses/update', [CourseController::class, 'update'])->name('courses.update'); // No parameter
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // Course Intakes
    Route::get('course-intakes', [CourseIntakesController::class, 'index'])->name('course-intakes.index');
    Route::post('course-intakes', [CourseIntakesController::class, 'store'])->name('course-intakes.store');
    Route::put('course-intakes/{course_intake}', [CourseIntakesController::class, 'update'])->name('course-intakes.update');
    Route::delete('course-intakes/{course_intake}', [CourseIntakesController::class, 'destroy'])->name('course-intakes.destroy');

    // FAQs
    Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
    Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
    Route::put('faqs/{id}', [FaqController::class, 'update'])->name('faqs.update');
    Route::delete('faqs/{id}', [FaqController::class, 'destroy'])->name('faqs.destroy');

    // About Pages
    Route::get('about-pages', [AboutPageController::class, 'index'])->name('about-pages.index');
    Route::post('about-pages', [AboutPageController::class, 'store'])->name('about-pages.store');
    Route::put('about-pages/{id}', [AboutPageController::class, 'update'])->name('about-pages.update');
    Route::delete('about-pages/{id}', [AboutPageController::class, 'destroy'])->name('about-pages.destroy');

    // About Images
    Route::post('about-images', [AboutPageController::class, 'storeImage'])->name('about-images.store');
    Route::put('about-images/{id}', [AboutPageController::class, 'updateImage'])->name('about-images.update');
    Route::delete('about-images/{id}', [AboutPageController::class, 'destroyImage'])->name('about-images.destroy');

    // Blog Categories
    Route::get('blog-categories', [BlogCategoryController::class, 'index'])->name('blog-categories.index');
    Route::post('blog-categories', [BlogCategoryController::class, 'store'])->name('blog-categories.store');
    Route::put('blog-categories/{id}', [BlogCategoryController::class, 'update'])->name('blog-categories.update');
    Route::delete('blog-categories/{id}', [BlogCategoryController::class, 'destroy'])->name('blog-categories.destroy');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');

    // Blogs
    Route::get('blogs', [BlogController::class, 'index'])->name('blogs.index');
    Route::post('blogs', [BlogController::class, 'store'])->name('blogs.store');
    Route::put('blogs/{id}', [BlogController::class, 'update'])->name('blogs.update');
    Route::delete('blogs/{id}', [BlogController::class, 'destroy'])->name('blogs.destroy');
}); // ← This closes the Website Routes group

// Public Routes
Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
Route::post('messages', [MessageController::class, 'store'])->name('messages.store');

// Courses Routes
Route::get('/courses', [CourseController::class, 'publicIndex'])->name('course.index');
Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/departments/{slug}', [DepartmentController::class, 'show'])->name('departments.show');
Route::get('/application', [ApplicationController::class, 'index'])->name('application.form');
Route::post('/application', [ApplicationController::class, 'store'])->name('application.store');
Route::get('/application/success/{id}', [ApplicationController::class, 'success'])->name('application.success');
Route::get('/application/course/{id}', [ApplicationController::class, 'getCourseDetails'])->name('application.course.details');

// Blog Routes
Route::get('/news', [BlogController::class, 'publicindex'])->name('blog.index');
Route::get('/news/category/{categorySlug}', [BlogController::class, 'byCategory'])->name('blog.by-category');
Route::get('/news/{categorySlug}/{blogSlug}', [BlogController::class, 'publicshow'])->name('blog.show');

// Mobile Schools Public Route
Route::get('/mobile-schools', [MobileSchoolController::class, 'publicIndex'])->name('mobile-schools.index');

// Downloads Public Route
Route::get('/downloads', [DownloadController::class, 'downloads'])->name('download.index');

// Gallery Public Routes
Route::get('/gallery', [GalleryController::class, 'publicIndex'])->name('gallerie.index');
Route::get('/gallery/{id}', [GalleryController::class, 'publicShow'])->name('gallerie.show');

// FAQ Public Routes
Route::get('/faq', [FaqController::class, 'publicIndex'])->name('faq.index');
Route::get('/faq/{slug}', [FaqController::class, 'publicShow'])->name('faq.show');
Route::get('/contact', [ContactInfoController::class, 'contactus'])->name('contact');

// Policy Public Routes
Route::get('/policies', [PolicyController::class, 'publicIndex'])->name('policie.index');
Route::get('/policies/{slug}', [PolicyController::class, 'publicShow'])->name('policies.show');

// Partners Public Routes
Route::get('/partners', [PartnerController::class, 'publicIndex'])->name('partner.index');
Route::get('/partners/{id}', [PartnerController::class, 'publicShow'])->name('partners.show');

// About Us Public Route
Route::get('/about', [AboutPageController::class, 'publicIndex'])->name('aboutus.index');
Route::get('/courseintakes', [CourseIntakesController::class, 'publicIndex'])->name('courseintake.show');

// Event Public Routes
Route::get('/events', [EventController::class, 'publicindex'])->name('event.index');
Route::get('/events/type/{type}', [EventController::class, 'byType'])->name('events.by-type');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// Event Application Routes
Route::prefix('event-applications')->group(function () {
    // Public routes (for applicants)
    Route::get('/create/{event}', [EventApplicationController::class, 'create'])->name('event-applications.create');
    Route::post('/store/{event}', [EventApplicationController::class, 'store'])->name('event-applications.store');

    // Protected routes (for admin/management)
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [EventApplicationController::class, 'index'])->name('event-applications.index');
        Route::get('/{application}', [EventApplicationController::class, 'show'])->name('event-applications.show');
        Route::get('/{application}/edit', [EventApplicationController::class, 'edit'])->name('event-applications.edit');
        Route::put('/{application}', [EventApplicationController::class, 'update'])->name('event-applications.update');
        Route::delete('/{application}', [EventApplicationController::class, 'destroy'])->name('event-applications.destroy');
        Route::patch('/{application}/status', [EventApplicationController::class, 'updateStatus'])->name('event-applications.update-status');
    });
});

// Event Payment Routes
Route::post('/events/{event}/apply/payment', [EventPaymentController::class, 'processEventPayment'])
    ->name('event.payment.process');

Route::post('/events/payment/callback', [EventPaymentController::class, 'paymentCallback'])
    ->name('event.payment.callback');

Route::post('/events/payment/status', [EventPaymentController::class, 'checkPaymentStatus'])
    ->name('event.payment.status');

Route::get('/events/application/success', [EventApplicationController::class, 'success'])
    ->name('event.application.success');
// Add this route for stats
Route::get('/cafeteria/api/stats', [CafeteriaController::class, 'getStats'])
    ->name('cafeteria.api.stats')
    ->middleware(['auth', 'verified', 'check.cafeteria.role']);
// POS Routes
Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
Route::get('/sales/api/pos-products', [SaleController::class, 'apiPosProducts'])->name('sales.api.pos-products');
Route::get('/sales/search-customers', [SaleController::class, 'apiSearchCustomers'])->name('sales.search.customers');
Route::get('/sales/today-stats', [SaleController::class, 'apiTodayStats'])->name('sales.today.stats');

// M-Pesa Payment Routes
Route::post('/sales/initiate-mpesa', [SaleController::class, 'initiateMpesa'])->name('sales.initiate.mpesa');
Route::post('/sales/check-mpesa-status', [SaleController::class, 'checkMpesaStatus'])->name('sales.check.mpesa.status');

// Sale Routes
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales/{id}/print-receipt', [SaleController::class, 'printReceipt'])->name('sales.print.receipt');
Route::get('/sales/{id}/email-receipt', [SaleController::class, 'emailReceipt'])->name('sales.email.receipt');

Route::post('/api/kcb/sales/callback', [App\Http\Controllers\SaleController::class, 'handleKcbCallback'])
    ->name('sales.payment.callback')
    ->withoutMiddleware(['web', 'csrf']); // Important: disable CSRF for external callbacks
