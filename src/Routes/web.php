<?php
global $router;

// Rotas de autenticação e usuário
$router->post(path: '/api/register', action: 'UserController@register');  // Registra um novo usuário
$router->post(path: '/api/login', action: 'UserController@login');        // Realiza login e retorna um token JWT
$router->post(path: '/api/logout', action: 'UserController@logout');      // Realiza o logout do usuário (remove o token)
$router->post(path: '/api/recover-password', action: 'UserController@recoverPassword');  // Inicia o processo de recuperação de senha
$router->post(path: '/api/reset-password', action: 'UserController@resetPassword');      // Reseta a senha do usuário após o link de recuperação
$router->get(path: '/api/profile', action: 'UserController@profile');    // Retorna os dados do perfil do usuário
$router->patch(path: '/api/profile', action: 'UserController@updateProfile');  // Atualiza os dados do perfil do usuário
$router->get(path: '/api/check-auth', action: 'UserController@checkAuth');  // Verifica se o usuário está autenticado
$router->get(path: '/api/verify-token', action: 'UserController@verifyToken');  // Verifica a validade do token JWT
$router->post(path: '/api/change-password', action: 'UserController@changePassword');  // Altera a senha do usuário
$router->post(path: '/api/send-verification-email', action: 'UserController@sendVerificationEmail');  // Envia email para verificar o email do usuário
$router->get(path: '/api/verify-email/{token}', action: 'UserController@verifyEmail');  // Verifica o código de email enviado para o usuário após o registro
$router->post(path: '/api/resend-verification', action: 'UserController@resendVerification');  // Reenvia o código de verificação de email
$router->get(path: '/api/account-status', action: 'UserController@accountStatus');  // Retorna o status da conta (ativa ou banida)
$router->post(path: '/api/img-change/', action: 'UserController@updateProfilePicture');  // Altera a imagem do usuario


// Rotas de curtidas e matches
$router->post(path: '/api/like', action: 'LikeController@likeUser');  // Curtir usuário
$router->get(path: '/api/user-matches', action: 'LikeController@listMatches');  // Lista de matches
$router->get(path: '/api/to-like-list', action: 'LikeController@likeList');  // Lista de usuários para curtir

// Rota temporária para simular login com sessão (para testes)
$router->get(path: '/api/set-session', action: function () {
    session_start();
    $_SESSION['user_id'] = 1;
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'user_id' => 1]);
    exit;  // Certifique-se de que a execução do código seja interrompida após a resposta
});

// Rota de teste
$router->get('/api/test', function () {
    echo json_encode("Test route is working!");
});

// Rota de status da API
$router->get('/api/', function() {
    echo json_encode(["message" => "API is working"]);
});
