<?php
/**
 * Classes List Page
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';

if (!empty($search)) {
    $search_escaped = escape_string($search);
    $where = " WHERE class_name LIKE '%$search_escaped%' OR class_code LIKE '%$search_escaped%'";
}

$query = "SELECT c.*, COUNT(s.id) as total_students FROM classes c 
          LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
          " . $where . " GROUP BY c.id ORDER BY c.grade_level ASC, c.form ASC";

$result = $conn->query($query);
$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    if (delete_record('classes', $delete_id)) {
        header("Location: classes.php?success=deleted");
        exit();
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-1">Classes</h1>
            <p class="text-muted">Manage all classes in the system</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>pages/add-class.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Class
            </a>
        </div>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Class successfully <?php echo htmlspecialchars($_GET['success']); ?>!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-3">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by class name or code..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="classes.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Clear</a>
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
                    <?php if (!empty($classes)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Class Code</th>
                                        <th>Grade</th>
                                        <th>Form</th>
                                        <th>Students</th>
                                        <th>Capacity</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($class['class_code']); ?></td>
                                            <td><?php echo $class['grade_level']; ?></td>
                                            <td><?php echo htmlspecialchars($class['form'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $class['total_students']; ?></span>
                                            </td>
                                            <td><?php echo $class['capacity']; ?></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>pages/edit-class.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $class['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this class?')">
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
                            <i class="fas fa-info-circle"></i> No classes found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>