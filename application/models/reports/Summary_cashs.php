<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_cashs extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('date' => $this->lang->line('reports_date')),
			array('payment_type' => $this->lang->line('reports_payment_type')),
			array('amount' => $this->lang->line('reports_cash_amount'), 'sorter' => 'number_sorter'),
			array('action' => '')
		);
	}

	private function _common_from()
	{
		$this->db->from('sales AS sales');
	}

	private function _common_where(array $inputs)
	{
		$this->db->where('sales.sale_status', COMPLETED);
		$this->db->group_start();
			$this->db->where('sales.sale_type', SALE_TYPE_POS);
			$this->db->or_where('sales.sale_type', SALE_TYPE_INVOICE);
			$this->db->or_where('sales.sale_type', SALE_TYPE_RETURN);
		$this->db->group_end();
	}

	public function getData(array $inputs)
	{
		$this->db->select('sales_payments.payment_date, sales_payments.payment_type, sales_payments.payment_amount, sales_payments.sale_id');
		$this->db->from('sales_payments AS sales_payments');
		
		$this->_common_from();

		$this->db->where('DATE(sales_payments.payment_date) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		$this->db->where('sales_payments.sale_id = sales.sale_id');
		
		$this->_common_where($inputs);

		$payments = $this->db->get()->result_array();

		return $payments;
	}

	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(payments.payment_amount) AS subtotal');
		$this->db->from('sales_payments AS payments');
		$this->db->from('sales AS sales');
		$this->db->where('DATE(payments.payment_date) BETWEEN ' . $this->db->escape($inputs['start_date']) . 
						 ' AND ' . $this->db->escape($inputs['end_date']));
		$this->db->where('payments.sale_id = sales.sale_id');
		
		$this->_common_where($inputs);
		
		$total_payment = $this->db->get()->row_array();

		$this->db->select('SUM(sales.change_returned) AS change_returned');
		$this->db->from('sales AS sales');
		$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		
		$this->_common_where($inputs);
		
		$change_returned = $this->db->get()->row_array();

		$summary = array (
			'subtotal' => $total_payment['subtotal'],
			'change_returned' => $change_returned['change_returned'],
			'total' => $total_payment['subtotal'] - $change_returned['change_returned']
		);

		return $summary;
	}
}
?>
