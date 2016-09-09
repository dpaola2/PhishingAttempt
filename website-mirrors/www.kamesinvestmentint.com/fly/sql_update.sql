UPDATE `{sql_prefix}config` SET `Default` = '4.3.0' WHERE `Key` = 'rl_version' LIMIT 1;

INSERT INTO `{sql_prefix}config` (`Group_ID`, `Position`, `Key`, `Default`, `Values`, `Type`, `Data_type`, `Plugin`) VALUES
(5, 15, 'ld_keep_alive', '1', '', 'bool', 'int', '' ),
(5, 16, 'ld_keep_hiddenfields', 'phone,mobile', '', 'textarea', 'varchar', '' ),
(2, 12, 'account_thumb_divider', '', '', 'divider', 'varchar', ''),
(2, 13, 'account_thumb_width', '100', '', 'text', 'int', ''),
(2, 14, 'account_thumb_height', '100', '', 'text', 'varchar', '');

INSERT INTO `{sql_prefix}lang_keys` (`Code`, `Module`, `Key`, `Value`, `Plugin`, `Status`) VALUES
('en', 'admin', 'choose_listing_type', 'Choose listing type', '', 'active'),
('en', 'admin', 'config+name+ld_keep_hiddenfields', 'Fields which should be hidden on details page when listing deleted or expired', '', 'active'),
('en', 'admin', 'config+des+ld_keep_alive', 'enter field keys through comma', '', 'active'),
('en', 'admin', 'config+name+ld_keep_alive', 'Keep listing details page alive after listing expired or deleted', '', 'active'),
('en', 'admin', 'config+des+ld_keep_alive', 'you have to enable trash box to make it working for deleted listings', '', 'active'),
('en', 'frontEnd', 'ld_inactive_notice', 'The listing is either inactive or expired', '', 'active'),
('en', 'frontEnd', 'notice_account_type_approval', 'Your account type has been disabled by the Administrator', '', 'active'),
('en', 'ext', 'ext_resize_in_progress', 'Processing, please wait...', '', 'active'),
('en', 'ext', 'ext_resize_error', 'System error occurred, please contact Flynax support', '', 'active'),
('en', 'ext', 'ext_resize_completed', 'Picture dimensions updated successfully.', '', 'active'),
('en', 'admin', 'resize_images', 'Resize pictures after changing dimensions', '', 'active'),
('en', 'admin', 'resize_images_prompt', 'Picture dimensions have been changed. Would you like to resize all pictures now?', '', 'active'),
('en', 'common', 'account_fields+name+company_name', 'Company Name', '', 'active'),
('en', 'admin', 'config+name+account_thumb_divider', 'Thumbnail dimensions', '', 'active'),
('en', 'admin', 'config+name+account_thumb_width', 'Thumbnail width (in pixels)', '', 'active'),
('en', 'admin', 'config+name+account_thumb_height', 'Thumbnail height (in pixels)', '', 'active');

ALTER TABLE  `{sql_prefix}accounts` ADD `company_name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

INSERT INTO `{sql_prefix}account_fields` (`Key`, `Type`, `Default`, `Values`, `Condition`, `Multilingual`, `Details_page`, `Add_page`, `Required`, `Map`, `Opt1`, `Opt2`, `Status`, `Readonly`) VALUES 
('company_name', 'text', '', '50', '', '0', '1', '1', '0', '0', '0', 0, 'active', '1');

UPDATE `{sql_prefix}trash_box` SET `Key` = REPLACE(REPLACE(`Criterion`, "`ID` = '", ''), "'", '') WHERE `Zones` = 'tmp_categories';