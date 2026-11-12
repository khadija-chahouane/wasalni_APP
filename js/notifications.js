(function () {
    const box = document.getElementById("notif-box");
    if (!box) return;
    const api = box.dataset.api;
    const countEl = document.getElementById("notif-count");
    const listEl = document.getElementById("notif-list");
    const base = api.replace("API/notifications.php", "");

    async function charger() {
        try {
            const data = await (await fetch(api)).json();
            if (!data.success) return;
            countEl.textContent = data.unread;
            listEl.innerHTML = "";
            if (!data.items.length) {
                const li = document.createElement("li");
                li.textContent = "Aucune notification";
                listEl.appendChild(li);
            }
            data.items.forEach(n => {
                const li = document.createElement("li");
                if (n.lien) {
                    const a = document.createElement("a");
                    a.href = base + n.lien;
                    a.textContent = n.message;
                    li.appendChild(a);
                } else {
                    li.textContent = n.message;
                }
                if (String(n.is_read) === "0") li.className = "unread";
                listEl.appendChild(li);
            });
        } catch (e) {}
    }

    document.getElementById("notif-btn").addEventListener("click", async () => {
        listEl.hidden = !listEl.hidden;
        if (!listEl.hidden) {
            await fetch(api, {method: "POST"});
            countEl.textContent = "0";
        }
    });

    charger();
    setInterval(charger, 15000);
})();