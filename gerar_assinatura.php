<?php
// --- CONFIGURAÇÕES E BANCO DE DADOS ---

// Estrutura de dados das empresas e tipos de assinatura (em ordem alfabética)
$empresas = [
    'afagu' => [
        'nome' => 'Afagu',
        'logo' => 'afagu.png',
        'dominioEmail' => 'grupogda.com.br',
        'corFonte' => '#246cb2', // Cor azul (mesma do Grupo Anjo da Guarda)
        'tiposAssinatura' => [
            'padrao' => [
                'nome' => 'Padrão',
                'imagemBase' => 'afagu.png'
            ]
        ]
    ],
    'cemiterio-anjo-guarda' => [
        'nome' => 'Cemitério Anjo da Guarda',
        'logo' => 'cepag.png',
        'dominioEmail' => 'grupogda.com.br',
        'corFonte' => '#14427d', // Cor teal-azul escuro
        'tiposAssinatura' => [
            'padrao' => [
                'nome' => 'Padrão',
                'imagemBase' => 'cepag.png'
            ]
        ]
    ],
    'grupo-anjo-guarda' => [
        'nome' => 'Grupo Anjo da Guarda',
        'logo' => 'logo-grupo.png',
        'dominioEmail' => 'grupogda.com.br',
        'corFonte' => '#246cb2', // Cor azul para as fontes
        'tiposAssinatura' => [
            'padrao' => [
                'nome' => 'Padrão',
                'imagemBase' => 'IMAGEM_BASE.png'
            ]
        ]
    ],
    'paz-eterna' => [
        'nome' => 'Paz Eterna',
        'logo' => 'pazeterna.png',
        'dominioEmail' => 'grupogda.com.br',
        'corFonte' => '#3e4095', // Cor azul escuro
        'tiposAssinatura' => [
            'padrao' => [
                'nome' => 'Padrão',
                'imagemBase' => 'pazeterna.png'
            ]
        ]
    ]
];

// Caminho para o arquivo da fonte.
// CERTIFIQUE-SE QUE ESSES ARQUIVOS ESTÃO NA MESMA PASTA!
$arquivoFonte = 'Agrandir-Narrow.otf'; // Coloque o nome exato do seu arquivo de fonte.

// --- PROCESSAMENTO DOS DADOS ---

// Pega os dados enviados pelo formulário
$empresaKey = $_POST['empresa'] ?? '';
$tipoAssinaturaKey = $_POST['tipo-assinatura'] ?? '';
$nome = strtoupper($_POST['nome']); // Deixa o nome em maiúsculo, como no exemplo
$cargo = strtoupper($_POST['cargo']); // Deixa o cargo em maiúsculo
$telefone = $_POST['telefone'];
$email = $_POST['email'];

// Validação
if (!isset($empresas[$empresaKey])) {
    die('Empresa não encontrada.');
}

$empresa = $empresas[$empresaKey];

if (!isset($empresa['tiposAssinatura'][$tipoAssinaturaKey])) {
    die('Tipo de assinatura não encontrado.');
}

$tipoAssinatura = $empresa['tiposAssinatura'][$tipoAssinaturaKey];

// Pega a imagem base e cor da fonte correspondentes
$imagemBase = $tipoAssinatura['imagemBase'];
$corFonteHex = $empresa['corFonte'];

// Função para converter cor hexadecimal para RGB
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return [$r, $g, $b];
}

// --- GERAÇÃO DA IMAGEM ---

// Define que o arquivo gerado será uma imagem PNG
header('Content-Type: image/png');

// Carrega a imagem base
$img = imagecreatefrompng($imagemBase);

// Converte a cor hexadecimal para RGB e aloca a cor
$rgb = hex2rgb($corFonteHex);
$corTexto = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);

// Função para quebrar nome longo em múltiplas linhas
function quebrarNomeLongo($fonte, $tamanhoFonte, $nome, $maxWidth) {
    $palavras = explode(' ', $nome);
    $linhas = [];
    $linhaAtual = '';
    
    foreach ($palavras as $palavra) {
        $testeLinha = $linhaAtual ? $linhaAtual . ' ' . $palavra : $palavra;
        $bbox = imagettfbbox($tamanhoFonte, 0, $fonte, $testeLinha);
        $largura = $bbox[4] - $bbox[0];
        
        if ($largura <= $maxWidth || empty($linhaAtual)) {
            $linhaAtual = $testeLinha;
        } else {
            $linhas[] = $linhaAtual;
            $linhaAtual = $palavra;
        }
    }
    
    if (!empty($linhaAtual)) {
        $linhas[] = $linhaAtual;
    }
    
    return empty($linhas) ? [$nome] : $linhas;
}

// Coordenadas e tamanhos (eixo X, eixo Y)
// Estes valores podem precisar de pequenos ajustes para o alinhamento perfeito.

// Escreve o NOME (com quebra de linha se necessário)
$linhasNome = quebrarNomeLongo($arquivoFonte, 30, $nome, 400);
$yNome = 80;
foreach ($linhasNome as $linha) {
    imagettftext($img, 30, 0, 40, $yNome, $corTexto, $arquivoFonte, $linha);
    $yNome += 35; // Espaçamento entre linhas
}

// Escreve o CARGO (ajustado para aparecer abaixo do nome)
$yCargo = 80 + (count($linhasNome) * 35) + 5;
imagettftext($img, 15, 0, 42, $yCargo, $corTexto, $arquivoFonte, $cargo);

// Escreve o TELEFONE (abaixo do cargo)
$yTelefone = $yCargo + 20;
imagettftext($img, 15, 0, 42, $yTelefone, $corTexto, $arquivoFonte, $telefone);

// Escreve o E-MAIL (abaixo do telefone)
$yEmail = $yTelefone + 18;
imagettftext($img, 15, 0, 42, $yEmail, $corTexto, $arquivoFonte, $email);


// Gera a imagem PNG final e a exibe no navegador
imagepng($img);

// Libera a memória usada pela imagem
imagedestroy($img);
?>