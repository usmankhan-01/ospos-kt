
<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
	$(document).ready(function()
	{
		var start_date = '<?php echo $start_date; ?>';
		var end_date = '<?php echo $start_date; ?>';
		var mode = '<?php echo $mode; ?>';
		var location = '<?php echo $stock_locations; ?>';
		window.location = [window.location, start_date, end_date, mode, location].join("/");
	});
</script>