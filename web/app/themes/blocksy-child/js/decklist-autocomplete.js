document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll(".autocomplete-card");

    inputs.forEach(input => {
        let currentFocus;

        input.addEventListener("input", function(e) {
            const val = this.value;
            if (!val || val.length < 2) return;

            // Chiamata API Scryfall
            fetch(`https://api.scryfall.com/cards/autocomplete?q=${encodeURIComponent(val)}`)
                .then(resp => resp.json())
                .then(data => {
                    closeAllLists();
                    const list = document.createElement("div");
                    list.setAttribute("class", "autocomplete-items");
                    this.parentNode.appendChild(list);

                    data.data.forEach(card => {
                        const item = document.createElement("div");
                        item.innerHTML = card;
                        item.addEventListener("click", () => {
                            input.value = card;
                            closeAllLists();
                        });
                        list.appendChild(item);
                    });
                });
        });

        function closeAllLists(elmnt) {
            const items = document.querySelectorAll(".autocomplete-items");
            items.forEach(item => {
                if (elmnt != item && elmnt != input) item.parentNode.removeChild(item);
            });
        }

        document.addEventListener("click", function(e) {
            closeAllLists(e.target);
        });
    });

    // Limite copie carte
    document.querySelectorAll("input[type=number]").forEach(numInput => {
        numInput.addEventListener("input", () => {
            let val = parseInt(numInput.value) || 0;
            if (val > 4) numInput.value = 4;
            if (val < 0) numInput.value = 0;
        });
    });
});