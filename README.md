# Legacy Mercury Editor Style Options

In Mercury Editor 2.2.x releases, layout and style option plugins have been
removed as part of an effort to make it possible to uninstall ME without
consequences (other than loosing the authoring enhancements directly provided
by the module).

The style_options_plugins module provides support for the legacy style options
removed from Mercury Editor.

## To use this module:

* Add the repo to composer: `composer config repositories.style_options_plugins vcs https://github.com/AtenDesignGroup/style_options_plugins.git`
* Install the module with composer: `composer require atendesigngroup/style_options_plugins`
* Enable the module: `drush en style_options_plugins`
* Upgrade Mercury Editor with composer: `composer require drupal/mercury_editor:^2.2`
* Clear the cache: `drush cr`
* Replace any references in your themes/modules from `mercury_editor/style_options` to `style_options_plugins/style_option`.
* Test and verify all layouts and paragraphs are rendering correctly.

