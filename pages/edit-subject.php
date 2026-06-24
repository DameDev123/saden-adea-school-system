<?php
/**
 * Edit Subject Page
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$error = '';
$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($subject_id <= 0) {
    header("Location: subjects.php");
    exit();
}

$subject = get_record_by_id('subjects', $subject_id);
$teachers = get_records('teachers', 'is_active = 1', 'last_name ASC');

if (empty($subject)) {
    header("Location: subjects.php?error=notfound");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $subject_code = sanitize($_POST['subject_code'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $teacher_id = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
    $grade_level = !empty($_POST['grade_level']) ? (int)$_POST['grade_level'] : null;
    $credit_hours = (int)($_POST['credit_hours'] ?? 3);
    
    if (empty($subject_name) || empty($subject_code)) {
        $error = "All required fields must be filled.";
    } else {
        if ($subject_code != $subject['subject_code']) {
            $check_query = "SELECT id FROM subjects WHERE subject_code = ? AND id != ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("si", $subject_code, $subject_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Subject code already exists.";
            }
            $stmt->close();
        }
        
        if (empty($error)) {
            $data = [
                'subject_name' => $subject_name,
                'subject_code' => $subject_code,
                'description' => $description,
                'teacher_id' => $teacher_id,
                'grade_level' => $grade_level,
                'credit_hours' => $credit_hours
            ];
            
            if (update_record('subjects', $data, $subject_id)) {
                header("Location: subjects.php?success=updated");
                exit();
            } else {
                $error = "Error updating subject. Please try again.";
            }
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-1">Edit Subject</h1>
            <p class="text-muted">Update subject information</p>
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
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject_code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" value="<?php echo htmlspecialchars($subject['subject_code']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($subject['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="teacher_id" class="form-label">Teacher</label>
                                <select class="form-control" id="teacher_id" name="teacher_id">
                                    <option value="">-- Select Teacher --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>" <?php echo ($teacher['id'] == $subject['teacher_id'] ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="grade_level" class="form-label">Grade Level</label>
                                <input type="number" class="form-control" id="grade_level" name="grade_level" value="<?php echo htmlspecialchars($subject['grade_level'] ?? ''); ?>" min="1" max="6">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="credit_hours" class="form-label">Credit Hours</label>
                            <input type="number" class="form-control" id="credit_hours" name="credit_hours" value="<?php echo htmlspecialchars($subject['credit_hours'] ?? '3'); ?>" min="1">
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Subject</button>
                                <a href="<?php echo BASE_URL; ?>pages/subjects.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>