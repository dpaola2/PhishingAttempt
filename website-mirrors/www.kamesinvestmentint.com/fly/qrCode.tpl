<!-- QR Code link -->

<li>
    <a href="#" class="qrCodeModal">{$lang.title_qrCode}</a> <a href="#" class="qrCodeModal"><img style="vertical-align: top;margin-top: 1px;" src="{$smarty.const.RL_PLUGINS_URL}qrCode/qrcode.png" alt="{$lang.title_qrCode}" title="{$lang.title_qrCode}"/></a>
    <script type="text/javascript">
        {literal}
        $(document).ready(function(){
            $('.qrCodeModal').click(function(e){
                e.preventDefault();
                var src = "{/literal}{$smarty.const.RL_FILES_URL}qrcode/user_{$listing_data.Account_ID}/listing_{$listing_data.ID}{literal}.png";
                var qrImg = new Image();
                qrImg.onload = function(){
                    $(this).flModal({
                        caption: '{/literal}{$lang.title_qrCode}{literal}',
                        content: '<img src="'+src+'" alt="" style="border:1px solid #000000;" />',
                        width: 'auto',
                        height: 'auto',
                        click: false
                    });
                };
                qrImg.src = src;
            });
        });
        {/literal}
    </script>
</li>

<!-- QR Code link end -->