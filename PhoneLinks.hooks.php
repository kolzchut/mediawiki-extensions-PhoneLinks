<?php
/**
 * Hooks for PhoneLinks extension
 *
 * @file
 * @ingroup Extensions
 */


// These *should not* be used for validation - they are intentionally naive and allow non-existing
// phone numbers, for simplicity's sake!
//@todo special Israeli prefixes: 1-599-\d{6}, 1-700-\d{6}, 1-800-\d{6}, 1-801-\d{6}
//@todo add option to auto-recognize phone numbers
$phonePatterns = array(
	'Israel' => '/0(2|3|4|5[0-9]|8|9|7[1-9])-?[2-9]\d{6}',
	'USA' => '((\(\d{3}\)?)|(\d{3}))([-./]?)(\d{3})([\s-./]?)(\d{4})'
);

class PhoneLinksHooks {
	public static function makeConfig() {
		return new GlobalVarConfig( 'wgPhoneLinks' );
	}

	private static function getConfig() {
		return ConfigFactory::getDefaultInstance()->makeConfig( 'wgPhoneLinks' );
	}

	public static function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs ) {
		// Don't use parse_url() - as it doesn't correctly detect all tel: links, ffs
		if ( substr( $url, 0, 4 ) !== 'tel:' ) {
			return true;
		}

		$attribs['class'] .= ' phonenum';
		unset( $attribs['target'] );

		$hideOnMobile = self::getConfig()->get( 'HideOnMobile' );
		$hideOnDesktop = self::getConfig()->get( 'HideOnDesktop' );

		if( !$hideOnMobile && !$hideOnDesktop ) {
			return true;
		}

		$disableLinks = true;
		if ( ExtensionRegistry::getInstance()->isLoaded( 'MobileDetect' ) ) {
			$isMobile = MobileDetect::isMobile();
		} else {
			$isMobile = false;
		}

		if (
			( $isMobile && !$hideOnMobile )
	        || ( !$isMobile && !$hideOnDesktop )
		) {
			$disableLinks = false;
		}


		if( $disableLinks ) {
			$link = Html::element( 'span', array( 'class' => 'phonenum phonenum-unlinked' ), $text );
			return false;
		}

		return true;
	}
}
