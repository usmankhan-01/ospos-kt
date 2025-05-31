<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Receivables extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('receivables');

		$this->load->library('receiving_lib');
		$this->load->library('sale_lib');
		$this->load->library('barcode_lib');
	}

	public function index()
	{

		$this->db->set('amount','amount+'. $amount, False);
		$this->db->where('sale_id', $sale_id);
		$this->db->update('sales_receivables');

		$person_id = $this->session->userdata('person_id');

		if(!$this->Employee->has_grant('reports_sales', $person_id))
		{
			redirect('no_access/sales/reports_sales');
		}
		else
		{
			$data['table_headers'] = get_receivables_manage_table_headers();

			// filters that will be loaded in the multiselect dropdown
			if($this->config->item('invoice_enable') == TRUE)
			{
				$data['filters'] = array(
					'only_invoices' => $this->lang->line('sales_invoice_filter'));
			}
			else
			{
				$data['filters'] = array('only_cash' => $this->lang->line('sales_cash_filter'),
					'only_due' => $this->lang->line('sales_due_filter'),
					'only_check' => $this->lang->line('sales_check_filter'));
			}

		}
		$this->load->view("receivables/manage", $data);
	}

	public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$filters = array('sale_type' => 'all',
						 'location_id' => 'all',
						 'start_date' => $this->input->get('start_date'),
						 'end_date' => $this->input->get('end_date'),
						 'only_invoices' => $this->config->item('invoice_enable') && $this->input->get('only_invoices'),
						 'is_valid_receipt' => $this->Sale->is_valid_receipt($search));

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);

		$receivables = $this->Receivable->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Receivable->get_found_rows($search, $filters);

		$data_rows = array();
		$index = 1;
		foreach($receivables->result() as $receivable)
		{
			$data_rows[] = $this->xss_clean(get_receivable_data_row($receivable, $offset + $index));
			$index++;
		}

		if($total_rows > 0)
		{
			$data_rows[] = $this->xss_clean(get_receivable_data_last_row($receivables));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function edit($receivable_id)
	{
		$data = array();

		$data['employees'] = array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			foreach(get_object_vars($employee) as $property => $value)
			{
				$employee->$property = $this->xss_clean($value);
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$receivable_info = $this->xss_clean($this->Receivable->get_info($receivable_id)->row_array());
		$data['receivable_amount'] = $receivable_info['remaining'];
		$data['receivable_id'] = $receivable_info['receivable_id'];
		$data['sale_id'] = $receivable_info['sale_id'];
		$data['receivable_info'] = $receivable_info;

		$this->load->view('receivables/form', $data);
	}

	/**
	 * This saves the receivable from the update receivable view (receivable/form).
	 * It only updates the receivable table and payments.
	 * @param int $receivable_id
	 */
	public function save($receivable_id = -1)
	{

		$receivable_data = array(
			'receivable_id' => $this->input->post('receivable_id'),
			'receivable_amount' => $this->input->post('receivable_amount')
		);

		// go through all the payment type input from the form, make sure the form matches the name and iterator number
		$payments = array(
			'receivable_id' => $this->input->post('receivable_id'),
			'sale_id' => $this->input->post('sale_id'),
			'payment_amount' => $this->input->post('receivable_amount'),
			'payment_type' => 'Cash',
			'payment_date' => date('Y-m-d H:i:s')
			);	

		if($this->Receivable->addPayment($payments) && $receivable_id > 0)
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('receivables_successfully_updated'), 'id' => $receivable_id));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('receivables_unsuccessfully_updated'), 'id' => $receivable_id));
		}
	}

	public function view($sale_id)
	{
		$this->sale_lib->empty_sales_payments();

		$data = array();

		$sale_info = $this->Sale->get_sale_info($sale_id);

		$data["sale_info"] = $sale_info;
		$data["sale_payments"] = $sale_info["sale_payments"];
		$data["sales_items"] = $sale_info["sales_items"];
		$this->load->view('sales/sale_info', $data);
	}

	public function delete_payment($payment_id)
	{
		$this->sale_lib->delete_sales_payment($payment_id);
		$data['result']= true;
		return json_encode($data);
	}

	public function save_payments()
	{
		//saving payment data
		$payments_data = $this->sale_lib->get_sales_payments();
		foreach($payments_data as $payment)
		{
			if (isset($payment->isDelete) && $payment->isDelete)
			{
				$this->Receivable->row_delete($payment->payment_id, $payment->sale_id, $payment->payment_amount);
			}
		}

		//saving sale items data
		$sale_info = $this->sale_lib->get_sales_info();
		$sales_items = $sale_info["sales_items"];
		foreach ($sales_items as $line=>$item)
		{
			if (isset($item->isModified) && $item->isModified)
			{
				$values = array('quantity_purchased' => $item->item_quantity); 
				$this->db->where('sale_id', $item->sale_id);
				$this->db->where('item_id', $item->item_id);
				$this->db->where('line', $item->item_line);
				$this->db->update('sales_items', $values);
			}

			if (isset($item->isDelete) && $item->isDelete)
			{
				$this->db->where('sale_id', $item->sale_id);
				$this->db->where('item_id', $item->item_id);
				$this->db->where('line', $item->item_line);
				$this->db->delete('sales_items');
			}
		}

		//saving remaining amount in receivables table
		$amount = array('amount' => $sale_info["remaining_amount"]); 
		$this->db->where('sale_id', $sale_info["sale_id"]);
		$this->db->update('sales_receivables', $amount);
	 
		$this->output
		->set_content_type("application/json")
		->set_output(json_encode(array('status'=>true, 'redirect'=>base_url('receivables') )));
	}

	public function edit_sale_item()
	{
		$item_id = $this->input->get('item_id');
		$quantity = $this->input->get('quantity');
		$existing_quantity = $this->input->get('existing_quantity');
		$sale_id = $this->input->get('sale_id');
		$item_line = $this->input->get('itemline');
		$item_location = $this->input->get('itemlocation');

		$data = array();

		if ($quantity > 0 && $quantity <= $existing_quantity)
		{
			$quantity = parse_decimals($quantity);
			$this->Sale->edit_sale_item($item_id, $sale_id, $item_line, $quantity);
		}
		else
		{
			$data['error_message'] = $this->lang->line('sales_error_editing_item');
		}

		//reloading the pop up content
		$sale_info = $this->sale_lib->get_sales_info();
		$data["sale_info"] = $sale_info;
		$data["sale_payments"] = $sale_info["sale_payments"];
		$data["sales_items"] = $sale_info["sales_items"];
		$theHTMLResponse = $this->load->view('sales/sale_info', $data, TRUE);

		$this->output
		->set_content_type("application/json")
		->set_output(json_encode(array('status'=>true, 'html'=>$theHTMLResponse )));
	}

	public function delete_sale_item()
	{
		$item_id = $this->input->get('item_id');
		$sale_id = $this->input->get('sale_id');
		$item_line = $this->input->get('itemline');

		$this->sale_lib->delete_sales_item($item_id, $sale_id, $item_line);
		$data['result']= true;
		return json_encode($data);
	}
}
?>
