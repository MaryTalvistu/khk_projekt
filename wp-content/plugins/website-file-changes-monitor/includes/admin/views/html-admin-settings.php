<?php
/**
 * Settings View.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Scan Frequencies.
 */
$frequency_options = apply_filters(
	'wfcm_file_changes_scan_frequency',
	array(
		'hourly'  => __( 'Hourly', 'website-file-changes-monitor' ),
		'daily'   => __( 'Daily', 'website-file-changes-monitor' ),
		'weekly'  => __( 'Weekly', 'website-file-changes-monitor' ),
		'monthly' => __( 'Monthly', 'website-file-changes-monitor' ),
	)
);

// Scan hours option.
$scan_hours = array(
	'00' => _x( '00:00', 'a time string representing midnight', 'website-file-changes-monitor' ),
	'01' => _x( '01:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'02' => _x( '02:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'03' => _x( '03:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'04' => _x( '04:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'05' => _x( '05:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'06' => _x( '06:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'07' => _x( '07:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'08' => _x( '08:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'09' => _x( '09:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'10' => _x( '10:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'11' => _x( '11:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'12' => _x( '12:00', 'a time string representing midday', 'website-file-changes-monitor' ),
	'13' => _x( '13:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'14' => _x( '14:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'15' => _x( '15:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'16' => _x( '16:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'17' => _x( '17:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'18' => _x( '18:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'19' => _x( '19:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'20' => _x( '20:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'21' => _x( '21:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'22' => _x( '22:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
	'23' => _x( '23:00', 'a time string of hour followed by minutes', 'website-file-changes-monitor' ),
);

// Scan days option.
$scan_days = array(
	'1' => _x( 'Monday', 'the first day of the week and first day of the work week', 'website-file-changes-monitor' ),
	'2' => _x( 'Tuesday', 'the second day of the week', 'website-file-changes-monitor' ),
	'3' => _x( 'Wednesday', 'the third day of the week', 'website-file-changes-monitor' ),
	'4' => _x( 'Thursday', 'the fourth day of the week', 'website-file-changes-monitor' ),
	'5' => _x( 'Friday', 'the fith day of the week, last day of the work week', 'website-file-changes-monitor' ),
	'6' => _x( 'Saturday', 'the first day of the weekend', 'website-file-changes-monitor' ),
	'7' => _x( 'Sunday', 'the last day of the week and last day of the weekend', 'website-file-changes-monitor' ),
);

// Scan date option.
$scan_date = array(
	'01' => _x( '01', 'a day number in a given month', 'website-file-changes-monitor' ),
	'02' => _x( '02', 'a day number in a given month', 'website-file-changes-monitor' ),
	'03' => _x( '03', 'a day number in a given month', 'website-file-changes-monitor' ),
	'04' => _x( '04', 'a day number in a given month', 'website-file-changes-monitor' ),
	'05' => _x( '05', 'a day number in a given month', 'website-file-changes-monitor' ),
	'06' => _x( '06', 'a day number in a given month', 'website-file-changes-monitor' ),
	'07' => _x( '07', 'a day number in a given month', 'website-file-changes-monitor' ),
	'08' => _x( '08', 'a day number in a given month', 'website-file-changes-monitor' ),
	'09' => _x( '09', 'a day number in a given month', 'website-file-changes-monitor' ),
	'10' => _x( '10', 'a day number in a given month', 'website-file-changes-monitor' ),
	'11' => _x( '11', 'a day number in a given month', 'website-file-changes-monitor' ),
	'12' => _x( '12', 'a day number in a given month', 'website-file-changes-monitor' ),
	'13' => _x( '13', 'a day number in a given month', 'website-file-changes-monitor' ),
	'14' => _x( '14', 'a day number in a given month', 'website-file-changes-monitor' ),
	'15' => _x( '15', 'a day number in a given month', 'website-file-changes-monitor' ),
	'16' => _x( '16', 'a day number in a given month', 'website-file-changes-monitor' ),
	'17' => _x( '17', 'a day number in a given month', 'website-file-changes-monitor' ),
	'18' => _x( '18', 'a day number in a given month', 'website-file-changes-monitor' ),
	'19' => _x( '19', 'a day number in a given month', 'website-file-changes-monitor' ),
	'20' => _x( '20', 'a day number in a given month', 'website-file-changes-monitor' ),
	'21' => _x( '21', 'a day number in a given month', 'website-file-changes-monitor' ),
	'22' => _x( '22', 'a day number in a given month', 'website-file-changes-monitor' ),
	'23' => _x( '23', 'a day number in a given month', 'website-file-changes-monitor' ),
	'24' => _x( '24', 'a day number in a given month', 'website-file-changes-monitor' ),
	'25' => _x( '25', 'a day number in a given month', 'website-file-changes-monitor' ),
	'26' => _x( '26', 'a day number in a given month', 'website-file-changes-monitor' ),
	'27' => _x( '27', 'a day number in a given month', 'website-file-changes-monitor' ),
	'28' => _x( '28', 'a day number in a given month', 'website-file-changes-monitor' ),
	'29' => _x( '29', 'a day number in a given month', 'website-file-changes-monitor' ),
	'30' => _x( '30', 'a day number in a given month', 'website-file-changes-monitor' ),
);

// WP Directories.
$wp_directories = wfcm_get_server_directories( 'display' );

$wp_directories = apply_filters( 'wfcm_file_changes_scan_directories', $wp_directories );

$disabled = ! $settings['enabled'] ? 'disabled' : false;

// get email notice type and convert emails array to string seporated by commas.
$email_notice_type = ( isset( $settings[ WFCM_Settings::NOTIFY_TYPE ] ) && 'custom' === $settings[ WFCM_Settings::NOTIFY_TYPE ] ) ? 'custom' : 'admin';
$email_custom_list = ( isset( $settings[ WFCM_Settings::NOTIFY_ADDRESSES ] ) ) ? $settings[ WFCM_Settings::NOTIFY_ADDRESSES ] : '';
// convert to string FROM an array.
$email_custom_list = ( is_array( $email_custom_list ) ) ? implode( ',', $email_custom_list ) : $email_custom_list;
?>

<div class="wrap wfcm-settings">
	<h1><?php esc_html_e( 'Website File Changes Settings', 'website-file-changes-monitor' ); ?></h1>
	<?php self::show_messages(); ?>
	<form method="post" action="" enctype="multipart/form-data">
		<h3><?php esc_html_e( 'Which file changes do you want to be notified of?', 'website-file-changes-monitor' ); ?></h3>
		<!-- Type of Changes -->
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-file-changes-type"><?php esc_html_e( 'Notify me when files are', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<label for="added">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="added" <?php echo in_array( 'added', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'Added', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br>
						<label for="deleted">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="deleted" <?php echo in_array( 'deleted', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'Deleted', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br>
						<label for="modified">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="modified" <?php echo in_array( 'modified', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'Modified', 'website-file-changes-monitor' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Email to send changes notices to -->
		<h3><?php esc_html_e( 'Where should we send the file changes notification?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'By default the plugin sends the email notifications to the administrator email address configured in the WordPress settings. Use the below setting to send the email notification to a different email address. You can specify multiple email addresses by separating them with a comma.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-notification-email-address"><?php esc_html_e( 'Notify this address', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<label for="email-notice-admin">
							<input type="radio" id="email-notice-admin" name="wfcm-settings[<?php echo esc_attr( WFCM_Settings::NOTIFY_TYPE ); ?>]" value="admin"<?php echo ( 'custom' !== $email_notice_type ) ? ' checked' : ''; ?>>
							<span><?php esc_html_e( 'Use admin email address in website settings.', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br>
						<label for="email-notice-custom">
							<input type="radio" id="email-notice-custom" name="wfcm-settings[<?php echo esc_attr( WFCM_Settings::NOTIFY_TYPE ); ?>]" value="custom"<?php echo ( 'custom' === $email_notice_type ) ? ' checked' : ''; ?>>
							<input type="email" id="notice-email-address" name="wfcm-settings[<?php echo esc_attr( WFCM_Settings::NOTIFY_ADDRESSES ); ?>]" multiple pattern="^([\w+-.%]+@[\w-.]+\.[A-Za-z]{2,4},*[\W]*)+$" value="<?php echo esc_attr( $email_custom_list ); ?>">
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Scan times -->
		<h3><?php esc_html_e( 'When should the plugin scan your website for file changes?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'By default the plugin will run file changes scans once a week. If you can, ideally you should run file changes scans on a daily basis. The file changes scanner is very efficient and requires very little resources. Though if you have a fairly large website we recommend you to scan it when it is the least busy. The scan process should only take a few seconds to complete.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-frequency"><?php esc_html_e( 'Scan frequency', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<select name="wfcm-settings[scan-frequency]">
							<?php foreach ( $frequency_options as $value => $html ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['frequency'] ); ?>><?php echo esc_html( $html ); ?></option>
							<?php endforeach; ?>
						</select>
					</fieldset>
				</td>
			</tr>
			<tr id="scan-time-row">
				<th><label for="wfcm-settings-scan-hour"><?php esc_html_e( 'Scan time', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<label>
							<select name="wfcm-settings[scan-hour]">
								<?php foreach ( $scan_hours as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['hour'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Hour', 'website-file-changes-monitor' ); ?></span>
						</label>

						<label>
							<select name="wfcm-settings[scan-day]">
								<?php foreach ( $scan_days as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['day'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Day', 'website-file-changes-monitor' ); ?></span>
						</label>

						<label>
							<select name="wfcm-settings[scan-date]">
								<?php foreach ( $scan_date as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['date'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Day', 'website-file-changes-monitor' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Scan frequency -->

		<h3><?php esc_html_e( 'Which directories should be scanned for file changes?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'The plugin will scan all the directories in your WordPress website by default because that is the most secure option. Though if for some reason you do not want the plugin to scan any of these directories you can uncheck them from the below list.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tbody>
				<tr>
					<th><label for="wfcm-settings-directories"><?php esc_html_e( 'Directories to scan', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset <?php echo esc_attr( $disabled ); ?>>
							<?php foreach ( $wp_directories as $value => $html ) : ?>
								<label>
									<input name="wfcm-settings[scan-directories][]" type="checkbox" value="<?php echo esc_attr( $value ); ?>" <?php echo in_array( $value, $settings['directories'], true ) ? 'checked' : false; ?> />
									<?php echo esc_html( $html ); ?>
								</label>
								<br />
							<?php endforeach; ?>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- Scan directories -->

		<h3><?php esc_html_e( 'What is the biggest file size the plugin should scan?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'By default the plugin does not scan files that are bigger than 5MB. Such files are not common, hence typically not a target.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-file-size"><?php esc_html_e( 'File size limit', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="number" name="wfcm-settings[scan-file-size]" min="1" max="100" value="<?php echo esc_attr( $settings['file-size'] ); ?>" /> <?php esc_html_e( 'MB', 'website-file-changes-monitor' ); ?>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Maximum File Size -->

		<h3><?php esc_html_e( 'Scan common development folders?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Some sites may contain special development folders like ".git", ".github", ".svg" and "node_modules". We do not scan these folders by default however if you would like to scan them check this box. Scanning these directories could take a long time if there are lots of files in these directories additionally these files can change frequently - it may result in many added/modified files notifications.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="wfcm-debug-logging"><?php esc_html_e( 'Scan common development directories', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<input id="wfcm-debug-logging" type="checkbox" name="wfcm-settings[scan-dev-folders]" value="1" <?php checked( $settings['scan-dev-folders'] ); ?>>
					</fieldset>
				</td>
			</tr>
		</table>


		<h3><?php esc_html_e( 'Do you want to exclude specific files or files with a particular extension from the scan?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'The plugin will scan everything that is in the WordPress root directory or below, even if the files and directories are not part of WordPress. It is recommended to scan all source code files and only exclude files that cannot be tampered, such as text files, media files etc, most of which are already excluded by default.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-exclude-dirs"><?php esc_html_e( 'Exclude all files in these directories', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="exclude-list" id="wfcm-exclude-dirs-list">
								<?php foreach ( $settings['exclude-dirs'] as $dir ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-dirs][]" id="<?php echo esc_attr( $dir ); ?>" value="<?php echo esc_attr( $dir ); ?>" checked />
										<label for="<?php echo esc_attr( $dir ); ?>"><?php echo esc_html( $dir ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-exclude-type="dirs" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-exclude-type="dirs" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description">
							<?php esc_html_e( 'Specify the name of the directory and the path to it in relation to the website\'s root. For example, if you want to want to exclude all files in the sub directory dir1/dir2 specify the following:', 'website-file-changes-monitor' ); ?>
							<br>
							<?php echo esc_html( trailingslashit( ABSPATH ) ) . 'dir1/dir2/'; ?>
						</p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude directories -->
			<tr>
				<th><label for="wfcm-settings-exclude-filenames"><?php esc_html_e( 'Exclude these files', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="exclude-list" id="wfcm-exclude-files-list">
								<?php foreach ( $settings['exclude-files'] as $file ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-files][]" id="<?php echo esc_attr( $file ); ?>" value="<?php echo esc_attr( $file ); ?>" checked />
										<label for="<?php echo esc_attr( $file ); ?>"><?php echo esc_html( $file ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-exclude-type="files" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-exclude-type="files" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description"><?php esc_html_e( 'Specify the name and extension of the file(s) you want to exclude. Wildcard not supported. There is no need to specify the path of the file.', 'website-file-changes-monitor' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude filenames -->
			<tr>
				<th><label for="wfcm-settings-exclude-extensions"><?php esc_html_e( 'Exclude these file types', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="exclude-list" id="wfcm-exclude-exts-list">
								<?php foreach ( $settings['exclude-exts'] as $file_type ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-exts][]" id="<?php echo esc_attr( $file_type ); ?>" value="<?php echo esc_attr( $file_type ); ?>" checked />
										<label for="<?php echo esc_attr( $file_type ); ?>"><?php echo esc_html( $file_type ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-exclude-type="exts" type="button" value="<?php esc_html_e( 'Remove', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-exclude-type="exts" type="button" value="<?php esc_html_e( 'Add', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description"><?php esc_html_e( 'Specify the extension of the file types you want to exclude. You should exclude any type of logs and backup files that tend to be very big.', 'website-file-changes-monitor' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude extensions -->
		</table>
		<!-- Exclude directories, files, extensions -->

		<h3><?php esc_html_e( 'Launch an instant file changes scan', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Click the "Scan now" button to launch an instant file changes scan using the configured settings. You can navigate away from this page during the scan. Note that the instant scan can be more resource intensive than scheduled scans.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tbody>
				<tr>
					<th>
						<label><?php esc_html_e( 'Launch instant scan', 'website-file-changes-monitor' ); ?></label>
					</th>
					<td>
						<fieldset <?php echo esc_attr( $disabled ); ?>>
							<?php if ( 'yes' === $settings['enabled'] && ! wfcm_get_setting( 'scan-in-progress', false ) ) : ?>
								<input type="button" class="button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan now', 'website-file-changes-monitor' ); ?>">
								<input type="button" class="button-secondary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop scan', 'website-file-changes-monitor' ); ?>" disabled>
							<?php elseif ( 'no' === $settings['enabled'] && wfcm_get_setting( 'scan-in-progress', false ) ) : ?>
								<input type="button" class="button button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan in progress', 'website-file-changes-monitor' ); ?>" disabled>
								<input type="button" class="button button-ui-primary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop scan', 'website-file-changes-monitor' ); ?>">
								<!-- Scan in progress -->
							<?php else : ?>
								<input type="button" class="button button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan now', 'website-file-changes-monitor' ); ?>" disabled>
								<input type="button" class="button button-secondary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop scan', 'website-file-changes-monitor' ); ?>" disabled>
							<?php endif; ?>
						</fieldset>
						<div id="wfcm-scan-response" class="hidden">
							<?php /* Translators: Contact us hyperlink */ ?>
							<p><?php echo sprintf( esc_html__( 'Oops! Something went wrong with the scan. Please %s for assistance.', 'website-file-changes-monitor' ), '<a href="https://www.wpwhitesecurity.com/support/?utm_source=plugin&utm_medium=referral&utm_campaign=WFCM&utm_content=help+page" target="_blank">' . esc_html__( 'contact us', 'website-file-changes-monitor' ) . '</a>' ); ?></p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- / Instant Scan -->

		<h3><?php esc_html_e( 'Temporarily disable file scanning', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Use the below switch to disable file scanning. When you disable and re-enable scanning, the plugin will compare the file scan to those of the last scan before it was disabled.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="wfcm-file-changes"><?php esc_html_e( 'File scanning', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<?php esc_html_e( 'Off', 'website-file-changes-monitor' ); ?>
						<div class="wfcm-toggle">
							<label for="wfcm-toggle__switch-keep-log">
								<input type="checkbox" id="wfcm-toggle__switch-keep-log" name="wfcm-settings[keep-log]" value="yes" <?php checked( $settings['enabled'], 'yes' ); ?>>
								<span class="wfcm-toggle__switch"></span>
								<span class="wfcm-toggle__toggle"></span>
							</label>
						</div>
						<?php esc_html_e( 'On', 'website-file-changes-monitor' ); ?>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Disable File Changes -->

		<h3><?php esc_html_e( 'Debug & uninstall settings', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Enable the debug logging when requested by support. This is used for support.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="wfcm-debug-logging"><?php esc_html_e( 'Debug logs', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<input id="wfcm-debug-logging" type="checkbox" name="wfcm-settings[debug-logging]" value="1" <?php checked( $settings['debug-logging'] ); ?>>
					</fieldset>
				</td>
			</tr>
		</table>

		<table class="form-table wfcm-settings-danger">
			<tr>
				<th><label for="wfcm-delete-data"><?php esc_html_e( 'Delete plugin data upon uninstall', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<input id="wfcm-delete-data" name="wfcm-settings[delete-data]" type="checkbox" value="1" <?php checked( $settings['delete-data'] ); ?>>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Delete plugin data and settings -->

		<?php
		wp_nonce_field( 'wfcm-save-admin-settings' );
		submit_button();
		?>
	</form>
</div>
