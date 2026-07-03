# Changelog

## Version 1.1.0 (2026-07-03)
- Decide link-vs-plain-text on the client by input capability instead of
  server-side device detection, so a page's HTML no longer varies by device
  and stays safe to cache behind a shared CDN. Removes the runtime dependency
  on the MobileDetect extension.

## Version 1.0.0 (2026-01-21)
- Breaking change: **Requires MediaWiki 1.43+**
- Modern hook handler architecture with dependency injection
- Proper namespacing: `MediaWiki\Extension\PhoneLinks\`

## Version 0.0.1
Initial release
