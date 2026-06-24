<?php
/**
 * Subjects List Page
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = 'is_active = 1';

if (!empty($search)) {
    $search_escaped = escape_string($search);
    $where .= " AND (subject_name LIKE '%$search_escaped%' OR subject_code LIKE '%$search_escaped%')";
}

$query = "SELECT s.*, t.first_name, t.last_name FROM subjects s
          LEFT JOIN teachers t ON s.teacher_id = t.id
          WHERE " . $where . " ORDER BY s.subject_name ASC";

$result = $conn->query($query);
$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    if (delete_record('subjects', $delete_id)) {
        header("Location: subjects.php?success=deleted");
        exit();
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-1">Subjects</h1>
            <p class="text-muted">Manage all subjects in the system</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>pages/add-subject.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Subject
            </a>
        </div>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Subject successfully <?php echo htmlspecialchars($_GET['success']); ?>!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-3">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by subject name or code..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="subjects.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (!empty($subjects)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Subject Name</th>
                                        <th>Subject Code</th>
                                        <th>Teacher</th>
                                        <th>Grade Level</th>
                                        <th>Credit Hours</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                            <td><?php echo htmlspecialchars(($subject['first_name'] ?? '') . ' ' . ($subject['last_name'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars($subject['grade_level'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($subject['credit_hours'] ?? '3'); ?></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>pages/edit-subject.php?id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $subject['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?')">
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
                            <i class="fas fa-info-circle"></i> No subjects found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>