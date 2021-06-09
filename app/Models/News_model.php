<?php namespace App\Models;

use CodeIgniter\Model;

class News_model extends Model {


	public function get_news() {
		$builder = $this->db->table('news');
		$builder->orderBy('top', 'DESC');
		$builder->orderBy('date', 'DESC');
		$query = $builder->get();
		return $query->getResultArray();
	}
	
	
	public function get_news_by_id($id = 0) {
		$builder = $this->db->table('news');
		$builder->where(['id' => $id]);
		$query = $builder->get();
		return $query->getRowArray();
	}
	
	
	public function set_news($news_item){
		$builder = $this->db->table('news');
		return $builder->insert($news_item);
	}
	
	
	public function update_news($news_id,$news_item) {
		$builder = $this->db->table('news');
		$builder->where('id', $news_id);
		return $builder->update($news_item);
	}
	

	public function delete_news($news_id) {
		if (!empty($news_id)) {
			$builder = $this->db->table('news');
			return $builder->delete(['id' => $news_id]); 
		}
	}
	
}