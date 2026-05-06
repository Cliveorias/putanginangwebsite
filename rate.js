// Sidebar Toggle
function toggleMenu() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
}

// Star Rating Logic
const stars = document.querySelectorAll('#star-rating i');
const ratingInput = document.getElementById('rating-value');

stars.forEach(star => {
    star.addEventListener('click', () => {
        const val = star.getAttribute('data-value');
        ratingInput.value = val;

        stars.forEach(s => {
            if (s.getAttribute('data-value') <= val) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });
});

// Image Preview Logic
function previewImage(input) {
    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('image-preview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
        }

        reader.readAsDataURL(input.files[0]);
    }
}

// Remove Attached Image
function removeImage() {
    const input = document.getElementById('image-upload');
    const previewContainer = document.getElementById('image-preview-container');
    
    input.value = ""; // Clear file input
    previewContainer.style.display = 'none';
}