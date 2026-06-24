<?php
/**
 * Results/Grades List Page
 * 
 * Display all student results with filtering
 * - View all grades
 * - Filter by student, subject, academic year
 * - Calculate average marks
 * - Assign grades (A-F)
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$student_filter = isset($_GET['student']) ? (int)$_GET['student'] : 0;
$year_filter = isset($_GET['year']) ? sanitize($_GET['year']) : '';

$where = '1=1';

if ($student_filter > 0) {
    $where .= " AND r.student_id = $student_filter";
}

if (!empty($year_filter)) {
    $year_escaped = escape_string($year_filter);
    $where .= " AND r.academic_year = '$year_escaped'";
}

$query = "SELECT r.*, s.admission_number, s.first_name, s.last_name, 
          subj.subject_name, c.class_name
          FROM results r
          JOIN students s ON r.student_id = s.id
          JOIN subjects subj ON r.subject_id = subj.id
          JOIN classes c ON r.class_id = c.id
          WHERE " . $where . " ORDER BY r.academic_year DESC, s.last_name ASC";

$result = $conn->query($query);
$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = $row;
}

$students = get_records('students', 'is_active = 1', 'last_name ASC');

if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    if (delete_record('results', $delete_id)) {
        header("Location: results.php?success=deleted");
        exit();
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-1">Results & Grades</h1>
            <p class="text-muted">Manage student results and grades</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>pages/add-result.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Result
            </a>
        </div>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Result successfully <?php echo htmlspecialchars($_GET['success']); ?>!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-3">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <select class="form-control" name="student">
                                <option value="">-- Filter by Student --</option>
                                <?php foreach ($students as $stud): ?>
                                    <option value="<?php echo $stud['id']; ?>" <?php echo ($student_filter == $stud['id'] ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($stud['first_name'] . ' ' . $stud['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <input type="text" class="form-control" name="year" placeholder="Academic Year (e.g., 2024)" value="<?php echo htmlspecialchars($year_filter); ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-outline-primary w-100" type="submit"><i class="fas fa-filter"></i> Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (!empty($results)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Exam</th>
                                        <th>CA</th>
                                        <th>Total</th>
                                        <th>Grade</th>
                                        <th>Year</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $res): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($res['first_name'] . ' ' . $res['last_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($res['admission_number']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($res['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($res['class_name']); ?></td>
                                            <td><?php echo $res['exam_score'] ?? 'N/A'; ?></td>
                                            <td><?php echo $res['continuous_assessment'] ?? 'N/A'; ?></td>
                                            <td>
                                                <strong><?php echo $res['total_marks'] ?? 'N/A'; ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($res['grade'] == 'A' ? 'success' : ($res['grade'] == 'B' ? 'info' : 'warning')); ?>">
                                                    <?php echo $res['grade'] ?? 'N/A'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($res['academic_year'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>pages/edit-result.php?id=<?php echo $res['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $res['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this result?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle"></i> No results found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>