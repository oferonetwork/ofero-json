<?php
/**
 * Ofero.json Generator - Standalone PHP Editor
 *
 * A complete, single-file solution for generating and managing ofero.json files.
 * Features: Password protection, theming, full editor, validation, and auto-save.
 *
 * @version 1.0.0
 * @author Ofero.me
 * @license MIT
 */

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session
session_start();

// Configuration
define('CONFIG_PREFIX', 'oferoconfig-');
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

/**
 * Main Application Class
 */
class OferoGenerator {
    private $configFile;
    private $config;
    private $oferoJsonPath;
    private $errors = [];
    private $messages = [];

    public function __construct() {
        $this->findOrCreateConfig();
        $this->loadConfig();
        $this->oferoJsonPath = dirname(__FILE__) . '/' . $this->config['settings']['outputPath'];
    }

    /**
     * Find existing config or create new one
     */
    private function findOrCreateConfig() {
        $files = glob(dirname(__FILE__) . '/' . CONFIG_PREFIX . '*.json');

        if (!empty($files)) {
            $this->configFile = $files[0];
        } else {
            // Create new config with random suffix
            $randomSuffix = bin2hex(random_bytes(8));
            $this->configFile = dirname(__FILE__) . '/' . CONFIG_PREFIX . $randomSuffix . '.json';

            // Copy template
            $template = file_get_contents(dirname(__FILE__) . '/oferoconfig-TEMPLATE.json');
            file_put_contents($this->configFile, $template);
        }
    }

    /**
     * Load configuration
     */
    private function loadConfig() {
        $this->config = json_decode(file_get_contents($this->configFile), true);
    }

