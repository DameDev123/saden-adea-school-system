<?php
/**
 * Edit Student Page
 * 
 * Form to edit existing student information
 * - Load student data
 * - Update validation
 * - Check for duplicate admission numbers
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$error = '';
$success = '';
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id <= 0) {
    header("Location: students.php");
    exit();
}

// Get student data
$student = get_record_by_id('students', $student_id);

if (empty($student)) {
    header("Location: students.php?error=notfound");
    exit();
}

// Get all classes for dropdown
$classes = get_records('classes', '', 'class_name ASC');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $admission_number = sanitize($_POST['admission_number'] ?? '');
    $date_of_birth = sanitize($_POST['date_of_birth'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $guardian_name = sanitize($_POST['guardian_name'] ?? '');
    $guardian_phone = sanitize($_POST['guardian_phone'] ?? '');
    $class_id = (int)($_POST['class_id'] ?? 0);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($admission_number) || empty($class_id)) {
        $error = "All required fields must be filled.";
    } elseif (!is_valid_email($email)) {
        $error = "Invalid email address.";
    } else {
        // Check if admission number changed and if new one already exists
        if ($admission_number != $student['admission_number']) {
            $check_query = "SELECT id FROM students WHERE admission_number = ? AND id != ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("si", $admission_number, $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Admission number already exists.";
            }
            $stmt->close();
        }
        
        if (empty($error)) {
            // Update student
            $data = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'admission_number' => $admission_number,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'phone' => $phone,
                'address' => $address,
                'guardian_name' => $guardian_name,
                'guardian_phone' => $guardian_phone,
                'class_id' => $class_id
            ];
            
            if (update_record('students', $data, $student_id)) {
                header("Location: students.php?success=updated");
                exit();
            } else {
                $error = "Error updating student. Please try again.";
            }
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">Edit Student</h1>
            <p class="text-muted">Update student information</p>
        </div>
    </div>
    
    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Edit Student Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" novalidate>
                        <!-- Personal Information Section -->
                        <h5 class="mb-3 border-bottom pb-2">
                            <i class="fas fa-user"></i> Personal Information
                        </h5>
                        
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="first_name" 
                                    name="first_name"
                                    value="<?php echo htmlspecialchars($student['first_name']); ?>"
                                    required
                                >
                            </div>
                            
                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="last_name" 
                                    name="last_name"
                                    value="<?php echo htmlspecialchars($student['last_name']); ?>"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email"
                                    value="<?php echo htmlspecialchars($student['email']); ?>"
                                    required
                                >
                            </div>
                            
                            <!-- Date of Birth -->
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="date_of_birth" 
                                    name="date_of_birth"
                                    value="<?php echo htmlspecialchars($student['date_of_birth']); ?>"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Gender -->
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="Other" <?php echo ($student['gender'] == 'Other' ? 'selected' : ''); ?>>Other</option>
                                    <option value="Male" <?php echo ($student['gender'] == 'Male' ? 'selected' : ''); ?>>Male</option>
                                    <option value="Female" <?php echo ($student['gender'] == 'Female' ? 'selected' : ''); ?>>Female</option>
                                </select>
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="phone" 
                                    name="phone"
                                    value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"
                                >
                            </div>
                        </div>
                        
                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea 
                                class="form-control" 
                                id="address" 
                                name="address" 
                                rows="2"
                            ><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Academic Information Section -->
                        <h5 class="mb-3 border-bottom pb-2 mt-4">
                            <i class="fas fa-graduation-cap"></i> Academic Information
                        </h5>
                        
                        <div class="row">
                            <!-- Admission Number -->
                            <div class="col-md-6 mb-3">
                                <label for="admission_number" class="form-label">Admission Number <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="admission_number" 
                                    name="admission_number"
                                    value="<?php echo htmlspecialchars($student['admission_number']); ?>"
                                    required
                                >
                            </div>
                            
                            <!-- Class -->
                            <div class="col-md-6 mb-3">
                                <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo ($class['id'] == $student['class_id'] ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Guardian Information Section -->
                        <h5 class="mb-3 border-bottom pb-2 mt-4">
                            <i class="fas fa-user-shield"></i> Guardian Information
                        </h5>
                        
                        <div class="row">
                            <!-- Guardian Name -->
                            <div class="col-md-6 mb-3">
                                <label for="guardian_name" class="form-label">Guardian Name</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="guardian_name" 
                                    name="guardian_name"
                                    value="<?php echo htmlspecialchars($student['guardian_name'] ?? ''); ?>"
                                >
                            </div>
                            
                            <!-- Guardian Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="guardian_phone" class="form-label">Guardian Phone</label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="guardian_phone" 
                                    name="guardian_phone"
                                    value="<?php echo htmlspecialchars($student['guardian_phone'] ?? ''); ?>"
                                >
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Student
                                </button>
                                <a href="<?php echo BASE_URL; ?>pages/students.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
