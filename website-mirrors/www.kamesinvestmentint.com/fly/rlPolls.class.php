<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLPOLLS.CLASS.PHP
 *
 *	The software is a commercial product delivered under single, non-exclusive, 
 *	non-transferable license for one domain or IP address. Therefore distribution, 
 *	sale or transfer of the file in whole or in part without permission of Flynax 
 *	respective owners is considered to be illegal and breach of Flynax License End 
 *	User Agreement. 
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2014 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

class rlPolls extends reefless
{
	/**
	* @var language class object
	**/
	var $rlLang;
	
	/**
	* @var validator class object
	**/
	var $rlValid;
	
	/**
	* @var content code for polls blocks
	*/
	var $content = 'global $rlSmarty;
		$polls_array = unserialize(\'{$polls_replace}\');
		$count_max = count($polls_array);
		$index = rand(0, $count_max-1);
		shuffle($polls_array);
		$poll=$polls_array[$index];
		$polls = explode(",", $_COOKIE["polls"]);
		$poll_items = $poll["items"];
		if ( in_array( $poll["ID"], $polls ) )
		{
			$poll["voted"] = true;
		}
		foreach ($poll_items as $key => $pValue)
		{
			$poll_items[$key]["percent"] = floor(((int)$poll_items[$key]["Votes"]*100)/$poll["total"]);
		}
		$poll["items"] = $poll_items;
		$rlSmarty -> assign("poll", $poll);
		$rlSmarty -> display(RL_PLUGINS."polls".RL_DS."polls.block.tpl");';

	/**
	* @var content code for empty polls blocks
	*/
	var $empty_content = 'global $rlSmarty;
		$poll = "";
		$rlSmarty -> assign("poll", $poll);
		$rlSmarty -> display(RL_PLUGINS."polls".RL_DS."polls.block.tpl");';

	
	/**
	* class constructor
	**/
	function rlPolls()
	{
		global $rlLang, $rlValid;
		
		$this -> rlLang   = & $rlLang;
		$this -> rlValid  = & $rlValid;
	}
	
	/**
	* get poll
	*
	* @param int $poll_id - poll id
	*
	* @return array - poll data
	**/
	function get( $poll_id = false)
	{
		/* get poll */
		$where = $poll_id == 'all' ? array('Random' => 1, 'Status' => 'active') : array('ID' => $poll_id, 'Status' => 'active');
		
		$poll = $this -> fetch(array('ID', 'ID` AS `Key', 'Random'), $where, null, null, 'polls');
		foreach( $poll as $key => $val )
		{
			$where_sum = $poll_id != 'all' ? "`Poll_ID` = '{$poll_id}'" : "`Poll_ID` = '{$poll[$key]['ID']}'";
			
			if ( !empty($poll[$key]) )
			{
				foreach( $GLOBALS['languages'] as $lKey => $lVal )
				{
					$poll[$key]['name'][$lKey] = str_replace("'", "&#39;", $this -> rlLang -> replaceLangKeys( 'polls+name+'.$poll[$key]['Key'], null, null, $lKey ));
				}
			
				/* get poll's items */
				$poll_items = $this -> fetch( array('ID` AS `Key', 'Votes', 'Color'), array('Poll_ID' => $poll[$key]['ID']), "ORDER BY `ID`", null, 'polls_items' );
				foreach( $poll_items as $pKey => $pVal )
				{
					foreach( $GLOBALS['languages'] as $lKey => $lVal )
					{
						$poll_items[$pKey]['name'][$lKey] = str_replace("'", "&#39;", $this -> rlLang -> replaceLangKeys( 'polls_items+name+'.$poll_items[$pKey]['Key'], null, null, $lKey ));
					}
				}
				$total_votes = $this -> getRow("SELECT SUM(`Votes`) AS `sum` FROM `" . RL_DBPREFIX . "polls_items` WHERE {$where_sum} LIMIT 1");

				foreach ($poll_items as $key2 => $pValue)
				{
					$poll_items[$key2]["percent"] = floor(((int)$poll_items[$key2]["Votes"]*100)/$poll[$key]["total"]);
				}
				
				$total_votes = $poll[$key]['total'] = $total_votes['sum'];
				$poll[$key]['items'] = $poll_items;
			}
		}
		return $poll;	
	}
	
	/**
	* vote
	*
	* @package xAjax
	*
	* @param int $poll_id - poll id
	* @param string $vote_key - vote key
	*
	**/
	function ajaxVote( $poll_id = false, $vote_key = false, $random = false, $tpl = 1 )
	{
		global $_response;

		if ( empty($vote_key) )
		{
			$_response -> script("$('#vote_button').val('{$GLOBALS['lang']['vote']}');");
			return $_response;
		}

		$poll_id = (int)$poll_id;
		$polls = explode(',', $_COOKIE['polls']);

		if ( !in_array( $poll_id, $polls ) )
		{
			$this -> query("UPDATE `" .RL_DBPREFIX . "polls_items` SET `Votes` = `Votes` + 1 WHERE `Poll_ID` = '{$poll_id}' AND `ID` = '{$vote_key}'");
			
			/* save vote in cookie */
			$polls[] = $poll_id;
			$value = implode(',', $polls);
			$expire_time = time()+2592000;

			setcookie( 'polls', $value, $expire_time, '/' );
		}
		
		$polls = serialize($this -> get( $random ? 'all' : $poll_id ));	

		$content = str_replace('{$polls_replace}', $polls, $this -> content);
		/* edit poll block */
		if( $random )
		{
			$this -> query("UPDATE `" .RL_DBPREFIX . "blocks` SET `Content` = '{$this -> rlValid -> xSql($content)}' WHERE `Key` = 'polls' LIMIT 1");
		}
		else
		{
			$this -> query("UPDATE `" .RL_DBPREFIX . "blocks` SET `Content` = '{$this -> rlValid -> xSql($content)}' WHERE `Key` = 'polls_{$poll_id}' LIMIT 1");
		}
		
		$mess = $GLOBALS['lang']['notice_vote_accepted'];
		$_response -> script( "$('#notice_obj').fadeOut('fast', function(){ $('#notice_message').html('{$mess}'); $('#notice_obj').fadeIn('slow'); $('#error_obj').fadeOut('fast');});" );
		
		$array_polls = $this -> get( $poll_id );

		$poll=$array_polls[0];
		$poll_items = $poll["items"];

		$poll["voted"] = true;
		foreach ($poll_items as $key => $pValue)
		{
			$poll_items[$key]["percent"] = floor(((int)$poll_items[$key]["Votes"]*100)/$poll["total"]);
		}

		$poll["items"] = $poll_items;
		$GLOBALS['rlSmarty'] -> assign("poll", $poll);
		
		$GLOBALS['rlSmarty'] -> assign("block", $tpl);

		$tpl = RL_PLUGINS . 'polls' . RL_DS . 'polls.block.tpl';
		$_response -> assign( 'poll_container_'.$poll_id, 'innerHTML', $GLOBALS['rlSmarty'] -> fetch( $tpl, null, null, false ) );

		$_response -> script("$('#vote_button').val('{$GLOBALS['lang']['vote']}');");
		
		return $_response;
	}
	
	/**
	* delete poll
	*
	* @package xAjax
	*
	* @param int $poll_id - poll id
	*
	**/
	function ajaxDeletePoll( $poll_id = false )
	{
		global $_response;
		$poll_id = (int)$poll_id;
		
		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$_response -> redirect( RL_URL_HOME . ADMIN . '/index.php?action=session_expired' );
			return $_response;
		}
		
		if ( !$poll_id )
		{
			return $_response;
		}
	
		$random = $this -> getOne('Random', "`ID` = {$poll_id}", 'polls');

		// delete poll
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "polls` WHERE `ID` = '{$poll_id}' LIMIT 1");
		
