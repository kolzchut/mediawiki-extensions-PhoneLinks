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

use ExtensionRegistry;
use MediaWiki\Config\Config;
use MediaWiki\Hook\LinkerMakeExternalLinkHook;
use MediaWiki\Html\Html;

/**
 * Hook handlers for the PhoneLinks extension
 */
class Hooks implements LinkerMakeExternalLinkHook {

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
	 * Modify telephone links based on device type and configuration.
	 *
	 * @inheritDoc
	 */
	public function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs, $linkType ) {
		// Don't use parse_url() - as it doesn't correctly detect all tel: links
		if ( !str_starts_with( $url, 'tel:' ) ) {
			return true;
		}

		// Add phonenum class and remove target attribute for telephone links
		$attribs['class'] = ( $attribs['class'] ?? '' ) . ' phonenum';
		unset( $attribs['target'] );

		$hideOnMobile = $this->config->get( 'PhoneLinksHideOnMobile' );
		$hideOnDesktop = $this->config->get( 'PhoneLinksHideOnDesktop' );

		// If not hiding on any device, allow normal rendering
		if ( !$hideOnMobile && !$hideOnDesktop ) {
			return true;
		}

		$isMobile = false;

		// Detect mobile device if MobileDetect extension is available
		if ( ExtensionRegistry::getInstance()->isLoaded( 'MobileDetect' ) ) {
			$isMobile = \MediaWiki\Extension\MobileDetect\MobileDetect::isMobile();
		}

		// Determine whether links should be disabled for the current device
		$disableLinks = $isMobile ? (bool)$hideOnMobile : (bool)$hideOnDesktop;

		if ( $disableLinks ) {
			// Replace link with a span element
			$link = Html::rawElement( 'span', [ 'class' => 'phonenum phonenum-unlinked' ], $text );
			return false;
		}
	}
}
