<?php
/* =========================================================
   CAFFEINE & COVE
   ADMIN FOOTER
   ========================================================= */

$scriptPath = $_SERVER['SCRIPT_NAME'];

$adminPosition = strpos($scriptPath, '/admin/');

if ($adminPosition !== false) {
    $adminBase = substr($scriptPath, 0, $adminPosition + 7);
} else {
    $adminBase = '/';
}
?>

<footer class="main-footer">

    <strong>
        Caffeine & Cove
    </strong>

    &copy; 2026 All rights reserved.


</footer>


</div>


<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script src="<?php echo $adminBase; ?>assests/plugins/jquery/jquery.min.js"></script>

<script src="<?php echo $adminBase; ?>assests/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="<?php echo $adminBase; ?>assests/js/adminlte.min.js"></script>

</body>
</html>