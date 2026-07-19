<?php

use Syntax\SteamApi\Exceptions\UnrecognizedId;
use Syntax\SteamApi\SteamIdConverter;

require_once 'BaseTester.php';

/** @group Id */
class IdTest extends BaseTester
{

    /** @test
     * @throws UnrecognizedId
     */
    public function test_it_converts_an_id()
    {
        $ids = $this->steamClient->convertId($this->id64);

        $this->assertEquals($this->id32, $ids->id32);
        $this->assertEquals($this->id64, $ids->id64);
        $this->assertEquals($this->id3, $ids->id3);
    }

    public function test_it_preserves_the_steam2_universe_alias(): void
    {
        $converter = new SteamIdConverter();

        $steam0 = $converter->convertId('STEAM_0:1:188144897');
        $steam1 = $converter->convertId('STEAM_1:1:188144897');

        $this->assertSame('STEAM_0:1:188144897', $steam0->id32);
        $this->assertSame('STEAM_1:1:188144897', $steam1->id32);
        $this->assertSame($steam0->id64, $steam1->id64);
        $this->assertSame($steam0->id3, $steam1->id3);
    }

    public function test_steam64_and_steam3_default_to_the_legacy_steam2_alias(): void
    {
        $converter = new SteamIdConverter();

        // Convert STEAM_1 first to verify that state does not leak between calls.
        $converter->convertId('STEAM_1:1:188144897');

        $fromSteam64 = $converter->convertId('76561198336555523');
        $fromSteam3 = $converter->convertId('[U:1:376289795]');

        $this->assertSame('STEAM_0:1:188144897', $fromSteam64->id32);
        $this->assertSame('STEAM_0:1:188144897', $fromSteam3->id32);
        $this->assertSame('[U:1:376289795]', $fromSteam64->id3);
        $this->assertSame('[U:1:376289795]', $fromSteam3->id3);
    }
}