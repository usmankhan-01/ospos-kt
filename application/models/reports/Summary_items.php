<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_items extends Summary_report
{
	protected function _get_data_columns()
	{
		$CI =& get_instance();

		$columns = array(
			array('item_name' => $this->lang->line('reports_item')),
			array('quantity' => $this->lang->line('reports_quantity')),
			array('subtotal' => $this->lang->line('reports_subtotal'), 'sorter' => 'number_sorter'),
			array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'),
			array('total' => $this->lang->line('reports_total'), 'sorter' => 'number_sorter'),
			array('cost' => $this->lang->line('reports_cost'), 'sorter' => 'number_sorter'),
			array('profit' => $this->lang->line('reports_profit'), 'sorter' => 'number_sorter'));

		//adding custom fields data in items list
		for ($i = 1; $i <= 10; ++$i)
		{
			if($CI->config->item('custom'.$i.'_name') != NULL)
			{
				array_push($columns, array('custom'.$i => $CI->config->item('custom'.$i.'_name')));
			}
		}
		return $columns;
	}

	protected function _select(array $inputs)
	{
		parent::_select($inputs);

		$this->db->select('
				MAX(items.name) AS name,
				items.custom1 AS custom1,
				items.custom2 AS custom2,
				items.custom3 AS custom3,
				items.custom4 AS custom4,
				items.custom5 AS custom5,
				items.custom6 AS custom6,
				items.custom7 AS custom7,
				items.custom8 AS custom8,
				items.custom9 AS custom9,
				items.custom1 AS custom10,
				SUM(sales_items.quantity_purchased) AS quantity_purchased,
		');
	}

	protected function _from()
	{
		parent::_from();

		$this->db->join('items AS items', 'sales_items.item_id = items.item_id', 'inner');		
	}

	protected function _group_order()
	{
		$this->db->group_by('items.item_id');
		$this->db->order_by('name');
	}
}
?>
