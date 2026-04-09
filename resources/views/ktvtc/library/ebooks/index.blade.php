@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Digital E-Books</h1>
            <p class="text-gray-600 mt-2">Manage your digital book collection</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Upload E-Book
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total E-Books</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalEBooks ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeCount ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Featured</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $featuredCount ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Downloads</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalDownloads ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-indigo-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-indigo-100 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Views</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalViews ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Title, Author, ISBN..."
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select id="categoryFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="featured">Featured</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    {{-- E-Books Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($eBooks as $ebook)
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden hover:shadow-lg transition-all duration-300 group">
                <!-- Cover Image -->
                <div class="relative h-64 bg-gray-100">
                    @if($ebook->cover_image)
                        <img src="{{ asset('storage/' . $ebook->cover_image) }}"
                             alt="{{ $ebook->title }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                            <svg class="w-16 h-16 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif

                    <!-- Badges -->
                    <div class="absolute top-2 left-2 flex gap-2">
                        @if($ebook->is_featured)
                            <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Featured
                            </span>
                        @endif
                        @if(!$ebook->is_active)
                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">Inactive</span>
                        @endif
                    </div>

                    <!-- Format Badge -->
                    <div class="absolute top-2 right-2">
                        <span class="bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                            {{ $ebook->file_format }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4">
                    <h3 class="text-lg font-bold text-gray-900 font-serif mb-1 line-clamp-1">{{ $ebook->title }}</h3>
                    <p class="text-sm text-gray-600 mb-2">by {{ $ebook->author }}</p>

                    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            {{ $ebook->download_count }} downloads
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ $ebook->view_count }} views
                        </span>
                    </div>

                    <div class="mb-3">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium text-white"
                              style="background-color: {{ $ebook->category->color ?? '#6B7280' }}">
                            {{ $ebook->category->name ?? 'Uncategorized' }}
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a href="{{ route('library.ebooks.show', $ebook) }}"
                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors text-center">
                            View Details
                        </a>
                        <button onclick="openEditModal({{ $ebook->id }})"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No eBooks found</h3>
                        <p class="text-gray-600 mb-4">Start building your digital library.</p>
                        <button onclick="openCreateModal()"
                            class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Upload First E-Book
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($eBooks->hasPages())
        <div class="mt-6">
            {{ $eBooks->links() }}
        </div>
    @endif
</div>

<!-- Create/Edit Modal -->
<div id="ebookModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif" id="modalTitle">Upload E-Book</h3>
                            <p class="text-amber-100 text-sm" id="modalSubtitle">Add a new digital book to the library</p>
                        </div>
                    </div>
                    <button onclick="closeModal()" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="ebookForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">
                    <input type="hidden" name="ebook_id" id="ebookId">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" id="ebookTitle" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                            <input type="text" name="isbn" id="ebookIsbn"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Author *</label>
                            <input type="text" name="author" id="ebookAuthor" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Publisher</label>
                            <input type="text" name="publisher" id="ebookPublisher"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Publication Year</label>
                            <input type="number" name="publication_year" id="ebookYear"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                            <input type="text" name="language" id="ebookLanguage" value="English"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select name="category_id" id="ebookCategory" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-Book File (PDF/EPUB) *</label>
                            <input type="file" name="ebook_file" id="ebookFile" accept=".pdf,.epub"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Max size: 20MB. Supported: PDF, EPUB</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                            <input type="file" name="cover_image" id="ebookCover" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="ebookDescription" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                        </div>

                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_featured" id="ebookFeatured" value="1" class="mr-2">
                                <span class="text-sm text-gray-700">Feature this eBook</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" id="ebookActive" value="1" checked class="mr-2">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeModal()"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700">
                            Save E-Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Upload E-Book';
        document.getElementById('modalSubtitle').textContent = 'Add a new digital book to the library';
        document.getElementById('methodField').value = 'POST';
        document.getElementById('ebookForm').action = '{{ route("library.ebooks.store") }}';
        document.getElementById('ebookId').value = '';
        document.getElementById('ebookTitle').value = '';
        document.getElementById('ebookIsbn').value = '';
        document.getElementById('ebookAuthor').value = '';
        document.getElementById('ebookPublisher').value = '';
        document.getElementById('ebookYear').value = '';
        document.getElementById('ebookLanguage').value = 'English';
        document.getElementById('ebookCategory').value = '';
        document.getElementById('ebookDescription').value = '';
        document.getElementById('ebookFeatured').checked = false;
        document.getElementById('ebookActive').checked = true;
        document.getElementById('ebookFile').required = true;
        document.getElementById('ebookModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function openEditModal(id) {
        fetch(`/library/ebooks/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalTitle').textContent = 'Edit E-Book';
                document.getElementById('modalSubtitle').textContent = 'Update e-book information';
                document.getElementById('methodField').value = 'PUT';
                document.getElementById('ebookForm').action = `/library/ebooks/${id}`;
                document.getElementById('ebookId').value = id;
                document.getElementById('ebookTitle').value = data.title;
                document.getElementById('ebookIsbn').value = data.isbn || '';
                document.getElementById('ebookAuthor').value = data.author;
                document.getElementById('ebookPublisher').value = data.publisher || '';
                document.getElementById('ebookYear').value = data.publication_year || '';
                document.getElementById('ebookLanguage').value = data.language || 'English';
                document.getElementById('ebookCategory').value = data.category_id;
                document.getElementById('ebookDescription').value = data.description || '';
                document.getElementById('ebookFeatured').checked = data.is_featured === 1;
                document.getElementById('ebookActive').checked = data.is_active === 1;
                document.getElementById('ebookFile').required = false;
                document.getElementById('ebookModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value;

        let url = '{{ route("library.ebooks.index") }}?';
        if (search) url += `search=${encodeURIComponent(search)}&`;
        if (category) url += `category=${category}&`;
        if (status) url += `status=${status}&`;

        window.location.href = url.slice(0, -1);
    }

    function closeModal() {
        document.getElementById('ebookModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endsection
