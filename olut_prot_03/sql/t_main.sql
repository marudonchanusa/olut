-- メイントランザクション生成
-- 2005/9/28
-- ver 0.06

create table t_main(
	id		serial,
	act_date	date,
	slip_no		numeric(7),
	line_no		numeric(2),
	act_flag	char(5),
	com_code	char(5),
	orig_code	char(5),
	dest_code	char(5),
	store_sec_code	char(2),
	ship_sec_code	char(2),
	warehouse_code	char(2),
	amount		numeric(11,2),
	unit_price	numeric(11,2),
	total_price	numeric(11),
	payment_flag	char(1),
	memo		text,
	calc_flag	char(1),
	update_userid	varchar(32),
	updated		timestamp,
	deleted		timestamp
);	

create index t_main_index_1 on t_main(act_date);
create index t_main_index_2 on t_main(slip_no);
create index t_main_index_3 on t_main(line_no);
create index t_main_index_4 on t_main(act_flag);
create index t_main_index_5 on t_main(com_code);
create index t_main_index_6 on t_main(orig_code);
create index t_main_index_7 on t_main(dest_code);

