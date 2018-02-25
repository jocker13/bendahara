<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class laporan_transaksi_model extends CI_Model {
	var $table = 'transaksiumum';
	var $column_order = array('tanggal','nama_transaksi','jenis','nama_sie','banyak','harga_satuan','saldo',NULL); //set column field database for datatable orderable
	var $column_search = array('tanggal','nama_transaksi','jenis','nama_sie','banyak','harga_satuan','saldo'); //set column field database for datatable searchable just firstname , lastname , address are searchable
	var $order = array('id_tran' => 'asc');

	public function save($data)
	{
		$this->db->insert('transaksiumum',$data);
	}
/*	public function ubah($id_tran,$data)
	{
		$this->db->where('id_tran',$id_tran);
		$this->db->update('transaksiumum', $data);
	}*/
	public function getSaldoakhir()
	{
		$sql =$this->db->query("select saldo from transaksiumum where id_tran=(select max(id_tran)from transaksiumum)");
		$ret = $sql->row();
		return $ret->saldo;
	}


private function _get_datatables_query($bulan,$tahun)
	{
		$this->db->from($this->table);
		$this->db->where('month(tanggal)',$bulan);
		$this->db->where('year(tanggal)',$tahun);
		// $this->db->query("select * from transaksiumum where month(tanggal)='$bulan' and year(tanggal)='$tahun' ");
	
		
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables($bulan,$tahun)
	{
		$this->_get_datatables_query($bulan,$tahun);
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered($bulan,$tahun)
	{
		$this->_get_datatables_query($bulan,$tahun);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	public function delete_by_id($id)
	{
		$this->db->where('id_tran', $id);
		$this->db->delete($this->table);
	}
	public function get_by_id($id)
	{
		$this->db->from($this->table);
		$this->db->where('id_tran',$id);
		$query = $this->db->get();

		return $query->row();
	}
}