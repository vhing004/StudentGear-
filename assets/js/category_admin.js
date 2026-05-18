    function openEditProdModal(category) {
        // 1. Điền các trường văn bản và số
        document.getElementById('edit_prod_id').value = category.id;
        document.getElementById('edit_prod_name').value = category.name;
        document.getElementById('edit_prod_desc').value = category.description;
        document.getElementById('edit_prod_active').value = category.is_active;
        // document.getElementById('edit_prod_active').checked = (parseInt(category.is_active) === 1);


        // 3. Xử lý hiển thị ảnh Preview trong Modal
        const previewImg = document.getElementById('edit_prod_preview');
        if (category.image && category.image.trim() !== "") {
            if (category.image.startsWith('https')) {
                previewImg.src = category.image;
            } else {
                previewImg.src = category.image;
            }
        } else {
            // Nếu null hoặc rỗng, dùng ảnh mặc định
            previewImg.src = '../assets/images/no-image.png';
        }

        // 4. Hiển thị Modal bằng hàm chung đã viết trước đó
        // Reset lại trạng thái ảnh về mặc định (hiện cũ, ẩn mới)
        document.getElementById('old_img_container').style.display = 'block';
        document.getElementById('new_img_preview_container').style.display = 'none';
        document.getElementById('edit_prod_image_input').value = "";


        // Lắng nghe sự kiện chọn file
        document.getElementById('edit_prod_image_input').addEventListener('change', function() {
            const file = this.files[0];
            const oldContainer = document.getElementById('old_img_container');
            const newContainer = document.getElementById('new_img_preview_container');
            const newPreviewImg = document.getElementById('edit_prod_new_preview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // 1. Đổ dữ liệu ảnh mới vào thẻ img preview
                    newPreviewImg.src = e.target.result;

                    // 2. Ẩn ảnh cũ, hiện ảnh mới
                    oldContainer.style.display = 'none';
                    newContainer.style.display = 'block';
                }

                reader.readAsDataURL(file);
            }
        });

        openModal('editProdModal');
    }