-- m_user.sql
-- 担当者マスター生成
-- ver 0.01
-- 2005/9/9

create table m_user(
	userid varchar(32),
	password varchar(32),
	mail_address varchar(255),
	screen_lines numeric(2),
	name varchar(32),
	update_userid varchar(32),
	updated	timestamp,
	deleted	timestamp
);

create index m_user_index1 on m_user(userid);

insert into m_user (userid,password,screen_lines) values('olut','886e610d40a6aad89e8f96db7b9076eb',20);
