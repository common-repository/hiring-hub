/**
 * External dependencies
 */
import { decodeJSON, encodeJSON } from '@teydeastudio/utils/src/json.js';

/**
 * Callback function
 *
 * @return {void}
 */
const callback = () => {
	/**
	 * List of CSS classes
	 */
	const classes = {
		checkbox: 'wp-block-hiring-hub-query-loop-filtering-filter__checkbox',
		checkboxSelected: 'wp-block-hiring-hub-query-loop-filtering-filter__checkbox--selected',
		choice: 'wp-block-hiring-hub-query-loop-filtering-filter__choice',
		choiceSelected: 'wp-block-hiring-hub-query-loop-filtering-filter__choice--selected',
		clearButton: 'wp-block-hiring-hub-query-loop-filtering-filter__clear-button',
		dropdown: 'wp-block-hiring-hub-query-loop-filtering-filter__dropdown',
		dropdownVisible: 'wp-block-hiring-hub-query-loop-filtering-filter__dropdown--visible',
		filter: 'wp-block-hiring-hub-query-loop-filtering-filter',
		filterWithDropdown: 'wp-block-hiring-hub-query-loop-filtering-filter--dropdown',
		radio: 'wp-block-hiring-hub-query-loop-filtering-filter__radio',
		radioSelected: 'wp-block-hiring-hub-query-loop-filtering-filter__radio--selected',
		selectionSummary: 'wp-block-hiring-hub-query-loop-filtering-filter__selection-summary',
	};

	/**
	 * Hide all visible dropdowns at once
	 *
	 * @return {void}
	 */
	const hideAllDropdowns = () => {
		for ( const dropdown of document.querySelectorAll( `.${ classes.dropdownVisible }` ) ) {
			dropdown.classList.remove( classes.dropdownVisible );
		}
	};

	/**
	 * Show single dropdown
	 *
	 * @param {Node} dropdown Node to show.
	 *
	 * @return {void}
	 */
	const showDropdown = ( dropdown ) => {
		dropdown.classList.add( classes.dropdownVisible );
	};

	/**
	 * Select single choice
	 *
	 * @param {Node}   choice Choice to select.
	 * @param {string} type   Field's type; either "checkbox" or "radio".
	 *
	 * @return {void}
	 */
	const selectChoice = ( choice, type ) => {
		const icon = choice.querySelector( `.${ 'checkbox' === type ? classes.checkbox : classes.radio }` );

		choice.classList.add( classes.choiceSelected );
		icon.classList.add( 'checkbox' === type ? classes.checkboxSelected : classes.radioSelected );
	};

	/**
	 * Unselect single choice
	 *
	 * @param {Node}   choice Choice to unselect.
	 * @param {string} type   Field's type; either "checkbox" or "radio".
	 *
	 * @return {void}
	 */
	const unselectChoice = ( choice, type ) => {
		const icon = choice.querySelector( `.${ 'checkbox' === type ? classes.checkbox : classes.radio }` );

		choice.classList.remove( classes.choiceSelected );
		icon.classList.remove( 'checkbox' === type ? classes.checkboxSelected : classes.radioSelected );
	};

	/**
	 * Unselect all choices
	 *
	 * @param {Node}   filter Filter to process.
	 * @param {string} type   Field's type; either "checkbox" or "radio".
	 *
	 * @return {void}
	 */
	const unselectAllChoices = ( filter, type ) => {
		for ( const choice of filter.querySelectorAll( `.${ classes.choice }` ) ) {
			unselectChoice( choice, type );
		}
	};

	/**
	 * Update the selection summary
	 *
	 * @param {Node}  filter  Filter to process.
	 * @param {Array} choices Current choices.
	 *
	 * @return {void}
	 */
	const updateSelectionSummary = ( filter, choices = [] ) => {
		const hasDropdown = filter.classList.contains( classes.filterWithDropdown );

		if ( ! hasDropdown ) {
			return;
		}

		const selectionSummary = filter.querySelector( `.${ classes.selectionSummary }` );

		if ( 0 === choices.length ) {
			selectionSummary.textContent = '';
			return;
		}

		const selectionLabelsMapping = decodeJSON( filter.dataset.selectionLabelsMapping );
		let updatedText = '';

		for ( const choice of choices ) {
			updatedText += ( '' === updatedText ? '' : ', ' ) + selectionLabelsMapping[ choice ];
		}

		selectionSummary.textContent = updatedText;
	};

	/**
	 * Update the dropdown visibility
	 *
	 * @param {Event} event Click event.
	 *
	 * @return {void}
	 */
	const updateDropdownVisibility = ( event ) => {
		// Is the click within a filter?
		const filter = event.target.closest( `.${ classes.filter }` );

		if ( ! filter ) {
			hideAllDropdowns();
			return;
		}

		// Is the click within a visible dropdown?
		const isWithinVisibleDropdown = null !== event.target.closest( `.${ classes.dropdownVisible }` );

		if ( isWithinVisibleDropdown ) {
			return;
		}

		// Get the dropdown data.
		const dropdown = filter.querySelector( `.${ classes.dropdown }` );
		const hasDropdown = filter.classList.contains( classes.filterWithDropdown );

		if ( ! hasDropdown ) {
			/**
			 * There's no dropdown within the current filter;
			 * hide them all
			 */
			hideAllDropdowns();
		} else {
			/**
			 * Check whether there's an active dropdown within
			 * the current filter; if no, show it; otherwise,
			 * hide them all
			 */
			const hasActiveDropdown = dropdown.classList.contains( classes.dropdownVisible );
			hideAllDropdowns();

			if ( ! hasActiveDropdown ) {
				showDropdown( dropdown );
			}
		}
	};

	/**
	 * Update the checkbox/radio field's state
	 *
	 * @param {Event} event Click event.
	 *
	 * @return {void}
	 */
	const updateFieldsState = ( event ) => {
		// Is the click within a filter?
		const filter = event.target.closest( `.${ classes.filter }` );

		if ( ! filter ) {
			return;
		}

		// Get the filter type.
		const { type } = filter.dataset;

		// Get the input field and its current value.
		const inputField = filter.querySelector( 'input[type="hidden"]' );

		if ( ! inputField ) {
			return;
		}

		/**
		 * Is the click within the "clear" button?
		 */
		const clearButton = event.target.closest( `.${ classes.clearButton }` );

		if ( clearButton ) {
			// Update the field's value.
			inputField.value = '';

			// Uncheck all selections.
			unselectAllChoices( filter, type );

			// Update the selection summary.
			updateSelectionSummary( filter );

			return;
		}

		/**
		 * Is the click within the checkbox/radio button?
		 */
		const choice = event.target.closest( `.${ classes.choice }` );
		const choiceButton = event.target.closest( `.${ classes.choice } button` );

		if ( choice && choiceButton ) {
			// Get the choice value and index.
			const { value } = choiceButton.dataset;

			// Get the current choices.
			let choices = decodeJSON( inputField.value, [] );
			const index = choices.indexOf( value );

			if ( 'checkbox' === type ) {
				/**
				 * Process the checkbox field type
				 */
				if ( -1 !== index ) {
					delete choices[ index ];
					choices = choices.filter( Boolean );
					unselectChoice( choice, type );
				} else {
					choices.push( value );
					selectChoice( choice, type );
				}
			} else if ( 'radio' === type ) {
				/**
				 * Process the radio field type
				 */
				if ( -1 !== index ) {
					choices = [];
					unselectChoice( choice, type );
				} else {
					choices = [ value ];
					unselectAllChoices( filter, type );
					selectChoice( choice, type );
				}
			}

			// Update the field's value.
			inputField.value = encodeJSON( choices );

			// Update the selection summary.
			updateSelectionSummary( filter, choices );
		}
	};

	/**
	 * Hide all opened dropdowns if the user clicks outside any of them
	 *
	 * @param {Event} event Click event.
	 *
	 * @return {void}
	 */
	document.addEventListener( 'click', ( event ) => {
		// Update the dropdown visibility.
		updateDropdownVisibility( event );

		// Update the checkbox/radio field's state.
		updateFieldsState( event );
	} );

	/**
	 * Hide all dropdowns on "Esc" button press
	 *
	 * @param {Event} event Keydown event.
	 *
	 * @return {void}
	 */
	document.addEventListener( 'keydown', ( event ) => {
		// Hide all dropdowns on "Esc" button press.
		if ( 'Escape' === event.key || 'Esc' === event.key ) {
			hideAllDropdowns();
		}
	} );
};

/**
 * Queue a callback function to be called during a browser's
 * idle periods so the main thread is not blocked
 */
if ( window.requestIdleCallback instanceof Function ) {
	window.requestIdleCallback( callback );
} else {
	setTimeout( callback, 0 );
}
