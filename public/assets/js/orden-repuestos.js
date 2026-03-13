(function () {
    var btn = document.getElementById("btnAgregarItem");
    var tbody = document.getElementById("ordenItemsBody");
    if (!btn || !tbody) return;

    btn.addEventListener("click", function () {
        var tr = document.createElement("tr");
        tr.innerHTML =
            '<td><input type="text" name="item_referencia[]" placeholder="Ref."></td>' +
            '<td><input type="text" name="item_descripcion[]" placeholder="Descripción"></td>' +
            '<td><input type="text" name="item_cantidad[]" placeholder="Cant."></td>' +
            '<td><input type="number" name="item_precio[]" min="0" step="0.01" placeholder="0"></td>';
        tbody.appendChild(tr);
    });
})();
