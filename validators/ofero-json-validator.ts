/**
 * Ofero.json Validator
 *
 * Provides JSON Schema-based validation for ofero.json files
 * Supports three validation levels: basic, moderate, strict
 */

import Ajv, { type ErrorObject } from 'ajv';
import addFormats from 'ajv-formats';

// Validation levels
export type ValidationLevel = 'basic' | 'moderate' | 'strict';

// Validation result
export interface ValidationResult {
	valid: boolean;
	errors: ValidationError[];
	warnings: ValidationWarning[];
}

// Validation error
export interface ValidationError {
	path: string;
	message: string;
	keyword?: string;
	params?: Record<string, any>;
}

// Validation warning
export interface ValidationWarning {
	path: string;
	message: string;
	type: string;
}

// Create Ajv instance
const ajv = new Ajv({
	allErrors: true,
	verbose: true,
	strict: false,
	validateFormats: true
});

// Add format validators
addFormats(ajv);

// Load and compile the schema (lazy loaded)
let validateSchema: any = null;
let schemaLoaded = false;

async function loadSchema() {
	if (!schemaLoaded) {
		try {
			const response = await fetch('/schemas/ofero-json-schema.json');
			const schema = await response.json();
			validateSchema = ajv.compile(schema);
			schemaLoaded = true;
		} catch (error) {
			console.error('Failed to load ofero.json schema:', error);
			throw new Error('Failed to load validation schema');
		}
	}
	return validateSchema;
}

/**
 * Validate ofero.json data
 *
 * @param data - The data to validate
 * @param level - Validation level (basic, moderate, strict)
 * @returns Validation result with errors and warnings
 */
export async function validateOferoJson(
	data: any,
	level: ValidationLevel = 'moderate'
): Promise<ValidationResult> {
	const result: ValidationResult = {
		valid: true,
		errors: [],
		warnings: []
	};

	// Load schema if not already loaded
	const validator = await loadSchema();

	// 1. JSON Schema validation
	const isValid = validator(data);

	if (!isValid && validator.errors) {
		result.valid = false;
		result.errors = formatErrors(validator.errors);
	}

	// 2. Basic validation (always applied)
	const basicErrors = validateBasic(data);
	if (basicErrors.length > 0) {
		result.valid = false;
		result.errors.push(...basicErrors);
	}

	// 3. Moderate validation (if level is moderate or strict)
	if (level === 'moderate' || level === 'strict') {
		const moderateErrors = validateModerate(data);
		if (moderateErrors.length > 0) {
			result.valid = false;
			result.errors.push(...moderateErrors);
		}

		// Add warnings for recommended fields
		result.warnings.push(...getRecommendedFieldWarnings(data));
	}

	// 4. Strict validation (if level is strict)
	if (level === 'strict') {
		const strictErrors = validateStrict(data);
		if (strictErrors.length > 0) {
			result.valid = false;
			result.errors.push(...strictErrors);
		}
	}

	return result;
}

/**
 * Validate language overlay file
 *
 * @param overlay - The overlay data to validate
 * @returns Validation result
 */
export function validateOverlay(overlay: any): ValidationResult {
	const result: ValidationResult = {
		valid: true,
		errors: [],
		warnings: []
	};

	// Check required fields
	if (!overlay.version) {
		result.errors.push({
			path: 'version',
			message: 'Version is required in overlay file'
		});
		result.valid = false;
	}

	if (!overlay.language) {
		result.errors.push({
			path: 'language',
			message: 'Language is required in overlay file'
		});
		result.valid = false;
	}

	// Validate language code format
	if (overlay.language && !/^[a-z]{2}$/.test(overlay.language)) {
		result.errors.push({
			path: 'language',
			message: 'Language must be a valid ISO 639-1 code (e.g., "en", "ro", "de")'
		});
		result.valid = false;
	}

	// Check that overlay only contains translatable fields
	const allowedFields = [
		'version',
		'language',
		'generatedAt',
		'organization',
		'locations',
		'branding',
		'featured',
		'team',
		'tokenomics',
		'analytics',
		'roadmap',
		'press',
		'careers',
		'ai',
		'extensions'
	];

	Object.keys(overlay).forEach((key) => {
		if (!allowedFields.includes(key)) {
			result.warnings.push({
				path: key,
				message: `Field "${key}" is not translatable and should not be in overlay file`,
				type: 'non-translatable'
			});
		}
	});

	return result;
}

