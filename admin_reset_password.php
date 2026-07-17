<?php
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: index.php");
    exit;
}

if (!isset($_POST['admin_reset_password'])) {
    header("Location: view_ipcrf.php");
    exit;
}

$resetUserEmail = trim($_POST['reset_user_email'] ?? '');
$temporaryPassword = trim($_POST['temporary_password'] ?? '');

if ($resetUserEmail === '' || $temporaryPassword === '' || !filter_var($resetUserEmail, FILTER_VALIDATE_EMAIL)) {
    header("Location: view_ipcrf.php?reset_status=invalid");
    exit;
}

try {
    $findUserStmt = $conn->prepare("SELECT id, role FROM users WHERE email = ?");
    $findUserStmt->bind_param("s", $resetUserEmail);
    $findUserStmt->execute();
    $findUserResult = $findUserStmt->get_result();

    if ($findUserResult && $findUserResult->num_rows > 0) {
        $targetUser = $findUserResult->fetch_assoc();

        if (!isset($targetUser['role'])) {
            header("Location: view_ipcrf.php?reset_status=error");
            exit;
        }

        $hashedTempPassword = password_hash($temporaryPassword, PASSWORD_DEFAULT);
        $resetStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $resetStmt->bind_param("ss", $hashedTempPassword, $resetUserEmail);

        if ($resetStmt->execute()) {
            header("Location: view_ipcrf.php?reset_status=success&reset_email=" . urlencode($resetUserEmail));
            exit;
        }
    } else {
        header("Location: view_ipcrf.php?reset_status=notfound");
        exit;
    }
} catch (mysqli_sql_exception $e) {
    // fall through to generic error redirect
}

header("Location: view_ipcrf.php?reset_status=error");
exit;
?>
