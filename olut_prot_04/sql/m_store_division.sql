-- 店舗内部門マスター生成
-- 2005/7/29
-- ver 0.01

create table m_store_division(
	code   char(2) not null,
	name   varchar(50) not null default '',
	name_k varchar(60) not null,
	update_userid	varchar(32),
	updated timestamp,
	deleted timestamp
);

