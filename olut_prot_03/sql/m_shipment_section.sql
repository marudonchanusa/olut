-- 本部部門マスター生成
-- 2005/9/4
-- ver 0.03

create table m_shipment_section(
	code   char(2) not null,
	name   varchar(50) not null default '',
	name_k varchar(60) not null,
	ck_flag char(1),
	ck_com_code char(5),
	isdcd	char(15),
	update_userid	varchar(32),
	updated timestamp,
	deleted timestamp
);

create index m_shipment_section_index_1 on m_shipment_section(code);


