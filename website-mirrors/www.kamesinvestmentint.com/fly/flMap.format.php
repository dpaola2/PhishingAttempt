<?php

class flMap extends reefless
{
	function flMap()
	{
		global $reefless;
		$reefless -> loadClass('Debug');
		$reefless -> loadClass('Actions');
		$reefless -> loadClass('Cache');
		$reefless -> loadClass('Resize');
	}

	var $statistics = array();
	var $listing_base_data = array();
	var $data_formats_mapping = array();
	var $fields_mapping = array();
	var $fields_mapping_rev = array();
	var $listing_fields_mapping = array();
	var $xpath = false;
	var $cdata_fields = array();
	var $import_status = false; //imported, mapping, fail
	var $adata = false;
	var $mapping_system_use_database = true;//less memory usage but more database usage
	var $cats_stack = array();
	var $cats_stack_mapping = array();	
	var $defaults = array();
	var $categories_mapping_fail = false;
	var $categories_mapping_checked = false;
	var $tmp_defaults = false;
	var $firstrun = true;


	function import( $feed )
	{
		error_reporting(E_ALL);
		$this -> import_status = 'fail';

		global $rlXmlImport, $rlActions, $rlCache, $lang, $fields_mapping;	
		
		set_time_limit(0);

		if( !defined('AJAX_MODE') || defined('AJAX_XML_START') )
		{
			$rlXmlImport -> xmlLogger($lang['xf_progress_start'], "notice");
		}

		if( defined('AJAX_MODE') )
		{
			$this -> mapping_system_use_database = true;
		}
		
		$this -> fields_mapping = $rlXmlImport -> getMapping( $feed['Format'], 'import' );		
		$this -> fields_mapping_rev = $rlXmlImport -> mapping_rev[ $feed['Format'] ];		

		foreach( $this -> fields_mapping['fields_info'] as $field_key => $field )
		{
			if( !in_array($field['Key'], array('Category_ID', 'posted_by') ) )
			{
				if( ($field['Type'] == 'select' || $field['Type'] == 'mixed') && $field['Condition'] )
				{
					$dfs[] = $field['Condition'];
				}
				elseif( in_array($field['Type'], array('select', 'radio', 'checkbox') ) )
				{
					$lfs[] = $field['Key'];
				}
			}
		}
		$dfs[] = 'currency';		

		$this -> data_formats_mapping = $rlXmlImport -> getDfMap( $dfs );
		if( !$this -> mapping_system_use_database )
		{
			$this -> categories_mapping = $rlXmlImport -> getCategoriesMap( 0, $feed['Listing_type'] );
		}
		$this -> listing_fields_mapping = $rlXmlImport -> getFieldsMap( $lfs );

		$plan_info = $this -> fetch("*", array( "ID" => $feed['Plan_ID'] ), null, null, 'listing_plans', 'row');

		$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_remote` LIKE '%/%' AND `Data_remote` NOT LIKE '% %' AND `Format` = '".$feed['Format']."'";
		$additions = $this -> getAll($sql);

		if( $additions )
		{
			foreach( $additions as $pkey => $addition )
			{
				$reader = new XMLReader();
				$reader -> open( $this -> xml_file );

				$xpath = explode( "/", strtolower($addition['Data_remote']) );

				while( $reader -> read() )
				{					
					foreach( $xpath as $pkey => $path )
					{
						if( $reader -> nodeType == XMLReader::ELEMENT && strtolower($reader->localName) == $path )
						{
							if( $pkey == count($xpath) - 1 )//last node
							{
								$this -> adata[$addition['Data_remote']] = $reader -> readString();
								break 2;
							}
						}
					}
				}
			}
		}

		$modules_base = RL_PLUGINS."xmlFeeds".RL_DS."modules".RL_DS;
		$add_module = $modules_base.$feed['Format'].".php";

		$skip_default_xml_handling = false;

		if( is_readable($add_module) )
		{
			include( $add_module );
		}

		if( defined('AJAX_MODE') )
		{
			$limit = 5;
			$start = $_SESSION['xmlFeedsImport']['start'] ? $_SESSION['xmlFeedsImport']['start'] : 0;
		}
		$iteration = 0;

		$read_fail = true;
		if( !$skip_default_xml_handling )
		{
			$reader = new XMLReader();
			$reader -> open( $this -> xml_file );
			
			$xpath = explode( "/", strtolower($this -> xpath) );
			
			while( $reader -> read() )
			{
				if( defined('AJAX_MODE') )
				{
					$ajax_end = true;
				}

				$read_fail = false;
				foreach( $xpath as $pkey => $path )
				{
					if( $reader -> nodeType == XMLReader::ELEMENT && strtolower($reader->localName) == $path )
					{
						$xpath_correct = true;
						if( defined('AJAX_MODE') )
						{
							$ajax_end = false;
							$_SESSION['xmlFeedsImport']['xpath_correct'] = true;
						}

						$iteration++;
						if( $start && $iteration < $start )
						{
							continue;
						}						

						if( $pkey == count($xpath) - 1 )//last node
						{
							$node = $reader -> expand(); //convert xmlReader object to the DOM
							$dom  = new DomDocument();
							$dom -> appendChild($dom->importNode($node, true));
							$sxl = simplexml_import_dom($dom);//import created DOM object with account data to the simpleXml

							$arr = $rlXmlImport -> toArray( $sxl );
							//$arr = $rlXmlImport -> toArray( $sxl, false, true);//json method
							$out = $rlXmlImport -> extractSubNodes( $arr );

							$this -> importListing( $out, $feed );

							$this -> firstrun = false;
							unset($node, $dom, $sxl);
						}						
					}
				}				

				if( $limit && $iteration >= ($start + $limit) )
				{
					$_SESSION['xmlFeedsImport']['start'] = $iteration;
					break;
				}
			}
		}		

		if( !defined('AJAX_MODE') || $ajax_end )
		{
			if( $ajax_end )
			{
				define('AJAX_XML_END', true);
				unset($_SESSION['xmlFeedsImport']);
			}
		
			if( $read_fail )
			{
				$rlXmlImport -> xmlLogger($lang['xf_progress_file_fail'], "error");
			}
			elseif( !$xpath_correct && !defined('AJAX_MODE') )
			{
				$rlXmlImport -> xmlLogger($lang['xf_progress_xpath_fail'], "error");
			}
			elseif( defined('AJAX_MODE') && !$_SESSION['xmlFeedsImport']['xpath_correct'] )
			{
				$rlXmlImport -> xmlLogger($lang['xf_progress_xpath_fail'], "error");
			}

			$rlXmlImport -> recountCategories();
			$rlXmlImport -> xmlLogger($lang['xf_progress_'.$this -> import_status], "notice");
		}
	}

	private function importListing( $listing, $feed )
	{
		global $rlXmlImport, $rlActions, $rlValid, $lang, $config;
		
		$rlXmlImport -> xmlLogger( "<hr />" );

		$this -> import_status = 'fail';
		$data = $this -> listing_base_data;
		unset($data['Category_ID'], $pictures);

		if( $GLOBALS['rlRef'] )
		{			
			$data['ref_number'] = $GLOBALS['rlRef'] -> generate(false, $config['ref_tpl']);			
		}
		
		if( !$listing )
		{
			$rlXmlImport -> xmlLogger( $lang['xf_progress_no_data'], "error" );
		}
		
		$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Default` != '' AND `Format` = '".$feed['Format']."'";
		$tmp_defaults = $this -> firstrun ? $this -> tmp_defaults = $this -> getAll($sql) : $this -> tmp_defaults;

		foreach( $tmp_defaults as $k => $v )
		{
			$listing[$v['Data_local']] = $v['Default'];
			$defaults[ $v['Data_local'] ] = $v['Default'];
		}

		foreach( $this -> adata as $k => $v )
		{
			$listing[$k] = $v;
		}

		ksort($listing);
		foreach( $listing as $xml_field => $xml_value )
		{
			if( $flynax_field = $this -> fields_mapping['mapping'][$xml_field] )
			{			
				if( $field_info = $this -> fields_mapping['fields_info'][$flynax_field] )
				{
					if( $listing[$xml_field] )
					{
						switch ( $field_info['Type'] )
						{
							case "select":							
									if( $field_info['Condition'] == 'years' )
									{
										$data[$flynax_field] = $listing[$xml_field];
									}
								elseif( $this -> data_formats_mapping[ $field_info['Condition'] ][ strtolower($listing[$xml_field]) ] && $field_info['Condition'] )
									{
										$data[$flynax_field] = $this -> data_formats_mapping[ $field_info['Condition'] ][ strtolower($listing[$xml_field]) ];
									}
								elseif( $this -> listing_fields_mapping[$field_info['Key']][ strtolower($listing[$xml_field]) ] && !$field_info['Condition'] )
								{
									$data[$flynax_field] = $this -> listing_fields_mapping[$field_info['Key']][ strtolower($listing[$xml_field]) ];									
								}
									elseif( $listing[$xml_field] )
									{
										if( is_numeric(strpos($field_info['Key'], "level")) )
										{
											preg_match('/(.*)_level([0-9])/', $flynax_field, $match);

											$top_field = $match[1];
											$mf_level = $match[2];
											$prev_field = $mf_level > 1 ? $top_field."_level".$mf_level : $top_field;
											
											$sql ="SELECT `T1`.`Key` FROM `".RL_DBPREFIX."data_formats` AS `T1` ";
											$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('data_formats+name+', `T1`.`Key`) ";
											$sql .="WHERE `T2`.`Value` = '{$listing[$xml_field]}' ";

											if( $data[$prev_field] )
											{
												$sql .="AND `T1`.`Key` LIKE '{$data[$prev_field]}%'";
											}
											$item = $this -> getRow($sql); //trying to find xml value in the data entries											
											
											if( $item )
											{
												$data[$flynax_field] = $item['Key'];
											}
											else
											{												
												$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_local` = '{$top_field}' AND `Format` = '{$feed['Format']}' ";
												$top_field_mapping = $this -> getRow($sql);
												
												if( !$top_field_mapping )
												{
													$top_field_mapping['Parent_ID'] = 0;
													$top_field_mapping['Data_remote'] = '';
													$top_field_mapping['Data_local'] = $top_field;

													if( $rlActions -> insertOne($top_field_mapping, "xml_mapping") )
													{
														$rlXmlImport -> xmlLogger(str_replace('{xml_field}', $xml_field, $lang['xf_progress_map_field_added']), "notice");
														$this -> fields_mapping['mapping'][$xml_field] = "";
													}
												}

												$parent_id = $top_field_mapping['ID'];
												for( $i=0; $i<=$mf_level; $i++ )
												{
													$check_field = $i > 0 ? $top_field."_level".$i : $top_field;

													if( $data[$check_field] )
													{
														//go deeper in mapping
														$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_local` = '".$data[$check_field]."' ";
														$sql .="AND `Format` = '{$feed['Format']}' AND `Parent_ID` = '{$parent_id}' ";
														$current_mf_mapping = $this -> getRow( $sql );

														if( !$current_mf_mapping )
														{															
															$current_mf_mapping['Data_local'] = $data[$check_field];
															$current_mf_mapping['Data_remote'] = $listing[ $this -> fields_mapping_rev[$check_field] ];

															$current_mf_mapping['Format'] = $feed['Format'];
															$current_mf_mapping['Parent_ID'] = $parent_id;

															$rlActions -> insertOne($current_mf_mapping, "xml_mapping");
															$current_mf_mapping['ID'] = mysql_insert_id();
															$rlXmlImport -> xmlLogger( str_replace($find, $replace, $lang['xf_progress_map_item_added']), "notice");
														}
														$parent_id = $current_mf_mapping['ID'];
													}
													else
													{
														$current_mf_mapping = array();
														$current_mf_mapping['Data_local'] = "";
														$current_mf_mapping['Data_remote'] = $listing[ $this -> fields_mapping_rev[$check_field] ];
														$current_mf_mapping['Format'] = $feed['Format'];
														$current_mf_mapping['Parent_ID'] = $parent_id;

														$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE ";
														foreach( $current_mf_mapping as $k => $v )
														{
															if( $k != 'Data_local')	
															{
																$sql .="`{$k}` = '{$v}' AND ";
															}
														}
														$sql = substr($sql, 0, -4);
														$existing_mf_mapping = $this -> getRow( $sql );

														if( $existing_mf_mapping )
														{
															$current_mf_mapping = $existing_mf_mapping;
															$parent_id = $existing_mf_mapping['ID'];

															if( !$existing_mf_mapping['Data_local'] )
															{
																$find = array('{xml_value}', '{xml_field}', '{xml_parent}');
																$replace = array($listing[$this -> fields_mapping_rev[$check_field]], $xml_field, $listing[ $this -> fields_mapping_rev[$prev_field] ]);

																$rlXmlImport -> xmlLogger( str_replace($find, $replace, $lang['xf_progress_map_item_not_mapped_mf']), "notice");
															}
															else
															{
																$data[$check_field] = $existing_mf_mapping['Data_local'];
															}
														}
														else
														{
														$rlActions -> insertOne($current_mf_mapping, "xml_mapping");
														$parent_id = mysql_insert_id();
															
															$find = array('{xml_value}', '{xml_field}', '{xml_prev_field}');
															$replace = array($listing[$this -> fields_mapping_rev[$check_field]], $xml_field, $listing[ $this -> fields_mapping_rev[$prev_field] ]);

															$rlXmlImport -> xmlLogger( str_replace($find, $replace, $lang['xf_progress_map_item_added_mf']), "notice");
														}														
													}
												}
											}
										}
										else
										{
											$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_local` = '{$flynax_field}' AND `Format` = '{$feed['Format']}' ";
											$ex_mapping = $this -> getRow($sql);

											if( $ex_mapping )
											{
												$ex_item_mapping = $this -> fetch("*", array('Parent_ID' => $ex_mapping['ID'], 'Format' => $feed['Format']), null, null, "xml_mapping", "row");

												if( !$ex_item_mapping )
												{
													$field_map_insert['Parent_ID'] = $ex_mapping['ID'];
													$field_map_insert['Data_remote'] = $listing[$xml_field];
													$field_map_insert['Format'] = $feed['Format'];

													if( $rlActions -> insertOne($field_map_insert, "xml_mapping") )
													{								
														$find = array('{xml_value}', '{xml_field}');
														$replace = array($listing[$xml_field], $xml_field);

														$rlXmlImport -> xmlLogger( str_replace($find, $replace, $lang['xf_progress_map_item_added']), "notice");
													}
												}
												elseif( $ex_item_mapping['Data_local'] )
												{
													$data[$flynax_field] = $ex_item_mapping['Data_local'];
												}
												elseif( !$ex_item_mapping['Data_local'] )
												{
													$find = array('{xml_value}', '{xml_field}');
													$replace = array($listing[$this -> fields_mapping_rev[$flynax_field]], $this -> fields_mapping_rev[$flynax_field]);

													$rlXmlImport -> xmlLogger( str_replace($find, $replace, $lang['xf_progress_map_item_not_mapped']), "notice");
												}
											}
										}
									}
								break;
							case "checkbox":
							case "radio":
								$data[$flynax_field] = $this -> listing_fields_mapping[$flynax_field][strtolower($listing[$xml_field])];
								break;
							case "price":
								$price_field = $flynax_field;
							case "mixed":
								$mixed_fields[$flynax_field] = $field_info['Condition'];								
							default:
								if( $data[$flynax_field] && !$defaults[$flynax_field] )
								{
									if( $flynax_field == 'additional_information' && in_array( strtolower( $listing[$xml_field] ), array("true", "false")) && $field_info['Type'] == "textarea" )
									{
										preg_match('/item_(.*?)$/i', $xml_field, $match);

										if( $match[1] )
										{
											$listing[$xml_field] = $match[1];
										}
									}
									$data[$flynax_field] .= ", ".$listing[$xml_field];
								}
								else
								{
									$data[$flynax_field] = $listing[$xml_field];
								}
							break;
						}
					}
				}else //system field
				{
					if( $flynax_field == 'currency' )
					{
						$currency = $this -> data_formats_mapping['currency'][ $listing[$xml_field] ];
					}
					elseif( $flynax_field == 'pictures' )
					{
						if( $rlValid -> isUrl($listing[$xml_field]) )
						{
							$pictures[] = $listing[$xml_field];
						}else
						{
							$pictures = $listing[$xml_field];
						}
					}
					elseif( strpos($flynax_field, 'category') !== false )
					{
						preg_match('/category_(\d)/', $flynax_field, $match);
						$cat_fields[$match[1]] = $xml_field;
					}
					elseif( $flynax_field == 'reference_id' )
					{
						$data['xml_ref'] = $listing[$xml_field];
					}
				}		
			}
			elseif( !isset($this -> fields_mapping['mapping'][$xml_field]) )
			{
				$this -> import_status = 'mapping';

				$mapping_insert['Data_remote'] = $xml_field;
				$mapping_insert['Example_value'] = $xml_value;
				$mapping_insert['Parent_ID'] = 0;
				$mapping_insert['Format'] = $feed['Format'];
				$mapping_insert['Status'] = 'active';

				$sql ="SELECT `Key` FROM `".RL_DBPREFIX."lang_keys` WHERE LOWER(`Value`) = '".$xml_field."'";
				$local_field_lk = $this -> getRow($sql, "Key");
				if( $local_field_lk )
				{
					$mapping_insert['Data_local'] = str_replace('listings_fields+name_', $local_field_lk);
				}

				if( $rlActions -> insertOne($mapping_insert, "xml_mapping") )
				{
					$rlXmlImport -> xmlLogger(str_replace('{xml_field}', $xml_field, $lang['xf_progress_map_field_added']), "notice");
					$this -> fields_mapping['mapping'][$xml_field] = $mapping_insert['Data_local'] ? $mapping_insert['Data_local'] : "";					
				}				
			}
		}

		$pictures = $rlXmlImport -> extractPictures( $pictures );
		
		$data = array_filter($data);

		if( !$currency )
		{
			$currency = $this -> data_formats_mapping['currency'][ $listing[$xml_field] ];
		}

		if( !$currency && is_array($this -> data_formats_mapping['currency']) )
		{
			$currency = current($this -> data_formats_mapping['currency']);
		}

		if( $price_field && $currency && $data[$price_field])
		{
			if( !is_float($data[$price_field]) )
			{				
				$data[$price_field] = (float) preg_replace('/[a-z,]*/i', '', $data[$price_field]);
			}			

			$data[$price_field] .="|".$currency;
		}
		
		foreach( $mixed_fields as $k => $v )
		{
			if( $data[$k] )
			{
				$df = current($this -> data_formats_mapping[$v]);
				$data[$k] .="|".$df;
			}
		}
		
		if( $this -> mapping_system_use_database )
		{
			/*categories detection system - xmlFeeds 2.1.0 */
			if( $cat_fields )
			{
				if( $this -> firstrun )
				{
					ksort($cat_fields);
					for( $i=0;$i<=max(array_keys($cat_fields));$i++ )
					{
						$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_local` = 'category_{$i}' AND `Format` = '{$feed['Format']}' ";
						$cat_mapping_field = $this -> getRow($sql);
						if( !$cat_mapping_field )
						{
							$this -> categories_mapping_fail = true;							
						}
					}					
				}

				if( $this -> categories_mapping_fail )
				{
					$rlXmlImport -> xmlLogger( str_replace("{level}", $i, $lang['xf_progress_category_mapping_missed']), "notice");
				}
				else
				{
					$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_local` = 'category_0' AND `Format` = '{$feed['Format']}' ";
					$cat_mapping_field = $this -> cat_mapping_field ? $this -> cat_mapping_field : $this -> cat_mapping_field = $this -> getRow($sql);
				
					$parents = array();
					
					foreach( $cat_fields as $ckey => $cat_field )
					{
						$sql ="SELECT `T1`.`ID`, `T2`.`Value`, `T1`.`Key` FROM `".RL_DBPREFIX."categories` AS `T1` ";
						$sql .="LEFT JOIN `".RL_DBPREFIX."lang_keys` AS `T2` ON `T2`.`Key` = CONCAT('categories+name+', `T1`.`Key`) ";
						$sql .="WHERE `T2`.`Value` = '".$listing[$cat_field]."' ";
						if( $category )
						{
							$sql .="AND `T1`.`Parent_ID` = ".$category['ID'];
						}
						$category = $this -> cats_stack[$listing[$cat_field]] ? $this -> cats_stack[$listing[$cat_field]] : $this -> cats_stack[$listing[$cat_field]] = $this -> getRow($sql);

						if( !$category )
						{
							$parent_id = $cat_mapping_field['ID'];

							foreach( $parents as $pk => $parent )
							{
								$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_remote` = '".$parent['Value']."' ";
								$sql .="AND `Format` = '{$feed['Format']}' AND `Parent_ID` = ".$parent_id;
								$parent_cat_mapping_item = $this -> cats_stack_mapping[$parent['Value']."|".$parent_id] ? $this -> cats_stack_mapping[$parent['Value']."|".$parent_id] : $this -> cats_stack_mapping[$parent['Value']."|".$parent_id] = $this -> getRow($sql);								

								if( !$parent_cat_mapping_item )
								{
									$parent_cat_mapping_item['Parent_ID'] = $parent_id;
									$parent_cat_mapping_item['Data_remote'] = $parent['Value'];
									$parent_cat_mapping_item['Data_local'] = $parent['Key'];
									$parent_cat_mapping_item['Format'] = $feed['Format'];

									$rlActions -> insertOne($parent_cat_mapping_item, "xml_mapping");
									$parent_cat_mapping_item['ID'] = mysql_insert_id();
								}
								$parent_id = $parent_cat_mapping_item['ID'];
							}

							if( $parent_cat_mapping_item['Data_remote'] != $listing[$cat_fields[$ckey-1]] )
							{								
								$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_remote` = '".$listing[$cat_fields[$ckey-1]]."' ";
								$sql .="AND `Format` = '{$feed['Format']}' AND `Parent_ID` = ".$parent_id;								
								$parent_cat_mapping_item = $this -> cats_stack_mapping[$listing[$cat_fields[$ckey-1]]."|".$parent_id] ? $this -> cats_stack_mapping[$listing[$cat_fields[$ckey-1]]."|".$parent_id] : $this -> cats_stack_mapping[$listing[$cat_fields[$ckey-1]]."|".$parent_id] = $this -> getRow($sql);

								$parent_id = $parent_cat_mapping_item['ID'];
							}

							$sql ="SELECT `ID`, `Data_local` FROM `".RL_DBPREFIX."xml_mapping` WHERE ";
							$sql .="`Parent_ID` = '".$parent_id."' ";
							$sql .="AND `Data_remote` = '".$listing[$cat_field]."' ";
							$sql .="AND `Format` = '".$feed['Format']."' ";
							
							$ex_mapping_item = $this -> getRow( $sql );
							$ex_mapping_item = $this -> cats_stack_mapping[$listing[$cat_field]."|".$parent_id] ? $this -> cats_stack_mapping[$listing[$cat_field]."|".$parent_id] : $this -> cats_stack_mapping[$listing[$cat_field]."|".$parent_id] = $this -> getRow( $sql );
				
							if( !$ex_mapping_item )
							{
								$find = array('{listing_cat_data}', '{ckey}');
								$replace = array($listing[$cat_field], $ckey);
								$rlXmlImport -> xmlLogger( str_replace($find, $replace, $lang['xf_progress_category_not_found']), 'notice');

								$cat_map_insert['Parent_ID'] = $parent_id;
								$cat_map_insert['Data_remote'] = $listing[$cat_field];
								$cat_map_insert['Format'] = $feed['Format'];

								if( $rlActions -> insertOne($cat_map_insert, "xml_mapping") )
								{
									$rlXmlImport -> xmlLogger(str_replace($find, $replace, $lang['xf_progress_category_item_added']), "notice");
								}
							}
							elseif( $ex_mapping_item['Data_local'] )
							{
								$data['Category_ID'] = $this -> getOne("ID", "`Key` = '".$ex_mapping_item['Data_local']."'", "categories");
							}
							else
							{
								$p_out = $lang['categories']." > ";
								foreach( $parents as $key => $parent )
								{
									$p_out .= $parent['Value'] . " > ";
								}
								$p_out = substr($p_out, 0, -2);

								$find = array('{xml_field}', '{xml_value}');
								$replace = array($p_out, $listing[$cat_field]);
								
								$rlXmlImport -> xmlLogger(str_replace($find, $replace, $lang['xf_progress_map_item_not_mapped']), "notice");
							}
						}
						else
						{
							$data['Category_ID'] = $category['ID'];
							$parents[] = $category;
						}
					}
				}
			}
		}
		else
		{
			/* categories detection system: depending on number of category levels it goes deeper after each successful detection
			 for example 1st iteration audi -category found, $current_level_cat_mapping became subs of audi, and so on. 
			 On each level there is system to insert category automatically 
			 Or to add entry to mappingn system for admin to link with appropriate website category
			 depending on configuration
			 */

			$current_level_cat_mapping = $this -> categories_mapping;		
			
			foreach( $cat_fields as $ckey => $cat_field )
			{
				if ( $current_level_cat_mapping[ strtolower( $listing[ $cat_field ] ) ]['id'] )
				{
					$data['Category_ID'] = $current_level_cat_mapping[ strtolower( $listing[ $cat_field ] ) ]['id'];
					$current_level_cat_mapping = $current_level_cat_mapping[ strtolower( $listing[ $cat_fields[$ckey] ] ) ]['subs'];
					$found_parents[] = strtolower( $listing[ $cat_fields[$ckey] ] );
				}
				else
				{
					$find = array('{listing_cat_data}', '{ckey}');
					$replace = array($listing[$cat_field], $ckey);
					$rlXmlImport -> xmlLogger( str_replace($find, $replace, $lang['xf_progress_category_not_found']), 'notice');

					if( $config['xml_import_categories_automatically'] )
					{
						$data['Category_ID'] = $inserted_category = $rlXmlImport -> createCategory($listing[$cat_field], $data['Category_ID']);

						$rlXmlImport -> xmlLogger('Import listing: '.$listing[$cat_field].' inserted', 'notice');
						
						if( !$found_parents )
						{
							$this -> categories_mapping[strtolower($listing[$cat_field])]['id'] = $inserted_category;						
						}
						elseif( count( $found_parents ) == 1 )
						{
							$this -> categories_mapping[$found_parents[0]]['subs'][strtolower( $listing[ $cat_fields[1] ] )]['id'] = $inserted_category;
						}					
						elseif( count( $found_parents ) == 2 )
						{
							$this -> categories_mapping[$found_parents[0]]['subs'][strtolower( $listing[ $cat_fields[1] ] )]['subs'][strtolower( $listing[ $cat_fields[2] ] )]['id'] = $inserted_category;
						}				
					}
					else
					{
						$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_local` = 'category_0' AND `Format` = '{$feed['Format']}' ";
						$cat_mapping_field = $this -> getRow($sql);

						if( $ckey )
						{
							$sql ="SELECT * FROM `".RL_DBPREFIX."xml_mapping` WHERE `Data_remote` = '".$listing[$cat_fields[$ckey-1]]."' ";
							$sql .="AND `Format` = '{$feed['Format']}' AND `Parent_ID` = ".$cat_mapping_field['ID'];
							$parent_cat_mapping_item = $this -> getRow($sql);

							if( !$parent_cat_mapping_item )
							{
								$parent_cat_mapping_item['Parent_ID'] = $cat_mapping_field['ID'];
								$parent_cat_mapping_item['Data_remote'] = $listing[$cat_fields[$ckey-1]];
								//$parent_cat_mapping_item['Data_local'] = $listing[$cat_fields[$ckey-1]];
								$parent_cat_mapping_item['Format'] = $feed['Format'];

								$rlActions -> insertOne($parent_cat_mapping_item, "xml_mapping");
								$parent_cat_mapping_item['ID'] = mysql_insert_id();
							}						
							
							$sql ="SELECT `ID` FROM `".RL_DBPREFIX."xml_mapping` WHERE ";
							$sql .="`Parent_ID` = '".$parent_cat_mapping_item['ID']."' ";
							$sql .="AND `Data_remote` = '".$listing[$cat_field]."' ";
							$sql .="AND `Format` = '".$feed['Format']."' ";
							
							$ex_mapping_item = $this -> getRow( $sql );

							if( !$ex_mapping_item )
							{
								$cat_map_insert['Parent_ID'] = $parent_cat_mapping_item['ID'];
								$cat_map_insert['Data_remote'] = $listing[$cat_field];
								$cat_map_insert['Format'] = $feed['Format'];
														
								if( $rlActions -> insertOne($cat_map_insert, "xml_mapping") )
								{								
									$rlXmlImport -> xmlLogger(str_replace($find, $replace, $lang['xf_progress_category_item_added']), "notice");
								}
							}
						}
						else
						{
							$sql ="SELECT `ID` FROM `".RL_DBPREFIX."xml_mapping` WHERE ";
							$sql .="`Parent_ID` = '".$cat_mapping_field['ID']."' ";
							$sql .="AND `Data_remote` = '".$listing[$cat_field]."' ";
							$sql .="AND `Format` = '".$feed['Format']."' ";

							$ex_mapping_item = $this -> getRow( $sql );

							if( !$ex_mapping_item )
							{
								$cat_map_insert['Parent_ID'] = $cat_mapping_field['ID'];
								$cat_map_insert['Data_remote'] = $listing[$cat_field];
								$cat_map_insert['Format'] = $feed['Format'];

								if( $rlActions -> insertOne($cat_map_insert, "xml_mapping") )
								{
									$rlXmlImport -> xmlLogger(str_replace($find, $replace, $lang['xf_progress_category_item_added']), "notice");								
								}
							}
						}
					}
				}
			}
		}		

		if( !$data['Category_ID'] )
		{
			$data['Category_ID'] = $this -> listing_base_data['Category_ID'];
		}

		if( !$data['Account_ID'] )
		{
			$rlXmlImport -> xmlLogger($lang['xf_progress_no_account'], 'error');
			return false;
		}
		
		if( !$data['xml_ref'] )
		{
			$rlXmlImport -> xmlLogger($lang['xf_progress_no_ref'], 'error');
			return false;
		}

		unset($data['account_type']);

		$data = $rlValid -> xSql($data);
		$data['Account_ID'] = (int)$data['Account_ID'];		
		
		$this -> import_status = 'imported';

		if( $data['xml_ref'] && $listing_id = $this -> getOne("ID", "`xml_ref` = '{$data['xml_ref']}' AND `xml_feed_key` = '{$data['xml_feed_key']}'", 'listings') )
		{
			$update['fields'] = $data;
			$update['where']['ID'] = $listing_id;

			$rlActions -> updateOne( $update, 'listings' );

			if( $pictures )
			{
				$rlXmlImport -> copyPictures( $pictures, $listing_id, 'update' );
			}
			
			$this -> statistics['updated'] .= $listing_id.",";

			$rlXmlImport -> xmlLogger( str_replace('[id]', "<b>".$listing_id."</b>",  $lang['xf_progress_ad_updated']), "notice");
		}
		else
		{
			$rlActions -> insertOne( $data, 'listings' );

			$listing_id = mysql_insert_id();

			if( $pictures )
			{
				$rlXmlImport -> copyPictures( $pictures, $listing_id );
			}

			$this -> statistics['inserted'] .= $listing_id.",";

			$rlXmlImport -> xmlLogger( str_replace('[id]', "<b>".$listing_id."</b>",  $lang['xf_progress_ad_inserted']), "notice");			
		}
	}

