// Scripts gerais da GlowBella.

// Pede confirmação antes de ações perigosas (ex.: excluir).
// Uso: <button type="submit" data-confirmar="Excluir este produto?">Excluir</button>
document.addEventListener('click', function (evento) {
    const elemento = evento.target.closest('[data-confirmar]');

    if (elemento && !confirm(elemento.dataset.confirmar)) {
        evento.preventDefault();
    }
});
