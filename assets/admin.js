/**
 * HideItDude Admin JavaScript
 */

jQuery(document).ready(function($) {
	'use strict';
	
	HideItDude.init();
});

var HideItDude = {
	
	/**
	 * Initialize the plugin
	 */
	init: function() {
		this.initTabs();
		this.initSelectAllButtons();
		this.bindEvents();
	},
	
	/**
	 * Initialize tab functionality
	 */
	initTabs: function() {
		$('.nav-tab').on('click', function(e) {
			e.preventDefault();
			
			var targetTab = $(this).attr('href');
			
			// Remove active class from all tabs and panes
			$('.nav-tab').removeClass('nav-tab-active');
			$('.tab-pane').removeClass('active');
			
			// Add active class to clicked tab
			$(this).addClass('nav-tab-active');
			
			// Show corresponding tab pane
			$(targetTab).addClass('active');
			
			// Update URL hash
			window.location.hash = targetTab;
		});
		
		// Set initial active tab based on URL hash or default to first tab
		var hash = window.location.hash;
		if (hash && $(hash).length) {
			$('.nav-tab[href="' + hash + '"]').click();
		} else {
			$('.nav-tab:first').click();
		}
	},
	
	/**
	 * Initialize Select All buttons
	 */
	initSelectAllButtons: function() {
		$('.hideitdude-checkboxes').each(function() {
			var $container = $(this);
			var $selectAllBtn = $('<button type="button" class="button select-all">Select All</button>');
			var $selectNoneBtn = $('<button type="button" class="button select-none">Select None</button>');
			
			var $buttonGroup = $('<div class="select-buttons"></div>');
			$buttonGroup.append($selectAllBtn).append($selectNoneBtn);
			
			$container.before($buttonGroup);
			
			$selectAllBtn.on('click', function() {
				$container.find('input[type="checkbox"]').prop('checked', true);
			});
			
			$selectNoneBtn.on('click', function() {
				$container.find('input[type="checkbox"]').prop('checked', false);
			});
		});
	},
	
	/**
	 * Bind various events
	 */
	bindEvents: function() {
		// Keyboard shortcut: Ctrl+S to save
		$(document).on('keydown', function(e) {
			if (e.ctrlKey && e.keyCode === 83) {
				e.preventDefault();
				$('form').submit();
			}
		});
	}
};

// Add utility CSS
jQuery(document).ready(function($) {
	$('<style>')
		.text(`
			.select-buttons {
				margin-bottom: 10px;
				text-align: right;
			}
			
			.select-buttons .button {
				margin-left: 5px;
				font-size: 12px;
				padding: 4px 8px;
				height: auto;
				line-height: 1.2;
			}
		`)
		.appendTo('head');
});
