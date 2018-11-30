-- 単位マスター生成
-- 2005/7/29
-- ver 0.02

create table m_unit(
	code   char(2) not null,
	name   varchar(50) not null default '',
	name_k varchar(60) not null,
	update_userid	varchar(32),
	updated timestamp,
	deleted timestamp
);

