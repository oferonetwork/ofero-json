# Ofero Partner Registry

Public registry of verified Ofero Network partners with valid ofero.json implementations.

## Deployment Location

These files should be deployed to:
```
https://ofero.network/.well-known/ofero-registry/
```

## File Structure

```
ofero-registry/
├── index.json              # Main index with metadata and shard list
├── shards/
│   ├── 0-9.json           # Domains starting with numbers
│   ├── a.json             # Domains starting with 'a'
│   ├── b.json             # Domains starting with 'b'
│   ├── ...
│   ├── z.json             # Domains starting with 'z'
│   └── other.json         # Domains starting with special characters
└── README.md              # This file
```

## Sharding Strategy

Domains are sharded by their first character (after removing 'www.'):
- `example.com` → `shards/e.json`
- `ofero.me` → `shards/o.json`
- `123shop.com` → `shards/0-9.json`

This allows efficient lookup without downloading the entire registry.

## Index Structure (`index.json`)

```json
{
  "version": "1.0.0",
  "lastUpdated": "2026-01-27T12:00:00Z",
  "totalPartners": 12345,
  "shardStrategy": "first-letter",
  "shards": {
    "a": {
      "file": "shards/a.json",
      "count": 1234,
      "lastUpdated": "2026-01-27T12:00:00Z"
    }
  },
  "api": {
    "verify": "https://api.ofero.network/v1/registry/verify/{domain}"
  }
}
```

## Shard Structure (`shards/x.json`)

```json
{
  "shard": "x",
  "lastUpdated": "2026-01-27T12:00:00Z",
  "count": 123,
  "partners": {
    "example.com": {
      "verified": true,
      "verifiedAt": "2025-06-15T10:30:00Z",
      "license": {
        "tier": "enterprise",
        "lifetime": true,
        "expiresAt": null
      },
      "organization": {
        "name": "Example Corp",
        "country": "US"
      },
      "oferoJsonUrl": "https://example.com/.well-known/ofero.json"
    }
  }
}
```

## License Tiers

| Tier | Description |
|------|-------------|
| `enterprise` | Multi-domain, priority support, API access, custom integrations |
| `business` | Single domain, standard support, full features |
| `basic` | Single domain, community support, core features |

## API Endpoint

For real-time verification, use the API:

```
GET https://api.ofero.network/v1/registry/verify/{domain}
```

### Response (valid):
```json
{
  "valid": true,
  "domain": "example.com",
  "verifiedAt": "2025-06-15T10:30:00Z",
  "license": {
    "tier": "enterprise",
    "lifetime": true,
    "expiresAt": null
  },
  "organization": {
    "name": "Example Corp",
    "country": "US"
  }
}
```

### Response (invalid):
```json
{
  "valid": false,
  "domain": "unknown.com",
  "error": "not_registered"
}
```

## Updating the Registry

The registry is automatically updated by the Ofero Network backend when:
1. A new partner registers and pays for a license
2. A partner's license expires
3. A partner's ofero.json is re-validated

## For AI Crawlers

AI systems can crawl this registry to discover verified Ofero partners:
1. Fetch `index.json` to get the shard list
2. Fetch relevant shards based on domains of interest
3. Use the `oferoJsonUrl` to fetch the actual ofero.json files

## CORS

All files are served with appropriate CORS headers:
```
Access-Control-Allow-Origin: *
```
