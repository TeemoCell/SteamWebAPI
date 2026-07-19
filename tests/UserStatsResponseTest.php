<?php

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Syntax\SteamApi\Steam\User\Stats;

class UserStatsResponseTest extends TestCase
{
    public function test_global_achievement_percentages_returns_empty_array_when_wrapper_is_missing(): void
    {
        $stats = $this->statsReturning((object) []);

        $this->assertSame([], $stats->GetGlobalAchievementPercentagesForApp(620));
    }

    public function test_global_achievement_percentages_returns_empty_array_when_achievements_are_missing(): void
    {
        $stats = $this->statsReturning((object) [
            'achievementpercentages' => (object) [],
        ]);

        $this->assertSame([], $stats->GetGlobalAchievementPercentagesForApp(620));
    }

    public function test_user_stats_returns_empty_array_when_wrapper_is_missing(): void
    {
        $stats = $this->statsReturning((object) []);

        $this->assertSame([], $stats->GetUserStatsForGame(620));
    }

    public function test_user_stats_returns_empty_array_when_achievements_are_missing(): void
    {
        $stats = $this->statsReturning((object) [
            'playerstats' => (object) [],
        ]);

        $this->assertSame([], $stats->GetUserStatsForGame(620));
    }

    private function statsReturning(object $response): Stats&MockObject
    {
        $stats = $this->getMockBuilder(Stats::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setUpClient'])
            ->getMock();

        $stats->method('setUpClient')->willReturn($response);

        return $stats;
    }
}
