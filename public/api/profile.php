<?php
/**
 * Doctor Profile API (Procedural)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Simulated authenticated doctor ID
$doctorId = 1;

// Handle GET requests - Fetch doctor profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT 
                d.id,
                u.name,
                u.email,
                u.phone,
                d.specialty,
                d.experience_years,
                d.bio,
                d.photo
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = ?
        ");
        $stmt->execute([$doctorId]);
        $doctor = $stmt->fetch();
        
        if ($doctor) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'doctor' => [
                    'id' => (int)$doctor['id'],
                    'name' => $doctor['name'],
                    'email' => $doctor['email'],
                    'phone' => $doctor['phone'],
                    'specialty' => $doctor['specialty'],
                    'experience_years' => (int)$doctor['experience_years'],
                    'bio' => $doctor['bio'],
                    'photo' => $doctor['photo']
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => true, 'message' => 'Doctor not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle POST requests - Update doctor profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        global $pdo;
        
        // Get form data
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $specialty = isset($_POST['specialty']) ? trim($_POST['specialty']) : '';
        $experience_years = isset($_POST['experience_years']) ? (int)$_POST['experience_years'] : 0;
        $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
        
        // Validate required fields
        if (empty($name) || empty($specialty)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Name and specialty are required']);
            exit;
        }
        
        $photo = null;
        
        // Handle photo upload if provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            // Validate file type
            if (!in_array($file['type'], $allowed_types)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'Only image files are allowed']);
                exit;
            }
            
            // Validate file size (max 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'File size exceeds 5MB limit']);
                exit;
            }
            
            // Create uploads directory if it doesn't exist
            $upload_dir = __DIR__ . '/../../public/uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'doctor_' . $doctorId . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $photo = '/uploads/profiles/' . $filename;
            } else {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Failed to upload file']);
                exit;
            }
        }
        
        // Update users table (name, email, phone)
        if (!empty($name) || !empty($email) || !empty($phone)) {
            $user_fields = [];
            $user_params = [];
            
            if (!empty($name)) {
                $user_fields[] = 'name = ?';
                $user_params[] = $name;
            }
            if (!empty($email)) {
                $user_fields[] = 'email = ?';
                $user_params[] = $email;
            }
            if (!empty($phone)) {
                $user_fields[] = 'phone = ?';
                $user_params[] = $phone;
            }
            
            if (!empty($user_fields)) {
                // First get user_id from doctors table
                $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
                $stmt->execute([$doctorId]);
                $doctor_row = $stmt->fetch();
                
                if ($doctor_row) {
                    $user_id = $doctor_row['user_id'];
                    $user_params[] = $user_id;
                    $query = "UPDATE users SET " . implode(', ', $user_fields) . " WHERE id = ?";
                    $pdo->prepare($query)->execute($user_params);
                }
            }
        }
        
        // Update doctors table (specialty, experience_years, bio, photo)
        $doctor_fields = [];
        $doctor_params = [];
        
        if (!empty($specialty)) {
            $doctor_fields[] = 'specialty = ?';
            $doctor_params[] = $specialty;
        }
        if ($experience_years >= 0) {
            $doctor_fields[] = 'experience_years = ?';
            $doctor_params[] = $experience_years;
        }
        if (!empty($bio)) {
            $doctor_fields[] = 'bio = ?';
            $doctor_params[] = $bio;
        }
        if ($photo) {
            $doctor_fields[] = 'photo = ?';
            $doctor_params[] = $photo;
        }
        
        if (!empty($doctor_fields)) {
            $doctor_params[] = $doctorId;
            $query = "UPDATE doctors SET " . implode(', ', $doctor_fields) . " WHERE id = ?";
            $pdo->prepare($query)->execute($doctor_params);
        }
        
        // Fetch updated doctor data
        $stmt = $pdo->prepare("
            SELECT 
                d.id,
                u.name,
                u.email,
                u.phone,
                d.specialty,
                d.experience_years,
                d.bio,
                d.photo
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = ?
        ");
        $stmt->execute([$doctorId]);
        $updated_doctor = $stmt->fetch();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'doctor' => [
                'id' => (int)$updated_doctor['id'],
                'name' => $updated_doctor['name'],
                'email' => $updated_doctor['email'],
                'phone' => $updated_doctor['phone'],
                'specialty' => $updated_doctor['specialty'],
                'experience_years' => (int)$updated_doctor['experience_years'],
                'bio' => $updated_doctor['bio'],
                'photo' => $updated_doctor['photo']
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => true, 'message' => 'Method not allowed']);
?>
