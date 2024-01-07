<?php

require_once("templates/session.php");

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/input.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/avatar.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/files.php");

function save_profile_picture($user_id) {
  if(was_uploaded('avatar')) {
    $check = @getimagesize($_FILES['avatar']['tmp_name']);
    if($check === false) {
      return [
        'error' => "Plik nie jest obrazem!",
        'saved' => false
      ];
    }
    if($_FILES['avatar']['size'] > 500000) {;
      return [
        'error' => "Zdjęcie profilowe jest zbyt duże!",
        'saved' => false
      ];
    }
    
    $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $target_path = getcwd() . '/avatars/img_' . $user_id . "." . $extension;

    if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_path)) {
      return [
        'error' => "Nie udało się zapisać pliku zdjęcia profilowego!",
        'saved' => false
      ];
    }

    $new_avatar_url = '/avatars/img_' . $user_id . "." . $extension;
    if(!Db::save_profile_picture($user_id, $new_avatar_url)) {
      return [
        'error' => "Nie udało się zapisać pliku zdjęcia profilowego!",
        'saved' => false
      ];
    }

    return [
      'error' => null,
      'saved' => true
    ];
  }

  return [
    'error'=> null,
    'saved' => false
  ];
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setting_type'])) {
  switch($_POST['setting_type']) {
    case 'primary_info': {
      $res = save_profile_picture($user['id']);
      $error_msg = $res['error'];
      
      if($res['error'] === null && $res['saved']) {
        $_SESSION['user'] = Db::get_user_by_id($user['id']);
        $user = $_SESSION['user'];
      }

      if($res['error'] === null && isset($_POST['name'])) {
        if(strlen($_POST['name']) > 256) {
          $error_msg = "Nazwa użytkownika jest zbyt długa!";
        } else if(!Db::update_user_name($user['id'], $_POST['name'])) {
          $error_msg = "Nie udało się zmienić nazwy użytkownika";
        } else {
          $_SESSION['user'] = Db::get_user_by_id($user['id']);
          $user = $_SESSION['user'];
        }
      }
    } break;
    case 'change_password': {
      if(!isset($_POST['current_password'])) {
        $error_msg = 'Nie podano aktualnego hasła!';
      }
      if(!isset($_POST['new_password'])) {
        $error_msg = 'Nie podano nowego hasła!';
      }
      if(!isset($_POST['new_password_verify'])) {
        $error_msg = 'Nie podano ponownie nowego hasła!';
      }
      if(!isset($error_msg) && $_POST['new_password'] === $_POST['new_password_verify']) { 
        $result = Db::update_password($user['id'], $_POST['current_password'], $_POST['new_password']);

        if($result !== null) {
          switch($result) {
            case ChangePasswordError::CantFindUser: {
              $error_msg = "Nie można znaleźć takiego użytkownika!";
            } break;
            case ChangePasswordError::CurrentPasswordIncorrect: {
              $error_msg = "Aktualne hasło jest niepoprawne!";
            } break;
            case ChangePasswordError::UnknownError: {
              $error_msg = "Wystąpił błąd!";
            } break;
          }
        } else {
          $password_change_success_msg = "Udało się zaktualizować hasło!";
        }

      } else if(!isset($error_msg)) {
        $error_msg = "Hasła się nie zgadzają!";
      }
    } break;
  }
}

$css_files = [
  '/css/settings.css'
];

require_once("templates/header.php");
?>

<main class="container">
  <article class="settings-container">
    <h2>Podstawowe informacje</h2>
    <form id="primary_info_form" action="/settings.php" method="POST" enctype="multipart/form-data" class="form">
      <input type="hidden" name="setting_type" value="primary_info"/>
      <img class="avatar" src="<?= get_avatar_url($user); ?>"/>
      <div class="form-field">
        <label for="avatar">Avatar</label>
        <input name="avatar" id="avatar" type="file"/>
      </div>
      <div class="form-field">
        <label for="name">Imię i nazwisko</label>
        <!-- maxlength="256" -->
        <input name="name" id="name"  required <?= to_val($user['name'] ?? null) ?>/>
      </div>
      <input type="submit" value="Zapisz"/>
    </form>
    <?php if(isset($error_msg) && $error_msg != null && isset($_POST['setting_type']) && $_POST['setting_type'] === 'primary_info'): ?>
      <p><?= $error_msg ?></p>
    <?php endif; ?>
  </article>
  <article class="settings-container">
    <h2>Zmiana hasła</h2>
    <form class="form" id="change_password_form" action="/settings.php" method="POST">
      <input type="hidden" name="setting_type" value="change_password"/>
      <div class="form-field">
        <label for="current_password">Aktualne hasło</label>
        <input name="current_password" id="current_password" type="password" required/>
      </div>
      <div class="form-field">
        <label for="new_password">Nowe hasło</label>
        <input name="new_password" id="new_password" type="password" required/>
      </div>
      <div class="form-field">
        <label for="new_password_verify">Powtórz nowe hasło</label>
        <input name="new_password_verify" id="new_password_verify" type="password" required/>
      </div>
      <input type="submit" value="Zapisz"/>
    </form>
    <?php if(isset($error_msg) && $error_msg != null && isset($_POST['setting_type']) && $_POST['setting_type'] === 'change_password'): ?>
      <p><?= $error_msg ?></p>
    <?php endif; ?>
    <?php if(isset($password_change_success_msg)): ?>
      <p><?= $password_change_success_msg; ?></p>
    <?php endif; ?>
  </article>
</main>

<script src="/js/settings.js"></script>
<?php
require_once("templates/footer.php");
?>