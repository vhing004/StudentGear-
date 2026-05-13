<?php
// 1. Khởi động session và kết nối cơ sở dữ liệu
session_start();
require_once '../../config/db.php';

/**
 * 2. LOGIC BẢO MẬT: Chỉ Admin/Staff mới được vào
 * Kiểm tra user_id (đã đăng nhập) và role (là tài khoản admin)
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Nếu không phải admin, đẩy về trang login ở thư mục gốc
    header("Location: " . BASE_URL . "../auth/login.php");
    exit();
}

// 1. Lấy danh sách sản phẩm kèm tên danh mục
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.is_active DESC, p.id DESC";
$products = $conn->query($sql);

// 2. Lấy danh sách danh mục để đổ vào Select Box trong Modal
$categories = $conn->query("SELECT id, name FROM categories WHERE is_active = 1");
$cat_list = [];
while ($cat = $categories->fetch_assoc()) {
    $cat_list[] = $cat;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <h2>Danh sách sản phẩm</h2>
                <button class="btn-primary" onclick="openModal('addProdModal')">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </button>
            </header>

            <section class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá nhập/Bán</th>
                            <th>Danh mục</th>
                            <th>KM (%)</th>
                            <th>Nổi bật</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="product-img-wrapper">
                                        <?php
                                        $img_src = $row['image'];
                                        $final_src = "";

                                        // TRƯỜNG HỢP 1: Nếu là link từ internet (bắt đầu bằng http)
                                        if (strpos($img_src, 'http') === 0) {
                                            $final_src = $img_src;
                                        }
                                        // TRƯỜNG HỢP 2: Nếu là file upload trong máy (có tồn tại file)
                                        elseif (!empty($img_src) && file_exists($img_src)) {
                                            $final_src = $img_src;
                                        }
                                        // TRƯỜNG HỢP 3: Ảnh trống hoặc file không tồn tại
                                        else {
                                            $final_src = "../../assets/images/no-image.png";
                                        }
                                        ?>

                                        <img src="<?= $final_src ?>"
                                            alt="<?= htmlspecialchars($row['name']) ?>">
                                    </div>
                                </td>
                                <td style="max-width: 200px;"><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                <td>
                                    <small>Nhập: <?= number_format($row['cost_price'], 0, ',', '.') ?>₫</small><br>
                                    <strong>Bán: <?= number_format($row['price'], 0, ',', '.') ?>₫</strong>
                                </td>
                                <td><?= $row['category_name'] ?></td>
                                <td><?= $row['discount_percent'] ?>%</td>
                                <td>
                                    <i class="fa-star <?= $row['is_featured'] ? 'fa-solid text-warning' : 'fa-regular' ?>"></i>
                                </td>
                                <td>
                                    <span class="status-badge <?= $row['is_active'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $row['is_active'] ? 'Hoạt động' : 'Ngừng kinh doanh' ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- <button class="action-link" onclick='openEditProdModal(<?= json_encode($row, JSON_HEX_APOS) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button> -->
                                    <button class="action-link"
                                        onclick='openEditProdModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <a href="handlers/delete_product.php?id=<?= $row['id'] ?>"
                                        class="action-link text-danger"
                                        onclick="return confirm('Bạn có muốn ngừng kinh doanh sản phẩm này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- MODAL ADD -->
    <div id="addProdModal" class="modal">
        <div class="modal__content" style="max-width: 800px;">
            <h3>Thêm sản phẩm mới</h3>
            <form action="../handlers/add_product.php" method="POST" enctype="multipart/form-data" class="grid-form">
                <div class="auth-form__group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" class="auth-form__input" required>
                </div>

                <div class="grid-2-col">
                    <div class="auth-form__group">
                        <label>Giá nhập (Cost)</label>
                        <input type="number" name="cost_price" class="auth-form__input" required>
                    </div>
                    <div class="auth-form__group">
                        <label>Giá bán (Price)</label>
                        <input type="number" name="price" class="auth-form__input" required>
                    </div>
                </div>

                <div class="grid-3-col">
                    <div class="auth-form__group">
                        <label>Danh mục</label>
                        <select name="category_id" class="auth-form__input">
                            <?php foreach ($cat_list as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="auth-form__group">
                        <label>Kho hàng</label>
                        <input type="number" name="stock" class="auth-form__input" value="0">
                    </div>
                    <div class="auth-form__group">
                        <label>Giảm giá (%)</label>
                        <input type="number" name="discount_percent" class="auth-form__input" value="0">
                    </div>
                </div>

                <div class="auth-form__group">
                    <label>Mô tả chi tiết</label>
                    <textarea name="description" class="auth-form__input" style="height:100px;"></textarea>
                </div>

                <div class="grid-2-col">
                    <div class="auth-form__group">
                        <label>Hình ảnh sản phẩm</label>
                        <input type="file" name="image" id="product_image_input" class="auth-form__input" accept="image/*" required>

                        <div id="image_preview_box" style="margin-top: 15px; display: none; position: relative; width: 150px;">
                            <img id="img_preview" src="#" alt="Preview" style="width: 100%; border-radius: 8px; border: 2px solid #ddd; object-fit: cover;">
                            <button type="button" onclick="removePreview()" style="position: absolute; top: -10px; right: -10px; background: red; color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="auth-form__group" style="display:flex; align-items:center; gap:20px; padding-top:30px;">
                        <label><input type="checkbox" name="is_featured" value="1"> Nổi bật</label>
                        <label><input type="checkbox" name="is_new" value="1" checked> Sản phẩm mới</label>
                    </div>
                </div>

                <div class="modal__footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addProdModal')">Hủy</button>
                    <button type="submit" class="btn-primary">Lưu sản phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL UPDATE -->
    <div id="editProdModal" class="modal">
        <div class="modal__content" style="max-width: 800px;">
            <h3>Chỉnh sửa sản phẩm</h3>

            <form action="../handlers/edit_product.php" method="POST" enctype="multipart/form-data" class="grid-form">
                <input type="hidden" name="id" id="edit_prod_id">

                <div class="auth-form__group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" id="edit_prod_name" class="auth-form__input" required>
                </div>

                <div class="grid-2-col">
                    <div class="auth-form__group">
                        <label>Giá nhập (VNĐ)</label>
                        <input type="number" name="cost_price" id="edit_prod_cost" class="auth-form__input" required>
                    </div>
                    <div class="auth-form__group">
                        <label>Giá bán (VNĐ)</label>
                        <input type="number" name="price" id="edit_prod_price" class="auth-form__input" required>
                    </div>
                </div>

                <div class="grid-3-col">
                    <div class="auth-form__group">
                        <label>Danh mục</label>
                        <select name="category_id" id="edit_prod_cat" class="auth-form__input" required>
                            <?php foreach ($cat_list as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="auth-form__group">
                        <label>Kho hàng</label>
                        <input type="number" name="stock" id="edit_prod_stock" class="auth-form__input">
                    </div>
                    <div class="auth-form__group">
                        <label>Giảm giá (%)</label>
                        <input type="number" name="discount_percent" id="edit_prod_discount" class="auth-form__input">
                    </div>
                </div>

                <div class="auth-form__group">
                    <label>Mô tả sản phẩm</label>
                    <textarea name="description" id="edit_prod_desc" class="auth-form__input" style="height:100px;"></textarea>
                </div>

                <div class="grid-2-col">
                    <div class="auth-form__group">
                        <label>Hình ảnh sản phẩm</label>

                        <div id="old_img_container" style="margin-bottom: 10px;">
                            <p style="font-size: 12px; color: #888;">Ảnh hiện tại:</p>
                            <div class="product-img-wrapper" style="width: 100px; height: 100px;">
                                <img id="edit_prod_preview" src="" alt="Old Image">
                            </div>
                        </div>

                        <div id="new_img_preview_container" style="display: none; margin-bottom: 10px;">
                            <p style="font-size: 12px; color: #28a745; font-weight: bold;">Ảnh mới chọn:</p>
                            <div class="product-img-wrapper" style="width: 100px; height: 100px; border: 2px solid #28a745;">
                                <img id="edit_prod_new_preview" src="#" alt="New Preview">
                            </div>
                        </div>

                        <input type="file" name="image" id="edit_prod_image_input" class="auth-form__input" accept="image/*">
                    </div>
                    <div class="auth-form__group">
                        <label>Cài đặt hiển thị</label>
                        <div style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                            <label class="checkbox-container">
                                <input type="checkbox" name="is_featured" id="edit_prod_featured" value="1"> Sản phẩm nổi bật
                            </label>
                            <label class="checkbox-container">
                                <input type="checkbox" name="is_new" id="edit_prod_new" value="1"> Sản phẩm mới
                            </label>
                            <label class="checkbox-container">
                                <input type="checkbox" name="is_active" id="edit_prod_active" value="1"> Đang kinh doanh
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal__footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editProdModal')">Hủy</button>
                    <button type="submit" class="btn-primary">Cập nhật sản phẩm</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>
<script>
    // Hàm mở Modal
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('is-closing');
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    // Hàm đóng Modal kèm hiệu ứng
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);

        // Thêm class closing để kích hoạt animation slideUp và fadeOut
        modal.classList.add('is-closing');

        // Đợi animation chạy xong (0.3s = 300ms) rồi mới ẩn hẳn
        setTimeout(() => {
            modal.classList.remove('is-open');
            modal.classList.remove('is-closing');
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Cập nhật lại window.onclick để cũng có hiệu ứng khi click ra ngoài
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            const modalId = event.target.id;
            closeModal(modalId);
        }
    }

    // Hàm xem trước ảnh khi chọn file
    function previewImage(input) {
        const container = document.getElementById('image_preview_container');
        const preview = document.getElementById('add_preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('product_image_input').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img_preview').setAttribute('src', e.target.result);
                document.getElementById('image_preview_box').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    function removePreview() {
        document.getElementById('product_image_input').value = "";
        document.getElementById('image_preview_box').style.display = 'none';
    }




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
</script>