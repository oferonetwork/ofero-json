# Ofero Registry API

Simple REST API for verifying domain licenses in the Ofero Network.

## 🚀 Deployment

1. Upload to `https://licence.ofero.network/`
2. Make sure `registry.json` is writable: `chmod 644 registry.json`
3. Change admin token in `index.php` (line 22)

## 📡 API Endpoints

### 1. Verify Domain (Public)

**GET** `/api/v1/registry/verify/{domain}`

Checks if a domain is registered and has a valid license.

**Example:**
```bash
curl https://licence.ofero.network/api/v1/registry/verify/irigarden.ro
```

**Success Response (200):**
```json
{
  "valid": true,
  "domain": "irigarden.ro",
  "license": {
    "tier": "enterprise",
    "lifetime": true,
    "expires_at": null
  },
  "organization": {
    "name": "Iri Garden",
    "email": "contact@irigarden.ro"
  },
  "registered_at": "2025-01-28T12:00:00+02:00",
  "last_verified": "2025-01-28T14:30:00+02:00"
}
```

**Not Found Response (404):**
```json
{
  "valid": false,
  "error": "not_registered",
  "message": "Domain not found in Ofero Network registry",
  "domain": "example.com"
}
```

**Expired License Response (403):**
```json
{
  "valid": false,
  "error": "license_expired",
  "message": "License expired on 2024-12-31T23:59:59+02:00",
  "domain": "example.com",
  "license": {
    "tier": "business",
    "lifetime": false,
    "expires_at": "2024-12-31T23:59:59+02:00"
  }
}
```

---

### 2. Register Domain (Admin Only)

**POST** `/api/v1/registry/register`

**Headers:**
```
Authorization: Bearer your-secret-admin-token-here
Content-Type: application/json
```

**Body:**
```json
{
  "domain": "example.com",
  "tier": "enterprise",
  "lifetime": true,
  "organization": {
    "name": "Example Company",
    "email": "contact@example.com"
  }
}
```

**Example:**
```bash
curl -X POST https://licence.ofero.network/api/v1/registry/register \
  -H "Authorization: Bearer your-secret-admin-token-here" \
  -H "Content-Type: application/json" \
  -d '{
    "domain": "newdomain.com",
    "tier": "business",
    "lifetime": false,
    "expires_at": "2026-12-31T23:59:59+02:00",
    "organization": {
      "name": "New Domain Inc",
      "email": "admin@newdomain.com"
    }
  }'
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Domain registered successfully",
  "domain": "example.com",
  "entry": {
    "domain": "example.com",
    "license": {
      "tier": "enterprise",
      "lifetime": true,
      "expires_at": null
    },
    "organization": {
      "name": "Example Company",
      "email": "contact@example.com"
    },
    "registered_at": "2025-01-28T14:30:00+02:00",
    "updated_at": "2025-01-28T14:30:00+02:00"
  }
}
```

---

### 3. List All Domains (Admin Only)

**GET** `/api/v1/registry/list`

**Headers:**
```
Authorization: Bearer your-secret-admin-token-here
```

**Example:**
```bash
curl https://licence.ofero.network/api/v1/registry/list \
  -H "Authorization: Bearer your-secret-admin-token-here"
```

**Success Response (200):**
```json
{
  "success": true,
  "count": 3,
  "domains": [
    {
      "domain": "irigarden.ro",
      "license": { ... },
      "organization": { ... }
    },
    ...
  ]
}
```

---

### 4. Health Check (Public)

**GET** `/api/v1/registry/health`

**Example:**
```bash
curl https://licence.ofero.network/api/v1/registry/health
```

**Response (200):**
```json
{
  "status": "healthy",
  "service": "Ofero Registry API",
  "version": "1.0.0",
  "timestamp": "2025-01-28T14:30:00+02:00"
}
```

---

## 🔐 Security

1. **Change Admin Token**: Edit `index.php` line 22:
   ```php
   define('ADMIN_TOKEN', 'generate-a-strong-random-token-here');
   ```

2. **Generate Strong Token:**
   ```bash
   openssl rand -hex 32
   ```

3. **Protect Registry File**: The `.htaccess` file already blocks direct access to `registry.json`

---

## 📝 License Tiers

- **basic**: Free tier, basic features
- **business**: Paid tier, full features, single domain
- **enterprise**: Premium tier, multi-domain, priority support, API access

---

## 🛠️ Testing Locally

```bash
# Test verify endpoint
curl http://localhost/api/v1/registry/verify/irigarden.ro

# Test register (replace token)
curl -X POST http://localhost/api/v1/registry/register \
  -H "Authorization: Bearer your-secret-admin-token-here" \
  -H "Content-Type: application/json" \
  -d '{"domain":"test.com","tier":"basic","lifetime":true,"organization":{"name":"Test"}}'

# Test list
curl http://localhost/api/v1/registry/list \
  -H "Authorization: Bearer your-secret-admin-token-here"
```

---

## 📁 File Structure

```
licence.ofero.network/
├── index.php          # Main API handler
├── .htaccess         # URL rewriting & security
├── registry.json     # License database (auto-created)
└── README.md         # This file
```

---

## 🔄 How WordPress Plugin Uses This

The WordPress plugin (`class-license-verifier.php`) calls:

```php
GET https://licence.ofero.network/api/v1/registry/verify/{domain}
```

Every 5 minutes (cached), and displays a badge based on response.

---

## 🚨 Troubleshooting

**404 errors on endpoints?**
- Make sure `mod_rewrite` is enabled in Apache
- Check `.htaccess` is being read

**Permission denied on registry.json?**
```bash
chmod 644 registry.json
chown www-data:www-data registry.json  # Linux
```

**CORS errors?**
- Already configured in `index.php` headers
- If still issues, check server config

---

## 📞 Support

For issues or questions: contact@ofero.network
