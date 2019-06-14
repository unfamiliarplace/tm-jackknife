<?php

/**
 * Renders the elections page.
 */
final class MJKElectionsRenderer extends JKNRenderer {

	/*
	 * =========================================================================
	 * Messages
	 * =========================================================================
	 */

	/**
	 * Return text for the start of the page.
	 *
	 * @return string
	 */
    static function page_start() {
        return '[vc_row][vc_column][vc_column_text]For more information,' .
               ' see the <a title="Elections FAQ" href="' . home_url() .
               '/contribute/elections-faq">Elections FAQ</a> and the' .
               ' <a href="' . site_url() . '/constitution">constitution</a>.' .
               '<hr />[/vc_column_text][/vc_column][/vc_row]';
    }

    // Top of each column
    const top_bod = '[vc_column_text]<h2>Board of Directors</h2>' .
                    '[/vc_column_text][vc_column_text]' .
					'<a href="http://voting.utoronto.ca">Click here to vote' .
					' if there is an election on.</a>[/vc_column_text]';
    const top_eb = '[vc_column_text]<h2>Editorial Board</h2>[/vc_column_text]' .
                   '[vc_column_text]If there is an election on, the voters\'' .
                   ' list will be sent privately to voters.[/vc_column_text]';

    // Finished text
    const finished_intro = 'This election has closed.';
    const finished_heading = '<h4>Elected %s</h4>';

    // Announcement text
    const announce_heading = '<h3>%s</h3>';
    const announce_bod = '<em>The Medium</em> is currently holding an' .
                         ' election for %s undergraduate student seats on its' .
                         ' board of directors.<br><br>Please direct any' .
                         ' questions to <a href="mailto:cro@themedium.ca">' .
						 'cro@themedium.ca</a>.';
    const announce_eb = 'The Medium is currently holding an election for all' .
                        ' six positions on the Editorial Board:' .
                        ' Editor-in-Chief, News Editor, Arts Editor,' .
                        ' Features Editor, Sports Editor, and Photo Editor.' .
						'<br><br>A candidates&rsquo; forum will be held %s in' .
                        ' The Medium\'s office.<br><br>Please direct any' .
                        ' questions to <a href="mailto:editor@themedium.ca">' .
                        'editor@themedium.ca</a>.';

    // Nomination text
    const nomination_heading = "<h3>Nomination</h3>";

	/**
	 * Return the board election nomination text.
	 *
	 * @return string
	 */
	static function nomination_bod() {
        return '<strong>File: </strong><a href="%s">Board election nomination' .
               ' form for %s</a><br><br>To nominate yourself, simply submit' .
               ' the above form along with a resume. The form requires' .
               ' getting 20 signatures. Bring it to <a title="Contact us"' .
               ' href="' . get_site_url() . '/contact-us">the office</a> or' .
               ' email it to <a href="mailto:cro@themedium.ca">' .
               'cro@themedium.ca</a>.<br><br>Any UTM undergraduate can run.';
    }

	/**
	 * Return the editorial board election nomination text.
	 *
	 * @return string
	 */
	static function nomination_eb() {
        return '<strong>File:</strong> <a href="%s">Editorial board election' .
               ' nomination form for %s</a><br><br>To nominate yourself,' .
               ' simply submit the above form, a resume, and a statement' .
               ' outlining your interest, qualifications, and plans for the' .
               ' position. Bring it to <a title="Contact us" href="' .
               get_site_url() . '/contact-us">the office</a> or email it to' .
               ' <a href="mailto:editor@themedium.ca">editor@themedium.ca</a>.' .
               '<br><br>Any UTM undergraduate or outgoing undergraduate can' .
               ' run. However, unless they have contributed to at least 5' .
               ' issues in the section they are applying to, they must' .
               ' demonstrate significant equivalent experience.';
    }

    // Voting text
    const voting_heading = "<h3>Voting</h3>";
    const voting_bod = 'To vote, go to <a href="https://voting.utoronto.ca">' .
                       'voting.utoronto.ca</a> between %s and %s.' .
                       '<br><br>Any UTM undergraduate can vote.';

