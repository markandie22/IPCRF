<?php
include("db.php");
include("upload_helper.php");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header("Location: index.php");
    exit;
}

$currentUser = null;
$userStmt = $conn->prepare("SELECT name, email, school_name, password, profile_picture FROM users WHERE id = ?");
$userStmt->bind_param("i", $_SESSION['user_id']);
$userStmt->execute();
$currentUser = $userStmt->get_result()->fetch_assoc();

$currentUserName = $currentUser['name'] ?? '';
$currentUserSchool = $currentUser['school_name'] ?? '';
$currentUserAvatar = $currentUser['profile_picture'] ?? '';
$currentUserInitials = '';
foreach (array_slice(preg_split('/\s+/', trim($currentUserName)), 0, 2) as $part) {
    if ($part !== '') $currentUserInitials .= mb_strtoupper(mb_substr($part, 0, 1));
}
if ($currentUserInitials === '') $currentUserInitials = 'T';

$passwordMessage = '';
$avatarMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $passwordMessage = '<div class="message">Please fill in all password fields.</div>';
    } elseif (!password_verify($currentPassword, $currentUser['password'] ?? '')) {
        $passwordMessage = '<div class="message">Current password is incorrect.</div>';
    } elseif (strlen($newPassword) < 8) {
        $passwordMessage = '<div class="message">New password must be at least 8 characters.</div>';
    } elseif ($newPassword !== $confirmPassword) {
        $passwordMessage = '<div class="message">New password and confirmation do not match.</div>';
    } else {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newHash, $_SESSION['user_id']);
        if ($updateStmt->execute()) {
            $passwordMessage = '<div class="message message--success">Password updated successfully.</div>';
        } else {
            $passwordMessage = '<div class="message">Failed to update password. Please try again.</div>';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    $upload = handle_image_upload($_FILES['avatar'] ?? [], 'uploads/avatars');
    if (!$upload['success']) {
        $avatarMessage = '<div class="message">' . htmlspecialchars($upload['error']) . '</div>';
    } else {
        $updateStmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $updateStmt->bind_param("si", $upload['path'], $_SESSION['user_id']);
        if ($updateStmt->execute()) {
            delete_uploaded_file($currentUserAvatar);
            $currentUserAvatar = $upload['path'];
            $avatarMessage = '<div class="message message--success">Profile picture updated.</div>';
        } else {
            delete_uploaded_file($upload['path']);
            $avatarMessage = '<div class="message">Failed to save profile picture. Please try again.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - IPCRF</title>
    <link rel="stylesheet" href="teacher_dashboard.css?v=<?php echo @filemtime(__DIR__ . '/teacher_dashboard.css'); ?>">
</head>
<body>
<div class="dashboard-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-badge">IP</span>
            <span>IPCRF Classroom</span>
        </div>

        <div class="sidebar-profile">
            <span class="sidebar-profile__avatar">
                <?php if ($currentUserAvatar): ?>
                    <img src="<?php echo htmlspecialchars($currentUserAvatar); ?>" alt="">
                <?php else: ?>
                    <?php echo htmlspecialchars($currentUserInitials); ?>
                <?php endif; ?>
            </span>
            <div class="sidebar-profile__info">
                <strong><?php echo htmlspecialchars($currentUserName !== '' ? $currentUserName : 'Teacher'); ?></strong>
                <span><?php echo htmlspecialchars($currentUserSchool !== '' ? $currentUserSchool : 'No school set'); ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a class="sidebar-nav__link" href="dashboard.php">
                <span class="sidebar-nav__icon" aria-hidden="true">&#8962;</span> Dashboard
            </a>
            <a class="sidebar-nav__link active" href="teacher_settings.php">
                <span class="sidebar-nav__icon" aria-hidden="true">&#9881;</span> Settings
            </a>
        </nav>

        <div class="sidebar-footer">
            <a class="btn btn-secondary" href="logout.php">Logout</a>
        </div>
    </aside>

    <div class="dashboard-main">
    <main class="page-wrap">
        <section class="hero-card">
            <h1 class="hero-title">Settings</h1>
            <p class="hero-subtitle">Manage your account details and password.</p>
        </section>

        <section class="card-grid">
            <article class="card">
                <h3>Account Information</h3>
                <dl class="settings-info">
                    <dt>Name</dt>
                    <dd><?php echo htmlspecialchars($currentUserName !== '' ? $currentUserName : '&mdash;'); ?></dd>
                    <dt>Email</dt>
                    <dd><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></dd>
                    <dt>School</dt>
                    <dd><?php echo htmlspecialchars($currentUserSchool !== '' ? $currentUserSchool : '&mdash;'); ?></dd>
                </dl>
                <p class="settings-hint">To change your name or school, contact your school head or admin.</p>
            </article>

            <article class="card">
                <h3>Profile Picture</h3>
                <div class="settings-avatar-preview">
                    <?php if ($currentUserAvatar): ?>
                        <img src="<?php echo htmlspecialchars($currentUserAvatar); ?>" alt="">
                    <?php else: ?>
                        <span class="settings-avatar-preview__placeholder"><?php echo htmlspecialchars($currentUserInitials); ?></span>
                    <?php endif; ?>
                </div>
                <form method="POST" action="" enctype="multipart/form-data" class="settings-form">
                    <label for="avatar">Choose an image (JPG, PNG, GIF, or WEBP — max 2MB)</label>
                    <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/gif,image/webp" required>

                    <button type="submit" name="update_avatar" class="btn btn-primary">Upload Picture</button>
                    <?php echo $avatarMessage; ?>
                </form>
            </article>

            <article class="card">
                <h3>Change Password</h3>
                <form method="POST" action="" class="settings-form">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>

                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="8" required>

                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>

                    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                    <?php echo $passwordMessage; ?>
                </form>
            </article>
        </section>
    </main>
    </div>
</div>
</body>
</html>
