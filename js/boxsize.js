/**
 * @file
 *
 * Provides a behavior for the boxsize element.
 */

(function (once) {
  /**
   * Stores the axes lock options in the element's data.
   *
   * @param {HTMLElement} element
   *  The element to Boxsize element wrapper that contains the
   *  inputs related to either margin or padding.
   *
   * @returns {void}
   */
  function setLockState(element) {
    element.dataset.axisLock = [
      element.querySelector('input[name*="[lock][x]"]:checked') ? 1 : 0,
      element.querySelector('input[name*="[lock][y]"]:checked') ? 1 : 0,
      element.querySelector('input[name*="[lock][all]"]:checked') ? 1 : 0,
    ].join('-');
  };

  /**
   * Matches the values of the inputs based on the lock state.
   *
   * @param {HTMLElement} element
   *   The element to Boxsize element wrapper
   *
   * @returns {void}
   */
  function matchValues(element) {
    const value = {
      'top': element.querySelector('[name*="[top]"]').value,
      'left': element.querySelector('[name*="[left]"]').value,
    };

    const elements = {
      'top': element.querySelector('[name*="[top]"]'),
      'right': element.querySelector('[name*="[right]"]'),
      'bottom': element.querySelector('[name*="[bottom]"]'),
      'left': element.querySelector('[name*="[left]"]'),
    };

    // Enable all elements.
    Object.values(elements).forEach((el) => {
      el.removeAttribute('disabled');
    });

    // The lock state is in the form [x]-[y]-[all].
    switch(element.dataset.axisLock) {
      case '1-1-1':
      case '1-0-1':
      case '0-0-1':
      case '0-1-1':
        elements.right.value = value.top;
        elements.right.setAttribute('disabled', 'disabled');
        elements.bottom.value = value.top;
        elements.bottom.setAttribute('disabled', 'disabled');
        elements.left.value = value.top;
        elements.left.setAttribute('disabled', 'disabled');
        break;
      case '0-1-0':
        elements.bottom.value = value.top;
        elements.bottom.setAttribute('disabled', 'disabled');
        break;
      case '1-1-0':
        elements.right.value = value.left;
        elements.right.setAttribute('disabled', 'disabled');
        elements.bottom.value = value.top;
        elements.bottom.setAttribute('disabled', 'disabled');
        break;
      case '1-0-0':
        elements.right.value = value.left;
        elements.right.setAttribute('disabled', 'disabled');
        break;
    }
  }

  Drupal.behaviors.boxSize = {
    attach: function (context) {
      const elements = once('boxSizeElement', '.js-so-boxsize', context);
      elements.forEach(function (element) {
        // Add a lock state to the element via data.
        setLockState(element);
        matchValues(element)

        // Add a change handler to the lock inputs.
        element.querySelectorAll('input[name*="[lock]').forEach(function (input) {
          input.addEventListener('change', function () {
            setLockState(element);
            matchValues(element)
          });
        });

        // Add a change handler to the top and left inputs.
        element.querySelectorAll('[name*="[top]"], [name*="[left]"]').forEach(input => {
          input.addEventListener('change', function (event) {
            matchValues(element)
          });
        })
      });
    }
  }
})(once);
