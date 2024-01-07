<?php
require_once("templates/session.php");

if(!isset($_GET['id']) && $user['role'] !== 'sportsman') {
  header("Location: /panel.php");
  die();
}

require_once("lib/array.php");

$contest = Db::get_contest_by_id($_GET["id"]);
if($contest) {
  $results = Db::get_user_results_in_contest_id($user['id'], $_GET['id']);
  $results = replace_ids($results, 'task_id');
  $tasks = Db::get_contest_tasks_by_contest_id($_GET['id']);
}

$css_files = [
  "/css/student_results.css"
];

require_once("templates/header.php");
?>

<main>
  <?php if($contest): ?>
    <h1>Wyniki zawodów z dnia: <?= $contest['time'] ?></h1>
    <article>
      <h2>Opis</h2>
      <p><?= $contest['description'] ?></p>
      <h2>Wyniki</h2>
      <table>
        <thead>
          <tr>
            <th>Nazwa zadania</th>
            <th>Wynik</th>
            <th>Ocena</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($tasks as $task): ?>
            <tr>
              <td><?= $task['name'] ?></td>
              <td><?= $results[$task['id']]['value'] ?? '-' ?></td>
              <td><?= $results[$task['id']]['grade'] ?? '-' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </article>
  <?php else: ?>
    <article>
      <p>Nie ma takich zawodów</p>
    </article>
  <?php endif; ?>
</main>

<?php
require_once("templates/footer.php");
?>