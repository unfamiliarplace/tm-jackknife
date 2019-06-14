<?php

/**
 * Rencers the Board of Directors page.
 */
final class MJKBODRenderer extends JKNRenderer {

	/*
	 * =========================================================================
	 * Messages
	 * =========================================================================
	 */

    // Start of the whole page
    const page_start = '<p>This is a listing of various official documents,' .
                       ' primarily for the use of <em>The Medium</em>\'s' .
                       ' board of directors, but open to all UTM students to' .
                       ' read.</p><hr />';
    const none_text = '[vc_column_text]<em>None on file.</em>[/vc_column_text]';

    // Constitution heading
    const const_heading = '<h3>Constitution & By-laws</h3>';
    const const_intro = '<em>The Medium</em> is incorporated as the non-profit' .
                        ' corporation Medium II Publications in Ontario.';

    // Audits heading
    const audits_heading = '<hr /><h3>Audited financial statements</h3>';
    const audits_intro = 'The audit for a given academic year is completed' .
                         ' during the following academic year.';

    // BOD meetings heading
    const bod_meetings_heading = '<hr /><h3>Board of Directors meetings</h3>';
    const bod_meetings_intro = 'The board of directors meets several times a' .
                               ' year to discuss company issues. Motions made' .
                               ' and passed at a board meeting have some' .
                               ' binding force. Note that minutes are' .
                               ' unofficial until approved at a subsequent' .
                               ' meeting.';

    // AGMs heading
    const agms_heading = '<hr /><h3>Annual General Meetings</h3>';
    const agms_intro = 'The annual general meeting is open to all members.' .
                       ' General questions and questions about governance are' .
                       ' addressed, and the audited financial statements are' .
                       ' presented. Motions passed at an AGM have' .
                       ' wide-ranging force.';

    // SGMs heading
    const sgms_heading = '<hr /><h3>Special General Meetings</h3>';
    const sgms_intro = 'A special general meeting is held whenever a large' .
                       ' number of members call for one. It has essentially' .
                       ' the same powers as an annual general meeting.';

    // Elections heading
    const elections_heading = '<hr /><h3>Elections to the board of' .
                              ' directors</h3>';
	const elections_intro = 'Visit the <a title="Elections"' .
	                        ' href="%s/elections">Elections</a> page for' .
	                        ' information about any current, upcoming, or' .
	                        ' past elections.';

	// Elections masthead
    const elections_masthead = '<br>Visit the <a title="Masthead"' .
                               ' href="/masthead">Masthead</a> page to see' .
                               ' the roster of current directors.';


	/*
	 * =========================================================================
	 * Gathering
	 * =========================================================================
	 */

	/**
	 * @return string The file ID for the constitution.
	 */
	static function get_constitution(): string {
		return MJKBod_ACF::get(MJKBod_ACF::constitution);
    }

	/**
	 * Return an array of audited financial statement files.
	 *
	 * ['statement' => file_id, 'year_end' => datestring]
	 *
	 * @return array
	 */
	static function get_audits(): array {

        $audits = [];

        if (MJKBod_ACF::have_rows(MJKBod_ACF::audits)) {
	        while (MJKBod_ACF::have_rows(MJKBod_ACF::audits)) {
                the_row();

                $statement = MJKBod_ACF::sub(MJKBod_ACF::audit);
                $year_end = MJKBod_ACF::sub(MJKBod_ACF::audit_ye);

                $audits[] = ['statement' => $statement, 'year_end' => $year_end];
            }
        }

        // Sort by date -- newest first
        usort($audits, function(array $a, array $b): int {
        	$pri_a = JKNTime::dt($a['year_end']);
	        $pri_b = JKNTime::dt($b['year_end']);
	        return $pri_b <=> $pri_a;
        });

        return $audits;
    }

	/**
	 * Return the meetings of a given type.
	 *
	 * ['meeting_date' => datestring, 'agenda' => file_id,
	 *  'minutes' => file_id, 'quorum' => bool]
	 *
	 * @param string $meeting_type An ACF field name.
	 * @return array
	 */
	static function get_meetings(string $meeting_type): array {
        $meetings = [];

		if (MJKBod_ACF::have_rows(constant("MJKBod_ACF::{$meeting_type}s"))) {
			while (MJKBod_ACF::have_rows(constant("MJKBod_ACF::{$meeting_type}s"))) {

                the_row();

                $date = MJKBod_ACF::sub(constant("MJKBod_ACF::{$meeting_type}_date"));
                $agenda = MJKBod_ACF::sub(constant("MJKBod_ACF::{$meeting_type}_agenda"));
                $minutes = MJKBod_ACF::sub(constant("MJKBod_ACF::{$meeting_type}_minutes"));
                $quorum = MJKBod_ACF::sub(constant("MJKBod_ACF::{$meeting_type}_quorum"));

                $meetings[] = [
                	'date' => $date,
	                'agenda' => $agenda,
	                'minutes' => $minutes,
	                'quorum' => $quorum
                ];
            }
        }

		// Sort by date -- newest first
		usort($meetings, function(array $a, array $b): int {
			$pri_a = JKNTime::dt($a['date']);
			$pri_b = JKNTime::dt($b['date']);
			return $pri_b <=> $pri_a;
		});

        return $meetings;
    }


	/*
	 * =========================================================================
	 * Rendering
	 * =========================================================================
	 */

	/**
	 * Return the formatted constitution.
	 *
	 * @param string $constitution A file ID.
	 * @return string
	 */
	static function render_constitution(string $constitution): string {

        $html = '';
        $html .= '[vc_column_text]';

        $html .= '<strong>File:</strong> <a target="_blank" href="' .
                 MJKCommonTools::cdn_url(wp_get_attachment_url($constitution)) .
                 '" title="Constitution & By-laws" target="_blank">' .
                 'Medium II Publications Constitution & By-laws</a>';

        $html .= '[/vc_column_text]';
        return $html;
    }