/**
 * Format Ajv errors into readable format
 */
function formatErrors(errors: ErrorObject[]): ValidationError[] {
	return errors.map((error) => {
		const path = error.instancePath || error.schemaPath || 'root';
		let message = error.message || 'Validation error';

		// Enhance error messages based on keyword
		if (error.keyword === 'required') {
			const missingProp = (error.params as any).missingProperty;
			message = `Missing required field: ${missingProp}`;
		} else if (error.keyword === 'enum') {
			const allowedValues = (error.params as any).allowedValues;
			message = `Invalid value. Allowed values: ${allowedValues.join(', ')}`;
		} else if (error.keyword === 'format') {
			const format = (error.params as any).format;
			message = `Invalid ${format} format`;
		} else if (error.keyword === 'pattern') {
			message = `Does not match required pattern`;
		}

		return {
			path: path.replace(/^\//, '').replace(/\//g, '.'),
			message,
			keyword: error.keyword,
			params: error.params
		};
	});
}

/**
 * Basic validation (always applied)
 */
function validateBasic(data: any): ValidationError[] {
	const errors: ValidationError[] = [];

	// Check JSON structure
	if (typeof data !== 'object' || data === null) {
		errors.push({
			path: 'root',
			message: 'Data must be a valid JSON object'
		});
		return errors;
	}

	// Check required top-level fields
	if (!data.language) {
		errors.push({ path: 'language', message: 'Language is required' });
	}

	if (!data.domain) {
		errors.push({ path: 'domain', message: 'Domain is required' });
	}

	if (!data.canonicalUrl) {
		errors.push({ path: 'canonicalUrl', message: 'Canonical URL is required' });
	}

	// Check metadata section
	if (!data.metadata) {
		errors.push({ path: 'metadata', message: 'Metadata section is required' });
	} else {
		if (!data.metadata.version) {
			errors.push({ path: 'metadata.version', message: 'Metadata version is required' });
		} else if (!/^\d+\.\d+\.\d+$/.test(data.metadata.version)) {
			errors.push({
				path: 'metadata.version',
				message: 'Version must be semantic version format (e.g., 1.0.0)'
			});
		}

		if (!data.metadata.schemaVersion) {
			errors.push({
				path: 'metadata.schemaVersion',
				message: 'Schema version must be "ofero-metadata-2.0"'
			});
		} else if (data.metadata.schemaVersion === 'ofero-metadata-1.0') {
			errors.push({
				path: 'metadata.schemaVersion',
				message:
					'Schema version "ofero-metadata-1.0" is no longer supported. This is a v2 validator — upgrade your file per docs/MIGRATION-v1-to-v2.md and bump schemaVersion to "ofero-metadata-2.0".'
			});
		} else if (data.metadata.schemaVersion !== 'ofero-metadata-2.0') {
			errors.push({
				path: 'metadata.schemaVersion',
				message: `Schema version must be "ofero-metadata-2.0", got "${data.metadata.schemaVersion}"`
			});
		}

		if (!data.metadata.lastUpdated) {
			errors.push({ path: 'metadata.lastUpdated', message: 'Last updated timestamp is required' });
		}
	}

	if (!data.organization) {
		errors.push({ path: 'organization', message: 'Organization section is required' });
		return errors;
	}

	// Check organization required fields
	if (!data.organization.legalName || data.organization.legalName.trim() === '') {
		errors.push({
			path: 'organization.legalName',
			message: 'Organization legal name is required and cannot be empty'
		});
	}

	if (!data.organization.website) {
		errors.push({ path: 'organization.website', message: 'Organization website is required' });
	}

	if (!data.organization.entityType) {
		errors.push({ path: 'organization.entityType', message: 'Organization entity type is required' });
	}

	return errors;
}

/**
 * Validate domain consistency
 * Ensures domain field matches the domain extracted from canonicalUrl
 */
function validateDomainConsistency(data: any): ValidationError[] {
	const errors: ValidationError[] = [];

	if (data.canonicalUrl && data.domain) {
		try {
			const url = new URL(data.canonicalUrl);
			const urlDomain =
				url.hostname + (url.port && url.port !== '443' && url.port !== '80' ? `:${url.port}` : '');

			if (urlDomain !== data.domain) {
				errors.push({
					path: 'domain',
					message: `Domain must match the domain in canonicalUrl. Expected: "${urlDomain}", Got: "${data.domain}"`
				});
			}
		} catch (e) {
			// Invalid URL - will be caught by schema validation
		}
	}

	return errors;
}

/**
 * Moderate validation (structure + basic formats)
 */
function validateModerate(data: any): ValidationError[] {
	const errors: ValidationError[] = [];

	// Validate domain consistency (domain matches canonicalUrl)
	const domainErrors = validateDomainConsistency(data);
	errors.push(...domainErrors);

	// Validate email formats
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

	if (data.organization?.contactEmail && !emailRegex.test(data.organization.contactEmail)) {
		errors.push({
			path: 'organization.contactEmail',
			message: 'Invalid email format'
		});
	}

	if (data.security?.securityEmail && !emailRegex.test(data.security.securityEmail)) {
		errors.push({
			path: 'security.securityEmail',
			message: 'Invalid email format'
		});
	}

	if (data.communications?.support?.email && !emailRegex.test(data.communications.support.email)) {
		errors.push({
			path: 'communications.support.email',
			message: 'Invalid email format'
		});
	}

	// Validate URL formats
	const urlRegex = /^https?:\/\/.+/;

	if (data.organization?.website && !urlRegex.test(data.organization.website)) {
		errors.push({
			path: 'organization.website',
			message: 'Website must be a valid URL (http:// or https://)'
		});
	}

	// Validate ISO codes
	const countryCodeRegex = /^[A-Z]{2}$/;

	if (
		data.organization?.identifiers?.primaryIncorporation?.country &&
		!countryCodeRegex.test(data.organization.identifiers.primaryIncorporation.country)
	) {
		errors.push({
			path: 'organization.identifiers.primaryIncorporation.country',
			message: 'Country must be a valid ISO 3166-1 alpha-2 code (e.g., US, GB, DE)'
		});
	}

	// Validate language code
	const languageCodeRegex = /^[a-z]{2}$/;
	if (data.language && !languageCodeRegex.test(data.language)) {
		errors.push({
			path: 'language',
			message: 'Language must be a valid ISO 639-1 code (e.g., en, ro, de)'
		});
	}

	// Validate date-time format
	if (data.generatedAt) {
		try {
			const date = new Date(data.generatedAt);
			if (isNaN(date.getTime())) {
				errors.push({
					path: 'generatedAt',
					message: 'GeneratedAt must be a valid ISO 8601 date-time'
				});
			}
		} catch {
			errors.push({
				path: 'generatedAt',
				message: 'GeneratedAt must be a valid ISO 8601 date-time'
			});
		}
	}

	// Reject v1 inline-catalog fields with a clear migration message
	errors.push(...validateNoInlineCatalog(data));

	return errors;
}

/**
 * Strict validation (comprehensive checks)
 */
function validateStrict(data: any): ValidationError[] {
	const errors: ValidationError[] = [];

	// Validate coordinates range
	if (data.locations) {
		data.locations.forEach((location: any, index: number) => {
			if (location.coordinates) {
				const { latitude, longitude } = location.coordinates;
				if (latitude !== undefined && (latitude < -90 || latitude > 90)) {
					errors.push({
						path: `locations[${index}].coordinates.latitude`,
						message: 'Latitude must be between -90 and 90'
					});
				}
				if (longitude !== undefined && (longitude < -180 || longitude > 180)) {
					errors.push({
						path: `locations[${index}].coordinates.longitude`,
						message: 'Longitude must be between -180 and 180'
					});
				}
			}
		});
	}

	// Validate hex colors
	const hexColorRegex = /^#[0-9A-Fa-f]{6}$/;
	if (data.branding?.guidelines?.colorPalette) {
		const palette = data.branding.guidelines.colorPalette;
		['primary', 'secondary', 'accent'].forEach((color) => {
			if (palette[color] && !hexColorRegex.test(palette[color])) {
				errors.push({
					path: `branding.guidelines.colorPalette.${color}`,
					message: 'Color must be a valid hex color (e.g., #FFD530)'
				});
			}
		});
	}

	// Validate IBAN format (basic check)
	if (data.banking?.accounts) {
		data.banking.accounts.forEach((account: any, index: number) => {
			if (account.iban) {
				const iban = account.iban.replace(/\s/g, '');
				if (iban.length < 15 || iban.length > 34) {
					errors.push({
						path: `banking.accounts[${index}].iban`,
						message: 'IBAN must be between 15 and 34 characters'
					});
				}
			}
		});
	}

	// Validate catalog.feeds[]
	const feedErrors = validateFeeds(data);
	errors.push(...feedErrors);

	// Validate inline-catalog caps (signature, highlights, featured)
	const capErrors = validateInlineCaps(data);
	errors.push(...capErrors);

	return errors;
}

/**
 * Validate catalog.feeds[] entries
 * Each feed must have type/format/url, HTTPS url, and (when present) date-time lastUpdated.
 */
function validateFeeds(data: any): ValidationError[] {
	const errors: ValidationError[] = [];
	const feeds: any[] = Array.isArray(data?.catalog?.feeds) ? data.catalog.feeds : [];

	const validTypes = [
		'products',
		'menu',
		'services',
		'packages',
		'portfolio',
		'reservations',
		'rooms',
		'other'
	];
	const validFormats = [
		'json',
		'jsonl',
		'xml',
		'csv',
		'rss',
		'atom',
		'schema.org-jsonld',
		'google-merchant-xml',
		'gtfs',
		'ical',
		'other'
	];

	feeds.forEach((feed, idx) => {
		const base = `catalog.feeds[${idx}]`;
		if (!feed || typeof feed !== 'object') {
			errors.push({ path: base, message: 'Feed entry must be an object' });
			return;
		}
		if (!feed.type) {
			errors.push({ path: `${base}.type`, message: 'Feed type is required' });
		} else if (!validTypes.includes(feed.type)) {
			errors.push({
				path: `${base}.type`,
				message: `Invalid feed type "${feed.type}". Must be one of: ${validTypes.join(', ')}`
			});
		}
		if (!feed.format) {
			errors.push({ path: `${base}.format`, message: 'Feed format is required' });
		} else if (!validFormats.includes(feed.format)) {
			errors.push({
				path: `${base}.format`,
				message: `Invalid feed format "${feed.format}". Must be one of: ${validFormats.join(', ')}`
			});
		}
		if (!feed.url) {
			errors.push({ path: `${base}.url`, message: 'Feed url is required' });
		} else if (typeof feed.url !== 'string' || !feed.url.startsWith('https://')) {
			errors.push({ path: `${base}.url`, message: 'Feed url must be an HTTPS URL' });
		}
		if (feed.lastUpdated !== undefined) {
			const date = new Date(feed.lastUpdated);
			if (isNaN(date.getTime())) {
				errors.push({
					path: `${base}.lastUpdated`,
					message: 'lastUpdated must be a valid ISO 8601 date-time'
				});
			}
		}
		if (feed.itemCount !== undefined) {
			if (typeof feed.itemCount !== 'number' || feed.itemCount < 0 || !Number.isInteger(feed.itemCount)) {
				errors.push({
					path: `${base}.itemCount`,
					message: 'itemCount must be a non-negative integer'
				});
			}
		}
		if (feed.language !== undefined && !/^[a-z]{2}$/.test(feed.language)) {
			errors.push({
				path: `${base}.language`,
				message: 'Feed language must be a valid ISO 639-1 code (e.g., en, fr, de)'
			});
		}
	});

	return errors;
}

/**
 * Enforce inline-catalog hard caps:
 *   featured.products / featured.services <= 12
 *   catalog.signature <= 6
 *   catalog.highlights <= 6
 */
function validateInlineCaps(data: any): ValidationError[] {
	const errors: ValidationError[] = [];

	const featured = data?.featured;
	if (featured && typeof featured === 'object') {
		(['products', 'services'] as const).forEach((k) => {
			const arr = featured[k];
			if (Array.isArray(arr) && arr.length > 12) {
				errors.push({
					path: `featured.${k}`,
					message: `featured.${k} is capped at 12 items in v2.0 (got ${arr.length}). Move the rest to catalog.feeds[].`
				});
			}
		});
	}

	const sig = data?.catalog?.signature;
	if (Array.isArray(sig) && sig.length > 6) {
		errors.push({
			path: 'catalog.signature',
			message: `catalog.signature is capped at 6 items (got ${sig.length}). Move the rest to a catalog.feeds[] entry.`
		});
	}

	const hl = data?.catalog?.highlights;
	if (Array.isArray(hl) && hl.length > 6) {
		errors.push({
			path: 'catalog.highlights',
			message: `catalog.highlights is capped at 6 items (got ${hl.length}). Move the rest to a catalog.feeds[] entry.`
		});
	}

	return errors;
}

/**
 * Reject any v1 inline-catalog fields. Used at moderate level so v1 files fail
 * with a clear migration message rather than an opaque schema error.
 */
function validateNoInlineCatalog(data: any): ValidationError[] {
	const errors: ValidationError[] = [];
	const forbidden = ['menu', 'dailyMenu', 'services', 'packages', 'portfolio', 'productFeeds', 'serviceFeeds'];
	const catalog = data?.catalog;
	if (!catalog || typeof catalog !== 'object') return errors;
	for (const key of forbidden) {
		if (key in catalog) {
			errors.push({
				path: `catalog.${key}`,
				message: `catalog.${key} was removed in v2.0. Move this data to catalog.feeds[] (see docs/MIGRATION-v1-to-v2.md).`
			});
		}
	}
	return errors;
}

/**
 * Get warnings for recommended but missing fields
 */
function getRecommendedFieldWarnings(data: any): ValidationWarning[] {
	const warnings: ValidationWarning[] = [];

	// Recommend HTTPS over HTTP
	if (data.organization?.website?.startsWith('http://')) {
		warnings.push({
			path: 'organization.website',
			message: 'HTTPS is recommended for website URL',
			type: 'security'
		});
	}

	// brandAssets was used by early docs and tools but was never part of the schema
	if (data.brandAssets !== undefined) {
		warnings.push({
			path: 'brandAssets',
			message: '"brandAssets" is not part of the schema and is ignored by consumers; rename it to "branding" (same structure: logos.vector/raster, icons, guidelines)',
			type: 'deprecated'
		});
	}

	// Recommend verification section
	if (!data.verification) {
		warnings.push({
			path: 'verification',
			message: 'Verification section is recommended for domain and wallet proofs',
			type: 'recommended'
		});
	}

	// Recommend security section
	if (!data.security) {
		warnings.push({
			path: 'security',
			message: 'Security section with security email is recommended',
			type: 'recommended'
		});
	}

	// Recommend communications section
	if (!data.communications) {
		warnings.push({
			path: 'communications',
			message: 'Communications section is recommended for social media and support info',
			type: 'recommended'
		});
	}

	// Recommend AI settings
	if (!data.ai) {
		warnings.push({
			path: 'ai',
			message: 'AI settings section is recommended to control AI indexing',
			type: 'recommended'
		});
	}

	// Warn if catalog exists but has no external feeds (v2: catalog without feeds is incomplete)
	if (data.catalog && typeof data.catalog === 'object') {
		const feeds = data.catalog.feeds;
		const hasFeeds = Array.isArray(feeds) && feeds.length > 0;
		const hasAnyInlinePreview =
			(Array.isArray(data.catalog.signature) && data.catalog.signature.length > 0) ||
			(Array.isArray(data.catalog.highlights) && data.catalog.highlights.length > 0);
		if (!hasFeeds && !hasAnyInlinePreview) {
			warnings.push({
				path: 'catalog.feeds',
				message:
					'Catalog section is present but has no feeds[] or inline previews. Add a catalog.feeds[] entry pointing to your external menu/products/portfolio.',
				type: 'recommended'
			});
		}
	}

	// Warn about stale feeds (lastUpdated > 90 days ago)
	if (Array.isArray(data?.catalog?.feeds)) {
		const ninetyDaysAgoMs = 90 * 24 * 60 * 60 * 1000;
		const now = Date.now();
		data.catalog.feeds.forEach((feed: any, idx: number) => {
			if (feed?.lastUpdated) {
				const t = new Date(feed.lastUpdated).getTime();
				if (!isNaN(t) && now - t > ninetyDaysAgoMs) {
					warnings.push({
						path: `catalog.feeds[${idx}].lastUpdated`,
						message: `Feed has not been updated in over 90 days (lastUpdated: ${feed.lastUpdated})`,
						type: 'staleness'
					});
				}
			}
		});
	}

	return warnings;
}

/**
 * Get validation summary
 */
export function getValidationSummary(result: ValidationResult): string {
	if (result.valid) {
		if (result.warnings.length === 0) {
			return '✅ Valid ofero.json file with no warnings';
		} else {
			return `✅ Valid ofero.json file with ${result.warnings.length} warning(s)`;
		}
	} else {
		return `❌ Invalid ofero.json file with ${result.errors.length} error(s)`;
	}
}
