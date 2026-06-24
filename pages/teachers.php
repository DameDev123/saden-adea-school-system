<?php
/**
 * Teachers List Page
 * 
 * Display all teachers with search and filter functionality
 * - View all teachers
 * - Search by name or email
 * - Edit and delete functionality
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_login();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = 'is_active = 1';

if (!empty($search)) {
    $search_escaped = escape_string($search);
    $where .= " AND (first_name LIKE '%$search_escaped%' OR last_name LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%')";
}

$teachers = get_records('teachers', $where, 'last_name ASC');

// Handle delete
if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    if (delete_record('teachers', $delete_id)) {
        header("Location: teachers.php?success=deleted");
        exit();
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-1">Teachers</h1>
            <p class="text-muted">Manage all teachers in the system</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>pages/add-teacher.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Teacher
            </a>
        </div>
    </div>
    
    <!-- Success Message -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Teacher successfully <?php echo htmlspecialchars($_GET['success']); ?>!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Search Bar -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-3">
                <form method="GET" action="">
                    <div class="input-group">
                        <input 
                            type="text" 
                            class="form-control" 
                            name="search" 
                            placeholder="Search by name or email..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="teachers.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Teachers Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (!empty($teachers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Specialization</th>
                                        <th>Employment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['specialization'] ?? 'N/A'); ?></td>
                                            <td><?php echo format_date($teacher['employment_date']); ?></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>pages/edit-teacher.php?id=<?php echo $teacher['id']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $teacher['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Delete this teacher?')">
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
                            <i class="fas fa-info-circle"></i> No teachers found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>