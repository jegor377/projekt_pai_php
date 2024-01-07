<?php

require_once("templates/session.php");

if(!isset($_GET['id']) || $user['role'] !== 'trainer') {
  header("Location: /panel.php");
  exit();
}

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/array.php");

$results = Db::get_results_by_user_id($_GET["id"]);
$results = group_by_name($results, 'contest_id');

if($results) {
  $tasks = Db::get_contest_tasks_by_contest_ids(array_keys($results));
  $tasks = group_by_name($tasks, 'contest_id');
}

$student = Db::get_user_by_id($_GET['id']);
if(!$student) {
  header('Location: /panel.php?msg=Nie ma takiego sportowca');
  die();
}

$css_files = [
  "/css/sportsman_results.css"
];

require_once("templates/header.php");
?>

<main class="container">
  <h1>Wyniki sportowca: <?= $student['name'] ?></h1>
  <article class="results-container">
    <?php foreach($results as $contest): ?>
      <div class="contest box">
        <?php
          $contest_time = $contest[0]['time'];
          $contest_descr = $contest[0]['description'];
          $contest_id = $contest[0]['contest_id'];
        ?>
        <p><strong>Data:</strong> <?= $contest_time ?></p>
        <p><strong>Opis:</strong> <?= $contest_descr ?></p>
        <a href="/edit_contest.php?id=<?= $contest_id ?>">Edytuj</a>

        <table>
          <thead>
            <tr>
              <th>Nazwa zadania</th>
              <th>Wynik</th>
              <th>Ocena</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $contest_tasks = replace_ids($tasks[$contest_id], 'id');
              $contest_results = replace_ids($contest, 'task_id');
            ?>
            <?php foreach($contest_tasks as $task): ?>
              <tr>
                <td><?= $task['name'] ?></td>
                <td><?= $contest_results[$task['id']]['value'] ?? '-' ?></td>
                <td><?= $contest_results[$task['id']]['grade'] ?? '-' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </article>
</main>

<?php
require_once("templates/footer.php");
?>