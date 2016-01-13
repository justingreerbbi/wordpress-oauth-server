<?php
/**
 * Server Status
 */
function wo_server_status_page (){
 ?>
<div class="wrap">
	<h2>Server Status</h2>
	<p>
		The following information is helpful when debugging or reporting an issue. Please note that the
		information provided here is a reference only.
	</p>
	<table>
		<tr>
			<th style="text-align:left;">Plugin Build: </th>
			<td>
				<?php echo strpos(_WO()->version, '-') ? _WO()->version . " <span style='color:orange;'><small>You are using a development version of the plugin.</small></span>" : _WO()->version;?>
			</td>
		</tr>

		<tr>
			<th style="text-align:left;">PHP Version (<?php echo PHP_VERSION;?>): </th>
			<td>
				<?php echo version_compare(PHP_VERSION, '5.3.9') >= 0 ? " <span style='color:green;'>OK</span>" : " <span style='color:red;'>Failed</span> - <small>Please upgrade PHP to 5.4 or greater.</small>";?>
			</td>
		</tr>

		<tr>
			<th style="text-align:left;">Apache Version: </th>
			<td>
				<?php echo function_exists('apache_get_version') ? apache_get_version() : '<strong>apache_get_version()</strong> not enabled.'; ?>
			</td>
		</tr>

		<tr>
			<th style="text-align:left;">Running CGI: </th>
			<td>
				<?php echo substr(php_sapi_name(), 0, 3) != 'cgi' ? " <span style='color:green;'>OK</span>" : " <span style='color:orange;'>Notice</span> - <small>Header 'Authorization Basic' may not work as expected.</small>";?>
			</td>
		</tr>

		<tr>
			<th style="text-align:left;">Certificates Generated: </th>
			<td>
				<?php echo !wo_has_certificates() ? " <span style='color:red;'>No Certificates Found</span>" : "<span style='color:green;'>Certificates Found</span>"?>
			</td>
		</tr>

		<tr>
			<th style="text-align:left;">License: </th>
			<td>
				<?php echo !_vl() ? " <span style='color:orange;'>Standard" : "<span style='color:green;'>Licensed</span>"?>
			</td>
		</tr>

	</table>
</div>
<?php
}