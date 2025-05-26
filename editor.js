function format(command) {
  document.execCommand(command, false, null);
}

function submitContent() {
  const editor = document.getElementById('editor');
  const hidden = document.getElementById('hiddenContent');

  const htmlContent = editor.innerHTML.trim();
  const plainText = editor.innerText.trim();

  if (plainText === '' || plainText === '<br>') {
    alert('Komentar tidak boleh kosong.');
    return false;
  }
  hidden.value = htmlContent;
  return true;
}

editor.addEventListener("drop", (e) => {
    e.preventDefault();
    const files = e.dataTransfer.files;

    for (let file of files) {
        if (file.type.startsWith("image/")) {
            const formData = new FormData();
            formData.append("image", file);

            fetch("/pjbl/upload.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(url => {
                const img = document.createElement("img");
                img.src = url;
                img.style.maxWidth = "100%";
                editor.appendChild(img);
            })
            .catch(() => alert("Upload gagal"));
        } else {
            alert("Hanya file gambar diperbolehkan.");
        }
    }
});
