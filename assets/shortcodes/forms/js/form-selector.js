(function ($) {
	'use strict';

	class DealerluxFormSelector {
		/**
		 * @param {HTMLSelectElement} element Select element.
		 */
		constructor(element) {
			this.$select = $(element);
			this.placeholder = this.$select.data('placeholder') || 'Select a form';
			this.allowClear = this.$select.data('allow-clear') === true ||
				this.$select.data('allow-clear') === 'true';

			this.initialize();
		}

		/**
		 * Initialize Select2 and redirect behavior.
		 *
		 * @return {void}
		 */
		initialize() {
			if (
				! this.$select.length ||
				typeof $.fn.select2 !== 'function'
			) {
				return;
			}

			if (this.$select.hasClass('select2-hidden-accessible')) {
				return;
			}

			this.$select.select2({
				width: '100%',
				placeholder: this.placeholder,
				allowClear: this.allowClear
			});

			this.$select.on(
				'select2:select.dealerluxFormSelector',
				(event) => this.handleSelection(event)
			);
		}

		/**
		 * Redirect to the URL stored in the selected option.
		 *
		 * @param {Object} event Select2 selection event.
		 * @return {void}
		 */
		handleSelection(event) {
			const selectedUrl = event &&
				event.params &&
				event.params.data
				? event.params.data.id
				: '';

			if (!selectedUrl) {
				return;
			}

			window.location.assign(selectedUrl);
		}
	}

	/**
	 * Initialize all selector instances on the page.
	 *
	 * @return {void}
	 */
	function initializeFormSelectors() {
		$(
			'[data-dealerlux-form-selector] ' +
			'.dealerlux-form-selector__select'
		).each(function () {
			new DealerluxFormSelector(this);
		});
	}

	$(initializeFormSelectors);
})(jQuery);