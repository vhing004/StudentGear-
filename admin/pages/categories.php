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
// $categories = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $conn->query("SELECT * FROM categories ORDER BY is_active DESC, id ASC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản trị - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <h2>Quản lý danh mục</h2>
                <button class="btn-primary" onclick="document.getElementById('addModal').style.display='block'">
                    <i class="fas fa-plus"></i> Thêm danh mục mới
                </button>
            </header>

            <section class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <!-- <th>STT</th> -->
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
                                <!-- <td><?= $row['id'] ?></td> -->
                                <td><img src="<?= $row['image'] ?>" width="50" height="50" style="object-fit:cover; border-radius:5px;"></td>
                                <td><strong><?= $row['name'] ?></strong></td>
                                <td><?= $row['description'] ?></td>
                                <td>
                                    <span class="status-badge <?= $row['is_active'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $row['is_active'] ? 'Hoạt động' : 'Ngừng kinh doanh' ?>
                                    </span>

                                </td>
                                <td>
                                    <button class="action-link"
                                        style="border:none; background:none; cursor:pointer;"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
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
    <div id="addModal" class="modal">
        <div class="modal__content">
            <h3>Thêm danh mục mới</h3>

            <form action="<?php echo BASE_URL; ?>admin/handlers/add_category.php" method="POST" enctype="multipart/form-data">

                <div class="auth-form__group">
                    <label>Tên danh mục</label>
                    <input type="text" name="name" class="auth-form__input" required placeholder="Ví dụ: Laptop Gaming">
                </div>

                <div class="auth-form__group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="auth-form__input" required placeholder="laptop-gaming">
                </div>

                <div class="auth-form__group">
                    <label>Mô tả</label>
                    <textarea name="description" class="auth-form__input" style="height:100px;" placeholder="Nhập mô tả cho danh mục này..."></textarea>
                </div>

                <div class="auth-form__group">
                    <label>Hình ảnh đại diện</label>
                    <input type="file" name="image" class="auth-form__input" required style="padding: 8px;">
                    <small style="color: #888;">* Định dạng: JPG, PNG, WebP (Tỷ lệ 1:1 là tốt nhất)</small>
                </div>

                <div class="modal__footer">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').style.display='none'">
                        Hủy
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus"></i> Tạo danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- UPDATE MODAL -->
    <div id="editModal" class="modal" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div style="background:#fff; width:550px; margin:5% auto; padding:25px; border-radius:12px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
            <h3 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">Chỉnh sửa danh mục</h3><br>

            <form action="<?php echo BASE_URL; ?>admin/handlers/edit_category.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">

                <div class="auth-form__group">
                    <label>Trạng thái hoạt động</label>
                    <select name="is_active" id="edit_is_active" class="auth-form__input">
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Ngừng kinh doanh (Khóa)</option>
                    </select>
                    <small style="color: #888;">* Nếu khóa, khách hàng sẽ không thấy danh mục này.</small>
                </div>

                <div class="auth-form__group">
                    <label>Tên danh mục:</label>
                    <input type="text" name="name" id="edit_name" class="auth-form__input" required>
                </div><br>

                <div class="auth-form__group">
                    <label>Slug:</label>
                    <input type="text" name="slug" id="edit_slug" class="auth-form__input" required>
                </div><br>

                <div class="auth-form__group">
                    <label>Mô tả:</label>
                    <textarea name="description" id="edit_description" class="auth-form__input" style="height:100px; padding:10px;"></textarea>
                </div><br>

                <div class="auth-form__group">
                    <label>Ảnh hiện tại:</label><br>
                    <img id="edit_preview" src="" width="80" style="border-radius:5px; margin: 10px 0;"><br>
                    <label>Thay đổi ảnh (để trống nếu giữ nguyên):</label>
                    <input type="file" name="image">
                </div><br>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Hủy</button>
                    <button type="submit" class="btn-primary">Cập nhật danh mục</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>

<script>
    function openEditModal(category) {
        // Điền dữ liệu vào các input trong Modal
        document.getElementById('edit_id').value = category.id;
        document.getElementById('edit_name').value = category.name;
        document.getElementById('edit_slug').value = category.slug;
        document.getElementById('edit_description').value = category.description;

        // Đổ trạng thái vào select
        document.getElementById('edit_is_active').value = category.is_active;

        // Hiển thị ảnh cũ để xem trước
        document.getElementById('edit_preview').src = category.image;

        // Hiển thị Modal
        document.getElementById('editModal').style.display = 'block';
    }

    // Đóng modal khi click ra ngoài vùng trắng
    window.onclick = function(event) {
        let addModal = document.getElementById('addModal');
        let editModal = document.getElementById('editModal');
        if (event.target == addModal) addModal.style.display = "none";
        if (event.target == editModal) editModal.style.display = "none";
    }
</script>