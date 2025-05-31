<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("payables/save/".$payable_info['payable_id'], array('id'=>'payable_pay_form', 'class'=>'form-horizontal')); ?>
	<fieldset  id="payables_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('payables_remaining'), 'remaining', array('class'=>'control-label col-xs-3')); ?>
			<?php echo form_label($payable_info['remaining'], 'remaining_value', array('class'=>'control-label col-xs-8', "style"=>"text-align:left")); ?>
		</div>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('payables_pay_amount'), 'amount', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name' => 'payable_amount', 'value' => $payable_amount, 'id' => 'payable_amount', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('payable_id', $payable_id);?>
				<?php echo form_hidden('receiving_id', $receiving_id);?>
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
		$("input[name='payable_id']").val(ui.item.value);
		$("input[name='payable_amount']").val(ui.item.label);
	};


	// declare submitHandler as an object.. will be reused
	var submit_form = function()
	{
		$(this).ajaxSubmit(
		{
			success:function(response)
			{
				dialog_support.hide();
				table_support.handle_submit('<?php echo site_url('payables'); ?>', response);
			},
			dataType:'json'
		});
	};

	$('#payable_pay_form').validate($.extend(
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
