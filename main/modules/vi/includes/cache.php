<?php

/*
 * =========================================================================
 * A very simple cache based on JKNCache for volumes and issues.
 *
 * Structure and contents:
 *      /vi/v_###/i_##/issuu_data.json
 *      /vi/v_###/i_##/archiveorg_thumb.jpg
 *      /vi/v_###/vol_general/vol_thumb.png
 * =========================================================================
 */


/*
 * =========================================================================
 * Cache directories
 * =========================================================================
 */

/**
 * A volume cache directory identified by 'v_' + a 3-padded volume number.
 */
class MJKVICacheDir_Volume extends JKNCacheDir {

	/**
	 * Return the unique ID based on the volume.
	 *
	 * @return string
	 */
    function id(): string {
        $vol = $this->args['volume'];
        $n = str_pad($vol->get_num($strict=true), 3, '0', STR_PAD_LEFT);
        if ($vol->is_erindalian) $n = 'e_' . $n;
        return sprintf('v_%s', $n);
    }
}

/**
 * An issue cache directory identified by 'i_' + a 2-padded volume number.
 */
class MJKVICacheDir_Issue extends JKNCacheDir {

	/**
	 * Return the unique ID based on the issue.
	 *
	 * @return string
	 */
    function id(): string {
        $iss = $this->args['issue'];
        $n = str_pad($iss->get_num(), 2, '0', STR_PAD_LEFT);
        return sprintf('i_%s', $n);
    }
}

/**
 * A general volume cache directory, for storing a volume's non-issue data.
 */
class MJKVICacheDir_VolGeneral extends JKNCacheDir {

	/**
	 * Return a standard ID, the same for every volume.
	 *
	 * @return string
	 */
    function id(): string { return 'vol_general'; }
}


/*
 * =========================================================================
 * Cache objects
 * =========================================================================
 */

/**
 * An issue's JSON Issuu data (embed html, thumbnail URL, etc.).
 */
class MJKVICacheObject_IssuuData extends JKNCacheObject {

	/**
	 * Return the filename.
	 *
	 * @return string
	 */
    function fname(): string { return 'issuu_data.json'; }

	/**
	 * Return the callable that will create the data to cache.
	 *
	 * @param array $args
	 * @return callable
	 */
    function fetcher(array $args=[]): callable {
        return [$this->args['issue'], 'create_issuu_data'];
    }
}

/**
 * An issue's JPEG thumbnail from archive.org.
 */
class MJKVICacheObject_ArchiveorgThumb extends JKNCacheObject {

	/**
	 * Return the filename.
	 *
	 * @return string
	 */
    function fname(): string { return 'archiveorg_thumb.jpg'; }

	/**
	 * Return the callable that will create the data to cache.
	 *
	 * @param array $args
	 * @return callable
	 */
    function fetcher(array $args=[]): callable {
        return [$this->args['issue'], 'create_archiveorg_thumbnail'];
    }
}

/**
 * A volume's PNG thumbnail derived from Issuu and archive.org thumbnails.
 */
class MJKVICacheObject_VolThumb extends JKNCacheObject {

	/**
	 * Return the filename.
	 *
	 * @return string
	 */
	function fname(): string { return 'vol_thumb.png'; }

	/**
	 * Return the callable that will create the data to cache.
	 *
	 * @param array $args
	 * @return callable
	 */
    function fetcher(array $args=[]): callable {
        return [$this->args['volume'], 'make_thumbnail'];
    }
}