	public function export( $fp, $where, $order, $total_limit, $type )
	{
		global $rlXmlImport;

		if( !$this -> fields_mapping['mapping'] )
		{
			echo 'Error: no mapping configured';
			exit;
		}		

		foreach( $this -> fields_mapping['fields_info'] as $field_key => $field )
		{
			if( ($field['Type'] == 'select' || $field['Type'] == 'mixed') && $field['Condition'] )
			{
				$dfs[] = $field['Condition'];
			}elseif( in_array($field['Type'], array('select', 'radio', 'checkbox') ) )
			{
				$lfs[] = $field['Key'];
			}
		}
		
		$modules_base = RL_PLUGINS."xmlFeeds".RL_DS."modules".RL_DS;
		$add_module = $modules_base.$GLOBALS['format']."_export.php";
		
		$skip_default_xml_handling = false;		
		
		if( is_readable($add_module) )
		{
			include( $add_module );
		}

		if( !$skip_default_xml_handling )
		{
			$this -> data_formats_mapping = $rlXmlImport -> getDfMap( $dfs, 'export' );
			$this -> listing_fields_mapping = $rlXmlImport -> getFieldsMap( $lfs, 'export');

			$xml = '<?xml version="1.0" encoding="utf-8" ?>';

			$xpath = explode("/", $this -> xpath );
			$last = current( array_splice($xpath, -1, 1));
			
			foreach( $xpath as $xk => $xp )
			{
				$xml .= '<'.$xp.'>';
			}
			fwrite( $fp, $xml );unset($xml);
			
			$limit = 10;
			while( $listings = $rlXmlImport -> getListings( $where, $order, $start, $limit, $type ) )
			{
				foreach( $listings as $key => $listing ) 
				{
					$xml = $this -> exportListing( $listing, $last );
					
					fwrite( $fp, $xml );
					
				}
				$start = $start + $limit;
				if( $start >= $total_limit )
				{
					break;
				}
			}
			
			foreach( $xpath as $xk => $xp )
			{
				$xml .= '</'.$xp.'>';
			}
			fwrite( $fp, $xml );
		}
	}
	
