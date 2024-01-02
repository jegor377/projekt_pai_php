document.addEventListener("DOMContentLoaded", () => {
})

function current_datetime(date = new Date()) {
  return date.toISOString().slice(0, 19).replace('T', ' ');
}

function prevoius_day() {
  const now = new Date();
  now.setDate(now.getDate() - 1);
  return now;
}
