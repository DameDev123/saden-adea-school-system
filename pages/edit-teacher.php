<?php
/**
 * Edit Teacher Page
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$error = '';
$teacher_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($teacher_id <= 0) {
    header("Location: teachers.php");
    exit();
}

$teacher = get_record_by_id('teachers', $teacher_id);

if (empty($teacher)) {
    header("Location: teachers.php?error=notfound");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $date_of_birth = sanitize($_POST['date_of_birth'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $employment_date = sanitize($_POST['employment_date'] ?? '');
    $qualification = sanitize($_POST['qualification'] ?? '');
    $specialization = sanitize($_POST['specialization'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = "All required fields must be filled.";
    } elseif (!is_valid_email($email)) {
        $error = "Invalid email address.";
    } else {
        if ($email != $teacher['email']) {
            $check_query = "SELECT id FROM teachers WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("si", $email, $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already exists.";
            }
            $stmt->close();
        }
        
        if (empty($error)) {
            $data = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'employment_date' => $employment_date,
                'qualification' => $qualification,
                'specialization' => $specialization
            ];
            
            if (update_record('teachers', $data, $teacher_id)) {
                header("Location: teachers.php?success=updated");
                exit();
            } else {
                $error = "Error updating teacher. Please try again.";
            }
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">Edit Teacher</h1>
            <p class="text-muted">Update teacher information</p>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" novalidate>
                        <h5 class="mb-3 border-bottom pb-2"><i class="fas fa-user"></i> Personal Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($teacher['date_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="Other" <?php echo ($teacher['gender'] == 'Other' ? 'selected' : ''); ?>>Other</option>
                                    <option value="Male" <?php echo ($teacher['gender'] == 'Male' ? 'selected' : ''); ?>>Male</option>
                                    <option value="Female" <?php echo ($teacher['gender'] == 'Female' ? 'selected' : ''); ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($teacher['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <h5 class="mb-3 border-bottom pb-2 mt-4"><i class="fas fa-briefcase"></i> Professional Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employment_date" class="form-label">Employment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="employment_date" name="employment_date" value="<?php echo htmlspecialchars($teacher['employment_date']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="qualification" class="form-label">Qualification</label>
                                <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo htmlspecialchars($teacher['qualification'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo htmlspecialchars($teacher['specialization'] ?? ''); ?>">
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Teacher</button>
                                <a href="<?php echo BASE_URL; ?>pages/teachers.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>