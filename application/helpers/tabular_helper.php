<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tabular views helper
 */

/*
Basic tabular headers function
*/
function transform_headers_readonly($array)
{
	$result = array();

	foreach($array as $key => $value)
	{
		$result[] = array('field' => $key, 'title' => $value, 'sortable' => $value != '', 'switchable' => !preg_match('(^$|&nbsp)', $value));
	}

	return json_encode($result);
}

/*
Basic tabular headers function
*/
function transform_headers($array, $readonly = FALSE, $editable = TRUE)
{
	$result = array();

	if(!$readonly)
	{
		$array = array_merge(array(array('checkbox' => 'select', 'sortable' => FALSE)), $array);
	}

	if($editable)
	{
		$array[] = array('edit' => '');
	}

	foreach($array as $element)
	{
		reset($element);
		$result[] = array('field' => key($element),
			'title' => current($element),
			'switchable' => isset($element['switchable']) ? $element['switchable'] : !preg_match('(^$|&nbsp)', current($element)),
			'sortable' => isset($element['sortable']) ? $element['sortable'] : current($element) != '',
			'checkbox' => isset($element['checkbox']) ? $element['checkbox'] : FALSE,
			'class' => isset($element['checkbox']) || preg_match('(^$|&nbsp)', current($element)) ? 'print_hide' : '',
			'sorter' => isset($element['sorter']) ? $element ['sorter'] : '');
	}

	return json_encode($result);
}


/*
Get the header for the sales tabular view
*/
function get_sales_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('sale_id' => $CI->lang->line('common_id')),
		array('sale_time' => $CI->lang->line('sales_sale_time')),
		array('customer_name' => $CI->lang->line('customers_customer')),
		array('sale_total' => $CI->lang->line('sales_sale_total')),
		array('amount_paid' => $CI->lang->line('sales_amount_paid')),
		array('change_returned' => $CI->lang->line('sales_change_returned')),
		array('amount_due' => $CI->lang->line('sales_amount_due'))
		//array('payment_type' => $CI->lang->line('sales_payment_type'))
	);

	if($CI->config->item('invoice_enable') == TRUE)
	{
		$headers[] = array('invoice_number' => $CI->lang->line('sales_invoice_number'));
		$headers[] = array('invoice' => '&nbsp', 'sortable' => FALSE);
	}

	$headers[] = array('view' => '&nbsp', 'sortable' => FALSE);
	$headers[] = array('receipt' => '&nbsp', 'sortable' => FALSE);
	

	return transform_headers($headers);
}

/*
Get the html data row for the sales
*/
function get_sale_data_row($sale)
{
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);

	$row = array (
		'sale_id' => $sale->sale_id,
		'sale_time' => date($CI->config->item('dateformat') . ' ' . $CI->config->item('timeformat'), strtotime($sale->sale_time)),
		'customer_name' => $sale->customer_name,
		'sale_total' => to_currency($sale->sale_total),
		'amount_paid' => to_currency($sale->amount_paid),
		'change_returned' => to_currency($sale->change_returned),
		'amount_due' => to_currency($sale->amount_due)
		//'payment_type' => $sale->payment_type
	);

	if($CI->config->item('invoice_enable'))
	{
		$row['invoice_number'] = $sale->invoice_number;
		$row['invoice'] = empty($sale->invoice_number) ? '' : anchor($controller_name."/invoice/$sale->sale_id", '<span class="glyphicon glyphicon-list-alt"></span>',
			array('title'=>$CI->lang->line('sales_show_invoice'))
		);
	}

	$row['view'] = anchor("receivables/view/$sale->sale_id", '<span class="fas fa-shopping-cart"></span>',
		array('class' => 'modal-dlg-wide modal-dlg print_hide', 'data-btn-save' => 'Save Changes', 'title' => $CI->lang->line('receivables_view'))
	);
	
	$row['receipt'] = anchor($controller_name."/receipt/$sale->sale_id", '<span class="glyphicon glyphicon-usd"></span>',
		array('title' => $CI->lang->line('sales_show_receipt'))
	);
	$row['edit'] = anchor($controller_name."/edit/$sale->sale_id", '<span class="glyphicon glyphicon-edit"></span>',
		array('class' => 'modal-dlg print_hide', 'data-btn-delete' => $CI->lang->line('common_delete'), 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_update'))
	);

	return $row;
}

