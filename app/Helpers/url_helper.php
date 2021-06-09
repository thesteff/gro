<?php
	/**
	 * Create URL Title
	 *
	 * Takes a "title" string as input and creates a
	 * human-friendly URL string with a "separator" string
	 * as the word separator.
	 *
	 * @todo	Remove old 'dash' and 'underscore' usage in 3.1+.
	 * @param	string	$str		Input string
	 * @param	string	$separator	Word separator
	 *			(usually '-' or '_')
	 * @param	bool	$lowercase	Whether to transform the output string to lowercase
	 * @return	string
	 */
	function url_title($str, $separator = '-', $lowercase = FALSE) {
	
		// On remplace les accents sans les supprimer (é->e, à->a, ...)
		$str = iconv('UTF-8','ASCII//TRANSLIT',$str);
	
		if ($separator === 'dash')
		{
			$separator = '-';
		}
		elseif ($separator === 'underscore')
		{
			$separator = '_';
		}

		$q_separator = preg_quote($separator, '#');

		$trans = array(
			'&.+?;'			=> '',
			'[^\w\d _-]'		=> '',
			'\s+'			=> $separator,
			'('.$q_separator.')+'	=> $separator
		);
		
		$str = strip_tags($str);
		foreach ($trans as $key => $val)
		{
			//$str = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $str);
			$str = preg_replace('#'.$key.'#iu', $val, $str);
		}

		if ($lowercase === TRUE)
		{
			$str = strtolower($str);
		}

		return trim(trim($str, $separator));
	}
	
	
	/**
	 * Create Dir Path
	 *
	 * idem url_title en conservant les espaces
	 * @return	string
	 */
	function dir_path($str, $separator = '_', $lowercase = FALSE) {
	
		// On remplace les accents sans les supprimer (é->e, à->a, ...)
		$str = iconv('UTF-8','ASCII//TRANSLIT',$str);
	
		if ($separator === 'dash')
		{
			$separator = '-';
		}
		elseif ($separator === 'underscore')
		{
			$separator = '_';
		}

		$q_separator = preg_quote($separator, '#');

		$trans = array(
			'&.+?;'			=> '',
			'[^\w\d _-]'		=> '',
			//'\s+'			=> $separator,
			'('.$q_separator.')+'	=> $separator
		);
		
		$str = strip_tags($str);
		foreach ($trans as $key => $val)
		{
			//$str = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $str);
			$str = preg_replace('#'.$key.'#iu', $val, $str);
		}

		if ($lowercase === TRUE)
		{
			$str = strtolower($str);
		}

		//return trim(trim($str, $separator));
		return $str;
	}