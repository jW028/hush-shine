<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$response = [
    'success' => false,
    'message' => '', 
    'errors' => []
];

if (is_post()) {
    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);  
    $category_id = $_POST['category_id'] ?? '';

    if (empty($name)) {
        $response['errors'][] = 'Product name is required';
    }

    if ($price === false || $price <= 0) {
        $response['errors'][] = 'Valid price is required';
    }

    if ($quantity === false || $quantity <= 0) {
        $response['errors'][] = 'Valid quantity is required';
    }

    // Process image upload if exists
    $image_paths = [];
    if (!empty($_FILES['images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $upload_dir = '../images/products/';

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        for ($i=0; $i<count($_FILES['images']['name']); $i++) {
            if (isset($_FILES['images']['error'][$i]) && $_FILES['images']['error'][$i] == 0) {
                $filename = $_FILES['images']['name'][$i];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $seq_num = $i + 1;
                    $new_filename = uniqid() . "_$seq_num." . $ext;
                    $upload_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                        $image_paths[] = $new_filename;
                    } else {
                        $response['errors'][] = "Failed to upload image #" . ($i+1);
                    }
                } else {
                    $response['errors'][] = "Invalid file type for image #" . ($i+1);
                }
            }
        }
    }

    $image_path = !empty($image_paths) ? json_encode($image_paths) : null;

    // If no errors, insert product
    if (empty($response['errors'])) {
        try {
            $query = "SELECT prod_id FROM product ORDER BY prod_id DESC LIMIT 1";
            $stmt = $_db->query($query);
            $last_product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($last_product) {
                $last_id = intval(substr($last_product['prod_id'], 1));
                $prod_id = 'P' . str_pad($last_id + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $prod_id = 'P001';
            }

            $stmt = $_db->prepare("INSERT INTO product (prod_id, prod_name, prod_desc, price, quantity, cat_id, image) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$prod_id, $name, $description, $price, $quantity, $category_id, $image_path]);
            
            $_SESSION['success'] = 'Product added successfully';
            header('Location: admin_menu.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
               
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;