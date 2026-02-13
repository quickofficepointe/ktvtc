@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- About Page Content Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">About Page Content</h1>
            <button onclick="openAboutPageModal()"
                class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
                {{ $aboutPage ? 'Edit About Page' : 'Create About Page' }}
            </button>
        </div>

        @if($aboutPage)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold mb-2">Our Story</h3>
                <div class="prose max-w-none">
                    {!! $aboutPage->our_story !!}
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Mission</h3>
                    <p class="text-gray-700">{{ $aboutPage->mission }}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Vision</h3>
                    <p class="text-gray-700">{{ $aboutPage->vision }}</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Core Values</h3>
                    <div class="prose max-w-none">
                        {!! $aboutPage->core_values !!}
                    </div>
                </div>
            </div>
        </div>

        @if($aboutPage->banner_image)
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-2">Banner Image</h3>
            <img src="{{ $aboutPage->banner_image }}" alt="Banner" class="max-w-md rounded-lg shadow">
        </div>
        @endif

        @if($aboutPage->video_url)
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-2">Video</h3>
            <a href="{{ $aboutPage->video_url }}" target="_blank" class="text-blue-600 hover:underline">
                {{ $aboutPage->video_url }}
            </a>
        </div>
        @endif

        @else
        <div class="text-center py-8">
            <p class="text-gray-500">No about page content found. Click the button above to create one.</p>
        </div>
        @endif
    </div>

    <!-- About Images Section -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">About Page Images</h2>
            <button onclick="openImageModal()"
                class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
                Add New Image
            </button>
        </div>

        @if($aboutImages->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($aboutImages as $image)
            <div class="border rounded-lg overflow-hidden shadow-sm">
                <img src="{{ $image->image_path }}" alt="{{ $image->caption }}" class="w-full h-48 object-cover">
                <div class="p-4">
                    @if($image->caption)
                    <p class="text-gray-700 mb-2">{{ $image->caption }}</p>
                    @endif
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Order: {{ $image->order }}</span>
                        <div class="space-x-2">
                            <button onclick="openEditImageModal({{ $image->id }}, '{{ addslashes($image->image_path) }}', '{{ addslashes($image->caption) }}', {{ $image->order }})"
                                class="text-indigo-600 hover:text-indigo-900 text-sm">
                                Edit
                            </button>
                            <button onclick="confirmDeleteImage('{{ route('about-images.destroy', $image->id) }}')"
                                class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-500">No images found. Click the button above to add images.</p>
        </div>
        @endif
    </div>
</div>

<!-- About Page Content Modal -->
<div id="aboutPageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeAboutPageModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 relative max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <button onclick="closeAboutPageModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                {{ $aboutPage ? 'Edit About Page' : 'Create About Page' }}
            </h3>
            <form action="{{ route('about-pages.store') }}" method="POST">
                @csrf
                @if($aboutPage)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Our Story</label>
                        <textarea name="our_story" class="summernote mt-1 block w-full border rounded-md px-3 py-2">{{ $aboutPage->our_story ?? '' }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mission</label>
                        <textarea name="mission" rows="3" class="mt-1 block w-full border rounded-md px-3 py-2">{{ $aboutPage->mission ?? '' }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vision</label>
                        <textarea name="vision" rows="3" class="mt-1 block w-full border rounded-md px-3 py-2">{{ $aboutPage->vision ?? '' }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Core Values</label>
                        <textarea name="core_values" class="summernote mt-1 block w-full border rounded-md px-3 py-2">{{ $aboutPage->core_values ?? '' }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Banner Image URL</label>
                        <input type="text" name="banner_image" value="{{ $aboutPage->banner_image ?? '' }}" class="mt-1 block w-full border rounded-md px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Video URL</label>
                        <input type="text" name="video_url" value="{{ $aboutPage->video_url ?? '' }}" class="mt-1 block w-full border rounded-md px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ $aboutPage->meta_title ?? '' }}" class="mt-1 block w-full border rounded-md px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                        <textarea name="meta_description" rows="2" class="mt-1 block w-full border rounded-md px-3 py-2">{{ $aboutPage->meta_description ?? '' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeAboutPageModal()" class="px-4 py-2 bg-gray-200 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">
                        {{ $aboutPage ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Create Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeImageModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Image</h3>
            <form action="{{ route('about-images.store') }}" method="POST">
                @csrf
                <input type="hidden" name="about_page_id" value="{{ $aboutPage->id ?? '' }}">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Image URL</label>
                        <input type="text" name="image_path" class="mt-1 block w-full border rounded-md px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Caption</label>
                        <input type="text" name="caption" class="mt-1 block w-full border rounded-md px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order</label>
                        <input type="number" name="order" value="0" class="mt-1 block w-full border rounded-md px-3 py-2">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeImageModal()" class="px-4 py-2 bg-gray-200 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">Add Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Edit Modal -->
<div id="editImageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeEditImageModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeEditImageModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Image</h3>
            <form id="editImageForm" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Image URL</label>
                        <input type="text" id="editImagePath" name="image_path" class="mt-1 block w-full border rounded-md px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Caption</label>
                        <input type="text" id="editCaption" name="caption" class="mt-1 block w-full border rounded-md px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order</label>
                        <input type="number" id="editOrder" name="order" class="mt-1 block w-full border rounded-md px-3 py-2">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeEditImageModal()" class="px-4 py-2 bg-gray-200 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">Update Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // About Page Modal
    function openAboutPageModal() {
        document.getElementById('aboutPageModal').classList.remove('hidden');
        $('.summernote').summernote({ height: 200 });
    }

    function closeAboutPageModal(event = null) {
        if(event && event.target !== document.getElementById('aboutPageModal')) return;
        document.getElementById('aboutPageModal').classList.add('hidden');
    }

    // Image Create Modal
    function openImageModal() {
        @if(!$aboutPage)
            alert('Please create about page content first.');
            return;
        @endif
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal(event = null) {
        if(event && event.target !== document.getElementById('imageModal')) return;
        document.getElementById('imageModal').classList.add('hidden');
    }

    // Image Edit Modal
    function openEditImageModal(id, imagePath, caption, order) {
        const modal = document.getElementById('editImageModal');
        modal.classList.remove('hidden');

        document.getElementById('editImageForm').action = `/about-images/${id}`;
        document.getElementById('editImagePath').value = imagePath;
        document.getElementById('editCaption').value = caption;
        document.getElementById('editOrder').value = order;
    }

    function closeEditImageModal(event = null) {
        if(event && event.target !== document.getElementById('editImageModal')) return;
        document.getElementById('editImageModal').classList.add('hidden');
    }

    // Delete Image
    function confirmDeleteImage(url) {
        if(confirm('Are you sure you want to delete this image?')){
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection
