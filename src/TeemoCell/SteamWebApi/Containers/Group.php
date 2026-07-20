<?php

namespace TeemoCell\SteamWebApi\Containers;

use SimpleXMLElement;
use TeemoCell\SteamWebApi\SteamIdConverter;
use Illuminate\Support\Collection;
use TeemoCell\SteamWebApi\Containers\Group\Details;
use TeemoCell\SteamWebApi\Containers\Group\MemberDetails;

class Group
{
    public string $groupID64;

    public Details $groupDetails;

    public MemberDetails $memberDetails;

    public int $startingMember;

    public Collection $members;

    /**
     * @param SimpleXMLElement $group
     */
    public function __construct(SimpleXMLElement $group)
    {
        $this->groupID64 = (string) $group->groupID64;
        $this->groupDetails = new Details($group->groupDetails);
        $this->memberDetails = new MemberDetails($group->groupDetails);
        $this->startingMember = (int) (string) $group->startingMember;

        $this->members = new Collection;
        $converter = new SteamIdConverter();

        foreach ($group->members->steamID64 as $member) {
            $this->members->add($converter->convertId((string) $member));
        }
    }
}
