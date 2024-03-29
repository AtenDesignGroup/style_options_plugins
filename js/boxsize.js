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
    console.log('matchValues');
    const value = {
      'top': element.querySelector('[name*="[top]"]').value,
      'right': element.querySelector('[name*="[right]"]').value,
    };

    // The lock state is in the form [x]-[y]-[all].
    switch(element.dataset.axisLock) {
      case '1-1-1':
        element.querySelector('[name*="[right]"]').value = value.top;
        element.querySelector('[name*="[bottom]"]').value = value.top;
        element.querySelector('[name*="[left]"]').value = value.top;
        break;
      case '0-1-0':
        element.querySelector('[name*="[bottom]"]').value = value.top;
        break;
      case '1-1-0':
        element.querySelector('[name*="[bottom]"]').value = value.top;
        element.querySelector('[name*="[left]"]').value = value.right;
        break;
      case '1-0-0':
        element.querySelector('[name*="[left]"]').value = value.right;
        break;
    }
  }

  Drupal.behaviors.boxSize = {
    attach: function (context) {
      const elements = once('boxSizeElement', '.js-so-boxsize', context);
      elements.forEach(function (element) {
        // Add a lock state to the element via data.
        setLockState(element);

        // Add a change handler to the lock inputs.
        element.querySelectorAll('input[name*="[lock]').forEach(function (input) {
          input.addEventListener('change', function () {
            setLockState(element);
            matchValues(element)
          });
        });

        // Add a change handler to the top and right inputs.
        element.querySelectorAll('[name*="[top]"], [name*="[right]"]').forEach(input => {
          input.addEventListener('change', function (event) {
            matchValues(element)
          });
        })
      });
    }
  }
})(once);