/*
Get the html data last row for the sales
*/
function get_sale_data_last_row($sales)
{
	$CI =& get_instance();
	$sum_sale_total = 0;
	$sum_amount_paid = 0;
	$sum_change_returned = 0;
	$sum_amount_due = 0;

	foreach($sales->result() as $key=>$sale)
	{
		$sum_sale_total += $sale->sale_total;
		$sum_amount_paid += $sale->amount_paid;
		$sum_change_returned += $sale->change_returned;
		$sum_amount_due += $sale->amount_due;
	}

	return array(
		'sale_id' => '-',
		'sale_time' => '<b>'.$CI->lang->line('sales_total').'</b>',
		'sale_total' => '<b>'.to_currency($sum_sale_total).'</b>',
		'amount_paid' => '<b>'. to_currency($sum_amount_paid).'</b>',
		'amount_due' => '<b>'. to_currency($sum_amount_due).'</b>',
		'change_returned' => '<b>'.to_currency($sum_change_returned).'</b>'
	);
}

/*
Get the header for the payables tabular view
*/
function get_payables_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('sr_no' => $CI->lang->line('sr_no')),
		array('sale_time' => $CI->lang->line('payables_date')),
		array('supplier_name' => $CI->lang->line('suppliers_supplier')),
		array('total_sale' => $CI->lang->line('payables_total_sale')),
		array('total_due' => $CI->lang->line('payables_total_due')),
		array('remaining' => $CI->lang->line('payables_remaining'))
	);

	$headers[] = array('view' => '', 'sortable' => FALSE);

	return transform_headers($headers);
}

/*
Get the html data row for the payables
*/
function get_payables_data_row($payables, $index)
{
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);

	$row = array (
		'sr_no' => $index,
		'payable_id' => $payables->payable_id,
		'sale_time' => date($CI->config->item('dateformat'), strtotime($payables->receiving_time)),
		'supplier_name' => $payables->supplier_name,
		'total_sale' => to_currency($payables->total_receiving),
		'total_due' => to_currency($payables->total_due),
		'remaining' => to_currency($payables->remaining)
	);

	if($payables->remaining > 0)
	{
		$row['edit'] = anchor($controller_name."/edit/$payable->payable_id", '<span class="fab fa-cc-amazon-pay"></span>',
		array('class' => 'modal-dlg print_hide', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_pay'))
		);
	}
	$row['view'] = anchor($controller_name."/view/$payable->receiving_id", '<span class="fas fa-shopping-cart"></span>',
		array('class' => 'modal-dlg-wide modal-dlg print_hide', 'data-btn-save' => 'Save Changes', 'title' => $CI->lang->line($controller_name.'_view'))
	);

	return $row;
}

/*
Get the html data last row for the payables
*/
function get_payables_data_last_row($payables)
{
	$CI =& get_instance();
	$sum_total_sale = 0;
	$sum_total_due = 0;
	$sum_remaining = 0;

	foreach($payables->result() as $key=>$payable)
	{
		$sum_total_sale += $payable->total_sale;
		$sum_total_due += $payable->total_due;
		$sum_remaining += $payable->remaining;
	}

	return array(
		'sr_no' => '-',
		'sale_time' => '<b>'.$CI->lang->line('sales_total').'</b>',
		'total_sale' => '<b>'.to_currency($sum_total_sale).'</b>',
		'total_due' => '<b>'.to_currency($sum_total_due).'</b>',
		'remaining' => '<b>'.to_currency($sum_remaining).'</b>',
	);
}

/*
Get the header for the receivables tabular view
*/
function get_receivables_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('sr_no' => $CI->lang->line('sr_no')),
		array('sale_time' => $CI->lang->line('receivable_date')),
		array('customer_name' => $CI->lang->line('customers_customer')),
		array('total_sale' => $CI->lang->line('receivable_total_sale')),
		array('total_due' => $CI->lang->line('receivable_total_due')),
		array('remaining' => $CI->lang->line('receivable_remaining'))
	);

	$headers[] = array('view' => '', 'sortable' => FALSE);

	return transform_headers($headers);
}

