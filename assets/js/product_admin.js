    // FORMAT VND
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm định dạng chuỗi số hoặc số thực thành định dạng VND chuẩn
        function formatVNDString(value) {
            // Nếu giá trị là số (từ DB truyền ra), ép về chuỗi trước
            if (typeof value === 'number') {
                value = Math.floor(value).toString();
            }

            // Bóc tách bỏ phần thập phân .00 nếu có từ dữ liệu DECIMAL trước khi lọc
            if (value.includes('.')) {
                let parts = value.split('.');
                // Nếu phần sau dấu chấm là đuôi số thập phân của DB (nhỏ hơn hoặc bằng 2 chữ số)
                if (parts[1] && parts[1].length <= 2) {
                    value = parts[0]; // Chỉ giữ lại phần số nguyên
                }
            }

            let num = value.replace(/\D/g, ''); // Loại bỏ toàn bộ ký tự chữ, giữ lại số sạch

            if (num) {
                // Ép về số nguyên hệ cơ số 10 để loại bỏ hoàn toàn số 0 thừa ở đầu
                return new Intl.NumberFormat('vi-VN').format(parseInt(num, 10));
            }
            return '';
        }

        // Lắng nghe gõ phím trực tiếp trên các ô input
        const inputVndList = document.querySelectorAll('.input-vnd');
        inputVndList.forEach(input => {
            input.addEventListener('input', function() {
                this.value = formatVNDString(this.value);
            });
        });

        // Xử lý bóc tách dấu chấm TRƯỚC KHI SUBMIT gửi lên PHP
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const vndsInForm = form.querySelectorAll('.input-vnd');
                vndsInForm.forEach(input => {
                    // Biến đổi ngược "1.200.000" thành số thuần túy "1200000" để lưu vào DB
                    input.value = input.value.replace(/\./g, '');
                });
            });
        });

        // Hàm global bổ trợ khi đổ dữ liệu vào ô Edit Modal
        window.formatInputVNDOnEdit = function(inputSelector) {
            const inputField = document.querySelector(inputSelector);
            if (inputField && inputField.value) {
                inputField.value = formatVNDString(inputField.value);
            }
        };
    });

    // OPEN EDIT MODAL
    function openEditProdModal(product) {
        // 1. Điền các trường văn bản và số
        document.getElementById('edit_prod_id').value = product.id;
        document.getElementById('edit_prod_name').value = product.name;
        document.getElementById('edit_prod_price').value = product.price;
        document.getElementById('edit_prod_cost').value = product.cost_price;
        document.getElementById('edit_prod_stock').value = product.stock;
        document.getElementById('edit_prod_discount').value = product.discount_percent;
        document.getElementById('edit_prod_desc').value = product.description;
        document.getElementById('edit_prod_cat').value = product.category_id;

        // 2. Điền các trạng thái Checkbox (Sử dụng ép kiểu về Boolean)
        document.getElementById('edit_prod_featured').checked = (parseInt(product.is_featured) === 1);
        document.getElementById('edit_prod_new').checked = (parseInt(product.is_new) === 1);
        document.getElementById('edit_prod_active').checked = (parseInt(product.is_active) === 1);


        // 3. KÍCH HOẠT ĐỊNH DẠNG SỐ: Biến giá trị thô thành dạng "1.500.000" trên giao diện
        if (typeof window.formatInputVNDOnEdit === 'function') {
            window.formatInputVNDOnEdit('#edit_prod_price');
            window.formatInputVNDOnEdit('#edit_prod_cost');
        }

        // 3. Xử lý hiển thị ảnh Preview trong Modal
        const previewImg = document.getElementById('edit_prod_preview');
        if (product.image.startsWith('http')) {
            // Nếu là link web
            previewImg.src = product.image;
        } else {
            // Nếu là file local (nhớ lùi 1 cấp thư mục để ra khỏi admin)
            previewImg.src = product.image;
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