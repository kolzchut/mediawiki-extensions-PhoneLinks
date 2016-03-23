PhoneLinks extension for MediaWiki
========================================

This extension hides phone links (\<a href="tel:">), instead showing only
their static text.


## Configuration
* `$wgPhoneLinksHideOnMobile`: Whether to hide phone links on mobile.
  Default: false.
* `$wgPhoneLinksHideOnDesktop`: Whether to hide phone links on desktop.
  Default: true.

  Note: this works with a soft dependency on Extension:MobileDetect.
  If MobileDetect is not loaded, the platform is always assumed
  to be a desktop.
