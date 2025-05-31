<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

abstract class Summary_report extends Report
{
	/*
	 * function which returns query for getting sale IDs for sales whose full payment
	 * has been cleared within the given period
	 */
	private function _get_paid_sales_ids($start_date, $end_date)
	{
		// Pick up only non-suspended records
		$where = 'sales.sale_status = 0 AND ';
		$where2 = '';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'DATE(payments.payment_date) BETWEEN ' . $this->db->escape($start_date) . ' AND ' . $this->db->escape($end_date);
			$where2 = 'DATE(sales_payments.payment_date) <= ' . $this->db->escape($end_date);
		}
		else
		{
			$where .= 'payments.payment_date BETWEEN ' . $this->db->escape(rawurldecode($start_date)) . ' AND ' . $this->db->escape(rawurldecode($end_date));
			$where2 = 'sales_payments.payment_date <= ' . $this->db->escape($end_date);
		}

		$decimals = totals_decimals();

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased - ROUND((sales_items.item_unit_price * sales_items.quantity_purchased * sales_items.discount_percent / 100), ' . $decimals . ')';
		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';

		if($this->config->item('tax_included'))
		{
			$sale_total = 'SUM(' . $sale_price . ')';
			$sale_subtotal = $sale_total . ' - ' . $tax;
		}
		else
		{
			$sale_subtotal = 'SUM(' . $sale_price . ')';
			$sale_total = $sale_subtotal . ' + ' . $tax;
		}
		
