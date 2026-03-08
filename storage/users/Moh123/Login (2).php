<?php
require_once '../lib/helpers.php';


$errors = [];
$old = ['email' => '', 'agreement' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim((string) ($_POST['email'] ?? '')));
  $password = (string) ($_POST['password'] ?? '');
  $agreement = isset($_POST['agreement']) ? 1 : 0;

  $old = ['email' => $email, 'agreement' => $agreement];

  $errors = validate_login($_POST);

  if (!$errors) {
    $u = find_user_by_email($email);

    if (!$u) {
      $errors['login'] = "No registered user. Please register first.";
    } elseif (!password_verify($password, (string) $u['password_hash'])) {
      $errors['login'] = "Password not correct.";
    } else {
      $_SESSION['user'] = [
        'username' => $u['username'],
        'email' => $u['email'],
        'name' => $u['name'] ?? $u['username'],
      ];

      // ensure user folder exists
      user_root_dir();

      // OPTIONAL: create a file named by username on first login if not exists
      $base = user_root_dir();
      $marker = $base . '/' . $u['username'] . '.txt';
      if (!file_exists($marker))
        file_put_contents($marker, "Welcome " . $u['username'] . "\n");

      flash_set('success', 'Login successful!');
      redirect('file_manager.php');
    }
  }
}

$flash = flash_get();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="style.css" />
  <title>Login</title>
</head>

<body>
  <section class="LoginSection">
    <div class="left side">
      <div class="signup">
        <h1>Sign Up</h1>
        <p>Sign up with your simple details.<br />It will be cross checked by the administration.</p>
      </div>
      <div class="signin">
        <h1>Sign In</h1>
        <p>Sign in with your email and password</p>
      </div>
    </div>

    <div class="right side">
      <?php if ($flash): ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="field full">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" value="<?= e($old['email']) ?>">
          <span class="error-message"><?= e($errors['login'] ?? '') ?></span>
        </div>

        <div class="field full">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password">
        </div>

        <div class="field full">
          <input type="checkbox" id="agreement" name="agreement" <?= $old['agreement'] ? 'checked' : '' ?>>
          <label for="agreement" class="agreement-label">I agree to the terms and conditions</label>
          <span class="error-message"><?= e($errors['agreement'] ?? '') ?></span>
        </div>

        <div class="field full">
          <div class="loginOrSignup">
            <input type="button" value="Sign up" onclick="location.href='register.php'">
            <p id="or">or</p>
            <input type="submit" value="Log in">
          </div>
        </div>
      </form>
    </div>
  </section>
<!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>

</html>