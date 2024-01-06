<?php
session_start();

if(isset($_SESSION["user_id"])) {
  header("Location: /panel.php");
  die();
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/db.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/input.php");

function fail() {
  global $error_msg;
  $error_msg = "Wystąpił błąd!";
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  try {
    $result = Db::register_user($_POST);
    if($result) {
      header('Location: /login.php?msg=Rejestracja się powiodła!');
      die();
    }
  } catch(RegisterException $e) {
    switch($e->getCode()) {
      case RegisterError::EmailMissing->value: {
        $error_msg = "Nie podano emaila!";
      } break;
      case RegisterError::EmailFormatIncorrect->value: {
        $error_msg = "Format emeila jest niepoprawny!";
      } break;
      case RegisterError::EmailTooLong->value: {
        $error_msg = "Email jest za długi!";
      } break;
      case RegisterError::NameMissing->value: {
        $error_msg = "Nie podano imienia!";
      } break;
      case RegisterError::PasswordMissing->value: {
        $error_msg = "Nie podano hasła!";
      } break;
      case RegisterError::PasswordVerifyMissing->value: {
        $error_msg = "Nie podano hasła do sprawdzenia!";
      } break;
      case RegisterError::PasswordsDontMatch->value: {
        $error_msg = "Hasła nie są takie same!";
      } break;
      case RegisterError::RoleMissing->value: {
        $error_msg = "Nie podano roli użytkownika!";
      } break;
      case RegisterError::RoleIncorrect->value: {
        $error_msg = "Rola jest niepoprawna!";
      } break;
      case RegisterError::TrainerIdMissing->value: {
        $error_msg = "Nie podano trenera!";
      } break;
      case RegisterError::UserExists->value: {
        $error_msg = "Użytkownik o podanym emailu już istnieje!";
      } break;
      case RegisterError::NameTooLong->value: {
        $error_msg = "Imię i nazwisko zbyt długie!";
      } break;
      case RegisterError::Other->value: {
        fail();
      } break;
      default: {
        fail();
      } break;
    }
  } catch(Exception $e) {
    fail();
  }
}

$trainers = Db::get_all_trainers();

require_once("templates/header.php");
?>

<main>
  <form id="register_form" action="/register.php" method="POST">
    <div>
      <label for="name">Imię i nazwisko</label>
      <input name="name" id="name" type="name" required maxlength="256" <?= to_val($_POST['name'] ?? null) ?>/>
    </div>
    <div>
      <label for="email">Email</label>
      <input name="email" id="email" type="email" required maxlength="100" <?= to_val($_POST['email'] ?? null) ?>/>
    </div>
    <div>
      <label for="password">Hasło</label>
      <input name="password" id="password" type="password" required <?= to_val($_POST['password'] ?? null) ?>/>
    </div>
    <div>
      <label for="password_verify">Powtórz hasło</label>
      <input name="password_verify" id="password_verify" type="password" required <?= to_val($_POST['password_verify'] ?? null) ?>/>
    </div>

    <div>
      <select name="role" id="role">
        <?php if($trainers->rowCount() > 0): ?>
          <option value="sportsman" <?= selected($_POST['role'] ?? null, 'sportsman'); ?>>Sportowiec</option>
        <?php endif; ?>
        <option value="trainer" <?= selected($_POST['role'] ?? null, 'trainer'); ?>>Trener</option>
      </select>
    </div>

    <?php if($trainers->rowCount() > 0): ?>
      <div id="trainer_id" <?= isset($_POST['role']) && $_POST['role'] === 'trainer' ? 'style="visibility: hidden;"' : '' ?>>
        <select name="trainer_id" <?= to_val($_POST['trainer_id'] ?? null) ?>>
          <?php foreach($trainers as $row): ?>
            <option value="<?= $row['id'] ?>" <?= selected($_POST['trainer_id'] ?? null, $row['id']); ?>><?= $row['name'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <input type="submit" value="Zarejestruj"/>
  </form>
  <?php
    if(isset($error_msg)) {
      ?>
        <p><?= $error_msg ?></p>
      <?php
    }
  ?>
</main>

<script src="/js/register.js"></script>
<?php
require_once("templates/footer.php");