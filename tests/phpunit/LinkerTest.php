<?php
/**
 * Copyright 2012-2017 Daniel Kraus <bovender@bovender.de> ('bovender')
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 * @author Daniel Kraus <bovender@bovender.de>
 */

/**
 * Unit tests for the LinkTitles\Linker class.
 *
 * The test class is prefixed with 'LinkTitles' to avoid a naming collision
 * with a class that exists in the MediaWiki core.
 *
 * Ideally the test classes should be namespaced, but when you do that, they
 * will no longer be automatically discovered.
 *
 * @group Database
 */
class LinkTitlesLinkerTest extends LinkTitles\TestCase {
  protected $title;

  protected function setUp() {
    $this->title = $this->insertPage( 'source page', 'This page is the test page' )['title'];
    parent::setUp(); // call last to have the Targets object invalidated after inserting the page
  }

  /**
   * @dataProvider provideLinkContentSmartModeData
   */
  public function testLinkContentSmartMode( $capitalLinks, $smartMode, $input, $expectedOutput ) {
    $this->setMwGlobals( 'wgCapitalLinks', $capitalLinks );
    $config = new LinkTitles\Config();
    $config->firstOnly = false;
    $config->smartMode = $smartMode;
    $linker = new LinkTitles\Linker( $config );
    $this->assertSame( $expectedOutput, $linker->linkContent( $this->title, $input ));
  }

  public function provideLinkContentSmartModeData() {
    return [
      [
        true, // wgCapitalLinks
        true, // smartMode
        'With smart mode on and $wgCapitalLinks = true, this page should link to link target',
        'With smart mode on and $wgCapitalLinks = true, this page should link to [[link target]]'
      ],
      [
        true, // wgCapitalLinks
        false, // smartMode
        'With smart mode off and $wgCapitalLinks = true, this page should link to link target',
        'With smart mode off and $wgCapitalLinks = true, this page should link to [[link target]]'
      ],
      [
        true, // wgCapitalLinks
        true, // smartMode
        'With smart mode on and $wgCapitalLinks = true, this page should link to Link Target',
        'With smart mode on and $wgCapitalLinks = true, this page should link to [[Link target|Link Target]]'
      ],
      [
        true, // wgCapitalLinks
        false, // smartMode
        'With smart mode off and $wgCapitalLinks = true, this page should not link to Link Target',
        'With smart mode off and $wgCapitalLinks = true, this page should not link to Link Target'
      ],
      [
        false, // wgCapitalLinks
        true, // smartMode
        'With smart mode on and $wgCapitalLinks = false, this page should link to Link target',
        'With smart mode on and $wgCapitalLinks = false, this page should link to [[Link target]]'
      ],
      [
        false, // wgCapitalLinks
        true, // smartMode
        'With smart mode on and $wgCapitalLinks = false, this page should link to link target',
        'With smart mode on and $wgCapitalLinks = false, this page should link to [[Link target|link target]]'
      ],
      [
        false, // wgCapitalLinks
        false, // smartMode
        'With smart mode off and $wgCapitalLinks = false, this page should not link to link target',
        'With smart mode off and $wgCapitalLinks = false, this page should not link to link target'
      ],
      [
        false, // wgCapitalLinks
        false, // smartMode
        'With smart mode off and $wgCapitalLinks = false, this page should not link to Link target',
        'With smart mode off and $wgCapitalLinks = false, this page should not link to [[Link target]]'
      ],
      [
        false, // wgCapitalLinks
        true, // smartMode
        'With smart mode on and $wgCapitalLinks = false, this page should link to Link Target',
        'With smart mode on and $wgCapitalLinks = false, this page should link to [[Link target|Link Target]]'
      ],
      [
        false, // wgCapitalLinks
        false, // smartMode
        'With smart mode off and $wgCapitalLinks = false, this page should not link to Link Target',
        'With smart mode off and $wgCapitalLinks = false, this page should not link to Link Target'
      ],
    ];
  }

  /**
   * @dataProvider provideLinkContentFirstOnlyData
   */
  public function testLinkContentFirstOnly( $firstOnly, $input, $expectedOutput ) {
    $config = new LinkTitles\Config();
    $config->firstOnly = $firstOnly;
    $linker = new LinkTitles\Linker( $config );
    $this->assertSame( $expectedOutput, $linker->linkContent( $this->title, $input ));
  }

  public function provideLinkContentFirstOnlyData() {
    return [
      [
        false, // firstOnly
        'With firstOnly = false, link target is a link target multiple times',
        'With firstOnly = false, [[link target]] is a [[link target]] multiple times'
      ],
      [
        false, // firstOnly
        'With firstOnly = false, [[link target]] is a link target multiple times',
        'With firstOnly = false, [[link target]] is a [[link target]] multiple times'
      ],
      [
        true, // firstOnly
        'With firstOnly = true, link target is a link target only once',
        'With firstOnly = true, [[link target]] is a link target only once'
      ],
      [
        true, // firstOnly
        'With firstOnly = true, [[link target]] is a link target only once',
        'With firstOnly = true, [[link target]] is a link target only once'
      ],
    ];
  }

  public function testLinkContentBlackList() {
    $config = new LinkTitles\Config();
    $config->blackList = [ 'Foo', 'Link target', 'Bar' ];
    $linker = new LinkTitles\Linker( $config );
    $text = 'If the link target is blacklisted, it should not be linked';
    $this->assertSame( $text, $linker->linkContent( $this->title, $text ) );
  }
}
