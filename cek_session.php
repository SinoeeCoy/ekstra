<?php
session_start();
echo "<h3>Debug Session</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['role'])) {
    echo "<p>Role terdeteksi: <strong>" . $_SESSION['role'] . "</strong></p>";
} else {
    echo "<p style='color:red;'>Role TIDAK terdeteksi dalam session!</p>";
}

if (isset($_SESSION['level'])) {
    echo "<p>Level terdeteksi: <strong>" . $_SESSION['level'] . "</strong></p>";
}

if (isset($_SESSION['user'])) {
    echo "<p>User terdeteksi: <strong>" . $_SESSION['user'] . "</strong></p>";
}
?>