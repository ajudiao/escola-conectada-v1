/* ===========================================
   Escola Conectada - MED Angola
   Professor Specific JavaScript
   =========================================== */

/**
 * Controlador de Notas
 */
const MiniPautaController = {
  endpoint: window.NOTAS_ENDPOINT || '',

  init: function() {
    this.bindEvents();
  },

  bindEvents: function() {
    const self = this;
    const carregarBtn = document.getElementById('btnCarregarAlunos');
    const form = document.getElementById('formNotas');
    const printBtn = document.getElementById('btnImprimir');

    if (carregarBtn) {
      carregarBtn.addEventListener('click', function() {
        self.carregarAlunos();
      });
    }

    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        self.guardarNotas();
      });
    }

    if (printBtn) {
      printBtn.addEventListener('click', function() {
        window.print();
      });
    }

    document.addEventListener('input', function(e) {
      if (e.target.classList.contains('nota-input')) {
        self.calcularMediasLinha(e.target);
      }
    });
  },

  getEndpoint: function() {
    const form = document.getElementById('formNotas');
    if (form?.dataset.endpoint) {
      return form.dataset.endpoint;
    }
    if (form?.action) {
      return form.action;
    }
    return this.endpoint || window.location.href;
  },

  carregarAlunos: function() {
    const trimestre = document.getElementById('selectTrimestre')?.value;
    const turma = document.getElementById('selectTurma')?.value;
    const anoLectivo = document.getElementById('selectAnoLectivo')?.value;

    if (!trimestre || !turma || !anoLectivo) {
      this.mostrarNotificacao('Selecione trimestre, turma e ano lectivo antes de carregar.', 'warning');
      return;
    }

    const payload = {
      action: 'buscarNotas',
      trimestre: trimestre,
      turma: turma,
      anoLectivo: anoLectivo
    };

    this.postData(payload)
      .then(response => {
        const alunos = response.alunos || this.getAlunosExemplo(turma);
        const notas = response.notas || {};
        this.preencherTabela(alunos, notas);
        this.mostrarNotificacao(response.message || 'Dados carregados com sucesso.', 'success');
      })
      .catch(error => {
        this.mostrarNotificacao(error.message || 'Erro ao carregar os alunos.', 'danger');
      });
  },

  preencherTabela: function(alunos, notas) {
    const tbody = document.getElementById('miniPautaBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!Array.isArray(alunos) || alunos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="17" class="text-center text-muted py-4">Nenhum aluno encontrado para esta seleção.</td></tr>';
      return;
    }

    alunos.forEach(aluno => {
      const row = this.gerarLinhaAluno(aluno, notas[aluno.numero] || {});
      tbody.appendChild(row);
    });
  },

  gerarLinhaAluno: function(aluno, notasObj) {
    const tr = document.createElement('tr');
    tr.dataset.alunoId = aluno.numero;

    tr.innerHTML = `
      <td class="col-numero text-center">${aluno.numero}</td>
      <td class="col-nome">${aluno.nome}</td>
      <td class="col-sexo text-center">${aluno.sexo || ''}</td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="mac1" min="0" max="20" value="${notasObj.mac1 || ''}" /></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="npp1" min="0" max="20" value="${notasObj.npp1 || ''}" /></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="npt1" min="0" max="20" value="${notasObj.npt1 || ''}" /></td>
      <td class="text-center col-media"><span class="computed-value mt1">-</span></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="mac2" min="0" max="20" value="${notasObj.mac2 || ''}" /></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="npp2" min="0" max="20" value="${notasObj.npp2 || ''}" /></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="npt2" min="0" max="20" value="${notasObj.npt2 || ''}" /></td>
      <td class="text-center col-media"><span class="computed-value mt2">-</span></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="mac3" min="0" max="20" value="${notasObj.mac3 || ''}" /></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="npp3" min="0" max="20" value="${notasObj.npp3 || ''}" /></td>
      <td><input type="number" class="form-control form-control-sm nota-input" data-tipo="npt3" min="0" max="20" value="${notasObj.npt3 || ''}" /></td>
      <td class="text-center col-media"><span class="computed-value mt3">-</span></td>
      <td class="text-center col-mf"><span class="computed-value mf">-</span></td>
      <td class="text-center col-estado"><span class="text-muted">Pendente</span></td>
    `;

    const inputs = tr.querySelectorAll('.nota-input');
    inputs.forEach(input => {
      input.addEventListener('input', () => this.calcularMediasLinha(input));
    });

    if (inputs.length) {
      this.calcularMediasLinha(inputs[0]);
    }

    return tr;
  },

  calcularMediasLinha: function(inputOrRow) {
    const row = inputOrRow.closest ? inputOrRow.closest('tr') : inputOrRow;
    if (!row) return;

    const getNum = function(tipo) {
      const input = row.querySelector(`input[data-tipo="${tipo}"]`);
      return input && input.value !== '' ? parseInt(input.value, 10) : null;
    };

    const mt1 = this.calcularMediaTrimestral(getNum('mac1'), getNum('npp1'), getNum('npt1'));
    const mt2 = this.calcularMediaTrimestral(getNum('mac2'), getNum('npp2'), getNum('npt2'));
    const mt3 = this.calcularMediaTrimestral(getNum('mac3'), getNum('npp3'), getNum('npt3'));

    this.atualizarMedia(row, 'mt1', mt1);
    this.atualizarMedia(row, 'mt2', mt2);
    this.atualizarMedia(row, 'mt3', mt3);
    this.atualizarMedia(row, 'mf', this.calcularMediaFinal(mt1, mt2, mt3));

    const allInputs = Array.from(row.querySelectorAll('.nota-input'));
    const anyEmpty = allInputs.some(input => input.value === '');
    const estadoCell = row.querySelector('.col-estado');
    if (!estadoCell) return;

    if (anyEmpty) {
      estadoCell.innerHTML = '<span class="text-muted">Pendente</span>';
      return;
    }

    const mfText = row.querySelector('.mf')?.textContent;
    const mf = mfText ? parseFloat(mfText) : null;
    if (mf === null || isNaN(mf)) {
      estadoCell.innerHTML = '<span class="text-muted">Pendente</span>';
    } else if (mf >= 10) {
      estadoCell.innerHTML = '<span class="text-success">Aprovado</span>';
    } else {
      estadoCell.innerHTML = '<span class="text-danger">Reprovado</span>';
    }
  },

  guardarNotas: function() {
    const trimestre = document.getElementById('selectTrimestre')?.value;
    const turma = document.getElementById('selectTurma')?.value;
    const anoLectivo = document.getElementById('selectAnoLectivo')?.value;

    if (!trimestre || !turma || !anoLectivo) {
      this.mostrarNotificacao('Preencha trimestre, turma e ano lectivo antes de submeter.', 'warning');
      return;
    }

    const rows = Array.from(document.querySelectorAll('#miniPautaBody tr[data-aluno-id]'));
    if (!rows.length) {
      this.mostrarNotificacao('Carregue a lista de alunos antes de submeter.', 'warning');
      return;
    }

    const alunos = rows.map(row => {
      const data = {
        alunoId: row.dataset.alunoId,
        nome: row.querySelector('.col-nome')?.textContent.trim() || '',
        sexo: row.querySelector('.col-sexo')?.textContent.trim() || '',
        notas: {}
      };

      row.querySelectorAll('.nota-input').forEach(input => {
        data.notas[input.dataset.tipo] = input.value !== '' ? parseInt(input.value, 10) : null;
      });

      data.mt1 = row.querySelector('.mt1')?.textContent || '-';
      data.mt2 = row.querySelector('.mt2')?.textContent || '-';
      data.mt3 = row.querySelector('.mt3')?.textContent || '-';
      data.mf = row.querySelector('.mf')?.textContent || '-';
      return data;
    });

    const payload = {
      action: 'salvarNotas',
      trimestre: trimestre,
      turma: turma,
      anoLectivo: anoLectivo,
      alunos: alunos
    };

    this.postData(payload)
      .then(response => {
        this.mostrarNotificacao(response.message || 'Notas submetidas com sucesso.', 'success');
      })
      .catch(error => {
        this.mostrarNotificacao(error.message || 'Erro ao submeter as notas.', 'danger');
      });
  },

  getAlunosExemplo: function(turma) {
    const nomes = ['Ana Pereira','Pedro Santos','João Costa','Maria Silva','Luís Gomes','Rita Alves','Carlos Mendes','Sofia Ribeiro','Miguel Sousa','Paula Rocha','Bruno Dias','Inês Matos'];
    return nomes.map((nome, index) => ({ numero: index + 1, nome: nome + ' - ' + turma, sexo: index % 2 === 0 ? 'F' : 'M' }));
  },

  postData: function(payload) {
    const endpoint = this.getEndpoint();
    const formData = new FormData();
    formData.append('payload', JSON.stringify(payload));

    return fetch(endpoint, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.text();
      })
      .then(text => {
        if (!text) {
          return { message: 'Resposta vazia do servidor.' };
        }
        try {
          return JSON.parse(text);
        } catch (e) {
          return { message: text };
        }
      });
  },

  calcularMediaTrimestral: function(mac, npp, npt) {
    const validValues = [mac, npp, npt].filter(value => value !== null && !isNaN(value));
    return validValues.length > 0 ? Math.round(validValues.reduce((sum, value) => sum + value, 0) / validValues.length) : null;
  },

  calcularMediaFinal: function(mt1, mt2, mt3) {
    const validValues = [mt1, mt2, mt3].filter(value => value !== null && !isNaN(value));
    return validValues.length > 0 ? Math.round(validValues.reduce((sum, value) => sum + value, 0) / validValues.length) : null;
  },

  getCorNota: function(valor) {
    return valor >= 16 ? 'bg-success' : valor >= 10 ? 'bg-warning' : 'bg-danger';
  },

  mostrarNotificacao: function(mensagem, tipo) {
    const status = document.getElementById('notasStatus');
    if (!status) {
      if (window.Utils && Utils.mostrarNotificacao) {
        return Utils.mostrarNotificacao(mensagem, tipo);
      }
      return alert(mensagem);
    }
    status.textContent = mensagem;
    status.className = `alert alert-${tipo === 'warning' ? 'warning' : tipo === 'danger' ? 'danger' : 'success'} mt-3`;
    status.classList.remove('d-none');
    setTimeout(() => status.classList.add('d-none'), 7000);
  }
};

