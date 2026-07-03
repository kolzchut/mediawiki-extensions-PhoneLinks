<?php
/**
 * This file is part of the MediaWiki extension PhoneLinks.
 *
 * PhoneLinks is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhoneLinks is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhoneLinks. If not, see <http://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup Extensions
 */

namespace MediaWiki\Extension\PhoneLinks;

use MediaWiki\Config\Config;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\LinkerMakeExternalLinkHook;
use MediaWiki\Html\Html;

/**
 * Hook handlers for the PhoneLinks extension
 */
class Hooks implements LinkerMakeExternalLinkHook, BeforePageDisplayHook {

	private Config $config;

	/**
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Handle the LinkerMakeExternalLink hook
	 *
	 * Render telephone links so the same HTML works on every device: emit both a
	 * real tel: link and a plain-text span, and let ext.phoneLinks.styles reveal
	 * the appropriate one by input capability (see the module for why). Because
	 * the markup no longer depends on server-side device detection, the output
	 * stays identical per device and remains safe to cache at a shared CDN.
	 *
	 * @inheritDoc
	 */
	public function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs, $linkType ) {
		// Don't use parse_url() - as it doesn't correctly detect all tel: links
		if ( !str_starts_with( $url, 'tel:' ) ) {
			return true;
		}

		// Add phonenum class and remove target attribute for telephone links
		unset( $attribs['target'] );
		$attribs['class'] = trim( ( $attribs['class'] ?? '' ) . ' phonenum' );

		$hideOnMobile = (bool)$this->config->get( 'PhoneLinksHideOnMobile' );
		$hideOnDesktop = (bool)$this->config->get( 'PhoneLinksHideOnDesktop' );

		// Not hiding anywhere: render the tel: link normally on every device.
		if ( !$hideOnMobile && !$hideOnDesktop ) {
			return true;
		}

		// Hidden everywhere: never a link, just plain text.
		if ( $hideOnMobile && $hideOnDesktop ) {
			$link = Html::rawElement( 'span', [ 'class' => 'phonenum phonenum-unlinked' ], $text );
			return false;
		}

		// Hidden on one device class only: emit BOTH the tel: link and a
		// plain-text span, each gated by the capability media query in
		// ext.phoneLinks.styles. The rendered HTML is device-agnostic.
		$linkVisibility = $hideOnDesktop ? 'ext-phonelinks-dialable' : 'ext-phonelinks-not-dialable';
		$textVisibility = $hideOnDesktop ? 'ext-phonelinks-not-dialable' : 'ext-phonelinks-dialable';

		$anchorAttribs = [ 'href' => $url ] + $attribs;
		$anchorAttribs['class'] = trim( $attribs['class'] . ' ' . $linkVisibility );

		$link = Html::rawElement( 'a', $anchorAttribs, $text )
			. Html::rawElement( 'span',
				[ 'class' => 'phonenum phonenum-unlinked ' . $textVisibility ], $text );

		return false;
	}

	/**
	 * Load the capability-based visibility styles whenever tel: links may be
	 * hidden on some device class. Gated on config (which is device-independent),
	 * so it does not affect the cacheability of the page.
	 *
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $this->config->get( 'PhoneLinksHideOnMobile' )
			|| $this->config->get( 'PhoneLinksHideOnDesktop' )
		) {
			$out->addModuleStyles( [ 'ext.phoneLinks.styles' ] );
		}
	}
}
