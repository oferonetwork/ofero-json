# Migrating from ofero.json v2.x to v3.0

**Status:** Required migration. v2 files (`schemaVersion: "ofero-metadata-2.0"`) are rejected by v3 validators. Consumers that only display data are asked to keep reading v2 files during the transition (see [Reading older files](SPECIFICATION.md#reading-older-files-v2)).

v3.0 changes one thing: **the opening-hours format.** Everything else in a v2 file stays valid. The conversion is mechanical and can be scripted (see [Automatic conversion](#automatic-conversion)).

---

## Why we made this breaking change

In v2, each day in `businessHours` was a single string, `"09:00-18:00"` or `"Closed"`. Many businesses are not open in one block:

- shops, offices and workshops that close for lunch (`09:00-13:00` and `14:00-18:00`);
- restaurants with a lunch and a dinner service;
- clinics with morning and afternoon consultations.

v2 could not express these. Packing two intervals into one string (`"09:00-13:00, 14:00-18:00"`) passed validation, but no format was defined, so each consumer would read it differently, or not at all. A list of intervals has one meaning that consumers can check, and it maps directly to schema.org `OpeningHoursSpecification` (one entry per interval).

Changing the type of an existing field breaks every parser that expects a string, so this is a major version.

---

## What changed at a glance

| Aspect | v2.x | v3.0 |
| --- | --- | --- |
| `metadata.schemaVersion` | `"ofero-metadata-2.0"` | `"ofero-metadata-3.0"` |
| `businessHours.<day>` | string: `"09:00-18:00"` | array: `["09:00-18:00"]` |
| Split schedule (lunch break) | Not expressible | `["09:00-13:00", "14:00-18:00"]` |
| Closed day | `"Closed"` | `[]` |
| Day not stated | Key omitted | Key omitted (unchanged — means unknown, not closed) |
| `specialHours[].hours` | string: `"10:00-14:00"` / `"Closed"` | array: `["10:00-14:00"]` / `[]` |
| Interval format | Documented, not validated | Validated by the schema: `HH:MM-HH:MM`, end may be `24:00`, end before start = past midnight |
| Other keys in `businessHours` | Allowed (ignored) | Rejected — only the seven weekdays and `timezone` |

---

## Step-by-step migration recipe

### 1. Convert each day of `businessHours`

```jsonc
// v2
"businessHours": {
	"monday": "09:00-18:00",
	"saturday": "10:00-14:00",
	"sunday": "Closed",
	"timezone": "UTC"
}

// v3
"businessHours": {
	"monday": ["09:00-18:00"],
	"saturday": ["10:00-14:00"],
	"sunday": [],
	"timezone": "UTC"
}
```

If the location closes for lunch, now is the time to say so:

```json
"monday": ["09:00-13:00", "14:00-18:00"]
```

### 2. Convert `specialHours[].hours` the same way

```jsonc
// v2
{ "date": "2026-12-25", "name": "Christmas Day", "hours": "Closed" }
{ "from": "2026-07-01", "to": "2026-08-31", "name": "Summer Schedule", "hours": "09:00-15:00" }

// v3
{ "date": "2026-12-25", "name": "Christmas Day", "hours": [] }
{ "from": "2026-07-01", "to": "2026-08-31", "name": "Summer Schedule", "hours": ["09:00-15:00"] }
```

### 3. Fix values that are not a time range

v3 only accepts `HH:MM-HH:MM` intervals. Values such as `"By appointment"`, `"24/7"` or `"9-18"` must change:

| v2 value | v3 value |
| --- | --- |
| `"9-18"`, `"9:00-18:00"` | `["09:00-18:00"]` (two-digit hours) |
| `"24/7"`, `"Open 24 hours"` | `["00:00-24:00"]` |
| `"09:00-13:00, 14:00-18:00"` | `["09:00-13:00", "14:00-18:00"]` |
| `"By appointment"` | The hours in which appointments are possible, plus `"publicAccess": "by-appointment"` on the location |
| `"Monday"`, `"Sat"` and other unknown keys in `businessHours` | Rename to the lower-case weekday (`monday` … `sunday`) |

### 4. Bump `metadata.schemaVersion`

```json
{
	"metadata": {
		"version": "3.0.0",
		"schemaVersion": "ofero-metadata-3.0",
		"lastUpdated": "2026-10-24T10:00:00Z"
	}
}
```

`metadata.version` is the version of your own file. A MAJOR bump there is optional but tells consumers the shape of the file changed.

### 5. Validate

Against the JSON Schema:

```bash
npx ajv-cli validate --spec=draft2020 -c ajv-formats -s schema/ofero-json-schema.json -d your-ofero.json
```

The TypeScript validator (`validateOferoJson(data, 'strict')` in `validators/ofero-json-validator.ts`) rejects `ofero-metadata-2.0` files and string hours with a pointer to this guide. In strict mode it also checks that the intervals of each day are in chronological order, do not overlap, and that only the last one runs past midnight.

---

## Automatic conversion

This Node.js script converts a v2 file in place. It stops on any value it cannot convert, so you can fix those by hand (step 3).

```js
// migrate-hours.js — usage: node migrate-hours.js ofero.json
const fs = require('fs');
const file = process.argv[2];
const data = JSON.parse(fs.readFileSync(file, 'utf8'));
const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const RANGE = /^([01]\d|2[0-3]):[0-5]\d-(([01]\d|2[0-3]):[0-5]\d|24:00)$/;

function toIntervals(value, where) {
	if (Array.isArray(value)) return value; // already v3
	if (/^closed$/i.test(String(value).trim())) return [];
	const parts = String(value).split(/[,;]/).map((s) => s.trim()).filter(Boolean);
	if (parts.length && parts.every((p) => RANGE.test(p))) return parts;
	throw new Error(`${where}: cannot convert ${JSON.stringify(value)} — fix it by hand`);
}

(data.locations || []).forEach((loc, i) => {
	const bh = loc.businessHours;
	if (bh) {
		for (const day of DAYS) {
			if (day in bh) bh[day] = toIntervals(bh[day], `locations[${i}].businessHours.${day}`);
		}
	}
	(loc.specialHours || []).forEach((entry, j) => {
		entry.hours = toIntervals(entry.hours, `locations[${i}].specialHours[${j}].hours`);
	});
});

data.metadata.schemaVersion = 'ofero-metadata-3.0';
fs.writeFileSync(file, JSON.stringify(data, null, 2) + '\n');
console.log('Converted', file);
```

The reference tools also migrate for you: the WordPress generator and the PHP generator read v2 hours and write v3 on the next save.

---

## For consumers

- Read `businessHours.<day>` and `specialHours[].hours` as arrays of `"HH:MM-HH:MM"` strings.
- Show every interval of the day, e.g. "09:00–13:00, 14:00–18:00". Do not merge them into "09:00–18:00".
- `[]` = closed; a missing day = unknown.
- An end earlier than the start runs past midnight and belongs to the day it starts on.
- Until publishers migrate, accept v2 strings as described in [Reading older files](SPECIFICATION.md#reading-older-files-v2): `"HH:MM-HH:MM"` → one interval, `"Closed"` → `[]`, anything else → show as text.

---

## FAQ

**Q: Can I just bump the schemaVersion?**
No. With `ofero-metadata-3.0`, string values in `businessHours` fail schema validation. Convert the hours first.

**Q: My location has no lunch break. Do I still need to change anything?**
Yes — `"09:00-18:00"` becomes `["09:00-18:00"]` and `"Closed"` becomes `[]`. The meaning is the same.

**Q: How do I say "open until 2 AM"?**
`"friday": ["18:00-02:00"]`. An end earlier than the start runs past midnight; the hours after midnight belong to Friday, not Saturday.

**Q: Why not `{ "opens": "09:00", "closes": "13:00" }` objects?**
Strings keep the file short and easy to write by hand, and the schema can still validate each one with a pattern. Converting them to schema.org `opens`/`closes` is a split on `-`.

**Q: Will v2 consumers break when I upgrade?**
Parsers that expect a string will. Upgrade your consumers first (the WordPress shortcodes plugin 3.0 reads both formats), then your file.

---

## Rollback

1. Restore the previous version of your ofero.json from git history.
2. Revert `metadata.schemaVersion` to `"ofero-metadata-2.0"`.
3. Pin tooling to the last v2 release (WordPress generator 2.1.x, shortcodes plugin 2.0.x).

Rolling back means split schedules cannot be published again.
