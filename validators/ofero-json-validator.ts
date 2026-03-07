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
		'brandAssets',
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

		if (!data.metadata.schemaVersion || data.metadata.schemaVersion !== 'ofero-metadata-1.0') {
			errors.push({
				path: 'metadata.schemaVersion',
				message: 'Schema version must be "ofero-metadata-1.0"'
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
	if (data.brandAssets?.guidelines?.colorPalette) {
		const palette = data.brandAssets.guidelines.colorPalette;
		['primary', 'secondary', 'accent'].forEach((color) => {
			if (palette[color] && !hexColorRegex.test(palette[color])) {
				errors.push({
					path: `brandAssets.guidelines.colorPalette.${color}`,
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

	// Validate menu items
	if (data.catalog?.menu?.categories) {
		data.catalog.menu.categories.forEach((cat: any, catIdx: number) => {
			const base = `catalog.menu.categories[${catIdx}]`;

			// Validate serviceHours format HH:MM-HH:MM
			if (cat.serviceHours !== undefined) {
				const hoursRegex = /^\d{2}:\d{2}-\d{2}:\d{2}$/;
				if (!hoursRegex.test(cat.serviceHours)) {
					errors.push({
						path: `${base}.serviceHours`,
						message: `serviceHours must be in HH:MM-HH:MM format (e.g., "08:00-12:00"), got "${cat.serviceHours}"`
					});
				}
			}

			if (cat.items) {
				cat.items.forEach((item: any, itemIdx: number) => {
					const itemBase = `${base}.items[${itemIdx}]`;

					if (item.price < 0) {
						errors.push({
							path: `${itemBase}.price`,
							message: 'Price must be a non-negative number'
						});
					}

					if (item.ingredients !== undefined && !Array.isArray(item.ingredients)) {
						errors.push({
							path: `${itemBase}.ingredients`,
							message: 'ingredients must be an array of strings'
						});
					}
				});
			}
		});
	}

	// Validate dailyMenu
	if (data.catalog?.dailyMenu) {
		const dm = data.catalog.dailyMenu;
		const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
		const validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
		const validCourses = ['starter', 'soup', 'main', 'dessert', 'drink', 'side', 'other'];

		if (dm.weekOf !== undefined && !dateRegex.test(dm.weekOf)) {
			errors.push({
				path: 'catalog.dailyMenu.weekOf',
				message: 'weekOf must be a valid date in YYYY-MM-DD format'
			});
		}

		if (dm.schedule) {
			// Check for unexpected day keys
			Object.keys(dm.schedule).forEach((day) => {
				if (!validDays.includes(day)) {
					errors.push({
						path: `catalog.dailyMenu.schedule.${day}`,
						message: `Invalid day key "${day}". Must be one of: ${validDays.join(', ')}`
					});
				}
			});

			validDays.forEach((day) => {
				const items: any[] = dm.schedule[day];
				if (!items) return;

				if (!Array.isArray(items)) {
					errors.push({
						path: `catalog.dailyMenu.schedule.${day}`,
						message: `schedule.${day} must be an array`
					});
					return;
				}

				items.forEach((item: any, idx: number) => {
					const base = `catalog.dailyMenu.schedule.${day}[${idx}]`;

					if (!item.name?.default) {
						errors.push({
							path: `${base}.name`,
							message: 'Daily menu item must have a name with a default value'
						});
					}

					if (item.price === undefined || item.price === null) {
						errors.push({
							path: `${base}.price`,
							message: 'Daily menu item must have a price'
						});
					} else if (typeof item.price !== 'number' || item.price < 0) {
						errors.push({
							path: `${base}.price`,
							message: 'Daily menu item price must be a non-negative number'
						});
					}

					if (item.course !== undefined && !validCourses.includes(item.course)) {
						errors.push({
							path: `${base}.course`,
							message: `Invalid course "${item.course}". Must be one of: ${validCourses.join(', ')}`
						});
					}

					if (item.ingredients !== undefined && !Array.isArray(item.ingredients)) {
						errors.push({
							path: `${base}.ingredients`,
							message: 'ingredients must be an array of strings'
						});
					}

					if (item.portionSize !== undefined && typeof item.portionSize !== 'string') {
						errors.push({
							path: `${base}.portionSize`,
							message: 'portionSize must be a string (e.g., "220g", "350ml")'
						});
					}
				});
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

	// Restaurant-specific recommendations
	if (data.catalog?.menu?.categories) {
		data.catalog.menu.categories.forEach((cat: any, catIdx: number) => {
			if (cat.items) {
				cat.items.forEach((item: any, itemIdx: number) => {
					const base = `catalog.menu.categories[${catIdx}].items[${itemIdx}]`;
					if (!item.ingredients || item.ingredients.length === 0) {
						warnings.push({
							path: `${base}.ingredients`,
							message: `Menu item "${item.name?.default || item.id}" has no ingredients listed`,
							type: 'recommended'
						});
					}
					if (!item.portionSize) {
						warnings.push({
							path: `${base}.portionSize`,
							message: `Menu item "${item.name?.default || item.id}" has no portionSize specified`,
							type: 'recommended'
						});
					}
				});
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
