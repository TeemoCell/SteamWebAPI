<?php

require_once 'BaseTester.php';

/** @group Item */
class ItemTest extends BaseTester
{
    public function test_it_gets_items_for_an_app_by_user_id()
    {
        $inventory = $this->steamClient->item()->GetPlayerItems($this->appId, $this->id64);

        $this->assertCount(3, $inventory->items);

        $item = $inventory->items->first();

        $this->checkItemProperties($item);
    }
}