<?php
/**
 * Simple Admin Panel for Ofero Registry
 *
 * Access: https://licence.ofero.network/admin-panel.php
 * Password: Change ADMIN_PASSWORD below!
 */

session_start();

// Configuration
define('ADMIN_PASSWORD', 'changeme123'); // CHANGE THIS!
define('REGISTRY_FILE', __DIR__ . '/registry.json');

// Handle login
if (isset($_POST['login'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin-panel.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-panel.php');
    exit;
}

// Check authentication
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];

// Handle form submissions (only when logged in)
if ($is_logged_in) {
    // Add domain
    if (isset($_POST['add_domain'])) {
        $registry = load_registry();

        $domain = strtolower($_POST['domain']);
        $domain = preg_replace('/^www\./', '', $domain);

        $registry[$domain] = [
            'domain' => $domain,
            'license' => [
                'tier' => $_POST['tier'],
                'lifetime' => isset($_POST['lifetime']),
                'expires_at' => isset($_POST['lifetime']) ? null : $_POST['expires_at']
            ],
            'organization' => [
                'name' => $_POST['org_name'],
                'email' => $_POST['org_email']
            ],
            'registered_at' => date('c'),
            'updated_at' => date('c')
        ];

        save_registry($registry);
        $success = "Domain '{$domain}' added successfully!";
    }

    // Delete domain
    if (isset($_GET['delete'])) {
        $registry = load_registry();
        $domain = $_GET['delete'];

        if (isset($registry[$domain])) {
            unset($registry[$domain]);
            save_registry($registry);
            $success = "Domain '{$domain}' deleted successfully!";
        }
    }
}

// Load registry functions
function load_registry() {
    if (!file_exists(REGISTRY_FILE)) {
        return [];
    }
    $content = file_get_contents(REGISTRY_FILE);
    return json_decode($content, true) ?? [];
}

function save_registry($registry) {
    $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(REGISTRY_FILE, $json);
}

$registry = $is_logged_in ? load_registry() : [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofero Registry - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f0f0f1;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #1d2327;
            font-size: 28px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #1d2327;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }

        .btn {
            background: #2271b1;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .btn:hover {
            background: #135e96;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1d2327;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-enterprise {
            background: #ffd700;
            color: #856404;
        }

        .badge-business {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-basic {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-lifetime {
            background: #28a745;
            color: white;
        }

        .badge-expiring {
            background: #ffc107;
            color: #856404;
        }

        .login-form {
            max-width: 400px;
            margin: 100px auto;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e9ecef;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2271b1;
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$is_logged_in): ?>
            <!-- Login Form -->
            <div class="login-form">
                <div class="card">
                    <h2 style="margin-bottom: 20px;">Ofero Registry Admin</h2>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required autofocus>
                        </div>
                        <button type="submit" name="login" class="btn">Login</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="header">
                <h1>🌐 Ofero Registry Admin</h1>
                <a href="?logout" class="logout-btn">Logout</a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat-box">
                    <span class="stat-value"><?php echo count($registry); ?></span>
                    <span class="stat-label">Total Domains</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">
                        <?php
                        $enterprise = array_filter($registry, fn($d) => $d['license']['tier'] === 'enterprise');
                        echo count($enterprise);
                        ?>
                    </span>
                    <span class="stat-label">Enterprise</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value">
                        <?php
                        $lifetime = array_filter($registry, fn($d) => $d['license']['lifetime']);
                        echo count($lifetime);
                        ?>
                    </span>
                    <span class="stat-label">Lifetime Licenses</span>
                </div>
            </div>

            <!-- Add Domain Form -->
            <div class="card">
                <h2 style="margin-bottom: 20px;">Add New Domain</h2>

                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Domain *</label>
                            <input type="text" name="domain" placeholder="example.com" required>
                        </div>

                        <div class="form-group">
                            <label>License Tier *</label>
                            <select name="tier" required>
                                <option value="basic">Basic</option>
                                <option value="business">Business</option>
                                <option value="enterprise" selected>Enterprise</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Organization Name *</label>
                            <input type="text" name="org_name" required>
                        </div>

                        <div class="form-group">
                            <label>Organization Email</label>
                            <input type="email" name="org_email">
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="lifetime" checked>
                                Lifetime License
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Expires At (if not lifetime)</label>
                            <input type="datetime-local" name="expires_at">
                        </div>
                    </div>

                    <button type="submit" name="add_domain" class="btn">Add Domain</button>
                </form>
            </div>

            <!-- Domain List -->
            <div class="card">
                <h2 style="margin-bottom: 20px;">Registered Domains (<?php echo count($registry); ?>)</h2>

                <?php if (empty($registry)): ?>
                    <p>No domains registered yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Organization</th>
                                <th>Tier</th>
                                <th>License</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registry as $domain => $data): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($domain); ?></strong></td>
                                    <td><?php echo htmlspecialchars($data['organization']['name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $data['license']['tier']; ?>">
                                            <?php echo strtoupper($data['license']['tier']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($data['license']['lifetime']): ?>
                                            <span class="badge badge-lifetime">LIFETIME</span>
                                        <?php else: ?>
                                            <span class="badge badge-expiring">
                                                Expires: <?php echo date('Y-m-d', strtotime($data['license']['expires_at'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($data['registered_at'])); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo urlencode($domain); ?>"
                                           class="btn-danger"
                                           style="padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 12px;"
                                           onclick="return confirm('Delete <?php echo htmlspecialchars($domain); ?>?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
