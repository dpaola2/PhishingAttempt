<!-- subscribe checkbox -->

<label id="mn_container_reg" style="display: block;padding: 5px 0;">
	<input value="1" type="checkbox" {if (isset($smarty.post.profile.mn_subscribe) && $smarty.post.profile.mn_subscribe) || !isset($smarty.post.profile.mn_subscribe)}checked="checked"{/if} name="profile[mn_subscribe]" /> {$lang.massmailer_newsletter_subscribe_to}
</label>

<!-- subscribe checkbox end -->