# Platform Sections Explained

This document explains the distinction between `platformAccounts` and `communications.social` sections in ofero.json files, using the **company-with-both-platforms.json** example.

## Why Both Sections Exist

The ofero.json standard includes two separate sections for platform presence because they serve **different audiences and use cases**:

### `platformAccounts` - B2B Integration IDs

**Purpose**: Machine-readable account identifiers for automated B2B integrations

**Contains**: Structured platform-specific IDs (page IDs, business IDs, analytics IDs, etc.)

**Used by**:
- Merchant onboarding platforms
- Affiliate networks
- CRM systems
- Partner integration APIs
- Payment processors
- Marketing automation platforms

**Example from company-with-both-platforms.json**:
```json
"platformAccounts": {
  "facebook": {
    "pageId": "globalfashion",
    "businessId": "987654321"
  },
  "instagram": {
    "username": "globalfashion",
    "businessAccountId": "123456789"
  }
}
```

### `communications.social` - Public URLs

**Purpose**: Human-readable URLs for public discovery and engagement

**Contains**: Full clickable URLs to social media profiles

**Used by**:
- Website visitors
- Customers and community members
- AI/LLM systems for entity discovery
- Search engines
- Social media aggregators
- Marketing analytics tools

**Example from company-with-both-platforms.json**:
```json
"communications": {
  "social": {
    "instagram": "https://instagram.com/globalfashion",
    "facebook": "https://facebook.com/globalfashion"
  }
}
```

## Real-World Use Cases

### Use Case 1: Merchant Onboarding

**Scenario**: GlobalFashion wants to join an affiliate marketing platform

**What happens**:
1. Affiliate platform fetches `globalfashion.example.com/.well-known/ofero.json`
2. Platform reads `platformAccounts.facebook.businessId` → `"987654321"`
3. Platform uses this ID to **automatically verify** and link GlobalFashion's Facebook Business account
4. Platform reads `platformAccounts.instagram.businessAccountId` → `"123456789"`
5. Platform automatically connects Instagram Business account for tracking
6. **No manual entry needed** - the merchant is onboarded in seconds

**Without platformAccounts**: Merchant would need to manually enter all account IDs, risking typos and verification delays.

### Use Case 2: Community Discovery

**Scenario**: Customer wants to follow GlobalFashion on social media

**What happens**:
1. Website displays social icons linking to `communications.social` URLs
2. Customer clicks Instagram icon → goes to `https://instagram.com/globalfashion`
3. AI assistant asked "Where can I find GlobalFashion on TikTok?" reads the ofero.json
4. AI responds with direct link: `https://tiktok.com/@globalfashion`

**Without communications.social**: Websites would need to hardcode social links in multiple places, making updates difficult.

### Use Case 3: CRM Integration

**Scenario**: Sales team uses a CRM that auto-imports company information

**What happens**:
1. CRM fetches ofero.json and reads `platformAccounts.linkedin.companyId` → `"7654321"`
2. CRM uses LinkedIn API with this company ID to pull:
   - Company size
   - Recent posts
   - Employee count
   - Engagement metrics
3. Sales team gets enriched company profile automatically

**Without platformAccounts**: Sales team manually searches LinkedIn and copies information.

## Data Format Comparison

### Same Platform, Different Data

Notice how the same platforms appear in both sections with different data:

| Platform | `platformAccounts` | `communications.social` |
|----------|-------------------|------------------------|
| **Facebook** | `{ pageId: "globalfashion", businessId: "987654321" }` | `"https://facebook.com/globalfashion"` |
| **Instagram** | `{ username: "globalfashion", businessAccountId: "123456789" }` | `"https://instagram.com/globalfashion"` |
| **LinkedIn** | `{ companyId: "7654321", pageUrl: "..." }` | `"https://linkedin.com/company/globalfashion"` |
| **X (Twitter)** | `{ handle: "@GlobalFashion", userId: "98765432" }` | `"https://twitter.com/GlobalFashion"` |

### Why the Duplication is Necessary

1. **Different consumers**: APIs need IDs, humans need URLs
2. **Different verification**: IDs are platform-verifiable, URLs are clickable
3. **Different purposes**: IDs enable automation, URLs enable navigation
4. **Different extensibility**: IDs allow platform-specific fields, URLs are standardized strings

## When to Use Each Section

### Always populate `platformAccounts` if:
- ✅ You have verified business accounts on major platforms
- ✅ You want automated merchant onboarding
- ✅ You integrate with affiliate networks or marketplaces
- ✅ You want CRM systems to auto-enrich your company profile
- ✅ You use business analytics tools that need account IDs

### Always populate `communications.social` if:
- ✅ You have public social media profiles
- ✅ You want customers to find and follow you
- ✅ You want AI systems to correctly identify your social presence
- ✅ You want website social icons to auto-populate
- ✅ You want improved SEO and social discovery

### Best Practice: Populate Both

For maximum benefit, organizations should **populate both sections**:

```json
{
  "platformAccounts": {
    // For B2B automation and account linking
    "instagram": {
      "username": "globalfashion",
      "businessAccountId": "123456789"
    }
  },
  "communications": {
    "social": {
      // For customer engagement and discovery
      "instagram": "https://instagram.com/globalfashion"
    }
  }
}
```

## Benefits Summary

### Benefits of `platformAccounts`:
1. **Faster Onboarding**: Auto-fill forms during partner registration
2. **Reduced Errors**: No manual typing of long IDs
3. **Account Verification**: Platform IDs enable ownership verification
4. **API Integration**: Enable seamless CRM and analytics integrations
5. **B2B Trust**: Verifiable business account IDs build partner confidence

### Benefits of `communications.social`:
1. **Customer Discovery**: Easy social media navigation
2. **SEO Improvement**: Search engines index social links
3. **AI Understanding**: LLMs can accurately find your social presence
4. **Community Engagement**: Clear paths for followers to connect
5. **Brand Consistency**: Centralized social link management

## Validation Example

Both sections are **optional** in the ofero.json schema, but complementary:

```json
{
  "platformAccounts": {
    // OPTIONAL: Include if you want B2B automation
  },
  "communications": {
    "social": {
      // OPTIONAL: Include for public social discovery
    }
  }
}
```

However, the **best practice** is to populate both when you have established platform presence.

## FAQ

**Q: Should I include the same platform in both sections?**
A: **Yes!** If you have a Facebook Business Page, include the page ID in `platformAccounts` AND the URL in `communications.social`.

**Q: Can I use just one section?**
A: You can, but you'll miss significant benefits. Use both for maximum value.

**Q: What if I only have personal accounts, not business accounts?**
A: Use `communications.social` only. `platformAccounts` is primarily for verified business accounts.

**Q: Do both sections need to be identical?**
A: No. `platformAccounts` contains structured IDs, `communications.social` contains URLs. They reference the same profiles but in different formats.

**Q: Can I add platforms not listed in the schema?**
A: **Yes!** Both sections are extensible. Add any platform (TikTok, Pinterest, Snapchat, etc.).

## Additional Resources

- **Full Specification**: See `SPECIFICATION.md` section "Platform Accounts vs Social Communications"
- **Schema Definition**: `/static/schemas/ofero-json-schema.json`
- **More Examples**: See other files in `/examples/` directory

---

**Example File**: company-with-both-platforms.json
**Last Updated**: January 15, 2025
**Schema Version**: ofero-metadata-1.0
