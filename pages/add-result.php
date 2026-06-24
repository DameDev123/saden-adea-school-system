<?php
/**
 * Add Result Page
 * 
 * Form to add student grades/results
 * - Calculate total from exam, CA, and assignment scores
 * - Auto-assign grade (A-F)
 * - Validation
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$error = '';
$students = get_records('students', 'is_active = 1', 'last_name ASC');
$subjects = get_records('subjects', 'is_active = 1', 'subject_name ASC');
$classes = get_records('classes', '', 'class_name ASC');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $class_id = (int)($_POST['class_id'] ?? 0);
    $term = sanitize($_POST['term'] ?? '');
    $academic_year = sanitize($_POST['academic_year'] ?? '');
    $exam_score = !empty($_POST['exam_score']) ? (float)$_POST['exam_score'] : null;
    $continuous_assessment = !empty($_POST['continuous_assessment']) ? (float)$_POST['continuous_assessment'] : null;
    $assignment = !empty($_POST['assignment']) ? (float)$_POST['assignment'] : null;
    $comments = sanitize($_POST['comments'] ?? '');
    
    if ($student_id <= 0 || $subject_id <= 0 || $class_id <= 0 || empty($academic_year)) {
        $error = "All required fields must be filled.";
    } else {
        // Calculate total marks
        $total_marks = 0;
        if ($exam_score) $total_marks += $exam_score;
        if ($continuous_assessment) $total_marks += $continuous_assessment;
        if ($assignment) $total_marks += $assignment;
        
        // Get grade
        $grade = get_grade($total_marks);
        
        // Check for duplicate
        $check_query = "SELECT id FROM results WHERE student_id = ? AND subject_id = ? AND academic_year = ? AND term = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("iiss", $student_id, $subject_id, $academic_year, $term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Result already exists for this student, subject, and term.";
        } else {
            $entered_by = $_SESSION['user_id'];
            $data = [
                'student_id' => $student_id,
                'subject_id' => $subject_id,
                'class_id' => $class_id,
                'term' => $term,
                'academic_year' => $academic_year,
                'exam_score' => $exam_score,
                'continuous_assessment' => $continuous_assessment,
                'assignment' => $assignment,
                'total_marks' => $total_marks,
                'grade' => $grade,
                'comments' => $comments,
                'entered_by' => $entered_by
            ];
            
            if (insert_record('results', $data)) {
                header("Location: results.php?success=added");
                exit();
            } else {
                $error = "Error adding result. Please try again.";
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
            <h1 class="mb-1">Add Student Result</h1>
            <p class="text-muted">Enter student grades and marks</p>
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
                        <h5 class="mb-3 border-bottom pb-2"><i class="fas fa-graduation-cap"></i> Student Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                                <select class="form-control" id="student_id" name="student_id" required>
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students as $stud): ?>
                                        <option value="<?php echo $stud['id']; ?>">
                                            <?php echo htmlspecialchars($stud['first_name'] . ' ' . $stud['last_name'] . ' (' . $stud['admission_number'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classes as $cls): ?>
                                        <option value="<?php echo $cls['id']; ?>">
                                            <?php echo htmlspecialchars($cls['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <h5 class="mb-3 border-bottom pb-2 mt-4"><i class="fas fa-book"></i> Subject & Term Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-control" id="subject_id" name="subject_id" required>
                                    <option value="">-- Select Subject --</option>
                                    <?php foreach ($subjects as $subj): ?>
                                        <option value="<?php echo $subj['id']; ?>">
                                            <?php echo htmlspecialchars($subj['subject_name'] . ' (' . $subj['subject_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="term" class="form-label">Term</label>
                                <select class="form-control" id="term" name="term">
                                    <option value="">-- Select Term --</option>
                                    <option value="Term 1">Term 1</option>
                                    <option value="Term 2">Term 2</option>
                                    <option value="Term 3">Term 3</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="academic_year" name="academic_year" placeholder="e.g., 2024" value="<?php echo date('Y'); ?>" required>
                        </div>
                        
                        <h5 class="mb-3 border-bottom pb-2 mt-4"><i class="fas fa-chart-bar"></i> Marks</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="exam_score" class="form-label">Exam Score</label>
                                <input type="number" class="form-control" id="exam_score" name="exam_score" step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="continuous_assessment" class="form-label">Continuous Assessment (CA)</label>
                                <input type="number" class="form-control" id="continuous_assessment" name="continuous_assessment" step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="assignment" class="form-label">Assignment</label>
                                <input type="number" class="form-control" id="assignment" name="assignment" step="0.01" min="0" max="100">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Additional comments about the student's performance..."></textarea>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Result</button>
                                <a href="<?php echo BASE_URL; ?>pages/results.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle"></i> Grade Scale</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>A</strong></td>
                                <td>80 - 100</td>
                            </tr>
                            <tr>
                                <td><strong>B</strong></td>
                                <td>70 - 79</td>
                            </tr>
                            <tr>
                                <td><strong>C</strong></td>
                                <td>60 - 69</td>
                            </tr>
                            <tr>
                                <td><strong>D</strong></td>
                                <td>50 - 59</td>
                            </tr>
                            <tr>
                                <td><strong>E</strong></td>
                                <td>40 - 49</td>
                            </tr>
                            <tr>
                                <td><strong>F</strong></td>
                                <td>Below 40</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>