<?php
require_once __DIR__ . '/../domain/helper.php';
require_once __DIR__ . '/../domain/FileLister.php';
require_once __DIR__ . '/../domain/PathGuard.php';
require_once __DIR__ . '/../domain/FileService.php';

require_login();
$user = current_user();

$fm = new PathGuard(STORAGE_ROOT, (string) ($user['username'] ?? ''));

$fileServicde = new FileService($fm);
$lister = new FileLister($fm);

$dir = (string) ($_GET['dir'] ?? '');
$dir = ($dir === '' || $dir === '.') ? '' : $fm->normalizeRel($dir);

$err = null;
$ok = null;

try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'mkdir') {
      $name = (string) ($_POST['name'] ?? '');
      $fileService->makeDir($dir, $name);
      $ok = 'Folder created.';
    } elseif ($action === 'delete') {
      $path = (string) ($_POST['path'] ?? '');
      $fileService->delete($path);
      $ok = 'Deleted.';
    } elseif ($action === 'upload') {
      $fileService->upload($dir, $_FILES['file'] ?? []);
      $ok = 'Uploaded.';
    }
  }

  $items = $lister->list($dir);
} catch (Throwable $t) {
  $err = $t->getMessage();
  $items = [];
}

function build_dir_link(string $dir): string
{
  return 'file_manager.php?dir=' . rawurlencode($dir);
}

$parentDir = '';
if ($dir !== '') {
  $parts = explode('/', $dir);
  array_pop($parts);
  $parentDir = implode('/', $parts);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>File Manager</title>
  <link rel="stylesheet" href="../style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" 
  integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body class="fm-body">


  <div class="fm-header">
    <div class="title">File Management System</div>
    <div class="right">
      <a href="../logout.php">Log out</a>
      <span ><i class="fa-solid fa-user"></i> <?= e((string) ($user['username'] ?? '')) ?></span>
    </div>
  </div>

  <div class="fm-container">

  
    <div class="fm-sub">
      <div class="h"><i class="fa-solid fa-folder-tree"></i> File Manager</div>

      <div class="actions">
        <button class="fm-btn" type="button" onclick="openModal('mkdirModal')">Create Folder</button>
        <button class="fm-btn" type="button" onclick="openModal('uploadModal')">Upload File</button>
      </div>
    </div>

 
    <?php if ($err): ?><div class="fm-alert err"><?= e($err) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="fm-alert ok"><?= e($ok) ?></div><?php endif; ?>

  
    <div class="fm-card">
      <table class="fm-table ">
        <thead>
          <tr>
            <th class="fm-column" >Title/Name</th>
            <th class="fm-column" >File Type</th>
            <th class="fm-column" >Date Added</th>
            <th class="fm-column"> Manage</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
          <tr><td class="text-center" colspan="4"  >Empty</td></tr>
        <?php endif; ?>

        <?php foreach ($items as $it): ?>
          <?php
          $name = (string) $it['name'];
          $isDir = (bool) $it['isDir'];
          $relPath = $dir === '' ? $name : ($dir . '/' . $name);

          $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
          $type = $isDir ? 'Folder' : ($ext ? ('.' . $ext) : 'File');
          $dt = $it['mtime'] ? date('m/d/Y g:i a', (int) $it['mtime']) : '-';
          ?>
          <tr>
            <td>
              <?php if ($isDir): ?>
                <i class="fa-regular fa-folder-open"></i> <span> <?= e($name) ?></span>
              <?php else: ?>
                <span > <?= e($name) ?></span>
                <div class="fm-name-details"><?= e($name) ?></div>
              <?php endif; ?>
            </td>

            <td><?= e($type) ?></td>
            <td><?= e($dt) ?></td>

            <td class="d-flex gap-3">
              
            <?php if ($isDir): ?>
             <a class="fm-icon view" href="<?= e(build_dir_link($relPath)) ?>" title="Open folder">
                 <i class="fa-regular fa-eye"></i></a>
              <?php else: ?>
               <a class="fm-icon view" href="" title="View file">
                   <i class="fa-regular fa-eye"></i></a>
               <?php endif; ?>

              <!-- Delete -->
              <form method="post">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="path" value="<?= e($relPath) ?>">
                <button class="fm-icon del" type="submit"
                        onclick="return confirm('Delete <?= e($name) ?> ?')">
                      <i class="fa-regular fa-trash-can" ></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    
  </div>

  <div class="fm-footer">Crafted at training with Sprintive</div>

  <!-- Create Folder Modal -->
  <div id="mkdirModal" class="fm-overlay" onclick="overlayClose(event, 'mkdirModal')">
    <div class="fm-modal">
      <button class="fm-close" type="button" onclick="closeModal('mkdirModal')">×</button>
      <h3>Create Folder</h3>
      <form method="post">
        <input type="hidden" name="action" value="mkdir">
        <input class="fm-input" type="text" name="name" placeholder="" required>
        <div class="fm-modal-actions">
          <button class="fm-btn gray" type="button" onclick="closeModal('mkdirModal')">Cancel</button>
          <button class="fm-btn" type="submit">Create</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Upload Modal -->
  <div id="uploadModal" class="fm-overlay" onclick="overlayClose(event, 'uploadModal')">
    <div class="fm-modal">
      <button class="fm-close" type="button" onclick="closeModal('uploadModal')">×</button>
      <h3>Upload File</h3>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <input class="fm-input" type="file" name="file" required>
        <div class="fm-modal-actions">
          <button class="fm-btn gray" type="button" onclick="closeModal('uploadModal')">Cancel</button>
          <button class="fm-btn" type="submit">Upload</button>
        </div>
      </form>
    </div>
  </div>

<script>
function openModal(id){ 
  document.getElementById(id).classList.add('open'); 
}
function closeModal(id){ 
  document.getElementById(id).classList.remove('open'); 
}
function overlayClose(e, id){
  if(e.target.classList.contains('fm-overlay')) closeModal(id);
 
}
</script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
  integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>