	/**
	 * Return a notice about voting in the editorial board election.
	 *
	 * @return string
	 */
	static function voting_eb() {
        return 'To vote, come to <a title="Contact us" href="' .
               get_site_url() . '/contact-us">the office</a>, phone us at' .
               ' 289 633 3963, or email <a' .
               ' href="mailto:editor@themedium.ca">editor@themedium.ca</a>' .
               ' on the voting day (%s). The current editorial board and any' .
               ' student who has contributed to at least half the issues in' .
               ' either semester can vote. All voters will be notified of' .
               ' their eligibility before the end of the voting period.';
    }

    // Timeline text
    const timeline_heading = '<h3>Timeline</h3>';
    const timeline_bod = '<strong>%s -- %s:</strong> Nomination period' .
		'<br><strong>%s:</strong> Candidate statements published' .
		'<br><strong>%s — %s:</strong> Voting period' .
		'<br><strong>%s:</strong> Results announced';
    const timeline_eb = '<strong>%s -- %s:</strong> Nomination period' .
		'<br><strong>%s:</strong> Candidates\' forum' .
		'<br><strong>%s:</strong> Voting day' .
		'<br><strong>%s:</strong> Results announced';


	/*
	 * =========================================================================
	 * Gathering
	 * =========================================================================
	 */

	/**
	 * Return the board of directors elections.
	 *
	 * [[
	 *  'date' => date,
	 *  'finished' => finished,
	 *  'winners' =>
	 *      [
	 *      'name' => name,
	 *      'position' => position
	 *      ]
	 * ]]
	 *
	 * @return array
	 */
	static function gather_bod_elections(): array {

        $bod_elections = [];

        if (MJKElections_ACF::have_rows(MJKElections_ACF::bods)) {
	        while (MJKElections_ACF::have_rows(MJKElections_ACF::bods)) {
                the_row();


                $date = MJKElections_ACF::sub(MJKElections_ACF::bod_date);
                $finished = MJKElections_ACF::sub(MJKElections_ACF::bod_finished);

                // finished
                if ($finished) {

                    $winners = [];

	                if (MJKElections_ACF::have_sub_rows(MJKElections_ACF::bod_ws)) {
	                    while (MJKElections_ACF::have_sub_rows(MJKElections_ACF::bod_ws)) {
                            the_row();

                            $name = MJKElections_ACF::sub(MJKElections_ACF::bod_w_name);

							// For consistency with eb election format.
                            $winners[] = ['name' => $name];
                        }
                    }

                    $bod_elections[] = [
                    	'date' => $date,
	                    'finished' => $finished,
	                    'winners' => $winners
                    ];

                } else {

                    $vacant_seats = MJKElections_ACF::sub(MJKElections_ACF::bod_vacant);
                    $nomination_form = MJKElections_ACF::sub(MJKElections_ACF::bod_form);
                    $nomination_period_start = MJKElections_ACF::sub(MJKElections_ACF::bod_nom_start);
                    $nomination_period_end = MJKElections_ACF::sub(MJKElections_ACF::bod_nom_end);
                    $statements_published = MJKElections_ACF::sub(MJKElections_ACF::bod_statements_day);
                    $voting_period_start = MJKElections_ACF::sub(MJKElections_ACF::bod_voting_start);
                    $voting_period_end = MJKElections_ACF::sub(MJKElections_ACF::bod_voting_end);
                    $results_announced_date = MJKElections_ACF::sub(MJKElections_ACF::bod_results_day);

                    $bod_elections[] = [
	                    'date'                      => $date,
	                    'finished'                  => $finished,
	                    'vacant_seats'               => $vacant_seats,
	                    'nomination_form'           => $nomination_form,
	                    'nomination_period_start'   => $nomination_period_start,
	                    'nomination_period_end'     => $nomination_period_end,
	                    'statements_published'      => $statements_published,
	                    'voting_period_start'       => $voting_period_start,
	                    'voting_period_end'         => $voting_period_end,
	                    'results_announced_date'    => $results_announced_date
                    ];
                }
            }
        }

        return $bod_elections;
    }

