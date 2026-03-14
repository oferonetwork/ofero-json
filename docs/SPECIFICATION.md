# Ofero.json Metadata Standard - Specification v1.0

**Official Specification for the ofero.json Universal Metadata Format**

**Version:** 1.0.0
**Schema Version:** ofero-metadata-1.0
**Last Updated:** December 14, 2025
**Status:** Public Standard

---

## Table of Contents

1. [Overview](#overview)
2. [Purpose and Use Cases](#purpose-and-use-cases)
3. [File Location and Format](#file-location-and-format)
4. [Core Structure](#core-structure)
5. [Entity Types](#entity-types)
6. [Field Reference](#field-reference)
7. [Multi-Language Support](#multi-language-support)
8. [Legal Forms Reference](#legal-forms-reference)
9. [Validation Requirements](#validation-requirements)
10. [Examples](#examples)
11. [Implementation Guidelines](#implementation-guidelines)
12. [Version History](#version-history)

---

## Overview

**Ofero.json** is a universal, machine-readable metadata standard for organizations, businesses, protocols, and entities. Similar to how `robots.txt` provides instructions to web crawlers or how OpenAPI defines API specifications, `ofero.json` provides structured, verifiable information about an organization's identity, operations, and digital presence.

### Key Characteristics

- **Universal**: Works for any entity type (companies, NGOs, protocols, stores, individuals)
- **Machine-Readable**: Designed for AI/LLM consumption and automated processing
- **Verifiable**: Includes cryptographic proofs and verification mechanisms
- **Extensible**: Supports blockchain/web3 metadata alongside traditional business data
- **Multi-Language**: Built-in support for translations via language overlays
- **Self-Documenting**: JSON Schema provides complete field definitions and validation

---

## Purpose and Use Cases

### Primary Use Cases

1. **AI/LLM Consumption**
   - Provide accurate, authoritative information about your organization
   - Control what AI systems know about your entity
   - Specify preferred sources and content interpretation

2. **Partner Integration (B2B)**
   - Automated merchant onboarding
   - Partner discovery and verification
   - API endpoint discovery
   - Integration metadata exchange

3. **Web3 Entity Metadata**
   - Verify blockchain wallet ownership
   - Token contract information
   - Treasury and governance details
   - Protocol documentation

4. **Business Verification**
   - Legal entity information
   - Regulatory compliance details
   - Certification and accreditation
   - Banking and payment information

### Who Should Use Ofero.json?

- **Companies**: Businesses of all sizes for merchant verification, partner discovery
- **Associations/NGOs**: Non-profits for transparency, donor verification, compliance
- **Web3 Protocols**: DeFi protocols, DAOs, blockchain projects
- **Online Stores**: E-commerce platforms for catalog feeds, brand assets
- **API Providers**: Services with public APIs needing endpoint documentation

---

## File Location and Format

### Standard Location

```
https://yourdomain.com/.well-known/ofero.json
```

The file **must** be placed in the `/.well-known/` directory at the root of your primary domain.

### File Format

- **Format**: JSON (UTF-8 encoding)
- **Extension**: `.json`
- **MIME Type**: `application/json`
- **Maximum Size**: Recommended < 500 KB (excluding external references)

### HTTP Requirements

- **HTTPS**: Strongly recommended (required for verification)
- **CORS**: Should allow cross-origin requests
- **Cache**: Recommend `Cache-Control: public, max-age=3600` (1 hour)
- **Status**: Must return HTTP 200 OK

### HTTPS Requirement

**CRITICAL SECURITY POLICY:** All URLs in ofero.json files **MUST** use HTTPS protocol. HTTP URLs are explicitly disallowed for:

- **Security**: Prevent man-in-the-middle attacks and data tampering
- **Trust**: HTTPS indicates proper SSL/TLS configuration
- **Partnership Verification**: Partnership validation relies on secure URL matching
- **Data Integrity**: Ensures fetched metadata hasn't been modified in transit

**Enforcement:** The JSON Schema enforces HTTPS via `pattern: "^https://"` on all URI fields.

**Breaking Change:** Starting with schema version 1.0, all URL fields must use HTTPS exclusively. The previous pattern `^https?://` that allowed HTTP has been replaced with `^https://`.

---

## Core Structure

### Minimal Required File

Every ofero.json file **must** contain these top-level fields:

```json
{
	"language": "en",
	"domain": "yourdomain.com",
	"canonicalUrl": "https://yourdomain.com/.well-known/ofero.json",
	"metadata": {
		"version": "1.0.0",
		"schemaVersion": "ofero-metadata-1.0",
		"lastUpdated": "2025-01-15T10:00:00Z",
		"createdAt": "2025-01-15T10:00:00Z"
	},
	"organization": {
		"legalName": "Your Organization Name",
		"brandName": {
			"default": "Your Brand",
			"translations": {
				"ro": "Your Brand in Romanian",
				"de": "Ihre Marke"
			}
		},
		"entityType": "company",
		"legalForm": "LLC",
		"description": {
			"default": "Brief description of your organization",
			"translations": {
				"ro": "Short description of your organization in Romanian",
				"de": "Kurze Beschreibung Ihrer Organisation"
			}
		},
		"website": "https://yourdomain.com",
		"primaryEmail": "contact@yourdomain.com",
		"primaryPhone": "+1234567890",
		"identifiers": {
			"global": {},
			"primaryIncorporation": {
				"country": "US",
				"registrationNumber": "12345678",
				"taxId": "12-3456789",
				"vatNumber": "US123456789"
			},
			"perCountry": []
		}
	}
}
```

### Full Structure Overview

```json
{
	// REQUIRED ROOT FIELDS
	"language": "en",
	"domain": "yourdomain.com",
	"canonicalUrl": "https://yourdomain.com/.well-known/ofero.json",

	// REQUIRED SECTIONS
	"metadata": {
		/* File versioning, timestamps, update frequency */
	},
	"organization": {
		/* Legal entity info */
	},

	// RECOMMENDED SECTIONS (BUSINESS REGISTRATION)
	"businessClassification": {
		/* Industry taxonomy, MCC/NAICS/SIC codes, operational status */
	},
	"platformAccounts": {
		/* Existing platform IDs (Facebook, Google, LinkedIn, etc.) */
	},

	// RECOMMENDED SECTIONS (GENERAL)
	"locations": [
		/* Physical locations */
	],
	"banking": {
		/* Bank accounts */
	},
	"wallets": [
		/* Blockchain wallets */
	],
	"brandAssets": {
		/* Logos, icons, brand guidelines */
	},
	"catalog": {
		/* Product/service feeds */
	},
	"featured": {
		/* Featured products/services */
	},
	"verification": {
		/* Domain and wallet proofs */
	},
	"security": {
		/* Security contact, security audits */
	},
	"communications": {
		/* Social media, support */
	},
	"ai": {
		/* AI indexing preferences */
	},
	"compliance": {
		/* Regulatory compliance */
	},

	// OPTIONAL SECTIONS
	"recommendations": [
		/* Recommended websites */
	],
	"certificates": [
		/* Certifications */
	],
	"promoCodes": [
		/* Promotional offers */
	],
	"team": {
		/* Leadership, advisors, investors */
	},
	"tokenomics": {
		/* Token information */
	},
	"apis": {
		/* Public APIs */
	},
	"integrations": {
		/* Partner integrations */
	},
	"mutualPartnerships": [
		/* Verified partnerships */
	],
	"analytics": {
		/* Public statistics */
	},
	"roadmap": {
		/* Project roadmap */
	},
	"press": {
		/* Press kit, news */
	},
	"careers": {
		/* Job openings */
	}
}
```

---

## Entity Types

Ofero.json supports multiple entity types with type-specific fields:

### Available Entity Types

| Entity Type   | Description                   | Use For                                   |
| ------------- | ----------------------------- | ----------------------------------------- |
| `company`     | For-profit business           | Corporations, LLCs, startups, SMEs        |
| `foundation`  | Charitable foundation         | Grant-making organizations, endowments    |
| `association` | Member association            | Professional associations, clubs          |
| `ngo`         | Non-governmental organization | Charities, non-profits, advocacy groups   |
| `protocol`    | Web3 protocol                 | DeFi protocols, blockchain networks, DAOs |
| `store`       | Online/offline store          | E-commerce platforms, retail shops        |
| `individual`  | Individual person             | Freelancers, consultants, personal brands |
| `project`     | Generic project               | Open source projects, initiatives         |
| `other`       | Other entity types            | Custom or unlisted types                  |

### Entity-Specific Fields

#### For Companies (`entityType: "company"`)

**Required:**

- `industry` - Business industry sector (automatically enforced by schema conditional validation)

**Not applicable:**

- `socialActivityDomain`
- `nonProfitStatus`

```json
{
	"organization": {
		"entityType": "company",
		"industry": "Fintech / Blockchain / Web3",
		"legalForm": "LLC"
	}
}
```

#### For Associations/NGOs/Foundations

**Required when `entityType` is `"association"`, `"ngo"`, or `"foundation"`:**

- `socialActivityDomain` - Domain of social activity (automatically enforced by schema conditional validation)
- `nonProfitStatus` - Boolean indicating non-profit status (automatically enforced by schema conditional validation)

**Not applicable:**

- `industry`

```json
{
	"organization": {
		"entityType": "association",
		"socialActivityDomain": "Education / Youth Development",
		"nonProfitStatus": true,
		"legalForm": "501(c)(3)"
	}
}
```

**Note:** The JSON Schema uses conditional validation (`allOf` with `if/then` conditions) to automatically enforce these requirements based on entity type. If validation fails, you'll receive a clear error message indicating which fields are required for your entity type.

### Best Practices for Protocols and Projects

Organizations with `entityType` of `"protocol"` or `"project"` **SHOULD** include the following sections for complete metadata:

**Recommended Sections:**

- ✅ **tokenomics** - Token information, supply, distribution, contract addresses
- ✅ **governance** - Governance model, voting mechanisms, proposal systems
- ✅ **nftCollections** - NFT collections (if applicable to your protocol/project)
- ✅ **security.bugBounty** - Bug bounty program details for responsible disclosure
- ✅ **security.securityAudits** - Third-party audit reports (CertiK, Trail of Bits, etc.)
- ✅ **wallets** - Treasury and operational wallets with cryptographic proofs
- ✅ **apis** - Public API endpoints, RPC nodes, indexers
- ✅ **communications.social** - Community channels (Discord, Telegram, Twitter)
- ✅ **team** - Core team, advisors, investors (if not anonymous)
- ✅ **roadmap** - Development roadmap and milestones

**Why These Matter:**

- **Tokenomics**: Protocols need transparent token information for investor/user trust
- **Governance**: Essential for DAO participation and decentralized decision-making
- **Security Audits**: Demonstrate commitment to security and build user confidence
- **Bug Bounties**: Encourage responsible disclosure and show security-first mindset
- **Wallets**: Enable users to verify treasury holdings and on-chain activity
- **APIs**: Enable ecosystem integrations and developer adoption
- **Community Channels**: Foster community engagement and support

**Example Structure for Protocols:**

```json
{
	"organization": {
		"entityType": "protocol",
		"legalForm": "Foundation"
	},
	"tokenomics": {
		"token": {
			"symbol": "TKN",
			"name": "Protocol Token",
			"type": "ERC-20",
			"totalSupply": "1000000000"
		}
	},
	"nftCollections": [
		{
			"name": "Governance NFTs",
			"contractAddress": "0x...",
			"blockchain": "ethereum",
			"standard": "ERC-721"
		}
	],
	"security": {
		"bugBounty": {
			"platform": "immunefi",
			"maxRewardUsd": 1000000
		},
		"securityAudits": [
			{
				"auditor": "CertiK",
				"scope": "smart-contracts",
				"reportUrl": "https://..."
			}
		]
	},
	"wallets": [
		{
			"blockchain": "ethereum",
			"address": "0x...",
			"purpose": "treasury"
		}
	],
	"apis": {
		"public": [
			{
				"name": "Protocol API",
				"baseUrl": "https://api.protocol.io"
			}
		]
	}
}
```

**Note:** While these sections are strongly recommended for protocols and projects, they are NOT enforced by schema validation to allow flexibility for different project types and stages of development. Early-stage projects may not have all sections ready, and that's acceptable.

---

## Field Reference

### Top-Level Required Fields

#### `language`

- **Type:** String
- **Required:** Yes
- **Pattern:** `^[a-z]{2}$`
- **Description:** ISO 639-1 language code for the primary language of this file
- **Examples:** `"en"`, `"ro"`, `"de"`, `"fr"`, `"es"`

#### `domain`

- **Type:** String
- **Required:** Yes
- **Pattern:** `^([a-z0-9]+([\.\-]{1}[a-z0-9]+)*\.[a-z]{2,}|localhost)(:[0-9]{1,5})?$`
- **Description:** Base domain without protocol. Used for domain ownership verification. Must match the domain extracted from canonicalUrl.
- **Examples:**
  - `"example.com"`
  - `"api.example.co.uk"`
  - `"localhost:3000"`
- **Validation:** The domain extracted from `canonicalUrl` must exactly match this field. For example, if `canonicalUrl` is `"https://example.com/.well-known/ofero.json"`, then `domain` must be `"example.com"`.

#### `canonicalUrl`

- **Type:** String (URI)
- **Required:** Yes
- **Format:** Full HTTPS URL ending with `/ofero.json`
- **Pattern:** `^https://.+/ofero\\.json$`
- **Description:** Exact canonical URL where this ofero.json file is located. MUST be HTTPS and MUST end with `/ofero.json` for standardization.
- **Examples:**
  - `"https://yourdomain.com/.well-known/ofero.json"`
  - `"https://example.com/metadata/ofero.json"`
  - `"https://api.example.com/public/ofero.json"`
- **Validation Notes:**
  - ✅ Valid: `https://example.com/.well-known/ofero.json` (standard location)
  - ✅ Valid: `https://subdomain.example.com/ofero.json` (subdomain)
  - ✅ Valid: `https://example.com/v1/metadata/ofero.json` (nested path)
  - ❌ Invalid: `https://example.com/metadata` (must end with `/ofero.json`)
  - ❌ Invalid: `https://example.com/ofero-v1.json` (must be exactly `ofero.json`)
  - ❌ Invalid: `http://example.com/ofero.json` (must be HTTPS)

### Metadata Section

The `metadata` section contains file versioning, timestamps, and update frequency information.

#### `metadata.version`

- **Type:** String
- **Required:** Yes
- **Pattern:** `^\d+\.\d+\.\d+$` (semantic versioning)
- **Description:** Semantic version of THIS ofero.json file (not the schema version). Increment this version whenever you update your file.
- **Versioning Guide:**
  - **MAJOR** (1.x.x): Breaking changes (e.g., business identity change, entity type change)
  - **MINOR** (x.1.x): New sections or fields added (e.g., added businessClassification, new location)
  - **PATCH** (x.x.1): Value updates only (e.g., updated logo URL, changed phone number)
- **Examples:** `"1.0.0"`, `"1.2.3"`, `"2.0.0"`

#### `metadata.schemaVersion`

- **Type:** String
- **Required:** Yes
- **Const:** `"ofero-metadata-1.0"`
- **Description:** Ofero.json schema version. This is constant and only changes when the ofero.json standard itself gets updated.

#### `metadata.lastUpdated`

- **Type:** String
- **Required:** Yes
- **Format:** ISO 8601 date-time
- **Description:** Timestamp when this file was last modified
- **Example:** `"2025-01-15T10:00:00Z"`

#### `metadata.createdAt`

- **Type:** String
- **Required:** No
- **Format:** ISO 8601 date-time
- **Description:** Timestamp when this file was first created
- **Example:** `"2025-01-10T09:00:00Z"`

#### `metadata.createdBy`

- **Type:** String
- **Required:** No
- **Description:** Tool or system that created this file
- **Examples:** `"ofero-cli/1.2.0"`, `"manual/1.0"`, `"oferome-frontend/1.0"`

#### `metadata.updatedBy`

- **Type:** String
- **Required:** No
- **Description:** Tool or system that last updated this file
- **Examples:** `"ofero-cli/1.2.1"`, `"manual/1.0"`

#### `metadata.updateFrequency`

- **Type:** String (enum)
- **Required:** No
- **Values:** `"hourly"`, `"daily"`, `"weekly"`, `"monthly"`, `"quarterly"`, `"rarely"`
- **Description:** How often platforms should re-fetch this file for updates
- **Example:** `"weekly"`

#### `metadata.changelogUrl`

- **Type:** String (URI)
- **Required:** No
- **Pattern:** `^https://`
- **Description:** Optional URL to machine-readable changelog
- **Example:** `"https://example.com/.well-known/ofero-changelog.json"`

#### `keywords`

- **Type:** TranslatableString
- **Required:** No
- **Description:** Keywords for AI search indexing and discovery. Use relevant terms that describe your organization, products, services, and industry. This field is translatable per language.
- **Use Cases:** Improves discoverability by AI systems, search engines, and partner discovery tools
- **Examples:**
  ```json
  {
  	"default": "fintech, blockchain, web3, DeFi, NFT, payments",
  	"translations": {
  		"ro": "financial technology, blockchain, web3, DeFi, NFT, payments",
  		"de": "Finanztechnologie, Blockchain, Web3, DeFi, NFT, Zahlungen"
  	}
  }
  ```

#### `openSource`

- **Type:** Object (see OpenSource schema)
- **Required:** No
- **Description:** Information about open source projects and repositories maintained by the organization
- **See:** [OpenSource Section](#opensource-section)

#### `privacy`

- **Type:** Object (see Privacy schema)
- **Required:** No
- **Description:** Privacy and data handling policies for this ofero.json file
- **See:** [Privacy Section](#privacy-section)

### Organization Section

The `organization` object contains core legal entity information.

#### Required Fields

| Field          | Type               | Description                                 | Example                                          |
| -------------- | ------------------ | ------------------------------------------- | ------------------------------------------------ |
| `legalName`    | string             | Official legal name                         | `"Ofero Network S.A."`                           |
| `brandName`    | string             | Brand or trading name (translatable)        | `"Ofero"`                                        |
| `entityType`   | enum               | Entity type                                 | `"company"`                                      |
| `legalForm`    | string             | Legal form (see legal forms reference)      | `"SA (RO)"`                                      |
| `description`  | string             | Organization description (translatable)     | `"A global fintech platform..."`                 |
| `tagline`      | TranslatableString | One-sentence elevator pitch (max 160 chars) | `"Decentralized exchange for cross-chain swaps"` |
| `website`      | uri                | Primary website URL (HTTPS required)        | `"https://ofero.network"`                        |
| `primaryEmail` | email              | Primary contact email                       | `"contact@ofero.network"`                        |
| `primaryPhone` | string             | Primary phone number (E.164 format)         | `"+40722333444"`                                 |
| `identifiers`  | object             | Legal identifiers                           | See Identifiers section                          |

#### Optional Fields

| Field                  | Type    | Description                                    | Applicable To          |
| ---------------------- | ------- | ---------------------------------------------- | ---------------------- |
| `industry`             | string  | Industry sector                                | Companies only         |
| `socialActivityDomain` | string  | Social activity domain                         | NGOs/Associations only |
| `nonProfitStatus`      | boolean | Non-profit status                              | NGOs/Associations only |
| `legalFormCategory`    | enum    | Legal form category                            | All entities           |
| `hqLocationId`         | string  | Reference to HQ location ID in locations array | All entities           |
| `relatedWebsites`      | array   | Related websites (subdomains, regional sites)  | All entities           |

### Phone Number Validation

All phone number fields in the schema follow the **E.164 international format**:

**Pattern:** `^\\+?[1-9]\\d{1,14}$`

**Requirements:**

- Optional `+` prefix
- Must start with a digit 1-9 (country code cannot start with 0)
- Total length: 2-15 digits (excluding the `+`)

**Examples:**

- ✅ Valid: `"+1234567890"`, `"+40722333444"`, `"12345678"`, `"+491234567890"`
- ❌ Invalid: `"0722333444"` (starts with 0), `"phone"` (non-numeric), `"+12345678901234567"` (too long)

**Fields with E.164 Validation:**

1. `organization.primaryPhone`
2. `locations[].contacts[].phone`
3. `websiteManager.phone`
4. `defaultAccount.phone`
5. `defaultAccount.mobile`
6. `defaultAccount.fax`
7. `team.leadership[].phone`
8. `support.phone`

**Best Practices:**

- Always include the `+` prefix for clarity
- Use international format even for local numbers
- Remove spaces, dashes, and parentheses: use `"+40722333444"` not `"+40 722 333 444"`
- Include country code for all numbers

### Tagline

The `tagline` field is the **most important field for AI summaries**, search results, and partnership discovery tables.

**Guidelines:**

- Maximum 160 characters (enforced by clients, not schema)
- One sentence that captures your value proposition
- Should be compelling and clear for AI systems to understand
- Examples:
  - "Decentralized exchange for cross-chain token swaps"
  - "AI-powered financial planning for small businesses"
  - "Open-source toolkit for blockchain developers"

**Example:**

```json
{
	"organization": {
		"tagline": {
			"default": "Decentralized exchange for cross-chain token swaps",
			"translations": {
				"ro": "Decentralized exchange for cross-chain token swaps in Romanian"
			}
		}
	}
}
```

### Headquarters Location Reference

The `hqLocationId` field allows you to reference your headquarters location from the `locations` array:

```json
{
  "organization": {
    "hqLocationId": "hq-ro-bucharest"
  },
  "locations": [
    {
      "id": "hq-ro-bucharest",
      "type": "headquarters",
      "name": "Headquarters Romania",
      "address": { ... }
    }
  ]
}
```

**Validation:**

- If `hqLocationId` is provided, `locations` array must contain at least 1 item
- The ID should match a location in the `locations` array (enforced by client validation)

### Identifiers Structure

```json
{
	"identifiers": {
		"global": {
			"lei": "5493001KJTIIGC8Y1R12", // Legal Entity Identifier (20 chars)
			"duns": "123456789" // D-U-N-S Number (9 digits)
		},
		"primaryIncorporation": {
			"country": "US", // ISO 3166-1 alpha-2
			"registrationNumber": "C1234567", // Company registration number
			"taxId": "12-3456789", // Tax ID
			"vatNumber": "", // VAT number (if applicable)
			"eori": "" // EORI (EU only, optional)
		},
		"perCountry": [
			// Country-specific identifiers
			{
				"country": "DE",
				"handelsregisterNumber": "HRB 123456",
				"ustId": "DE123456789"
			}
		]
	}
}
```

### Business Classification Section

The `businessClassification` section provides structured information about your business type, industry, and operational status. This is essential for merchant verification, partner discovery, and platform onboarding.

**When to use:** This section is recommended for companies, stores, and protocols. It's optional for foundations, NGOs, and individuals.

**Key fields:**

```json
{
	"businessClassification": {
		"industry": ["technology", "software", "saas"],
		"mccCode": "5734",
		"naicsCode": "518210",
		"sicCode": "7372",
		"primaryProducts": ["software", "cloud-services"],
		"secondaryProducts": ["consulting", "training"],
		"targetMarket": ["B2B", "B2C"],
		"operationalStatus": "active"
	}
}
```

#### Field Definitions

| Field               | Type   | Required | Description                                                                 |
| ------------------- | ------ | -------- | --------------------------------------------------------------------------- |
| `industry`          | array  | Yes      | Hierarchical taxonomy from ofero-json-industries.json (general to specific) |
| `mccCode`           | string | No       | Merchant Category Code (4 digits, ISO 18245)                                |
| `naicsCode`         | string | No       | North American Industry Classification (6 digits)                           |
| `sicCode`           | string | No       | Standard Industrial Classification (4 digits)                               |
| `primaryProducts`   | array  | Yes      | Main product/service categories                                             |
| `secondaryProducts` | array  | No       | Additional offerings                                                        |
| `targetMarket`      | array  | Yes      | One or more of: "B2C", "B2B", "B2G", "C2C"                                  |
| `operationalStatus` | enum   | Yes      | One of: "active", "suspended", "closed", "pending"                          |

#### Industry Taxonomy

The `industry` field uses a hierarchical taxonomy with 158 industries across 4 levels:

**Level 1 - Sectors** (23 total)

- Examples: `"technology"`, `"fintech"`, `"healthcare"`, `"retail"`

**Level 2 - Sub-categories** (48 total)

- Examples: `"software"`, `"blockchain"`, `"ecommerce"`, `"restaurant"`

**Level 3 - Specific Types** (69 total)

- Examples: `"saas"`, `"defi"`, `"marketplace"`, `"fast-food"`

**Level 4 - Niches** (21 total)

- Examples: `"enterprise-saas"`, `"defi-lending"`, `"nft-marketplace"`, `"burger-chain"`

**Usage:**

```json
{
	"industry": ["technology", "software", "saas", "enterprise-saas"]
}
```

The industry path must be valid (each item must be a child of the previous). You can use 1-4 levels depending on specificity.

**Full taxonomy:** Available at `/schemas/ofero-json-industries.json`

#### MCC/NAICS/SIC Codes

These codes help with automatic categorization and payment processor integration:

- **MCC (Merchant Category Code)**: 4-digit code used by payment processors
  - Pattern: `^\d{4}$`
  - Example: `"5814"` (Fast Food Restaurants)

- **NAICS (North American Industry Classification)**: 6-digit code
  - Pattern: `^\d{6}$`
  - Example: `"722513"` (Limited-Service Restaurants)

- **SIC (Standard Industrial Classification)**: 4-digit code
  - Pattern: `^\d{4}$`
  - Example: `"5812"` (Eating Places)

**Validation:** Only format validation (digit count) is enforced. The schema does not validate against official code registries.

#### Target Market

Specify your primary business model:

- **B2C**: Business to Consumer (retail, direct sales)
- **B2B**: Business to Business (enterprise, wholesale)
- **B2G**: Business to Government (government contracts)
- **C2C**: Consumer to Consumer (peer-to-peer marketplaces)

Multiple values allowed for hybrid business models.

#### Operational Status

- **active**: Business is operational
- **suspended**: Temporarily not operating
- **closed**: Permanently closed
- **pending**: Registration/setup in progress

### Platform Accounts Section

The `platformAccounts` section contains existing account IDs across major platforms. This enables automatic account linking during merchant onboarding and reduces duplicate profiles.

**When to use:** Recommended for all entities with existing platform presence.

**Supported platforms:** Facebook, Google, Twitter/X, LinkedIn, Instagram, and extensible for others.

**Example:**

```json
{
	"platformAccounts": {
		"facebook": {
			"pageId": "mcdonalds",
			"businessId": "123456789"
		},
		"google": {
			"businessProfileId": "ChIJN1t_tDeuEmsRUsoyG83frY4",
			"merchantCenterId": "123456",
			"analyticsId": "UA-123456-1"
		},
		"x": {
			"handle": "@McDonalds",
			"userId": "50346649"
		},
		"linkedin": {
			"companyId": "1234567",
			"pageUrl": "https://linkedin.com/company/mcdonalds"
		},
		"instagram": {
			"username": "mcdonalds",
			"businessAccountId": "123456789"
		}
	}
}
```

#### Platform-Specific Fields

**Facebook:**

- `pageId`: Facebook Page username or ID
- `businessId`: Facebook Business Manager ID

**Google:**

- `businessProfileId`: Google Business Profile ID
- `merchantCenterId`: Google Merchant Center ID
- `analyticsId`: Google Analytics ID

**X (Twitter):**

- `handle`: Twitter handle (with @)
- `userId`: Numeric Twitter user ID

**LinkedIn:**

- `companyId`: LinkedIn Company ID
- `pageUrl`: LinkedIn company page URL

**Instagram:**

- `username`: Instagram username
- `businessAccountId`: Instagram Business Account ID

#### Adding Custom Platforms

The schema is extensible. You can add any platform:

```json
{
	"platformAccounts": {
		"tiktok": {
			"username": "yourcompany",
			"verifiedBadge": true
		},
		"youtube": {
			"channelId": "UC1234567890",
			"channelUrl": "https://youtube.com/@yourcompany"
		}
	}
}
```

**Note:** All platform accounts are self-reported. Platforms should independently verify account ownership after import.

**See Also:** For public-facing social media URLs, see the [Communications Section](#communications-section). The `platformAccounts` section is specifically for machine-readable account identifiers used in B2B integrations.

### Platform Accounts vs Social Communications

Both `platformAccounts` and `communications.social` may reference the same platforms, but they serve **distinct purposes**:

**Comparison Table**:

| Aspect           | `platformAccounts`                                   | `communications.social`                       |
| ---------------- | ---------------------------------------------------- | --------------------------------------------- |
| **Purpose**      | B2B account linking, merchant onboarding automation  | Public social presence, user engagement       |
| **Format**       | Structured IDs and identifiers                       | Direct clickable URLs                         |
| **Audience**     | Partner platforms, integrations, automated systems   | Website visitors, customers, AI/LLM discovery |
| **Example**      | `{ pageId: "mcdonalds", businessId: "123456789" }`   | `"https://facebook.com/mcdonalds"`            |
| **Use Case**     | Auto-fill merchant onboarding forms, CRM integration | Community engagement, social media discovery  |
| **Verification** | Platform-verified IDs for account linking            | Public URLs for navigation                    |

**When to Use Both**:

Organizations should populate **both sections** when they have established social media presence:

- **platformAccounts**: Include platform-specific IDs to enable B2B partners to automatically link your accounts during integrations
- **communications.social**: Include full URLs for public discovery and community engagement

**Real-World Example**:

```json
{
	"platformAccounts": {
		"facebook": {
			"pageId": "mcdonalds",
			"businessId": "987654321"
		},
		"instagram": {
			"username": "mcdonalds",
			"businessAccountId": "123456789"
		},
		"linkedin": {
			"companyId": "1234567",
			"pageUrl": "https://linkedin.com/company/mcdonalds"
		}
	},
	"communications": {
		"social": {
			"facebook": "https://facebook.com/mcdonalds",
			"instagram": "https://instagram.com/mcdonalds",
			"linkedin": "https://linkedin.com/company/mcdonalds"
		}
	}
}
```

**Benefits of Populating Both**:

1. **Reduced Manual Entry**: B2B partners can auto-import your account IDs during merchant onboarding
2. **Improved Discoverability**: Users and AI systems can easily find your social profiles
3. **Verified Identity**: Platform IDs help verify account authenticity during integrations
4. **Better Integration**: Seamless onboarding with merchant platforms, affiliate networks, and CRMs

### Locations Section

Physical locations, offices, and branches.

```json
{
	"locations": [
		{
			"id": "hq-ro-bucharest", // Unique identifier
			"type": "headquarters", // headquarters | branch | international-branch | representative-office
			"name": "Ofero Headquarters", // Location name (translatable)
			"address": {
				"street": "123 Main Street",
				"city": "New York",
				"region": "NY",
				"postalCode": "10001",
				"country": "US" // ISO 3166-1 alpha-2
			},
			"coordinates": {
				// Optional GPS coordinates
				"latitude": 44.4268,
				"longitude": 26.1025
			},
			"businessHours": {
				// Optional business hours
				"monday": "09:00-18:00",
				"tuesday": "09:00-18:00",
				"wednesday": "09:00-18:00",
				"thursday": "09:00-18:00",
				"friday": "09:00-17:00",
				"saturday": "Closed",
				"sunday": "Closed",
				"timezone": "Europe/Bucharest" // IANA timezone
			},
			"specialHours": [
				// Optional — overrides businessHours for specific dates or periods
				{
					"date": "2025-12-25",   // Single day: ISO 8601 date (YYYY-MM-DD)
					"name": "Christmas Day",
					"hours": "Closed"       // HH:MM-HH:MM or "Closed"
				},
				{
					"date": "2025-12-31",
					"name": "New Year's Eve",
					"hours": "10:00-15:00"
				},
				{
					"from": "2025-07-01",   // Date range: use from + to
					"to": "2025-08-31",
					"name": "Summer Schedule",
					"hours": "09:00-15:00"
				}
			],
			"contacts": [
				// Optional contact persons
				{
					"name": "John Doe",
					"role": "Office Manager",
					"email": "john@ofero.network",
					"phone": "+40 711 111 111",
					"languages": ["en", "ro"],
					"public": true // Publicly accessible contact
				}
			]
		}
	]
}
```

#### Special Hours (`specialHours`)

`specialHours` is an optional array inside each `Location` object. Entries override the regular `businessHours` schedule for a specific date or date range.

| Field   | Type   | Required | Description |
|---------|--------|----------|-------------|
| `date`  | string (ISO 8601 date) | Required if no `from`/`to` | Single day override (e.g., `"2025-12-25"`). Mutually exclusive with `from`/`to`. |
| `from`  | string (ISO 8601 date) | Required if no `date` | Start of a date-range override. Must be paired with `to`. |
| `to`    | string (ISO 8601 date) | Required if no `date` | End of a date-range override. Must be paired with `from`. |
| `name`  | string | Yes | Human-readable label (e.g., `"Christmas Day"`, `"Summer Schedule"`). |
| `hours` | string | Yes | Hours in `HH:MM-HH:MM` format, or `"Closed"`. Same format as `businessHours` day fields. |

**Rules:**
- Use `date` for public holidays and one-off closures.
- Use `from` + `to` for seasonal or multi-day schedule changes.
- `date` and `from`/`to` are mutually exclusive within the same entry.
- When a date falls within multiple entries, the **first matching entry** takes precedence.
- `specialHours` does not inherit the `timezone` from `businessHours`; the parent location's `businessHours.timezone` is assumed.

### Banking Section

Bank account information for payments.

```json
{
	"banking": {
		"accounts": [
			{
				"currency": "USD", // ISO 4217 currency code
				"iban": "", // IBAN (if applicable, mainly EU)
				"accountNumber": "1234567890", // Account number (if IBAN not applicable)
				"routingNumber": "021000021", // Routing/sort code (US/UK)
				"bankName": "Example Bank",
				"bic": "", // BIC code
				"swift": "EXAMPUS3MXXX", // SWIFT code
				"type": "corporate", // corporate | business | savings
				"country": "US" // ISO 3166-1 alpha-2
			}
		]
	}
}
```

### Wallets Section

Blockchain wallet addresses with extensions for web3-specific metadata.

```json
{
	"wallets": [
		{
			"blockchain": "multiversx", // Blockchain name
			"network": "mainnet", // mainnet | testnet | devnet
			"address": "erd1xxxxx...", // Wallet address
			"purpose": "treasury", // Purpose description
			"extensions": {
				// Blockchain-specific extensions
				"evm": {
					"chainId": 1,
					"explorerUrl": "https://etherscan.io"
				},
				"dex": "Uniswap",
				"liquidityPools": [
					{
						"pair": "OFE/USDC",
						"poolAddress": "0x...",
						"lpToken": "OFE-USDC-LP"
					}
				],
				"chainlink": {
					"feeds": [
						{
							"pair": "OFE/USD",
							"feedAddress": "0x...",
							"network": "ethereum-mainnet"
						}
					]
				}
			}
		}
	]
}
```

### Brand Assets Section

Logos, icons, and brand guidelines.

#### Logo Best Practices

For maximum portal compatibility, provide multiple logo variants:

- **Aspect Ratios**: Provide both **square (1:1)** and **horizontal (wide)** logos
  - Square logos: Ideal for dashboards, avatars, app icons (recommended: 512×512 to 1024×1024 pixels)
  - Horizontal logos: Ideal for headers, partner pages, banners (recommended: 600×60 to 1200×628 pixels)
  - Vertical logos: Rare but supported for specialized layouts (256×512 to 512×1024 pixels)

- **Logo Types**: Different composition variants
  - `full`: Complete logo with icon and text
  - `icon-only`: Symbol/mark without text
  - `text-only`: Text/wordmark only
  - `symbol`: Abstract symbol
  - `wordmark`: Stylized text

- **Color Variants**: Different color schemes for various backgrounds
  - `color`: Full color version (default)
  - `light`: Logo optimized for **dark backgrounds** (light-colored logo on dark theme)
  - `dark`: Logo optimized for **light backgrounds** (dark-colored logo on light theme)
  - `monochrome`: Single color version
  - `white`, `black`, `grayscale`: Specific color versions

- **Primary Logo**: Mark one logo as `"primary": true` to indicate the preferred/default logo

- **File Formats**: PNG with transparent background or SVG (scalable)

- **File Size**: Maximum 512KB per logo file

#### Example: Minimal Logo Setup

```json
{
	"brandAssets": {
		"logos": {
			"vector": [
				{
					"type": "svg",
					"url": "https://ofero.network/logo-square.svg",
					"primary": true, // Primary/default logo
					"aspectRatio": "square", // square | horizontal | vertical
					"logoType": "full", // full | icon-only | text-only | symbol | wordmark
					"colorVariant": "color", // color | light | dark | monochrome | grayscale | white | black
					"width": 512,
					"height": 512,
					"alt": "Ofero Network logo", // Accessibility text
					"license": "© Ofero Network"
				}
			],
			"raster": []
		},
		"icons": {
			"favicon": {
				"ico": "https://ofero.network/favicon.ico",
				"png32x32": "https://ofero.network/favicon-32.png",
				"png192x192": "https://ofero.network/favicon-192.png"
			},
			"appIcons": [
				{
					"url": "https://ofero.network/icon-512.png",
					"resolution": "512x512",
					"purpose": "webapp" // webapp | ios | android
				}
			]
		},
		"brandingKeywords": [
			// Keywords (translatable)
			"ofero",
			"blockchain",
			"fintech"
		],
		"guidelines": {
			"brandBook": "https://ofero.network/brand-guidelines.pdf",
			"colorPalette": {
				"primary": "#FFD530", // Hex color
				"secondary": "#111111",
				"accent": "#FF6A00"
			}
		}
	}
}
```

#### Example: Comprehensive Logo Variants

```json
{
	"brandAssets": {
		"logos": {
			"vector": [
				{
					"type": "svg",
					"url": "https://acme.com/logo-full-color-square.svg",
					"primary": true, // This is the primary logo
					"aspectRatio": "square",
					"logoType": "full",
					"colorVariant": "color",
					"width": 512,
					"height": 512,
					"alt": "Acme Corp full logo"
				},
				{
					"type": "svg",
					"url": "https://acme.com/logo-full-color-horizontal.svg",
					"aspectRatio": "horizontal", // Wide format for headers/banners
					"logoType": "full",
					"colorVariant": "color",
					"width": 600,
					"height": 60,
					"alt": "Acme Corp horizontal logo"
				},
				{
					"type": "svg",
					"url": "https://acme.com/logo-icon-only.svg",
					"aspectRatio": "square",
					"logoType": "icon-only", // Symbol without text
					"colorVariant": "color",
					"width": 512,
					"height": 512,
					"alt": "Acme Corp icon"
				},
				{
					"type": "svg",
					"url": "https://acme.com/logo-full-light.svg",
					"aspectRatio": "square",
					"logoType": "full",
					"colorVariant": "light", // Light-colored logo FOR DARK backgrounds/dark themes
					"width": 512,
					"height": 512,
					"alt": "Acme Corp logo for dark backgrounds"
				},
				{
					"type": "svg",
					"url": "https://acme.com/logo-full-dark.svg",
					"aspectRatio": "square",
					"logoType": "full",
					"colorVariant": "dark", // Dark-colored logo FOR LIGHT backgrounds/light themes
					"width": 512,
					"height": 512,
					"alt": "Acme Corp logo for light backgrounds"
				}
			],
			"raster": [
				{
					"type": "png",
					"url": "https://acme.com/logo-square@2x.png",
					"aspectRatio": "square",
					"logoType": "full",
					"colorVariant": "color",
					"width": 1024,
					"height": 1024,
					"density": "2x", // Retina/HiDPI display
					"alt": "Acme Corp logo retina"
				}
			]
		}
	}
}
```

### Website Manager Section

Information about the website manager, owner, or operator (optional).

```json
{
	"websiteManager": {
		"type": "company", // company | freelancer | agency | individual
		"name": "Example Digital Agency",
		"website": "https://example-agency.com",
		"email": "contact@example-agency.com",
		"phone": "+1 555 123 4567",
		"address": "123 Main St, City, Country" // One-line address
	}
}
```

**All fields are optional**. This section is useful when:

- A third-party company manages the website
- A freelancer maintains the site
- The website operator is different from the organization owner
- You want to provide technical contact information separate from the organization

**Field Descriptions:**

| Field     | Type   | Description                            | Example                                                 |
| --------- | ------ | -------------------------------------- | ------------------------------------------------------- |
| `type`    | enum   | Type of manager                        | `"company"`, `"freelancer"`, `"agency"`, `"individual"` |
| `name`    | string | Company name or individual's full name | `"John Doe"` or `"Acme Web Services"`                   |
| `website` | uri    | Website URL of the manager/company     | `"https://manager-site.com"`                            |
| `email`   | email  | Contact email address                  | `"contact@manager.com"`                                 |
| `phone`   | string | Contact phone number                   | `"+40 722 333 444"`                                     |
| `address` | string | One-line address                       | `"456 Tech Blvd, San Francisco, CA, USA"`               |

### Default Account Section

Default account information for the organization (optional).

```json
{
	"defaultAccount": {
		"email": "accounts@example.com",
		"firstName": "John",
		"lastName": "Doe",
		"companyName": "Example Corp",
		"address": {
			// Structured address
			"street": "123 Business Ave",
			"city": "San Francisco",
			"region": "California",
			"postalCode": "94101",
			"country": "US"
		},
		"addressOneLine": "123 Business Ave, San Francisco, CA 94101, USA",
		"phone": "+1 555 123 4567",
		"mobile": "+1 555 987 6543",
		"fax": "+1 555 123 4568",
		"logoUrl": "https://example.com/logo-512.png",
		"logoThumbUrl": "https://example.com/logo-128.png",
		"vatNumber": "US123456789",
		"taxId": "12-3456789",
		"website": "https://example.com",
		"timezone": "America/Los_Angeles",
		"language": "en",
		"currency": "USD"
	}
}
```

**Purpose and Use Cases:**

The `defaultAccount` section provides standardized contact and business information that can be used for:

1. **Form Pre-filling**: Partner websites can auto-populate registration and onboarding forms
2. **B2B Integrations**: Automated merchant onboarding with verified business details
3. **Invoice Generation**: Default billing contact and tax information
4. **API Integrations**: Standardized account data for third-party systems
5. **CRM Systems**: Automatic contact creation with complete information
6. **Affiliate Networks**: Quick merchant registration with verified details

**All fields are optional**. Include only what you're comfortable sharing publicly.

**Field Descriptions:**

| Field            | Type   | Description                              | Example                                    |
| ---------------- | ------ | ---------------------------------------- | ------------------------------------------ |
| `email`          | email  | Default business contact email           | `"accounts@company.com"`                   |
| `firstName`      | string | First name of primary contact            | `"John"`                                   |
| `lastName`       | string | Last name of primary contact             | `"Doe"`                                    |
| `companyName`    | string | Company/DBA name                         | `"Example Corp"`                           |
| `address`        | object | Structured address (see Address schema)  | See example above                          |
| `addressOneLine` | string | Single-line address format               | `"123 Main St, City, State, ZIP, Country"` |
| `phone`          | string | Primary phone number                     | `"+1 555 123 4567"`                        |
| `mobile`         | string | Mobile phone number                      | `"+1 555 987 6543"`                        |
| `fax`            | string | Fax number                               | `"+1 555 123 4568"`                        |
| `logoUrl`        | uri    | Company logo URL (512x512+ recommended)  | `"https://example.com/logo.png"`           |
| `logoThumbUrl`   | uri    | Thumbnail logo URL (128x128 recommended) | `"https://example.com/logo-thumb.png"`     |
| `vatNumber`      | string | VAT number for invoicing                 | `"GB123456789"`, `"DE123456789"`           |
| `taxId`          | string | Tax ID for invoicing                     | `"12-3456789"`                             |
| `website`        | uri    | Primary website URL                      | `"https://example.com"`                    |
| `timezone`       | string | IANA timezone                            | `"Europe/Bucharest"`                       |
| `language`       | string | Preferred language (ISO 639-1)           | `"en"`, `"ro"`, `"de"`                     |
| `currency`       | string | Preferred currency (ISO 4217)            | `"EUR"`, `"USD"`, `"GBP"`                  |

**Best Practices:**

1. **Logo Recommendations**:
   - Use square format (1:1 aspect ratio)
   - PNG with transparent background preferred
   - `logoUrl`: 512x512px or higher for high-quality display
   - `logoThumbUrl`: 128x128px for thumbnails and icons

2. **Address Format**:
   - Provide both structured (`address`) and one-line (`addressOneLine`) formats
   - Structured format better for form filling
   - One-line format better for display and labels

3. **Contact Information**:
   - Use E.164 format for phone numbers when possible
   - Provide both general contact and specific contact person details

4. **Privacy Considerations**:
   - Only include information you want publicly accessible
   - Consider using generic business contacts rather than personal details
   - Review data protection regulations (GDPR, CCPA) before publishing

### Team Section

Leadership, advisors, and investors.

```json
{
	"team": {
		"leadership": [
			{
				"name": "Stefan Olaru",
				"role": "CEO & CTO", // Translatable
				"photo": "https://ofero.network/team/stefan.jpg", // 3:4 aspect ratio recommended
				"photoThumb": "https://ofero.network/team/stefan-thumb.jpg",
				"bio": "Stefan is a blockchain entrepreneur...", // Translatable
				"department": "Executive", // Translatable
				"email": "stefan@ofero.network",
				"phone": "+40 711 111 111",
				"social": {
					"linkedin": "https://linkedin.com/in/stefan",
					"twitter": "https://twitter.com/stefan",
					"github": "https://github.com/stefan",
					"website": "https://stefanolaru.com"
				},
				"expertise": ["blockchain", "fintech", "product"]
			}
		],
		"advisors": [
			/* Same structure as leadership */
		],
		"investors": [
			{
				"name": "Example VC",
				"type": "VC", // VC | Angel | Strategic
				"photo": "https://example-vc.com/logo.png",
				"bio": "Venture capital firm...", // Translatable
				"website": "https://example-vc.com",
				"social": {
					"linkedin": "https://linkedin.com/company/example-vc"
				}
			}
		]
	}
}
```

### Tokenomics Section

Token and cryptocurrency information (for web3 entities).

```json
{
	"tokenomics": {
		"token": {
			"symbol": "OFE",
			"name": "Ofero Token", // Translatable
			"type": "ESDT", // ESDT | ERC-20 | BEP-20 | SPL
			"blockchain": "multiversx",
			"network": "mainnet",
			"identifier": "OFE-a1b2c3", // Token ID or contract address
			"decimals": 18,
			"totalSupply": "1000000000", // String for large numbers
			"circulatingSupply": "500000000",
			"contractAddresses": {
				// Multiple contract addresses
				"main": "erd1qqqqqqqqqqqqqpgq...",
				"staking": "erd1qqqqqqqqqqqqqpgq...",
				"rewards": "erd1qqqqqqqqqqqqqpgq..."
			},
			"explorerUrl": "https://explorer.multiversx.com/tokens/OFE-a1b2c3"
		}
	}
}
```

### NFT Collections Section

Information about NFT collections owned or operated by the organization.

```json
{
	"nftCollections": [
		{
			"name": "Example NFT Collection",
			"contractAddress": "0x1234567890abcdef1234567890abcdef12345678",
			"blockchain": "ethereum",
			"network": "mainnet",
			"standard": "ERC-721",
			"totalSupply": 10000,
			"marketplaceUrl": "https://opensea.io/collection/example-nft",
			"explorerUrl": "https://etherscan.io/token/0x1234567890abcdef1234567890abcdef12345678"
		}
	]
}
```

**Field Descriptions:**

| Field             | Required | Type    | Description                                                   |
| ----------------- | -------- | ------- | ------------------------------------------------------------- |
| `name`            | Yes      | string  | Collection name (e.g., "Bored Ape Yacht Club", "CryptoPunks") |
| `contractAddress` | Yes      | string  | Smart contract address for the NFT collection                 |
| `blockchain`      | Yes      | string  | Blockchain name (ethereum, polygon, solana, multiversx, etc.) |
| `network`         | No       | enum    | Network type (mainnet, testnet, devnet)                       |
| `standard`        | No       | enum    | NFT standard (ERC-721, ERC-1155, ESDT-NFT, SPL, other)        |
| `totalSupply`     | No       | integer | Total supply of NFTs in the collection                        |
| `marketplaceUrl`  | No       | uri     | Marketplace URL (OpenSea, Magic Eden, etc.)                   |
| `explorerUrl`     | No       | uri     | Blockchain explorer URL                                       |

**NFT Standards:**

- **ERC-721**: Ethereum Non-Fungible Token standard (one unique token per ID)
- **ERC-1155**: Ethereum Multi-Token standard (mix of fungible and non-fungible tokens)
- **ESDT-NFT**: MultiversX ESDT Non-Fungible Token standard
- **SPL**: Solana Program Library token standard
- **other**: Custom or other NFT standards

**Use Cases:**

- NFT projects showcasing their collections
- Organizations with branded NFT drops
- DAOs with membership/governance NFTs
- Metaverse projects with land/asset NFTs
- Gaming projects with in-game asset NFTs
- Art galleries with digital art collections

**Example (MultiversX NFT):**

```json
{
	"nftCollections": [
		{
			"name": "Elrond Apes",
			"contractAddress": "EAPES-a1b2c3",
			"blockchain": "multiversx",
			"network": "mainnet",
			"standard": "ESDT-NFT",
			"totalSupply": 10000,
			"marketplaceUrl": "https://xoxno.com/collection/EAPES-a1b2c3",
			"explorerUrl": "https://explorer.multiversx.com/collections/EAPES-a1b2c3"
		}
	]
}
```

**Example (Solana NFT):**

```json
{
	"nftCollections": [
		{
			"name": "Solana Monkeys",
			"contractAddress": "SMBtHCCC6RYRutFEPb4gZqeBLUZbMNhRKaMKZZLHi7W",
			"blockchain": "solana",
			"network": "mainnet",
			"standard": "SPL",
			"totalSupply": 5000,
			"marketplaceUrl": "https://magiceden.io/marketplace/solana_monkey_business",
			"explorerUrl": "https://solscan.io/token/SMBtHCCC6RYRutFEPb4gZqeBLUZbMNhRKaMKZZLHi7W"
		}
	]
}
```

### Mutual Partnerships Section

Verified partnerships with other organizations that also have ofero.json files.

```json
{
	"mutualPartnerships": [
		{
			"name": "Partner Organization Name",
			"website": "https://partner.com",
			"oferoJson": "https://partner.com/.well-known/ofero.json", // MUST match partner's fileLocation exactly
			"description": "Strategic partnership for...",
			"status": "verified" // pending-verification | verified | unreciprocated-invalid
		}
	]
}
```

**Partnership Verification Rules:**

1. **URL Matching Requirement**: The `oferoJson` URL you specify **must exactly match** the partner's `fileLocation` field in their ofero.json file.

2. **Reciprocal Listing**: For a partnership to be `"verified"`, both organizations must list each other in their respective `mutualPartnerships` arrays.

3. **Status Values**:
   - `"pending-verification"`: Partnership not yet verified (default status). Waiting for reciprocal listing or validation.
   - `"verified"`: Partnership confirmed (both organizations list each other, URLs match exactly). Validators should auto-upgrade status to "verified" when reciprocity is detected.
   - `"unreciprocated-invalid"`: Partner does not list you back, or URLs don't match exactly

4. **Default Behavior**: New partnerships are automatically set to `"pending-verification"` status. Automated validators can upgrade to `"verified"` once both parties list each other with matching URLs.

**Example Validation Flow:**

Organization A (`https://company-a.com/.well-known/ofero.json`):

```json
{
	"fileLocation": "https://company-a.com/.well-known/ofero.json",
	"mutualPartnerships": [
		{
			"name": "Company B",
			"oferoJson": "https://company-b.com/.well-known/ofero.json",
			"status": "verified"
		}
	]
}
```

Organization B (`https://company-b.com/.well-known/ofero.json`):

```json
{
	"fileLocation": "https://company-b.com/.well-known/ofero.json",
	"mutualPartnerships": [
		{
			"name": "Company A",
			"oferoJson": "https://company-a.com/.well-known/ofero.json", // Matches Company A's fileLocation ✓
			"status": "verified"
		}
	]
}
```

### Communications Section

Social media, messaging, and support channels.

**See Also:** For platform-specific account IDs used in merchant onboarding and B2B integrations, see the [Platform Accounts Section](#platform-accounts-section). This section focuses on public URLs for user navigation and community engagement.

#### Format Guidelines

**IMPORTANT:** Always provide **complete URLs**, not just usernames or handles:

- **Facebook:** Full URL required
  - ✅ `https://facebook.com/yourpage` (vanity URL)
  - ✅ `https://facebook.com/profile.php?id=123456` (numeric ID)
  - ❌ `yourpage` (username only - not valid)

- **Instagram:** Full URL required
  - ✅ `https://instagram.com/username`
  - ❌ `@username` (handle only - not valid)

- **WhatsApp:** Use `wa.me` link format
  - ✅ `https://wa.me/1234567890` (phone with country code, no spaces)
  - ❌ `+1 234 567 890` (formatted number - not a URL)

- **X (Twitter):** Full URL required
  - ✅ `https://x.com/username` or `https://twitter.com/username`
  - ❌ `@username` (handle only - not valid)

- **LinkedIn:** Full URL required
  - ✅ `https://linkedin.com/company/name` (company pages)
  - ✅ `https://linkedin.com/in/username` (personal profiles)
  - ❌ `company/name` (path only - not valid)

- **YouTube:** Full URL required
  - ✅ `https://youtube.com/@channelname` (handle format)
  - ✅ `https://youtube.com/c/channelname` (custom URL)
  - ✅ `https://youtube.com/channel/UC...` (channel ID)
  - ❌ `@channelname` (handle only - not valid)

- **Telegram:** Full URL required
  - ✅ `https://t.me/channelname`
  - ❌ `@channelname` (handle only - not valid)
  - Note: Handles can be stored separately in `messaging.telegram.handle`

- **Discord:** Full URL required
  - ✅ `https://discord.gg/invitecode`
  - ❌ `invitecode` (code only - not valid)

#### Example

```json
{
	"communications": {
		"social": {
			"website": "https://ofero.network",
			"email": "contact@ofero.network",
			"twitter": "https://twitter.com/oferonetwork",
			"linkedin": "https://linkedin.com/company/oferonetwork",
			"telegram": "https://t.me/oferonetwork",
			"discord": "https://discord.gg/oferonetwork",
			"youtube": "https://youtube.com/@oferonetwork",
			"facebook": "https://facebook.com/oferonetwork",
			"instagram": "https://instagram.com/oferonetwork",
			"reddit": "https://reddit.com/r/oferonetwork",
			"blog": "https://blog.ofero.network",
			"whitepaper": "https://ofero.network/whitepaper.pdf",
			"coinmarketcap": "https://coinmarketcap.com/currencies/ofero",
			"coingecko": "https://coingecko.com/en/coins/ofero"
		},
		"messaging": {
			"whatsapp": {
				"number": "+12345678900", // Phone with country code (no spaces)
				"link": "https://wa.me/12345678900", // wa.me link format
				"businessAccount": true
			},
			"telegram": {
				"handle": "@oferonetwork", // Handle for reference
				"channelUrl": "https://t.me/oferonetwork" // Full URL for navigation
			}
		},
		"support": {
			"email": "support@ofero.network",
			"portal": "https://support.ofero.network",
			"phone": "+1 234 567 8900",
			"languages": ["en", "es", "de"] // ISO 639-1 codes
		}
	}
}
```

### AI Settings Section

Control how AI systems index and use your metadata.

```json
{
	"ai": {
		"indexing": {
			"allow": true, // Allow AI indexing
			"allowTraining": false // Allow AI training on this data
		},
		"preferredSources": [
			// Preferred URLs for AI to prioritize
			"https://ofero.network",
			"https://docs.ofero.network"
		],
		"contentNotes": "This JSON describes official corporate metadata..." // Translatable
	}
}
```

### Verification Section

Domain and wallet ownership verification.

```json
{
	"verification": {
		"domain": "ofero.network", // Primary domain (without protocol)
		"walletProof": {
			"message": "I confirm ownership of the wallets listed...",
			"signature": "0xSIGNATURE-HERE",
			"algorithm": "eip-191" // eip-191 | ed25519
		},
		"dns": [
			// DNS verification records
			{
				"record": "ofero-verification",
				"value": "d41d8cd98f00b204e9800998ecf8427e"
			}
		],
		"lastVerified": "2025-01-15T10:00:00Z"
	}
}
```

### OpenSource Section

Information about open source projects and repositories maintained by the organization.

```json
{
	"openSource": {
		"repositories": [
			{
				"url": "https://github.com/oferome/ofero-json",
				"license": "MIT",
				"description": {
					"default": "Universal metadata standard for organizations",
					"translations": {
						"ro": "Universal metadata standard for organizations in Romanian"
					}
				},
				"stars": 150
			},
			{
				"url": "https://github.com/oferome/oferome-frontend",
				"license": "Apache-2.0",
				"description": {
					"default": "SvelteKit frontend application"
				}
			}
		]
	}
}
```

**Field Descriptions:**

| Field         | Required | Type               | Description                                      |
| ------------- | -------- | ------------------ | ------------------------------------------------ |
| `url`         | Yes      | uri (HTTPS)        | Repository URL (GitHub, GitLab, Bitbucket, etc.) |
| `license`     | Yes      | string             | Open source license identifier                   |
| `description` | No       | TranslatableString | Repository description                           |
| `stars`       | No       | integer            | Star/like count for popularity indication        |

**Common License Identifiers:**

- `"MIT"` - MIT License
- `"Apache-2.0"` - Apache License 2.0
- `"GPL-3.0"` - GNU General Public License v3.0
- `"BSD-3-Clause"` - BSD 3-Clause License
- `"ISC"` - ISC License
- `"AGPL-3.0"` - GNU Affero General Public License v3.0

**Use Cases:**

- Showcase open source contributions
- Attract contributors and developers
- Demonstrate transparency and community engagement
- Enable automated discovery of organization's open source projects

### Privacy Section

Privacy and data handling policies for the ofero.json file and organization.

```json
{
	"privacy": {
		"dataClassification": "public",
		"gdprCompliance": true,
		"dataRetentionPeriod": "P1Y"
	}
}
```

**Field Descriptions:**

| Field                 | Required | Type    | Description                                               |
| --------------------- | -------- | ------- | --------------------------------------------------------- |
| `dataClassification`  | No       | enum    | Data classification level for this file                   |
| `gdprCompliance`      | No       | boolean | Whether organization is GDPR compliant                    |
| `dataRetentionPeriod` | No       | string  | How long this data should be retained (ISO 8601 duration) |

**Data Classification Levels:**

- `"public"` - Freely shareable information, no restrictions
- `"internal"` - Organization-internal use only
- `"confidential"` - Need-to-know basis, restricted access
- `"restricted"` - Highest security level, very limited access

**Data Retention Period Format (ISO 8601):**

The `dataRetentionPeriod` field uses ISO 8601 duration format:

- **Format:** `P[n]Y[n]M[n]W[n]DT[n]H[n]M[n]S`
- **Pattern:** `^P(\\d+Y)?(\\d+M)?(\\d+W)?(\\d+D)?(T(\\d+H)?(\\d+M)?(\\d+S)?)?$`

**Examples:**

- `"P1Y"` - 1 year
- `"P6M"` - 6 months
- `"P30D"` - 30 days
- `"P1Y6M"` - 1 year and 6 months
- `"PT2H"` - 2 hours
- `"P7D"` - 7 days (1 week)

**Use Cases:**

- Communicate data handling practices
- Indicate GDPR compliance status
- Set expectations for data freshness and retention
- Help partners understand data sensitivity level

### Security Section

Security contact, responsible disclosure, and security audits.

```json
{
	"security": {
		"pgp": {
			"publicKey": "-----BEGIN PGP PUBLIC KEY BLOCK-----\n..."
		},
		"securityEmail": "security@ofero.network",
		"responsibleDisclosureUrl": "https://ofero.network/security",
		"securityAudits": [
			{
				"auditor": "CertiK",
				"date": "2024-12-15",
				"reportUrl": "https://certik.com/projects/oferome-audit-report",
				"scope": "smart-contracts",
				"findings": 3,
				"criticalFindingsResolved": true
			},
			{
				"auditor": "Trail of Bits",
				"date": "2024-11-20",
				"reportUrl": "https://trailofbits.com/reports/oferome-audit",
				"scope": "full",
				"findings": 12,
				"criticalFindingsResolved": true
			}
		]
	}
}
```

**Security Audits Field Descriptions:**

| Field                      | Required | Type        | Description                                         |
| -------------------------- | -------- | ----------- | --------------------------------------------------- |
| `auditor`                  | Yes      | string      | Auditor name or organization                        |
| `date`                     | Yes      | date        | Audit completion date (ISO 8601 format: YYYY-MM-DD) |
| `reportUrl`                | Yes      | uri (HTTPS) | URL to the public audit report                      |
| `scope`                    | Yes      | enum        | Audit scope/type                                    |
| `findings`                 | No       | integer     | Total number of findings/issues discovered          |
| `criticalFindingsResolved` | No       | boolean     | Whether all critical findings have been resolved    |

**Audit Scope Types:**

- `"smart-contracts"` - Blockchain smart contract security audit
- `"full"` - Comprehensive security audit (all systems)
- `"economic"` - Economic/tokenomics security review
- `"infrastructure"` - Infrastructure and deployment security
- `"api"` - API security audit
- `"web-app"` - Web application security (frontend/backend)

**Common Auditors:**

- CertiK, Hacken, Quantstamp (blockchain/smart contracts)
- Trail of Bits, NCC Group, Bishop Fox (general security)
- OpenZeppelin, ConsenSys Diligence (smart contracts)

**Use Cases:**

- Build trust with partners and users
- Demonstrate security commitment
- Showcase third-party validation
- Provide transparency about security posture
- Required for many partner integrations and compliance

#### Bug Bounty Programs

Organizations can disclose their bug bounty programs for security researchers:

```json
{
	"security": {
		"bugBounty": {
			"platform": "immunefi",
			"url": "https://immunefi.com/bounty/yourproject",
			"maxRewardUsd": 1000000,
			"inScope": ["smart contracts", "web app", "mobile app"]
		}
	}
}
```

**Field Descriptions:**

| Field          | Type    | Description                                                                             |
| -------------- | ------- | --------------------------------------------------------------------------------------- |
| `platform`     | enum    | Bug bounty platform (immunefi, hackenproof, cantina, bugcrowd, hackerone, custom, none) |
| `url`          | uri     | Direct link to bug bounty page (HTTPS required)                                         |
| `maxRewardUsd` | integer | Maximum reward in USD (e.g., 1000000 for $1M)                                           |
| `inScope`      | array   | List of in-scope assets (short descriptions)                                            |

**Platforms:**

- **immunefi** - Leading web3 bug bounty platform, specialized in smart contract security
- **hackenproof** - Blockchain security bug bounty platform
- **cantina** - Smart contract security competitions and audits
- **bugcrowd** - Traditional bug bounty platform for web/mobile/API security
- **hackerone** - Enterprise bug bounty and vulnerability disclosure platform
- **custom** - Self-hosted or custom bug bounty program
- **none** - No active bug bounty program (default)

**Example (Web3 Protocol):**

```json
{
	"security": {
		"bugBounty": {
			"platform": "immunefi",
			"url": "https://immunefi.com/bounty/uniswap",
			"maxRewardUsd": 2250000,
			"inScope": ["smart contracts", "governance contracts"]
		}
	}
}
```

**Example (Traditional Web App):**

```json
{
	"security": {
		"bugBounty": {
			"platform": "hackerone",
			"url": "https://hackerone.com/yourcompany",
			"maxRewardUsd": 100000,
			"inScope": ["web app", "API", "mobile app", "infrastructure"]
		}
	}
}
```

**Example (No Bug Bounty):**

```json
{
	"security": {
		"bugBounty": {
			"platform": "none"
		}
	}
}
```

**Best Practices:**

- Set realistic `maxRewardUsd` based on asset value and risk
- Clearly define `inScope` assets to guide researchers
- Keep URL updated if you change platforms
- Consider starting with `platform: "custom"` if self-hosting
- Include responsible disclosure contact even without formal program

### Compliance Section

Regulatory compliance and policy information.

```json
{
	"compliance": {
		"jurisdictions": [
			{
				"country": "US", // ISO 3166-1 alpha-2
				"regulator": "SEC / State Registry",
				"licenseType": "Local registration",
				"licenseId": "C1234567"
			}
		],
		"dataProtection": {
			"gdprRepresentative": "Ofero EU Service Center",
			"dpoEmail": "privacy@ofero.network"
		},
		"policies": {
			"privacyPolicyUrl": "https://ofero.network/legal/privacy",
			"termsOfServiceUrl": "https://ofero.network/legal/terms",
			"cookiePolicyUrl": "https://ofero.network/legal/cookies"
		}
	}
}
```

### Extensions Section

Schema version and additional notes.

```json
{
	"extensions": {
		"notes": "Additional notes about this file...", // Translatable
		"schemaVersion": "ofero-metadata-1.0" // REQUIRED: Schema version
	}
}
```

---

## Multi-Language Support

Ofero.json supports translations through the **TranslatableString structure** — inline translations embedded directly within the main file. There is no requirement to use English as the primary language. A company writes the `default` value in whichever language they operate in, as declared by the top-level `language` field.

Consumers that need a specific language should read `language` from the base file, then look up their preferred language in `translations`. If not found, fall back to `default`.

### TranslatableString Structure

**Breaking Change:** The TranslatableString definition has been redesigned to use an object with `default` and `translations` properties.

**New Structure:**

```json
{
	"default": "Text in the company's primary language (required)",
	"translations": {
		"en": "English translation here",
		"de": "Text auf Deutsch",
		"fr": "Texte en français",
		"en-US": "American English text"
	}
}
```

**Key Features:**

- **Required `default` field**: Always present, written in the language declared by the top-level `language` field
- **Optional `translations` object**: Contains translations into other languages/locales
- **Locale support**: Supports both language codes (`en`, `de`) and locale codes (`en-US`, `de-DE`)
- **Pattern:** `^[a-z]{2}(-[A-Z]{2})?$` (e.g., `en`, `en-US`, `de`, `de-DE`)

**Example Usage:**

```json
{
	"organization": {
		"brandName": {
			"default": "Ofero"
		},
		"description": {
			"default": "A global fintech platform connecting businesses",
			"translations": {
				"de": "Eine globale Fintech-Plattform zur Vernetzung von Unternehmen",
				"fr": "Une plateforme fintech mondiale connectant les entreprises"
			}
		}
	},
	"keywords": {
		"default": "fintech, blockchain, web3, DeFi, payments",
		"translations": {
			"de": "Finanztechnologie, Blockchain, Web3, DeFi, Zahlungen",
			"fr": "technologie financière, blockchain, web3, DeFi, paiements"
		}
	}
}
```

**Migration from Old Structure:**

**Old Structure (Deprecated):**

```json
{
	"brandName": {
		"en": "Ofero",
		"ro": "Ofero"
	}
}
```

**New Structure (Required):**

```json
{
	"brandName": {
		"default": "Ofero",
		"translations": {
			"ro": "Ofero"
		}
	}
}
```

### Translatable Fields

Only these fields use the TranslatableString structure:

**Top-Level:**

- `keywords` - Keywords for AI search indexing

**Organization:**

- `description` - Organization description
- `brandName` - Brand or trading name

**Locations:**

- `name` - Location name

**Brand Assets:**

- `brandingKeywords` - Branding keywords (deprecated, use top-level `keywords` instead)

**OpenSource:**

- `repositories[].description` - Repository description

**Featured:**

- `products[].name` - Product name
- `services[].name` - Service name

**Team:**

- `leadership[].role` - Leadership role/title
- `leadership[].bio` - Biography
- `leadership[].department` - Department name
- `advisors[].role` - Advisor role/title
- `advisors[].bio` - Biography
- `advisors[].department` - Department name
- `investors[].bio` - Investor biography

**Tokenomics:**

- `token.name` - Token name

**Analytics:**

- `stats` (custom fields) - Custom statistics

**Roadmap:**

- `milestones[].title` - Milestone title

**Press:**

- `latestNews[].title` - News article title

**Careers:**

- `culture` - Company culture description

**AI:**

- `contentNotes` - Content interpretation notes

**Extensions:**

- `notes` - Additional notes

### Non-Translatable Fields

These fields should **never** be translated (same across all languages):

- IDs, codes, identifiers
- URLs, email addresses
- Phone numbers
- Technical data (contract addresses, API endpoints)
- Dates, numbers
- Country/currency codes

---

## Legal Forms Reference

Ofero.json supports 120+ legal forms across 13+ countries. See the complete list in [`ofero-json-legal-forms.json`](../../static/schemas/ofero-json-legal-forms.json).

### Categories

| Category            | Description                    | Examples                           |
| ------------------- | ------------------------------ | ---------------------------------- |
| `nonprofit`         | Non-profit organizations       | NPO, 501(c)(3), Registered Charity |
| `foundation`        | Foundations and trusts         | Foundation, Stiftung, Trust        |
| `ngo`               | Non-governmental organizations | NGO, ONG                           |
| `cooperative`       | Cooperative societies          | Cooperative, SCOP                  |
| `social_enterprise` | Social enterprises             | CIC, B Corporation, PBC            |
| `limited_liability` | Limited liability companies    | LLC, GmbH, Ltd, SRL                |
| `joint_stock`       | Stock corporations             | Inc, AG, SA, PLC                   |
| `partnership`       | Partnerships                   | LP, LLP, Partnership               |
| `sole_proprietor`   | Sole proprietorships           | Sole Proprietorship, PFA, II       |
| `custom`            | Custom or other forms          | Other                              |

### Supported Countries

- **International (INTL)** - Generic international forms
- **United States (US)** - LLC, Inc, C Corp, S Corp, LP, LLP, 501(c)(3), etc.
- **United Kingdom (GB)** - Ltd, PLC, LLP, CIC, Charity
- **Germany (DE)** - GmbH, AG, UG, Verein, Stiftung, gGmbH
- **France (FR)** - SARL, SA, SAS, EURL, Association Loi 1901, Fondation
- **Netherlands (NL)** - BV, NV, Stichting, Vereniging
- **Canada (CA)** - Inc, Corp, Ltd, Registered Charity
- **Australia (AU)** - Pty Ltd, Ltd, Incorporated Association
- **Switzerland (CH)** - Verein, Stiftung
- **Spain (ES)** - Asociación, Fundación
- **Italy (IT)** - Associazione, Fondazione, ONLUS
- **Romania (RO)** - SRL, SA, PFA, II, Asociație, Fundație, ONG
- **Sweden (SE)** - AB

### Usage

```json
{
	"organization": {
		"legalForm": "LLC", // Use exact value from legal-forms.json
		"legalFormCategory": "limited_liability" // Use category from legal-forms.json
	}
}
```

---

## Validation Requirements

### Validation Levels

Ofero.json supports three validation levels:

#### Basic (Minimum Required)

- Valid JSON structure
- Required top-level fields present (`version`, `language`, `generatedAt`, `generatedBy`, `fileLocation`, `organization`, `extensions`)
- `fileLocation` valid HTTPS URL (enforced with `^https://` pattern)
- `organization.legalName` non-empty
- `organization.website` valid HTTPS URL
- `organization.entityType` valid enum
- `organization.primaryPhone` matches E.164 pattern (if provided)
- `extensions.schemaVersion` = `"ofero-metadata-1.0"`
- TranslatableString fields have required `default` property

#### Moderate (Recommended)

All basic validation plus:

- Required fields per section
- Email format validation (RFC 5322)
- All URL fields enforce HTTPS pattern (`^https://`)
- Array types correct
- Enum values match allowed options
- ISO 8601 date formats (`generatedAt`, `updatedAt`)
- E.164 phone number validation (all 8 phone fields)
- ISO country codes (ISO 3166-1 alpha-2)
- ISO currency codes (ISO 4217)
- ISO language codes (ISO 639-1) and locale codes (e.g., `en-US`, `ro-RO`)
- ISO 8601 duration format for `privacy.dataRetentionPeriod`
- `mutualPartnerships.oferoJson` matches HTTPS URL format
- Conditional validation: If `entityType="company"` then `industry` required
- Conditional validation: If `entityType` in ["association","ngo","foundation"] then `socialActivityDomain` and `nonProfitStatus` required
- TranslatableString `translations` keys match locale pattern `^[a-z]{2}(-[A-Z]{2})?$`

#### Strict (Optional)

All moderate validation plus:

- Blockchain address format validation per blockchain type
- Bank account format validation per country (IBAN, routing numbers)
- Business hours format validation
- GPS coordinates range validation (latitude: -90 to 90, longitude: -180 to 180)
- E.164 strict phone number validation with country code verification
- IBAN checksum validation
- LEI format validation (20 characters, alphanumeric)
- DUNS format validation (9 digits)
- Security audit date validation (not in future)
- Cross-validation: Fetch partner's ofero.json and verify `mutualPartnerships` reciprocity
- Cross-validation: Verify `mutualPartnerships.oferoJson` exactly matches partner's `fileLocation`
- Cross-validation: Verify all HTTPS URLs are accessible (200 OK status)

### Recommended Validation Level

**Moderate** validation is recommended for most implementations. It catches common errors while remaining flexible for extensions.

### Validation Tools

- **JSON Schema Validator**: Use the official JSON Schema at `/schemas/ofero-json-schema.json`
- **Online Validator**: [Coming Soon] Web-based validation tool
- **CLI Tool**: [Coming Soon] Command-line validator

---

## Examples

### Example 1: Minimal Company

```json
{
	"version": "1.0",
	"language": "en",
	"generatedAt": "2025-01-15T10:00:00Z",
	"generatedBy": "manual/1.0",
	"fileLocation": "https://example-tech.com/.well-known/ofero.json",
	"organization": {
		"legalName": "Example Tech LLC",
		"brandName": {
			"default": "ExampleTech"
		},
		"entityType": "company",
		"legalForm": "LLC",
		"legalFormCategory": "limited_liability",
		"description": {
			"default": "A technology company specializing in software solutions"
		},
		"industry": "Technology / Software",
		"website": "https://example-tech.com",
		"primaryEmail": "contact@example-tech.com",
		"primaryPhone": "+15551234567",
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

### Example 2: Non-Profit Association

```json
{
	"version": "1.0",
	"language": "en",
	"generatedAt": "2025-01-15T10:00:00Z",
	"generatedBy": "ofero-cli/1.2.0",
	"updatedAt": "2025-01-15T10:00:00Z",
	"updatedBy": "ofero-cli/1.2.0",
	"fileLocation": "https://globaleducation.org/.well-known/ofero.json",
	"organization": {
		"legalName": "Global Education Foundation",
		"brandName": {
			"default": "Global Education",
			"translations": {
				"ro": "Global Education in Romanian"
			}
		},
		"entityType": "association",
		"legalForm": "501(c)(3)",
		"legalFormCategory": "nonprofit",
		"description": {
			"default": "Providing educational resources to underserved communities worldwide",
			"translations": {
				"ro": "Providing educational resources to underserved communities worldwide in Romanian"
			}
		},
		"socialActivityDomain": "Education / International Development",
		"nonProfitStatus": true,
		"website": "https://globaleducation.org",
		"primaryEmail": "info@globaleducation.org",
		"primaryPhone": "+15559876543",
		"identifiers": {
			"global": {},
			"primaryIncorporation": {
				"country": "US",
				"registrationNumber": "87654321",
				"taxId": "98-7654321",
				"vatNumber": ""
			},
			"perCountry": []
		}
	},
	"keywords": {
		"default": "education, nonprofit, international development, charity, learning"
	},
	"privacy": {
		"dataClassification": "public",
		"gdprCompliance": true,
		"dataRetentionPeriod": "P1Y"
	},
	"wallets": [
		{
			"blockchain": "ethereum",
			"network": "mainnet",
			"address": "0xABCDEF0123456789ABCDEF0123456789ABCDEF01",
			"purpose": "donations",
			"extensions": {
				"evm": {
					"chainId": 1,
					"explorerUrl": "https://etherscan.io"
				}
			}
		}
	],
	"ai": {
		"indexing": {
			"allow": true,
			"allowTraining": true
		},
		"preferredSources": ["https://globaleducation.org", "https://docs.globaleducation.org"]
	},
	"extensions": {
		"schemaVersion": "ofero-metadata-1.0"
	}
}
```

### Example 3: Web3 Protocol

```json
{
	"version": "1.0",
	"language": "en",
	"generatedAt": "2025-01-15T10:00:00Z",
	"fileLocation": "https://defipro.io/.well-known/ofero.json",
	"organization": {
		"legalName": "DeFi Protocol Foundation",
		"brandName": "DefiPro",
		"entityType": "protocol",
		"legalForm": "Foundation",
		"legalFormCategory": "foundation",
		"description": "Decentralized finance protocol for yield optimization",
		"website": "https://defipro.io",
		"primaryEmail": "contact@defipro.io",
		"primaryPhone": "+41 22 123 4567",
		"identifiers": {
			"global": {},
			"primaryIncorporation": {
				"country": "CH",
				"registrationNumber": "CHE-123.456.789",
				"taxId": "CHE-123.456.789",
				"vatNumber": ""
			},
			"perCountry": []
		}
	},
	"wallets": [
		{
			"blockchain": "ethereum",
			"network": "mainnet",
			"address": "0x1234567890ABCDEF1234567890ABCDEF12345678",
			"purpose": "treasury",
			"extensions": {
				"evm": {
					"chainId": 1
				}
			}
		}
	],
	"tokenomics": {
		"token": {
			"symbol": "DFP",
			"name": "DefiPro Token",
			"type": "ERC-20",
			"blockchain": "ethereum",
			"network": "mainnet",
			"identifier": "0xABCDEF0123456789ABCDEF0123456789ABCDEF01",
			"decimals": 18,
			"totalSupply": "1000000000",
			"circulatingSupply": "500000000",
			"explorerUrl": "https://etherscan.io/token/0xABCDEF0123456789ABCDEF0123456789ABCDEF01"
		}
	},
	"apis": {
		"public": [
			{
				"name": "DefiPro API",
				"version": "v1",
				"baseUrl": "https://api.defipro.io/v1",
				"docsUrl": "https://docs.defipro.io",
				"authentication": "bearer",
				"rateLimit": "1000/hour"
			}
		]
	},
	"extensions": {
		"schemaVersion": "ofero-metadata-1.0"
	}
}
```

### Example 4: Restaurant with Menu

See the complete example in [examples/restaurant-example.json](examples/restaurant-example.json).

Key features demonstrated:

- `catalog.menu` with categories and menu items
- Pricing with variants (sizes) and add-ons
- Dietary labels and allergen information
- Multi-language support for menu items
- `restaurantDetails` for capacity, service types, reservations, amenities
- `apiEndpoints.availability` for real-time occupancy data

#### Pricing rules for menu items

- `price` — numeric value, required. Currency is defined once at `catalog.defaultCurrency`.
- `priceUnit` — optional string for when the pricing basis needs clarification (e.g. `"per glass"`, `"per kg"`). Omit for standard per-portion pricing.
- Do **not** use a `priceFormatted` field. Formatting is the responsibility of the consuming system.

#### Variants and add-ons

Each `variant` and `addon` must have a unique `id` within its parent item, so that order systems and external portals can reference them unambiguously.

```json
{
	"catalog": {
		"defaultCurrency": "USD",
		"menu": {
			"categories": [
				{
					"id": "pizza",
					"name": { "default": "Pizza" },
					"items": [
						{
							"id": "margherita",
							"name": { "default": "Pizza Margherita" },
							"description": { "default": "Tomato sauce, mozzarella, basil" },
							"price": 35,
							"dietary": ["vegetarian"],
							"allergens": ["gluten", "dairy"],
							"variants": [
								{ "id": "margherita-small", "name": "Small (26cm)", "price": 35 },
								{ "id": "margherita-large", "name": "Large (32cm)", "price": 45 }
							],
							"addons": [
								{ "id": "addon-extra-mozzarella", "name": "Extra mozzarella", "price": 8 },
								{ "id": "addon-prosciutto", "name": "Prosciutto", "price": 12 }
							]
						}
					]
				}
			],
			"dietaryOptions": ["vegetarian", "vegan", "gluten-free"]
		}
	},
	"restaurantDetails": {
		"seatingCapacity": 80,
		"indoorSeats": 60,
		"outdoorSeats": 20,
		"serviceTypes": {
			"dineIn": true,
			"takeaway": true,
			"delivery": true
		},
		"reservations": {
			"required": false,
			"recommended": true,
			"bookingUrl": "https://restaurant.com/book",
			"advanceNotice": "24h"
		},
		"amenities": ["wifi", "air-conditioning", "terrace", "wheelchair-accessible"],
		"cuisine": ["italian", "pizza", "mediterranean"],
		"priceRange": "$$",
		"averageCheckPerPerson": 45,
		"ratingsUrl": "https://g.page/your-restaurant"
	},
	"apiEndpoints": {
		"availability": "https://restaurant.com/api/availability",
		"reservations": "https://restaurant.com/api/reservations"
	}
}
```

### Example 5: Auto Service with Service List

See the complete example in [examples/auto-service-example.json](examples/auto-service-example.json).

Key features demonstrated:

- `catalog.services` with variable pricing
- Duration estimates and warranty information
- Service categories

```json
{
	"catalog": {
		"defaultCurrency": "USD",
		"services": [
			{
				"id": "oil-change",
				"name": { "default": "Oil and Filter Change" },
				"category": "maintenance",
				"price": 80,
				"priceUnit": "fixed",
				"duration": "30 min",
				"warranty": "30 days"
			},
			{
				"id": "brake-pads",
				"name": { "default": "Brake Pad Replacement" },
				"category": "brakes",
				"priceFrom": 100,
				"priceTo": 200,
				"priceUnit": "fixed",
				"duration": "1-2 hours",
				"warranty": "6 months or 10,000 km"
			}
		]
	}
}
```

### Example 6: Karting Center with Packages

See the complete example in [examples/karting-example.json](examples/karting-example.json).

Key features demonstrated:

- `catalog.packages` for experience-based pricing
- Participant limits and age requirements
- What's included in each package

```json
{
	"catalog": {
		"defaultCurrency": "USD",
		"packages": [
			{
				"id": "adult-10min",
				"name": { "default": "Adult Race - 10 minutes" },
				"price": 75,
				"pricePerPerson": true,
				"duration": "10 min",
				"includes": ["Helmet and equipment", "Safety briefing"],
				"ageRequirement": "16+, min 150cm",
				"maxParticipants": 10
			},
			{
				"id": "corporate-event",
				"name": { "default": "Corporate Event" },
				"price": 150,
				"pricePerPerson": true,
				"duration": "3 hours",
				"includes": ["Exclusive track access", "Catering", "Trophies"],
				"minParticipants": 10,
				"maxParticipants": 40
			}
		]
	}
}
```

### Example 7: Architecture Firm with Portfolio

See the complete example in [examples/architecture-firm-example.json](examples/architecture-firm-example.json).

Key features demonstrated:

- `catalog.portfolio` for showcasing projects
- Service pricing (per sqm, hourly)
- Awards and project categories

```json
{
	"catalog": {
		"defaultCurrency": "EUR",
		"services": [
			{
				"id": "residential-design",
				"name": { "default": "Residential Architecture" },
				"priceFrom": 25,
				"priceTo": 50,
				"priceUnit": "per-item",
				"duration": "3-6 months"
			}
		],
		"portfolio": [
			{
				"id": "villa-m",
				"title": { "default": "Villa M - Minimalist Lake House" },
				"category": "residential",
				"client": "Private Client",
				"date": "2024-08-15",
				"images": [
					{
						"url": "https://modernarch.ro/portfolio/villa-m/exterior.jpg",
						"caption": "Lake-facing facade"
					}
				],
				"awards": ["Romanian Architecture Award 2024"],
				"featured": true
			}
		]
	}
}
```

### Example 8: Modeling Agency with Portfolio

See the complete example in [examples/modeling-agency-example.json](examples/modeling-agency-example.json).

Key features demonstrated:

- `catalog.portfolio` for showcasing campaigns
- B2B service offerings
- Team section with leadership info

---

For complete examples, see:

- [Minimal Example](examples/minimal.json) - Minimal required fields only
- [Company Full Example](examples/company-full.json) - Complete company with all sections
- [E-commerce Store Example](examples/ecommerce-store.json) - Online retail store
- [Web3 Protocol Example](examples/web3-protocol.json) - Blockchain protocol/DeFi project
- [Platform Integration Example](examples/company-with-both-platforms.json) - Company with platform accounts
- [Restaurant Example](examples/restaurant-example.json) - Restaurant with full menu
- [Auto Service Example](examples/auto-service-example.json) - Auto service with service list
- [Karting Example](examples/karting-example.json) - Karting center with packages
- [Architecture Firm Example](examples/architecture-firm-example.json) - Architecture studio with portfolio
- [Modeling Agency Example](examples/modeling-agency-example.json) - Talent agency with portfolio

---

## Implementation Guidelines

### Getting Started

1. **Choose Your Primary Language**: The language your organization operates in (e.g., `en`, `de`, `fr`, `hu`)
2. **Determine Your Entity Type**: company, association, ngo, protocol, etc.
3. **Find Your Legal Form**: Consult the legal forms reference
4. **Gather Required Information**: Legal name, identifiers, contact details
5. **Create Base File**: Start with minimal required fields
6. **Add Optional Sections**: Based on your needs (wallets, team, tokenomics, etc.)
7. **Validate**: Use JSON Schema validator
8. **Deploy**: Upload to `/.well-known/ofero.json`
9. **Verify**: Test accessibility and CORS

### Best Practices

1. **Keep It Current**: Update when information changes (quarterly review recommended)
2. **Use HTTPS**: Always serve over secure connection
3. **Validate Before Deploy**: Use JSON Schema validator
4. **Start Minimal**: Begin with required fields, add optional sections as needed
5. **Verify Ownership**: Use wallet proofs and DNS records for verification
6. **Consistent Naming**: Use consistent identifiers across all sections
7. **Link Related Files**: Use absolute URLs for all external references
8. **Cache Appropriately**: Set reasonable cache headers (1-24 hours)
9. **Monitor Access**: Track who's accessing your ofero.json file
10. **Version Control**: Keep your ofero.json in version control

### Security Considerations

1. **Don't Include Secrets**: Never include private keys, passwords, or sensitive credentials
2. **Limit Personal Information**: Only include contact information you want public
3. **Verify External Links**: Ensure all external URLs are under your control
4. **Wallet Proofs**: Use cryptographic signatures to prove wallet ownership
5. **Regular Audits**: Review and update information quarterly
6. **DNS Verification**: Use DNS TXT records for domain verification

### Common Mistakes to Avoid

1. ❌ **Hardcoding Dates**: Use `generatedAt` as actual generation time
2. ❌ **Missing Required Fields**: Validate against schema before deploying
3. ❌ **Inconsistent Language**: Don't mix languages in `default` values — all `default` fields must be in the language declared by the top-level `language` field
4. ❌ **Invalid URLs**: Always use absolute URLs with protocol (https://)
5. ❌ **Wrong Entity Type**: Choose correct entity type for your organization
6. ❌ **Translating Technical Fields**: Don't translate IDs, codes, addresses
7. ❌ **Oversized Files**: Keep file size reasonable (< 500 KB recommended)
8. ❌ **Stale Information**: Update regularly, especially contact information

---

## Version History

### Version 1.0.0 (January 15, 2025)

**Initial Public Release**

**Core Features:**

- Complete JSON Schema specification with 45+ interface definitions
- TranslatableString structure with `default` + `translations` properties for multi-language support
- HTTPS enforcement on all 69 URL fields for security
- Required `generatedBy` field for tracking file origin
- Optional `updatedAt` and `updatedBy` fields for tracking file updates
- `keywords` field (TranslatableString) for AI search indexing and discovery
- `openSource` section with repositories array for open source project tracking
- `privacy` section with data classification, GDPR compliance, and retention period
- `securityAudits` array under Security section for third-party audit transparency
- E.164 phone validation pattern on 8 phone fields for international format
- Conditional validation for entity types (company requires `industry`, NGOs require `socialActivityDomain` + `nonProfitStatus`)
- Default value `"pending-verification"` for `mutualPartnerships.status` with validator auto-upgrade
- Extended locale support in TranslatableString (e.g., `en-US`, `ro-RO`)
- ISO 8601 duration pattern validation for `privacy.dataRetentionPeriod`

**Additional Features:**

- 120+ legal forms across 13+ countries
- Multi-language overlay system for complete translations
- Web3/blockchain extensions for DeFi protocols
- Comprehensive field reference documentation
- Three validation levels (basic, moderate, strict)

**Schema Version:** `ofero-metadata-1.0`

**Security & Standards:**

- All URLs must use HTTPS protocol (no HTTP allowed)
- Phone numbers follow E.164 international format (`+[country][number]`)
- Partnership verification with exact URL matching for trust
- Data retention periods use ISO 8601 duration format (e.g., `P1Y`, `P6M`)
- Security audit tracking with third-party auditors (CertiK, Trail of Bits, etc.)

---

## Contributing

This is an open standard. Contributions welcome at:

- **GitHub**: [github.com/oferome/ofero-json](https://github.com/oferome/ofero-json) (example)
- **Discussions**: For questions and suggestions
- **Issues**: For bugs and improvements

---

## License

This specification is released under the **MIT License**.

---

## Support and Contact

- **Documentation**: https://ofero.network/ofero-json
- **Validator**: [Coming Soon]
- **Examples**: See `/docs/ofero-json/examples/`
- **Schema**: `/static/schemas/ofero-json-schema.json`
- **Legal Forms**: `/static/schemas/ofero-json-legal-forms.json`

---

**© 2025 Ofero Network. Specification v1.0**
