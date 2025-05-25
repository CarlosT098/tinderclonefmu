<?php

// Função para iniciar o buffer de saída e interceptar o conteúdo
function start_output_handler() {
    ob_start('handle_output');
}

// Função que manipula o conteúdo do buffer antes de enviá-lo para o navegador
function handle_output($buffer) {
    // Verifica se o conteúdo gerado é JSON
    if (is_json($buffer)) {
        // Modifica o JSON para evitar a codificação de caracteres especiais
        $buffer = json_encode(json_decode($buffer), JSON_UNESCAPED_UNICODE);
    }
    
    // Retorna o conteúdo modificado
    return $buffer;
}

// Função para verificar se o conteúdo é um JSON válido
function is_json($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

// Função para garantir que a saída seja finalizada e enviada
function end_output_handler() {
    ob_end_flush();
}
