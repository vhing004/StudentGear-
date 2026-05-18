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
// Lấy danh sách danh mục
$categories = $conn->query("SELECT * FROM categories ORDER BY is_active DESC, id DESC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="../../assets/images/admin.webp" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <h2>Quản lý danh mục</h2>
                <button class="btn-primary" onclick="openModal('addProdModal')">
                    <i class="fas fa-plus"></i> Thêm danh mục
                </button>
            </header>

            <section class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $categories->fetch_assoc()): ?>
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
                                <td><strong><?= $row['name'] ?></strong></td>
                                <td><?= $row['description'] ?></td>
                                <td>
                                    <span class="status-badge <?= $row['is_active'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $row['is_active'] ? 'Hoạt động' : 'Ngừng kinh doanh' ?>
                                    </span>

                                </td>
                                <td>
                                    <button class="action-link"
                                        onclick='openEditProdModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="../handlers/delete_category.php?id=<?= $row['id'] ?>"
                                        class="action-link text-danger"
                                        onclick="return confirm('Xác nhận xóa danh mục này?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- ADD MODAL-->
    <div id="addProdModal" class="modal">
        <div class="modal__content">
            <h3>Thêm danh mục mới</h3>

            <form action="../handlers/add_category.php" method="POST" enctype="multipart/form-data" class="grid-form">

                <div class="auth-form__group">
                    <label>Tên danh mục</label>
                    <input type="text" name="name" class="auth-form__input" required placeholder="Ví dụ: Laptop Gaming">
                </div>

                <div class="auth-form__group">
                    <label>Mô tả</label>
                    <textarea name="description" class="auth-form__input" style="height:100px;" placeholder="Nhập mô tả cho danh mục này..."></textarea>
                </div>

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
                <div class="modal__footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addProdModal')">Hủy</button>
                    <button type="submit" class="btn-primary">Lưu sản phẩm</button>
                </div>
            </form>
        </div>
    </div>


    <!-- UPDATE MODAL -->
    <div id="editProdModal" class="modal">
        <div class="modal__content" style="max-width: 800px;">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">Chỉnh sửa danh mục</h3>

            <form action="../handlers/edit_category.php" method="POST" enctype="multipart/form-data" class="grid-form">
                <input type="hidden" name="id" id="edit_prod_id">

                <div class="auth-form__group">
                    <label>Tên danh mục:</label>
                    <input type="text" name="name" id="edit_prod_name" class="auth-form__input" required>
                </div>

                <div class="auth-form__group">
                    <label>Mô tả:</label>
                    <textarea name="description" id="edit_prod_desc" class="auth-form__input" style="height:100px; padding:10px;"></textarea>
                </div>

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
                    <label>Trạng thái hoạt động</label>
                    <select name="is_active" id="edit_prod_active" class="auth-form__input">
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Ngừng kinh doanh (Khóa)</option>
                    </select>
                    <small style="color: #888;">* Nếu khóa, khách hàng sẽ không thấy danh mục này.</small>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editProdModal')">Hủy</button>
                    <button type="submit" class="btn-primary">Cập nhật danh mục</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
<script src="../../assets/js/modal_admin.js"></script>
<script src="../../assets/js/category_admin.js"></script>
