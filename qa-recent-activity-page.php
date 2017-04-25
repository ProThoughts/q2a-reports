<?php

class qa_recent_activity_page {

	function match_request($request)
	{
		$parts=explode('/', $request);

		return $parts[0]=='recent-activity' && (qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN);
	}

	function process_request($request)
	{
		//Getting the dates
		$startDate = qa_post_text('startDate');
		$endDate = qa_post_text('endDate');

		//Adding Q2A header and elements, removing sidebar
		$qa_content = qa_content_prepare();
		unset($qa_content['sidebar']); 
		unset($qa_content['sidepanel']);

        //Generating Subnavigation
        $qa_content['custom-subnav'] = '<div class="hidden-xs subnav-row clearfix">
            <div class="qa-nav-sub">
                <ul class="qa-nav-sub-list">
                    <li class="qa-nav-sub-item active">
                        <a href="./?qa=recent-activity" class="qa-nav-sub-link">Recent Activity</a>
                    </li>
                    <li class="qa-nav-sub-item">
                        <a href="./?qa=reports-hourly" class="qa-nav-sub-link">Hourly Stats</a>
                    </li>
                    <li class="qa-nav-sub-item">
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
            <button id="mostRecent" type="button">Show 2000 Most Recent Events</button>
			</form>';

        $stats = $this->get_recent_stats($startDate, $endDate);
        if (sizeof($stats) >= 2000){
            $qa_content['custom_data_limit_error'] = '
                <div class="alert alert-warning" id="limit-error">Upper limit of 2000 non-zero data records reached. Some data has been cut off.</div>';
        }
        $qa_content['custom_data_retrieval'] = '
        <div class="alert alert-danger" id="no-data-error">No data found. Please check your report options and try again.</div>
        <table id="recent-activity"></table>
        <script>
        var json = '.json_encode($stats).';
        $(document).ready(function(){
            runRecentActivityReport(json);
        });
            </script>';
        return $qa_content;
	}
	function get_recent_stats($startDate, $endDate){
		if ($startDate && $endDate){
            $query = "
                SELECT datetime, ipaddress, handle, event, params
                FROM ^eventlog
                WHERE DATE(datetime) >= $
                        AND DATE(datetime) <= $
                LIMIT 2000
            ";
            $result = qa_db_query_sub($query, $startDate, $endDate);
        }
        else if ($startDate){
            $query = "
                SELECT datetime, ipaddress, handle, event, params
                FROM ^eventlog
                WHERE DATE(datetime) >= $
                LIMIT 2000
            ";
            $result = qa_db_query_sub($query, $startDate);
        }
        else if ($endDate){
            $query = "
                SELECT datetime, ipaddress, handle, event, params
                FROM ^eventlog
                WHERE DATE(datetime) <= $
                LIMIT 2000
            ";
            $result = qa_db_query_sub($query, $endDate);
        }
        else{
            $query = "
                SELECT datetime, ipaddress, handle, event, params
                FROM ^eventlog
                ORDER BY datetime DESC
                LIMIT 2000
            ";
            $result = qa_db_query_sub($query);
        }
		$stats = qa_db_read_all_assoc($result);
		return $stats;
	}
}