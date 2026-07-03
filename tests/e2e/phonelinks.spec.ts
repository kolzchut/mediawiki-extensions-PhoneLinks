import { test, expect } from '@playwright/test';
import { readFileSync } from 'fs';
import { join } from 'path';

// Read the extension's real style module from source so this test can never
// drift from what ships. The module is plain CSS (no Less-specific syntax), so
// the browser parses it directly.
const css = readFileSync(
  join( __dirname, '..', '..', 'resources', 'ext.phoneLinks.styles.less' ),
  'utf8'
);

// Markup mirrors what Hooks::onLinkerMakeExternalLink emits for the shipped
// default (hidden on desktop): a real tel: link plus a plain-text fallback,
// both always present so the HTML is device-agnostic.
const fixture = `<!doctype html>
<meta charset="utf-8">
<title>PhoneLinks fixture</title>
<style>${css}</style>
<a href="tel:+972521234567" class="external text phonenum ext-phonelinks-dialable">052-1234567</a>
<span class="phonenum phonenum-unlinked ext-phonelinks-not-dialable">052-1234567</span>`;

test( 'reveals the tel: link only on devices that can place calls', async ( { page } ) => {
	await page.setContent( fixture );

	const canDial = await page.evaluate(
		() => matchMedia( '(hover: none) and (pointer: coarse)' ).matches
	);

	const link = page.locator( 'a.ext-phonelinks-dialable' );
	const text = page.locator( 'span.ext-phonelinks-not-dialable' );

	if ( canDial ) {
		// Phones / tablets: the number is a tappable tel: link.
		await expect( link ).toBeVisible();
		await expect( text ).toBeHidden();
	} else {
		// Desktop: the number is inert plain text. The link is display:none, so
		// it is also out of the tab order and the accessibility tree — pressing
		// Tab never lands on it.
		await expect( link ).toBeHidden();
		await expect( text ).toBeVisible();

		await page.keyboard.press( 'Tab' );
		const linkFocused = await link.evaluate( ( el ) => el === document.activeElement );
		expect( linkFocused ).toBe( false );
	}
} );
