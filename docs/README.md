# Ofero.json - Universal Metadata Standard

A universal, machine-readable metadata format for organizations, businesses, protocols, and entities.

## Quick Start

### 1. Create Your ofero.json File

Create a file at `https://yourdomain.com/.well-known/ofero.json`:

```json
{
  "version": "1.0",
  "language": "en",
  "generatedAt": "2025-01-15T10:00:00Z",
  "organization": {
    "legalName": "Your Company Name",
    "brandName": "Your Brand",
    "entityType": "company",
    "legalForm": "LLC",
    "description": "Brief description of your organization",
    "website": "https://yourdomain.com",
    "primaryEmail": "contact@yourdomain.com",
    "primaryPhone": "+1234567890",
    "identifiers": {
      "global": {},
      "primaryIncorporation": {
        "country": "US",
        "registrationNumber": "12345678",
        "taxId": "12-3456789",
        "vatNumber": ""
      },
      "perCountry": []
    }
  },
  "extensions": {
    "schemaVersion": "ofero-metadata-1.0"
  }
}
```

### 2. Validate Your File

Use the JSON Schema validator:
- **Schema File**: [`ofero-json-schema.json`](../../static/schemas/ofero-json-schema.json)
- **Online Validator**: [Coming Soon]

### 3. Deploy

1. Upload file to `/.well-known/ofero.json`
2. Ensure HTTPS is enabled
3. Set appropriate CORS headers
4. Verify accessibility

## What is Ofero.json?

Ofero.json is a standardized metadata format similar to:
- `robots.txt` for web crawlers
- `schema.org` for structured data
- `OpenAPI` for API specifications

It provides **machine-readable information** about your organization for:
- AI/LLM consumption
- Partner integration (B2B)
- Merchant verification
- Web3 entity metadata
- Business verification

## Use Cases

### For Companies
- Automated merchant onboarding
- Partner discovery
- Brand asset distribution
- API documentation

### For NGOs/Associations
- Transparency and accountability
- Donor verification
- Compliance documentation
- Grant applications

### For Web3 Projects
- Token information
- Treasury transparency
- Wallet verification
- Protocol documentation

## Entity Types

Choose your entity type:
- `company` - For-profit businesses
- `foundation` - Charitable foundations
- `association` - Member associations
- `ngo` - Non-governmental organizations
- `protocol` - Web3 protocols
- `store` - Online/offline stores
- `individual` - Personal brands
- `project` - Open source projects
- `other` - Custom types

## Key Features

### Multi-Language Support
Create language overlays for translations:
- Base file: `ofero.json` (English)
- Overlays: `ofero-ro.json`, `ofero-de.json`, etc.

### Blockchain Integration
Include wallet addresses and token information:
```json
{
  "wallets": [{
    "blockchain": "ethereum",
    "network": "mainnet",
    "address": "0x...",
    "purpose": "treasury"
  }],
  "tokenomics": {
    "token": {
      "symbol": "TOKEN",
      "name": "Token Name",
      "type": "ERC-20"
    }
  }
}
```

### Verification
Prove ownership with cryptographic signatures:
```json
{
  "verification": {
    "domain": "yourdomain.com",
    "walletProof": {
      "message": "...",
      "signature": "0x...",
      "algorithm": "eip-191"
    }
  }
}
```

## Documentation

- **[Full Specification](SPECIFICATION.md)** - Complete field reference
- **[Legal Forms Reference](../../static/schemas/ofero-json-legal-forms.json)** - 120+ legal forms
- **[JSON Schema](../../static/schemas/ofero-json-schema.json)** - Validation schema
- **[Examples](examples/)** - Reference implementations

## Examples

- **[Minimal Example](examples/minimal.json)** - Complete file with all sections
- **[Company Full Example](examples/company-full.json)** - Basic company
- **[E-commerce Store Example](examples/ecommerce-store.json)** - Non-profit
- **[Web3 Protocol Example](examples/web3-protocol.json)** - Web3 protocol
- **[Platform Integration Example](examples/company-with-both-platforms.json)** - Translation example

## Required Fields

Minimum required fields:
- `version` - Schema version (`"1.0"`)
- `language` - ISO 639-1 code (e.g., `"en"`)
- `generatedAt` - ISO 8601 timestamp
- `organization` - Organization details
  - `legalName`
  - `brandName`
  - `entityType`
  - `legalForm`
  - `description`
  - `website`
  - `primaryEmail`
  - `primaryPhone`
  - `identifiers`
- `extensions` - Schema version
  - `schemaVersion` - Must be `"ofero-metadata-1.0"`

## Optional Sections

Enhance your file with:
- `locations` - Physical locations
- `banking` - Bank accounts
- `wallets` - Blockchain wallets
- `brandAssets` - Logos and brand guidelines
- `team` - Leadership and advisors
- `tokenomics` - Token information
- `apis` - Public APIs
- `communications` - Social media and support
- `platformAccounts` - Platform-specific account IDs for B2B integrations
- `compliance` - Regulatory information
- And more...

**💡 Platform Presence Tip**: Organizations with social media should populate:
- **`platformAccounts`** - For B2B account linking (page IDs, business IDs, analytics IDs)
- **`communications.social`** - For public URLs (full social media links)
- See [PLATFORM_SECTIONS_EXPLAINED.md](./examples/PLATFORM_SECTIONS_EXPLAINED.md) for details

## Validation Levels

Choose your validation level:

**Basic** - Minimum requirements
- Valid JSON
- Required fields present

**Moderate** (Recommended)
- All basic checks
- Format validation (email, URLs)
- Enum values
- ISO codes

**Strict** - Full validation
- All moderate checks
- Blockchain address formats
- Bank account validation
- GPS coordinates

## Tools and Resources

### Generation Tool
Use our web-based generator:
`https://oferome.com/ofero-json/generate`

### Validation Tool
Validate your file online:
`https://oferome.com/ofero-json/validate`

### Legal Forms Lookup
Find your legal form:
[Legal Forms Database](../../static/schemas/ofero-json-legal-forms.json)

## Best Practices

1. ✅ **Keep it current** - Update quarterly
2. ✅ **Use HTTPS** - Always serve over secure connection
3. ✅ **Validate first** - Test before deploying
4. ✅ **Start simple** - Add optional sections gradually
5. ✅ **Verify ownership** - Use wallet/DNS proofs
6. ❌ **Don't include secrets** - No private keys or passwords
7. ❌ **Don't hardcode dates** - Use actual generation time
8. ❌ **Don't mix languages** - Use overlays for translations

## Support

- **Documentation**: https://ofero.network/ofero-json
- **Issues**: [GitHub Issues](https://github.com/oferome/ofero-json/issues)
- **Discussions**: [GitHub Discussions](https://github.com/oferome/ofero-json/discussions)

## License

MIT License - See [SPECIFICATION.md](SPECIFICATION.md) for details

## Version

Current Version: **1.0.0**
Schema Version: **ofero-metadata-1.0**

---

**[Read Full Specification →](SPECIFICATION.md)**

