<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/db.php");
require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/avatar.php");

if(isset($_SESSION["user_id"])) {
  $user = Db::get_user_by_id($_SESSION['user_id']);
} else {
  header("Location: /login.php");
  exit();
}

if($user['role'] !== 'trainer') {
  header('Location: /panel.php');
  exit();
}

function go_to_panel() {
  header("Location: /panel.php");
  exit();
}

if(!isset($_GET['id']) && !isset($_POST['contest_id'])) {
  go_to_panel();
}

function delete_task_fail() {
  global $error_msg;
  $error_msg = "Nie udało się skasować zadania";
}

if(isset($_GET['action']) && isset($_GET['task_id'])) {
  switch($_GET['action']) {
    case 'delete': {
      try {
        if(Db::delete_task($_GET['task_id'])) {
          delete_task_fail();
        }
      } catch(Exception $e) {
        delete_task_fail();
      }
    } break;
  }
}

$contest_id = $_GET['id'] ?? $_POST['contest_id'];
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operation'])) {
  switch($_POST["operation"]) {
    case "save_primary_info": {
      save_primary_info();
    } break;
    case "add_task": {
      add_task();
    } break;
    case "save_results": {
      save_results();
    } break;
  }
}

$contest = Db::get_contest_by_id($contest_id);

if(!$contest) {
  go_to_panel();
}

$students = Db::get_trainer_students($user["id"]);

$contest_results = Db::get_user_results_by_contest_id($contest["id"]);
$contest_tasks = Db::get_contest_tasks_by_contest_id($contest["id"]);

$css_files = [
  '/css/edit_contest.css'
];

function result_of_student_for_task($contest_results, $user_id, $task_id) {
  foreach($contest_results as $row) {
    if($row['contestant_id'] === $user_id && $row['task_id'] === $task_id) {
      return $row;
    }
  }
  return false;
}

function StudentHead($student) {
?>
  <div class="student-head">
    <img class="student-avatar" src="<?= get_avatar_url($student); ?>"/>
    <p><?= $student['name'] ?></p>
  </div>
<?php
}

function save_primary_info() {
  global $contest_id;
  global $error_msg;
  if(!isset($_POST['date'])) {
    $error_msg = 'Nie podano daty';
    return;
  }

  if(!isset($_POST['descr'])) {
    $error_msg = 'Nie podano opisu';
    return;
  }

  try {
    Db::update_contest_primary_info($contest_id, $_POST['date'], $_POST['descr'], $_POST['finished'] ?? false);
  } catch(Exception $e) {
    $error_msg = "Nie udało się zaktualizować podstawowych informacji";
  }
}

function add_task() {
  global $contest_id;
  global $error_msg;

  if(!isset($_POST["task_name"])) {
    $error_msg = "Nie podano nazwy zadania";
    return;
  }

  if(strlen($_POST["task_name"]) > 256) {
    $error_msg = "Za długa nazwa zadania";
    return;
  }

  if($_POST['task_name'] !== '') {
    Db::add_task($contest_id, $_POST["task_name"]);
  }
}

function save_results() {
  global $error_msg;

  try {
    Db::update_results($_POST);
  } catch(UpdateResultException $e) {
    $error_msg = $e->getMessage();
  }
}

require_once("templates/header.php");
?>

<main class="container">
  <p class="error-msg"><?= $error_msg ?? ""; ?></p>
  <div class="primary-conf">
    <h1>Edycja zawodów</h1>
    <form class="primary-conf-form" action="/edit_contest.php" method="POST">
      <input type="hidden" name="operation" value="save_primary_info"/>
      <input type="hidden" name="contest_id" value="<?= $contest['id'] ?>"/>
      <div class="edit-field">
        <label for="date">Data</label>
        <input type="datetime-local" name="date" id="date" value="<?= $contest['time'] ?>"/>
      </div>
      <div class="edit-field">
        <label for="descr">Opis</label>
        <textarea id="descr" name="descr"><?= $contest['description'] ?></textarea>
      </div>
      <div>
        <label for="finished">Zakończone</label>
        <input type="checkbox" id="finished" name="finished" <?= $contest['finished'] ? "checked" : "" ?>/>
      </div>
      <input type="submit" value="Zapisz"/>
    </form>
  </div>
  <div class="contest-tasks">
    <h2>Zadania w zawodach</h2>
    <div class="tasks">
      <?php foreach($contest_tasks as $task): ?>
        <div class="task">
          <p><?= $task['name'] ?></p>
          <div>
            <a href="/edit_contest.php?id=<?= $contest['id'] ?>&action=delete&task_id=<?= $task['id'] ?>">X</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <form class="contest-tasks-form" action="/edit_contest.php" method="POST">
      <input type="hidden" name="operation" value="add_task"/>
      <input type="hidden" name="contest_id" value="<?= $contest['id'] ?>"/>
      <div>
        <label for="task_name">Nazwa</label>
        <input name="task_name" id="task_name"/>
      </div>
      <input type="submit" value="Dodaj"/>
    </form>
  </div>
  <div class="students-list">
    <h2>Wyniki sportowców</h2>
    <?php foreach($students as $student): ?>
      <form action="/edit_contest.php" method="POST">
        <input type="hidden" name="operation" value="save_results"/>
        <input type="hidden" name="contest_id" value="<?= $contest['id'] ?>"/>
        <input type="hidden" name="contestant_id" value="<?= $student['id'] ?>"/>
        <?= StudentHead($student) ?>
        <?php if($contest_tasks): ?>
          <table>
            <thead>
              <tr>
                <th>Nazwa zadania</th>
                <th>Wynik</th>
                <th>Ocena trenera</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($contest_tasks as $task): ?>
                <?php
                  $result = result_of_student_for_task($contest_results, $student['id'], $task['id']);
                ?>
                <tr>
                  <input type="hidden" name="task_id[]" value="<?= $task['id'] ?>"/>
                  <input type="hidden" name="result_id[]" value="<?= $result['id'] ?? 'new' ?>"/>
                  <td><?= $task['name'] ?></td>
                  <td><input type="number" name="value[]" value="<?= $result['value'] ?? "" ?>"/></td>
                  <td><input max="10" min="1" type="number" name="grade[]" value="<?= $result['grade'] ?? "" ?>"/></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
        <input type="submit" value="Zapisz"/>
      </form>
    <?php endforeach; ?>
  </div>
</main>

<?php
require_once("templates/footer.php");
?>