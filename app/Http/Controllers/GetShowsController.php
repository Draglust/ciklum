<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}


class GetShowsController extends Controller
{
	public function index()
	{
		$main_message = 'Type something to find a show, IE. /show/thrones';

		return $main_message;
	}

	public function show($search)
	{
		$data['search'] = $search;
		$data['search'] = $this->clean_results($data['search']);

		//if($data['search']!='' && !is_numeric($data['search'])):
		if($data['search']!=''):
			$base_url = "http://api.tvmaze.com/search/shows?q={$data['search']}";
			$url_content = $this->get_web($base_url);

			if($url_content['data']):
				$json_data = $this->clean_json($url_content['data'],$data['search']);
			else:
				return 'Invalid response';
			endif;

			return $json_data;

		else:
			return 'Invalid response';
		endif;
	}

	public function get_web($uri)
	{
		$start1 = microtime(true);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_REFERER, "http://www.google.com");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $uri);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$output['data'] = curl_exec($ch);
		curl_close($ch);

		$end1 = microtime(true) - $start1;
		$output['time'] = $end1;
		$output['response'] = $code ;
		return $output;
	}

	public function clean_results($string_data)
	{
		$string_to_replace = array('@','#',';','.',':','(','"','\'',')','/');
		$data_output = $string_data;
		foreach($string_to_replace as $str_to_replace):
			$data_output = str_replace($str_to_replace, '', $string_data);
		endforeach;

		return $data_output;
	}

	public function clean_json($data_json,$exact_name)
	{
		$data_json = json_decode($data_json,TRUE);
		//print_r($data_json);die();
		foreach($data_json as $json):
			$valid_array = array();
			if(strstr(strtolower($json['show']['name']), strtolower($exact_name))):
				$valid_array['score'] = $json['score'];
				$valid_array['name'] = $json['show']['name'];
			endif;

			if($valid_array):
				$data_output[] = $valid_array;
			endif;
			unset($valid_array);
		endforeach;

		if(isset($data_output)):
			return $data_output;
		else:
			return 'Invalid response';
		endif;
	}


	public function is_valid($str) 
	{
	    $ret = preg_match('/[^A-Za-z0-9.#\\-$]/', $str);
	   	
	   	return !$ret;
	}
}
