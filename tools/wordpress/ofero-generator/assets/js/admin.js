/**
 * Ofero Generator Admin JavaScript
 *
 * @package Ofero_Generator
 */

(function($) {
    'use strict';

    // Tab switching
    $('.ofero-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();

        var tab = $(this).data('tab');

        // Update tab active state
        $('.ofero-tabs .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Show correct content
        $('.ofero-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');

        // Update URL hash
        if (history.pushState) {
            history.pushState(null, null, '#tab-' + tab);
        }
    });

    // Check URL hash on load
    if (window.location.hash) {
        var hash = window.location.hash.replace('#', '');
        if (hash.startsWith('tab-')) {
            var tabName = hash.replace('tab-', '');
            $('.ofero-tabs .nav-tab[data-tab="' + tabName + '"]').trigger('click');
        }
    }

    // Add repeater item
    $(document).on('click', '.ofero-add-item', function() {
        var containerId = $(this).data('container');
        var templateId = $(this).data('template');
        var container = $('#' + containerId);
        var template = $('#' + templateId).html();

        container.append(template);

        // Trigger custom event
        container.find('.ofero-repeater-item').last().trigger('ofero:item-added');
    });

    // Remove repeater item
    $(document).on('click', '.ofero-repeater-remove', function() {
        if (confirm(oferoGenerator.strings.confirmDelete)) {
            $(this).closest('.ofero-repeater-item').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });

    // Update repeater title on input change
    $(document).on('change keyup', '.ofero-repeater-item input:first', function() {
        var item = $(this).closest('.ofero-repeater-item');
        var value = $(this).val();
        if (value) {
            item.find('.ofero-repeater-title').text(value);
        }
    });

    // Media uploader
    var mediaFrame;

    $(document).on('click', '.ofero-media-select', function(e) {
        e.preventDefault();

        var button = $(this);
        var urlInput = button.siblings('.ofero-media-url');

        // If the media frame already exists, reopen it
        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        // Create a new media frame
        mediaFrame = wp.media({
            title: 'Select or Upload Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        // When an image is selected
        mediaFrame.on('select', function() {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            urlInput.val(attachment.url);

            // Update preview if exists
            var preview = urlInput.closest('.ofero-repeater-content').find('.ofero-asset-preview');
            if (preview.length) {
                preview.find('img').attr('src', attachment.url);
            } else {
                urlInput.closest('.ofero-field-row').after(
                    '<div class="ofero-asset-preview"><img src="' + attachment.url + '" alt="" style="max-width: 150px; max-height: 100px;"></div>'
                );
            }
        });

        mediaFrame.open();
    });

    // Auto-save functionality
    var autoSaveTimer;
    var autoSaveEnabled = typeof oferoGenerator !== 'undefined' && oferoGenerator.autoSave;

    if (autoSaveEnabled) {
        $('#ofero-editor-form').on('change input', 'input, select, textarea', function() {
            clearTimeout(autoSaveTimer);

            $('.ofero-autosave-status').text(oferoGenerator.strings.saving);

            autoSaveTimer = setTimeout(function() {
                saveDraft();
            }, 3000);
        });
    }

    function saveDraft() {
        var formData = collectFormData();

        $.ajax({
            url: oferoGenerator.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ofero_save_draft',
                nonce: oferoGenerator.nonce,
                data: JSON.stringify(formData)
            },
            success: function(response) {
                if (response.success) {
                    $('.ofero-autosave-status').text(oferoGenerator.strings.saved);
                    setTimeout(function() {
                        $('.ofero-autosave-status').text('');
                    }, 2000);
                }
            },
            error: function() {
                $('.ofero-autosave-status').text(oferoGenerator.strings.error);
            }
        });
    }

    // Collect form data as JSON
    function collectFormData() {
        var data = {
            language: $('#language').val(),
            domain: $('#domain').val(),
            canonicalUrl: $('#canonicalUrl').val(),
            metadata: {
                version: $('#metadata_version').val(),
                schemaVersion: 'ofero-metadata-1.0'
            },
            organization: {
                legalName: $('#org_legalName').val(),
                brandName: $('#org_brandName').val(),
                entityType: $('#org_entityType').val(),
                legalForm: $('#org_legalForm').val(),
                description: $('#org_description').val(),
                website: $('#org_website').val(),
                contactEmail: $('#org_contactEmail').val(),
                contactPhone: $('#org_contactPhone').val(),
                identifiers: {
                    global: {},
                    primaryIncorporation: {
                        country: $('#inc_country').val(),
                        registrationNumber: $('#inc_registrationNumber').val(),
                        taxId: $('#inc_taxId').val(),
                        vatNumber: $('#inc_vatNumber').val()
                    },
                    perCountry: []
                }
            },
            locations: collectArrayData('location'),
            banking: collectBankingData(),
            wallets: collectWalletsData(),
            brandAssets: collectBrandData(),
            communications: {
                social: collectSocialData(),
                support: collectSupportData()
            }
        };

        return data;
    }

    function collectArrayData(prefix) {
        var items = [];
        $('input[name="' + prefix + '_name[]"]').each(function(i) {
            var name = $(this).val();
            if (!name) return;

            items.push({
                name: name,
                type: $('select[name="' + prefix + '_type[]"]').eq(i).val(),
                address: {
                    street: $('input[name="' + prefix + '_street[]"]').eq(i).val(),
                    city: $('input[name="' + prefix + '_city[]"]').eq(i).val(),
                    region: $('input[name="' + prefix + '_region[]"]').eq(i).val(),
                    postalCode: $('input[name="' + prefix + '_postal[]"]').eq(i).val(),
                    country: $('input[name="' + prefix + '_country[]"]').eq(i).val()
                },
                phone: $('input[name="' + prefix + '_phone[]"]').eq(i).val(),
                email: $('input[name="' + prefix + '_email[]"]').eq(i).val()
            });
        });
        return items;
    }

    function collectBankingData() {
        var items = [];
        $('input[name="bank_iban[]"]').each(function(i) {
            var iban = $(this).val();
            if (!iban) return;

            items.push({
                accountName: $('input[name="bank_accountName[]"]').eq(i).val(),
                bankName: $('input[name="bank_name[]"]').eq(i).val(),
                iban: iban,
                bic: $('input[name="bank_bic[]"]').eq(i).val(),
                currency: $('input[name="bank_currency[]"]').eq(i).val()
            });
        });
        return items;
    }

    function collectWalletsData() {
        var items = [];
        $('input[name="wallet_address[]"]').each(function(i) {
            var address = $(this).val();
            if (!address) return;

            items.push({
                blockchain: $('select[name="wallet_blockchain[]"]').eq(i).val(),
                network: $('input[name="wallet_network[]"]').eq(i).val(),
                address: address,
                label: $('input[name="wallet_label[]"]').eq(i).val()
            });
        });
        return items;
    }

    function collectBrandData() {
        var items = [];
        $('input[name="brand_url[]"]').each(function(i) {
            var url = $(this).val();
            if (!url) return;

            items.push({
                type: $('select[name="brand_type[]"]').eq(i).val(),
                variant: $('select[name="brand_variant[]"]').eq(i).val(),
                url: url,
                format: $('input[name="brand_format[]"]').eq(i).val()
            });
        });
        return items;
    }

    function collectSocialData() {
        var items = [];
        $('input[name="social_url[]"]').each(function(i) {
            var url = $(this).val();
            if (!url) return;

            items.push({
                platform: $('select[name="social_platform[]"]').eq(i).val(),
                url: url
            });
        });
        return items;
    }

    function collectSupportData() {
        var items = [];
        $('input[name="support_contact[]"]').each(function(i) {
            var contact = $(this).val();
            if (!contact) return;

            items.push({
                type: $('select[name="support_type[]"]').eq(i).val(),
                contact: contact
            });
        });
        return items;
    }

    // Form validation before submit
    $('#ofero-editor-form').on('submit', function(e) {
        var requiredFields = {
            'language': 'Language',
            'domain': 'Domain',
            'canonicalUrl': 'Canonical URL',
            'metadata_version': 'Version',
            'org_legalName': 'Legal Name',
            'org_entityType': 'Entity Type'
        };

        var errors = [];

        for (var field in requiredFields) {
            var value = $('#' + field).val();
            if (!value || value.trim() === '') {
                errors.push(requiredFields[field] + ' is required');
                $('#' + field).css('border-color', '#d63638');
            } else {
                $('#' + field).css('border-color', '');
            }
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }
    });

    // Clear validation styling on input
    $('input, select, textarea').on('focus', function() {
        $(this).css('border-color', '');
    });

    // ===========================================
    // Business Type Selection
    // ===========================================

    // Highlight selected card on page load
    $('.ofero-business-type-radio:checked').closest('.ofero-business-type-card').addClass('selected');

    // Handle card selection
    $(document).on('change', '.ofero-business-type-radio', function() {
        $('.ofero-business-type-card').removeClass('selected');
        $(this).closest('.ofero-business-type-card').addClass('selected');

        var selectedType = $(this).val();
        applyBusinessTypeToOrganization(selectedType);

        // Submit the form immediately so PHP re-renders the correct tabs
        // We add a hidden flag so PHP knows it's a business type change, not a full save
        $('<input>').attr({type: 'hidden', name: 'ofero_change_business_type', value: '1'}).appendTo('#ofero-editor-form');
        $('#ofero-editor-form').submit();
    });

    // Suggest entityType in Organization tab based on business type
    function applyBusinessTypeToOrganization(type) {
        var suggestions = {
            'general':          'company',
            'restaurant':       'store',
            'hotel':            'company',
            'hotel_restaurant': 'company',
            'online_store':     'store',
            'clinic':           'company',
            'auto_service':     'company',
            'services':         'company',
        };
        var suggested = suggestions[type];
        if (suggested && $('#org_entityType').val() === '' || $('#org_entityType').val() === $('#org_entityType option:first').val()) {
            $('#org_entityType').val(suggested);
        }
    }

    // ===========================================
    // Menu Tab — Add Category / Add Item
    // ===========================================

    var menuCatCount = $('#menu-categories-container .ofero-menu-category').length;

    $(document).on('click', '#ofero-add-menu-category', function() {
        var catIndex = menuCatCount++;
        var html = '<div class="ofero-menu-category ofero-repeater-item" data-cat-index="' + catIndex + '">' +
            '<div class="ofero-repeater-header">' +
                '<span class="ofero-repeater-title">' + oferoGenerator.strings.newCategory + '</span>' +
                '<button type="button" class="button button-small ofero-menu-category-toggle">' + oferoGenerator.strings.collapse + '</button>' +
                '<button type="button" class="button button-small button-link-delete ofero-repeater-remove">' + oferoGenerator.strings.removeCategory + '</button>' +
            '</div>' +
            '<div class="ofero-menu-category-body ofero-repeater-content">' +
                '<table class="form-table">' +
                    '<tr><th>' + oferoGenerator.strings.categoryId + '</th><td><input type="text" name="menu_cat_id[]" class="regular-text" placeholder="e.g., pizza"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.categoryName + '</th><td><input type="text" name="menu_cat_name[]" class="regular-text"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.sortOrder + '</th><td><input type="number" name="menu_cat_sort[]" class="small-text" min="1" value="' + (catIndex + 1) + '"></td></tr>' +
                '</table>' +
                '<h4 style="margin: 16px 0 8px; padding-left: 12px;">' + oferoGenerator.strings.itemsInCategory + '</h4>' +
                '<div class="ofero-menu-items-container" id="menu-items-' + catIndex + '"></div>' +
                '<button type="button" class="button ofero-add-menu-item" style="margin: 8px 0 0 12px;" data-cat-index="' + catIndex + '">+ ' + oferoGenerator.strings.addItem + '</button>' +
            '</div>' +
        '</div>';
        $('#menu-categories-container').append(html);
    });

    $(document).on('click', '.ofero-add-menu-item', function() {
        var catIndex = $(this).data('cat-index');
        var $container = $('#menu-items-' + catIndex);
        var html = '<div class="ofero-menu-item ofero-repeater-item">' +
            '<div class="ofero-repeater-header" style="background: #f0f0f0;">' +
                '<span class="ofero-repeater-title">' + oferoGenerator.strings.newItem + '</span>' +
                '<button type="button" class="button button-small button-link-delete ofero-repeater-remove">' + oferoGenerator.strings.removeItem + '</button>' +
            '</div>' +
            '<div class="ofero-menu-item-body ofero-repeater-content" style="padding: 12px;">' +
                '<input type="hidden" name="menu_item_cat[]" value="' + catIndex + '">' +
                '<table class="form-table" style="margin:0;">' +
                    '<tr><th style="width:140px;">' + oferoGenerator.strings.itemId + '</th><td><input type="text" name="menu_item_id[]" class="regular-text" placeholder="e.g., margherita"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.name + '</th><td><input type="text" name="menu_item_name[]" class="regular-text"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.description + '</th><td><textarea name="menu_item_desc[]" rows="2" class="large-text"></textarea></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.price + '</th><td><input type="number" name="menu_item_price[]" class="small-text" min="0" step="0.01"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.imageUrl + '</th><td><input type="url" name="menu_item_image[]" class="regular-text" placeholder="https://..."></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.prepTime + '</th><td><input type="text" name="menu_item_prep[]" class="small-text" placeholder="15 min"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.available + '</th><td><label><input type="checkbox" name="menu_item_available[]" value="1" checked> ' + oferoGenerator.strings.itemAvailable + '</label></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.popular + '</th><td><label><input type="checkbox" name="menu_item_popular[]" value="1"> ' + oferoGenerator.strings.markPopular + '</label></td></tr>' +
                '</table>' +
            '</div>' +
        '</div>';
        $container.append(html);
    });

    // Collapse/expand menu categories and items
    $(document).on('click', '.ofero-menu-category-toggle, .ofero-menu-item-toggle', function() {
        var $body = $(this).closest('.ofero-repeater-item').find('.ofero-repeater-content').first();
        $body.slideToggle(200);
        $(this).text($body.is(':visible') ? oferoGenerator.strings.collapse : oferoGenerator.strings.expand);
    });

    // ===========================================
    // Restaurant Details — Add Delivery Platform
    // ===========================================

    $(document).on('click', '#ofero-add-delivery-platform', function() {
        var html = '<div class="ofero-repeater-item" style="display:flex;gap:12px;align-items:center;margin-bottom:8px;">' +
            '<input type="text" name="rd_delivery_name[]" class="regular-text" placeholder="' + oferoGenerator.strings.platformName + '">' +
            '<input type="url" name="rd_delivery_url[]" class="regular-text" placeholder="https://...">' +
            '<button type="button" class="button button-small button-link-delete ofero-repeater-remove">' + oferoGenerator.strings.remove + '</button>' +
        '</div>';
        $('#delivery-platforms-container').append(html);
    });

    // ===========================================
    // Services Tab — Add Service
    // ===========================================

    $(document).on('click', '#ofero-add-service', function() {
        var html = '<div class="ofero-repeater-item ofero-service-item">' +
            '<div class="ofero-repeater-header">' +
                '<span class="ofero-repeater-title">' + oferoGenerator.strings.newService + '</span>' +
                '<button type="button" class="button button-small button-link-delete ofero-repeater-remove">' + oferoGenerator.strings.remove + '</button>' +
            '</div>' +
            '<div class="ofero-repeater-content">' +
                '<table class="form-table">' +
                    '<tr><th style="width:160px;">' + oferoGenerator.strings.serviceId + '</th><td><input type="text" name="svc_id[]" class="regular-text"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.name + '</th><td><input type="text" name="svc_name[]" class="regular-text"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.description + '</th><td><textarea name="svc_desc[]" rows="2" class="large-text"></textarea></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.category + '</th><td><input type="text" name="svc_category[]" class="regular-text"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.price + '</th><td><input type="number" name="svc_price[]" class="small-text" min="0" step="0.01"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.duration + '</th><td><input type="text" name="svc_duration[]" class="small-text" placeholder="30 min"></td></tr>' +
                    '<tr><th>' + oferoGenerator.strings.available + '</th><td><label><input type="checkbox" name="svc_available[]" value="1" checked> ' + oferoGenerator.strings.serviceAvailable + '</label></td></tr>' +
                '</table>' +
            '</div>' +
        '</div>';
        $('#services-container').append(html);
    });

    // ===========================================
    // Translation Tab Functionality
    // ===========================================

    // Language search/filter
    $('#ofero-language-filter').on('input', function() {
        var query = $(this).val().toLowerCase();
        $('#ofero-language-list .ofero-language-checkbox').each(function() {
            var langName = $(this).data('lang-name');
            if (langName.indexOf(query) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Toggle translation fields when language is checked/unchecked
    $(document).on('change', '.ofero-lang-toggle', function() {
        var lang = $(this).data('lang');
        var isChecked = $(this).is(':checked');
        var $translationRows = $('.ofero-lang-row-' + lang);

        if (isChecked) {
            $translationRows.slideDown(200);
        } else {
            $translationRows.slideUp(200);
        }

        // Update visibility of translation content area
        updateTranslationContentVisibility();
    });

    // Update translation content visibility based on selected languages
    function updateTranslationContentVisibility() {
        var hasSelectedLanguages = $('.ofero-lang-toggle:checked').length > 0;

        if (hasSelectedLanguages) {
            $('.ofero-no-languages-message').hide();
            $('.ofero-translations-content').slideDown(200);
        } else {
            $('.ofero-translations-content').slideUp(200);
            $('.ofero-no-languages-message').show();
        }
    }

    // Select/Deselect all languages
    $(document).on('click', '#ofero-select-all-langs', function(e) {
        e.preventDefault();
        $('#ofero-language-list .ofero-language-checkbox:visible input').prop('checked', true).trigger('change');
    });

    $(document).on('click', '#ofero-deselect-all-langs', function(e) {
        e.preventDefault();
        $('#ofero-language-list .ofero-language-checkbox input').prop('checked', false).trigger('change');
    });

    // Quick language selection (common languages)
    $(document).on('click', '.ofero-quick-lang-select', function(e) {
        e.preventDefault();
        var langs = $(this).data('langs').split(',');

        // First deselect all
        $('#ofero-language-list .ofero-language-checkbox input').prop('checked', false);

        // Then select specified languages
        langs.forEach(function(lang) {
            $('.ofero-lang-toggle[data-lang="' + lang.trim() + '"]').prop('checked', true);
        });

        // Trigger change to update UI
        $('.ofero-lang-toggle').first().trigger('change');
    });

    // ===========================================
    // Keywords Tag Input
    // ===========================================

    function initKeywordTags() {
        var $hidden = $('#keywords');
        var $list   = $('#keywords-tags-list');
        var $input  = $('#keywords-tags-input');

        if (!$hidden.length) return;

        function normalize(val) {
            return val.trim().toLowerCase();
        }

        function getExisting() {
            return $hidden.val()
                .split(',')
                .map(function(k) { return k.trim(); })
                .filter(function(k) { return k.length > 0; });
        }

        function renderTags() {
            $list.empty();
            var tags = getExisting();
            tags.forEach(function(tag) {
                var $tag = $('<span class="ofero-tag"></span>').text(tag);
                var $remove = $('<button type="button" class="ofero-tag-remove" aria-label="Remove">&times;</button>');
                $remove.on('click', function() {
                    removeTag(tag);
                });
                $tag.append($remove);
                $list.append($tag);
            });
        }

        function addTag(raw) {
            var tag = raw.trim();
            if (!tag) return;
            var existing = getExisting();
            var dupe = existing.some(function(k) {
                return normalize(k) === normalize(tag);
            });
            if (dupe) return;
            existing.push(tag);
            $hidden.val(existing.join(', '));
            renderTags();
        }

        function removeTag(tag) {
            var existing = getExisting().filter(function(k) {
                return normalize(k) !== normalize(tag);
            });
            $hidden.val(existing.join(', '));
            renderTags();
        }

        // Handle Enter and comma
        $input.on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag($input.val().replace(/,/g, ''));
                $input.val('');
            }
        });

        // Also add on blur so pasting + clicking away works
        $input.on('blur', function() {
            var val = $input.val().replace(/,/g, '').trim();
            if (val) {
                addTag(val);
                $input.val('');
            }
        });

        // Support pasting a comma-separated list
        $input.on('paste', function(e) {
            var pasted = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
            if (pasted.indexOf(',') !== -1) {
                e.preventDefault();
                pasted.split(',').forEach(function(part) {
                    addTag(part);
                });
                $input.val('');
            }
        });

        // Click anywhere in the field focuses input
        $('#keywords-tags-field').on('click', function() {
            $input.focus();
        });

        // Initial render from saved value
        renderTags();
    }

    $(document).ready(function() {
        initKeywordTags();
    });

    // ===========================================
    // License Verification Refresh
    // ===========================================

    $(document).on('click', '.ofero-refresh-license', function(e) {
        e.preventDefault();

        var $button = $(this);
        var $section = $button.closest('.ofero-license-section');

        // Add spinning animation
        $button.addClass('spinning').prop('disabled', true);

        $.ajax({
            url: oferoGenerator.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ofero_refresh_license',
                nonce: oferoGenerator.nonce
            },
            success: function(response) {
                if (response.success && response.data.badge_html) {
                    // Replace the badge with new HTML
                    $section.find('.ofero-license-badge').replaceWith(response.data.badge_html);
                }
            },
            error: function() {
                alert('Failed to refresh license status. Please try again.');
            },
            complete: function() {
                $button.removeClass('spinning').prop('disabled', false);
            }
        });
    });

})(jQuery);
