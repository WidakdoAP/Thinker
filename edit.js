const editor = document.getElementById("editor");

function uploadImage(file) {
    const formData = new FormData();
    formData.append("image", file);

    return fetch("/pjbl/edit.php", {  
        method: "POST",
        body: formData
    }).then(res => {
        if (!res.ok) throw new Error("Upload gagal");
        return res.text();
    });
}

editor.addEventListener("keydown", (e) => {
    e.preventDefault();
});

editor.addEventListener("paste", (e) => {
    const items = e.clipboardData.items;
    let hasImage = false;

    for (let item of items) {
        if (item.type.startsWith("image/")) {
            hasImage = true;

            const file = item.getAsFile();

            uploadImage(file)
                .then(url => {
                    const img = document.createElement("img");
                    img.src = url;
                    img.style.maxWidth = "100%";
                    editor.appendChild(img);

                    // Set URL ke input hidden agar ikut submit
                    document.getElementById("profileImgPath").value = url;
                })
                .catch(() => alert("Upload gagal"));

            e.preventDefault();
        }
    }

    if (!hasImage) {
        e.preventDefault(); // Blokir paste teks atau data non-gambar
        alert("Hanya gambar yang dapat ditempel.");
    }
});

editor.addEventListener("drop", (e) => {
    e.preventDefault();
    const files = e.dataTransfer.files;

    for (let file of files) {
        if (file.type.startsWith("image/")) {
            uploadImage(file)
                .then(url => {
                    const img = document.createElement("img");
                    img.src = url;
                    img.style.maxWidth = "100%";
                    editor.appendChild(img);

                    // Set URL ke input hidden agar ikut submit
                    document.getElementById("profileImgPath").value = url;
                })
                .catch(() => alert("Upload gagal"));
        } else {
            alert("Hanya file gambar diperbolehkan.");
        }
    }
});

editor.addEventListener("dragover", (e) => {
    e.preventDefault();
});
