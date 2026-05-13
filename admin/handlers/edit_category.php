<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $is_active = $_POST['is_active']; // Nhận giá trị mới từ Modal
    // $is_active = isset($_POST['is_active']) ? 1 : 0;


    // Tạo slug mới dựa trên tên cập nhật
    // Hàm chuyển đổi tiếng Việt sang không dấu
    function create_slug($string)
    {
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            '/[^a-zA-Z0-9\-\_]/',
        );
        $replace = array(
            'a',
            'e',
            'i',
            'o',
            'u',
            'y',
            'd',
            'A',
            'E',
            'I',
            'O',
            'U',
            'Y',
            'D',
            '-',
        );
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower(trim($string, '-'));
        return $string;
    }

    // Áp dụng vào code của bạn
    $slug = create_slug($name);

    // Xử lý ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Có upload ảnh mới -> Xử lý upload
        $file_name = time() . "_" . $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], "../../assets/images/categories/" . $file_name);
        $image_path = "../../assets/images/categories/" . $file_name;

        // Cập nhật với ảnh mới
        $sql = "UPDATE categories SET name=?, description=?, image=?, slug=?, is_active=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $name, $description, $image_path, $slug, $is_active, $id);
    } else {
        // Không upload ảnh mới -> Giữ nguyên ảnh cũ
        $sql = "UPDATE categories SET name=?, description=?, slug=?, is_active=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $name, $description, $slug, $is_active, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../pages/categories.php?msg=updated");
    } else {
        echo "Lỗi: " . $conn->error;
    }
    // $stmt->close();
}
// // Nếu có ảnh (6 tham số: name(s), description(s), image(s), slug(s), is_active(i), id(i))
// $stmt->bind_param("ssssii", $name, $description, $image_path, $slug, $is_active, $id);

// // Nếu không có ảnh (5 tham số: name(s), description(s), slug(s), is_active(i), id(i))
// $stmt->bind_param("sssii", $name, $description, $slug, $is_active, $id);
