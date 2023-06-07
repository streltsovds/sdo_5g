<!--has-avatar-->
<div style="display: flex; justify-content: space-between">
    <span  style="font-weight: 500; font-size: 20px; line-height: 24px; letter-spacing: 0.02em;color: #1E1E1E;margin: 15px 0 19px 16px;"
    ><?= _('Сообщения'); ?> </span>

    <hm-sidebar-toggle
            style="
                margin: 7px 0 0 0;
                background: none !important;
                color: #1E1E1E;
                font-size: 16px;
                border-radius: 50%;"
            sidebar-name="userevents">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.25 4.8075L13.1925 3.75L9 7.9425L4.8075 3.75L3.75 4.8075L7.9425 9L3.75 13.1925L4.8075 14.25L9 10.0575L13.1925 14.25L14.25 13.1925L10.0575 9L14.25 4.8075Z" fill="#1E1E1E"/>
        </svg>
    </hm-sidebar-toggle>
</div>
<hm-user-events url="<?php echo $this->ajaxUrl;?>"></hm-user-events>
