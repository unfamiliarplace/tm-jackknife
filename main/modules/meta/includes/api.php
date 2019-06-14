<?php

/**
 * Provides an interface with post metadata.
 */
final class MJKMetaAPI {
    
    const role_to_db_key = [
        'Author'    => MJKMeta_ACF_Auth::authors,
        'Notes'     => MJKMeta_ACF_Auth::notes_contributors,
        'Photo'     => MJKMeta_ACF_Auth::photographers,
        'Video'     => MJKMeta_ACF_Auth::videographers
    ];


	/*
	 * =========================================================================
	 * Contributors
	 * =========================================================================
	 */

    /**
     * Return the subtitle for the given post ID.
     *
     * @param string $pid
     * @return string
     */
    static function subtitle(string $pid): string {
        return MJKMeta_ACF_Auth::get(MJKMeta_ACF_Subtitle::subtitle, $pid);
    }
    
    /**
     * Return the given array of users filtered to only include WP author role.
     *
     * @param array $contributors
     * @return array
     */
    static function filter_contributors(array $contributors): array {
        return array_filter($contributors, function(array $user): bool {
            return in_array('author', get_userdata($user['ID'])->roles);
        });
    }
    
    /**
     * Return the given users filtered for those eligible to be contributors.
     * Because of how ACF works, $users could be an array or an integer...
     *
     * @param array $users
     * @return array
     */
    static function contributors($users): array {
        if (empty($users)) return [];
        return self::filter_contributors($users);
    }
    
    /**
     * Return the authors for the given post ID.
     *
     * @param string $pid
     * @return array
     */
    static function authors(string $pid): array {
        return self::contributors(MJKMeta_ACF_Auth::get(
                MJKMeta_ACF_Auth::authors, $pid));
    }
    
    /**
     * Return the notes contributors for the given post ID.
     *
     * @param string $pid
     * @return array
     */
    static function notes_contributors(string $pid): array {
        return self::contributors(MJKMeta_ACF_Auth::get(
                MJKMeta_ACF_Auth::notes_contributors, $pid));
    }
    
    /**
     * Return the photographers for the given post ID.
     *
     * @param string $pid
     * @return array
     */
    static function photographers(string $pid): array {
        return self::contributors(MJKMeta_ACF_Auth::get(
                MJKMeta_ACF_Auth::photographers, $pid));
    }
    
    /**
     * Return the outside photo sources for the given post ID.
     *
     * @param string $pid
     * @return array
     */
    static function outside_photo_sources(string $pid): array {
        $sources = [];
        
        if (MJKMeta_ACF_Auth::have_rows(MJKMeta_ACF_Auth::outside_photo_sources, $pid)) {
            while (MJKMeta_ACF_Auth::have_rows(MJKMeta_ACF_Auth::outside_photo_sources, $pid)) {
                the_row();
                
                $source = MJKMeta_ACF_Auth::sub(MJKMeta_ACF_Auth::outside_photo_source);
                $sources[] = $source;
            }
        }
        
        return $sources;
    }
    
    /**
     * Return the videographers for the given post ID.
     *
     * @param string $pid
     * @return array
     */
    static function videographers(string $pid): array {
        return self::contributors(MJKMeta_ACF_Auth::get(
                MJKMeta_ACF_Auth::videographers, $pid));
    }


	/*
	 * =========================================================================
	 * Single user meta
	 * =========================================================================
	 */
    
    /**
     * Return an array of [role => posts] for all the contribution roles,
     * from newest to oldest, of the given user.
     *
     * @param string $uid
     * @return array An array of ['id' => post_id, 'post_date' => datestring]
     */
    static function user_roles_to_posts(string $uid): array {
        
        $role_to_posts = [];
        foreach(self::role_to_db_key as $role => $db_key) {
            $role_to_posts[$role] = self::user_role_posts($uid, $db_key);
        }
        
        return $role_to_posts;
    }
    
    /**
     * Return an array of post IDs for a given role from newest to oldest.
     *
     * @param string $uid
     * @param string $role A role that can be turned into a database metakey.
     * @return array An array of ['id' => post_id, 'post_date' => datestring]
     */
    static function user_role_posts(string $uid, string $role): array {
        global $wpdb;

        $meta_key_role = MJKMeta_ACF_Auth::qualify($role);
        $meta_value_uid = sprintf(':"?%s"?;', $uid);

        $query = $wpdb->prepare("
            SELECT ID
                FROM	wp_postmeta m
                JOIN	wp_posts p
                ON	p.ID = m.post_id

                WHERE   post_status = 'publish' AND
                        post_type = 'post' AND
                        meta_key = '%s' AND
                        meta_value REGEXP '%s'

                ORDER BY post_date DESC
                LIMIT 0, 10000
        ", $meta_key_role, $meta_value_uid);

        // Get results and unpack
        $results = $wpdb->get_results($query);
        $results = array_map(function(stdClass $a): string { return $a->ID; },
                $results);
        
        // Get unqiue. I'm really not sure why we have to do this >:(
        return array_unique($results);
    }

	/**
	 * Return an array of all the posts in the given [role => posts] array.
	 *
	 * @param array $role_to_posts An array mapping a role to the posts in it.
	 * @return array
	 */
    static function all_role(array $role_to_posts): array {
        
        // Flatten/merge
        $all = [];
        foreach($role_to_posts as $role => $posts) {
            $all = array_merge($all, $posts);
        }
        
        // Unique again (there could be dupes between the categories)
        $all = array_unique($all);
        
        // Sort by date descending
        usort($all, function(string $a, string $b): int {
            return -(get_the_time('U', $a) <=> get_the_time('U', $b));
        });
        
        return $all;
    }
}
