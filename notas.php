<?php
// Arquivo temporário para testar o POST das notas
// Salve como /notas.php na raiz do projeto

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$data = json_decode($_POST['payload'] ?? '{}', true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

error_log('Dados recebidos: ' . print_r($data, true));

switch ($data['action'] ?? '') {
    case 'buscarNotas':
        // Simular busca de alunos e notas
        $alunos = [
            ['numero' => 1, 'nome' => 'João Silva', 'sexo' => 'M'],
            ['numero' => 2, 'nome' => 'Maria Santos', 'sexo' => 'F'],
            ['numero' => 3, 'nome' => 'Pedro Costa', 'sexo' => 'M'],
        ];

        $notas = [
            1 => ['mac1' => 15, 'npp1' => 16, 'npt1' => 17], // notas existentes
            2 => [], // sem notas
            3 => ['mac2' => 14, 'npp2' => 15], // notas parciais
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Dados carregados com sucesso',
            'alunos' => $alunos,
            'notas' => $notas
        ]);
        break;

    case 'salvarNotas':
        // Simular salvamento
        echo json_encode([
            'success' => true,
            'message' => 'Notas salvas com sucesso',
            'saved' => count($data['alunos'] ?? [])
        ]);
        break;

    default:
        echo json_encode(['error' => 'Ação não reconhecida']);
}

?></content>
<parameter name="filePath">/opt/lampp/htdocs/escola-conectada/notas.php