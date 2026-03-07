<?php
/**
 * Ofero Registry API
 *
 * Simple API for verifying domain licenses in the Ofero Network
 *
 * Deploy this to: https://licence.ofero.network/
 *
 * Endpoints:
 * - GET /api/v1/registry/verify/{domain}
 * - POST /api/v1/registry/register (admin only)
 * - GET /api/v1/registry/list (admin only)
 *
 * @version 1.0.0
 */

// Configuration
define('REGISTRY_FILE', __DIR__ . '/registry.json');
define('ADMIN_TOKEN', 'your-secret-admin-token-here'); // Change this!

// Parse request
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($request_uri, PHP_URL_PATH);

// If accessing root path and it's a browser (not API client), show landing page
if (($path === '/' || $path === '/index.php') && is_browser_request()) {
    readfile(__DIR__ . '/landing.html');
    exit;
}

// For API requests, set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Route the request
if (preg_match('#^/api/v1/registry/verify/([a-zA-Z0-9\.-]+)$#', $path, $matches)) {
    handle_verify($matches[1]);
} elseif ($path === '/api/v1/registry/register' && $request_method === 'POST') {
    handle_register();
} elseif ($path === '/api/v1/registry/list' && $request_method === 'GET') {
    handle_list();
} elseif ($path === '/api/v1/registry/health') {
    handle_health();
} else {
    http_response_code(404);
    echo json_encode([
        'error' => 'not_found',
        'message' => 'Endpoint not found',
        'available_endpoints' => [
            'GET /api/v1/registry/verify/{domain}',
            'POST /api/v1/registry/register (requires auth)',
            'GET /api/v1/registry/list (requires auth)',
            'GET /api/v1/registry/health'
        ]
    ]);
}

/**
 * Check if request is from a browser (not API client)
 */
function is_browser_request() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    // If Accept header contains text/html, it's likely a browser
    return strpos($accept, 'text/html') !== false;
}

/**
 * Handle domain verification
 */
function handle_verify($domain) {
    // Normalize domain
    $domain = strtolower($domain);
    $domain = preg_replace('/^www\./', '', $domain);

    // Load registry
    $registry = load_registry();

    // Check if domain exists
    if (!isset($registry[$domain])) {
        http_response_code(404);
        echo json_encode([
            'valid' => false,
            'error' => 'not_registered',
            'message' => 'Domain not found in Ofero Network registry',
            'domain' => $domain
        ]);
        return;
    }

    $entry = $registry[$domain];

    // Check if license is expired
    if (!$entry['license']['lifetime'] && isset($entry['license']['expires_at'])) {
        $expires = strtotime($entry['license']['expires_at']);
        if ($expires < time()) {
            http_response_code(403);
            echo json_encode([
                'valid' => false,
                'error' => 'license_expired',
                'message' => 'License expired on ' . $entry['license']['expires_at'],
                'domain' => $domain,
                'license' => $entry['license']
            ]);
            return;
        }
    }

    // Valid license
    http_response_code(200);
    echo json_encode([
        'valid' => true,
        'domain' => $domain,
        'license' => $entry['license'],
        'organization' => $entry['organization'],
        'registered_at' => $entry['registered_at'],
        'last_verified' => date('c')
    ]);
}

/**
 * Handle domain registration (admin only)
 */
function handle_register() {
    // Verify admin token
    if (!verify_admin()) {
        unauthorized();
        return;
    }

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!isset($input['domain']) || !isset($input['organization'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'validation_error',
            'message' => 'Missing required fields: domain, organization'
        ]);
        return;
    }

    // Normalize domain
    $domain = strtolower($input['domain']);
    $domain = preg_replace('/^www\./', '', $domain);

    // Load registry
    $registry = load_registry();

    // Prepare entry
    $entry = [
        'domain' => $domain,
        'license' => [
            'tier' => $input['tier'] ?? 'basic',
            'lifetime' => $input['lifetime'] ?? false,
            'expires_at' => $input['expires_at'] ?? null
        ],
        'organization' => [
            'name' => $input['organization']['name'] ?? $domain,
            'email' => $input['organization']['email'] ?? null
        ],
        'registered_at' => date('c'),
        'updated_at' => date('c')
    ];

    // Save to registry
    $registry[$domain] = $entry;
    save_registry($registry);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Domain registered successfully',
        'domain' => $domain,
        'entry' => $entry
    ]);
}

/**
 * Handle registry list (admin only)
 */
function handle_list() {
    // Verify admin token
    if (!verify_admin()) {
        unauthorized();
        return;
    }

    $registry = load_registry();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($registry),
        'domains' => array_values($registry)
    ]);
}

/**
 * Health check endpoint
 */
function handle_health() {
    http_response_code(200);
    echo json_encode([
        'status' => 'healthy',
        'service' => 'Ofero Registry API',
        'version' => '1.0.0',
        'timestamp' => date('c')
    ]);
}

/**
 * Verify admin authentication
 */
function verify_admin() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? '';

    if (preg_match('/^Bearer (.+)$/', $auth, $matches)) {
        return $matches[1] === ADMIN_TOKEN;
    }

    return false;
}

/**
 * Send unauthorized response
 */
function unauthorized() {
    http_response_code(401);
    echo json_encode([
        'error' => 'unauthorized',
        'message' => 'Invalid or missing admin token'
    ]);
}

/**
 * Load registry from file
 */
function load_registry() {
    if (!file_exists(REGISTRY_FILE)) {
        return [];
    }

    $content = file_get_contents(REGISTRY_FILE);
    $data = json_decode($content, true);

    return $data ?? [];
}

/**
 * Save registry to file
 */
function save_registry($registry) {
    $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(REGISTRY_FILE, $json);
}