	/**
	 * Return a formatted audit.
	 *
	 * @param array $audit An array with the keys 'statement' and 'year_end'.
	 * @return string
	 */
	static function render_audit(array $audit): string {

        $statement = $audit['statement'];
        $year_end = $audit['year_end'];

        $html = '';

        $html .= '<a target="_blank" href="' .
                 MJKCommonTools::cdn_url(wp_get_attachment_url($statement)) .
                 '" title="Audited financial statements, year end ' .
                 $year_end .
                 '"target="_blank">Audited financial statements, year end ' .
                 $year_end .
                 '</a>';

        return $html;
    }

	/**
	 * Return a formatted list of audits.
	 *
	 * @param array $audits
	 * @return string
	 */
	static function render_audits(array $audits): string {

        $html = '';

        $html .= '[vc_column_text]<ul class="mgt_audits">';

        foreach ($audits as $audit) {
            $html .= '<li>';
            $html .= self::render_audit($audit);
            $html .= '</li>';
        }

        $html .= '</ul>[/vc_column_text]';
        return $html;
    }

	/**
	 * Render a meeting.
	 *
	 * @param array $meeting
	 * @return string
	 */
	static function render_meeting(array $meeting): string {

        $date = $meeting['date'];
        $agenda = $meeting['agenda'];
        $minutes = $meeting['minutes'];
        $quorum = $meeting['quorum'];

        $html = '';

        // Meeting date

        $html .= '<strong>' . $date . '</strong>';

        // Agenda

        $html .= '<br />';
        $html .= 'Agenda: ';
        if (!empty($agenda)) {
            $html .= '<a target="_blank" href="' .
                     MJKCommonTools::cdn_url(wp_get_attachment_url($agenda)) .
                     '" title="Agenda" target="_blank">Click to view PDF</a>';
        } else {
            $html .= 'No file added yet.';
        }

        // Minutes

        $html .= '<br />';
        $html .= 'Minutes: ';
        if (!empty($minutes)) {
            $html .= '<a target="_blank" href="' .
                     MJKCommonTools::cdn_url(wp_get_attachment_url($minutes)) .
                     '" title="Minutes">Click to view PDF</a>';
        } else {
            $html .= 'No file added yet.';
        }

        // Quorum
        // Only output if it's the next day
        if (time() >= strtotime($date . ' + 1 day')) {

            $html .= '<br />';

            if ($quorum) {
                $html .= '<em>Quorum was reached. This meeting was official.</em>';
            } else {
                $html .= '<em>Quorum was not reached. This meeting was unofficial.</em>';
            }
        }

        return $html;
    }

	/**
	 * Render a list of meetings.
	 *
	 * @param array $meetings
	 * @return string
	 */
	static function render_meetings(array $meetings): string {

        $html = '';

        $html .= '[vc_column_text]<ul class="mgt_meetings">';

        foreach ($meetings as $meeting) {
            $html .= '<li>';
            $html .= self::render_meeting($meeting);
            $html .= '</li>';
        }

        $html .= '</ul>[/vc_column_text]';
        return $html;
    }

	/**
	 * Return the formatted CSS.
	 *
	 * @return string
	 */
	static function format_style(): string {
        return JKNCSS::tag('
            .mgt_meetings li {
              list-style: none;
              margin-bottom: 15px;
            }

            .mgt_audits li {
                    list-style: none;
            }

            ul.mgt_meetings, ul.mgt_audits {
                margin: 0.75em 0;
                padding: 0 1em;
                list-style: none;
            }

            .mgt_meetings li:before, .mgt_audits li:before { 
                content: "";
                border-color: transparent #111;
                border-style: solid;
                border-width: 0.35em 0 0.35em 0.45em;
                display: block;
                height: 0;
                width: 0;
                left: -1em;
                top: 1.1em;
                position: relative;
            }

            .mgt_bod_page .wpb_text_column {
              margin-bottom: 0;
            }
        ');
    }


	/*
	 * =========================================================================
	 * Renderer
	 * =========================================================================
	 */

	/**
	 * Return the formatted Board of Directors page.
	 *
	 * @param array $args
	 * @return string
	 */
	static function content(array $args=[]): string {

        $constitution = self::get_constitution();
        $audits = self::get_audits();
        $bod_meetings = self::get_meetings('meeting');
        $agms = self::get_meetings('agm');
        $sgms = self::get_meetings('sgm');

        $html = '';
        $html .= self::format_style();
        $html .= '[vc_row el_class="mgt_bod_page"][vc_column]';

        $html .= self::page_start;

        $html .= self::const_heading;
        $html .= self::const_intro;
        $html .= self::render_constitution($constitution);

        $html .= self::audits_heading;
        $html .= self::audits_intro;
        $html .= (!empty($audits)) ? self::render_audits($audits) : self::none_text;

        $html .= self::bod_meetings_heading;
        $html .= self::bod_meetings_intro;
        $html .= (!empty($bod_meetings)) ? self::render_meetings($bod_meetings) : self::none_text;

        $html .= self::agms_heading;
        $html .= self::agms_intro;
        $html .= (!empty($agms)) ? self::render_meetings($agms) : self::none_text;

        $html .= self::sgms_heading;
        $html .= self::sgms_intro;
        $html .= (!empty($sgms)) ? self::render_meetings($sgms) : self::none_text;

        $html .= self::elections_heading;
        $html .= sprintf(self::elections_intro, get_site_url());
        $html .= self::elections_masthead;

        $html .= '[/vc_column][/vc_row]';

        return $html;
    }
}
