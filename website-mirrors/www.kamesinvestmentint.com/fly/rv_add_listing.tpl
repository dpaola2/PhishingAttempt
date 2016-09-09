{assign var='page_key' value='lt_'|cat:$listing_data.Listing_type}

{php}
global $listing_data, $listing_type, $rlSmarty, $rlListings;

/* get listing title */
$listing_title = $rlListings -> getListingTitle( $listing_data['Category_ID'], $listing_data, $listing_type['Key'] );
$listing_title = $rlSmarty -> str2path( $listing_title );

$rlSmarty -> assign( 'l_title' , $listing_title );
{/php}

<script type="text/javascript">
var rv_total_count = '{$config.rv_total_count}';
var storage_item_name = '{$rlBase|replace:"http://":""|replace:"htts://":""|replace:"www.":""|replace:".":"_"|replace:"/":"_"}';

{literal}
$(document).ready(function(){
	if ( isLocalStorageAvailable() ) {
		var listing_id = '{/literal}{$listing_data.ID}{literal}';
		var photo = '{/literal}{$listing_data.Main_photo}{literal}';
		var page_key = '{/literal}{$pages.$page_key}{literal}';
		var path = '{/literal}{$listing_data.Path}/{$l_title}{literal}';
		var title = '{/literal}{$pageInfo.name|escape:'javascript'|escape:'html'}{literal}';

		var listing = [listing_id, photo, page_key, path, title];

		rvAddListing( listing );
	} else {
		console.log("Error. Your browser doesn't support web storage");
	}
});
{/literal}
</script>