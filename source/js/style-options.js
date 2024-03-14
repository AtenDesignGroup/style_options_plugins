(($, Drupal, once) => {
  function toggleFields(selectElement, allHideFields) {
    const hideFields = allHideFields[selectElement.value] || [];
    (document.querySelectorAll('.me-hide-field') || []).forEach((field) => {
      field.classList.remove('me-hide-field');
    });
    const fieldSelectors = hideFields.map((field) => '.field--name-' + field.replace('_', '-'));
    fieldSelectors.forEach((selector) => {
      const el = document.querySelector(selector);
      if (el) {
        el.classList.add('me-hide-field');
      }
    });
  }
  Drupal.behaviors.mercuryEditorStyleOptions = {
    attach: function(context, settings) {
      if (settings.mercuryEditorStyleOptions) {
        once('me-style-options', '.me-style-option-tpl-suggestion').forEach((selectElement) => {
          selectElement.addEventListener('change', (e) => { toggleFields(e.target, settings.mercuryEditorStyleOptions.hideFields) });
          toggleFields(selectElement, settings.mercuryEditorStyleOptions.hideFields);
        });
      }
    }
  }
})(jQuery, Drupal, once)
