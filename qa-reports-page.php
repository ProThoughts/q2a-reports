<?php

class qa_reports_page {

	function match_request($request)
	{
		$parts=explode('/', $request);

		return $parts[0]=='reports' && (qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN);
	}

	//Displays reports in Admin section
	public function suggest_requests()
	{
		return array(
			array(
				'title' => 'Reports',
				'request' => 'reports',
				'nav' => null, // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
			),
		);
	}

	function process_request($request)
	{
		//Getting the dates
		$startDate = qa_post_text('startDate');
		$endDate = qa_post_text('endDate');

		if (!$startDate && !$endDate) {
			$endDate = date('Y-m-d');
			$startDate = date("Y-m-d", strtotime("-7 days"));
		}

		//Adding Q2A header and elements, removing sidebar
		$qa_content = qa_content_prepare();
		unset($qa_content['sidebar']); 
		unset($qa_content['sidepanel']);

		//Generating Subnavigation
        $qa_content['custom-subnav'] = '<div class="hidden-xs subnav-row clearfix">
            <div class="qa-nav-sub">
                <ul class="qa-nav-sub-list">
                    <li class="qa-nav-sub-item">
                        <a href="./?qa=recent-activity" class="qa-nav-sub-link">Recent Activity</a>
                    </li>
                    <li class="qa-nav-sub-item">
                        <a href="./?qa=reports-hourly" class="qa-nav-sub-link">Hourly Stats</a>
                    </li>
                    <li class="qa-nav-sub-item active">
                        <a href="./?qa=reports" class="qa-nav-sub-link">Daily Stats</a>
                    </li>
                    <li class="qa-nav-sub-item">
                        <a href="./?qa=reports-monthly" class="qa-nav-sub-link">Monthly Stats</a>
                    </li>
                </ul>
                <div class="qa-nav-sub-clear clearfix">
                </div>
            </div>
        </div>';

		//Generating form
		$qa_content['title'] = 'Reports';
		$qa_content['custom_report_form'] = '<form method="POST" autocomplete="off" action="'.qa_self_html().'">
			<label>Start Date: <input type="date" name="startDate" id="startDate" value="'.$startDate.'" max="'.date('Y-m-d').'"></input></label>
			<label>End Date: <input type="date" name="endDate" id="endDate" value="'.$endDate.'" max="'.date('Y-m-d').'"></input></label>
			<button type="submit" id="submit">Submit</button>
			<button class="lastxdays" days="30" type="button">Last 30 Days</button>
			<button class="lastxdays" days="60" type="button">Last 60 Days</button>
			<button class="lastxdays" days="90" type="button">Last 90 Days</button>
			<button class="lastxdays" days="120" type="button">Last 120 Days</button>
			</form>';

		
		if (($startDate && !$endDate) || (!$startDate && $endDate)){
			$qa_content['custom_error'] = '<div class="alert alert-danger" id="parameter-error">Missing a date. Please check your report options and try again.</div>';
			return $qa_content;
		}
		else{
			$stats = $this->get_daily_stats($startDate, $endDate);
			if (sizeof($stats) >= 2000){
				$qa_content['custom_data_limit_error'] = '
					<div class="alert alert-warning" id="limit-error">Upper limit of 2000 non-zero data records reached. Some data has been cut off.</div>';
			}
    		$qa_content['custom_data_retrieval'] = '
			<div class="alert alert-danger" id="no-data-error">No data found. Please check your report options and try again.</div>
			<div id="chart"></div>
			<script>
			var json = '.json_encode($stats).';
			$(document).ready(function(){
				runReport(json);
			});
				</script>
			<button type="button" id="hideData">Hide All</button>
			<button type="button" id="showData">Show All</button>';
			return $qa_content;
		}
	}
	function get_daily_stats($startDate, $endDate){
		$query = "
			SELECT 	
				DATE(datetime) as Day,
				SUM(event='a_post') as 'Answers Posted',
				SUM(event='a_select') as 'Answers Selected as Best Answer',
				SUM(event='badge_awarded') as 'Badges Awarded',
				SUM(event='feedback') as 'Feedback Submitted',
				SUM(event='q_post') as 'Questions Posted',
				SUM(event='search') as 'Searches',
				SUM(event='u_login') as 'User Logins',
				SUM(event='u_logout') as 'User Logouts',
				SUM(event='u_register') as 'User Registrations',
				SUM(event='u_save') as 'User Profile Edits',
				SUM(event='u_wall_post') as 'User Profile Wallposts',
				SUM(event='c_post') as 'Comment Posted',
				SUM(event='q_vote_down' or event='q_vote_up') as 'Votes',
				COUNT(*) as 'Total Interactions'
			FROM ^eventlog
			WHERE 
				DATE(datetime) >= $
				AND DATE(datetime) <= $
			GROUP BY Day
			LIMIT 2000
		";
		$result = qa_db_query_sub($query, $startDate, $endDate);
		$stats = qa_db_read_all_assoc($result);
		return $stats;
	}
}