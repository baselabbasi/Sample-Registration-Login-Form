<?php
require_once '../lib/helpers.php';

$error = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $error = validate_register($_POST);

  $old = [
    'username' => trim((string) ($_POST['username'] ?? '')),
    'name' => trim((string) ($_POST['name'] ?? $_POST['Name'] ?? '')),
    'address' => trim((string) ($_POST['address'] ?? '')),
    'country' => trim((string) ($_POST['country'] ?? '')),
    'zipCode' => trim((string) ($_POST['zipCode'] ?? $_POST['ZipCode'] ?? '')),
    'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
    'sex' => trim((string) ($_POST['sex'] ?? '')),
    'language' => (array) ($_POST['language'] ?? []),
    'about' => trim((string) ($_POST['about'] ?? '')),
  ];
  if (!$errors) {
    if (find_user_by_email($old['email']))
      $errors['email'] = "Email already registered.";
    if (find_user_by_username($old['username']))
      $errors['username'] = "Username already exists.";
  }
  if (!$errors) {
    $user = [
      'username' => $old['username'],
      'password_hash' => password_hash((string) ($_POST['password'] ?? ''), PASSWORD_DEFAULT),
      'name' => $old['name'],
      'zipCode' => $old['zipCode'],
      'country' => $old['country'],
      'email' => $old['email'],
      'sex' => $old['sex'],
      'languages' => array_values((array) ($_POST['language'] ?? [])),
      'addr' => $old['address'],
      'about' => $old['about'],
      'createdAt' => date('c'),
    ];

    add_user($user);

    $dir = STORAGE_ROOT . '/' . $user['username'];
    if (!is_dir($dir))
      mkdir($dir, 0775, true);

    flash_set('success', 'Registration successful! Please log in.');
    redirect('login.php');
  }
  $countries = ["Egypt", "Saudi Arabia", "Jordan", "Syria", "Iraq", "Lebanon", "Libya", "Yemen", "Sudan", "Algeria", "Morocco"];
  $flash = flash_get();
}
?>

<!doctype html>

<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <section class="RegistrationSection">
    <div class="left side">
      <div class="signup">
        <h1 id="signupTitle">Sign Up</h1>
        <p id="signupText">
          Sign up with your simple details.<br />
          It will nbe cross checked by the administration.
        </p>
      </div>

      <div class="signin">
        <h1 id="signinTitle">Sign In</h1>
        <p id="signinText">Sign in with your email and password</p>
      </div>
    </div>
    <div class="right side">
      <?php if ($flash): ?>
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="field">
          <label for="username">User Id:</label>
          <input type="text" id="username" name="username" value="<?= e($old['username'] ?? '') ?>" />
          <span id="usernameError" class="error-message"><?= e($errors['username'] ?? '') ?></span>
        </div>

        <div class="field">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password">
          <span id="passwordError" class="error-message"><?= e($errors['password'] ?? '') ?></span>
        </div>

        <div class="field">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>">
          <span id="nameError" class="error-message"><?= e($errors['name'] ?? '') ?></span>
        </div>

        <div class="field">
          <label for="Address">Address:</label>
          <input type="text" id="address" name="address" value="<?= e($old['address'] ?? '') ?>" />
          <span id="addressError" class="error-message"></span>
        </div>

        <div class="field">
          <label for="country">Country:</label>
          <select id="country" name="country">
            <option value="">Select a country</option>
            <?php foreach ($countries as $c): ?>
              <option value="<?= e($c) ?>" <?= (($old['country'] ?? '') === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="error-message"><?= e($errors['country'] ?? '') ?></span>
        </div>

        <div class="field">
          <label for="zipCode">Zip Code:</label>
          <input type="text" id="zipCode" name="zipCode" value="<?= e($old['zipCode'] ?? '') ?>">
          <span id="zipCodeError" class="error-message"><?= e($errors['zipCode'] ?? '') ?></span>
        </div>

        <div class="field">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" />
          <span id="emailError" class="error-message"><?= e($errors['email'] ?? '') ?></span>
        </div>

        <div class="field full">
          <label for="sex">Sex:</label>

          <input type="radio" id="male" name="sex" value="male" <?= (($old['sex'] ?? '') === 'male') ? 'checked' : '' ?>>
          <label for="male">Male</label>
          <input type="radio" id="female" name="sex" value="female" <?= (($old['sex'] ?? '') === 'female') ? 'checked' : '' ?>>
          <label for="female">Female</label>

          <br />
          <span class="error-message"><?= e($errors['sex'] ?? '') ?></span>
        </div>

        <div class="field full">
          <label for="language">Language:</label>

          <?php $langs = (array)($old['language'] ?? []); ?>

        <input type="checkbox" id="arabic" name="language[]" value="arabic" <?= in_array('arabic', $langs, true) ? 'checked' : '' ?>>
          <label for="arabic">Arabic</label>
        <input type="checkbox" id="nonArabic" name="language[]" value="Non Arabic" <?= in_array('Non Arabic', $langs, true) ? 'checked' : '' ?>>
          <label for="nonArabic">Non Arabic</label>
        <span class="error-message"><?= e($errors['language'] ?? '') ?></span>
        </div>

        <div class="field full">
          <label for="About">About:</label>
          <textarea id="about" name="about" rows="4" cols="50"><?= e($old['about'] ?? '') ?></textarea>
          <span id="aboutError" class="error-message"></span>
        </div>
        <div class="loginOrSignup">
          <div class="login">
            <input type="button" value="Log in" onclick="location.href = 'Login.php'" />
          </div>

          <p id="or">or</p>

          <div class="submit">
            <input type="submit" value="Sign Up">
          </div>
        </div>
      </form>
    </div>
  </section>

  <script src="script.js"></script>
</body>

</html>