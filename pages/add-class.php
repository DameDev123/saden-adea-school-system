<?php
/**
 * Add Class Page
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = sanitize($_POST['class_name'] ?? '');
    $class_code = sanitize($_POST['class_code'] ?? '');
    $grade_level = (int)($_POST['grade_level'] ?? 0);
    $form = sanitize($_POST['form'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 30);
    
    if (empty($class_name) || empty($class_code) || $grade_level <= 0) {
        $error = "All required fields must be filled.";
    } else {
        $check_query = "SELECT id FROM classes WHERE class_code = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $class_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Class code already exists.";
        } else {
            $data = [
                'class_name' => $class_name,
                'class_code' => $class_code,
                'grade_level' => $grade_level,
                'form' => $form,
                'capacity' => $capacity
            ];
            
            if (insert_record('classes', $data)) {
                header("Location: classes.php?success=added");
                exit();
            } else {
                $error = "Error adding class. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">Add New Class</h1>
            <p class="text-muted">Fill in the form below to add a new class</p>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="" novalidate>
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class_name" name="class_name" placeholder="e.g., Form 1A" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_code" class="form-label">Class Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class_code" name="class_code" placeholder="e.g., F1A" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="grade_level" name="grade_level" min="1" max="6" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="form" class="form-label">Form (Section)</label>
                            <input type="text" class="form-control" id="form" name="form" placeholder="e.g., A, B, C">
                        </div>
                        
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="30" min="1">
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Class</button>
                                <a href="<?php echo BASE_URL; ?>pages/classes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>