<?php
session_start();
session_destroy();
?>
<script>
localStorage.removeItem('token');
localStorage.removeItem('role');
window.location.href = 'pages/login.php';
</script>