		return '
			SELECT b.sale_id
			FROM (
				SELECT sales_payments.sale_id AS sale_id, IFNULL(SUM(sales_payments.payment_amount), 0) AS sale_payment_amount
				FROM '. $this->db->dbprefix('sales_payments').' AS sales_payments
				WHERE sales_payments.sale_id IN (
					SELECT payments.sale_id AS sale_id
					FROM '. $this->db->dbprefix('sales_payments').' AS payments
					INNER JOIN '. $this->db->dbprefix('sales').' AS sales ON sales.sale_id = payments.sale_id
					WHERE '.$where.'
				) AND ' . $where2 . '
				GROUP BY sale_id 
			) a
			INNER JOIN (
				SELECT sales.sale_id AS sale_id, IFNULL('.$sale_total.', '.$sale_subtotal.') AS sale_total, sales.change_returned AS change_returned
				FROM '. $this->db->dbprefix('sales_items').' AS sales_items
				INNER JOIN '. $this->db->dbprefix('sales').' AS sales ON sales_items.sale_id = sales.sale_id
				LEFT OUTER JOIN
					(
						SELECT sales_items_taxes.sale_id AS sale_id, sales_items_taxes.item_id AS item_id, sales_items_taxes.line AS line, SUM(sales_items_taxes.item_tax_amount) AS tax 
						FROM '. $this->db->dbprefix('sales_items_taxes').' AS sales_items_taxes 
						INNER JOIN '. $this->db->dbprefix('sales').' AS sales ON sales.sale_id = sales_items_taxes.sale_id 
						INNER JOIN '. $this->db->dbprefix('sales_items').' AS sales_items ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line 
						WHERE sales.sale_status = 0 
						GROUP BY sale_id, item_id, line
					) sales_items_taxes ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line
				WHERE sales.sale_status = 0
				GROUP BY sales.sale_id
				ORDER BY sales.sale_time ASC 
			) b ON b.sale_id = a.sale_id AND (a.sale_payment_amount - b.change_returned = b.sale_total)
		';
	}

	/*
	 * function which returns query for getting all sale IDs for sales whose full payment
	 * has been cleared till the end date
	 */
	private function _get_paid_sales_ids_to_date($start_date, $end_date)
	{
		// Pick up only non-suspended records
		$where = 'sales.sale_status = 0 AND ';
		$where2 = '';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'DATE(payments.payment_date) <= ' . $this->db->escape($end_date);
			$where2 = 'DATE(sales_payments.payment_date) <= ' . $this->db->escape($end_date);
		}
		else
		{
			$where .= 'payments.payment_date <= ' . $this->db->escape(rawurldecode($end_date));
			$where2 = 'sales_payments.payment_date <= ' . $this->db->escape($end_date);
		}

		$decimals = totals_decimals();

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased - ROUND((sales_items.item_unit_price * sales_items.quantity_purchased * sales_items.discount_percent / 100), ' . $decimals . ')';
		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';

		if($this->config->item('tax_included'))
		{
			$sale_total = 'SUM(' . $sale_price . ')';
			$sale_subtotal = $sale_total . ' - ' . $tax;
		}
		else
		{
			$sale_subtotal = 'SUM(' . $sale_price . ')';
			$sale_total = $sale_subtotal . ' + ' . $tax;
		}
		
		return '
			SELECT b.sale_id
			FROM (
				SELECT sales_payments.sale_id AS sale_id, IFNULL(SUM(sales_payments.payment_amount), 0) AS sale_payment_amount
				FROM '. $this->db->dbprefix('sales_payments').' AS sales_payments
				WHERE ' . $where2 . '
				GROUP BY sale_id 
			) a
			INNER JOIN (
				SELECT sales.sale_id AS sale_id, IFNULL('.$sale_total.', '.$sale_subtotal.') AS sale_total, sales.change_returned AS change_returned
				FROM '. $this->db->dbprefix('sales_items').' AS sales_items
				INNER JOIN '. $this->db->dbprefix('sales').' AS sales ON sales_items.sale_id = sales.sale_id
				LEFT OUTER JOIN
					(
						SELECT sales_items_taxes.sale_id AS sale_id, sales_items_taxes.item_id AS item_id, sales_items_taxes.line AS line, SUM(sales_items_taxes.item_tax_amount) AS tax 
						FROM '. $this->db->dbprefix('sales_items_taxes').' AS sales_items_taxes 
						INNER JOIN '. $this->db->dbprefix('sales').' AS sales ON sales.sale_id = sales_items_taxes.sale_id 
						INNER JOIN '. $this->db->dbprefix('sales_items').' AS sales_items ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line 
						WHERE sales.sale_status = 0 
						GROUP BY sale_id, item_id, line
					) sales_items_taxes ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line
				WHERE sales.sale_status = 0
				GROUP BY sales.sale_id
				ORDER BY sales.sale_time ASC 
			) b ON b.sale_id = a.sale_id AND (a.sale_payment_amount - b.change_returned = b.sale_total)
		';
	}

	/*
	 * function which returns query for getting sale IDs for receivables sales
	 */
	private function _get_receivables_sales_ids($start_date, $end_date)
	{
		
		// Pick up only non-suspended records
		$where = 'sales.sale_status = 0 AND sales.sale_type = 0 AND ';
		//$where2 = '';

		if(empty($this->config->item('date_or_time_format')))
		{
			//$where .= 'DATE(SALES.sale_time) BETWEEN ' . $this->db->escape($start_date) . ' AND ' . $this->db->escape($end_date);
			$where .= 'DATE(SALES.sale_time) <= ' . $this->db->escape($end_date);
		}
		else
		{
			//$where .= 'SALES.sale_time BETWEEN ' . $this->db->escape(rawurldecode($start_date)) . ' AND ' . $this->db->escape(rawurldecode($end_date));
			$where .= 'SALES.sale_time <= ' . $this->db->escape($end_date);
		}
		
		$paid_sales_ids = $this->_get_paid_sales_ids_to_date($start_date, $end_date);
		return '
			SELECT DISTINCT REC.sale_id
			FROM (
				SELECT SALES.sale_id
				FROM '. $this->db->dbprefix('sales').' AS SALES
				INNER JOIN '. $this->db->dbprefix('sales_receivables').' AS RECEIVABLES
				ON SALES.sale_id = RECEIVABLES.sale_id
				WHERE '. $where. ')
			AS REC
			LEFT JOIN 
				(' . $paid_sales_ids . ')
			AS PAID
			ON REC.sale_id = PAID.sale_id 
			WHERE PAID.sale_id IS NULL';
	}

	/**
	 * Private interface implementing the core basic functionality for all reports
	 */

	private function _common_select(array $inputs)
	{
		$where = '';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		$decimals = totals_decimals();

		$sale_price = 'sales_items.item_unit_price * sales_items.quantity_purchased - ROUND((sales_items.item_unit_price * sales_items.quantity_purchased * sales_items.discount_percent / 100), ' . $decimals . ')';
		$sale_cost = 'SUM(sales_items.item_cost_price * sales_items.quantity_purchased)';
		$tax = 'IFNULL(SUM(sales_items_taxes.tax), 0)';

		if($this->config->item('tax_included'))
		{
			$sale_total = 'SUM(' . $sale_price . ')';
			$sale_subtotal = $sale_total . ' - ' . $tax;
		}
		else
		{
			$sale_subtotal = 'SUM(' . $sale_price . ')';
			$sale_total = $sale_subtotal . ' + ' . $tax;
		}

		// create a temporary table to contain all the sum of taxes per sale item
		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('sales_items_taxes_temp') .
			' (INDEX(sale_id), INDEX(item_id))
			(
				SELECT sales_items_taxes.sale_id AS sale_id,
					sales_items_taxes.item_id AS item_id,
					sales_items_taxes.line AS line,
					SUM(sales_items_taxes.item_tax_amount) AS tax
				FROM ' . $this->db->dbprefix('sales_items_taxes') . ' AS sales_items_taxes
				INNER JOIN ' . $this->db->dbprefix('sales') . ' AS sales
					ON sales.sale_id = sales_items_taxes.sale_id
				INNER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items
					ON sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.line = sales_items_taxes.line
				WHERE ' . $where . '
				GROUP BY sale_id, item_id, line
			)'
		);

		$this->db->select("
				IFNULL(IFNULL($sale_subtotal, $sale_total), 0) AS subtotal,
				$tax AS tax,
				IFNULL(IFNULL($sale_total, $sale_subtotal), 0) AS total,
				IFNULL($sale_cost, 0) AS cost,
				IFNULL(IFNULL($sale_subtotal, $sale_total), 0) - IFNULL($sale_cost, 0) AS profit
		");
	}

	private function _common_from()
	{
		$this->db->from('sales_items AS sales_items');
		$this->db->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
		$this->db->join('sales_items_taxes_temp AS sales_items_taxes',
			'sales_items.sale_id = sales_items_taxes.sale_id AND sales_items.item_id = sales_items_taxes.item_id AND sales_items.line = sales_items_taxes.line',
			'left outer');
	}

	private function _common_where(array $inputs)
	{
		$paid_sales = NULL;
		
		if($inputs['sale_type'] == 'paid')
		{
			$sale_ids = $this->_get_paid_sales_ids($inputs['start_date'], $inputs['end_date']); 
			
			$this->db->where('sales.sale_id IN ('.$sale_ids.')');
		}
		else if ($inputs['sale_type'] == 'due')
		{
			$sale_ids = $this->_get_receivables_sales_ids($inputs['start_date'], $inputs['end_date']); 
			
			$this->db->where('sales.sale_id IN ('.$sale_ids.')');
		}
		else 
		{
			if(empty($this->config->item('date_or_time_format')))
			{
				$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
			}
			else
			{
				$this->db->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
			}
		}

		if($inputs['location_id'] != 'all')
		{
			$this->db->where('sales_items.item_location', $inputs['location_id']);
		}

		if($inputs['sale_type'] == 'complete')
		{
			$this->db->where('sales.sale_status', COMPLETED);
			$this->db->group_start();
				$this->db->where('sales.sale_type', SALE_TYPE_POS);
				$this->db->or_where('sales.sale_type', SALE_TYPE_INVOICE);
				$this->db->or_where('sales.sale_type', SALE_TYPE_RETURN);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'sales' || $inputs['sale_type'] == 'paid' || $inputs['sale_type'] == 'due')
		{
			$this->db->where('sales.sale_status', COMPLETED);
			$this->db->group_start();
				$this->db->where('sales.sale_type', SALE_TYPE_POS);
				$this->db->or_where('sales.sale_type', SALE_TYPE_INVOICE);
			$this->db->group_end();
		}
		elseif($inputs['sale_type'] == 'quotes')
		{
			$this->db->where('sales.sale_status', SUSPENDED);
			$this->db->where('sales.sale_type', SALE_TYPE_QUOTE);
		}
		elseif($inputs['sale_type'] == 'work_orders')
		{
			$this->db->where('sales.sale_status', SUSPENDED);
			$this->db->where('sales.sale_type', SALE_TYPE_WORK_ORDER);
		}
		elseif($inputs['sale_type'] == 'canceled')
		{
			$this->db->where('sales.sale_status', CANCELED);
		}
		elseif($inputs['sale_type'] == 'returns')
		{
			$this->db->where('sales.sale_status', COMPLETED);
			$this->db->where('sales.sale_type', SALE_TYPE_RETURN);
		}
	}

	/**
	 * Protected class interface implemented by derived classes if necessary
	 */

	abstract protected function _get_data_columns();

	protected function _select(array $inputs)	{ $this->_common_select($inputs); }
	protected function _from()					{ $this->_common_from(); }
	protected function _where(array $inputs)	{ $this->_common_where($inputs); }
	protected function _group_order()			{}

	/**
	 * Public interface implementing the base abstract class, in general it should not be extended unless there is a valid reason like a non sale report (e.g. expenses)
	*/

	public function getDataColumns()
	{
		return $this->_get_data_columns();
	}

	public function getData(array $inputs)
	{
		$this->_select($inputs);

		$this->_from();

		$this->_where($inputs);

		$this->_group_order();

		return $this->db->get()->result_array();
	}

	public function getSummaryData(array $inputs)
	{
		$this->_common_select($inputs);

		$this->_common_from();

		$this->_where($inputs);

		return $this->db->get()->row_array();
	}
}
?>
