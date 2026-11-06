<?php
/**
 * Company Portal - Admin layout footer (AdminLTE 4)
 */
declare(strict_types=1);

use App\Config\App;
use App\Core\Session;

$footerYear = date('Y');
?>
      </div><!-- /.container-fluid -->
    </div><!-- /.app-content -->
  </main><!-- /.app-main -->

  <footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
      <span class="text-secondary">v<?= e(App::VERSION); ?></span>
    </div>
    <strong>Copyright &copy; <?= $footerYear; ?> <?= e(App::NAME); ?>.</strong> All rights reserved.
  </footer>

</div><!-- /.app-wrapper -->

<script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= asset('vendor/adminlte/js/adminlte.min.js'); ?>"></script>
<script src="<?= asset('js/app.js'); ?>"></script>
<?php Session::clearOld(); ?>
</body>
</html>
