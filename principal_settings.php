<?php
include("db.php");
include("upload_helper.php");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'principal') {
    die("Access denied.");
}

$schoolName = trim($_SESSION['school_name'] ?? '');

$currentUser = null;
$userStmt = $conn->prepare("SELECT name, email, school_name, password, profile_picture FROM users WHERE id = ?");
$userStmt->bind_param("i", $_SESSION['user_id']);
$userStmt->execute();
$currentUser = $userStmt->get_result()->fetch_assoc();
$currentUserAvatar = $currentUser['profile_picture'] ?? '';

$schoolLogoPath = '';
if ($schoolName !== '') {
    $logoStmt = $conn->prepare("SELECT logo_path FROM school_logos WHERE school_name = ?");
    $logoStmt->bind_param("s", $schoolName);
    $logoStmt->execute();
    $logoRow = $logoStmt->get_result()->fetch_assoc();
    $schoolLogoPath = $logoRow['logo_path'] ?? '';
}

$passwordMessage = '';
$avatarMessage = '';
$logoMessage = '';

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
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_logo'])) {
    if ($schoolName === '') {
        $logoMessage = '<div class="message">Your account has no school assigned, so a logo can\'t be linked to it.</div>';
    } else {
        $upload = handle_image_upload($_FILES['logo'] ?? [], 'uploads/school_logos');
        if (!$upload['success']) {
            $logoMessage = '<div class="message">' . htmlspecialchars($upload['error']) . '</div>';
        } else {
            $upsertStmt = $conn->prepare(
                "INSERT INTO school_logos (school_name, logo_path, updated_by, updated_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE logo_path = VALUES(logo_path), updated_by = VALUES(updated_by), updated_at = NOW()"
            );
            $upsertStmt->bind_param("ssi", $schoolName, $upload['path'], $_SESSION['user_id']);
            if ($upsertStmt->execute()) {
                delete_uploaded_file($schoolLogoPath);
                $schoolLogoPath = $upload['path'];
                $logoMessage = '<div class="message message--success">School logo updated.</div>';
            } else {
                delete_uploaded_file($upload['path']);
                $logoMessage = '<div class="message">Failed to save school logo. Please try again.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Settings - IPCRF</title>
    <link rel="stylesheet" href="style.css?v=<?php echo @filemtime(__DIR__ . '/style.css'); ?>">
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-card">
        <div class="admin-header">
            <div>
                <h2>Settings</h2>
                <p>Manage your account and your school's logo.</p>
            </div>
            <div class="admin-actions">
                <a class="btn btn-inline" href="principal_dashboard.php">Back to Dashboard</a>
                <a class="btn btn-inline btn-muted" href="logout.php">Logout</a>
            </div>
        </div>

        <div class="settings-section">
            <h3>Account Information</h3>
            <p><strong><?php echo htmlspecialchars($currentUser['name'] ?? ''); ?></strong> &mdash; <?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
            <p>School: <?php echo htmlspecialchars($schoolName !== '' ? $schoolName : 'Not set'); ?></p>
        </div>

        <div class="settings-section">
            <h3>Profile Picture</h3>
            <div class="settings-avatar-preview">
                <?php if ($currentUserAvatar): ?>
                    <img src="<?php echo htmlspecialchars($currentUserAvatar); ?>" alt="">
                <?php else: ?>
                    <span class="settings-avatar-preview__placeholder">&#128100;</span>
                <?php endif; ?>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
                <div class="form-group">
                    <label for="avatar">Choose an image (JPG, PNG, GIF, or WEBP &mdash; max 2MB)</label>
                    <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/gif,image/webp" required>
                </div>
                <button type="submit" name="update_avatar" class="btn btn-inline">Upload Picture</button>
                <?php echo $avatarMessage; ?>
            </form>
        </div>

        <div class="settings-section">
            <h3>School Logo</h3>
            <p>This logo represents <strong><?php echo htmlspecialchars($schoolName !== '' ? $schoolName : 'your school'); ?></strong>.</p>
            <div class="settings-avatar-preview settings-avatar-preview--square">
                <?php if ($schoolLogoPath): ?>
                    <img src="<?php echo htmlspecialchars($schoolLogoPath); ?>" alt="">
                <?php else: ?>
                    <span class="settings-avatar-preview__placeholder">&#127979;</span>
                <?php endif; ?>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
                <div class="form-group">
                    <label for="logo">Choose an image (JPG, PNG, GIF, or WEBP &mdash; max 2MB)</label>
                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/gif,image/webp" required>
                </div>
                <button type="submit" name="update_logo" class="btn btn-inline">Upload School Logo</button>
                <?php echo $logoMessage; ?>
            </form>
        </div>

        <div class="settings-section">
            <h3>Change Password</h3>
            <form method="POST" action="" class="admin-form">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="8" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-inline">Update Password</button>
                <?php echo $passwordMessage; ?>
            </form>
        </div>
    </div>
</div>
</body>
</html>