		// delete poll items
		$items = $this -> fetch(array('ID'), array('Poll_ID' => $poll_id), null, null, 'polls_items');
		$this -> query("DELETE FROM `" . RL_DBPREFIX . "polls_items` WHERE `Poll_ID` = '{$poll_id}'");
		
		// delete poll land's phrases
		foreach ($items as $key => $value)
		{
			$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'polls_items+name+{$items[$key]['ID']}' AND `Plugin` = 'polls' ");
		}
		if( $random )
		{
			$tmp_polls = $this -> get( 'all' );
			if( !empty($tmp_polls)	)
			{
				$polls = serialize($tmp_polls);
				$content = str_replace('{$polls_replace}', $polls, $this -> content);
			}
			else
			{
				$content = $empty_content;
			}
			/* edit poll block */
		
			$this -> query("UPDATE `" .RL_DBPREFIX . "blocks` SET `Content` = '{$this -> rlValid -> xSql($content)}' WHERE `Key` = 'polls' AND `Plugin` = 'polls' LIMIT 1");
		}
		else
		{
			$this -> query("DELETE FROM `" . RL_DBPREFIX . "blocks` WHERE `Key` = 'polls_{$poll_id}' AND `Plugin` = 'polls' LIMIT 1");
			$this -> query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'blocks+name+polls_{$poll_id}' AND `Plugin` = 'polls' ");
		}
		$_response -> script("pollsGrid.init();");
		
		$notice = $GLOBALS['lang']['item_deleted'];
		
		$_response -> assign( 'notice_block', 'innerHTML', $notice );
		$_response -> script( "$('#notice_obj').fadeIn('slow');" );
		
		return $_response;
	}
}
