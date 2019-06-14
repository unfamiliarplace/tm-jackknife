<?php

/**
 * Provides an interface for Front Page Cards.
 */
final class MJKFP_API {
    
    // The number of Front Page Cards older than the current one to keep
    const archives_to_keep = 1;

	/**
	 * Return the sorted Front Page Cards. FPCs are sorted by start date.
	 *
	 * @param MJKFP_FPCard[] $cards
	 * @param bool|null $desc
	 * @return MJKFP_FPCard[]
	 */
	static function sort_fpcards(array $cards, bool $desc=true): array {
        uasort($cards, function(MJKFP_FPCard $a, MJKFP_FPCard $b): int {
            return $a->get_start() <=> $b->get_start(); }
        );
        
        return ($desc) ? array_reverse($cards) : $cards;
    }

	/**
	 * Return the registered Front Page Cards.
	 *
	 * @return MJKFP_FPCard[]
	 */
	static function fpcards(): array {
        return MJKFPLoader::load_fpcards();
    }
    
	/**
	 * Return the Front Page Cards that don't start in the future.
	 *
	 * @return MJKFP_FPCard[]
	 */
	static function non_future_fpcards(): array {
        $fpcards = self::fpcards();
        return array_filter($fpcards, function (MJKFP_FPCard $fpcard): bool {
	        return !$fpcard->is_future(); });
    }

	/**
	 * Return the Front Page Cards that are eligible for deletion.
	 *
	 * @return MJKFP_FPCard[]
	 */
	static function deletable_fpcards(): array {
        $fpcards = self::non_future_fpcards();
        $fpcards = self::sort_fpcards($fpcards);
        $fpcards = array_slice($fpcards, 1 + self::archives_to_keep);
        return $fpcards;
    }

	/**
	 * Return the current Front Page Card.
	 *
	 * @return MJKFP_FPCard
	 */
	static function current_fpcard(): ?MJKFP_FPCard {
        $eligible_fpcards = self::non_future_fpcards();
        return empty($eligible_fpcards) ? null : reset($eligible_fpcards);
    }
}
