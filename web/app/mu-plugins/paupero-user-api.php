<?php
/**
 * Plugin Name: Paupero User API
 * Description: REST endpoints per la registrazione utenti e verifica email
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    if (!empty($_GET['paupero_logout']) && is_user_logged_in()) {
        if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'paupero_logout')) {
            wp_logout();
        }
        wp_safe_redirect(home_url('/'));
        exit;
    }
});

add_action('rest_api_init', function () {
    register_rest_route('paupero/v1', '/register', [
        'methods'             => 'POST',
        'callback'            => 'paupero_register_user',
        'permission_callback' => '__return_true',
        'args'                => [
            'nome' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'cognome' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'email' => [
                'required'          => true,
                'validate_callback' => fn($v) => is_email($v),
                'sanitize_callback' => 'sanitize_email',
            ],
            'password' => [
                'required' => true,
            ],
            'confirm_password' => [
                'required' => true,
            ],
            'data_nascita' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'cellulare' => [
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'bio' => [
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
            'mazzi_giocati' => [
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
        ],
    ]);

    register_rest_route('paupero/v1', '/login', [
        'methods'             => 'POST',
        'callback'            => 'paupero_login_user',
        'permission_callback' => '__return_true',
        'args'                => [
            'email' => [
                'required'          => true,
                'validate_callback' => fn($v) => is_email($v),
                'sanitize_callback' => 'sanitize_email',
            ],
            'password' => [
                'required' => true,
            ],
            'remember' => [
                'default' => false,
            ],
        ],
    ]);

    register_rest_route('paupero/v1', '/verify-email', [
        'methods'             => 'GET',
        'callback'            => 'paupero_verify_email',
        'permission_callback' => '__return_true',
        'args'                => [
            'token' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);

    register_rest_route('paupero/v1', '/profile', [
        [
            'methods'             => 'GET',
            'callback'            => 'paupero_get_profile',
            'permission_callback' => 'is_user_logged_in',
        ],
        [
            'methods'             => 'POST',
            'callback'            => 'paupero_update_profile',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'nome'          => ['sanitize_callback' => 'sanitize_text_field'],
                'cognome'       => ['sanitize_callback' => 'sanitize_text_field'],
                'bio'           => ['sanitize_callback' => 'sanitize_textarea_field'],
                'mazzi_giocati' => ['sanitize_callback' => 'sanitize_textarea_field'],
                'data_nascita'  => ['sanitize_callback' => 'sanitize_text_field'],
                'cellulare'     => ['sanitize_callback' => 'sanitize_text_field'],
                'theme'         => ['sanitize_callback' => 'sanitize_text_field'],
            ],
        ],
    ]);

    register_rest_route('paupero/v1', '/profile/password', [
        'methods'             => 'POST',
        'callback'            => 'paupero_change_password',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'current_password'     => ['required' => true],
            'new_password'         => ['required' => true],
            'confirm_new_password' => ['required' => true],
        ],
    ]);
});

function paupero_get_profile(): WP_REST_Response {
    $user = wp_get_current_user();
    return new WP_REST_Response([
        'nome'          => $user->first_name,
        'cognome'       => $user->last_name,
        'email'         => $user->user_email,
        'bio'           => $user->description,
        'data_nascita'  => get_user_meta($user->ID, 'paupero_data_nascita', true),
        'cellulare'     => get_user_meta($user->ID, 'paupero_cellulare', true),
        'mazzi_giocati' => get_user_meta($user->ID, 'paupero_mazzi_giocati', true),
        'theme'         => get_user_meta($user->ID, 'paupero_theme', true) ?: 'dimir',
    ], 200);
}

function paupero_update_profile(WP_REST_Request $request): WP_REST_Response|WP_Error {
    $user    = wp_get_current_user();
    $user_id = $user->ID;
    $nome    = $request->get_param('nome');
    $cognome = $request->get_param('cognome');

    $update_data = ['ID' => $user_id];
    if ($nome !== null)    $update_data['first_name'] = $nome;
    if ($cognome !== null) $update_data['last_name']  = $cognome;
    if ($request->get_param('bio') !== null) {
        $update_data['description'] = $request->get_param('bio');
    }
    if ($nome !== null || $cognome !== null) {
        $first = $nome ?? $user->first_name;
        $last  = $cognome ?? $user->last_name;
        $update_data['display_name'] = trim("$first $last") ?: $user->user_login;
    }

    if (count($update_data) > 1) {
        $result = wp_update_user($update_data);
        if (is_wp_error($result)) {
            return new WP_Error('update_failed', $result->get_error_message(), ['status' => 500]);
        }
    }

    if ($request->get_param('data_nascita') !== null) {
        update_user_meta($user_id, 'paupero_data_nascita', $request->get_param('data_nascita'));
    }
    if ($request->get_param('cellulare') !== null) {
        update_user_meta($user_id, 'paupero_cellulare', $request->get_param('cellulare'));
    }
    if ($request->get_param('mazzi_giocati') !== null) {
        update_user_meta($user_id, 'paupero_mazzi_giocati', $request->get_param('mazzi_giocati'));
    }

    $valid_themes = ['dimir', 'azorius', 'boros', 'golgari', 'gruul', 'simic'];
    $theme = $request->get_param('theme');
    if ($theme !== null && in_array($theme, $valid_themes, true)) {
        update_user_meta($user_id, 'paupero_theme', $theme);
    }

    return new WP_REST_Response(['success' => true, 'message' => 'Profilo aggiornato con successo.'], 200);
}

function paupero_change_password(WP_REST_Request $request): WP_REST_Response|WP_Error {
    $user             = wp_get_current_user();
    $current_password = $request->get_param('current_password');
    $new_password     = $request->get_param('new_password');
    $confirm_password = $request->get_param('confirm_new_password');

    if (!wp_check_password($current_password, $user->user_pass, $user->ID)) {
        return new WP_Error('wrong_password', 'La password attuale non è corretta.', ['status' => 401]);
    }
    if ($new_password !== $confirm_password) {
        return new WP_Error('password_mismatch', 'Le password non corrispondono.', ['status' => 422]);
    }
    if (strlen($new_password) < 8) {
        return new WP_Error('password_too_short', 'La password deve essere di almeno 8 caratteri.', ['status' => 422]);
    }

    wp_set_password($new_password, $user->ID);
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);

    return new WP_REST_Response(['success' => true, 'message' => 'Password aggiornata con successo.'], 200);
}

function paupero_login_user(WP_REST_Request $request): WP_REST_Response|WP_Error {
    $email    = $request->get_param('email');
    $password = $request->get_param('password');
    $remember = (bool) $request->get_param('remember');

    $user = get_user_by('email', $email);
    if (!$user) {
        return new WP_Error('invalid_credentials', 'Email o password non corretti.', ['status' => 401]);
    }

    $verified = get_user_meta($user->ID, 'paupero_email_verified', true);
    if ($verified === '0') {
        return new WP_Error(
            'email_not_verified',
            'Devi prima confermare la tua email. Controlla la tua casella di posta.',
            ['status' => 403]
        );
    }

    $signed_in = wp_signon([
        'user_login'    => $user->user_login,
        'user_password' => $password,
        'remember'      => $remember,
    ], is_ssl());

    if (is_wp_error($signed_in)) {
        return new WP_Error('invalid_credentials', 'Email o password non corretti.', ['status' => 401]);
    }

    return new WP_REST_Response([
        'success'      => true,
        'redirect_url' => home_url('/'),
    ], 200);
}

function paupero_register_user(WP_REST_Request $request): WP_REST_Response|WP_Error {
    $nome             = $request->get_param('nome');
    $cognome          = $request->get_param('cognome');
    $email            = $request->get_param('email');
    $password         = $request->get_param('password');
    $confirm_password = $request->get_param('confirm_password');
    $data_nascita     = $request->get_param('data_nascita');
    $cellulare        = $request->get_param('cellulare');
    $bio              = $request->get_param('bio');
    $mazzi_giocati    = $request->get_param('mazzi_giocati');

    if ($password !== $confirm_password) {
        return new WP_Error('password_mismatch', 'Le password non corrispondono.', ['status' => 422]);
    }

    if (strlen($password) < 8) {
        return new WP_Error('password_too_short', 'La password deve essere di almeno 8 caratteri.', ['status' => 422]);
    }

    // If email exists but account is still pending, resend verification instead of erroring
    $existing_user = get_user_by('email', $email);
    if ($existing_user) {
        $verified = get_user_meta($existing_user->ID, 'paupero_email_verified', true);
        if ($verified === '1') {
            return new WP_Error('email_exists', 'Questa email è già registrata.', ['status' => 409]);
        }
        return paupero_resend_verification($existing_user->ID, $nome, $email);
    }

    $username = sanitize_user(strstr($email, '@', true), true);
    if (username_exists($username)) {
        $username = $username . '_' . wp_generate_password(4, false);
    }

    $user_id = wp_insert_user([
        'user_login'  => $username,
        'user_email'  => $email,
        'user_pass'   => $password,
        'first_name'  => $nome,
        'last_name'   => $cognome,
        'role'        => '', // no role until email verified
        'description' => $bio ?? '',
    ]);

    if (is_wp_error($user_id)) {
        return new WP_Error('registrazione_fallita', $user_id->get_error_message(), ['status' => 500]);
    }

    if ($data_nascita) {
        update_user_meta($user_id, 'paupero_data_nascita', $data_nascita);
    }
    if ($cellulare) {
        update_user_meta($user_id, 'paupero_cellulare', $cellulare);
    }
    if ($mazzi_giocati) {
        update_user_meta($user_id, 'paupero_mazzi_giocati', $mazzi_giocati);
    }
    update_user_meta($user_id, 'paupero_email_verified', '0');

    $sent = paupero_send_verification_email($user_id, $nome, $email);
    if (is_wp_error($sent)) {
        wp_delete_user($user_id);
        return $sent;
    }

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Registrazione avvenuta! Controlla la tua email per confermare l\'account.',
    ], 201);
}

function paupero_resend_verification(int $user_id, string $nome, string $email): WP_REST_Response|WP_Error {
    $sent = paupero_send_verification_email($user_id, $nome, $email);
    if (is_wp_error($sent)) {
        return $sent;
    }
    return new WP_REST_Response([
        'success' => true,
        'message' => 'Email di conferma reinviata. Controlla la tua casella di posta.',
    ], 200);
}

function paupero_send_verification_email(int $user_id, string $nome, string $email): true|WP_Error {
    $token  = bin2hex(random_bytes(32));
    $expiry = time() + DAY_IN_SECONDS;
    update_user_meta($user_id, 'paupero_verify_token', $token);
    update_user_meta($user_id, 'paupero_verify_expiry', $expiry);

    $verify_url = rest_url('paupero/v1/verify-email?token=' . $token);
    $site_name  = get_bloginfo('name');

    $subject = sprintf('Conferma la tua email su %s', $site_name);
    $message = implode("\n\n", [
        sprintf('Ciao %s,', $nome),
        sprintf('Grazie per esserti registrato su %s!', $site_name),
        "Clicca il link qui sotto per confermare la tua email (valido per 24 ore):\n" . $verify_url,
        "Se non hai richiesto questa registrazione, ignora questa email.",
        $site_name,
    ]);

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf('From: %s <%s>', $site_name, get_option('admin_email')),
    ];

    if (!wp_mail($email, $subject, $message, $headers)) {
        return new WP_Error('email_failed', 'Impossibile inviare l\'email di conferma. Riprova più tardi.', ['status' => 500]);
    }

    return true;
}

function paupero_verify_email(WP_REST_Request $request): void {
    $token = $request->get_param('token');

    $register_page = get_page_by_path('registrazione');
    $register_url  = $register_page ? get_permalink($register_page) : home_url('/');

    $users = get_users([
        'meta_key'   => 'paupero_verify_token',
        'meta_value' => $token,
        'number'     => 1,
    ]);

    if (empty($users)) {
        wp_safe_redirect(add_query_arg('reg_status', 'invalid_token', $register_url), 302);
        exit;
    }

    $user   = $users[0];
    $expiry = (int) get_user_meta($user->ID, 'paupero_verify_expiry', true);

    if (time() > $expiry) {
        wp_delete_user($user->ID);
        wp_safe_redirect(add_query_arg('reg_status', 'token_expired', $register_url), 302);
        exit;
    }

    $wp_user = new WP_User($user->ID);
    $wp_user->set_role('subscriber');
    update_user_meta($user->ID, 'paupero_email_verified', '1');
    delete_user_meta($user->ID, 'paupero_verify_token');
    delete_user_meta($user->ID, 'paupero_verify_expiry');

    wp_safe_redirect(add_query_arg('reg_status', 'verified', $register_url), 302);
    exit;
}
