<?php
/*
  Plugin Name: Reports
  Plugin URI: https://github.com/stanhuan/q2a-reports
  Plugin Description: Reporting interface for Question2Answer
  Plugin Version: 1.0
  Plugin Date: 2017-04-25
  Plugin Author: Stanley Huang
  Plugin Author URI: http://stanhuan.com
  Plugin License: GPLv2
  Plugin Minimum Question2Answer Version: 1.5
  Plugin Update Check URI: https://raw.githubusercontent.com/stanhuan/q2a-reports/master/qa-plugin.php
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
  header('Location: ../../');
  exit;
}

qa_register_plugin_module(
  'page',
  'qa-reports-page.php',
  'qa_reports_page',
  'Reports'
);
qa_register_plugin_module(
  'page',
  'qa-recent-activity-page.php',
  'qa_recent_activity_page',
  'Recent Activity'
);
qa_register_plugin_module(
  'page',
  'qa-reports-monthly-page.php',
  'qa_reports_monthly_page',
  'Monthly Report'
);
qa_register_plugin_module(
  'page',
  'qa-reports-hourly-page.php',
  'qa_reports_hourly_page',
  'Hourly Report'
);
qa_register_plugin_layer(
  'qa-reports-layer.php', 
  'Reports Layer'
);