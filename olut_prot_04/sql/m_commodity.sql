-- 商品マスター生成
-- 2005/9/9
-- ver 0.02

create table m_commodity
(
	code	char(5),
	name	varchar(80),
	name_k	varchar(90),
	dist_flag	char(1),
	unit_code	char(2),
	order_unit_code	char(2),
	order_amount	numeric(5),
	lot_unit_code	char(2),
	lot_amount	numeric(5),
	ship_section_code	char(2),
	accd		char(4),
	itmcd		char(4),
	com_class_code	char(5),
	order_sheet_flag	char(1),
	stock_flag	char(1),
	current_unit_price	numeric(7,2),
	next_unit_price		numeric(7,2),
	jancd	char(20),
	note	text,
	note_k	text,
	depletion_flag	char(1),
	update_userid	varchar(32),
	updated		timestamp,
	deleted		timestamp
);

create index m_commodity_index_1 on m_commodity(code);