    /**
     * Save configuration
     */
    private function saveConfig() {
        file_put_contents($this->configFile, json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Check if password is set
     */
    public function isPasswordSet() {
        return !empty($this->config['auth']['passwordHash']);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        if (!isset($_SESSION['ofero_authenticated'])) {
            return false;
        }

        if (time() - $_SESSION['ofero_last_activity'] > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }

        $_SESSION['ofero_last_activity'] = time();
        return true;
    }

    /**
     * Check if account is locked
     */
    public function isLocked() {
        if (empty($this->config['auth']['lockedUntil'])) {
            return false;
        }

        if (time() > strtotime($this->config['auth']['lockedUntil'])) {
            $this->config['auth']['lockedUntil'] = null;
            $this->config['auth']['loginAttempts'] = 0;
            $this->saveConfig();
            return false;
        }

        return true;
    }

    /**
     * Set initial password
     */
    public function setPassword($password, $confirmPassword) {
        if (strlen($password) < 8) {
            $this->errors[] = 'Password must be at least 8 characters long.';
            return false;
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = 'Passwords do not match.';
            return false;
        }

        $this->config['auth']['passwordHash'] = password_hash($password, PASSWORD_BCRYPT);
        $this->config['auth']['createdAt'] = date('c');
        $this->saveConfig();

        $this->messages[] = 'Password set successfully. Please log in.';
        return true;
    }

    /**
     * Verify password and login
     */
    public function login($password, $remember = false) {
        if ($this->isLocked()) {
            $this->errors[] = 'Account is locked. Please try again later.';
            return false;
        }

        if (!password_verify($password, $this->config['auth']['passwordHash'])) {
            $this->config['auth']['loginAttempts']++;

            if ($this->config['auth']['loginAttempts'] >= MAX_LOGIN_ATTEMPTS) {
                $this->config['auth']['lockedUntil'] = date('c', time() + LOCKOUT_TIME);
                $this->errors[] = 'Too many failed attempts. Account locked for 15 minutes.';
            } else {
                $remaining = MAX_LOGIN_ATTEMPTS - $this->config['auth']['loginAttempts'];
                $this->errors[] = "Invalid password. {$remaining} attempts remaining.";
            }

            $this->saveConfig();
            return false;
        }

        // Successful login
        $this->config['auth']['loginAttempts'] = 0;
        $this->config['auth']['lockedUntil'] = null;
        $this->config['auth']['lastLogin'] = date('c');
        $this->saveConfig();

        $_SESSION['ofero_authenticated'] = true;
        $_SESSION['ofero_last_activity'] = time();

        if ($remember) {
            session_set_cookie_params(604800); // 7 days
        }

        return true;
    }

    /**
     * Logout
     */
    public function logout() {
        unset($_SESSION['ofero_authenticated']);
        unset($_SESSION['ofero_last_activity']);
        session_destroy();
    }

    /**
     * Change password
     */
    public function changePassword($currentPassword, $newPassword, $confirmPassword) {
        if (!password_verify($currentPassword, $this->config['auth']['passwordHash'])) {
            $this->errors[] = 'Current password is incorrect.';
            return false;
        }

        if (strlen($newPassword) < 8) {
            $this->errors[] = 'New password must be at least 8 characters long.';
            return false;
        }

        if ($newPassword !== $confirmPassword) {
            $this->errors[] = 'New passwords do not match.';
            return false;
        }

        $this->config['auth']['passwordHash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->saveConfig();

        $this->messages[] = 'Password changed successfully.';
        return true;
    }

    /**
     * Update theme settings
     */
    public function updateTheme($theme) {
        $allowedKeys = ['textColor', 'backgroundColor', 'backgroundSecondary', 'borderColor', 'accentColor', 'successColor', 'errorColor', 'warningColor'];

        foreach ($allowedKeys as $key) {
            if (isset($theme[$key]) && preg_match('/^#[0-9A-Fa-f]{6}$/', $theme[$key])) {
                $this->config['theme'][$key] = $theme[$key];
            }
        }

        $this->saveConfig();
        $this->messages[] = 'Theme updated successfully.';
        return true;
    }

    /**
     * Load ofero.json data
     */
    public function loadOferoJson() {
        if (file_exists($this->oferoJsonPath)) {
            return json_decode(file_get_contents($this->oferoJsonPath), true);
        }

        return $this->getDefaultOferoJson();
    }

    /**
     * Save ofero.json data
     */
    public function saveOferoJson($data) {
        // Ensure directory exists
        $dir = dirname($this->oferoJsonPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Backup existing file
        if ($this->config['settings']['backupEnabled'] && file_exists($this->oferoJsonPath)) {
            $backupPath = $this->oferoJsonPath . '.backup.' . date('Y-m-d-H-i-s');
            copy($this->oferoJsonPath, $backupPath);
        }

        // Update metadata
        $data['metadata']['lastUpdated'] = date('c');
        if (empty($data['metadata']['createdAt'])) {
            $data['metadata']['createdAt'] = date('c');
        }

        // Save file
        $result = file_put_contents(
            $this->oferoJsonPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        if ($result !== false) {
            $this->messages[] = 'ofero.json saved successfully.';
            return true;
        }

        $this->errors[] = 'Failed to save ofero.json. Check file permissions.';
        return false;
    }

    /**
     * Get default ofero.json structure
     */
    private function getDefaultOferoJson() {
        return [
            'language' => 'en',
            'domain' => $_SERVER['HTTP_HOST'] ?? 'example.com',
            'canonicalUrl' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'example.com') . '/.well-known/ofero.json',
            'metadata' => [
                'version' => '1.0.0',
                'schemaVersion' => 'ofero-metadata-1.0',
                'lastUpdated' => date('c'),
                'createdAt' => date('c')
            ],
            'organization' => [
                'legalName' => '',
                'brandName' => '',
                'entityType' => 'company',
                'legalForm' => '',
                'description' => '',
                'website' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'example.com'),
                'contactEmail' => '',
                'contactPhone' => '',
                'identifiers' => [
                    'global' => [],
                    'primaryIncorporation' => [
                        'country' => '',
                        'registrationNumber' => '',
                        'taxId' => '',
                        'vatNumber' => ''
                    ],
                    'perCountry' => []
                ]
            ],
            'locations' => [],
            'banking' => [],
            'wallets' => [],
            'brandAssets' => [],
            'catalog' => [],
            'communications' => [
                'social' => [],
                'support' => []
            ]
        ];
    }

    /**
     * Validate ofero.json data
     */
    public function validateOferoJson($data) {
        $errors = [];

        // Required fields
        if (empty($data['language'])) {
            $errors[] = 'Language is required.';
        }
        if (empty($data['domain'])) {
            $errors[] = 'Domain is required.';
        }
        if (empty($data['canonicalUrl'])) {
            $errors[] = 'Canonical URL is required.';
        }
        if (empty($data['metadata']['version'])) {
            $errors[] = 'Metadata version is required.';
        }
        if (empty($data['organization']['legalName'])) {
            $errors[] = 'Organization legal name is required.';
        }
        if (empty($data['organization']['entityType'])) {
            $errors[] = 'Organization entity type is required.';
        }

        return $errors;
    }

    /**
     * Get config
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get messages
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Generate CSRF token
     */
    public function getCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Initialize application
$app = new OferoGenerator();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token for all POST requests
    if (!$app->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $app->getErrors()[] = 'Invalid security token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'setup_password':
                if ($app->setPassword($_POST['password'] ?? '', $_POST['confirm_password'] ?? '')) {
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                }
                break;

            case 'login':
                if ($app->login($_POST['password'] ?? '', isset($_POST['remember']))) {
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                }
                break;

            case 'logout':
                $app->logout();
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;

            case 'change_password':
                $app->changePassword(
                    $_POST['current_password'] ?? '',
                    $_POST['new_password'] ?? '',
                    $_POST['confirm_password'] ?? ''
                );
                break;

            case 'save_theme':
                $app->updateTheme($_POST['theme'] ?? []);
                break;

            case 'save_ofero':
                $data = json_decode($_POST['ofero_data'] ?? '{}', true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $validationErrors = $app->validateOferoJson($data);
                    if (empty($validationErrors)) {
                        $app->saveOferoJson($data);
                    } else {
                        foreach ($validationErrors as $error) {
                            $app->getErrors()[] = $error;
                        }
                    }
                } else {
                    $app->getErrors()[] = 'Invalid JSON data.';
                }
                break;
        }
    }
}

// Get current state
$config = $app->getConfig();
$theme = $config['theme'];
$isPasswordSet = $app->isPasswordSet();
$isAuthenticated = $app->isAuthenticated();
$errors = $app->getErrors();
$messages = $app->getMessages();
$csrfToken = $app->getCsrfToken();

// Determine current view
if (!$isPasswordSet) {
    $view = 'setup';
} elseif (!$isAuthenticated) {
    $view = 'login';
} else {
    $view = $_GET['view'] ?? 'editor';
}

// Load ofero.json data for editor
$oferoData = [];
if ($view === 'editor') {
    $oferoData = $app->loadOferoJson();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofero.json Generator</title>
    <style>
        :root {
            --text-color: <?php echo htmlspecialchars($theme['textColor']); ?>;
            --bg-color: <?php echo htmlspecialchars($theme['backgroundColor']); ?>;
            --bg-secondary: <?php echo htmlspecialchars($theme['backgroundSecondary']); ?>;
            --border-color: <?php echo htmlspecialchars($theme['borderColor']); ?>;
            --accent-color: <?php echo htmlspecialchars($theme['accentColor']); ?>;
            --success-color: <?php echo htmlspecialchars($theme['successColor']); ?>;
            --error-color: <?php echo htmlspecialchars($theme['errorColor']); ?>;
            --warning-color: <?php echo htmlspecialchars($theme['warningColor']); ?>;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color);
        }

        .nav {
            display: flex;
            gap: 20px;
        }

        .nav a {
            color: var(--text-color);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }

        .nav a:hover, .nav a.active {
            background-color: var(--bg-secondary);
        }

        .card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--accent-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .form-textarea {
            min-height: 150px;
            font-family: 'Monaco', 'Menlo', monospace;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .btn-danger {
            background-color: var(--error-color);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error-color);
            color: var(--error-color);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .tab {
            padding: 10px 20px;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab:hover, .tab.active {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .json-preview {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 12px;
            overflow-x: auto;
            white-space: pre-wrap;
            max-height: 500px;
            overflow-y: auto;
        }

        .color-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .color-input {
            width: 50px;
            height: 40px;
            padding: 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-color);
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }

        .array-item {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }

        .array-item-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--error-color);
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 14px;
        }

        .add-item-btn {
            width: 100%;
            padding: 15px;
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            background: transparent;
            color: var(--text-color);
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .add-item-btn:hover {
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        .validation-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .validation-status.valid {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
        }

        .validation-status.invalid {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error-color);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
            }

            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($view === 'setup'): ?>
        <!-- Initial Password Setup -->
        <div class="login-container">
            <div class="card">
                <h1 class="card-title">Welcome to Ofero.json Generator</h1>
                <p style="margin-bottom: 20px; opacity: 0.8;">Please set a password to protect your generator.</p>

                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="setup_password">

                    <div class="form-group">
                        <label class="form-label">Password (min. 8 characters)</label>
                        <input type="password" name="password" class="form-input" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input" required minlength="8">
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">Set Password & Continue</button>
                </form>
            </div>
        </div>

        <?php elseif ($view === 'login'): ?>
        <!-- Login -->
        <div class="login-container">
            <div class="card">
                <h1 class="card-title">Ofero.json Generator</h1>
                <p style="margin-bottom: 20px; opacity: 0.8;">Please enter your password to continue.</p>

                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>

                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endforeach; ?>

                <?php if ($app->isLocked()): ?>
                    <div class="alert alert-error">Account is locked. Please try again later.</div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="login">

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-input" required autofocus>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="remember">
                                Remember me for 7 days
                            </label>
                        </div>

                        <button type="submit" class="btn" style="width: 100%;">Login</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Main Application -->
        <header class="header">
            <div class="logo">Ofero.json Generator</div>
            <nav class="nav">
                <a href="?view=editor" class="<?php echo $view === 'editor' ? 'active' : ''; ?>">Editor</a>
                <a href="?view=preview" class="<?php echo $view === 'preview' ? 'active' : ''; ?>">Preview</a>
                <a href="?view=settings" class="<?php echo $view === 'settings' ? 'active' : ''; ?>">Settings</a>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </nav>
        </header>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>

        <?php foreach ($messages as $message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endforeach; ?>

        <?php if ($view === 'editor'): ?>
        <!-- Editor View -->
        <div class="card">
            <div class="tabs">
                <button class="tab active" data-tab="basic">Basic Info</button>
                <button class="tab" data-tab="organization">Organization</button>
                <button class="tab" data-tab="locations">Locations</button>
                <button class="tab" data-tab="banking">Banking</button>
                <button class="tab" data-tab="wallets">Wallets</button>
                <button class="tab" data-tab="branding">Branding</button>
                <button class="tab" data-tab="communications">Communications</button>
                <button class="tab" data-tab="translations">Translations</button>
                <button class="tab" data-tab="raw">Raw JSON</button>
            </div>

            <form method="POST" id="oferoForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="save_ofero">
                <input type="hidden" name="ofero_data" id="oferoData">

                <!-- Basic Info Tab -->
                <div class="tab-content active" data-tab="basic">
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Language Code</label>
                            <input type="text" id="language" class="form-input" value="<?php echo htmlspecialchars($oferoData['language'] ?? 'en'); ?>" placeholder="en">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Domain</label>
                            <input type="text" id="domain" class="form-input" value="<?php echo htmlspecialchars($oferoData['domain'] ?? ''); ?>" placeholder="example.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Canonical URL</label>
                            <input type="url" id="canonicalUrl" class="form-input" value="<?php echo htmlspecialchars($oferoData['canonicalUrl'] ?? ''); ?>" placeholder="https://example.com/.well-known/ofero.json">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Version</label>
                            <input type="text" id="metadataVersion" class="form-input" value="<?php echo htmlspecialchars($oferoData['metadata']['version'] ?? '1.0.0'); ?>" placeholder="1.0.0">
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Keywords</label>
                            <?php
                            $kw = $oferoData['keywords'] ?? '';
                            $kwValue = is_array($kw) ? ($kw['default'] ?? '') : $kw;
                            ?>
                            <input type="text" id="keywords" class="form-input" value="<?php echo htmlspecialchars($kwValue); ?>" placeholder="blockchain, fintech, e-commerce, web3, saas">
                            <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">Comma-separated keywords for AI search indexing and discovery.</small>
                        </div>
                    </div>
                </div>

                <!-- Organization Tab -->
                <div class="tab-content" data-tab="organization">
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Legal Name *</label>
                            <input type="text" id="orgLegalName" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['legalName'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Brand Name</label>
                            <input type="text" id="orgBrandName" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['brandName'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Entity Type *</label>
                            <select id="orgEntityType" class="form-select">
                                <?php
                                $entityTypes = ['company', 'foundation', 'association', 'protocol', 'store', 'ngo', 'individual', 'project', 'other'];
                                $currentType = $oferoData['organization']['entityType'] ?? 'company';
                                foreach ($entityTypes as $type):
                                ?>
                                <option value="<?php echo $type; ?>" <?php echo $currentType === $type ? 'selected' : ''; ?>><?php echo ucfirst($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Legal Form</label>
                            <input type="text" id="orgLegalForm" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['legalForm'] ?? ''); ?>" placeholder="LLC, SRL, etc.">
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Description</label>
                            <textarea id="orgDescription" class="form-input" rows="3"><?php echo htmlspecialchars($oferoData['organization']['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="url" id="orgWebsite" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['website'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Email</label>
                            <input type="email" id="orgContactEmail" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['contactEmail'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Phone</label>
                            <input type="tel" id="orgContactPhone" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['contactPhone'] ?? ''); ?>">
                        </div>
                    </div>

                    <h3 style="margin: 30px 0 20px; color: var(--accent-color);">Primary Incorporation</h3>
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Country Code</label>
                            <input type="text" id="incCountry" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['identifiers']['primaryIncorporation']['country'] ?? ''); ?>" placeholder="US, RO, etc.">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Registration Number</label>
                            <input type="text" id="incRegNumber" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['identifiers']['primaryIncorporation']['registrationNumber'] ?? ''); ?>" placeholder="e.g., 12-3456789, 123456789">
                            <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">Company registration number from business registry</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tax ID</label>
                            <input type="text" id="incTaxId" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['identifiers']['primaryIncorporation']['taxId'] ?? ''); ?>" placeholder="e.g., 12-3456789, EIN, TIN">
                            <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">Tax identification number</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">VAT Number</label>
                            <input type="text" id="incVatNumber" class="form-input" value="<?php echo htmlspecialchars($oferoData['organization']['identifiers']['primaryIncorporation']['vatNumber'] ?? ''); ?>" placeholder="e.g., US12345678, GB123456789">
                            <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">VAT registration number (if applicable)</small>
                        </div>
                    </div>
                </div>

                <!-- Locations Tab -->
                <div class="tab-content" data-tab="locations">
                    <div id="locationsContainer">
                        <?php
                        $locations = $oferoData['locations'] ?? [];
                        foreach ($locations as $index => $location):
                        ?>
                        <div class="array-item location-item" data-index="<?php echo $index; ?>">
                            <button type="button" class="array-item-remove" onclick="removeLocation(this)">&times;</button>
                            <div class="grid">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-input loc-name" value="<?php echo htmlspecialchars($location['name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Type</label>
                                    <select class="form-select loc-type">
                                        <option value="headquarters" <?php echo ($location['type'] ?? '') === 'headquarters' ? 'selected' : ''; ?>>Headquarters</option>
                                        <option value="branch" <?php echo ($location['type'] ?? '') === 'branch' ? 'selected' : ''; ?>>Branch</option>
                                        <option value="store" <?php echo ($location['type'] ?? '') === 'store' ? 'selected' : ''; ?>>Store</option>
                                        <option value="warehouse" <?php echo ($location['type'] ?? '') === 'warehouse' ? 'selected' : ''; ?>>Warehouse</option>
                                        <option value="office" <?php echo ($location['type'] ?? '') === 'office' ? 'selected' : ''; ?>>Office</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" class="form-input loc-street" value="<?php echo htmlspecialchars($location['address']['street'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-input loc-city" value="<?php echo htmlspecialchars($location['address']['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Region / State</label>
                                    <input type="text" class="form-input loc-region" value="<?php echo htmlspecialchars($location['address']['region'] ?? ''); ?>" placeholder="State, province, or region">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-input loc-postal" value="<?php echo htmlspecialchars($location['address']['postalCode'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Country</label>
                                    <input type="text" class="form-input loc-country" value="<?php echo htmlspecialchars($location['address']['country'] ?? ''); ?>" placeholder="US" maxlength="2" style="text-transform: uppercase;">
                                    <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">2-letter ISO code (e.g., US, GB, DE)</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-input loc-phone" value="<?php echo htmlspecialchars($location['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-input loc-email" value="<?php echo htmlspecialchars($location['email'] ?? ''); ?>">
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label class="form-label">Location Photos (URLs)</label>
                                    <textarea class="form-input loc-photos" rows="2" placeholder="One HTTPS URL per line, e.g. https://example.com/photo.jpg" style="width:100%;"><?php echo htmlspecialchars(implode("\n", $location['photos'] ?? [])); ?></textarea>
                                    <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">Photos of this location. One URL per line.</small>
                                </div>
                            </div>
                            <!-- Special Hours -->
                            <div class="loc-special-hours-wrapper" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <strong style="font-size: 13px;">Special Hours</strong>
                                    <button type="button" onclick="addSpecialHour(this)" style="font-size: 12px;">+ Add Entry</button>
                                </div>
                                <div class="loc-special-hours-list">
                                <?php if (!empty($location['specialHours'])): ?>
                                <?php foreach ($location['specialHours'] as $entry): ?>
                                <div class="loc-special-hour-item" style="border-left: 3px solid #7986cb; padding-left: 12px; margin: 8px 0;">
                                    <div class="grid">
                                        <div class="form-group">
                                            <label class="form-label">Type</label>
                                            <select class="form-select loc-sh-type" onchange="toggleSpecialHourType(this)">
                                                <option value="date" <?php echo !isset($entry['from']) ? 'selected' : ''; ?>>Single day</option>
                                                <option value="range" <?php echo isset($entry['from']) ? 'selected' : ''; ?>>Date range</option>
                                            </select>
                                        </div>
                                        <div class="form-group loc-sh-date-field" <?php echo isset($entry['from']) ? 'style="display:none;"' : ''; ?>>
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-input loc-sh-date" value="<?php echo htmlspecialchars($entry['date'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group loc-sh-from-field" <?php echo !isset($entry['from']) ? 'style="display:none;"' : ''; ?>>
                                            <label class="form-label">From</label>
                                            <input type="date" class="form-input loc-sh-from" value="<?php echo htmlspecialchars($entry['from'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group loc-sh-to-field" <?php echo !isset($entry['from']) ? 'style="display:none;"' : ''; ?>>
                                            <label class="form-label">To</label>
                                            <input type="date" class="form-input loc-sh-to" value="<?php echo htmlspecialchars($entry['to'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Name / Label</label>
                                            <input type="text" class="form-input loc-sh-name" value="<?php echo htmlspecialchars($entry['name'] ?? ''); ?>" placeholder="e.g. Christmas Day">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Hours</label>
                                            <input type="text" class="form-input loc-sh-hours" value="<?php echo htmlspecialchars($entry['hours'] ?? ''); ?>" placeholder="e.g. 10:00-14:00 or Closed">
                                        </div>
                                    </div>
                                    <button type="button" onclick="this.closest('.loc-special-hour-item').remove()" style="margin-top: 4px; font-size: 12px; color: #ef4444; background: none; border: none; cursor: pointer;">&times; Remove</button>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                </div>
                            </div>

                            <div class="loc-contacts-wrapper" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                                <?php if (!empty($location['contacts'])): ?>
                                <?php foreach ($location['contacts'] as $contact): ?>
                                <div class="loc-contact-item" style="border-left: 3px solid #7986cb; padding-left: 12px; margin: 8px 0;">
                                    <div class="grid">
                                        <div class="form-group">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-input loc-contact-name" value="<?php echo htmlspecialchars($contact['name'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Role</label>
                                            <input type="text" class="form-input loc-contact-role" value="<?php echo htmlspecialchars($contact['role'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-input loc-contact-email" value="<?php echo htmlspecialchars($contact['email'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" class="form-input loc-contact-phone" value="<?php echo htmlspecialchars($contact['phone'] ?? ''); ?>" placeholder="+40722333444">
                                        </div>
                                        <div class="form-group" style="grid-column: 1 / -1;">
                                            <label class="form-label">Photo URL</label>
                                            <input type="url" class="form-input loc-contact-photo" value="<?php echo htmlspecialchars($contact['photo'] ?? ''); ?>" placeholder="https://example.com/photo.jpg" style="width:100%;">
                                        </div>
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" class="loc-contact-public" <?php echo !empty($contact['public']) ? 'checked' : ''; ?>>
                                                Public contact
                                            </label>
                                        </div>
                                    </div>
                                    <button type="button" onclick="this.closest('.loc-contact-item').remove()" style="margin-top: 4px; font-size: 12px; color: #ef4444; background: none; border: none; cursor: pointer;">&times; Remove contact</button>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="add-item-btn" onclick="addLocationContact(this)" style="margin-top: 8px; font-size: 12px;">+ Add Contact Person</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addLocation()">+ Add Location</button>
                </div>

                <!-- Banking Tab -->
                <div class="tab-content" data-tab="banking">
                    <div id="bankingContainer">
                        <?php
                        $banking = $oferoData['banking'] ?? [];
                        foreach ($banking as $index => $account):
                        ?>
                        <div class="array-item banking-item" data-index="<?php echo $index; ?>">
                            <button type="button" class="array-item-remove" onclick="removeBanking(this)">&times;</button>
                            <div class="grid">
                                <div class="form-group">
                                    <label class="form-label">Account Name</label>
                                    <input type="text" class="form-input bank-name" value="<?php echo htmlspecialchars($account['accountName'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-input bank-bankName" value="<?php echo htmlspecialchars($account['bankName'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">IBAN</label>
                                    <input type="text" class="form-input bank-iban" value="<?php echo htmlspecialchars($account['iban'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">BIC/SWIFT</label>
                                    <input type="text" class="form-input bank-bic" value="<?php echo htmlspecialchars($account['bic'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Currency</label>
                                    <input type="text" class="form-input bank-currency" value="<?php echo htmlspecialchars($account['currency'] ?? ''); ?>" placeholder="EUR, USD, RON">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addBanking()">+ Add Bank Account</button>
                </div>

                <!-- Wallets Tab -->
                <div class="tab-content" data-tab="wallets">
                    <div id="walletsContainer">
                        <?php
                        $wallets = $oferoData['wallets'] ?? [];
                        foreach ($wallets as $index => $wallet):
                        ?>
                        <div class="array-item wallet-item" data-index="<?php echo $index; ?>">
                            <button type="button" class="array-item-remove" onclick="removeWallet(this)">&times;</button>
                            <div class="grid">
                                <div class="form-group">
                                    <label class="form-label">Blockchain</label>
                                    <select class="form-select wallet-chain">
                                        <option value="ethereum" <?php echo ($wallet['blockchain'] ?? '') === 'ethereum' ? 'selected' : ''; ?>>Ethereum</option>
                                        <option value="multiversx" <?php echo ($wallet['blockchain'] ?? '') === 'multiversx' ? 'selected' : ''; ?>>MultiversX</option>
                                        <option value="bitcoin" <?php echo ($wallet['blockchain'] ?? '') === 'bitcoin' ? 'selected' : ''; ?>>Bitcoin</option>
                                        <option value="polygon" <?php echo ($wallet['blockchain'] ?? '') === 'polygon' ? 'selected' : ''; ?>>Polygon</option>
                                        <option value="solana" <?php echo ($wallet['blockchain'] ?? '') === 'solana' ? 'selected' : ''; ?>>Solana</option>
                                        <option value="other" <?php echo ($wallet['blockchain'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Network</label>
                                    <input type="text" class="form-input wallet-network" value="<?php echo htmlspecialchars($wallet['network'] ?? ''); ?>" placeholder="mainnet, testnet">
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-input wallet-address" value="<?php echo htmlspecialchars($wallet['address'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Label</label>
                                    <input type="text" class="form-input wallet-label" value="<?php echo htmlspecialchars($wallet['label'] ?? ''); ?>" placeholder="Treasury, Payments, etc.">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addWallet()">+ Add Wallet</button>
                </div>

                <!-- Branding Tab -->
                <div class="tab-content" data-tab="branding">
                    <div style="background: var(--card-bg); border-left: 4px solid var(--accent-color); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                        <strong style="color: var(--text-color); display: block; margin-bottom: 8px;">Logo Variants:</strong>
                        <ul style="color: var(--muted-color); font-size: 13px; margin: 0; padding-left: 20px; line-height: 1.6;">
                            <li><strong>Light:</strong> Logo optimized for dark backgrounds (light-colored logo for dark themes)</li>
                            <li><strong>Dark:</strong> Logo optimized for light backgrounds (dark-colored logo for light themes)</li>
                            <li><strong>Monochrome:</strong> Single-color version</li>
                            <li><strong>Primary:</strong> Default/main logo version</li>
                        </ul>
                    </div>
                    <div id="brandingContainer">
                        <?php
                        $brandAssets = $oferoData['brandAssets'] ?? [];
                        foreach ($brandAssets as $index => $asset):
                        ?>
                        <div class="array-item brand-item" data-index="<?php echo $index; ?>">
                            <button type="button" class="array-item-remove" onclick="removeBrandAsset(this)">&times;</button>
                            <div class="grid">
                                <div class="form-group">
                                    <label class="form-label">Type</label>
                                    <select class="form-select brand-type">
                                        <option value="logo" <?php echo ($asset['type'] ?? '') === 'logo' ? 'selected' : ''; ?>>Logo</option>
                                        <option value="icon" <?php echo ($asset['type'] ?? '') === 'icon' ? 'selected' : ''; ?>>Icon</option>
                                        <option value="banner" <?php echo ($asset['type'] ?? '') === 'banner' ? 'selected' : ''; ?>>Banner</option>
                                        <option value="favicon" <?php echo ($asset['type'] ?? '') === 'favicon' ? 'selected' : ''; ?>>Favicon</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Variant (theme/background)</label>
                                    <select class="form-select brand-variant">
                                        <option value="primary" <?php echo ($asset['variant'] ?? '') === 'primary' ? 'selected' : ''; ?>>Primary</option>
                                        <option value="dark" <?php echo ($asset['variant'] ?? '') === 'dark' ? 'selected' : ''; ?>>Dark (for light backgrounds)</option>
                                        <option value="light" <?php echo ($asset['variant'] ?? '') === 'light' ? 'selected' : ''; ?>>Light (for dark backgrounds)</option>
                                        <option value="monochrome" <?php echo ($asset['variant'] ?? '') === 'monochrome' ? 'selected' : ''; ?>>Monochrome</option>
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label class="form-label">URL</label>
                                    <input type="url" class="form-input brand-url" value="<?php echo htmlspecialchars($asset['url'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Format</label>
                                    <input type="text" class="form-input brand-format" value="<?php echo htmlspecialchars($asset['format'] ?? ''); ?>" placeholder="png, svg, jpg">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addBrandAsset()">+ Add Brand Asset</button>
                </div>

                <!-- Communications Tab -->
                <div class="tab-content" data-tab="communications">
                    <h3 style="margin-bottom: 20px; color: var(--accent-color);">Social Media</h3>
                    <div style="background: var(--card-bg); border-left: 4px solid var(--accent-color); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                        <strong style="color: var(--text-color); display: block; margin-bottom: 8px;">Important: Always use complete URLs</strong>
                        <ul style="color: var(--muted-color); font-size: 13px; margin: 0; padding-left: 20px; line-height: 1.6;">
                            <li><strong>Facebook:</strong> https://facebook.com/yourpage or https://facebook.com/profile.php?id=123456</li>
                            <li><strong>Instagram:</strong> https://instagram.com/username</li>
                            <li><strong>WhatsApp:</strong> https://wa.me/1234567890 (phone with country code, no spaces)</li>
                            <li><strong>X (Twitter):</strong> https://x.com/username or https://twitter.com/username</li>
                            <li><strong>LinkedIn:</strong> https://linkedin.com/company/name (companies) or https://linkedin.com/in/username (individuals)</li>
                            <li><strong>YouTube:</strong> https://youtube.com/@channelname</li>
                            <li><strong>Telegram:</strong> https://t.me/channelname</li>
                        </ul>
                    </div>
                    <div id="socialContainer">
                        <?php
                        $social = $oferoData['communications']['social'] ?? [];
                        foreach ($social as $index => $item):
                        ?>
                        <div class="array-item social-item" data-index="<?php echo $index; ?>">
                            <button type="button" class="array-item-remove" onclick="removeSocial(this)">&times;</button>
                            <div class="grid">
                                <div class="form-group">
                                    <label class="form-label">Platform</label>
                                    <select class="form-select social-platform">
                                        <option value="facebook" <?php echo ($item['platform'] ?? '') === 'facebook' ? 'selected' : ''; ?>>Facebook</option>
                                        <option value="instagram" <?php echo ($item['platform'] ?? '') === 'instagram' ? 'selected' : ''; ?>>Instagram</option>
                                        <option value="x" <?php echo ($item['platform'] ?? '') === 'x' ? 'selected' : ''; ?>>X (Twitter)</option>
                                        <option value="linkedin" <?php echo ($item['platform'] ?? '') === 'linkedin' ? 'selected' : ''; ?>>LinkedIn</option>
                                        <option value="youtube" <?php echo ($item['platform'] ?? '') === 'youtube' ? 'selected' : ''; ?>>YouTube</option>
                                        <option value="tiktok" <?php echo ($item['platform'] ?? '') === 'tiktok' ? 'selected' : ''; ?>>TikTok</option>
                                        <option value="discord" <?php echo ($item['platform'] ?? '') === 'discord' ? 'selected' : ''; ?>>Discord</option>
                                        <option value="telegram" <?php echo ($item['platform'] ?? '') === 'telegram' ? 'selected' : ''; ?>>Telegram</option>
                                        <option value="github" <?php echo ($item['platform'] ?? '') === 'github' ? 'selected' : ''; ?>>GitHub</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">URL (complete link)</label>
                                    <input type="url" class="form-input social-url" value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>" placeholder="https://platform.com/yourprofile">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addSocial()">+ Add Social Link</button>

                    <h3 style="margin: 30px 0 20px; color: var(--accent-color);">Support Channels</h3>
                    <div id="supportContainer">
                        <?php
                        $support = $oferoData['communications']['support'] ?? [];
                        foreach ($support as $index => $item):
                        ?>
                        <div class="array-item support-item" data-index="<?php echo $index; ?>">
                            <button type="button" class="array-item-remove" onclick="removeSupport(this)">&times;</button>
                            <div class="grid">
                                <div class="form-group">
                                    <label class="form-label">Type</label>
                                    <select class="form-select support-type">
                                        <option value="email" <?php echo ($item['type'] ?? '') === 'email' ? 'selected' : ''; ?>>Email</option>
                                        <option value="phone" <?php echo ($item['type'] ?? '') === 'phone' ? 'selected' : ''; ?>>Phone</option>
                                        <option value="chat" <?php echo ($item['type'] ?? '') === 'chat' ? 'selected' : ''; ?>>Chat</option>
                                        <option value="ticket" <?php echo ($item['type'] ?? '') === 'ticket' ? 'selected' : ''; ?>>Ticket System</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-input support-contact" value="<?php echo htmlspecialchars($item['contact'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addSupport()">+ Add Support Channel</button>
                </div>

                <!-- Translations Tab -->
                <div class="tab-content" data-tab="translations">
                    <p style="margin-bottom: 20px; opacity: 0.8;">
                        Add translations for your organization's name and description. Select languages and provide translations below.
                    </p>

                    <div class="form-group">
                        <label class="form-label">Additional Languages</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                            <?php
                            $languages = [
                                'ro' => 'Romanian', 'de' => 'German', 'fr' => 'French', 'es' => 'Spanish',
                                'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch', 'pl' => 'Polish',
                                'cs' => 'Czech', 'hu' => 'Hungarian', 'bg' => 'Bulgarian', 'ru' => 'Russian',
                                'uk' => 'Ukrainian', 'tr' => 'Turkish', 'ar' => 'Arabic', 'zh' => 'Chinese',
                                'ja' => 'Japanese', 'ko' => 'Korean'
                            ];
                            foreach ($languages as $code => $name):
                            ?>
                            <label style="display: flex; align-items: center; gap: 5px; padding: 5px 10px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 4px; cursor: pointer;">
                                <input type="checkbox" class="trans-lang-toggle" data-lang="<?php echo $code; ?>">
                                <?php echo $name; ?> (<?php echo $code; ?>)
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="translationsContainer" style="display: none;">
                        <h3 style="margin-bottom: 15px; color: var(--accent-color);">Brand Name Translations</h3>
                        <div class="form-group">
                            <label class="form-label">Default (<?php echo htmlspecialchars($oferoData['language'] ?? 'en'); ?>)</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars(is_array($oferoData['organization']['brandName'] ?? '') ? ($oferoData['organization']['brandName']['default'] ?? '') : ($oferoData['organization']['brandName'] ?? '')); ?>" disabled style="opacity: 0.7;">
                        </div>
                        <div id="brandNameTranslations" class="grid"></div>

                        <h3 style="margin: 30px 0 15px; color: var(--accent-color);">Description Translations</h3>
                        <div class="form-group">
                            <label class="form-label">Default (<?php echo htmlspecialchars($oferoData['language'] ?? 'en'); ?>)</label>
                            <textarea class="form-input" rows="2" disabled style="opacity: 0.7;"><?php echo htmlspecialchars(is_array($oferoData['organization']['description'] ?? '') ? ($oferoData['organization']['description']['default'] ?? '') : ($oferoData['organization']['description'] ?? '')); ?></textarea>
                        </div>
                        <div id="descriptionTranslations"></div>
                    </div>

                    <p style="margin-top: 20px; font-size: 14px; opacity: 0.7;">
                        <strong>Note:</strong> Translations are stored inline using the TranslatableString format: <code>{"default": "...", "translations": {"ro": "..."}}</code>
                    </p>
                </div>

                <!-- Raw JSON Tab -->
                <div class="tab-content" data-tab="raw">
                    <div class="form-group">
                        <label class="form-label">Raw JSON (Advanced)</label>
                        <textarea id="rawJson" class="form-textarea" style="min-height: 400px; font-family: monospace;"><?php echo htmlspecialchars(json_encode($oferoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></textarea>
                    </div>
                    <p style="margin-bottom: 20px; opacity: 0.7; font-size: 14px;">
                        Warning: Editing raw JSON will override form data. Make sure JSON is valid before saving.
                    </p>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <button type="submit" class="btn">Save ofero.json</button>
                    <button type="button" class="btn btn-secondary" onclick="validateJson()">Validate</button>
                    <button type="button" class="btn btn-secondary" onclick="downloadJson()">Download</button>
                </div>
            </form>
        </div>

        <?php elseif ($view === 'preview'): ?>
        <!-- Preview View -->
        <div class="card">
            <h2 class="card-title">ofero.json Preview</h2>

            <?php
            $oferoData = $app->loadOferoJson();
            $validationErrors = $app->validateOferoJson($oferoData);
            ?>

            <div class="validation-status <?php echo empty($validationErrors) ? 'valid' : 'invalid'; ?>">
                <?php if (empty($validationErrors)): ?>
                    <span style="font-size: 20px;">&#10003;</span>
                    <span>Valid ofero.json</span>
                <?php else: ?>
                    <span style="font-size: 20px;">&#10007;</span>
                    <span>Validation errors found</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($validationErrors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($validationErrors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="json-preview"><?php echo htmlspecialchars(json_encode($oferoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></div>

            <div style="margin-top: 20px;">
                <p><strong>File Location:</strong> <?php echo htmlspecialchars($config['settings']['outputPath']); ?></p>
                <p><strong>Last Updated:</strong> <?php echo htmlspecialchars($oferoData['metadata']['lastUpdated'] ?? 'Never'); ?></p>
            </div>
        </div>

        <?php elseif ($view === 'settings'): ?>
        <!-- Settings View -->
        <div class="grid">
            <div class="card">
                <h2 class="card-title">Theme Settings</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="save_theme">

                    <div class="form-group">
                        <label class="form-label">Text Color</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="theme[textColor]" class="color-input" value="<?php echo htmlspecialchars($theme['textColor']); ?>">
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($theme['textColor']); ?>" readonly style="flex: 1;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Background Color</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="theme[backgroundColor]" class="color-input" value="<?php echo htmlspecialchars($theme['backgroundColor']); ?>">
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($theme['backgroundColor']); ?>" readonly style="flex: 1;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Secondary Background</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="theme[backgroundSecondary]" class="color-input" value="<?php echo htmlspecialchars($theme['backgroundSecondary']); ?>">
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($theme['backgroundSecondary']); ?>" readonly style="flex: 1;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Border Color</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="theme[borderColor]" class="color-input" value="<?php echo htmlspecialchars($theme['borderColor']); ?>">
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($theme['borderColor']); ?>" readonly style="flex: 1;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Accent Color</label>
                        <div class="color-input-wrapper">
                            <input type="color" name="theme[accentColor]" class="color-input" value="<?php echo htmlspecialchars($theme['accentColor']); ?>">
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($theme['accentColor']); ?>" readonly style="flex: 1;">
                        </div>
                    </div>

                    <button type="submit" class="btn">Save Theme</button>
                </form>
            </div>

            <div class="card">
                <h2 class="card-title">Change Password</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" required minlength="8">
                    </div>

                    <button type="submit" class="btn">Change Password</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">About</h2>
            <p><strong>Ofero.json Generator</strong> v1.0.0</p>
            <p>A standalone tool for creating and managing ofero.json files.</p>
            <p style="margin-top: 10px;">
                <a href="https://ofero.me/ofero-json" target="_blank" style="color: var(--accent-color);">Learn more about ofero.json standard</a>
            </p>
        </div>

        <?php endif; ?>

        <?php endif; ?>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                tab.classList.add('active');
                document.querySelector(`.tab-content[data-tab="${tab.dataset.tab}"]`).classList.add('active');
            });
        });

        // Collect form data
        function collectFormData() {
            const rawTab = document.querySelector('.tab[data-tab="raw"]');
            if (rawTab && rawTab.classList.contains('active')) {
                try {
                    return JSON.parse(document.getElementById('rawJson').value);
                } catch (e) {
                    alert('Invalid JSON in raw editor');
                    return null;
                }
            }

            const data = {
                language: document.getElementById('language')?.value || 'en',
                domain: document.getElementById('domain')?.value || '',
                canonicalUrl: document.getElementById('canonicalUrl')?.value || '',
                metadata: {
                    version: document.getElementById('metadataVersion')?.value || '1.0.0',
                    schemaVersion: 'ofero-metadata-1.0',
                    lastUpdated: new Date().toISOString(),
                    createdAt: '<?php echo htmlspecialchars($oferoData['metadata']['createdAt'] ?? date('c')); ?>'
                },
                organization: {
                    legalName: document.getElementById('orgLegalName')?.value || '',
                    brandName: buildTranslatableString(
                        document.getElementById('orgBrandName')?.value || '',
                        collectTranslations('brandName')
                    ),
                    entityType: document.getElementById('orgEntityType')?.value || 'company',
                    legalForm: document.getElementById('orgLegalForm')?.value || '',
                    description: buildTranslatableString(
                        document.getElementById('orgDescription')?.value || '',
                        collectTranslations('description')
                    ),
                    website: document.getElementById('orgWebsite')?.value || '',
                    contactEmail: document.getElementById('orgContactEmail')?.value || '',
                    contactPhone: document.getElementById('orgContactPhone')?.value || '',
                    identifiers: {
                        global: {},
                        primaryIncorporation: {
                            country: document.getElementById('incCountry')?.value || '',
                            registrationNumber: document.getElementById('incRegNumber')?.value || '',
                            taxId: document.getElementById('incTaxId')?.value || '',
                            vatNumber: document.getElementById('incVatNumber')?.value || ''
                        },
                        perCountry: []
                    }
                },
                locations: collectLocations(),
                banking: collectBanking(),
                wallets: collectWallets(),
                brandAssets: collectBrandAssets(),
                communications: {
                    social: collectSocial(),
                    support: collectSupport()
                }
            };

            const keywords = document.getElementById('keywords')?.value?.trim() || '';
            if (keywords) {
                data.keywords = keywords;
            }

            return data;
        }

        function collectLocations() {
            const items = [];
            document.querySelectorAll('.location-item').forEach(item => {
                const loc = {
                    name: item.querySelector('.loc-name')?.value || '',
                    type: item.querySelector('.loc-type')?.value || 'headquarters',
                    address: {
                        street: item.querySelector('.loc-street')?.value || '',
                        city: item.querySelector('.loc-city')?.value || '',
                        region: item.querySelector('.loc-region')?.value || '',
                        postalCode: item.querySelector('.loc-postal')?.value || '',
                        country: item.querySelector('.loc-country')?.value || ''
                    },
                    phone: item.querySelector('.loc-phone')?.value || '',
                    email: item.querySelector('.loc-email')?.value || ''
                };
                // Collect location photos
                const photosRaw = item.querySelector('.loc-photos')?.value || '';
                const photos = photosRaw.split('\n').map(u => u.trim()).filter(u => u.length > 0);
                if (photos.length > 0) {
                    loc.photos = photos;
                }
                // Collect special hours
                const specialHours = [];
                item.querySelectorAll('.loc-special-hour-item').forEach(shi => {
                    const type  = shi.querySelector('.loc-sh-type')?.value || 'date';
                    const name  = shi.querySelector('.loc-sh-name')?.value.trim() || '';
                    const hours = shi.querySelector('.loc-sh-hours')?.value.trim() || '';
                    if (!name || !hours) return;
                    if (type === 'range') {
                        const from = shi.querySelector('.loc-sh-from')?.value || '';
                        const to   = shi.querySelector('.loc-sh-to')?.value || '';
                        if (from && to) specialHours.push({ from, to, name, hours });
                    } else {
                        const date = shi.querySelector('.loc-sh-date')?.value || '';
                        if (date) specialHours.push({ date, name, hours });
                    }
                });
                if (specialHours.length > 0) {
                    loc.specialHours = specialHours;
                }
                // Collect contact persons
                const contacts = [];
                item.querySelectorAll('.loc-contact-item').forEach(ci => {
                    const cName = ci.querySelector('.loc-contact-name')?.value || '';
                    if (!cName) return;
                    const contact = {
                        name: cName,
                        role: ci.querySelector('.loc-contact-role')?.value || '',
                        email: ci.querySelector('.loc-contact-email')?.value || '',
                        public: ci.querySelector('.loc-contact-public')?.checked || false
                    };
                    const phone = ci.querySelector('.loc-contact-phone')?.value || '';
                    if (phone) contact.phone = phone;
                    const photo = ci.querySelector('.loc-contact-photo')?.value || '';
                    if (photo) contact.photo = photo;
                    contacts.push(contact);
                });
                if (contacts.length > 0) {
                    loc.contacts = contacts;
                }
                items.push(loc);
            });
            return items;
        }

        function collectBanking() {
            const items = [];
            document.querySelectorAll('.banking-item').forEach(item => {
                items.push({
                    accountName: item.querySelector('.bank-name')?.value || '',
                    bankName: item.querySelector('.bank-bankName')?.value || '',
                    iban: item.querySelector('.bank-iban')?.value || '',
                    bic: item.querySelector('.bank-bic')?.value || '',
                    currency: item.querySelector('.bank-currency')?.value || ''
                });
            });
            return items;
        }

        function collectWallets() {
            const items = [];
            document.querySelectorAll('.wallet-item').forEach(item => {
                items.push({
                    blockchain: item.querySelector('.wallet-chain')?.value || '',
                    network: item.querySelector('.wallet-network')?.value || '',
                    address: item.querySelector('.wallet-address')?.value || '',
                    label: item.querySelector('.wallet-label')?.value || ''
                });
            });
            return items;
        }

        function collectBrandAssets() {
            const items = [];
            document.querySelectorAll('.brand-item').forEach(item => {
                items.push({
                    type: item.querySelector('.brand-type')?.value || '',
                    variant: item.querySelector('.brand-variant')?.value || '',
                    url: item.querySelector('.brand-url')?.value || '',
                    format: item.querySelector('.brand-format')?.value || ''
                });
            });
            return items;
        }

        function collectSocial() {
            const items = [];
            document.querySelectorAll('.social-item').forEach(item => {
                items.push({
                    platform: item.querySelector('.social-platform')?.value || '',
                    url: item.querySelector('.social-url')?.value || ''
                });
            });
            return items;
        }

        function collectSupport() {
            const items = [];
            document.querySelectorAll('.support-item').forEach(item => {
                items.push({
                    type: item.querySelector('.support-type')?.value || '',
                    contact: item.querySelector('.support-contact')?.value || ''
                });
            });
            return items;
        }

        // Form submission
        document.getElementById('oferoForm')?.addEventListener('submit', function(e) {
            const data = collectFormData();
            if (data) {
                document.getElementById('oferoData').value = JSON.stringify(data);
            } else {
                e.preventDefault();
            }
        });

        // Add/Remove functions
        function addLocation() {
            const container = document.getElementById('locationsContainer');
            const html = `
                <div class="array-item location-item">
                    <button type="button" class="array-item-remove" onclick="removeLocation(this)">&times;</button>
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-input loc-name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select class="form-select loc-type">
                                <option value="headquarters">Headquarters</option>
                                <option value="branch">Branch</option>
                                <option value="store">Store</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="office">Office</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Street Address</label>
                            <input type="text" class="form-input loc-street">
                        </div>
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-input loc-city">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Region / State</label>
                            <input type="text" class="form-input loc-region" placeholder="State, province, or region">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-input loc-postal">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-input loc-country" placeholder="US" maxlength="2" style="text-transform: uppercase;">
                            <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">2-letter ISO code (e.g., US, GB, DE)</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-input loc-phone">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input loc-email">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Location Photos (URLs)</label>
                            <textarea class="form-input loc-photos" rows="2" placeholder="One HTTPS URL per line, e.g. https://example.com/photo.jpg" style="width:100%;"></textarea>
                            <small style="color: var(--muted-color); font-size: 12px; display: block; margin-top: 4px;">Photos of this location. One URL per line.</small>
                        </div>
                    </div>
                    <div class="loc-special-hours-wrapper" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <strong style="font-size: 13px;">Special Hours</strong>
                            <button type="button" onclick="addSpecialHour(this)" style="font-size: 12px;">+ Add Entry</button>
                        </div>
                        <div class="loc-special-hours-list"></div>
                    </div>
                    <div class="loc-contacts-wrapper" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);"></div>
                    <button type="button" onclick="addLocationContact(this)" style="margin-top: 8px; font-size: 12px;">+ Add Contact Person</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function toggleSpecialHourType(select) {
            const item = select.closest('.loc-special-hour-item');
            const isRange = select.value === 'range';
            item.querySelector('.loc-sh-date-field').style.display = isRange ? 'none' : '';
            item.querySelector('.loc-sh-from-field').style.display = isRange ? '' : 'none';
            item.querySelector('.loc-sh-to-field').style.display   = isRange ? '' : 'none';
        }

        function addSpecialHour(btn) {
            const list = btn.closest('.loc-special-hours-wrapper').querySelector('.loc-special-hours-list');
            const html = `
                <div class="loc-special-hour-item" style="border-left: 3px solid #7986cb; padding-left: 12px; margin: 8px 0;">
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select class="form-select loc-sh-type" onchange="toggleSpecialHourType(this)">
                                <option value="date">Single day</option>
                                <option value="range">Date range</option>
                            </select>
                        </div>
                        <div class="form-group loc-sh-date-field">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-input loc-sh-date">
                        </div>
                        <div class="form-group loc-sh-from-field" style="display:none;">
                            <label class="form-label">From</label>
                            <input type="date" class="form-input loc-sh-from">
                        </div>
                        <div class="form-group loc-sh-to-field" style="display:none;">
                            <label class="form-label">To</label>
                            <input type="date" class="form-input loc-sh-to">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name / Label</label>
                            <input type="text" class="form-input loc-sh-name" placeholder="e.g. Christmas Day">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hours</label>
                            <input type="text" class="form-input loc-sh-hours" placeholder="e.g. 10:00-14:00 or Closed">
                        </div>
                    </div>
                    <button type="button" onclick="this.closest('.loc-special-hour-item').remove()" style="margin-top: 4px; font-size: 12px; color: #ef4444; background: none; border: none; cursor: pointer;">&times; Remove</button>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        }

        function addLocationContact(btn) {
            const wrapper = btn.previousElementSibling;
            const html = `
                <div class="loc-contact-item" style="border-left: 3px solid #7986cb; padding-left: 12px; margin: 8px 0;">
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-input loc-contact-name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-input loc-contact-role">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input loc-contact-email">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-input loc-contact-phone" placeholder="+40722333444">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Photo URL</label>
                            <input type="url" class="form-input loc-contact-photo" placeholder="https://example.com/photo.jpg" style="width:100%;">
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" class="loc-contact-public"> Public contact</label>
                        </div>
                    </div>
                    <button type="button" onclick="this.closest('.loc-contact-item').remove()" style="margin-top: 4px; font-size: 12px; color: #ef4444; background: none; border: none; cursor: pointer;">&times; Remove contact</button>
                </div>
            `;
            wrapper.insertAdjacentHTML('beforeend', html);
        }

        function removeLocation(btn) {
            btn.closest('.location-item').remove();
        }

        function addBanking() {
            const container = document.getElementById('bankingContainer');
            const html = `
                <div class="array-item banking-item">
                    <button type="button" class="array-item-remove" onclick="removeBanking(this)">&times;</button>
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Account Name</label>
                            <input type="text" class="form-input bank-name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-input bank-bankName">
                        </div>
                        <div class="form-group">
                            <label class="form-label">IBAN</label>
                            <input type="text" class="form-input bank-iban">
                        </div>
                        <div class="form-group">
                            <label class="form-label">BIC/SWIFT</label>
                            <input type="text" class="form-input bank-bic">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency</label>
                            <input type="text" class="form-input bank-currency" placeholder="EUR, USD, RON">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeBanking(btn) {
            btn.closest('.banking-item').remove();
        }

        function addWallet() {
            const container = document.getElementById('walletsContainer');
            const html = `
                <div class="array-item wallet-item">
                    <button type="button" class="array-item-remove" onclick="removeWallet(this)">&times;</button>
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Blockchain</label>
                            <select class="form-select wallet-chain">
                                <option value="ethereum">Ethereum</option>
                                <option value="multiversx">MultiversX</option>
                                <option value="bitcoin">Bitcoin</option>
                                <option value="polygon">Polygon</option>
                                <option value="solana">Solana</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Network</label>
                            <input type="text" class="form-input wallet-network" placeholder="mainnet, testnet">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-input wallet-address">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Label</label>
                            <input type="text" class="form-input wallet-label" placeholder="Treasury, Payments, etc.">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeWallet(btn) {
            btn.closest('.wallet-item').remove();
        }

        function addBrandAsset() {
            const container = document.getElementById('brandingContainer');
            const html = `
                <div class="array-item brand-item">
                    <button type="button" class="array-item-remove" onclick="removeBrandAsset(this)">&times;</button>
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select class="form-select brand-type">
                                <option value="logo">Logo</option>
                                <option value="icon">Icon</option>
                                <option value="banner">Banner</option>
                                <option value="favicon">Favicon</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Variant (theme/background)</label>
                            <select class="form-select brand-variant">
                                <option value="primary">Primary</option>
                                <option value="dark">Dark (for light backgrounds)</option>
                                <option value="light">Light (for dark backgrounds)</option>
                                <option value="monochrome">Monochrome</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">URL</label>
                            <input type="url" class="form-input brand-url">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Format</label>
                            <input type="text" class="form-input brand-format" placeholder="png, svg, jpg">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeBrandAsset(btn) {
            btn.closest('.brand-item').remove();
        }

        function addSocial() {
            const container = document.getElementById('socialContainer');
            const html = `
                <div class="array-item social-item">
                    <button type="button" class="array-item-remove" onclick="removeSocial(this)">&times;</button>
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Platform</label>
                            <select class="form-select social-platform">
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="x">X (Twitter)</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="youtube">YouTube</option>
                                <option value="tiktok">TikTok</option>
                                <option value="discord">Discord</option>
                                <option value="telegram">Telegram</option>
                                <option value="github">GitHub</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">URL (complete link)</label>
                            <input type="url" class="form-input social-url" placeholder="https://platform.com/yourprofile">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeSocial(btn) {
            btn.closest('.social-item').remove();
        }

        function addSupport() {
            const container = document.getElementById('supportContainer');
            const html = `
                <div class="array-item support-item">
                    <button type="button" class="array-item-remove" onclick="removeSupport(this)">&times;</button>
                    <div class="grid">
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select class="form-select support-type">
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="chat">Chat</option>
                                <option value="ticket">Ticket System</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact</label>
                            <input type="text" class="form-input support-contact">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeSupport(btn) {
            btn.closest('.support-item').remove();
        }

        // Validate JSON
        function validateJson() {
            const data = collectFormData();
            if (!data) return;

            const errors = [];

            if (!data.language) errors.push('Language is required');
            if (!data.domain) errors.push('Domain is required');
            if (!data.canonicalUrl) errors.push('Canonical URL is required');
            if (!data.metadata?.version) errors.push('Version is required');
            if (!data.organization?.legalName) errors.push('Legal name is required');
            if (!data.organization?.entityType) errors.push('Entity type is required');

            if (errors.length > 0) {
                alert('Validation Errors:\n\n' + errors.join('\n'));
            } else {
                alert('Validation passed! Your ofero.json is valid.');
            }
        }

        // Download JSON
        function downloadJson() {
            const data = collectFormData();
            if (!data) return;

            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'ofero.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Update raw JSON when switching tabs
        document.querySelector('.tab[data-tab="raw"]')?.addEventListener('click', function() {
            const data = collectFormData();
            if (data) {
                document.getElementById('rawJson').value = JSON.stringify(data, null, 2);
            }
        });

        // Color input sync
        document.querySelectorAll('.color-input').forEach(input => {
            input.addEventListener('input', function() {
                this.nextElementSibling.value = this.value;
            });
        });

        // Translation management
        const selectedLanguages = new Set();
        const languageNames = {
            'ro': 'Romanian', 'de': 'German', 'fr': 'French', 'es': 'Spanish',
            'it': 'Italian', 'pt': 'Portuguese', 'nl': 'Dutch', 'pl': 'Polish',
            'cs': 'Czech', 'hu': 'Hungarian', 'bg': 'Bulgarian', 'ru': 'Russian',
            'uk': 'Ukrainian', 'tr': 'Turkish', 'ar': 'Arabic', 'zh': 'Chinese',
            'ja': 'Japanese', 'ko': 'Korean'
        };

        document.querySelectorAll('.trans-lang-toggle').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const lang = this.dataset.lang;
                if (this.checked) {
                    selectedLanguages.add(lang);
                    addTranslationFields(lang);
                } else {
                    selectedLanguages.delete(lang);
                    removeTranslationFields(lang);
                }
                updateTranslationsVisibility();
            });
        });

        function updateTranslationsVisibility() {
            const container = document.getElementById('translationsContainer');
            container.style.display = selectedLanguages.size > 0 ? 'block' : 'none';
        }

        function addTranslationFields(lang) {
            const langName = languageNames[lang] || lang;

            // Brand name field
            const brandContainer = document.getElementById('brandNameTranslations');
            brandContainer.insertAdjacentHTML('beforeend', `
                <div class="form-group trans-field-${lang}">
                    <label class="form-label">${langName} (${lang})</label>
                    <input type="text" class="form-input trans-brandName-${lang}" placeholder="Brand name in ${langName}">
                </div>
            `);

            // Description field
            const descContainer = document.getElementById('descriptionTranslations');
            descContainer.insertAdjacentHTML('beforeend', `
                <div class="form-group trans-field-${lang}">
                    <label class="form-label">${langName} (${lang})</label>
                    <textarea class="form-input trans-description-${lang}" rows="2" placeholder="Description in ${langName}"></textarea>
                </div>
            `);
        }

        function removeTranslationFields(lang) {
            document.querySelectorAll(`.trans-field-${lang}`).forEach(el => el.remove());
        }

        function collectTranslations(fieldName) {
            const translations = {};
            selectedLanguages.forEach(lang => {
                const input = document.querySelector(`.trans-${fieldName}-${lang}`);
                if (input && input.value.trim()) {
                    translations[lang] = input.value.trim();
                }
            });
            return translations;
        }

        function buildTranslatableString(defaultValue, translations) {
            if (Object.keys(translations).length === 0) {
                return defaultValue;
            }
            return {
                default: defaultValue,
                translations: translations
            };
        }
    </script>
</body>
</html>
