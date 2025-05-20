function format(command) {
  document.execCommand(command, false, null);
}

function insertImage() {
  const url = prompt("Enter image URL");
  if (url) {
    document.execCommand('insertImage', false, url);
  }
}

function insertLink() {
  const url = prompt("Enter URL");
  if (url) {
    document.execCommand('createLink', false, url);
  }
}

function insertCode() {
  const code = prompt("Enter code");
  if (code) {
    document.execCommand('insertHTML', false, `<pre><code>${code}</code></pre>`);
  }
}

function submitContent() {
  const editor = document.getElementById('editor');
  const hidden = document.getElementById('hiddenContent');
  hidden.value = editor.innerHTML.trim();

  if (hidden.value === '' || hidden.value === '<br>') {
    alert('Komentar tidak boleh kosong.');
    return false;
  }
  return true;
}
