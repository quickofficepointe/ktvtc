@extends('layouts.app')

@section('title', 'Donate Books | Kenswed Technical College')
@section('meta_description', 'Support our library by donating books. Help us expand our collection.')

@section('content')
<section class="bg-gradient-to-r from-[#B91C1C] to-[#BF1F30] py-12 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Donate Books</h1>
            <p class="text-xl opacity-90">Help us build a better library for our community</p>
        </div>
    </div>
</section>

<section class="py-12">
    <div class="container mx-auto px-4 max-w-3xl">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Book Donation Request</h2>
                <p class="text-gray-600">Fill out this form to request a book donation to our library.</p>
            </div>

            <form method="POST" action="{{ route('library.donation.submit') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                        <input type="text" name="full_name" required value="{{ old('full_name') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('full_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Email *</label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Country *</label>
                        <input type="text" name="country" required value="{{ old('country') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('country') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Book Title *</label>
                        <input type="text" name="book_title" required value="{{ old('book_title') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('book_title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Author</label>
                        <input type="text" name="author" value="{{ old('author') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('author') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('isbn') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Quantity *</label>
                        <input type="number" name="quantity" required value="{{ old('quantity', 1) }}" min="1" max="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                        @error('quantity') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Book Condition *</label>
                        <select name="condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent">
                            <option value="">Select Condition</option>
                            <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                            <option value="like-new" {{ old('condition') == 'like-new' ? 'selected' : '' }}>Like New</option>
                            <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                        </select>
                        @error('condition') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Additional Information</label>
                        <textarea name="additional_info" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#B91C1C] focus:border-transparent"
                                  placeholder="Any additional information about your donation...">{{ old('additional_info') }}</textarea>
                        @error('additional_info') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="submit"
                            class="w-full bg-[#B91C1C] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#991B1B] transition-colors">
                        Submit Donation Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
