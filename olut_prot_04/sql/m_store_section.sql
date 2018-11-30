-- 店舗内部門マスター生成
-- 2005/8/26
-- ver 0.01

create table m_store_section(
	code   char(5) not null,
	store_section_code char(2),
	updated timestamp
);

create index m_store_section_index on m_store_section(code);