	/**
	 * Return the editorial board elections.
	 *
	 * [[
	 *  'date' => date,
	 *  'finished' => finished,
	 *  'winners' =>
	 *      [
	 *      'name' => name,
	 *      'role' => role
	 *      ]
	 * ]]
	 *
	 * @return array
	 */
	static function gather_eb_elections(): array {

        $eb_elections = [];

        if (MJKElections_ACF::have_rows(MJKElections_ACF::ebs)) {
	        while (MJKElections_ACF::have_rows(MJKElections_ACF::ebs)) {
                the_row();

                $date = MJKElections_ACF::sub(MJKElections_ACF::eb_date);
                $finished = MJKElections_ACF::sub(MJKElections_ACF::eb_finished);

                if ($finished) {

                    $winners = [];

	                if (MJKElections_ACF::have_sub_rows(MJKElections_ACF::eb_ws)) {
		                while (MJKElections_ACF::have_sub_rows(MJKElections_ACF::eb_ws)) {
                            the_row();

                            $name = MJKElections_ACF::sub(MJKElections_ACF::eb_w_name);
                            $role = MJKElections_ACF::sub(MJKElections_ACF::eb_w_role);

                            $winners[] = [
	                            'name'      => $name,
	                            'role'      => $role
                            ];
                        }
                    }

                    $eb_elections[] = [
                    	'date' => $date,
	                    'finished' => $finished,
	                    'winners' => $winners
                    ];

                } else {

                    $nomination_form = MJKElections_ACF::sub(MJKElections_ACF::eb_form);
                    $nomination_period_start = MJKElections_ACF::sub(MJKElections_ACF::eb_nom_start);
                    $nomination_period_end = MJKElections_ACF::sub(MJKElections_ACF::eb_nom_end);
                    $candidates_forum_date = MJKElections_ACF::sub(MJKElections_ACF::eb_forum_day);
                    $voting_day = MJKElections_ACF::sub(MJKElections_ACF::eb_voting_day);
                    $results_announced_date = MJKElections_ACF::sub(MJKElections_ACF::eb_results_day);

                    $eb_elections[] = [
	                    'date'                      => $date,
	                    'finished'                  => $finished,
	                    'nomination_form'           => $nomination_form,
	                    'nomination_period_start'   => $nomination_period_start,
	                    'nomination_period_end'     => $nomination_period_end,
	                    'candidates_forum_date'     => $candidates_forum_date,
	                    'voting_day'                => $voting_day,
	                    'results_announced_date'    => $results_announced_date
                    ];
                }
            }
        }

		return $eb_elections;
    }


	/*
	 * =========================================================================
	 * Rendering
	 * =========================================================================
	 */


	/**
	 * Render a finished election.
	 *
	 * @param array $election
	 * @param bool $verbose
	 * @return string
	 */
	static function render_finished_election(array $election,
			bool $verbose): string {

        $buffy = '';

        $date = $election['date'];
        $winners = $election['winners'];

        $buffy .= '[vc_column_text]';

        if ($verbose) {
            $buffy .= sprintf(self::announce_heading, $date);
            $buffy .= self::finished_intro;
        }

        $buffy .= sprintf(self::finished_heading, $date);
        $buffy .= '<ul class="medium_el_winners">';

        foreach ($winners as $winner) {

            $buffy .= '<li>';
            $buffy .= $winner['name'];
            $buffy .= array_key_exists('role', $winner) ? ' (' . $winner['role'] . ')' : '';
            $buffy .= '</li>';
        }

        $buffy .= '</ul>';

        $buffy .= '[/vc_column_text]';

        return $buffy;
    }

	/**
	 * Render a board of directors election.
	 *
	 * @param array $election
	 * @return string
	 */
	static function render_bod_election(array $election): string {

        $date = $election['date'];
        $vacant_seats = $election['vacant_seats'];
        $nomination_form = $election['nomination_form'];
        $nomination_period_start = $election['nomination_period_start'];
        $nomination_period_end = $election['nomination_period_end'];
        $statements_published = $election['statements_published'];
        $voting_period_start = $election['voting_period_start'];
        $voting_period_end = $election['voting_period_end'];
        $results_announced_date = $election['results_announced_date'];

        $buffy = '';

        $buffy .= '[vc_column_text]';

        $buffy .= sprintf(self::announce_heading, $date);
        $buffy .= sprintf(self::announce_bod, (string) $vacant_seats);

        $buffy .= self::nomination_heading;
        $buffy .= sprintf(self::nomination_bod(), wp_get_attachment_url($nomination_form), $date);

        $buffy .= self::voting_heading;
        $buffy .= sprintf(self::voting_bod, $voting_period_start, $voting_period_end);

        $buffy .= self::timeline_heading;
        $buffy .= sprintf(self::timeline_bod, $nomination_period_start, $nomination_period_end, $statements_published, $voting_period_start, $voting_period_end, $results_announced_date);

        $buffy .= '[/vc_column_text]';

        return $buffy;
    }