/**
 * Controlador de Faltas
 */
const FaltasController = {
  init: function() {
    this.bindEvents();
  },
  
  bindEvents: function() {
    const self = this;
    
    // Turma change
    const turmaSelect = document.getElementById('selectTurmaFaltas');
    if (turmaSelect) {
      turmaSelect.addEventListener('change', function() {
        self.carregarAlunosFaltas(this.value);
      });
    }
    
    // Date range
    const mesSelect = document.getElementById('selectMes');
    if (mesSelect) {
      mesSelect.addEventListener('change', function() {
        self.atualizarDatas();
      });
    }
    
    // Save button
    const saveBtn = document.getElementById('btnGuardarFaltas');
    if (saveBtn) {
      saveBtn.addEventListener('click', function() {
        self.guardarFaltas();
      });
    }
  },
  
  carregarAlunosFaltas: function(turmaKey) {
    const tbody = document.getElementById('faltasBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!turmaKey) {
      tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">Selecione uma turma</td></tr>';
      return;
    }
    
    const DadosFicticios = { alunos: { 'turma1': [{ numero: 1, nome: 'João', sexo: 'M' }, { numero: 2, nome: 'Maria', sexo: 'F' }] } };
    const alunos = DadosFicticios.alunos[turmaKey] || [];
    
    alunos.forEach(function(aluno) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${aluno.numero}</td>
        <td class="text-start">${aluno.nome}</td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="1"></td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="2"></td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="3"></td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="4"></td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="5"></td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="6"></td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="7"></td>
        <td><input type="checkbox" class="faltas-checkbox" data-dia="8"></td>
        <td><span class="badge badge-danger total-faltas">0</span></td>
      `;
      
      // Add event listeners for checkboxes
      tr.querySelectorAll('.faltas-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
          FaltasController.atualizarTotalFaltas(tr);
        });
      });
      
      tbody.appendChild(tr);
    });
  },
  
  atualizarTotalFaltas: function(row) {
    const total = row.querySelectorAll('.faltas-checkbox:checked').length;
    const badge = row.querySelector('.total-faltas');
    if (badge) {
      badge.textContent = total;
      badge.className = 'badge ' + (total > 0 ? 'badge-danger' : 'badge-success');
    }
  },
  
  atualizarDatas: function() {
    // Update date headers based on selected month
  },
  
  guardarFaltas: function() {
    alert('Faltas registadas com sucesso!');
  }
};

/**
 * Inicializar quando DOM estiver pronto
 */
document.addEventListener('DOMContentLoaded', function() {
  MiniPautaController.init();
  FaltasController.init();
});

// Exportar para uso global
window.MiniPautaController = MiniPautaController;
window.FaltasController = FaltasController;