/*
Get the html data row for the receivables
*/
function get_receivable_data_row($receivable, $index)
{
	$CI =& get_instance();
	$controller_name = $CI->uri->segment(1);

	$row = array (
		'sr_no' => $index,
		'receivable_id' => $receivable->receivable_id,
		'sale_time' => date($CI->config->item('dateformat'), strtotime($receivable->sale_time)),
		'customer_name' => $receivable->customer_name,
		'total_sale' => to_currency($receivable->total_sale),
		'total_due' => to_currency($receivable->total_due),
		'remaining' => to_currency($receivable->remaining)
	);

	if($receivable->remaining > 0)
	{
		$row['edit'] = anchor($controller_name."/edit/$receivable->receivable_id", '<span class="fab fa-cc-amazon-pay"></span>',
		array('class' => 'modal-dlg print_hide', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_pay'))
		);
	}
	$row['view'] = anchor($controller_name."/view/$receivable->sale_id", '<span class="fas fa-shopping-cart"></span>',
		array('class' => 'modal-dlg-wide modal-dlg print_hide', 'data-btn-save' => 'Save Changes', 'title' => $CI->lang->line($controller_name.'_view'))
	);

	return $row;
}

/*
Get the html data last row for the receivables
*/
function get_receivable_data_last_row($receivables)
{
	$CI =& get_instance();
	$sum_total_sale = 0;
	$sum_total_due = 0;
	$sum_remaining = 0;

	foreach($receivables->result() as $key=>$receivable)
	{
		$sum_total_sale += $receivable->total_sale;
		$sum_total_due += $receivable->total_due;
		$sum_remaining += $receivable->remaining;
	}

	return array(
		'sr_no' => '-',
		'sale_time' => '<b>'.$CI->lang->line('sales_total').'</b>',
		'total_sale' => '<b>'.to_currency($sum_total_sale).'</b>',
		'total_due' => '<b>'.to_currency($sum_total_due).'</b>',
		'remaining' => '<b>'.to_currency($sum_remaining).'</b>',
	);
}

/*
Get the sales payments summary
*/
function get_sales_manage_payments_summary($payments, $sales)
{
	$CI =& get_instance();
	$table = '<div id="report_summary">';

	foreach($payments as $key=>$payment)
	{
		$amount = $payment['payment_amount'];

		// WARNING: the strong assumption here is that if a change is due it was a cash transaction always
		// therefore we remove from the total cash amount any change due
		if( $payment['payment_type'] == $CI->lang->line('sales_cash') )
		{
			foreach($sales->result_array() as $key=>$sale)
			{
				$amount -= $sale['change_due'];
			}
		}
		$table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . to_currency($amount) . '</div>';
	}
	$table .= '</div>';

	return $table;
}

/*
Get the sales payments summary
*/
function get_sales_manage_payments_receivables_summary($payments, $sales, $receivables)
{
	$CI =& get_instance();
	$table = '<div id="row">';
	$bgColors = array('#117a8b', '#1e7e34', '#17a2b8', '#545b62', '#007bff', '#343a40');
	$colorIndex = 0;
	$total_payment = 0;
	$total_change_returned;
	$total_sale = 0;

	$column_margin_left = (12 - count($payments)*2) / 2;
	$table .= '<div class="col-lg-' . $column_margin_left . '"> </div>';

	foreach($payments as $key=>$payment)
	{
		$total_payment += $payment['payment_amount'];
		$amount = $payment['payment_amount'];

		// WARNING: the strong assumption here is that if a change is due it was a cash transaction always
		// therefore we remove from the total cash amount any change due
		if( $payment['payment_type'] == $CI->lang->line('sales_cash') )
		{
			foreach($sales->result_array() as $key=>$sale)
			{
				$amount -= $sale['change_due'];
			}
		}
		$table .= info_card('col-lg-2','#fff',$bgColors[$colorIndex],'fa-dollar-sign',to_currency($amount),'#fff',$payment['payment_type']);
		$colorIndex++;
	}

	foreach($sales->result() as $sale)
	{
		$total_sale += $sale->sale_total;
		$total_change_returned += $sale->change_returned;
	}

	$table .= '</div>';

	return $table;
}


/*
Get the header for the people tabular view
*/
function get_people_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('people.person_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number'))
	);

	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = array('messages' => '', 'sortable' => FALSE);
	}

	return transform_headers($headers);
}

/*
Get the html data row for the person
*/
function get_person_data_row($person)
{
	$CI =& get_instance();
	$controller_name = strtolower(get_class($CI));

	return array (
		'people.person_id' => $person->person_id,
		'last_name' => $person->last_name,
		'first_name' => $person->first_name,
		'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
		'phone_number' => $person->phone_number,
		'messages' => empty($person->phone_number) ? '' : anchor("Messages/view/$person->person_id", '<span class="glyphicon glyphicon-phone"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$person->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
	));
}


