<?php
/**
 * Edit Class Page
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$error = '';
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($class_id <= 0) {
    header("Location: classes.php");
    exit();
}

$class = get_record_by_id('classes', $class_id);

if (empty($class)) {
    header("Location: classes.php?error=notfound");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = sanitize($_POST['class_name'] ?? '');
    $class_code = sanitize($_POST['class_code'] ?? '');
    $grade_level = (int)($_POST['grade_level'] ?? 0);
    $form = sanitize($_POST['form'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 30);
    
    if (empty($class_name) || empty($class_code) || $grade_level <= 0) {
        $error = "All required fields must be filled.";
    } else {
        if ($class_code != $class['class_code']) {
            $check_query = "SELECT id FROM classes WHERE class_code = ? AND id != ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("si", $class_code, $class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Class code already exists.";
            }
            $stmt->close();
        }
        
        if (empty($error)) {
            $data = [
                'class_name' => $class_name,
                'class_code' => $class_code,
                'grade_level' => $grade_level,
                'form' => $form,
                'capacity' => $capacity
            ];
            
            if (update_record('classes', $data, $class_id)) {
                header("Location: classes.php?success=updated");
                exit();
            } else {
                $error = "Error updating class. Please try again.";
            }
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">Edit Class</h1>
            <p class="text-muted">Update class information</p>
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
                            <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_code" class="form-label">Class Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class_code" name="class_code" value="<?php echo htmlspecialchars($class['class_code']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="grade_level" name="grade_level" value="<?php echo $class['grade_level']; ?>" min="1" max="6" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="form" class="form-label">Form (Section)</label>
                            <input type="text" class="form-control" id="form" name="form" value="<?php echo htmlspecialchars($class['form'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo $class['capacity']; ?>" min="1">
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Class</button>
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