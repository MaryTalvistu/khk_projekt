<?php
/*
Plugin Name: My First plugin
Plugin URI: 
Description: This is my first WordPress Plugin
Author: Mary-Ann Talvistu
Author URI: http://pippinsplugins.com
Version: 1.0



/* global variables */



$mfwp_options = get_option('mfwp_settings');


/* includes*/

include('includes/scripts.php'); // this controls all JS / CSS
include('includes/data-processing.php'); // this controls all saving of data
include('includes/display-functions.php'); // display content functions
include('includes/admin-page.php'); // the plugin options page HTML and save functions