<?php
// Convenience redirect: some users may try /dashboard.php — redirect to admin dashboard
header('Location: /admin/dashboard.php');
exit;
