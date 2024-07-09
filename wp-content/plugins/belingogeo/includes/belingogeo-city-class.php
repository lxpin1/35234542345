<?php

class BelingoGeo_City {

	private $city_id;
	private $city_name;
	private $city_slug;
	private $city_meta;

	public function __construct($post = '') {

		if(!empty($post)) {
			$city = get_post($post, ARRAY_A);

			$this->city_id 	 = $city['ID'];
			$this->city_name = $city['post_title'];
			$this->city_slug = $city['post_name'];
			$this->city_meta = get_post_meta($city['ID']);
		}

	}

	public function get_id() {

		return $this->city_id;

	}

	public function get_name() {

		return $this->city_name;

	}

	public function get_slug() {

		return $this->city_slug;

	}

	public function get_meta() {

		return $this->city_meta;

	}

	public function set_name($name) {

		$this->city_name = $name;

	}

	public function set_slug($slug) {

		$this->city_slug = $slug;

	}

}


?>