document.addEventListener('DOMContentLoaded', function () {

  const classModalEl = document.getElementById('classModal');
  const formClass = document.getElementById('formClass');
  const classModalTitle = document.getElementById('classModalTitle');

  classModalEl.addEventListener('hidden.bs.modal', function () {
    classModalTitle.textContent = 'Nova Classe';
    formClass.reset();
    formClass.action = '/admin/classe';
    document.getElementById('classId').value = '';
    const alertEl = document.getElementById('classFormAlert');
    if (alertEl) {
      alertEl.classList.add('d-none');
      alertEl.textContent = '';
      alertEl.classList.remove('alert-danger', 'alert-success');
    }
  });

  document.getElementById('btnNewClass').addEventListener('click', function () {
    classModalTitle.textContent = 'Nova Classe';
    formClass.action = '/admin/classe';
    formClass.reset();
    document.getElementById('classId').value = '';
  });

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;
    const id = btn.getAttribute('data-id') || '';

    classModalTitle.textContent = 'Editar Classe';
    formClass.action = `/admin/classes/${id}/update`;
    document.getElementById('classId').value = id;

    fetch(`/admin/classes/${id}/edit`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('classNumero').value = data.classe.nome.split('ª')[0];
          document.getElementById('classDesc').value = data.classe.descricao;

          document.querySelectorAll('input[name="disciplinas[]"]').forEach(cb => cb.checked = false);
          data.disciplinas.forEach(discId => {
            const cb = document.querySelector(`input[name="disciplinas[]"][value="${discId}"]`);
            if (cb) cb.checked = true;
          });
        }
      });
  });
});