<?php

require_once("templates/session.php");

if(!isset($_GET['id']) || $user['role'] !== 'trainer') {
  header("Location: /panel.php");
  exit();
}

require_once($_SERVER["DOCUMENT_ROOT"] ."/lib/array.php");

$contest = Db::get_contest_by_id($_GET['id']);
$contest_tasks = Db::get_contest_tasks_by_contest_id($_GET['id']);
$contest_results = Db::get_user_results_by_contest_id($_GET['id']);

$contest_results_by_task_id = group_by_name($contest_results, 'task_id');

$css_files = [
  "/css/raport.css"
];

require_once("templates/header.php");
?>

<main>
  <h1>Raport z zawodów</h1>
  <article>
    <h3>Data i godzina</h3>
    <p><?= $contest['time'] ?></p>
    <h3>Opis</h3>
    <p><?= $contest['description'] ?></p>
  </article>
  <article>
    <h2>Raport</h2>
    <?php if($contest_tasks): ?>
      <table>
        <thead>
          <tr>
            <th>Nazwa zadania</th>
            <th>Liczba wyników i ocen</th>
            <th>Średni wynik</th>
            <th>Średnia ocena</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($contest_tasks as $task): ?>
            <?php
              $result_array = $contest_results_by_task_id[$task['id']] ?? [];
              $avg_values = average_obj_array($result_array, 'value');
              $avg_grades = average_obj_array($result_array, 'grade');
            ?>
            <tr>
              <td><?= $task['name'] ?></td>
              <td><?= count($result_array) ?></td>
              <td><?= $avg_values ? round($avg_values, 2) : "-" ?></td>
              <td><?= $avg_grades ? round($avg_grades, 2) : "-" ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>Nie zdefiniowano żadnych zadań w zawodach.</p>
    <?php endif; ?>
  </article>
</main>

<?php
require_once("templates/footer.php");
?>