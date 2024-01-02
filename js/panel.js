document.addEventListener("DOMContentLoaded", () => {
  const messages_container = document.getElementById('messages_container');
  fetch_messages(messages_container);
})

function current_datetime(date = new Date()) {
  return date.toISOString().slice(0, 19).replace('T', ' ');
}

function prevoius_day() {
  const now = new Date();
  now.setDate(now.getDate() - 1);
  return now;
}

var messages = [];
function fetch_messages(container) {
  const url = "/api/messages.php?" + new URLSearchParams({
    start_time: messages.length > 0 ? messages[messages.length - 1] : current_datetime(prevoius_day())
  });
  fetch(url, {
    method: 'GET'
  }).then(async res => {
    if(res.status === 200) {
      for(let message of await res.json()) {
        if(messages.find(m => m.id == message.id) === undefined) {
          messages.push(message);
          container.appendChild(Message({ message }));
        }
      }
    }
  }).catch(err => {
    console.log(err)
  })
}

function Message({
  id = null,
  classes = [],
  style = null,
  message
}) {
  return Div({
    id,
    classes,
    style,
    children: [
      Paragraph({
        text: message['content']
      })
    ]
  })
}
