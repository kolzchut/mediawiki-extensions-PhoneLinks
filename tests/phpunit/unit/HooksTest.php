<?php

namespace MediaWiki\Extension\PhoneLinks\Tests\Unit;

use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\PhoneLinks\Hooks;
use MediaWikiUnitTestCase;

/**
 * @covers \MediaWiki\Extension\PhoneLinks\Hooks
 */
class HooksTest extends MediaWikiUnitTestCase {

	/**
	 * Run the LinkerMakeExternalLink handler and return the resulting state.
	 *
	 * @return array{ret:mixed,link:string,attribs:array}
	 */
	private function invoke(
		bool $hideOnMobile,
		bool $hideOnDesktop,
		string $url,
		string $text = 'Call us',
		array $attribs = []
	): array {
		$hooks = new Hooks( new HashConfig( [
			'PhoneLinksHideOnMobile' => $hideOnMobile,
			'PhoneLinksHideOnDesktop' => $hideOnDesktop,
		] ) );
		// MediaWiki pre-populates $link with its own markup; we use a sentinel
		// so we can tell whether the handler replaced it.
		$link = 'UNTOUCHED';
		$ret = $hooks->onLinkerMakeExternalLink( $url, $text, $link, $attribs, 'free' );
		return [ 'ret' => $ret, 'link' => $link, 'attribs' => $attribs ];
	}

	public function testNonTelLinkIsLeftEntirelyAlone() {
		$result = $this->invoke( true, true, 'https://example.com', 'Example', [ 'class' => 'external' ] );
		$this->assertTrue( $result['ret'] );
		$this->assertSame( 'UNTOUCHED', $result['link'] );
		$this->assertSame( [ 'class' => 'external' ], $result['attribs'], 'Non-tel attribs untouched' );
	}

	public function testLinkRenderedNormallyWhenNothingHidden() {
		$result = $this->invoke( false, false, 'tel:+972123', 'Call' );
		$this->assertTrue( $result['ret'], 'Handler defers to MediaWiki for the markup' );
		$this->assertSame( 'UNTOUCHED', $result['link'] );
		$this->assertStringContainsString( 'phonenum', $result['attribs']['class'] );
	}

	public function testTargetAttributeStripped() {
		$result = $this->invoke( false, false, 'tel:+972123', 'Call', [ 'target' => '_blank' ] );
		$this->assertArrayNotHasKey( 'target', $result['attribs'] );
	}

	public function testPlainTextEverywhereWhenHiddenOnBothDeviceClasses() {
		$result = $this->invoke( true, true, 'tel:+972123', 'Call' );
		$this->assertFalse( $result['ret'] );
		$this->assertStringNotContainsString( '<a', $result['link'], 'No link element at all' );
		$this->assertStringContainsString( 'phonenum-unlinked', $result['link'] );
		$this->assertStringContainsString( 'Call', $result['link'] );
	}

	public function testDualRenderWhenHiddenOnDesktopOnly() {
		// The shipped default: a tappable link on dialable devices, text elsewhere.
		$result = $this->invoke( false, true, 'tel:+972123', 'Call' );
		$this->assertFalse( $result['ret'] );
		$link = $result['link'];
		$this->assertStringContainsString( '<a ', $link, 'Emits a real link' );
		$this->assertStringContainsString( 'tel:+972123', $link );
		$this->assertStringContainsString( 'ext-phonelinks-dialable', $link, 'Link shown only when dialable' );
		$this->assertStringContainsString( '<span', $link, 'Emits a plain-text fallback' );
		$this->assertStringContainsString(
			'phonenum-unlinked ext-phonelinks-not-dialable', $link, 'Text shown when not dialable' );
	}

	public function testDualRenderWhenHiddenOnMobileOnly() {
		// Inverse configuration: link on non-dialable devices, text on mobile.
		$result = $this->invoke( true, false, 'tel:+972123', 'Call' );
		$this->assertFalse( $result['ret'] );
		$link = $result['link'];
		$this->assertStringContainsString( 'ext-phonelinks-not-dialable', $link, 'Link shown when not dialable' );
		$this->assertStringContainsString(
			'phonenum-unlinked ext-phonelinks-dialable', $link, 'Text shown when dialable' );
	}

	public function testExistingClassesArePreserved() {
		$result = $this->invoke( false, true, 'tel:+972123', 'Call', [ 'class' => 'external text' ] );
		$this->assertStringContainsString( 'external text', $result['link'] );
		$this->assertStringContainsString( 'phonenum', $result['link'] );
	}
}