/*
Get the header for the customer tabular view
*/
function get_customer_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('people.person_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number')),
		array('total' => $CI->lang->line('common_total_spent'), 'sortable' => FALSE)
	);

	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = array('messages' => '', 'sortable' => FALSE);
	}

	return transform_headers($headers);
}

/*
Get the html data row for the customer
*/
function get_customer_data_row($person, $stats)
{
	$CI =& get_instance();
	$controller_name = strtolower(get_class($CI));

	return array (
		'people.person_id' => $person->person_id,
		'last_name' => $person->last_name,
		'first_name' => $person->first_name,
		'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
		'phone_number' => $person->phone_number,
		'total' => to_currency($stats->total),
		'messages' => empty($person->phone_number) ? '' : anchor("Messages/view/$person->person_id", '<span class="glyphicon glyphicon-phone"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$person->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
	));
}


/*
Get the header for the suppliers tabular view
*/
function get_suppliers_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('people.person_id' => $CI->lang->line('common_id')),
		array('company_name' => $CI->lang->line('suppliers_company_name')),
		array('agency_name' => $CI->lang->line('suppliers_agency_name')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('email' => $CI->lang->line('common_email')),
		array('phone_number' => $CI->lang->line('common_phone_number'))
	);

	if($CI->Employee->has_grant('messages', $CI->session->userdata('person_id')))
	{
		$headers[] = array('messages' => '');
	}

	return transform_headers($headers);
}

/*
Get the html data row for the supplier
*/
function get_supplier_data_row($supplier)
{
	$CI =& get_instance();
	$controller_name = strtolower(get_class($CI));

	return array (
		'people.person_id' => $supplier->person_id,
		'company_name' => $supplier->company_name,
		'agency_name' => $supplier->agency_name,
		'last_name' => $supplier->last_name,
		'first_name' => $supplier->first_name,
		'email' => empty($supplier->email) ? '' : mailto($supplier->email, $supplier->email),
		'phone_number' => $supplier->phone_number,
		'messages' => empty($supplier->phone_number) ? '' : anchor("Messages/view/$supplier->person_id", '<span class="glyphicon glyphicon-phone"></span>',
			array('class'=>"modal-dlg", 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line('messages_sms_send'))),
		'edit' => anchor($controller_name."/view/$supplier->person_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>"modal-dlg", 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update')))
		);
}


/*
Get the header for the items tabular view
*/
function get_items_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('items.item_id' => $CI->lang->line('common_id')),
		array('item_number' => $CI->lang->line('items_item_number')),
		array('name' => $CI->lang->line('items_name')),
		array('category' => $CI->lang->line('items_category')),
		array('company_name' => $CI->lang->line('suppliers_company_name')),
		array('cost_price' => $CI->lang->line('items_cost_price')),
		array('unit_price' => $CI->lang->line('items_unit_price')),
		array('quantity' => $CI->lang->line('items_quantity')),
		array('tax_percents' => $CI->lang->line('items_tax_percents'), 'sortable' => FALSE),
		array('item_pic' => $CI->lang->line('items_image'), 'sortable' => FALSE)
	);

	//adding custom fields data in items list
	for ($i = 1; $i <= 10; ++$i)
	{
		if($CI->config->item('custom'.$i.'_name') != NULL)
		{
			array_push($headers, array('custom'.$i => $CI->config->item('custom'.$i.'_name')));
		}
	}
	
	array_push($headers, array('inventory' => ''), array('stock' => ''));
	return transform_headers($headers);
}

