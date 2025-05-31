<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("receivables/save/".$receivable_info['receivable_id'], array('id'=>'receivable_pay_form', 'class'=>'form-horizontal')); ?>
	<fieldset  id="receivables_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivable_remaining'), 'remaining', array('class'=>'control-label col-xs-3')); ?>
			<?php echo form_label($receivable_info['remaining'], 'remaining_value', array('class'=>'control-label col-xs-8', "style"=>"text-align:left")); ?>
		</div>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivables_pay_amount'), 'amount', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name' => 'receivable_amount', 'value' => $receivable_amount, 'id' => 'receivable_amount', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('receivable_id', $receivable_id);?>
				<?php echo form_hidden('sale_id', $sale_id);?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>
		
<script type="text/javascript">
$(document).ready(function()
{

	var fill_value = function(event, ui)
	{
		event.preventDefault();
		$("input[name='receivable_id']").val(ui.item.value);
		$("input[name='receivable_amount']").val(ui.item.label);
	};


	// declare submitHandler as an object.. will be reused
	var submit_form = function()
	{
		$(this).ajaxSubmit(
		{
			success:function(response)
			{
				dialog_support.hide();
				table_support.handle_submit('<?php echo site_url('receivables'); ?>', response);
			},
			dataType:'json'
		});
	};

	$('#receivable_pay_form').validate($.extend(
	{
		submitHandler : function(form)
		{
			submit_form.call(form);
		},
		rules:
		{
			receivable_amount: {
				required: true,
				minStrict: 0,
				number: true
			}
		}
	}, form_support.error));

	$.validator.addMethod('minStrict', function (value, el, param) {
    		return value > param;
		}, function(params, element) {
  			return 'The amount to pay should be greater than 0'
	});

});
</script>
