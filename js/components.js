function CustomElement(type, {
  id = null,
  classes = [],
  style = null,
  children = [],
  text = ""
} = {}) {
  const element = document.createElement(type);

  element.textContent = text;

  if(!Array.isArray(children)) {
    const child = children;
    element.appendChild(child);
  } else {
    for(let child of children) {
      element.appendChild(child);
    }
  }

  if(id != null) {
    element.id = id;
  }

  if(!Array.isArray(classes)) {
    const className = classes;
    element.classList.add(className);
  } else {
    for(let className of classes) {
      element.classList.add(className);
    }
  }

  if(style != null) {
    element.style.cssText = style;
  }

  return element;
}

function Text(text) {
  return document.createTextNode(text);
}

function Div({
  id = null,
  classes = [],
  style = null,
  children = [],
  text = ""
}) {
  return CustomElement('div', {
    id,
    classes,
    style,
    children,
    text
  });
}

function Paragraph({
  id = null,
  classes = [],
  style = null,
  children = [],
  text = ""
}) {
  return CustomElement('p', {
    id,
    classes,
    style,
    children,
    text
  });
}

function Span({
  id = null,
  classes = [],
  style = null,
  children = [],
  text = ""
}) {
  return CustomElement('span', {
    id,
    classes,
    style,
    children,
    text
  });
}