/*
Get the html data row for the item
*/
function get_item_data_row($item)
{
	$CI =& get_instance();
	$item_tax_info = $CI->Item_taxes->get_info($item->item_id);
	$tax_percents = '';
	foreach($item_tax_info as $tax_info)
	{
		$tax_percents .= to_tax_decimals($tax_info['percent']) . '%, ';
	}
	// remove ', ' from last item
	$tax_percents = substr($tax_percents, 0, -2);
	$controller_name = strtolower(get_class($CI));

	$image = NULL;
	if($item->pic_filename != '')
	{
		$ext = pathinfo($item->pic_filename, PATHINFO_EXTENSION);
		if($ext == '')
		{
			// legacy
			$images = glob('./uploads/item_pics/' . $item->pic_filename . '.*');
		}
		else
		{
			// preferred
			$images = glob('./uploads/item_pics/' . $item->pic_filename);
		}

		if(sizeof($images) > 0)
		{
			$image .= '<a class="rollover" href="'. base_url($images[0]) .'"><img src="'.site_url('items/pic_thumb/' . pathinfo($images[0], PATHINFO_BASENAME)) . '"></a>';
		}
	}

	$result = array (
		'items.item_id' => $item->item_id,
		'item_number' => $item->item_number,
		'name' => $item->name,
		'category' => $item->category,
		'company_name' => $item->company_name,
		'cost_price' => to_currency($item->cost_price),
		'unit_price' => to_currency($item->unit_price),
		'quantity' => to_quantity_decimals($item->quantity),
		'tax_percents' => !$tax_percents ? '-' : $tax_percents,
		'item_pic' => $image,
		'custom1' => $item->custom1,
		'custom2' => $item->custom2,
		'custom3' => $item->custom3,
		'custom4' => $item->custom4,
		'custom5' => $item->custom5,
		'custom6' => $item->custom6,
		'custom7' => $item->custom7,
		'custom8' => $item->custom8,
		'custom9' => $item->custom9,
		'custom10' => $item->custom10,
		'inventory' => anchor($controller_name."/inventory/$item->item_id", '<span class="glyphicon glyphicon-pushpin"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_count'))
		),
		'stock' => anchor($controller_name."/count_details/$item->item_id", '<span class="glyphicon glyphicon-list-alt"></span>',
			array('class' => 'modal-dlg', 'title' => $CI->lang->line($controller_name.'_details_count'))
		),
		'edit' => anchor($controller_name."/view/$item->item_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class' => 'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title' => $CI->lang->line($controller_name.'_update'))
		));

	return $result;
}


/*
Get the header for the giftcard tabular view
*/
function get_giftcards_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('giftcard_id' => $CI->lang->line('common_id')),
		array('last_name' => $CI->lang->line('common_last_name')),
		array('first_name' => $CI->lang->line('common_first_name')),
		array('giftcard_number' => $CI->lang->line('giftcards_giftcard_number')),
		array('value' => $CI->lang->line('giftcards_card_value'))
	);

	return transform_headers($headers);
}

/*
Get the html data row for the giftcard
*/
function get_giftcard_data_row($giftcard)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	return array (
		'giftcard_id' => $giftcard->giftcard_id,
		'last_name' => $giftcard->last_name,
		'first_name' => $giftcard->first_name,
		'giftcard_number' => $giftcard->giftcard_number,
		'value' => to_currency($giftcard->value),
		'edit' => anchor($controller_name."/view/$giftcard->giftcard_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}


/*
Get the header for the taxes tabular view
*/
function get_taxes_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('tax_code' => $CI->lang->line('taxes_tax_code')),
		array('tax_code_name' => $CI->lang->line('taxes_tax_code_name')),
		array('tax_code_type_name' => $CI->lang->line('taxes_tax_code_type')),
		array('tax_rate' => $CI->lang->line('taxes_tax_rate')),
		array('rounding_code_name' => $CI->lang->line('taxes_rounding_code')),
		array('city' => $CI->lang->line('common_city')),
		array('state' => $CI->lang->line('common_state'))
	);

	return transform_headers($headers);
}

/*
Get the html data row for the tax
*/
function get_tax_data_row($tax_code_row)
{
	$CI =& get_instance();
	$controller_name=strtolower(get_class($CI));

	return array (
		'tax_code' => $tax_code_row->tax_code,
		'tax_code_name' => $tax_code_row->tax_code_name,
		'tax_code_type' => $tax_code_row->tax_code_type,
		'tax_rate' => $tax_code_row->tax_rate,
		'rounding_code' =>$tax_code_row->rounding_code,
		'tax_code_type_name' => $CI->Tax->get_tax_code_type_name($tax_code_row->tax_code_type),
		'rounding_code_name' => Rounding_mode::get_rounding_code_name($tax_code_row->rounding_code),
		'city' => $tax_code_row->city,
		'state' => $tax_code_row->state,
		'edit' => anchor($controller_name."/view/$tax_code_row->tax_code", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}


/*
Get the header for the item kits tabular view
*/
function get_item_kits_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('item_kit_id' => $CI->lang->line('item_kits_kit')),
		array('name' => $CI->lang->line('item_kits_name')),
		array('description' => $CI->lang->line('item_kits_description')),
		array('total_cost_price' => $CI->lang->line('items_cost_price'), 'sortable' => FALSE),
		array('total_unit_price' => $CI->lang->line('items_unit_price'), 'sortable' => FALSE)
	);

	return transform_headers($headers);
}

