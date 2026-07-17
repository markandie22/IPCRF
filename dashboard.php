<?php
include("db.php");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - IPCRF</title>
    <link rel="stylesheet" href="teacher_dashboard.css">
</head>
<body>
<div class="dashboard-shell">
    <header class="topbar">
        <div class="brand">
            <span class="brand-badge">IP</span>
            <span>IPCRF Classroom</span>
        </div>
        <div class="topbar-actions">
            <a class="btn btn-secondary" href="logout.php">Logout</a>
        </div>
    </header>

    <main class="page-wrap">
        <section class="hero-card">
            <h1 class="hero-title">Teacher Dashboard</h1>
            <p class="hero-subtitle">Welcome back! Manage your performance records and submit your IPCRF tasks quickly.</p>
        </section>

        <section class="card-grid">
            <article class="card">
                <h3>IPCRF Submission</h3>
                <p>Open and complete your Individual Performance Commitment and Review Form with updated objectives and outputs.</p>
                <a class="btn btn-primary" href="ipcrf_form.php">Fill IPCRF Form</a>
            </article>

            <article class="card">
                <h3>Quick Reminders</h3>
                <ul class="quick-list">
                    <li>Review your goals before final submission.</li>
                    <li>Keep supporting documents ready for validation.</li>
                    <li>Coordinate with your school head for deadlines.</li>
                </ul>
            </article>
        </section>
    </main>
</div>
</body>
</html>