	/**
	 * Render an editorial board election.
	 *
	 * @param array $election
	 * @return string
	 */
	static function render_eb_election(array $election): string {

        $date = $election['date'];
        $nomination_form = $election['nomination_form'];
        $nomination_period_start = $election['nomination_period_start'];
        $nomination_period_end = $election['nomination_period_end'];
        $candidates_forum_date = $election['candidates_forum_date'];
        $voting_day = $election['voting_day'];
        $results_announced_date = $election['results_announced_date'];

        $buffy = '';

        $buffy .= '[vc_column_text]';

        $buffy .= sprintf(self::announce_heading, $date);
        $buffy .= sprintf(self::announce_eb, $candidates_forum_date);

        $buffy .= self::nomination_heading;
        $buffy .= sprintf(self::nomination_eb(), wp_get_attachment_url($nomination_form), $date);

        $buffy .= self::voting_heading;
        $buffy .= sprintf(self::voting_eb(), $voting_day);

        $buffy .= self::timeline_heading;
        $buffy .= sprintf(self::timeline_eb, $nomination_period_start, $nomination_period_end, $candidates_forum_date, $voting_day, $results_announced_date);

        $buffy .= '[/vc_column_text]';

        return $buffy;
    }

	/**
	 * Render an election column.
	 *
	 * @param string $type
	 * @param array $elections
	 * @return string
	 */
	static function render_election_column(string $type,
			array $elections): string {

        $buffy = '';

        $buffy .= ($type == 'Board of Directors') ? self::top_bod : self::top_eb;

		if (!empty($elections)) {
			$buffy .= '[vc_tabs]';

	        // Ensure that a non-finished election is first
			$current = null;
			foreach($elections as $key => $election) {
				if (!$election['finished']) {
					$current = $election;
					unset($elections[$key]);
					break;
				}
			}

            // A tab for the current one if it exists

            $buffy .= '[vc_tab title="Current / Most Recent" tab_id="' .
                      str_replace(' ', '_', $type) . '_cmr"]';

            $most_recent = is_null($current) ? $elections[0] : $current;

            // First election is a finished one
            if ($most_recent['finished']) {
                $buffy .= self::render_finished_election($most_recent, true);

            // First election is a current one
            } else {

                if ($type == 'Board of Directors') {
                    $buffy .= self::render_bod_election($most_recent);
                } else {
                    $buffy .= self::render_eb_election($most_recent);
                }
            }

            $buffy .= '[/vc_tab]';

            // If we didn't have a current one, or if we did and there are more
            if (is_null($current) || !empty($elections)) {

            	// If we didn't have a current election, we already used index 0
            	$remaining = array_slice($elections, (int) is_null($current));

                $buffy .= '[vc_tab title="Archived" tab_id="' .
                          str_replace(' ', '_', $type) . '_arc"]';

                foreach ($remaining as $election) {
                    $buffy .= self::render_finished_election($election, false);
                }

                $buffy .= '[/vc_tab]';
            }

            $buffy .= '[/vc_tabs]';
        }


        return $buffy;
    }


	/*
	 * =========================================================================
	 * Renderer
	 * =========================================================================
	 */

	/**
	 * Return the entire rendered elections page.
	 *
	 * @param array $args
	 * @return string
	 */
	static function content(array $args=[]): string {

        $bod_elections = self::gather_bod_elections();
        $eb_elections = self::gather_eb_elections();

        $buffy = '';

        $buffy .= self::page_start();

        $buffy .= '[vc_row]';

        $buffy .= '[vc_column width="1/2"]';
        $buffy .= self::render_election_column('Board of Directors', $bod_elections);
        $buffy .= '[/vc_column]';

        $buffy .= '[vc_column width="1/2"]';
        $buffy .= self::render_election_column('Editorial Board Elections', $eb_elections);
        $buffy .= '[/vc_column]';

        $buffy .= '[/vc_row]';

        return $buffy;
    }
}