/*
Get the html data row for the item kit
*/
function get_item_kit_data_row($item_kit)
{
	$CI =& get_instance();
	$controller_name = strtolower(get_class($CI));

	return array (
		'item_kit_id' => $item_kit->item_kit_id,
		'name' => $item_kit->name,
		'description' => $item_kit->description,
		'total_cost_price' => to_currency($item_kit->total_cost_price),
		'total_unit_price' => to_currency($item_kit->total_unit_price),
		'edit' => anchor($controller_name."/view/$item_kit->item_kit_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}


/*
Get the header for the expense categories tabular view
*/
function get_expense_category_manage_table_headers()
{
	$CI =& get_instance();

	$headers = array(
		array('expense_category_id' => $CI->lang->line('expenses_categories_category_id')),
		array('category_name' => $CI->lang->line('expenses_categories_name')),
		array('category_description' => $CI->lang->line('expenses_categories_description'))
	);

	return transform_headers($headers);
}

/*
Gets the html data row for the expenses category
*/
function get_expense_category_data_row($expense_category)
{
	$CI =& get_instance();
	$controller_name = strtolower(get_class($CI));

	return array (
		'expense_category_id' => $expense_category->expense_category_id,
		'category_name' => $expense_category->category_name,
		'category_description' => $expense_category->category_description,
		'edit' => anchor($controller_name."/view/$expense_category->expense_category_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}


/*
Get the header for the expenses tabular view
*/
function get_expenses_manage_table_headers()
{
	$CI =& get_instance();
	$headers = array(
		array('expense_id' => $CI->lang->line('expenses_expense_id')),
		array('date' => $CI->lang->line('expenses_date')),
		array('supplier_name' => $CI->lang->line('expenses_supplier_name')),
		array('supplier_tax_code' => $CI->lang->line('expenses_supplier_tax_code')),
		array('amount' => $CI->lang->line('expenses_amount')),
		array('tax_amount' => $CI->lang->line('expenses_tax_amount')),
		array('payment_type' => $CI->lang->line('expenses_payment')),
		array('category_name' => $CI->lang->line('expenses_categories_name')),
		array('description' => $CI->lang->line('expenses_description')),
		array('createdBy' => $CI->lang->line('expenses_employee'))
	);

	return transform_headers($headers);
}

/*
Gets the html data row for the expenses.
*/
function get_expenses_data_row($expense)
{
	$CI =& get_instance();
	$controller_name = strtolower(get_class($CI));
	return array (
		'expense_id' => $expense->expense_id,
		'date' => date($CI->config->item('dateformat') . ' ' . $CI->config->item('timeformat'), strtotime($expense->date)),
		'supplier_name' => $expense->supplier_name,
		'supplier_tax_code' => $expense->supplier_tax_code,
		'amount' => to_currency($expense->amount),
		'tax_amount' => to_currency($expense->tax_amount),
		'payment_type' => $expense->payment_type,
		'category_name' => $expense->category_name,
		'description' => $expense->description,
		'createdBy' => $expense->first_name.' '. $expense->last_name,
		'edit' => anchor($controller_name."/view/$expense->expense_id", '<span class="glyphicon glyphicon-edit"></span>',
			array('class'=>'modal-dlg', 'data-btn-submit' => $CI->lang->line('common_submit'), 'title'=>$CI->lang->line($controller_name.'_update'))
		));
}

/*
Get the html data last row for the expenses
*/
function get_expenses_data_last_row($expense)
{
	$CI =& get_instance();
	$table_data_rows = '';
	$sum_amount_expense = 0;
	$sum_tax_amount_expense = 0;

	foreach($expense->result() as $key=>$expense)
	{
		$sum_amount_expense += $expense->amount;
		$sum_tax_amount_expense += $expense->tax_amount;
	}

	return array(
		'expense_id' => '-',
		'date' => '<b>'.$CI->lang->line('sales_total').'</b>',
		'amount' => '<b>'. to_currency($sum_amount_expense).'</b>',
		'tax_amount' => '<b>'. to_currency($sum_tax_amount_expense).'</b>'
	);
}

/*
Get the expenses payments summary
*/
function get_expenses_manage_payments_summary($payments, $expenses)
{
	$CI =& get_instance();
	$table = '<div id="report_summary">';

	foreach($payments as $key=>$payment)
	{
		$amount = $payment['amount'];
		$table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . to_currency($amount) . '</div>';
	}
	$table .= '</div>';

	return $table;
}

?>
