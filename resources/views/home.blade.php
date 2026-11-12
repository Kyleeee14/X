@extends('layouts.app')

@section('content')
<!-- Homepage Vibe - Feed Tweet Utama -->
<!-- Halaman ini menampilkan form untuk membuat tweet dan feed semua tweet -->
<div class="max-w-2xl mx-auto">
    <!-- Form Tweet Baru -->
    <!-- Hanya tampil untuk user yang sudah login -->
    @if(auth()->check())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form action="{{ route('tweets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3" onsubmit="submitTweetForm(event)">
                @csrf
                <div class="flex space-x-3">
                    <!-- Avatar user yang sedang login -->
                    <img src="{{ auth()->user()->avatar_url }}" 
                         alt="{{ auth()->user()->name }}" 
                         class="h-12 w-12 rounded-full">
                    <div class="flex-1">
                        <!-- Textarea untuk menulis tweet -->
                        <textarea name="content" 
                                  id="tweet-content"
                                  rows="3" 
                                  maxlength="280"
                                  oninput="document.getElementById('char-count').textContent = this.value.length"
                                  placeholder="What's happening?" 
                                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-lg"></textarea>
                        
                        <!-- Section Upload Gambar -->
                        <div class="mt-2 flex items-center justify-between">
                            <!-- Input file tersembunyi untuk upload gambar -->
                            <input type="file" 
                                   id="tweet-image" 
                                   name="image" 
                                   accept="image/*" 
                                   class="hidden"
                                   onchange="previewTweetImage()">
                            <!-- Label tombol untuk memilih gambar -->
                            <label for="tweet-image" class="flex items-center space-x-2 px-3 py-2 text-blue-500 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm">Image</span>
                            </label>
                            
                            <!-- Counter Karakter -->
                            <!-- Menampilkan jumlah karakter yang sudah ditulis -->
                            <div class="text-sm text-gray-500">
                                <span id="char-count">0</span>/280
                            </div>
                        </div>
                        
                        <!-- Preview Gambar -->
                        <!-- Tersembunyi default, muncul saat gambar dipilih -->
                        <div id="image-preview" class="hidden mt-3">
                            <div class="relative inline-block">
                                <img id="preview-img" src="" alt="Preview" class="max-w-full h-48 rounded-lg object-cover">
                                <!-- Tombol untuk menghapus gambar yang dipilih -->
                                <button type="button" 
                                        onclick="removeTweetImage()"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tombol Submit Tweet -->
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium transition-colors">
                            Tweet
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- Feed Tweet -->
    <!-- Menampilkan semua tweet dari user yang di-follow dan tweet user sendiri -->
    <div class="space-y-4">
        @foreach($tweets as $tweet)
            @include('tweets.tweet', ['tweet' => $tweet])
        @endforeach
    </div>
</div>

<!-- JavaScript untuk Upload Gambar Tweet -->
<!-- Fungsi ini menghandle preview dan hapus gambar -->
<script>
// Fungsi untuk menampilkan preview gambar saat user memilih file
function previewTweetImage() {
    const fileInput = document.getElementById('tweet-image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (fileInput.files && fileInput.files[0]) {
        // Gunakan FileReader untuk membaca file sebagai base64
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result; // Set src gambar untuk preview
            imagePreview.classList.remove('hidden'); // Tampilkan preview
        };
        reader.readAsDataURL(fileInput.files[0]); // Baca file
    } else {
        // Jika tidak ada file, sembunyikan preview
        imagePreview.classList.add('hidden');
        previewImg.src = '';
    }
}

// Fungsi untuk menghapus gambar yang dipilih
function removeTweetImage() {
    document.getElementById('tweet-image').value = ''; // Kosongkan input file
    document.getElementById('image-preview').classList.add('hidden'); // Sembunyikan preview
    document.getElementById('preview-img').src = ''; // Kosongkan src gambar
}

// Fungsi untuk submit form tweet
function submitTweetForm(event) {
    event.preventDefault(); // Prevent default form submission
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Submit form dengan AJAX atau normal
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reset form setelah berhasil
            form.reset();
            document.getElementById('char-count').textContent = '0';
            removeTweetImage();
            
            // Refresh halaman atau update feed
            window.location.reload();
        } else {
            // Handle error
            console.error('Error:', data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback ke normal form submission
        form.submit();
    });
}
</script>
@endsection