	private function exportListing( $listing, $last )
	{
		foreach( $this -> fields_mapping['mapping'] as $flynax_field => $xml_field )
		{
			if( $flynax_field == 'reference_id' )
			{
				$flynax_field = 'xml_ref';
			}
			elseif( $flynax_field == 'category_0' || $flynax_field == 'category_1' || $flynax_field == 'category_2' || $flynax_field == 'category_3' )
			{				
				if( !$cats_out[ $flynax_field ] )
				{					
					/* get category and all parent keys */
					$sql ="SELECT `Key`, `Level` FROM `".RL_DBPREFIX."categories` ";
					$sql .="WHERE `ID` = ".$listing['Category_ID']." OR FIND_IN_SET(`ID`, (SELECT `Parent_IDs` FROM `".RL_DBPREFIX."categories` WHERE `ID` = {$listing['Category_ID']}) )";

					$keys = $this -> getAll( $sql );
					
					$sql = "SELECT `Key`, `Value` FROM `".RL_DBPREFIX."lang_keys` WHERE ";
					foreach( $keys as $k => $v )
					{
						$levels[ $v['Key'] ] = $v['Level'];
						$sql .="`Key` = 'categories+name+".$v['Key']."' OR ";
					}
					$sql = substr($sql, 0, -3);

					$names = $this -> getAll($sql);

					foreach ($names as $key => $value) {
						$cat_key = str_replace('categories+name+', '', $value['Key']);						
						$cats_out['category_'.$levels[ $cat_key ]] = $value['Value'];
					}					
				}

				$advert[$xml_field] = $cats_out[ $flynax_field ];				
			}
			elseif( $flynax_field == 'built' )
			{
				$advert[$xml_field] = $listing[$flynax_field];
				unset( $listing[$flynax_field] );
			}
			elseif( $flynax_field == 'back_url' )
			{
				$cinfo = $this -> fetch(array("Type", "Path"), array("ID" => $listing['Category_ID']), null, null, "categories", "row");
				
				$browse_path = $this -> getOne("Path", "`Key` = 'lt_".$cinfo['Type']."'", "pages");
				$listing_title = $GLOBALS['rlListings'] -> getListingTitle( $listing['Category_ID'], $listing );

				$link = RL_URL_HOME . $browse_path . '/' . $cinfo['Path'] . '/' . $GLOBALS['rlSmarty'] -> str2path( array('string' => $listing_title) ). '-l' . $listing['ID'] . '.html';

				$advert[$xml_field] = $link;				
			}
			elseif( $flynax_field == 'pictures')
			{		
				$photos	= $this -> fetch("*", array('Listing_ID' => $listing['ID']), null, null, 'listing_photos');
				
				if( $photos )
				{
					foreach( $photos as $pk => $photo ) {
						//$picture['picture_url'] = $photo['Photo'];
						//$picture['picture_title'] = $photo['Photo'];				
						
						$advert['pictures'][]['picture'] = RL_FILES_URL.$photo['Photo'];
					} 
				}				
			}
			elseif( $flynax_field == 'pictures2')
			{		
				$photos	= $this -> fetch("*", array('Listing_ID' => $listing['ID']), null, null, 'listing_photos');
				
				if( $photos )
				{
					foreach( $photos as $pk => $photo )
					{
						$picture['picture_url'] = RL_FILES_URL.$photo['Photo'];
						$picture['picture_title'] = $photo['Description'];

						$advert[ $xml_field ][]['picture'] = $picture;
					} 
				}				
			}

			if( $listing[$flynax_field] )
			{
				switch( $this -> fields_mapping['fields_info'][$flynax_field]['Type'] )
				{
					case 'price':
						if( is_numeric( strpos($GLOBALS['format'],'trovit') ) )
						{
							$advert[$xml_field] = preg_replace("/\|.*$/", "", $listing[$flynax_field]);
						}
						else
						{
						$advert[$xml_field] = $GLOBALS['rlCommon'] -> adaptValue( $this -> fields_mapping['fields_info'][$flynax_field], $listing[$flynax_field] );
						}						
						//$advert[$xml_field]['@attributes'] = $listing[$flynax_field];
					break;
					case 'select':
						if(  $this -> fields_mapping['fields_info'][$flynax_field]['Condition'] )
						{
							if( strpos($flynax_field, "level") )
							{
								$advert[$xml_field] = $this -> getOne("Value", "`Key` ='data_formats+name+".$listing[ $flynax_field ]."'", "lang_keys");							
							}else
							{
							$advert[$xml_field] = $this -> data_formats_mapping[ $this -> fields_mapping['fields_info'][ $flynax_field ]['Condition'] ][ $listing[$flynax_field] ];	
						}
						}else/*if( $this -> listing_fields_mapping[ $flynax_field ][ $listing[$flynax_field] ] )*/
						{
							$advert[$xml_field] = $this -> listing_fields_mapping[ $flynax_field ][ $listing[$flynax_field] ];
						}
					break;
					case 'checkbox':
						$advert[$xml_field] = $GLOBALS['rlXmlImport'] -> adaptFeatures( $flynax_field, $listing[$flynax_field], ',', 'export', $this -> listing_fields_mapping[ $flynax_field ]);
						break;
					case 'mixed':
						$tval = explode("|", $listing[$flynax_field]);
						//$df = $this -> data_formats_mapping[ $this -> fields_mapping['fields_info'][ $flynax_field ]['Condition'] ][ $tval[1] ];
						$advert[$xml_field] = $tval[0];
						break;
					default:
						if( $xml_field == 'postcode' && is_numeric( strpos($GLOBALS['format'],'trovit') ) )
						{
							$advert[$xml_field] = strtoupper( substr($listing[$flynax_field], 0, 2)." ". substr($listing[$flynax_field], 2));
						}
						else
						{
						$advert[$xml_field] = $listing[$flynax_field];
						}
						break;
				}
			}

		}
		
		$data[ $last ] = $advert;

		return $GLOBALS['rlXmlImport'] -> toXML( $data );
	}
}
